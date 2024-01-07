@extends('admin.layouts.app')
@section('title', 'Main page')

@section('content')
{{-- {{session()->get('errors')}} --}}

    <!-- Start Page content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <a href="{{ url('carmake') }}">
                                <button class="btn btn-danger btn-sm pull-right" type="submit">
                                    <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                    @lang('view_pages.back')
                                </button>
                            </a>
                        </div>

                        <div class="col-sm-12">

                           <form  method="post" class="form-horizontal" action="{{ route('servicepageadd') }}" enctype="multipart/form-data">
                                @csrf
                            <div class="form-group">
                                <label><strong>Servicearea Header Text :</strong></label>
                                <textarea class="ckeditor form-control" name="serviceheadtext">@if($data) {{ $data->serviceheadtext }} @endif</textarea>
                            </div>
                            <div class="form-group">
                                <label><strong>Servicearea Sub Text :</strong></label>
                                <textarea class="ckeditor form-control" name="servicesubtext">@if($data) {{ $data->servicesubtext }} @endif</textarea>
                            </div>

                            <div class="form-group">
                                <div class="col-6">
                                    <label for="icon">Upload Servicearea Image 1 Size(359px × 359px)</label><br>
                                        @foreach ($serv as $key => $value)
                                          <img id="blah" src="{{ $p.$value }}" alt=""><br>
                                          @endforeach
                                    
                                    <input type="file" id="serviceasimage" multiple="multiple" onchange="readURL(this)" name="serviceimage[]" style="display:none">
                                    <button class="btn btn-primary btn-sm" type="button" onclick="$('#serviceasimage').click()" id="upload">Browse</button>
                                    <button class="btn btn-danger btn-sm" type="button" id="remove_img" style="display: none;">Remove</button><br>
                                    <span class="text-danger"> {{ $errors->first('serviceimage') }}  </span>
                                    </div>
                            </div>

                                <div class="form-group">
                                    <div class="col-12">
                                        <button class="btn btn-primary btn-sm pull-right m-5" type="submit">
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
</div>
    <!-- content -->
    <script src="//cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>

<script type="text/javascript">

    $(document).ready(function() {
       $('.ckeditor').ckeditor();
    });

</script>
@endsection
