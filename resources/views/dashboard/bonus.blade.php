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
            right: 3.5rem;
            border: transparent;
            background: transparent;
            color: rgba(0,0,0,0.7);
        }
        .totalRemainingCharacter{
            position: absolute;
            right: 2rem;
            color: rgba(0,0,0,0.7);
            top: 3.7rem;
        }
        .card {
            padding: 15px;
        }
        .more {
            overflow: hidden;
        }
        /*select.selectUser {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 20rem #f8f9fa !important;
        }*/
    </style>
@endsection


@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
         {{--   <div class="col-md-6">
                <h2 class="heading-white mb-4">Rewards / Birthday</h2>
            </div>--}}
            <div class="col-md-6">
                <h2 class="heading-white">Bonus (Offer)</h2>
            </div>
            <div class="col-md-6">
                <button class="btn btn-primary float-right" onclick="createBonus()">Add New</button>
            </div>
            {{--<div class="col-md-6">
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
    <div class="card mt-4">
    <div class="row">
       {{-- <div class="col-md-6">
            <h1 class="heading-white mb-4">Bonus (Offer)</h1>
        </div>
        <div class="col-md-6 mt-3">
            <button class="btn btn-primary float-right" onclick="createBonus()">Add New</button>
        </div>--}}
        <div class="col-md-12">
            @if(sizeof($all_bonus))
                <div class="col-md-12">
                    <table class="table table-hover sortable" id="bonusTable">
                        <thead>
                        <tr>
                            <th style="min-width: 80px;">No.</th>
                            <th style="min-width: 150px;">Bonus Name<i class="i-Up---Down"></i></th>
                            <th style="min-width: 100px;">Expiry<i class="i-Up---Down"></i></th>
                            <th style="min-width: 180px;">Applied For<i class="i-Up---Down"></i></th>
                            <th style="min-width: 180px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($all_bonus as $key=>$value)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>
                                    {{$value['bonus_name']}}
                                </td>
                                <td>
                                    {{date("n/j/Y", strtotime($value['bonus_expiry']))}}
                                </td>
                                <td>
                                    <div class="more">
                                        @foreach($value['appliedFor'] as $k => $item)
                                        {{$item['first_name']}} {{$item['last_name']}}
                                        @if(sizeof($value['appliedFor']) != $k+1)
                                            , <br>
                                        @endif
                                    @endforeach
                                    </div>
                                </td>
                                <td class="td-width">
                                    <a title="View" class="action-links"
                                       onclick="getSingleBonus({{$value->bonus_id}})">View</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="col-md-12 mt-5 text-center">
                    <img alt="" height="150" src="{{asset('public/images/not-found.png')}}">
                    <h5 class="mt-3 not-found">Not Found</h5>
                </div>
            @endif
        </div>
    </div>
    </div>
        <div class="modal fade" id="addNewBonus" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Bonus (Offer)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form name="bonusForm" onsubmit="applyBonus();return false;">
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label for="bonus_name">Name Of Bonus*</label>
                                    <input class="form-control"
                                           id="bonus_name"
                                           placeholder="Enter Name Of Bonus"
                                           name="bonusName" onkeydown="limitBonus(this.form.bonusName,this.form.countdownBonus,30);" onkeyup='limitBonus(this.form.bonusName,this.form.countdownBonus,30);' required/>
                                    <input readonly type="text" name="countdownBonus" size="1" class="text-right remainingCharacter" value="30 "><span class="totalRemainingCharacter">/ 30</span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="firstName1">Expiration/Dates Applicable*</label>
                                    <input class="form-control"
                                           type="date"
                                           id="bonus_expiry"
                                           placeholder="Enter Date"
                                           required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3 d-none">
                                    <label for="notification_text">Push Notification Text*</label>
                                    <textarea id="notification_text" class="form-control" rows="4"
                                              placeholder="Write Notification Text Here..." name="notification" required>TEst</textarea>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="description">Bonus Description*</label>
                                    <textarea id="description" class="form-control" rows="4"
                                              placeholder="Write Bonus Description Here..." name="message" required></textarea>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="term_and_condition">Terms & Conditions</label>
                                    <textarea id="term_and_condition" class="form-control" rows="4"
                                              placeholder="Write Terms & Conditions Here..." name="TandC" ></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label for="firstName1">Select User</label>
                                    <select class="form-control selectpicker"
                                            id="bonus_user_type"
                                            onchange="onUserChange()"
                                            >
                                        <option value="">Select</option>
                                        <option value="1">All users</option>
                                        <option value="2">Selected Users</option>
{{--                                        <option value="2">Users Who have Ordered More Than [$X] Within ([Y] Time--}}
{{--                                            Frame)--}}
{{--                                        </option>--}}
{{--                                        <option value="3">Users Who have Ordered Less Than [$X] Within ([Y] Time--}}
{{--                                            Frame)--}}
{{--                                        </option>--}}
{{--                                        <option value="4">Users Who Has Not Made an Order Within [X] Time Frame--}}
{{--                                            (Excludes--}}
{{--                                            New Users)--}}
{{--                                        </option>--}}
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="lastName1">Select Offer</label>
                                    <select class="form-control selectpicker" id="bonus_type" onchange="onOfferChange()"
                                            required>
                                        <option value="">Select</option>
                                        <option value="1">Extra Points</option>
                                        <option value="2">Points Multiplier</option>
{{--                                        <option value="3">Free Item</option>--}}
                                        <option value="4">% Off</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="exampleInputEmail1">Apply Condition</label>
                                    <select class="form-control selectpicker" id="bonus_condition_type"
                                            onchange="onConditionChange()">
                                        <option value="">No Condition</option>
                                        <option value="8">Upon user's birthday</option>
                                        <option value="7">Upon [X] lifetime points earned on the account</option>
                                        <option value="5">After [X] Orders placed within a date range
                                        </option>
                                        <option value="4">Places their order within date range</option>
                                        <option value="2">Placed their order on Specific Date
                                        </option>
                                        <option value="3">Placed their order on Specific Date (and Within Specified
                                            Hours)
                                        </option>
                                        <option value="9">Having their next order index number as [X]</option>
                                        <option value="10">Having total order value greater than [X] within date range</option>
                                        <option value="11">Having total order value less than [X] within date range</option>
{{--                                        <option value="12">Who have account created before [X DATE] but have not ordered within date range</option>--}}
                                    </select>
                                </div>
                                <div class="col-md-12" id="on_user_condition">
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <p class="text-primary" style="font-size: 16px;">Select User(s)</p>
                                        </div>
                                        <div class="col-md-10 row-border"></div>
                                        <div class="col-md-12" id="user_amount_view">
                                            <select multiple id="multiple-user-sel" class="form-control">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" id="on_offer_condition">
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <p class="text-primary" style="font-size: 16px;">Offer Parameters</p>
                                        </div>
                                        <div class="col-md-10 row-border"></div>
                                        <div class="col-md-6 mb-3" id="extra_points_view">
                                            <label for="exampleInputEmail1">Enter Number Of Extra Points</label>
                                            <input type="text" class="form-control" id="extra_points"
                                                   placeholder="Enter Extra Points">
                                        </div>
                                        <div class="col-md-6 mb-3" id="points_multiplier_view">
                                            <label for="exampleInputEmail1">Enter Points Multiplier</label>
                                            <input type="text" class="form-control" id="points_multiplier"
                                                   placeholder="Enter Points Multiplier">
                                        </div>
                                        <div class="col-md-6 mb-3" id="free_item_view">
                                            <label for="filter_by_menu_type">Select Menu Type</label>
                                            <select class="form-control selectpicker" id="filter_by_menu_type"
                                                    onchange="getMenuCategory()">
                                                <option value="">Select</option>
                                                @foreach($menu_types as $item)
                                                    <option value="{{$item->menu_id}}">{{$item->menu_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3" id="menu_category">
                                            <label for="filter_by_menu_category">Select Category</label>
                                            <select class="form-control " id="filter_by_menu_category"
                                                    onchange="getItems()">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3" id="items_view">
                                            <label for="free_item">Select Item</label>
                                            <select class="form-control" id="free_item">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3" id="percentage_off_view">
                                            <label for="exampleInputEmail1">Enter Discount In %</label>
                                            <input type="text" class="form-control" id="percentage_off"
                                                   placeholder="Enter Discount In %">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" id="on_condition">
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <p class="text-primary" style="font-size: 16px;">Apply Condition</p>
                                        </div>
                                        <div class="col-md-10 row-border"></div>
                                        <div class="col-md-6 mb-3" id="no_of_order_view">
                                            <label for="no_of_order">Number Of Order</label>
                                            <input type="text" class="form-control" id="no_of_order"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Enter Number Of Order">
                                        </div>
                                        <div class="col-md-6 mb-3" id="total_order_amount_view">
                                            <label for="total_order_amount">Total Order Amount $</label>
                                            <input type="text" class="form-control" id="total_order_amount"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Enter Amount">
                                        </div>
                                        <div class="col-md-6 mb-3" id="order_date_view">
                                            <label for="order_date">Order Date</label>
                                            <input type="date" class="form-control" id="order_date"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Select Order Date">
                                        </div>
                                        <div class="col-md-6 mb-3" id="order_time_view">
                                            <label for="order_date">Order Time</label>
                                            <input type="time" class="form-control" id="order_time"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Select Order Date">
                                        </div>
                                        <div class="col-md-6 mb-3" id="start_date_view">
                                            <label for="start_date">Start Date</label>
                                            <input type="date" class="form-control" id="start_date"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Select Start Date">
                                        </div>
                                        <div class="col-md-6 mb-3" id="end_date_view">
                                            <label for="end_date">End Date</label>
                                            <input type="date" class="form-control" id="end_date"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Select End Date">
                                        </div>
                                        <div class="col-md-6 mb-3" id="start_hours_view">
                                            <label for="end_date">Start Hours</label>
                                            <input type="time" class="form-control" id="start_hours"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Enter Start Hour">
                                        </div>
                                        <div class="col-md-6 mb-3" id="end_hours_view">
                                            <label for="end_date">End Hours</label>
                                            <input type="time" class="form-control" id="end_hours"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Enter End Hour">
                                        </div>
                                        <div class="col-md-6 mb-3" id="no_of_plates_view">
                                            <label for="end_date">Number Of Plates</label>
                                            <input type="text" class="form-control" id="no_of_plates"
                                                   placeholder="Enter No Of Plates">
                                        </div>
                                        <div class="col-md-6 mb-3" id="user_no_of_points_view">
                                            <label for="end_date">Number Of Points</label>
                                            <input type="text" class="form-control" id="user_no_of_points"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Enter No Of Points">
                                        </div>
                                        <div class="col-md-6 mb-3" id="order_index_number_view">
                                            <label for="order_index_number_view">Index Number</label>
                                            <input type="text" class="form-control" id="order_index_number"
                                                   onkeyup="getBonusUser()"
                                                   placeholder="Index Number">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="viewBonusInfo" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">View Bonus (Offer)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12" id="single_bonus_info"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
    <script>
        jQuery(document).ready(function() {

            jQuery('.more').readmore({
                speed: 300,
                collapsedHeight: 48 ,
                moreLink: '<a href="#">Read more &gt;</a>',
                lessLink: '<a href="#">Read less</a>',
                heightMargin: 16
            })
        })
    </script>
    <script>
        $(function () {
            /* $("#start_date").datepicker();
             $("#end_date").datepicker();*/
            var date = new Date();

            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();

            if (month < 10) month = "0" + month;
            if (day < 10) day = "0" + day;

            var today = year + "-" + month + "-" + day;


            document.getElementById('bonus_expiry').value = today;
            document.getElementById('start_date').value = today;
            document.getElementById('order_date').value = today;
            document.getElementById('end_date').value = today;
            $('#multiple-user-sel').select2({
                ajax: {
                    url: baseUrl+'get-users-keyword',
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (data) {
                        console.log(data.response);
                        return {
                            results: data.response
                        };
                    }
                    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
                }
            });
        });
    </script>
@endsection

@section('bottom-js')
    <script>

        let selectOffer;
        let selectUser;
        let selectCondition;

        function createBonus() {
            $('#on_user_condition').hide();
            $('#on_offer_condition').hide();
            $('#on_condition').hide();
            $('#extra_points_view').hide();
            $('#points_multiplier_view').hide();
            $('#free_item_view').hide();
            $('#percentage_off_view').hide();
            $('#no_of_order_view').hide();
            $('#start_date_view').hide();
            $('#end_date_view').hide();
            $('#end_hours_view').hide();
            $('#start_hours_view').hide();
            $('#user_no_of_points_view').hide();
            $('#no_of_plates_view').hide();
            $('#user_amount_view').hide();
            $('#menu_category').hide();
            $('#items_view').hide();
            $('#addNewBonus').modal('show');
        }
        function limitBonus(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
       /* function limitNotification(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
        function limitTandC(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }*/


        function onUserChange() {
            selectUser = $('#bonus_user_type').val();
            if (selectUser > 1) {
                $('#on_user_condition').show();
                selectUser == 4 ? $('#user_amount_view').hide() : $('#user_amount_view').show();
                $('#user_amount').val('');
                $('#start_time_frame').val('');
                $('#end_time_frame').val('');
            } else {
                $('#on_user_condition').hide();
                getBonusUser();
                $('#user_amount').val('');
                $('#start_time_frame').val('');
                $('#end_time_frame').val('');
            }
        }

        let appliedFor;

        function getBonusUser() {
            return true;
            // axios.post(baseUrl + 'get-bonus-users', {
            //     'user_type': $('#bonus_user_type').val(),
            //     'user_amount': $('#user_amount').val(),
            //     'start_time_frame': $('#start_time_frame').val(),
            //     'end_time_frame': $('#end_time_frame').val(),
            //     'bonus_condition': $('#bonus_condition_type').val(),
            //     'no_of_order': $('#no_of_order').val(),
            //     'start_date': $('#start_date').val(),
            //     'end_date': $('#end_date').val(),
            //     'order_date': $('#order_date').val(),
            //     'order_time': $('#order_time').val(),
            //     'user_no_of_points': $('#user_no_of_points').val(),
            // }).then(function (response) {
            //     appliedFor = response.data.response.records;
            // }).catch(function (error) {
            //     toastr.error(error.response.data.message, "Required!", {
            //         timeOut: "3000",
            //         positionClass: "toast-bottom-right"
            //     })
            // });
        }

        function onOfferChange() {
            selectOffer = $('#bonus_type').val();
            if (selectOffer) {
                $('#on_offer_condition').show();
                if (selectOffer == 1) {
                    $('#extra_points_view').show();
                    $('#points_multiplier_view').hide();
                    $('#free_item_view').hide();
                    $('#percentage_off_view').hide();
                    $('#items_view').hide();
                    $('#menu_category').hide();
                } else if (selectOffer == 2) {
                    $('#points_multiplier_view').show();
                    $('#extra_points_view').hide();
                    $('#free_item_view').hide();
                    $('#percentage_off_view').hide();
                    $('#items_view').hide();
                    $('#menu_category').hide();
                } else if (selectOffer == 3) {
                    $('#free_item_view').show();
                    $('#extra_points_view').hide();
                    $('#points_multiplier_view').hide();
                    $('#percentage_off_view').hide();
                    $('#items_view').show();
                    $('#menu_category').show();
                } else if (selectOffer == 4) {
                    $('#percentage_off_view').show();
                    $('#extra_points_view').hide();
                    $('#points_multiplier_view').hide();
                    $('#free_item_view').hide();
                    $('#items_view').hide();
                    $('#menu_category').hide();
                } else {
                    $('#extra_points_view').hide();
                    $('#points_multiplier_view').hide();
                    $('#free_item_view').hide();
                    $('#percentage_off_view').hide();
                    $('#items_view').hide();
                    $('#menu_category').hide();
                }
            } else {
                $('#on_offer_condition').hide();
            }
        }

        function onConditionChange() {
            selectCondition = $('#bonus_condition_type').val();
            if (selectCondition) {
                $('#on_condition').show();
                if (selectCondition == 1) {
                    $('#total_order_amount_view').hide();
                    $('#order_index_number_view').hide();
                    $('#no_of_order_view').show();
                    $('#start_date_view').hide();
                    $('#end_date_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                    $('#end_hours_view').hide();
                    $('#start_hours_view').hide();
                    $('#order_date_view').hide();
                    $('#order_time_view').hide();
                } else if (selectCondition == 2) {
                    $('#order_index_number_view').hide();
                    $('#total_order_amount_view').hide();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').hide();
                    $('#order_date_view').show();
                    $('#order_time_view').hide();
                    $('#end_date_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                } else if (selectCondition == 3) {
                    $('#order_index_number_view').hide();
                    $('#total_order_amount_view').hide();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').hide();
                    $('#end_date_view').hide();
                    $('#order_date_view').show();
                    $('#order_time_view').show();
                    $('#end_hours_view').hide();
                    $('#start_hours_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                } else if (selectCondition == 4) {
                    $('#order_index_number_view').hide();
                    $('#total_order_amount_view').hide();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').show();
                    $('#end_date_view').show();
                    $('#end_hours_view').hide();
                    $('#order_time_view').hide();
                    $('#order_date_view').hide();
                    $('#start_hours_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                } else if (selectCondition == 5) {
                    $('#order_index_number_view').hide();
                    $('#total_order_amount_view').hide();
                    $('#no_of_order_view').show();
                    $('#start_date_view').show();
                    $('#end_date_view').show();
                    $('#end_hours_view').hide();
                    $('#order_date_view').hide();
                    $('#order_time_view').hide();
                    $('#start_hours_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                } else if (selectCondition == 6) {
                    $('#order_index_number_view').hide();
                    $('#total_order_amount_view').hide();
                    $('#no_of_plates_view').show();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').hide();
                    $('#order_date_view').hide();
                    $('#end_date_view').hide();
                    $('#end_date_view').hide();
                    $('#order_time_view').hide();
                    $('#start_hours_view').hide();
                    $('#user_no_of_points_view').hide();
                } else if (selectCondition == 7) {
                    $('#order_index_number_view').hide();
                    $('#total_order_amount_view').hide();
                    $('#user_no_of_points_view').show();
                    $('#no_of_plates_view').hide();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').hide();
                    $('#end_date_view').hide();
                    $('#order_time_view').hide();
                    $('#order_date_view').hide();
                    $('#end_hours_view').hide();
                    $('#start_hours_view').hide();
                }
                else if (selectCondition == 8) {
                    $('#on_condition').hide();
                }
                else if (selectCondition == 9) {
                    $('#total_order_amount_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#order_index_number_view').show();
                    $('#no_of_plates_view').hide();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').hide();
                    $('#end_date_view').hide();
                    $('#order_time_view').hide();
                    $('#order_date_view').hide();
                    $('#end_hours_view').hide();
                    $('#start_hours_view').hide();
                }
                else if (selectCondition == 10) {
                    $('#order_index_number_view').hide();
                    $('#no_of_order_view').hide();
                    $('#total_order_amount_view').show();
                    $('#start_date_view').show();
                    $('#end_date_view').show();
                    $('#end_hours_view').hide();
                    $('#order_date_view').hide();
                    $('#order_time_view').hide();
                    $('#start_hours_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                }
                else if (selectCondition == 11) {
                    $('#order_index_number_view').hide();
                    $('#no_of_order_view').hide();
                    $('#total_order_amount_view').show();
                    $('#start_date_view').show();
                    $('#end_date_view').show();
                    $('#end_hours_view').hide();
                    $('#order_date_view').hide();
                    $('#order_time_view').hide();
                    $('#start_hours_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                }
                else {
                    $('#total_order_amount_view').hide();
                    $('#order_index_number_view').hide();
                    $('#no_of_order_view').hide();
                    $('#start_date_view').hide();
                    $('#end_date_view').hide();
                    $('#end_hours_view').hide();
                    $('#start_hours_view').hide();
                    $('#order_date_view').hide();
                    $('#order_time_view').hide();
                    $('#user_no_of_points_view').hide();
                    $('#no_of_plates_view').hide();
                }
            } else {
                $('#on_condition').hide();
            }
        }

        function getMenuCategory() {
            $('#filter_by_menu_category').html('');
            axios.get(baseUrl + 'get-menu-category/' + $('#filter_by_menu_type').val()).then(function (response) {
                if (response.data.response.length > 0) {
                    for (let i = 0; i < response.data.response.length; i++) {
                        $('#filter_by_menu_category').append('<option value="' + response.data.response[i].category.category_id + '">' + response.data.response[i].category.category_name + '</option>');
                    }
                    $('#menu_category').show();
                }
            }).catch(function (error) {
            });
        }

        function getItems() {
            var data = {
                'keyword': '',
                'menu_type': $('#filter_by_menu_type').val(),
                'category_id': $('#filter_by_menu_category').val(),
            };
            $('#free_item').html('');
            axios.post(baseUrl + 'search-side-menu', data).then(function (response) {
                if (response.data.response.length) {
                    for (i = 0; i < response.data.response.length; i++) {
                        $('#free_item').append('<option value="' + response.data.response[i].item_id + '">' + response.data.response[i].item_name + '</option>');
                    }
                    $('#items_view').show();
                }
                if ($('#filter_by_menu_type').val() && !$('#filter_by_menu_category').val()) {
                    getMenuCategory($('#filter_by_menu_type').val());
                }
            });
        }

        function applyBonus() {
            axios.post(baseUrl + 'create-bonus', {
                'bonus_name': $('#bonus_name').val(),
                'bonus_expiry': $('#bonus_expiry').val(),
                'notification_text': $('#notification_text').val(),
                'description': $('#description').val(),
                'term_and_condition': $('#term_and_condition').val(),
                'bonus_orders_no': $('#no_of_order').val(),
                'bonus_start_date': $('#start_date').val(),
                'bonus_start_hour': $('#start_hours').val(),
                'bonus_end_date': $('#end_date').val(),
                'bonus_end_hour': $('#end_hours').val(),
                'bonus_plates_no': $('#no_of_plates').val(),
                'bonus_user_points': $('#user_no_of_points').val(),
                'bonus_type': $('#bonus_type').val(),
                'bonus_user_type': $('#bonus_user_type').val(),
                'bonus_condition_type': $('#bonus_condition_type').val(),
                'bonus_free_item_id': $('#free_item').val(),
                'bonus_extra_point': $('#extra_points').val(),
                'bonus_points_multiplier': $('#points_multiplier').val(),
                'bonus_discount': $('#percentage_off').val(),
                'applied_for': appliedFor,
                'selected_users':$("#multiple-user-sel").val(),
                'order_date':$("#order_date").val(),
                'total_order_amount':$('#total_order_amount').val(),
                'order_index_number':$('#order_index_number').val()
            }).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                setTimeout(function () {
                    window.location.reload();
                }, 200);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }

        function deleteMenu(itemId, table) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.value
                ) {
                    axios.get(baseUrl + 'delete-items/' + itemId + '/' + table).then(function (response) {
                        toastr.success(response.data.message, "Success", {
                            timeOut: "3000",
                            positionClass: "toast-bottom-right"
                        });
                        setTimeout(function () {
                            window.location.reload();
                        }, 200);
                    }).catch(function (error) {
                        toastr.error(error.response.data.message, "Required!", {
                            timeOut: "3000",
                            positionClass: "toast-bottom-right"
                        });
                    });
                }
            })
        }

        function getSingleBonus(bonusId) {
            $('#single_bonus_info').html(' ');
            axios.get(baseUrl + 'get-single-bonus/' + bonusId).then(function (response) {
                let bonusType;
                let termsCondition;
                let bonusConditionType;
                let appliedFor = [];

                if (response.data.response.bonus_type == 1) {
                    bonusType = 'Extra Points';
                } else if (response.data.response.bonus_type == 2) {
                    bonusType = 'Points Multiplier';
                } else if (response.data.response.bonus_type == 3) {
                    bonusType = 'Free Item';
                } else if (response.data.response.bonus_type == 4) {
                    bonusType = '% Off';
                }

                if (response.data.response.bonus_condition_type == 0) {
                    bonusConditionType = 'NA';
                } else if (response.data.response.bonus_condition_type == 1) {
                    bonusConditionType = 'User\'s [X#] Order';
                } else if (response.data.response.bonus_condition_type == 2) {
                    bonusConditionType = 'All Orders Placed on Specific Date (Any time on that Date)';
                } else if (response.data.response.bonus_condition_type == 3) {
                    bonusConditionType = 'All Orders Placed on Specific Date (and Within Specified Hours)';
                } else if (response.data.response.bonus_condition_type == 4) {
                    bonusConditionType = 'All Orders Placed Within Date Range';
                } else if (response.data.response.bonus_condition_type == 5) {
                    bonusConditionType = 'After [X] Number of Orders Placed Within a Date Range';
                } else if (response.data.response.bonus_condition_type == 6) {
                    bonusConditionType = 'Buy One Plate and Get [X] On Second Plate (of equal or lesser value)';
                } else if (response.data.response.bonus_condition_type == 7) {
                    bonusConditionType = 'User Achieves [X] number of Points';
                }
                if (response.data.response.term_and_condition == null) {
                    termsCondition = 'NA';
                } else {
                    termsCondition = response.data.response.term_and_condition;
                }

                for (let x = 0; x < response.data.response.applied_for.length; x++) {
                    let obj = response.data.response.applied_for[x];
                    appliedFor.push(obj.first_name + ' ' + obj.last_name);
                }
                let dt1 = new Date(response.data.response.bonus_expiry);
                appliedFor = appliedFor.toString();
                $('#single_bonus_info').append(
                    '<div class="row">' +
                    '<div class="col-md-6"><p><b>Name :</b> ' + response.data.response.bonus_name + '</p></div>' +
                    '<div class="col-md-6"><p><b>Type :</b> ' + bonusType + '</p></div>' +
                    '<div class="col-md-6"><p><b>Condition Type :</b> ' + bonusConditionType + '</p></div>' +
                    '<div class="col-md-6"><p><b>Expiry Date :</b> ' + (dt1.getMonth()+ 1) + "/" + dt1.getDate() + "/" + dt1.getFullYear() + '</p></div>' +
                    '<div class="col-md-12"><p><b>Description :</b> ' + response.data.response.description + '</p></div>' +
                    // '<div class="col-md-12"><p><b>Notification Text :</b> ' + response.data.response.notification_text + '</p></div>' +
                    '<div class="col-md-12"><p><b>Term & Conditions :</b> ' + termsCondition + '</p></div>' +
                    '<div class="col-md-12"><p><b>Bonus Applied For :</b> ' + appliedFor + '</p></div>' +
                    '<div class="col-md-12 text-right"> <button class="btn btn-primary ml-1"\n' +
                    '                                    onclick="deleteMenu(' + response.data.response.bonus_id + ', \'bonus\')">\n' +
                    '                                Delete\n' +
                    '                            </button></div>' +
                    '</div>'
                );
                $('#viewBonusInfo').modal('show');
            }).catch(function (error) {
            });
        }
    </script>
    <script>
        var table = $('#bonusTable');
        $('.sortable th')
            .wrapInner('<span title="sort this column"/>')
            .each(function(){
                var th = $(this),
                    thIndex = th.index(),
                    inverse = false;
                th.click(function(){
                    table.find('td').filter(function(){
                        return $(this).index() === thIndex;
                    }).sortElements(function(a, b){
                        if( $.text([a]) == $.text([b]) )
                            return 0;
                        return $.text([a]) > $.text([b]) ?
                            inverse ? -1 : 1
                            : inverse ? 1 : -1;
                    }, function(){
                        return this.parentNode;
                    });
                    inverse = !inverse;
                });
            });
    </script>
@endsection
