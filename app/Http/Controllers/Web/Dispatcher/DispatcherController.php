<?php

namespace App\Http\Controllers\Web\Dispatcher;

use App\Base\Filters\Admin\RequestFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Http\Controllers\Web\BaseController;
use App\Models\Request\Request as RequestRequest;
use Illuminate\Http\Request;
use App\Base\Constants\Auth\Role;

class DispatcherController extends BaseController
{
    public function index()
    {
        $main_menu = 'dispatch_request';

        $sub_menu = null;

        $page = 'Dispatch Requests';

        return view('admin.dispatcher.requests', compact(['main_menu','sub_menu','page']));
    }

    public function dispatchView(){
        $main_menu = 'dispatch_request';

        $sub_menu = null;

        $page = 'Dispatch Requests';

        $default_lat = get_settings('default_latitude');
        $default_lng = get_settings('default_longitude');

        return view('admin.dispatcher.dispatch', compact(['main_menu','sub_menu','page', 'default_lat', 'default_lng']));
    }

    public function bookNow()
    {
         $main_menu = 'dispatch_request';

        $sub_menu = null;
       
        $default_lat = get_settings('default_latitude');
        $default_lng = get_settings('default_longitude');

        return view('dispatch.new-ui.book-now')->with(compact('main_menu','sub_menu','default_lat', 'default_lng'));
    }

    public function bookNowDelivery()
    {
         $main_menu = 'dispatch_request';

        $sub_menu = null;
       
        $default_lat = get_settings('default_latitude');
        $default_lng = get_settings('default_longitude');

        return view('dispatch.new-ui.book-now-delivery')->with(compact('main_menu','sub_menu','default_lat', 'default_lng'));
    }


    /**
    *
    * create new request
    */
    public function createRequest(Request $request)
    {
        dd($request->all());
    }

    public function loginView(){
        return view('admin.dispatch-login');
    }

    public function dashboard()
    {
        
        if (access()->hasRole(Role::DISPATCHER)) {
             return view('dispatch.home');
        } else {
        
        return view('dispatch-delivery.home');

        }
    }

    public function fetchBookingScreen($modal){
        return view("dispatch.$modal");
    }

    public function fetchRequestLists(QueryFilterContract $queryFilter){

        $query = RequestRequest::where('transport_type', 'taxi');

        $results = $queryFilter->builder($query)->customFilter(new RequestFilter)->paginate();

        return view('dispatch.request-list', compact('results'));
    }

    public function profile(){
        return view('dispatch.profile');
    }

     public function fetchSingleRequest(RequestRequest $requestmodel){
        return $requestmodel;
    }

     public function requestDetailedView(RequestRequest $requestmodel){
        $item = $requestmodel;
        
        return view('dispatch.request_detail',compact('item'));
    }
}
