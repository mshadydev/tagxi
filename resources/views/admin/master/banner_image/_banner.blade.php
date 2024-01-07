<div class="box-body no-padding">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>


                <th> @lang('view_pages.s_no')
                    <span style="float: right;">

                    </span>
                </th>
                <th> @lang('view_pages.icon')
                    <span style="float: right;">
                    </span>
                </th>
                <th> @lang('view_pages.status')
                    <span style="float: right;">
                    </span>
                </th>
                <th> @lang('view_pages.action')
                    <span style="float: right;">
                    </span>
                </th>
            </tr>
            </thead>
            <tbody>
            @if(count($results)<1)
                <tr>
                    <td colspan="11">
                        <p id="no_data" class="lead no-data text-center">
                            <img src="{{asset('assets/img/dark-data.svg')}}"
                                 style="width:150px;margin-top:25px;margin-bottom:25px;" alt="">
                        <h4 class="text-center"
                            style="color:#333;font-size:25px;">@lang('view_pages.no_data_found')</h4>
                        </p>
                    </td>
                </tr>
            @else

                @php  $i= $results->firstItem(); @endphp

                @foreach($results as $key => $result)

                    <tr>
                        <td>{{ $i++ }} </td>

                        <td><img class="img-circle" src="{{asset($result->image)}}" alt=""></td>
                        @if($result->active)
                            <td>
                                <button class="btn btn-success btn-sm">@lang('view_pages.active')</button>
                            </td>
                        @else
                            <td>
                                <button class="btn btn-danger btn-sm">@lang('view_pages.inactive')</button>
                            </td>
                        @endif
                        @if(!auth()->user()->company_key)
                            <td>

                                <button type="button" class="btn btn-info dropdown-toggle btn-sm" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">@lang('view_pages.action')
                                </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 35px, 0px); top: 0px; left: 0px; will-change: transform;">
                                    <a class="dropdown-item" href="{{url('banner_image/edit',$result->id)}}"><i class="fa fa-pencil"></i>@lang('view_pages.edit')</a>
                                    @if(auth()->user()->can('toggle-vehicle-types'))            
                                        @if($result->active)
                                            <a class="dropdown-item" href="{{url('banner_image/toggle_status',$result->id)}}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.inactive')</a>
                                        @else
                                            <a class="dropdown-item" href="{{url('banner_image/toggle_status',$result->id)}}">
                                            <i class="fa fa-dot-circle-o"></i>@lang('view_pages.active')</a>
                                        @endif
                                    @endif
                                    @if(auth()->user()->can('delete-vehicle-banner_image'))            
                                       <a class="dropdown-item sweet-delete" href="#" data-url="{{url('banner_image/delete',$result->id)}}"><i class="fa fa-trash-o"></i>@lang('view_pages.delete')</a>  
                                    @endif
                                  
                                    </div>

                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
        <div class="text-right">
            <span style="float:right">
                {{$results->links()}}
            </span>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>


