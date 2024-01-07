<?php

namespace App\Transformers\User;

use App\Models\User;
use App\Base\Constants\Auth\Role;
use App\Transformers\Transformer;
use App\Transformers\Access\RoleTransformer;
use App\Transformers\Requests\TripRequestTransformer;
use App\Models\Admin\Sos;
use App\Transformers\Common\SosTransformer;
use App\Transformers\User\FavouriteLocationsTransformer;
use App\Base\Constants\Setting\Settings;
use Carbon\Carbon;
use App\Models\Admin\UserDriverNotification;
use App\Transformers\Common\BannerImageTransformer;
use App\Models\Master\BannerImage;

class UserTransformer extends Transformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected array $availableIncludes = [
        'roles','onTripRequest','metaRequest','favouriteLocations','laterMetaRequest',
    ];
    /**
     * Resources that can be included default.
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'sos','bannerImage'

    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        $params = [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'mobile' => $user->countryDetail->dial_code.$user->mobile,
            'profile_picture' => $user->profile_picture,
            'active' => $user->active,
            'email_confirmed' => $user->email_confirmed,
            'mobile_confirmed' => $user->mobile_confirmed,
            'last_known_ip' => $user->last_known_ip,
            'last_login_at' => $user->last_login_at,
            'rating' => round($user->rating, 2),
            'no_of_ratings' => $user->no_of_ratings,
            'refferal_code'=>$user->refferal_code,
            'currency_code'=>$user->countryDetail->currency_code,
            'currency_symbol'=>$user->countryDetail->currency_symbol,
            'country_code'=>$user->countryDetail->code,
            //'map_key'=>get_settings('google_map_key'),
            'show_rental_ride'=>true,
            'show_ride_later_feature'=>true,
            // 'created_at' => $user->converted_created_at->toDateTimeString(),
            // 'updated_at' => $user->converted_updated_at->toDateTimeString(),
        ];

        $params['enable_modules_for_applications'] =  get_settings('enable_modules_for_applications');

        $params['contact_us_mobile1'] =  get_settings('contact_us_mobile1');
        $params['contact_us_mobile2'] =  get_settings('contact_us_mobile2');
        $params['contact_us_link'] =  get_settings('contact_us_link');
        $params['show_wallet_feature_on_mobile_app'] =  get_settings('show_wallet_feature_on_mobile_app');
        $params['show_bank_info_feature_on_mobile_app'] =  get_settings('show_bank_info_feature_on_mobile_app');

        $notifications_count= UserDriverNotification::where('user_id',$user->id)
            ->where('is_read',0)->count();
        $params['notifications_count']=$notifications_count;

        if(get_settings('show_rental_ride_feature')=='0'){
            $params['show_rental_ride'] = false;
        }

        if(get_settings('show_ride_later_feature')=='0'){
            $params['show_ride_later_feature'] = false;
        }

        $referral_comission = get_settings('referral_commision_for_user');
        $referral_comission_string = 'Refer a friend and earn'.$user->countryDetail->currency_symbol.''.$referral_comission;
        $params['referral_comission_string'] = $referral_comission_string;

        $params['user_can_make_a_ride_after_x_miniutes'] = get_settings(Settings::USER_CAN_MAKE_A_RIDE_AFTER_X_MINIUTES);

        $params['maximum_time_for_find_drivers_for_regular_ride'] = (get_settings(Settings::MAXIMUM_TIME_FOR_FIND_DRIVERS_FOR_REGULAR_RIDE) * 60);

         $params['maximum_time_for_find_drivers_for_bitting_ride'] = (get_settings(Settings::MAXIMUM_TIME_FOR_FIND_DRIVERS_FOR_BITTING_RIDE) * 60);

        $params['show_ride_without_destination'] = (get_settings(Settings::SHOW_RIDE_WITHOUT_DESTINATION));

        return $params;
    }

    /**
     * Include the roles of the user.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeRoles(User $user)
    {
        $roles = $user->roles;

        return $roles
        ? $this->collection($roles, new RoleTransformer)
        : $this->null();
    }
    /**
     * Include the request of the user.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeOnTripRequest(User $user)
    {
        $now = Carbon::now()->toDateTimeString();
        $request = $user->requestDetail()->where('is_cancelled', false)->where('user_rated', false)->where('driver_id', '!=', null)
        ->where(function ($query) use ($now) {
            $query->where('trip_start_time', '<=', $now);
            $query->orWhere('trip_start_time', null);
        })->first();

        return $request
        ? $this->item($request, new TripRequestTransformer)
        : $this->null();
    }
    /**
    * Include the request meta of the user.
    *
    * @param User $user
    * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
    */
    public function includeMetaRequest(User $user)
    {
        $request = $user->requestDetail()->where('is_completed', false)->where('is_cancelled', false)->where('user_rated', false)->where('driver_id', null)->first();

        return $request
        ? $this->item($request, new TripRequestTransformer)
        : $this->null();
    }

    /**
    * Include the request meta of the user.
    *
    * @param User $user
    * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
    */
    public function includeLaterMetaRequest(User $user)
    {
        //لم تكن تظهر الرحلة بسبب الفورمات الخطا من التطبيق فلم يكن يعرف موعد اطلاق الرحلة
        $current_date = Carbon::now()->format('Y-m-d H:i:s');

        $findable_duration = get_settings('minimum_time_for_search_drivers_for_schedule_ride');
        if(!$findable_duration){
            $findable_duration = 45;
        }
        $add_45_min = Carbon::now()->addMinutes($findable_duration)->format('Y-m-d H:i:s');

// تم حذف بعض الكوندشن لكي تظهر الداتا
/* الكوندشن القديم

        $request = $user->requestDetail()->where('is_completed', false)->where('is_cancelled', false)->where('user_rated', false)->where('is_later',true)->where('driver_id', null)->where('is_later', 0)->where('trip_start_time', '<=', $add_45_min)
                    ->where('trip_start_time', '>', $current_date)->first();

*/
         $request = $user->requestDetail()->where('is_completed', false)->where('is_cancelled', false)->where('user_rated', false)->where('is_later',true)->where('driver_id', null)->where('is_later', 1)->where('trip_start_time', '<=', $add_45_min)->first();

        return $request
        ? $this->item($request, new TripRequestTransformer)
        : $this->null();
    }

     /**
    * Include the request meta of the user.
    *
    * @param User $user
    * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
    */
    public function includeSos(User $user)
    {
        $request = Sos::select('id', 'name', 'number', 'user_type', 'created_by')
        ->where('created_by', auth()->user()->id)
        ->orWhere('user_type', 'admin')
        ->orderBy('created_at', 'Desc')
        ->companyKey()->get();

        return $request
        ? $this->collection($request, new SosTransformer)
        : $this->null();
    }

    /**
     * Include the favourite location of the user.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeFavouriteLocations(User $user)
    {
        $fav_locations = $user->favouriteLocations;

        return $fav_locations
        ? $this->collection($fav_locations, new FavouriteLocationsTransformer)
        : $this->null();
    }

    /**
     * Include the Banner image of the user.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeBannerImage()
    {
        $banner_image = BannerImage::where('active', true)->get();

        return $banner_image
        ? $this->collection($banner_image, new BannerImageTransformer)
        : $this->null();
    }



}
