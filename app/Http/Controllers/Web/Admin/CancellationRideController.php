<?php

namespace App\Http\Controllers\Web\Admin;

use App\Base\Filters\Admin\RequestFilter;
use App\Base\Filters\Admin\RequestCancellationFilter;
use App\Base\Filters\Master\CommonMasterFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Http\Controllers\Controller;
use App\Models\Request\Request as RequestRequest;
use App\Models\Request\RequestCancellationFee;
use App\Models\Admin\CancellationReason;
use Illuminate\Http\Request;
use App\Base\Constants\Setting\Settings;

class CancellationRideController extends Controller
{
    public function index()
    {
        $page = trans('pages_names.cancellation_rides');
        $main_menu = 'trip-request';
        $sub_menu = 'cancellation-rides';

        return view('admin.cancellation-rides.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function indexDelivery()
    {
        $page = trans('pages_names.cancellation_delivery_rides');
        $main_menu = 'delivery-trip-request';
        $sub_menu = 'cancellation-rides';

        return view('admin.cancellation-delivery-rides.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function getAllRides(QueryFilterContract $queryFilter)
    {
        // $query = RequestCancellationFee::query();

        $query = RequestCancellationFee::whereNotNull('request_id')->whereHas('requestDetail', function ($query) {
            $query->where('transport_type' , 'taxi');
        })->orderBy('created_at', 'desc');

        $results = $queryFilter->builder($query)->customFilter(new RequestCancellationFilter)->defaultSort('-created_at')->paginate();


        return view('admin.cancellation-rides._rides', compact('results'));
    }

    public function getAllDeliveryRides(QueryFilterContract $queryFilter)
    {

        // $cancelled_rides = RequestCancellationFee::all();

        // foreach($cancelled_rides as $cancelled_ride)
        // {
        //     $results = $cancelled_ride->requestDetail;

        // }

        $query = RequestCancellationFee::whereNotNull('request_id')->whereHas('requestDetail', function ($query) {
            $query->where('transport_type' , 'delivery');
        })->orderBy('created_at', 'desc');
        
        $results = $queryFilter->builder($query)->customFilter(new RequestCancellationFilter)->defaultSort('-created_at')->paginate();


        return view('admin.cancellation-delivery-rides._rides', compact('results'));

        // $query = RequestCancellationFee::where('request_id');
        // $query = RequestRequest::where('transport_type','delivery');
        // $query = Driver::where('approve', true)->where('owner_id', null)->orderBy('created_at', 'desc');


    }

   
     
}
