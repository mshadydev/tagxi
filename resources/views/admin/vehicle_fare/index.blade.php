@extends('admin.layouts.app')
@section('title', 'Main page')
@section('content')

    <!-- Start Page content -->
    <section class="content">
        {{-- <div class="container-fluid"> --}}


        <div class="row">
            <div class="col-12">
                <div class="box">

                    <div class="box-header with-border">
                        <div class="row text-right">
                         <div class="col-8 col-md-3">
                                <div class="form-group">
                                    <div class="controls">
                                        <input type="text" id="search_keyword" name="search" class="form-control"
                                            placeholder="@lang('view_pages.enter_keyword')">
                                    </div>
                                </div>
                            </div>

                            <div class="col-4 col-md-2 text-left">
                                <button id="search" class="btn btn-success btn-outline btn-sm py-2" type="submit">
                                    @lang('view_pages.search')
                                </button>
                            </div> 


                                <div class="col-md-12 text-center text-md-right">
                                    <a href="{{ url('vehicle_fare/create') }}" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-plus-circle mr-2"></i>@lang('view_pages.add_fare_types')</a>
                                    <!--  <a class="btn btn-danger">
                                                Export</a> -->
                                </div>
                            <!-- </form> -->
                        </div>

                    </div>
                    <div id="js-vehicle-fare-partial-target">
                        <include-fragment src="vehicle_fare/fetch">
                            <span style="text-align: center;font-weight: bold;">@lang('view_pages.loading')</span>
                        </include-fragment>
                    </div>
        <div class="modal fade" id="show-price-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title price-modal-title" id="staticBackdropLabel"><strong></h5>
                        <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                    </div>
        
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">{{ __('view_pages.base_price') }}</th>
                                        <th class="text-nowrap">{{ __('view_pages.base_distance') }}</th>
                                        <th class="text-nowrap">{{ __('view_pages.price_per_distance') }}</th>
                                        {{-- <th class="text-nowrap">{{ __('view_pages.waiting_charge') }}</th> --}}
                                        <th class="text-nowrap">{{ __('view_pages.price_per_time') }}</th>
                                        <th class="text-nowrap">{{ __('view_pages.cancellation_fee') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="base_price" name="base_price" class="text-nowrap"></td>
                                        <td id="base_distance" name="base_distance" class="text-nowrap"></td>
                                        <td id="price_per_distance" name="price_per_distance" class="text-nowrap"></td>
                                        {{-- <td id="waiting_charge" name="waiting_charge" class="text-nowrap"></td> --}}
                                        <td id="price_per_time" name="price_per_time" class="text-nowrap"></td>
                                        <td id="cancellation_fee" name="cancellation_fee" class="text-nowrap"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>    
    </div>

        {{-- </div> --}}
        <!-- container -->

    </section>
 <script src="{{asset('assets/js/fetchdata.min.js')}}"></script>
<script>
    var search_keyword = '';
    $(function() {
        $('body').on('click', '.pagination a', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $.get(url, $('#search').serialize(), function(data) {
                $('#js-vehicle-fare-partial-target').html(data);
            });
        });

        $('#search').on('click', function(e) {
            e.preventDefault();
            search_keyword = $('#search_keyword').val();
            fetchVehicleFare(search_keyword);
        });

        $('#reset').on('click', function(e) {
            e.preventDefault();
            search_keyword = $('#search_keyword').val('');
            fetchVehicleFare();
        });

        function fetchVehicleFare(search_keyword = ''){
            fetch('vehicle_fare/fetch?search=' + search_keyword)
            .then(response => response.text())
            .then(html => {
                document.querySelector('#js-vehicle-fare-partial-target').innerHTML = html
            });
        }
    });
    $(document).on('click', '.sweet-delete', function(e) {
                e.preventDefault();

                let url = $(this).attr('data-url');


                swal({
                    title: "Are you sure to delete ?",
                    type: "error",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                    cancelButtonText: "No! Keep it",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        swal.close();

                        $.ajax({
                            url: url,
                            cache: false,
                            success: function(res) {

                                fetch('vehicle_fare/fetch?search=' + search_keyword)
                                    .then(response => response.text())
                                    .then(html => {
                                        document.querySelector('#js-vehicle-fare-partial-target')
                                            .innerHTML = html
                                    });

                                $.toast({
                                    heading: '',
                                    text: res,
                                    position: 'top-right',
                                    loaderBg: '#ff6849',
                                    icon: 'success',
                                    hideAfter: 5000,
                                    stack: 1
                                });
                            }
                        });
                    }
                });
            });


        $(document).on('click', '#show-price-modal', function(e) {
        e.preventDefault();
        let price = $(this).data('price');
        console.log(price);

        $('#base_price').text(price.base_price);
        $('#base_distance').text(price.base_distance);
        $('#price_per_distance').text(price.price_per_distance);
        $('#price_per_time').text(price.price_per_time);
        $('#cancellation_fee').text(price.cancellation_fee);
        $('.price-modal-title').html(`<strong>${price.zone_type.vehicle_type.name}</strong> - ${price.price_type == 1 ? 'Ride Now Price' : 'Ride Later Price'}`)
        $('#show-price-modal').modal('show');
    });

</script>

@endsection