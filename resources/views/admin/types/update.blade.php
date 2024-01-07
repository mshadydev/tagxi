@extends('admin.layouts.app')


@section('title', 'Main page')

<!-- Bootstrap fileupload css -->
@section('content')

    <!-- Start Page content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-sm-12">
                    <div class="box">

                        <div class="box-header with-border">
                            <a href="{{ url('types') }}">
                                <button class="btn btn-danger btn-sm pull-right" type="submit">
                                    <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                    @lang('view_pages.back')
                                </button>
                            </a>
                        </div>

                        <div class="col-sm-12">
                            <form method="post" class="form-horizontal" action="{{url('types/update',$type->id)}}"
                                  enctype="multipart/form-data">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="">@lang('view_pages.transport_type') <span
                                                    class="text-danger">*</span></label>
                                            <select name="transport_type" id="transport_type" class="form-control"
                                                    required>
                                                <option value="" selected disabled>@lang('view_pages.select')</option>
                                                <option
                                                    value="taxi" {{ old('transport_type',$type->is_taxi) == 'taxi' ? 'selected' : '' }}>@lang('view_pages.taxi')</option>
                                                <option
                                                    value="delivery" {{ old('transport_type',$type->is_taxi) == 'delivery' ? 'selected' : '' }}>@lang('view_pages.delivery')</option>
                                                <option
                                                    value="both" {{ old('transport_type',$type->is_taxi) == 'both' ? 'selected' : '' }}>@lang('view_pages.both')</option>
                                                {{--                                                @foreach($vehicle_type as $types)--}}
                                                {{--                                                    <option value="{{$types}}" {{old("vehicle_type")=="$types"?'selected':''}}></option>--}}
                                                {{--                                                @endforeach--}}
                                                {{--                                                @if(old('transport_type',$type->is_taxi))--}}
                                                {{--                                                    <option value="" selected disabled>@lang('view_pages.select')</option>--}}
                                                {{--                                                    <option--}}
                                                {{--                                                        value="taxi"{{$type->is_taxi ? 'selected' : '' }} >@lang('view_pages.taxi')</option>--}}
                                                {{--                                                    <option--}}
                                                {{--                                                        value="delivery"{{ $type->is_taxi ? 'selected' : '' }} >@lang('view_pages.delivery')</option>--}}
                                                {{--                                                @endif--}}

                                            </select>
                                            <span class="text-danger">{{ $errors->first('transport_type') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group m-b-25">
                                            <label for="name">@lang('view_pages.name') <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="name" name="name"
                                                   value="{{old('name', $type->name)}}" required=""
                                                   placeholder="@lang('view_pages.enter_name')">
                                            <span class="text-danger">{{ $errors->first('name') }}</span>

                                        </div>
                                    </div>
                                    <div class="col-6" name="taxi" id="taxi">
                                        <div class="form-group m-b-25">
                                            <label for="name">@lang('view_pages.capacity') <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="number" id="capacity" name="capacity"
                                                   value="{{old('capacity',$type->capacity)}}" required=""
                                                   placeholder="@lang('view_pages.enter_capacity')" min="1">
                                            <span class="text-danger">{{ $errors->first('capacity') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6" name="delivery" id="delivery">
                                        <div class="form-group m-b-25">
                                            <label
                                                for="maximum_weight_can_carry">@lang('view_pages.maximum_weight_can_carry')
                                                <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="maximum_weight_can_carry"
                                                   name="maximum_weight_can_carry"
                                                   value="{{old('capacity',$type->capacity)}}" required=""
                                                   placeholder="@lang('view_pages.enter_maximum_weight_can_carry')"
                                                   min="1">
                                            <span class="text-danger">{{ $errors->first('capacity') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6" name="delivery_size" id="delivery_size">
                                        <div class="form-group m-b-25">
                                            <label for="name">@lang('view_pages.size') <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="size" name="size"
                                                   value="{{old('size', $type->size)}}"
                                                   placeholder="@lang('view_pages.enter_size')" min="1">
                                            <span class="text-danger">{{ $errors->first('size') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group m-b-25">
                                            <label for="short_description">@lang('view_pages.short_description') <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="name" name="short_description"
                                                   value="{{old('short_description',$type->short_description)}}"
                                                   required=""
                                                   placeholder="@lang('view_pages.enter_short_description')">
                                            <span class="text-danger">{{ $errors->first('short_description') }}</span>

                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group m-b-25">
                                            <label for="description">@lang('view_pages.description') <span
                                                    class="text-danger">*</span></label>
                                            <textarea type="text" name="description" id="description"
                                                      value="{{old('description',$type->description)}}"
                                                      class="form-control"
                                                      placeholder="@lang('view_pages.enter_description')">{{old('description',$type->description)}}</textarea>

                                            <span class="text-danger">{{ $errors->first('description') }}</span>

                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group m-b-25">
                                            <label for="supported_vehicles">@lang('view_pages.supported_vehicles') <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="supported_vehicles" id="supported_vehicles"
                                                      value="{{old('supported_vehicles',$type->supported_vehicles)}}"
                                                      class="form-control"
                                                      placeholder="Example: Toyato,Audi,Acura">{{old('supported_vehicles',$type->supported_vehicles)}}</textarea>

                                            <span class="text-danger">{{ $errors->first('supported_vehicles') }}</span>

                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="">@lang('view_pages.icon_types_for') <span
                                                    class="text-danger">*</span></label>
                                            <select name="icon_types_for" id="icon_types_for" class="form-control"
                                                    required>
                                                <option value="" selected disabled>@lang('view_pages.select')</option>
                                                <option
                                                    value="taxi" {{ old('icon_types_for', $type->icon_types_for) == 'taxi' ? 'selected' : '' }}>@lang('view_pages.taxi')</option>
                                                 <option
                                                    value="auto" {{ old('icon_types_for', $type->icon_types_for) == 'auto' ? 'selected' : '' }}>@lang('view_pages.auto')</option>    
                                                <option
                                                    value="truck" {{ old('icon_types_for', $type->icon_types_for) == 'truck' ? 'selected' : '' }}>@lang('view_pages.truck')</option>
                                                    <option
                                                    value="motor_bike" {{ old('icon_types_for', $type->icon_types_for) == 'motor_bike' ? 'selected' : '' }}>@lang('view_pages.motor_bike')</option>

                                            </select>
                                            <span class="text-danger">{{ $errors->first('icon_types_for') }}</span>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="">@lang('view_pages.trip_dispatch_type') <span
                                                    class="text-danger">*</span></label>
                                            <select name="trip_dispatch_type" id="trip_dispatch_type" class="form-control"
                                                    required>
                                                <option value="" selected disabled>@lang('view_pages.select')</option>
                                                <option
                                                    value="bidding" {{ old('trip_dispatch_type', $type->trip_dispatch_type) == 'bidding' ? 'selected' : '' }}>@lang('view_pages.bidding')</option>
                                                <option
                                                    value="normal" {{ old('trip_dispatch_type', $type->trip_dispatch_type) == 'normal' ? 'selected' : '' }}>@lang('view_pages.normal')</option>
                                                 </select>
                                            <span class="text-danger">{{ $errors->first('trip_dispatch_type') }}</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="col-6">
                                        <label for="icon">@lang('view_pages.icon')</label><br>
                                        <img id="blah" src="{{old('icon',asset($type->icon))}}" alt="missing image"><br>
                                        <input type="file" id="icon" onchange="readURL(this)" name="icon"
                                               style="display:none">
                                        <button class="btn btn-primary btn-sm" type="button"
                                                onclick="$('#icon').click()"
                                                id="upload">@lang('view_pages.browse')</button>
                                        <button class="btn btn-danger btn-sm" type="button" id="remove_img"
                                                style="display: none;">@lang('view_pages.remove')</button>
                                        <br>
                                        <span class="text-danger">{{ $errors->first('icon') }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-12">
                                        <button class="btn btn-primary btn-sm m-5 pull-right" type="submit">
                                            @lang('view_pages.save')
                                        </button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>


                </div>
            </div>
        </div>

    </div>
    <!-- container -->

    <!-- Laravel Javascript Validation -->
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Admin\VehicleTypes\CreateVehicleTypeRequest','#type-form') !!}

    <!-- Bootstrap fileupload js -->
    <script>
        $(document).ready(function () {
            // console.log(document.getElementById("transport_type").value);

            var transport_type =document.getElementById("transport_type").value;

            // alert(transport_type);

            if (transport_type == 'taxi') {
                        $("#taxi").show();
                        $("#delivery").hide();
                        $("#delivery_size").hide();

                    } else if (transport_type == 'delivery') {
                        $("#taxi").hide();
                        $("#delivery").show();
                        $("#delivery_size").show();
                    } else if (transport_type == 'both') {
                        $("#taxi").show();
                        $("#delivery").show();
                        $("#delivery_size").show();
                    }
                     else {
                        $("#taxi").hide();
                        $("#delivery").hide();
                        $("#delivery_size").hide();
                    }

            $('#transport_type').on('change', function () {
                if (this.value == 'taxi') {
                    $("#taxi").show();
                    $("#delivery").hide();
                    $("#delivery_size").hide();

                } else if (this.value == 'delivery') {
                    $("#taxi").hide();
                    $("#delivery").show();
                    $("#delivery_size").show();
                } else if (this.value == 'both') {
                    $("#taxi").show();
                    $("#delivery").show();
                    $("#delivery_size").show();
                }
                 else {
                    $("#taxi").hide();
                    $("#delivery").hide();
                    $("#delivery_size").hide();
                }

            });
        });

    </script>

@endsection

