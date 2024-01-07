<div class="box-body no-padding">
    <div class="table-responsive">
      <table class="table table-hover">
            <thead> 

                    <tr>
<th> @lang('view_pages.s_no')
<span style="float: right;">
</span>
</th>
<th> @lang('view_pages.zone_name')
<span style="float: right;">
</span>
</th><th> @lang('view_pages.transport_type')
<span style="float: right;">
</span>
</th><th> @lang('view_pages.vehicle_type')
<span style="float: right;">
</span>
</th><th> @lang('view_pages.price_type')
<span style="float: right;">
</span>
</th><th> @lang('view_pages.status')
<span style="float: right;">
</span>
</th><th> @lang('view_pages.action')
<span style="float: right;">
</span>
</th>
                </tr>
                </thead>
                <tbody>
                    @php  $i= $results->firstItem(); @endphp
                    @forelse ($results as $key => $result)

                            <td>{{ $i++ }}</td>
                            <td>{{ ($result->zoneType->zone->name) }}</td>
                            <td>{{ ($result->zoneType->transport_type) }}</td>
                            <td>{{ $result->zoneType->vehicleType->name }}
                            @if ($result->zoneType->zone->default_vehicle_type == $result->zoneType->vehicleType->id)
                            <button class="btn btn-warning btn-sm">Default</button>
					        @endif
                            @if ($result->zoneType->zone->default_vehicle_type_for_delivery == $result->zoneType->vehicleType->id)
                            <button class="btn btn-warning btn-sm">Default</button>
					        @endif
                            </td>
                            <td>
                                @if ($result->price_type == 1)
                                    <span class="btn btn-success btn-sm">{{ __('view_pages.ride_now') }}</span>
                                @else
                                    <span class="btn btn-danger btn-sm">{{ __('view_pages.ride_later') }}</span>
                                @endif
                            </td>
                                @if ($result->zoneType->active)
                            <td><button class="btn btn-success btn-sm">@lang('view_pages.active')</button></td>
                                @else
                            <td><button class="btn btn-danger btn-sm">@lang('view_pages.inactive')</button></td>
                                @endif
                           <td>
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@lang('view_pages.action')
                            </button>
                            <div class="dropdown-menu w-48 ">
                                <a class="dropdown-item" href="{{url('vehicle_fare/edit', $result->id)}}">
                                    <i class="fa fa-pencil"></i>@lang('view_pages.edit')
                                </a>
                                <a class="dropdown-item" href="{{url('vehicle_fare/rental_package/index', $result->zoneType->id)}}">
                                    <i class="fa fa-plus"></i>@lang('view_pages.assign_rental_package')
                                </a>
                                @if($result->zoneType->transport_type == 'taxi')
                                    @if ($result->active == 1 && $result->zoneType->zone->default_vehicle_type != $result->zoneType->vehicleType->id)
                                    <a class="dropdown-item" href="{{url('vehicle_fare/set/default',$result->id)}}"><i class="fa fa-dot-circle-o"></i>@lang('view_pages.set_as_default')</a>
                                    @endif  
                                @else  
                                    @if ($result->active == 1 && $result->zoneType->zone->default_vehicle_type_for_delivery !=  $result->zoneType->vehicleType->id)
                                    <a class="dropdown-item" href="{{url('vehicle_fare/set/default',$result->id)}}"><i class="fa fa-dot-circle-o"></i>@lang('view_pages.set_as_default')</a>
                                    @endif 
                                @endif       
                                @if($result->zoneType->active)
                                    <a class="dropdown-item" href="{{url('vehicle_fare/toggle_status', $result->id)}}">
                                    <i class="fa fa-dot-circle-o"></i>@lang('view_pages.inactive')</a>
                                @else
                                    <a class="dropdown-item" href="{{url('vehicle_fare/toggle_status', $result->id)}}">
                                    <i class="fa fa-dot-circle-o"></i>@lang('view_pages.active')</a>
                                @endif  
                                <a class="dropdown-item sweet-delete" href="#" data-url="{{url('vehicle_fare/delete',$result->id)}}">
                                 <i class="fa fa-trash-o"></i>@lang('view_pages.delete')</a>                                                              
                            </div>
                </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div id="no_data" class="lead no-data text-center">
                                    <img src="{{ asset('assets/img/dark-data.svg') }}">
                                    <h4 class="text-center">@lang('view_pages.no_data_found')</h4>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="intro-y g-col-12 d-flex flex-wrap flex-sm-row flex-sm-nowrap align-items-center">
    <nav class="w-full w-sm-auto me-sm-auto">
        <ul class="pagination">
            {{ $results->links('pagination::bootstrap-4') }}
        </ul>
    </nav>
</div>