<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\BaseController;
use App\Models\Admin\ServiceLocation;
use Illuminate\Http\Request;
use App\Base\Constants\Auth\Role as RoleSlug;
use App\Base\Constants\Taxi\DriverDocumentStatus;
use App\Base\Filters\Master\CommonMasterFilter;
use App\Base\Filters\Taxi\OwnerFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Base\Services\ImageUploader\ImageUploaderContract;
use App\Exports\CommonReportExport;
use App\Http\Requests\Taxi\Owner\StoreOwnerRequest;
use App\Http\Requests\Taxi\Owner\UpdateOwnerRequest;
use App\Jobs\Notifications\Auth\EmailConfirmationNotification;
use App\Models\Admin\Owner;
use App\Models\Admin\OwnerDocument;
use App\Models\Admin\OwnerNeededDocument;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Kreait\Firebase\Contract\Database;
use App\Models\Payment\OwnerWalletHistory;
use App\Models\Payment\OwnerWallet;
use App\Http\Requests\Admin\Owner\AddOwnerMoneyToWalletRequest;
use App\Transformers\Payment\OwnerWalletHistoryTransformer;
use App\Models\Payment\UserWalletHistory;
use App\Base\Constants\Setting\Settings;
use Illuminate\Support\Str;
use App\Base\Constants\Masters\WalletRemarks;

class OwnerController extends BaseController
{
    /**
     * The Owner model instance.
     *
     * @var \App\Models\Admin\Owner
     */
    protected $owner;

    /**
     * The User model instance.
     *
     * @var \App\Models\User
     */
    protected $user;

    protected $imageUploader;
    /**
     * OwnerController constructor.
     *
     * @param \App\Models\Admin\Owner $owner
     */
    public function __construct(Owner $owner, User $user,ImageUploaderContract $imageUploader,Database $database)
    {
        $this->owner = $owner;
        $this->user = $user;
        $this->imageUploader = $imageUploader;
        $this->database = $database;

    }

    public function index(ServiceLocation $area)
    {
        $page = trans('pages_names.owners');
        $main_menu = 'manage_owners';
        $sub_menu = $area->name;
        $activeOwners = Owner::whereApprove(true)->whereServiceLocationId($area->id)->count();
        $inactiveOwners = Owner::whereApprove(false)->whereServiceLocationId($area->id)->count();

        return view('admin.owners.index', compact('page', 'main_menu', 'sub_menu', 'activeOwners', 'inactiveOwners','area'));
    }

    public function getAllOwner(QueryFilterContract $queryFilter,ServiceLocation $area)
    {
        if (access()->hasRole(RoleSlug::SUPER_ADMIN)) {
            $query = Owner::orderBy('created_at', 'desc')->whereServiceLocationId($area->id);
        } else {
            // @TODO based who create owner
            $this->validateAdmin();
            $query = Owner::orderBy('created_at', 'desc')->whereServiceLocationId($area->id);
        }

        $results = $queryFilter->builder($query)->customFilter(new OwnerFilter)->paginate();

        if (request()->has('report')) {
            $format = request()->format;
            $view = 'admin.owners.reports.owners';
            $filename = "Owner Report-".date('ymdis').'.'.$format;

            Excel::store(new CommonReportExport($results, $view), $filename, 'reports');

            $path = url('/storage/reports/'.$filename);

            return $path;
        }

        return view('admin.owners._owners', compact('results'))->render();
    }
    
    public function create(ServiceLocation $area)
    {
        $page = trans('pages_names.add_owner');

        $services = ServiceLocation::whereActive(true)->get();
        $main_menu = 'manage_owners';
        $sub_menu = $area->name;
        $needed_document = OwnerNeededDocument::active()->get();

        return view('admin.owners.create', compact('services', 'page', 'main_menu', 'sub_menu','needed_document','area'));
    }

