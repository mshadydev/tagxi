<?php

namespace App\Transformers\Driver;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin\Driver;
use App\Base\Constants\Auth\Role;
use App\Transformers\Transformer;
use App\Models\Request\RequestBill;
use App\Models\Request\RequestMeta;
use App\Models\Admin\DriverDocument;
use App\Models\Admin\DriverNeededDocument;
use App\Transformers\Access\RoleTransformer;
use App\Transformers\Requests\TripRequestTransformer;
use App\Base\Constants\Setting\Settings;
use App\Models\Admin\Sos;
use App\Transformers\Common\SosTransformer;
use App\Models\Admin\UserDriverNotification;
use App\Transformers\Common\DriverVehicleTypeTransformer;

class DriverProfileTransformer extends Transformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected array $availableIncludes = [
        'onTripRequest','metaRequest'
    ];

    /**
    * Resources that can be included default.
    *
    * @var array
    */
    protected array $defaultIncludes = [
        'sos','driverVehicleType'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Driver $user)
    {
        $params = [
            'id' => $user->id,
            'owner_id' => $user->owner_id,
            'transport_type' => $user->transport_type,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'profile_picture' => $user->profile_picture,
            'active' => (bool)$user->active,
            'approve' => (bool)$user->approve,
            'available' => (bool)$user->available,
            'uploaded_document' => false,
            'declined_reason' => $user->reason,
            'service_location_id' => $user->service_location_id,
            'service_location_name' => $user->serviceLocation->name,
            'vehicle_year' => $user->vehicle_year,
            'vehicle_type_id' => $user->vehicle_type,
            'vehicle_type_name' => $user->vehicle_type_name,
            'vehicle_type_image' => $user->vehicle_type_image,
            'vehicle_type_icon_for' => $user->vehicle_type_icon_for,
            'car_make' => $user->car_make,
            'car_model' => $user->car_model,
            'car_make_name' => $user->car_make_name,
            'car_model_name' => $user->car_model_name,
            'car_color' => $user->car_color,
            'driver_lat' => $user->driver_lat,
            'driver_lng' => $user->driver_lng,
            'car_number' => $user->car_number,
            'rating' => round($user->rating, 2),
            'no_of_ratings' => $user->no_of_ratings,
            'timezone' => $user->timezone,
            'refferal_code' => $user->user->refferal_code,
            //'map_key'=>get_settings('google_map_key'),
            'company_key' => $user->user->company_key,
            'show_instant_ride' => false,
            'country_id' => $user->user->countryDetail->id,
            'currency_symbol' => get_settings(Settings::CURRENCY),
            'my_route_lat' => $user->my_route_lat,
            'my_route_lng' => $user->my_route_lng,
            'my_route_address' => $user->my_route_address,
            'enable_my_route_booking' => $user->enable_my_route_booking,
            'role' => 'driver',
            'enable_bidding' => false
        ];

        $params['vehicle_types'] = [];

        $params['enable_my_route_booking_feature'] =  false;



        if($user->driverVehicleTypeDetail()->exists()) {
            foreach ($user->driverVehicleTypeDetail as $key => $type) {

                $params['vehicle_types'][] = $type->vehicle_type;

                if($type->vehicleType->trip_dispatch_type == 'bidding') {

                    $params['enable_bidding'] = true;

                }
                if($type->vehicleType->trip_dispatch_type != 'bidding') {
                    $params['enable_my_route_booking_feature'] =  get_settings('enable_my_route_booking_feature');

                }

            }
        }


        $notifications_count = UserDriverNotification::where('driver_id', $user->id)
            ->where('is_read', 0)->count();

        $params['notifications_count'] = $notifications_count;


        if($user->fleet_id) {
            $params['car_make_name'] = $user->fleetDetail->carBrand->name;
            $params['car_model_name'] = $user->fleetDetail->carModel->name;
            $params['car_number'] = $user->fleetDetail->license_number;
            $params['car_color'] = $user->fleetDetail->car_color;

        }

        $params['enable_modules_for_applications'] =  get_settings('enable_modules_for_applications');

        $params['contact_us_mobile1'] =  get_settings('contact_us_mobile1');
        $params['contact_us_mobile2'] =  get_settings('contact_us_mobile2');
        $params['contact_us_link'] =  get_settings('contact_us_link');
        $params['show_wallet_feature_on_mobile_app'] =  get_settings('show_wallet_feature_on_mobile_app');
        $params['show_bank_info_feature_on_mobile_app'] =  get_settings('show_bank_info_feature_on_mobile_app');
        $params['how_many_times_a_driver_can_enable_the_my_route_booking_per_day'] =  get_settings('how_many_times_a_driver_can_enable_the_my_route_booking_per_day');


        $current_date = Carbon::now();

        $total_earnings = RequestBill::whereHas('requestDetail', function ($query) use ($user, $current_date) {
            $query->where('driver_id', $user->id)->where('is_completed', 1)->whereDate('trip_start_time', $current_date);
        })->sum('driver_commision');

        $timezone = $user->user->timezone ?: env('SYSTEM_DEFAULT_TIMEZONE');

        $updated_current_date =  $current_date->setTimezone($timezone);

        $params['total_earnings'] = $total_earnings;
        $params['current_date'] = $updated_current_date->toDateString();

        if($user->owner_id) {
            $driver_documents = DriverNeededDocument::active()->where(function ($query) {
                $query->where('account_type', 'fleet_driver')->orWhere('account_type', 'both');
            })->get();
        } else {

            $driver_documents = DriverNeededDocument::active()->where(function ($query) {
                $query->where('account_type', 'individual')->orWhere('account_type', 'both');
            })->get();
        }

        foreach ($driver_documents as $key => $needed_document) {
            if (DriverDocument::where('driver_id', $user->id)->where('document_id', $needed_document->id)->exists()) {
                $params['uploaded_document'] = true;
            } else {
                $params['uploaded_document'] = false;
            }
        }

        $low_balance = false;

        $driver_wallet = auth()->user()->driver->driverWallet;

        $wallet_balance = $driver_wallet ? $driver_wallet->amount_balance : 0;



        $minimum_balance = get_settings(Settings::DRIVER_WALLET_MINIMUM_AMOUNT_TO_GET_ORDER);

        if($user->owner_id) {

            $minimum_balance = get_settings(Settings::OWNER_WALLET_MINIMUM_AMOUNT_TO_GET_ORDER);

            $owner_wallet = $user->owner->ownerWalletDetail;

            $wallet_balance = $owner_wallet ? $owner_wallet->amount_balance : 0;

        }

        if($minimum_balance > 0) {
            if ($minimum_balance > $wallet_balance) {

                $user->active = false;

                $user->save();

                $params['active'] = false;


                $low_balance = true;
            }

        }

        $params['trip_accept_reject_duration_for_driver'] = get_settings(Settings::TRIP_ACCEPT_REJECT_DURATION_FOR_DRIVER);

        $params['maximum_time_for_find_drivers_for_bitting_ride'] = (get_settings(Settings::MAXIMUM_TIME_FOR_FIND_DRIVERS_FOR_BITTING_RIDE) * 60);

        $params['low_balance'] = $low_balance;

        $params['enable_shipment_load_feature'] = get_settings(Settings::ENABLE_SHIPMENT_LOAD_FEATURE);
        $params['enable_shipment_unload_feature'] = get_settings(Settings::ENABLE_SHIPMENT_UNLOAD_FEATURE);
        $params['enable_digital_signature'] = get_settings(Settings::ENABLE_DIGITAL_SIGNATURE);


        $params['distance_list'] = [
            ['name' => '0-2 km', 'value' => '0.43496'],
            ['name' => '0-5 km', 'value' => '1.0874'],
            ['name' => '0-7 km', 'value' => '1.7088']
        ];

        return $params;
    }

    /**
     * Include the request of the driver.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeOnTripRequest(Driver $user)
    {
        // dd($user);
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $request = $user->requestDetail()->where('is_cancelled', false)->where('driver_rated', false)->where(function ($query) use ($now) {
            $query->where('trip_start_time', '<=', $now);
            $query->orWhere('trip_start_time', '=', null);
        })->first();

        return $request
        ? $this->item($request, new TripRequestTransformer())
        : $this->null();
    }

    /**
    * Include the request meta of the user.
    *
    * @param User $user
    * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
    */
    public function includeSos(Driver $user)
    {

        $request = Sos::select('id', 'name', 'number', 'user_type', 'created_by')
        ->where('created_by', auth()->user()->id)
        ->orWhere('user_type', 'admin')
        ->orderBy('created_at', 'Desc')
        ->companyKey()->get();

        return $request
        ? $this->collection($request, new SosTransformer())
        : $this->null();
    }

    /**
     * Include the meta request of the driver.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeMetaRequest(Driver $user)
    {
        $request_meta = RequestMeta::where('driver_id', $user->id)->where('active', true)->first();
        if ($request_meta) {
            $request = $request_meta->request;
            return $request
        ? $this->item($request, new TripRequestTransformer())
        : $this->null();
        }
        return $this->null();
    }
    /**
    * Include the request meta of the user.
    *
    * @param User $user
    * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
    */
    public function includeDriverVehicleType(Driver $user)
    {

        $driverVehicleType = $user->driverVehicleTypeDetail;

        return $driverVehicleType
        ? $this->collection($driverVehicleType, new DriverVehicleTypeTransformer())
        : $this->null();
    }



}
