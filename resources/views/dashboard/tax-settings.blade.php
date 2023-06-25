@extends('layouts.master')

@section('page-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.css">
    <link type="text/css" rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css"/>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .select2-container {
            width: 100% !important;
            padding: 0;
        }

        i {
            font-family: 'iconsmind' !important;
            speak: none;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-size: 16px;
            padding: 0;
            position: relative;
            top: 2px;
        }

        .category-tab {
            background: #EDF3F9;
            padding: 10px;
        }

        .row-border {
            border-top: 1px solid #a92219;
            top: 12px;
            position: relative;
            right: 16px;
            max-width: 1076px;
        }
        .remainingCharacter {
            position: absolute;
            right: 4.2rem;
            border: transparent;
            background: transparent;
            color: rgba(0,0,0,0.7);
        }
        .totalRemainingCharacter{
            position: absolute;
            right: 2rem;
            color: rgba(0,0,0,0.7);
        }
        select {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 48rem #f8f9fa !important;
        }
        .card {
            padding: 15px;
        }
    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Settings</h2>
            </div>
           {{-- <div class="col-md-6">
                <div style="margin: auto" id="select-restaurant">
                    <div class="row">
                        @if(Auth::guard('admin')->user()->type ==1)
                            <div class="col-md-5 text-right mt-2 p-0">
                                <h5>Change Location :</h5>
                            </div>
                        @else
                            <div class="col-md-5">
                                <h5 style="color: #ffffff">Location :</h5>
                            </div>
                        @endif
                        <div class="col-md-7" style="width: 430px">
                            @if(true)
                                <select class="form-control  selectpicker" id="my_restaurant" onchange="onRestaurantChange()">
                                    @foreach($header_restaurant as $item)
                                        <option
                                            value="{{$item->id}}"
                                            {{$item->id==Session::get('my_restaurant') ? 'selected':''}}>
                                            {{$item->address}}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-control  selectpicker" id="my_restaurant" disabled>
                                    @foreach($header_restaurant as $item)
                                        <option
                                            value="{{$item->id}}"
                                            {{$item->id==Session::get('my_restaurant') ? 'selected':''}}>
                                            {{$item->address}}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
            </div>--}}
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{route('tax-settings')}}">Global Settings</a>
        </li>
        {{--<li class="nav-item">
            <a class="nav-link" href="{{route('preparation-time')}}">Time Settings</a>
        </li>--}}
        <li class="nav-item">
            <a class="nav-link" href="{{route('setting-restaurants')}}">Location Settings</a>
        </li>
    </ul>
    <div class="col-md-12">
        <div class="row mt-4">
            <div class="col-md-6">
                <h1 class="heading-white mb-3">Global Settings</h1>
            </div>
            <div class="col-md-6"></div>
        </div>
    <div class="card p-2 mt-4">
        {{--<div class="col-md-6">
            <h1 class="heading-white mb-4">Global Settings</h1>
        </div>--}}
        <div class="col-md-12">
            <form name="bonusForm" onsubmit="applyBonus();return false;">
                    <div class="col-md-6 form-group mb-3">
                        <label for="firstName1">Global Tax Value</label>
                        <input class="form-control"
                               id="tax_value"
                               placeholder="Global Tax Value"
                               required
                               value="{{$data->tax_value}}"
                        >
                    </div>
{{--                    <div class="col-md-6 form-group mb-3">--}}
{{--                        <label for="firstName1">Pickup Notification Time in Minutes(X minutes before notification will be sent)</label>--}}
{{--                        <input class="form-control"--}}
{{--                               id="pickup_notification_time"--}}
{{--                               placeholder="Pickup Notification time"--}}
{{--                               required--}}
{{--                               value="{{$data->pickup_notification_time}}"--}}
{{--                        >--}}
{{--                    </div>--}}
{{--                    <div class="col-md-6 form-group mb-3">--}}
{{--                        <label for="firstName1">Feedback Notification Time in Minutes(X minutes After notification will be sent)</label>--}}
{{--                        <input class="form-control"--}}
{{--                               id="feedback_notification_time"--}}
{{--                               placeholder="Feedback Notification time"--}}
{{--                               required--}}
{{--                               value="{{$data->feedback_notification_time}}"--}}
{{--                        >--}}
{{--                    </div>--}}
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary" id="sub-btn">Submit</button>
                    </div>
            </form>
        </div>
    </div>
    </div>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


@endsection

@section('bottom-js')
    <script>
        function applyBonus() {
            $('#sub-btn').text('Saving').prop('disabled',true);
            axios.post(baseUrl + 'setting/update-global-settings', {
                tax_value: $('#tax_value').val(),
                pickup_notification_time: $('#pickup_notification_time').val(),
                feedback_notification_time: $('#feedback_notification_time').val(),
            }).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                $('#sub-btn').text('Submit').prop('disabled',false);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
                $('#sub-btn').text('Submit').prop('disabled',false);
            });
        }
    </script>
@endsection
