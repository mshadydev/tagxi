<?php

namespace App\Transformers\Owner;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin\Owner;
use App\Base\Constants\Auth\Role;
use App\Transformers\Transformer;
use App\Models\Request\RequestBill;
use App\Models\Request\RequestMeta;
use App\Models\Admin\OwnerDocument;
use App\Models\Admin\OwnerNeededDocument;
use App\Transformers\Access\RoleTransformer;
use App\Transformers\Requests\TripRequestTransformer;
use App\Base\Constants\Setting\Settings;
use App\Models\Admin\Sos;
use App\Transformers\Common\SosTransformer;

class OwnerProfileTransformer extends Transformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected array $availableIncludes = [
        
    ];

    /**
    * Resources that can be included default.
    *
    * @var array
    */
    protected array $defaultIncludes = [
        
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Owner $user)
    {
        $params = [
            'id' => $user->id,
            'company_name' => $user->company_name,
            'address' => $user->address,
            'postal_code' => $user->postal_code,
            'city' => $user->city,
            'tax_number' => $user->tax_number,
            'name' => $user->owner_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'profile_picture' => $user->user->profile_picture,
            'active' => (bool)$user->active,
            'approve' => (bool)$user->approve,
            'available' => (bool)$user->available,
            'uploaded_document'=>false,
            'declined_reason'=>$user->reason,
            'service_location_id'=>$user->service_location_id,
            'service_location_name'=>$user->area->name,
            'timezone'=>$user->timezone,
            'refferal_code'=>$user->user->refferal_code,
            'country_id'=>$user->user->countryDetail->id,
            'currency_symbol' => $user->user->countryDetail->currency_symbol,
            'role'=>'owner',
            'transport_type' => $user->transport_type,

        ];

        $params['contact_us_mobile1'] =  get_settings('contact_us_mobile1');
        $params['contact_us_mobile2'] =  get_settings('contact_us_mobile2');
        $params['contact_us_link'] =  get_settings('contact_us_link');

        $current_date = Carbon::now();

        $timezone = $user->user->timezone?:env('SYSTEM_DEFAULT_TIMEZONE');

        $updated_current_date =  $current_date->setTimezone($timezone);

        $params['current_date'] = $updated_current_date->toDateString();

        $driver_documents = OwnerNeededDocument::active()->get();

        foreach ($driver_documents as $key => $needed_document) {
            if (OwnerDocument::where('owner_id', $user->id)->where('document_id', $needed_document->id)->exists()) {
                $params['uploaded_document'] = true;
            } else {
                $params['uploaded_document'] = false;
            }
        }

        $low_balance = false;

        $owner_wallet = auth()->user()->owner->ownerWalletDetail;

        $wallet_balance= $owner_wallet?$owner_wallet->amount_balance:0;

         $minimum_balance = get_settings(Settings::OWNER_WALLET_MINIMUM_AMOUNT_TO_GET_ORDER);

            if($minimum_balance >=0){
                if ($minimum_balance > $wallet_balance) {

                $user->active = false;

                $user->save();
                
                $params['active'] = false;


                $low_balance = true;
            }
                
            }

            $params['low_balance'] = $low_balance;


        return $params;
    }

   

   
}
