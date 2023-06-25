@extends('layouts.master')

@section('page-css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
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
                <h2>Timings</h2>
            </div>
            <div class="col-md-6">
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
                                <select class="form-control  selectpicker" id="my_restaurant"
                                        onchange="onRestaurantChange()">
                                    @if(Auth::guard('admin')->user()->type ==1)
                                        <option value="all" {{'all'==Session::get('my_restaurant') ? 'selected':''}}>
                                            All
                                        </option>
                                    @endif
                                    @foreach($header_restaurant as $item)
                                        @if(Auth::guard('admin')->user()->type ==1)
                                            <option
                                                value="{{$item->id}}"
                                                {{$item->id==Session::get('my_restaurant') ? 'selected':''}}>
                                                {{$item->address}}
                                            </option>
                                        @else
                                            @if($item->id==Session::get('my_restaurant'))
                                                <option
                                                    value="{{$item->id}}"
                                                    {{$item->id==Session::get('my_restaurant') ? 'selected':''}}>
                                                    {{$item->address}}
                                                </option>
                                            @endif
                                        @endif
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
            </div>
        </div>
    </div>
    <div class="col-md-12 form-main settings">
        <div class="row">
{{--            <div class="col-12 heading">--}}
{{--                <h3 class="">Kitchen online ordering</h3>--}}
{{--            </div>--}}
{{--            <div class="col-12 form-main">--}}
{{--                <div class="row">--}}
{{--                    <div class="col stock-form p-0">--}}
{{--                        <input id="toggle-on" onclick="onStatusChange()" class="toggle toggle-left" name="toggle" value="1" type="radio" checked>--}}
{{--                        <label for="toggle-on" class="btn"><span>ON</span></label>--}}
{{--                        <input id="toggle-off" onclick="onStatusChange()" class="toggle toggle-right" name="toggle" value="0" type="radio">--}}
{{--                        <label for="toggle-off" class="btn"><span>OFF</span></label>--}}
{{--                    </div>--}}
{{--                    <div class="col-auto time">--}}
{{--                        <button class="btn btn-success btn-update" onclick="kitchenOffline()">Update</button>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-12 form-group mb-3" id="message-wrap" style="display: none">--}}
{{--                        <label>Kitchen Offline Message(100 characters max)</label>--}}
{{--                        <textarea class="form-control" id="offline_message"></textarea>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <hr/>--}}

            <div class="col-12 heading sub">
                <h3 class="float-none">Restaurant Time Settings</h3>
                <p class="text-black-50">Note: timing changes will reflect next day</p>
            </div>
            <div class="col-12 form-main">
                <div class="row">
                    <div class="col-md-12 title">
                        <div class="form-group">
                            <input readonly type="text" class="form-control" id="" value="" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-12 duration">
                        @foreach($breakfastTimings as $timing)
                            <div class="row">
                                <p class="col-12 label-cus"></p>
                                <div class="col-md-2 day">
                                    <div class="col-md-12 form-group m-0">
                                        <input type="hidden" value="{{$timing->type}}" id="menutimetype-{{$timing->id}}">
                                        <input class="form-control text-capitalize" type="text" id="menutimeday-{{$timing->id}}" name="day[]" value="{{$timing->day}}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-8 time">
                                    <div class="row">
                                        <div class="col-md-2 form-group m-0">
                                            <select class="form-control selectpicker" id="menutimestart-{{$timing->id}}">
                                                <option value="">Select Time</option>
                                                @php
                                                    $start = "00:00";
                                                    $end = "23:45";

                                                    $tStart = strtotime($start);
                                                    $tEnd = strtotime($end);
                                                    $tNow = $tStart;
                                                    while($tNow <= $tEnd)
                                                    {
                                                        $selected = '';
                                                        if(date("H:i:s",$tNow) == $timing->from_1){
                                                            $selected = 'selected';
                                                        }
                                                        echo '<option '.$selected.' value="'.date("H:i",$tNow).'">'.date("h:i a",$tNow).'</option>';
                                                        $tNow = strtotime('+15 minutes',$tNow);
                                                    }
                                                @endphp
                                            </select>
                                        </div>
                                        <span class="to">to</span>
                                        <div class="col-md-2 form-group m-0">
                                            <select class="form-control selectpicker" id="menutimeend-{{$timing->id}}">
                                                <option value="">Select Time</option>
                                                @php
                                                    $start = "00:00";
                                                    $end = "23:45";

                                                    $tStart = strtotime($start);
                                                    $tEnd = strtotime($end);
                                                    $tNow = $tStart;
                                                    while($tNow <= $tEnd)
                                                    {
                                                        $selected = '';
                                                        if(date("H:i:s",$tNow) == $timing->to_1){
                                                        $selected = 'selected';
                                                        }
                                                    echo '<option '.$selected.' value="'.date("H:i",$tNow).'">'.date("h:i a",$tNow).'</option>';
                                                    $tNow = strtotime('+15 minutes',$tNow);
                                                    }
                                                @endphp
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group m-0">
                                            <input
                                                type="checkbox"
                                                id="ck-{{$timing->id}}"
                                                value="{{$timing->id}}"
                                                class="form-control timecheck"
                                                {{($timing->from_2 && $timing->to_2) ? 'checked="checked"': ''}}
                                            >
                                        </div>
                                        <div class="col-md-2 form-group m-0">
                                            <select class="form-control selectpicker"
                                                    id="menutimestart2-{{$timing->id}}"
                                                    {{(!$timing->from_2) ? 'disabled="disabled"': ''}}
                                            >
                                                <option value="">Select Time</option>
                                                @php
                                                    $start = "00:00";
                                                    $end = "23:45";

                                                    $tStart = strtotime($start);
                                                    $tEnd = strtotime($end);
                                                    $tNow = $tStart;
                                                    while($tNow <= $tEnd)
                                                    {
                                                        $selected = '';
                                                        if(date("H:i:s",$tNow) == $timing->from_2){
                                                            $selected = 'selected';
                                                        }
                                                        echo '<option '.$selected.' value="'.date("H:i",$tNow).'">'.date("h:i a",$tNow).'</option>';
                                                        $tNow = strtotime('+15 minutes',$tNow);
                                                    }
                                                @endphp
                                            </select>
                                        </div>
                                        <span class="to">to</span>
                                        <div class="col-md-2 form-group m-0">
                                            <select class="form-control selectpicker"
                                                    id="menutimeend2-{{$timing->id}}"
                                                {{(!$timing->to_2) ? 'disabled="disabled"': ''}}
                                            >
                                                <option value="">Select Time</option>
                                                @php
                                                    $start = "00:00";
                                                    $end = "23:45";

                                                    $tStart = strtotime($start);
                                                    $tEnd = strtotime($end);
                                                    $tNow = $tStart;
                                                    while($tNow <= $tEnd)
                                                    {
                                                        $selected = '';
                                                        if(date("H:i:s",$tNow) == $timing->to_2){
                                                        $selected = 'selected';
                                                        }
                                                    echo '<option '.$selected.' value="'.date("H:i",$tNow).'">'.date("h:i a",$tNow).'</option>';
                                                    $tNow = strtotime('+15 minutes',$tNow);
                                                    }
                                                @endphp
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1 time d-flex align-items-center">
                                        <label>Off?</label>
                                        <input
                                            type="checkbox"
                                            id="ck-off-{{$timing->id}}"
                                            value="{{$timing->id}}"
                                            class="form-control"
                                            {{($timing->offline) ? 'checked="checked"': ''}}
                                        >
                                </div>
                                <div class="col-md-1 time">
                                    <button class="btn btn-success" style="font-size: 18px" onclick="saveMenuTiming('{{$timing->id}}')">Save</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-12 heading mt-4 mb-2">
                <h3 class="">Future restaurant offline date(s)</h3>
            </div>
            <div class="col-12 form-main">
                <div class="row">
                    <div class="col-12 form-group pl-0">
                        <label>Select Date (or date range)</label>
                        <input class="form-control" type="text" name="offlineDateRange">
                    </div>
                    <div class="col-md-12 form-group pl-0" >
                        <label>Restaurant Offline Message(100 characters max)</label>
                        <textarea class="form-control" id="offline_message_range"></textarea>
                    </div>
                    <div class="col-auto time p-0">
                        <button class="btn btn-success btn-upload" onclick="kitchenOfflineRange()">Update</button>
                    </div>
                </div>
            </div>
            <div class="col-12 form-main">
                <b>Scheduled Date(s)</b>
                <table class="table table-responsive">
                    <thead>
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($offlineDates AS $offlineDate)
                        <tr>
                            <td>{{$offlineDate->start_date}}</td>
                            <td>{{$offlineDate->end_date}}</td>
                            <td>{{$offlineDate->offline_message}}</td>
                            <td><a href="javascript:void(0)" onclick="deleteDate('{{$offlineDate->id}}')">Delete</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection

@section('bottom-js')
    <script>
        var startDate = '';
        var endDate = '';
        jQuery(function () {
            $(".timecheck").change(function() {
                const id = $(this).val();
                if(this.checked) {
                    $('#menutimeend2-'+id).prop('disabled', false);
                    $('#menutimestart2-'+id).prop('disabled', false);
                    $('button[data-id="menutimeend2-'+id+'"]').removeClass('disabled');
                    $('button[data-id="menutimestart2-'+id+'"]').removeClass('disabled');
                }else{
                    $('#menutimeend2-'+id).prop('disabled', true);
                    $('#menutimestart2-'+id).prop('disabled', true);
                    $('#menutimeend2-'+id).val('');
                    $('#menutimestart2-'+id).val('');
                    $('button[data-id="menutimeend2-'+id+'"]').addClass('disabled');
                    $('button[data-id="menutimestart2-'+id+'"]').addClass('disabled');
                }
            });
            setTimeout(function () {
                jQuery('input[name="offlineDateRange"]').daterangepicker({
                    alwaysShowCalendars: true,
                    minDate: moment().format('YYYY-MM-DD'),
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                }, function (start, end, label) {
                    startDate = start.format('YYYY-MM-DD');
                    endDate = end.format('YYYY-MM-DD');
                });
                $('input[name="offlineDateRange"]').val('');
                $('input[name="offlineDateRange"]').attr("placeholder", "Please select date or date range");
            }, 2000);
        });
        function isNumber(evt) {
            var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
            if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57))
                return false;

            return true;
        }

        function onStatusChange(){
            if($("input[name='toggle']:checked").val() == 1){
                $('#offline_message').val('').hide();
                $('#message-wrap').hide();
            }
            if($("input[name='toggle']:checked").val() == 0){
                $('#offline_message').val('').show();
                $('#message-wrap').show();
            }
        }

        function kitchenOffline() {
            if($("input[name='toggle']:checked").val() == 0 && !$('#offline_message').val()){
                alert('Please enter a message');
                return false;
            }
            axios.post(baseUrl + 'setting/kitchen-status', {
                restaurant : 'all',
                offline_message: $('#offline_message').val(),
                status: $("input[name='toggle']:checked").val()
            }).then(function (response) {
                toastr.success('Kitchen status updated', "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            });
        }

        function kioskStatus() {
            axios.post(baseUrl + 'setting/kiosk-status', {
                restaurant : 'all',
                status: $("input[name='kioskStatus']:checked").val()
            }).then(function (response) {
                toastr.success('Kiosk status updated', "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            });
        }

        function selectTimings(id) {
            alert(id);
        }

        function kitchenOfflineRange() {
            axios.post(baseUrl + 'settings/kitchen-offline-range', {
                restaurant : 'all',
                offline_message_range: $('#offline_message_range').val(),
                startDate: startDate,
                endDate: endDate,
            }).then(function (response) {
                toastr.success('Kitchen status updated', "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                window.location.reload();
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            });
        }

        function applyBonus() {
            $('#sub-btn').text('Saving').prop('disabled',true);
            axios.post(baseUrl + 'setting/update-global-settings', {
                tax_value: $('#tax_value').val(),
                pickup_notification_time: $('#pickup_notification_time').val(),
                feedback_notification_time: $('#feedback_notification_time').val(),
                /* offline_message: $('#offline_message').val(),
                 is_offline: $('#offline').prop("checked") ? 1 : 0*/
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

        function saveMenuTiming(id){
            let offline = 0;
            if($('#ck-off-'+id).is(":checked")){
                offline = 1;
            }
            const inputData = {
                id,
                type: $('#menutimetype-'+id).val(),
                day: $('#menutimeday-'+id).val(),
                from_1: $('#menutimestart-'+id).val(),
                to_1: $('#menutimeend-'+id).val(),
                from_2: $('#menutimestart2-'+id).val(),
                to_2: $('#menutimeend2-'+id).val(),
                offline: offline
            }
            axios.post(baseUrl + 'settings/menu-timing-update', inputData).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }
        function deleteDate(id){
            var c = confirm('Are you sure?');
            if(c){
                window.location.href = baseUrl + 'settings/kitchen-offline-range-delete/'+id;
            }
        }
    </script>
@endsection
