<?php

namespace App\Http\Controllers\Api\V1\Request;

use App\Jobs\NotifyViaMqtt;
use App\Jobs\NotifyViaSocket;
use App\Base\Constants\Masters\UserType;
use App\Base\Constants\Masters\PushEnums;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Request\CancelTripRequest;
use App\Jobs\Notifications\AndroidPushNotification;
use App\Transformers\Requests\TripRequestTransformer;
use App\Models\Admin\CancellationReason;
use App\Base\Constants\Masters\zoneRideType;
use App\Base\Constants\Masters\WalletRemarks;
use App\Models\Request\DriverRejectedRequest;
use App\Models\Request\RequestMeta;
use App\Models\Admin\Driver;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;
use Sk\Geohash\Geohash;
use Illuminate\Support\Facades\Log;
use App\Jobs\Notifications\SendPushNotification;



/**
 * @group Driver-trips-apis
 *
 * APIs for Driver-trips apis
 */
class DriverCancelRequestController extends BaseController
{

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    /**
    * Driver Cancel Trip Request
    * @bodyParam request_id uuid required id of request
    * @bodyParam reason string optional reason provided by user
    * @bodyParam custom_reason string optional custom reason provided by user
    *@response {
    "success": true,
    "message": "driver_cancelled_trip"}
    */
    public function cancelRequest(CancelTripRequest $request)
    {
        /**
        * Validate the request which is authorised by current authenticated user
        * Cancel the request by updating is_cancelled true with reason if there is any reason
        * Notify the user that is cancelled the trip request by driver
        */
        // Validate the request which is authorised by current authenticated user
        $driver = auth()->user()->driver;
        // Update the availble status
        $driver->available = true;
        $driver->save();
        $driver->fresh();

        $request_detail = $driver->requestDetail()->where('id', $request->request_id)->first();
        // Throw an exception if the user is not authorised for this request
        if (!$request_detail) {
            $this->throwAuthorizationException();
        }

        // Existing Flow
        // $request_detail->update([
        //     'is_cancelled'=>true,
        //     'reason'=>$request->reason,
        //     'custom_reason'=>$request->custom_reason,
        //     'cancel_method'=>UserType::DRIVER,
        // ]);

        DriverRejectedRequest::create([
            'request_id'=>$request_detail->id,
            'is_after_accept'=>true,
            'driver_id'=>$driver->id,'reason'=>$request->reason,
            'custom_reason'=>$request->custom_reason]);

        /**
        * Apply Cancellation Fee
        */
        $charge_applicable = false;
        if ($request->custom_reason) {
            $charge_applicable = true;
        }
        if ($request->reason) {
            $reason = CancellationReason::find($request->reason);

            if ($reason->payment_type=='free') {
                $charge_applicable=false;
            } else {
                $charge_applicable=true;
            }
        }

          /**
         * get prices from zone type
         */
        if ($request_detail->is_later) {
            $ride_type = zoneRideType::RIDELATER;

        } else {
            $ride_type = zoneRideType::RIDENOW;

        }

        if ($charge_applicable) {

            $zone_type_price = $request_detail->zoneType->zoneTypePrice()->where('price_type', $ride_type)->first();

            $cancellation_fee = $zone_type_price->cancellation_fee;

            $requested_driver = $request_detail->driverDetail;

            if($request_detail->driverDetail->owner()->exists()){

            $owner_wallet = $request_detail->driverDetail->owner->ownerWalletDetail;
            $owner_wallet->amount_spent += $cancellation_fee;
            $owner_wallet->amount_balance -= $cancellation_fee;
            $owner_wallet->save();

            // Add the history
            $owner_wallet_history = $request_detail->driverDetail->owner->ownerWalletHistoryDetail()->create([
                'amount'=>$cancellation_fee,
                'transaction_id'=>$request_detail->id,
                'remarks'=>WalletRemarks::CANCELLATION_FEE,
                'request_id'=>$request_detail->id,
                'is_credit'=>false
            ]);


            }else{

                $driver_wallet = $requested_driver->driverWallet;
            $driver_wallet->amount_spent += $cancellation_fee;
            $driver_wallet->amount_balance -= $cancellation_fee;
            $driver_wallet->save();

            // Add the history
            $requested_driver->driverWalletHistory()->create([
            'amount'=>$cancellation_fee,
            'transaction_id'=>$request_detail->id,
            'remarks'=>WalletRemarks::CANCELLATION_FEE,
            'request_id'=>$request_detail->id,
            'is_credit'=>false]);

            }


            $request_detail->requestCancellationFee()->create(['driver_id'=>$request_detail->driver_id,'is_paid'=>true,'cancellation_fee'=>$cancellation_fee,'paid_request_id'=>$request_detail->id]);
        }

        // Get the user detail
        $user = $request_detail->userDetail;

        /**
         * Find New drivers for this Ride
         *
         * */

        // New Flow

        if($request_detail->is_bid_ride){

            $request_detail->update([
            'is_cancelled'=>true,
            'reason'=>$request->reason,
            'custom_reason'=>$request->custom_reason,
            'cancel_method'=>UserType::DRIVER,
        ]);

            goto no_drivers_available;
        }

        $request_detail->update([
            'driver_id'=>null,
            'arrived_at'=>null,
            'accepted_at'=>null,
            'is_driver_started'=>0,
            'is_driver_arrived'=>0,
            'updated_at'=>Carbon::now()->setTimezone('UTC')->toDateTimeString()
        ]);

        $nearest_drivers =  $this->getFirebaseDrivers($request_detail);

        $request_result =  fractal($request_detail, new TripRequestTransformer)->parseIncludes('userDetail');

         if (!$nearest_drivers) {
                goto no_drivers_available;
        }

         $selected_drivers = [];
        $i = 0;
        foreach ($nearest_drivers as $driver) {
            Log::info("in-loop");
            // $selected_drivers[$i]["request_id"] = $request_detail->id;
            $selected_drivers[$i]["user_id"] = $request_detail->userDetail->id;
            $selected_drivers[$i]["driver_id"] = $driver->id;
            $selected_drivers[$i]["active"] = $i == 0 ? 1 : 0;
            $selected_drivers[$i]["assign_method"] = 1;
            $selected_drivers[$i]["created_at"] = date('Y-m-d H:i:s');
            $selected_drivers[$i]["updated_at"] = date('Y-m-d H:i:s');
            $i++;
        }


        // Send notification to the very first driver
        $first_meta_driver = $selected_drivers[0]['driver_id'];

        // Add first Driver into Firebase Request Meta
        $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$first_meta_driver,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

        // Update Request
        $this->database->getReference('requests/'.$request_detail->id)->update(['cancelled_by_driver'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

        $pus_request_detail = $request_result->toJson();
        $push_data = ['notification_enum'=>PushEnums::REQUEST_CREATED,'result'=>$pus_request_detail];


        $socket_data = new \stdClass();
        $socket_data->success = true;
        $socket_data->success_message  = PushEnums::REQUEST_CREATED;
        $socket_data->result = $request_result;

        $driver = Driver::find($first_meta_driver);

        $notifable_driver = $driver->user;

        $title = trans('push_notifications.new_request_title');
        $body = trans('push_notifications.new_request_body');

        dispatch(new SendPushNotification($notifable_driver,$title,$body));

        foreach ($selected_drivers as $key => $selected_driver) {
            $request_detail->requestMeta()->create($selected_driver);
        }

        no_drivers_available:

        // Notify the user that the driver is cancelled the trip request
        if ($user) {
            $request_result =  fractal($request_detail, new TripRequestTransformer)->parseIncludes('driverDetail');

            $push_request_detail = $request_result->toJson();
            $title = trans('push_notifications.trip_cancelled_by_driver_title');
            $body = trans('push_notifications.trip_cancelled_by_driver_body');

            $push_data = ['success'=>true,'success_message'=>PushEnums::REQUEST_CANCELLED_BY_DRIVER,'result'=>(string)$push_request_detail];


            dispatch(new SendPushNotification($user,$title,$body));
        }


        return $this->respondSuccess(null, 'driver_cancelled_trip');
    }



    /**
    * Get Drivers from firebase
    */
    public function getFirebaseDrivers($request_detail)
    {

        $pick_lat = $request_detail->pick_lat;
        $pick_lng = $request_detail->pick_lng;

        $type_id = $request_detail->zoneType->type_id;

        // NEW flow
        $driver_search_radius = get_settings('driver_search_radius')?:30;

        $radius = kilometer_to_miles($driver_search_radius);

        $calculatable_radius = ($radius/2);

        $calulatable_lat = 0.0144927536231884 * $calculatable_radius;
        $calulatable_long = 0.0181818181818182 * $calculatable_radius;

        $lower_lat = ($pick_lat - $calulatable_lat);
        $lower_long = ($pick_lng - $calulatable_long);

        $higher_lat = ($pick_lat + $calulatable_lat);
        $higher_long = ($pick_lng + $calulatable_long);

        $g = new Geohash();

        $lower_hash = $g->encode($lower_lat,$lower_long, 12);
        $higher_hash = $g->encode($higher_lat,$higher_long, 12);

        $conditional_timestamp = Carbon::now()->subMinutes(7)->timestamp;

        $vehicle_type = $type_id;

        $fire_drivers = $this->database->getReference('drivers')->orderByChild('g')->startAt($lower_hash)->endAt($higher_hash)->getValue();

        $firebase_drivers = [];

        $i=-1;

        foreach ($fire_drivers as $key => $fire_driver) {
            $i +=1;
            $driver_updated_at = Carbon::createFromTimestamp($fire_driver['updated_at'] / 1000)->timestamp;

            if(array_key_exists('vehicle_type',$fire_driver) && $fire_driver['vehicle_type']==$vehicle_type && $fire_driver['is_active']==1 && $fire_driver['is_available']==1 && $conditional_timestamp < $driver_updated_at){

                $distance = distance_between_two_coordinates($pick_lat,$pick_lng,$fire_driver['l'][0],$fire_driver['l'][1],'K');

                if($distance <= $driver_search_radius){

                    $firebase_drivers[$fire_driver['id']]['distance']= $distance;

                }
                // $firebase_drivers[$fire_driver['id']]['distance']= $distance;

            }

        }

        asort($firebase_drivers);

        if (!empty($firebase_drivers)) {

            $nearest_driver_ids = [];

                foreach ($firebase_drivers as $key => $firebase_driver) {

                    $nearest_driver_ids[]=$key;
                }

                $driver_search_radius = get_settings('driver_search_radius')?:30;

                $haversine = "(6371 * acos(cos(radians($pick_lat)) * cos(radians(pick_lat)) * cos(radians(pick_lng) - radians($pick_lng)) + sin(radians($pick_lat)) * sin(radians(pick_lat))))";
                // Get Drivers who are all going to accept or reject the some request that nears the user's current location.
                $meta_drivers = RequestMeta::whereHas('request.requestPlace', function ($query) use ($haversine,$driver_search_radius) {
                    $query->select('request_places.*')->selectRaw("{$haversine} AS distance")
                ->whereRaw("{$haversine} < ?", [$driver_search_radius]);
                })->pluck('driver_id')->toArray();

                $nearest_drivers = Driver::where('active', 1)->where('approve', 1)->where('available', 1)->where('vehicle_type', $type_id)->whereIn('id', $nearest_driver_ids)->whereNotIn('id', $meta_drivers)->limit(10)->get();

                if ($nearest_drivers->isEmpty()) {
                    // $this->throwCustomException('all drivers are busy');

                    return null;
                }

                return $nearest_drivers;

        } else {

            return null;
        }
    }
}
