<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Jobs\NotifyViaMqtt;
use App\Models\Admin\Driver;
use App\Jobs\NotifyViaSocket;
use App\Models\Request\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Request\RequestMeta;
use App\Jobs\NoDriverFoundNotifyJob;
use App\Base\Constants\Masters\PushEnums;
use App\Jobs\Notifications\AndroidPushNotification;
use App\Transformers\Requests\TripRequestTransformer;
use App\Transformers\Requests\CronTripRequestTransformer;
use App\Models\Request\DriverRejectedRequest;
use Sk\Geohash\Geohash;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Log;
use App\Jobs\Notifications\SendPushNotification;


class AssignDriversForRegularRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign_drivers:for_regular_rides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Drivers for regular rides';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $current_time = Carbon::now()->format('Y-m-d H:i:s');
        $sub_5_min = Carbon::now()->subMinutes(20)->format('Y-m-d H:i:s');
        // DB::enableQueryLog();
        $requests = Request::where('is_later', 0)
                    ->where('created_at', '<=', $current_time)
                    ->where('created_at', '>', $sub_5_min)
                    ->where('is_bid_ride',0)
                    ->where('is_completed', 0)->where('is_cancelled', 0)->where('is_driver_started', 0)->get();
        // dd($current_time);

        // dd($sub_5_min);

        if ($requests->count()==0) {
            return $this->info('no-regular-rides-found');
        }

        // dd(DB::getQueryLog());
        foreach ($requests as $key => $request) {
            // Check if the request has any meta drivers
            if ($request->requestMeta()->exists()) {
                break;
            }
            // Get Drivers
            $pick_lat = $request->pick_lat;
            $pick_lng = $request->pick_lng;
            $type_id = $request->zoneType->type_id;


            $driver_search_radius = get_settings('driver_search_radius')?:30;

            $haversine = "(6371 * acos(cos(radians($pick_lat)) * cos(radians(pick_lat)) * cos(radians(pick_lng) - radians($pick_lng)) + sin(radians($pick_lat)) * sin(radians(pick_lat))))";
            // Get Drivers who are all going to accept or reject the some request that nears the user's current location.
            $meta_drivers = RequestMeta::whereHas('request.requestPlace', function ($query) use ($haversine,$driver_search_radius) {
                $query->select('request_places.*')->selectRaw("{$haversine} AS distance")
                ->whereRaw("{$haversine} < ?", [$driver_search_radius]);
            })->pluck('driver_id')->toArray();

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

            }elseif(array_key_exists('vehicle_types',$fire_driver) && is_array($fire_driver['vehicle_types']) && in_array($vehicle_type, $fire_driver['vehicle_types']) && $fire_driver['is_active']==1 && $fire_driver['is_available']==1 && $conditional_timestamp < $driver_updated_at){

                $distance = distance_between_two_coordinates($pick_lat,$pick_lng,$fire_driver['l'][0],$fire_driver['l'][1],'K');

               if($distance <= $driver_search_radius){

                    $firebase_drivers[$fire_driver['id']]['distance']= $distance;

                }


            }

        }

        asort($firebase_drivers);

            if (!empty($firebase_drivers)) {

                $nearest_driver_ids = [];

                foreach ($firebase_drivers as $key => $firebase_driver) {

                    $nearest_driver_ids[]=$key;
                }

                    // Already rejected drivers
                    $rejected_drivers = DriverRejectedRequest::where('request_id',$request->id)->pluck('driver_id')->toArray();

                    $nearest_drivers = Driver::where('active', 1)->where('approve', 1)->where('available', 1)->where('vehicle_type', $type_id)->where(function($query)use($request){
                    $query->where('transport_type',$request->transport_type)->orWhere('transport_type','both');
                })->whereIn('id', $nearest_driver_ids)->whereNotIn('id', $meta_drivers)->whereNotIn('id',$rejected_drivers)->limit(10)->get();


                $has_enabled_my_route_drivers=$nearest_drivers->where('enable_my_route_booking',1)->first();

                $route_coordinates=null;

                if($has_enabled_my_route_drivers){

                    //get line string from helper
                    $route_coordinates = get_line_string($request->pick_lat, $request->pick_lng, $request->drop_lat, $request->drop_lng);

                }

                    if ($nearest_drivers->isEmpty()) {
                        $this->info('no-drivers-available');
                        // @TODO Update attempt to the requests
                        $request->attempt_for_schedule += 1;
                        $request->save();
                        if ($request->attempt_for_schedule>5) {
                            $no_driver_request_ids = [];
                            $no_driver_request_ids[0] = $request->id;

                            $this->database->getReference('requests/'.$request->id)->update(['no_driver'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

                            $this->$database->getReference('request-meta/'.$request->id)->remove();


                            dispatch(new NoDriverFoundNotifyJob($no_driver_request_ids));
                        }
                    } else {


                         foreach ($nearest_drivers as $key => $nearest_driver) {

                        if($nearest_driver->enable_my_route_booking && $has_enabled_my_route_drivers!=null &$route_coordinates!=null){

                            $enabled_route_matched = $nearest_driver->intersects('route_coordinates',$route_coordinates)->first();

                            if(!$enabled_route_matched){

                                $nearest_drivers->forget($key);
                            }

                            $current_location_of_driver = $nearest_driver->enabledRoutes()->whereDate('created_at',$current_date)->orderBy('created_at','desc')->first();

                            $distance_between_current_location_to_drop = distance_between_two_coordinates($current_location_of_driver->current_lat, $current_location_of_driver->current_lng, $request->drop_lat, $request->drop_lng,'K');

                            $distance_between_current_location_to_my_route = distance_between_two_coordinates($current_location_of_driver->current_lat, $current_location_of_driver->current_lng, $nearest_driver->my_route_lat, $nearest_driver->my_route_lng,'K');

                            // Difference between both of above values

                            $difference = $distance_between_current_location_to_drop - $distance_between_current_location_to_my_route;

                            $difference=$difference < 0 ? (-1) * $difference : $difference;

                            if($difference>5){

                                $nearest_drivers->forget($key);

                            }

                        }

                    }

                    if ($nearest_drivers->isEmpty()) {
                        $this->info('no-drivers-available');
                        // @TODO Update attempt to the requests
                        $request->attempt_for_schedule += 1;
                        $request->save();
                        if ($request->attempt_for_schedule>5) {
                            $no_driver_request_ids = [];
                            $no_driver_request_ids[0] = $request->id;

                            $this->database->getReference('requests/'.$request->id)->update(['no_driver'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

                            $this->database->getReference('request-meta/'.$request->id)->remove();


                            dispatch(new NoDriverFoundNotifyJob($no_driver_request_ids));
                        }
                    }else{

                       $selected_drivers = [];
                        $i = 0;
                        foreach ($nearest_drivers as $driver) {

                            foreach ($firebase_drivers as $key => $firebase_driver) {

                            if($driver->id==$key){
                                    $selected_drivers[$i]["distance_to_pickup"] = $firebase_driver['distance'];
                                }
                            }

                            $selected_drivers[$i]["user_id"] = $request->user_id;
                            $selected_drivers[$i]["driver_id"] = $driver->id;
                            $selected_drivers[$i]["active"]= 0;
                            $selected_drivers[$i]["assign_method"] = 1;
                            $selected_drivers[$i]["created_at"] = date('Y-m-d H:i:s');
                            $selected_drivers[$i]["updated_at"] = date('Y-m-d H:i:s');

                             if(get_settings('trip_dispatch_type')==0){

                            $selected_drivers[$i]["active"] = 1;

                                // Add first Driver into Firebase Request Meta
                        $this->database->getReference('request-meta/'.$request->id)->set(['driver_id'=>$driver->id,'request_id'=>$request->id,'user_id'=>$request->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

                        $driver = Driver::find($driver->id);

                        $notifable_driver = $driver->user;

                        $title = trans('push_notifications.new_request_title');
                        $body = trans('push_notifications.new_request_body');

                        dispatch(new SendPushNotification($notifable_driver,$title,$body));


                             }

                            $i++;
                        }

                        if(get_settings('trip_dispatch_type')==0){

                            goto create_meta_request;
                        }

                        usort($selected_drivers, function($a, $b) {

                        return $a['distance_to_pickup'] <=> $b['distance_to_pickup'];

                        });
                        // Send notification to the very first driver
                        $first_meta_driver = $selected_drivers[0]['driver_id'];
                        $selected_drivers[0]["active"] = 1;

                        // Add first Driver into Firebase Request Meta
                        $this->database->getReference('request-meta/'.$request->id)->set(['driver_id'=>$first_meta_driver,'request_id'=>$request->id,'user_id'=>$request->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

                        $request_result =  fractal($request, new CronTripRequestTransformer)->parseIncludes('userDetail');
                        $pus_request_detail = $request_result->toJson();
                        $push_data = ['notification_enum'=>PushEnums::REQUEST_CREATED,'result'=>(string)$pus_request_detail];


                        $socket_data = new \stdClass();
                        $socket_data->success = true;
                        $socket_data->success_message  = PushEnums::REQUEST_CREATED;
                        $socket_data->result = $request_result;

                        $driver = Driver::find($first_meta_driver);

                        $notifable_driver = $driver->user;

                        $title = trans('push_notifications.new_request_title');
                        $body = trans('push_notifications.new_request_body');

                        dispatch(new SendPushNotification($notifable_driver,$title,$body));


                        // dispatch(new NotifyViaMqtt('create_request_'.$driver->id, json_encode($socket_data), $driver->id));
                        create_meta_request:

                        foreach ($selected_drivers as $key => $selected_driver) {
                            $request->requestMeta()->create($selected_driver);
                        }
                    }

                    }

            } else {
                $this->info('no-drivers-available');
                    $request->attempt_for_schedule += 1;
                    $request->save();
                    if ($request->attempt_for_schedule>5) {
                        $no_driver_request_ids = [];
                        $no_driver_request_ids[0] = $request->id;
                        dispatch(new NoDriverFoundNotifyJob($no_driver_request_ids));
                    }
            }
        }

        $this->info('success');
    }
}