    public function store(StoreOwnerRequest $request){
        $created_params = $request->only(['service_location_id','company_name','owner_name','name','surname','mobile','phone','email','address','postal_code','city','no_of_vehicles','tax_number','bank_name','ifsc','account_no','transport_type']);
        $userParam = $request->only(['name','email','mobile']);
        $userParam['mobile_confirmed'] = true;
        $userParam['password'] = bcrypt($request->input('password'));
        $created_params['password'] = bcrypt($request->input('password'));

        $user = $this->user->create($userParam);
        
        $token = str_random(40);
        $user->forceFill([
            'email_confirmation_token' => bcrypt($token)
        ])->save();
        
        // $this->dispatch(new EmailConfirmationNotification($user, $token));

        $user->attachRole(RoleSlug::OWNER);
        
        $user->owner()->create($created_params);

        $user->owner->ownerWalletDetail()->create(['amount_added'=>0]);

        foreach($request->needed_document as $key => $document){

            $name = 'document_'.($key + 1);
            $owner = Owner::whereId($user->owner->id)->first();
            $doc = OwnerNeededDocument::whereId($document)->first();
            // dd($doc);
            $expiry_date = $doc->has_expiry_date ? $request->expiry_date[$key] : null;

            $docController = new OwnerDocumentController($this->imageUploader);
            $docController->uploadOwnerDoc($name,$expiry_date,$request,$owner,$doc);
        }
        
        $message = trans('succes_messages.owner_added_succesfully');

        return redirect("owners/by_area/$request->service_location_id")->with('success', $message);
    }

    public function getById(Owner $owner)
    {
        $page = trans('pages_names.edit_owner');
        $main_menu = 'manage_owners';
        $sub_menu = $owner->area->name;
      
        $count = count($owner->ownerDocument);
        $neeedeDoc = OwnerNeededDocument::whereActive(true)->count();

       if($neeedeDoc != $count){

           return redirect('owners/document/view/'.$owner->id);
       }

        $item = $owner;
        $services = ServiceLocation::whereActive(true)->whereId($item->service_location_id)->get();
        $needed_document = OwnerNeededDocument::active()->get();
        return view('admin.owners.update', compact('item', 'services', 'page', 'main_menu', 'sub_menu','needed_document'));
    }

    public function update(Owner $owner,UpdateOwnerRequest $request)
    {
        $updated_params = $request->only(['service_location_id','company_name','owner_name','name','surname','mobile','phone','email','password','address','postal_code','city','expiry_date','no_of_vehicles','tax_number','bank_name','ifsc','account_no','tansport_type']);
        $userParam = $request->only(['name','email','mobile']);

        $owner->user->update($userParam);

        $owner->update($updated_params);

        if ($request->needed_document != null) 
        {

        foreach($request->needed_document as $key => $document)
        {
            $name = 'document_'.($key + 1);
            $doc = OwnerNeededDocument::whereId($document)->first();
            $expiry_date = $doc->has_expiry_date ? $request->expiry_date[$key] : null;

            $docController = new OwnerDocumentController($this->imageUploader);
            $docController->uploadOwnerDoc($name,$expiry_date,$request,$owner,$doc);
        
            }   
        }   

        $message = trans('succes_messages.owner_updated_succesfully');

        return redirect("owners/by_area/$owner->service_location_id")->with('success', $message);
    }

    public function delete(Owner $owner)
    {
        $owner->user()->delete();

        $message = trans('succes_messages.owner_deleted_succesfully');
        // return $message;
        return redirect("owners/by_area/$owner->service_location_id")->with('success', $message);
    }

    public function toggleApprove(Owner $owner)
    {
        $status = $owner->approve == 1 ? 0 : 1;
        
        if($status){
            
    
            $err = false;
            $neededDoc = OwnerNeededDocument::where('active','1')->count();
            $uploadedDoc = count($owner->ownerDocument);
    
            if ($neededDoc != $uploadedDoc) {
                $message = trans('succes_messages.owner_document_not_uploaded');
                return redirect("owners/by_area/$owner->service_location_id")->with('warning', $message);
            }
    
            foreach ($owner->ownerDocument as $ownerDoc) {
                if ($ownerDoc->document_status != 1) {
                    $err = true;
                }
            }
    
            if ($err) {
                $message = trans('succes_messages.owner_document_not_uploaded');
                return redirect("owners/by_area/$owner->service_location_id")->with('warning', $message);
            }
        }

        $owner->update([
            'approve' => $status
        ]);

        $this->database->getReference('owners/'.$owner->id)->update(['approve'=>(int)$status,'updated_at'=> Database::SERVER_TIMESTAMP]);

        $message = trans('succes_messages.owner_approve_status_changed_succesfully');

        return redirect("owners/by_area/$owner->service_location_id")->with('success', $message);
    }

    public function getOwnerByArea(Request $request){
        $id = $request->id;

        return $this->owner->whereServiceLocationId($id)->get();
    }
    public function OwnerPaymentHistory(Owner $owner)
    {
        $main_menu = 'owners';
        $sub_menu = 'owner_details';
        $item = $owner;
        // dd($item);

        $amount = OwnerWallet::where('user_id',$owner->id)->first();
        
        if ($amount == null) {

         $card = [];
         $card['total_amount'] = ['name' => 'total_amount', 'display_name' => 'Total Amount ', 'count' => "0", 'icon' => 'fa fa-flag-checkered text-green'];
        $card['amount_spent'] = ['name' => 'amount_spent', 'display_name' => 'Spend Amount ', 'count' => "0", 'icon' => 'fa fa-ban text-red'];
        $card['balance_amount'] = ['name' => 'balance_amount', 'display_name' => 'Balance Amount', 'count' => "0", 'icon' => 'fa fa-ban text-red'];

         $history = UserWalletHistory::where('user_id',$owner->id)->orderBy('created_at','desc')->paginate(10);
        }
        else{
         $card = [];
        $card['total_amount'] = ['name' => 'total_amount', 'display_name' => 'Total Amount ', 'count' => $amount->amount_added, 'icon' => 'fa fa-flag-checkered text-green'];
        $card['amount_spent'] = ['name' => 'amount_spent', 'display_name' => 'Spend Amount ', 'count' => $amount->amount_spent, 'icon' => 'fa fa-ban text-red'];
        $card['balance_amount'] = ['name' => 'balance_amount', 'display_name' => 'Balance Amount', 'count' => $amount->amount_balance, 'icon' => 'fa fa-ban text-red'];


         $history = OwnerWalletHistory::where('user_id',$owner->id)->orderBy('created_at','desc')->paginate(10);

          }

        return view('admin.owners.owner-payment-wallet', compact('card','main_menu','sub_menu','item','history'));
    }

    public function StoreOwnerPaymentHistory(AddOwnerMoneyToWalletRequest $request,Owner $owner)
    {

        $currency = get_settings(Settings::CURRENCY);

        // $converted_amount_array =  convert_currency_to_usd($user_currency_code, $request->input('amount'));

        // $converted_amount = $converted_amount_array['converted_amount'];
        // $converted_type = $converted_amount_array['converted_type'];
        // $conversion = $converted_type.':'.$request->amount.'-'.$converted_amount;
        $transaction_id = Str::random(6);


            $wallet_model = new OwnerWallet();
            $wallet_add_history_model = new OwnerWalletHistory();
            $user_id = $owner->id;


        $user_wallet = $wallet_model::firstOrCreate([
            'user_id'=>$user_id]);
        $user_wallet->amount_added += $request->amount;
        $user_wallet->amount_balance += $request->amount;
        $user_wallet->save();

        $wallet_add_history_model::create([
            'user_id'=>$user_id,
            'card_id'=>null,
            'amount'=>$request->amount,
            'transaction_id'=>$transaction_id,
            'merchant'=>null,
            'remarks'=>WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET_FROM_ADMIN,
            'is_credit'=>true]);


         $message = "money_added_successfully";
        return redirect()->back()->with('success', $message);


    }
}
