@extends('layouts.master')

@section('page-css')
    <style>
        ul.breadcrumb {
            /*padding: 10px 16px;*/
            list-style: none;
            background-color: transparent;
            /*border-bottom: 1px solid #a92219;*/
            border-radius: 0;
            /*padding-left: 0;*/
        }

        ul.breadcrumb li {
            display: inline;
            font-size: 18px;
        }

        ul.breadcrumb li + li:before {
            padding: 8px;
            color: black;
            content: "/\00a0";
        }

        ul.breadcrumb li a {
            color: #a92219;
            text-decoration: none;
        }

        ul.breadcrumb li a:hover {
            color: #a92219;
            text-decoration: underline;
        }

        .card-ecommerce-3 .card-img-left {
            height: 300px;
            -o-object-fit: cover;
            object-fit: cover;
            width: 500px;
        }

        .order-box {
            border: 1px solid #d1d1d1;
        }

        .text-primary {
            color: #a92219;
        }

        hr {
            margin-top: 12px;
            margin-bottom: 15px;
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            height: 0;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        select.moreTime {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 27rem #f8f9fa !important;
        }

        b {
            color: #4f4f4f;
        }

        .card {
            padding: 15px !important;
        }

        .font-16 {
            font-size: 18px;
            color: black;
        }

        .desktop-view {
            display: unset !important;
        }

        .ipad-view {
            display: none !important;
        }

        @media screen and (min-width: 800px) {
            .desktop-view {
                display: none !important;
            }

            .ipad-view {
                display: unset !important;
            }
        }
    </style>
@endsection

@section('main-content')
    <div class="card p-2" style="border-radius: 0">
        <div class="row m-0 pl-2">
            <div class="col-md-6">
                @if(Auth::guard('admin')->user()->type ==1)
                    <ul class="breadcrumb">
                        <li><a href="{{route('order-list')}}">Order(s)</a></li>
                        <li>Details of #{{$single_order->order_id}}</li>
                    </ul>
                @endif
                @if(Auth::guard('admin')->user()->type ==2)
                    <ul class="nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{route('order-list-manager')}}">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('items-availability')}}">Items</a>
                        </li>
                    </ul>
                @endif
            </div>
            <div class="col-md-6">
                @if(Auth::guard('admin')->user()->type ==2)
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
                @endif
            </div>
        </div>
    </div>
    <div style="" id="main-cont">
        <div class="col-md-12 pb-2 @if(Auth::guard('admin')->user()->type ==1) card mt-3 @else mt-2 @endif">
            <div class="row">
                <div class="col-md-12 mt-3">
                    <h5>Order Details</h5>
                </div>
                <div class="col-md-6 mb-2">Ordered On:<b
                        class="font-16"> {{date("n/j/Y", strtotime($single_order->created_at))}}</b></div>
                <div class="col-md-6 mb-2">Order Platform:
                    @if($single_order->is_server_order == 1)
                        <span class="badge badge-info">SERVER</span>
                    @else
                        {{$single_order->order_device == 1 ? '<span class="badge badge-info">WEBSITE</span>' : '<span class="badge badge-info">APP</span>'}}
                    @endif
                </div>
                @if($single_order->pickup_time == 0)
                    <div class="col-md-6 mb-2">Pickup Time: <b class="font-16">NA</b>
                        @endif
                        @if($single_order->pickup_time > 0)
                            <div class="col-md-6 mb-2">{{$single_order->order_type == 1 ? 'Pickup' : 'Delivery'}}
                                Time:<b
                                    class="font-16"> {{date("g:i a",$single_order->pickup_time)}}</b>
                                @endif

                                @if($single_order->status==1)
                                    <a title="Edit Preparation Time"
                                       style="color: #a92219;text-decoration: underline;cursor: pointer;"
                                       onclick="editTime({{$single_order->order_id}})">(Extend Time)</a>
                                @endif
                            </div>
                            <div class="col-md-6 mb-2">Order Status:@if($single_order->status==1)
                                    <span class="badge badge-success">New Order</span>
                                @elseif($single_order->status==2)
                                    <span class="badge badge-primary">Ready to pickup</span>
                                @elseif($single_order->status==3)
                                    <span class="badge badge-success">Picked Up</span>
                                @endif
                            </div>
                            <div class="col-md-6 mb-2">{{$single_order->order_type == 1 ? 'Pickup' : 'Delivery'}}
                                Location:<b
                                    class="font-16"> {{$single_order->restaurantDetails->address}}</b></div>
                            <div class="col-md-6 mb-2">Payment Status: @if($single_order->payment_status==1)
                                    <span class="badge badge-success">Paid</span>
                                @elseif($single_order->payment_status==2)
                                    @php
                                        $refundStatus = isset($orderRefunded) ? $orderRefunded : false;
                                    @endphp
                                     @if($refundStatus)
                                    <span class="badge badge-warning">Refunded</span>
                                    @else
                                    <span class="badge badge-warning">Refund Requested</span>
                                    @endif
                                @elseif($single_order->payment_status==4)
                                    <span class="badge badge-warning">Processing Refund</span>
                                @elseif($single_order->payment_status==3)
                                    <span class="badge badge-danger">Pending Payment</span>
                                @endif
                            </div>
                            <div class="col-md-6 mb-2">Discount Amount: ${{$single_order->discount_amount}}</div>

                            <div class="col-md-6 mb-2">
                                @if(Auth::guard('admin')->user()->type ==1)
                                    Print Bill:<a title="Print"
                                                  style="color: #a92219;text-decoration: underline;cursor: pointer;"
                                                  onclick="getSingleOrder({{$single_order->order_id}})">Print</a>
                                @endif
                            </div>

                            <div class="col-md-6 mb-2">Total Amount:<b class="font-16">
                                    ${{$single_order->total_amount}}</b>
                            </div>
                            {{--<div class="col-md-6 mb-2"><b>Preparation Time :</b>
                                <a title="Edit Preparation Time" style="color: #a92219;text-decoration: underline;cursor: pointer;"
                                   onclick="editTime({{$single_order->order_id}})">{{$single_order->preparation_time}}</a>
                            </div>--}}

{{--                            @if($single_order->payment_status==1)--}}
{{--                                <div class="col-md-6 mb-2">--}}
{{--                                    @if(Auth::guard('admin')->user()->type ==1)--}}
{{--                                        Refund: <a title="Refund"--}}
{{--                                                   style="color: #a92219;text-decoration: underline;cursor: pointer;"--}}
{{--                                                   onclick="openRefundModal({{$single_order->order_id}}, {{$single_order->total_amount}})">Click--}}
{{--                                            here to refund</a>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            @endif--}}
                            @if($single_order->coupon_type)
                                <div class="col-md-6 mb-2">Applied Reward: @if($single_order->coupon_type==1)
                                        <span>Basic Reward</span>
                                    @elseif($single_order->coupon_type==2)
                                        <span>Birthday Reward</span>
                                    @elseif($single_order->coupon_type==3)
                                        <span>Admin Reward</span>
                                    @else
                                        <span>Not Applied</span>
                                    @endif
                                </div>
                            @endif


                            <div class="col-md-6 mb-2">Earned Reward
                                Points: {{$rewards ? $rewards : 0}}</div>

                            @if($single_order->feedback!=null)
                                <div class="col-md-6 mb-2">Order Feedback: @if($single_order->feedback['feedback']==1)
                                        <span class="badge badge-success">Positive</span>
                                    @elseif($single_order->feedback['feedback']==2)
                                        <span class="badge badge-danger">Negative</span>
                                    @endif
                                </div>
                            @endif
                            @if($single_order->feedback!=null && $single_order->feedback['feedback']==2)
                                <div class="col-md-6 mb-2">
                                    <p>Order Feedback Comment: {{$single_order->feedback['review']}}</p>
                                </div>
                            @endif
                            {{--    <div class="col-md-12 mt-3">
                                    <h5>Customer Details</h5>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <b>Customer Name: </b> <span style="font-size: 15px; text-decoration: underline">
                                    @if($single_order->user_id == 0)
                                            {{$single_order->user_first_name}} {{$single_order->user_last_name}}
                                        @endif
                                        @if($single_order->user_id != 0)
                                            <a target="_blank" href="{{url('/')}}/users/customer-details/{{$single_order->user_id}}">
                                            {{$single_order->user_first_name}} {{$single_order->user_last_name}}
                                        </a>
                                        @endif
                                </span>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <b>Customer Type: </b>                <span>{{$single_order->user_id ? 'Logged-in Customer' : 'Guest Customer'}}</span>

                                </div>
                                <div class="col-md-6 mb-2">
                                    <b>Customer Number: </b>                 <span style="font-size: 15px">{{$single_order->user_number}}</span>

                                </div>
                                <div class="col-md-6 mb-2">
                                    <b>Customer Email: </b>                 <span> {{$single_order->user_email}}</span>

                                </div>--}}
                    </div>
            </div>
            @if($single_order->order_type == 2)
                <div class="col-md-12 @if(Auth::guard('admin')->user()->type ==1) card mt-3 @else mt-2 @endif">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <h5>Delivery Information</h5>
                        </div>
                        <div class="col-md-6 mb-2">
                            Delivery Address:<span>{{$single_order->delivery_address}}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            Delivery Notes: <span> {{$single_order->delivery_notes}}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            Postmates Delivery ID: <span> {{$single_order->postmates_delivery_id}}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            Postmates Tracking URL: <span> <a href="{{$single_order->postmates_tracking_url}}"
                                                              target="_blank">Open</a></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            Delivery Status: <span class="text-capitalize">{{$single_order->delivery_status}}</span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-md-12 @if(Auth::guard('admin')->user()->type ==1) card mt-3 @else mt-2 @endif">
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h5>Customer Details</h5>
                    </div>
                    <div class="col-md-6 mb-2">
                        Customer Name: <span class="font-16" style="text-decoration: underline">
                    @if($single_order->user_id == 0)
                                <b>{{$single_order->user_first_name}} {{$single_order->user_last_name}}</b>
                            @endif
                            @if($single_order->user_id != 0)
                                <a @if(Auth::guard('admin')->user()->type ==1)target="_blank"
                                   @endif href="{{url('/')}}/users/customer-details/{{$single_order->user_id}}">
                            {{$single_order->user_first_name}} {{$single_order->user_last_name}}
                        </a>
                            @endif
                </span>
                    </div>
                    <div class="col-md-6 mb-2">
                        Customer Type:<span>{{$single_order->user_id ? 'Logged-in Customer' : 'Guest Customer'}}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        Phone Number: <span style="font-size: 15px"><b
                                class="font-16"> {{$single_order->user_number}}</b></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        Customer Email:<span> {{$single_order->user_email}}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        Customer Address:<span> {{$single_order->address_line_1}}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12 @if(Auth::guard('admin')->user()->type ==1) card mt-3 @else mt-2 @endif  pb-2">
                <div class="row">
                    @if($single_order->transaction!=null)
                        <div class="col-md-12 mt-3">
                            <h5>Transaction Details</h5>
                        </div>
                        <div class="col-md-6 mb-2">Transaction ID: {{$single_order->transaction->order_code}}
                        </div>
                        <div class="col-md-6 mb-2">Amount:
                            ${{$single_order->transaction->amount ? $single_order->transaction->amount : 0}}</div>
                        {{--                    <div class="col-md-6 mb-2"><b>Customer Order Code--}}
                        {{--                            :</b> {{$single_order->transaction->customer_order_code}}</div>--}}
                        {{--                    <div class="col-md-6 mb-2"><b>Card Holder Name :</b> {{$single_order->transaction->name}}</div>--}}
                        <div class="col-md-6 mb-2">Card Number: {{$single_order->transaction->masked_card_number}}
                        </div>
                        {{--                    <div class="col-md-6 mb-2"><b>Card Type :</b> {{$single_order->transaction->card_class}}</div>--}}
                        {{--                    <div class="col-md-6 mb-2"><b>Card Scheme Name / Type--}}
                        {{--                            :</b> {{$single_order->transaction->card_scheme_name}}--}}
                        {{--                        / {{$single_order->transaction->card_scheme_type}}</div>--}}
                        <div class="col-md-6 mb-2">Payment Status: {{$single_order->transaction->payment_status}}
                        </div>
                    @endif
                </div>
            </div>
            @if(sizeof($single_order->refund))
                <div class="col-md-12 @if(Auth::guard('admin')->user()->type ==1) card @endif mt-3  pb-2">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <h5>Refund Details</h5>
                        </div>
                        @foreach($single_order->refund as $refund)
                            <div class="col-md-6 mb-2">Transaction ID: {{$refund->transaction_id}}
                            </div>
                            <div class="col-md-6 mb-2">Reference Number: {{$refund->reference_number}}
                            </div>
                            <div class="col-md-6 mb-2">Refund Amount: {{$refund->refund_amount}}
                            </div>
                            <div class="col-md-6 mb-2">Status: {{$refund->transaction_status}}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(sizeof($single_order->orderDetails))
                <div class="col-md-12 @if(Auth::guard('admin')->user()->type ==1) card mt-3 @else mt-2 @endif">
                    {{--    @foreach($single_order->orderDetails as $mainItem)
                            <div class="card card-ecommerce-3 o-hidden mb-4">
                                <div class="d-flex flex-column flex-sm-row">
                                    <div class="">
                                        <img style="max-width: 395px;height: 450px;"
                                             src="{{asset('public/storage/'.$mainItem->item_image)}}"
                                             alt="">
                                    </div>
                                    <div class="flex-grow-1 p-3">
                                        <div class="row">
                                            <div class="col-md-8 text-left">
                                                <p><b>Name: </b> {{$mainItem->item_name}} </p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                1 X ${{$mainItem->item_price}}
                                                = ${{1*$mainItem->item_price}}
                                            </div>
                                            @if(sizeof($mainItem->orderItems))
                                                @foreach($mainItem->orderItems as $item)
                                                    <div class="col-md-6">
                                                        <div class="card mb-4">
                                                            <div class="d-flex flex-column flex-sm-row">
                                                                --}}{{--<div class="">
                                                                    <img class="card-img-left"
                                                                         style="width: 100px;height: 100px;"
                                                                         src="{{asset('public/storage/'.$item->item_image)}}"
                                                                         alt="">
                                                                </div>--}}{{--
                                                                <div class="flex-grow-1 pt-2 pl-2">
                                                                    <p class="pb-0 mb-0">{{$item->item_name}}</p>
                                                                    <p class="pt-0 mt-0">Price : {{$item->item_count}} X
                                                                        ${{$item->item_price}}
                                                                        = ${{$item->item_count*$item->item_price}}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach--}}
                    <div class="row p-3">
                        <div class="col-md-6 mb-1 p-0">
                            <h5 class="w-50">Ordered Items</h5>
                        </div>
                        <div class="col-md-6 mb-1 p-0">
                            @if($single_order->total_amount > 0 && $single_order->payment_status == 1)
                                <button id="refundBtn" data-target="#itemRefund" data-toggle="modal"
                                        class="btn btn-primary float-right">Refund Selected Items
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            @if(Auth::guard('admin')->user()->type ==1)
                                <p><b>Select individual item(s) to refund for individual items OR select all to refund
                                        entire
                                        order.</b></p>
                            @else
                                <p><b>Select individual item(s) to refund for individual items.</b></p>
                            @endif

                            <div class="table-responsive pr-3 pl-3">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th style="width: 170px;" scope="col">Image</th>
                                        <th scope="col">Name</th>
                                        <th scope="col" class="text-right pr-4">Price</th>
                                        <th scope="col">Customization</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($single_order->orderDetails as $mainItem)
                                        <tr>
                                            <td class="text-center"><img alt="" height="100"
                                                                         style="width: 100px;height: 100px;"
                                                                         src="{{asset('public/storage/'.$mainItem->item_image)}}">
                                            </td>
                                            <td>{{$mainItem->item_name}} x {{$mainItem->item_count}}<br/>
                                                <span class="badge badge-danger">{{$mainItem->is_refunded == 1 ? 'REFUNDED '.$mainItem->refunded_qty.' QUANTITY(S)':''}}</span>
                                                <span class="badge badge-info">{{$mainItem->item_flag == 1 ? 'REWARD ITEM':''}}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                @if($mainItem->is_refunded == 1)
                                                    <p><strike><span>${{$mainItem->item_price * $mainItem->item_count}}</span></strike></p>
                                                    <span>${{$mainItem->item_price * ($mainItem->item_count-$mainItem->refunded_qty)}}</span>
                                                @endif
                                                @if($mainItem->is_refunded == 0)
                                                    <span>${{$mainItem->item_price * $mainItem->item_count}}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(sizeof($mainItem->orderItems))
                                                    @foreach($mainItem->orderItems as $k => $item)
                                                        <div class="row pl-4">
                                                            {{str_replace('(8oz)','',$item->item_name)}}
                                                            ({{$item->item_price ? '$'.$item->item_price : ''}}
                                                            X {{$mainItem->item_count}}

                                                            )
                                                        </div>
                                                        {{--@if(sizeof($details['order_item']) != $k+1),@endif--}}
                                                    @endforeach
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{--  @foreach($single_order->orderDetails as $mainItem)
                                  <div class="col-md-6 mb-3">
                                      <div class="row">
                                          <div class="col-md-3">
                                              <img height="150" src="{{asset('public/storage/'.$mainItem->item_image)}}">
                                          </div>
                                          <div class="col-md-9 p-0">
                                              <div class="row">
                                                  <div class="col-md-8"><h4>{{$mainItem->item_name}}</h4>
                                                  </div>
                                                  <div class="col-md-4">${{$mainItem->item_price}}</div>
                                              </div>
                                              @if(sizeof($mainItem->orderItems))
                                                  @foreach($mainItem->orderItems as $k => $item)
                                                      <div class="row pl-3">
                                                          {{$item->item_name}} ({{$item->item_count}}
                                                          ,${{$item->item_price}})
                                                      </div>
                                                      --}}{{--@if(sizeof($details['order_item']) != $k+1),@endif--}}{{--
                                                  @endforeach
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              @endforeach--}}
                        </div>
                    </div>
                </div>
        </div>
        @endif
        {{--  For desktop --}}
        @if($single_order->total_amount > 0)
            <div class="col-md-12 pt-0 pb-2 card"
                 style="padding-top: 5px;box-shadow: none;border-top: 1px solid #ccc;border-radius: 0;">
                <div class="row m-0">
                    <div class="col-md-10 text-right pr-4">
                        <h6>Sub Total</h6>
                        @if($single_order->discount_amount != 0.00)
                            <h6>Discount
                                @if($single_order->coupon_id)
                                    <span class="badge badge-danger">Free Entry</span>
                                @endif
                                @if($single_order->bonus_id)
                                    <span class="badge badge-danger">Bonus</span>
                                @endif
                            </h6>
                        @endif
                        <h6>Tax</h6>
                    </div>
                    <div class="col-md-2 text-right pr-4">
                        @if($single_order->payment_status!=2)
                            <h6 class="pr-3">${{$single_order->order_total}}</h6>
                        @endif
                        @if($single_order->payment_status==2)
                            <h6 class="pr-3"><strike><span style="color: #b3b7bb">${{$single_order->order_total}}</span></strike> ${{round($single_order->order_total-$single_order->refund[0]->sub_total_refund,2)}}</h6>
                        @endif
                        @if($single_order->discount_amount != 0.00 && $single_order->payment_status!=2)
                            <h6 class="pr-3">-${{$single_order->discount_amount}}</h6>
                        @endif
                        @if($single_order->discount_amount != 0.00 && $single_order->payment_status==2)
                            <h6 class="pr-3"><strike><span style="color: #b3b7bb">${{$single_order->discount_amount}}</span></strike> ${{round($single_order->discount_amount - $single_order->refund[0]->discount_adjust,2)}}</h6>
                        @endif
                        @if($single_order->payment_status!=2)
                            <h6 class="pr-3">${{$single_order->total_tax}}</h6>
                        @endif
                        @if($single_order->payment_status==2)
                            <h6 class="pr-3"><strike><span style="color: #b3b7bb">${{$single_order->total_tax}}</span></strike> ${{round($single_order->total_tax - $single_order->refund[0]->tax_refund,2)}}</h6>
                        @endif
                    </div>
                </div>
                @if($single_order->delivery_fee > 0)
                    <div class="row mt-0 pt-0 pr-4">
                        <div class="col-md-10 text-right">
                            <h6>Delivery Fee</h6>
                        </div>
                        <div class="col-md-2 text-right">
                            <h6 class="pr-3">${{$single_order->delivery_fee}}</h6>
                        </div>
                    </div>
                @endif
                <hr>
                <div class="row mt-0 pt-0 pr-4">
                    <div class="col-md-10 text-right">
                        <h6>Total</h6>
                    </div>
                    <div class="col-md-2 text-right">
                        @if($single_order->payment_status!=2)
                            <h6 class="pr-3">${{$single_order->total_amount}}</h6>
                        @endif
                        @if($single_order->payment_status==2)
                            <h6 class="pr-3"><strike><span style="color: #b3b7bb">${{$single_order->total_amount}}</span></strike> ${{round($single_order->total_amount-$single_order->refund[0]->refund_amount,2)}}</h6>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{--    For ipad --}}
    </div>
    <div class="modal fade" id="markPickedModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Picked?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{url('/orders/mark-order-picked/' . $single_order->order_id)}}">
                        @csrf
                        <button type="submit" class="btn btn-success">Yes</button>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editTimeModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add More Time (In Minutes)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <select class="form-control moreTime" id="preparation_time" required>
                                    <option value="">Select</option>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="25">25</option>
                                    <option value="30">30</option>
                                    <option value="35">35</option>
                                    <option value="40">40</option>
                                    <option value="45">45</option>
                                    <option value="50">50</option>
                                    <option value="55">55</option>
                                    <option value="60">60</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="editPreparationTime()" id="edit-btn">Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="refundModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Refund</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="radio radio-outline-primary">
                                        <input type="radio" id="partial_refund_yes" name="partial_refund" value="1">
                                        <span>Partial Refund</span>
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="radio radio-outline-primary">
                                        <input type="radio" id="partial_refund_no" name="partial_refund" value="0"
                                               checked>
                                        <span>Full Refund</span>
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Refund Amount</label>
                                <input class="form-control" type="text" name="refund_amount" id="refund_amount"
                                       placeholder="Refund Amount" disabled/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="refundActionBtn" class="btn btn-primary" onclick="refundPayment()">Refund
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemRefund" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <form action="{{route('partial-item-refund')}}" method="post">
                <input type="hidden" value="{{$single_order->order_id}}" name="order_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Refund Item(s)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive pr-3 pl-3">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    
                                    <th scope="col">
                                     
                                        <input type="checkbox" name="check_all[]" id="check_all" value="1" />
                                   
                                    </th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col" class="text-right pr-4">Quantity</th>
                                </tr>
                                </thead>
                                <tbody>
                                @csrf
                                @foreach($single_order->orderDetails as $mainItem)
                                    <tr>
                                        <td>
                                            @if($mainItem->is_refunded == 0 && $mainItem->item_flag == 0)
                                                <input type="hidden" value="0" name="item_check[{{$mainItem->order_detail_id}}]" />
                                                <input type="checkbox" name="item_check[{{$mainItem->order_detail_id}}]"
                                                       value="1"/>
                                            @endif
                                        </td>
                                        <td>{{$mainItem->item_name}}<br/>
                                            <span class="badge badge-danger">{{$mainItem->is_refunded == 1 ? 'REFUNDED '.$mainItem->refunded_qty.' QUANTITY(S)':''}}</span>
                                        </td>
                                        <td class="text-right pr-4">
                                            <input type="number" max="{{$mainItem->item_count}}"
                                                   value="{{$mainItem->item_count}}" min="1" minlength="1"
                                                   maxlength="{{$mainItem->item_count}}"
                                                   name="item_qty[{{$mainItem->order_detail_id}}]" required
                                                    {{$mainItem->is_refunded == 1 ? 'disabled':''}}
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            Refund
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    @if(Auth::guard('admin')->user()->type ==2)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"
                crossorigin="anonymous"></script>
        <script>
            $(document).ready(function () {
                $('body').css('position', 'fixed');
                $('body').css('width', '100%');

                $('#main-cont').slimScroll({
                    height: '72vh',
                    touchScrollStep: 80
                });
            });
        </script>
    @endif
    <script>
        document.ontouchmove = function (event) {
            event.preventDefault();
        }
        $(document).ready(function () {
            $("#check_all").on("change", function () {
                if ($("#check_all").is(
                    ":checked")) {
                    $("input[name='item_check[]']").prop('checked', true);
                } else {
                    $("input[name='item_check[]']").prop('checked', false);
                }
                // getSelecteditems();
            })

            $("input[name='item_check[]']").change(function () {
                if ($("input[name='item_check[]']:checked").length == $("input[name='item_check[]']").length) {
                    $('#check_all').prop('checked', true);
                } else {
                    $('#check_all').prop('checked', false);
                }
                // getSelecteditems();
            });

            $('input[name="partial_refund"]').change(function () {
                if ($('input[name="partial_refund"]:checked').val() == 1) {
                    $('#refund_amount').prop('disabled', false);
                } else {
                    $('#refund_amount').prop('disabled', true);
                }
            });
        })

        // function getSelecteditems() {
        //     if ($("input[name='item_check[]']:checked").length > 0) {
        //         $("#refundBtn").show();
        //     } else {
        //         $("#refundBtn").hide();
        //     }
        // }

        let selectedOrderId;

        function editTime(orderId) {
            $('#editTimeModal').modal('show');
            selectedOrderId = orderId;
        }

        function editPreparationTime() {
            $("#edit-btn").prop('disabled', true);
            axios.post(baseUrl + 'edit-order-preparation-time', {
                'order_id': selectedOrderId,
                'preparation_time': $('#preparation_time').val()
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

        function printBill(data) {
            $.ajax({
                type: "POST",
                url: 'http://localhost/print-php/example/receipt-with-logo.php',
                data: JSON.stringify(data),
                contentType: "application/json",
                dataType: "json",
                success: function (result) {

                }
            });
        }

        function getSingleOrder(orderId) {
            axios.get(baseUrl + 'get-single-order/' + orderId).then(function (response) {
                printBill(response.data.response);
            });
        }

        function openRefundModal(orderId, refundAmount) {
            $('#refundModal').modal('show');
            selectedOrderId = orderId;
            $('#refund_amount').val(refundAmount);
        }

        function refundPayment() {
            // Swal.fire({
            //     title: 'Are you sure?',
            //     text: "You want to refund this order.",
            //     type: 'warning',
            //     showCancelButton: true,
            //     confirmButtonColor: '#3085d6',
            //     cancelButtonColor: '#d33',
            //     confirmButtonText: 'Yes, refund it!'
            // }).then(function (result) {
            //     if (result.value) {
            //         window.location.href = baseUrl + 'order-refund/' + orderId;
            //     }
            // })
            $('#refundActionBtn').prop('disabled', true);
            axios.post(baseUrl + 'order-refund', {
                order_id: selectedOrderId,
                refund_amount: $('#refund_amount').val(),
                refund_type: $('input[name="partial_refund"]:checked').val()
            }).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                $('#refundActionBtn').prop('disabled', false);
                setTimeout(function () {
                    window.location.reload();
                }, 200);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
                $('#refundActionBtn').prop('disabled', false);
            });
        }

    function refundPaymentPartial(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to refund this order.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, refund it!'
  }).then(function(result) {
    if (result.value) {
      var checkValues = $("input[name='item_check[]']:checked").map(function() {
        return $(this).val();
      }).get();
      
      if (checkValues.length === 0) {
        // Only one item is selected, perform partial refund for that item
        axios.post(baseUrl + 'partial-order-refund', {
          order_id: orderId,
          items: checkValues[0]
        }).then(function(response) {
          toastr.success(response.data.message, 'Success', {
            timeOut: '3000',
            positionClass: 'toast-bottom-right'
          });
          setTimeout(function() {
            window.location.reload();
          }, 200);
        }).catch(function(error) {
          toastr.error(error.response.data.message, 'Required!', {
            timeOut: '3000',
            positionClass: 'toast-bottom-right'
          });
        });
      } else if (checkValues.length > 1) {
        // Multiple items or "check_all" checkbox is selected, perform partial refund for all items
        axios.post(baseUrl + 'partial-order-refund', {
          order_id: orderId,
          items: checkValues
        }).then(function(response) {
          toastr.success(response.data.message, 'Success', {
            timeOut: '3000',
            positionClass: 'toast-bottom-right'
          });
          setTimeout(function() {
            window.location.reload();
          }, 200);
        }).catch(function(error) {
          toastr.error(error.response.data.message, 'Required!', {
            timeOut: '3000',
            positionClass: 'toast-bottom-right'
          });
        });
      } else {
        // No items are selected
        toastr.warning('Please select at least one item for refund.', 'Warning', {
          timeOut: '3000',
          positionClass: 'toast-bottom-right'
        });
      }
    }
  });
}


        // function refundPaymentPartial(orderId) {
        //     Swal.fire({
        //         title: 'Are you sure?',
        //         text: "You want to refund this order.",
        //         type: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Yes, refund it!'
        //     }).then(function (result) {
        //         if (result.value) {
        //             var checkValues;
        //             if($("input[name='item_check[]']:checked").length == 1){
        //                 checkValues = $("input[name='item_check[]']:checked").val();
        //             }else{
        //                 checkValues = $("input[name='item_check[]']:checked").map(function () {
        //                     return $(this).val();
        //                 }).get();
        //             }
        //             console.log(checkValues);
        //             axios.post(baseUrl + 'partial-order-refund', {
        //                 order_id: orderId,
        //                 items: checkValues
        //             }).then(function (response) {
        //                 toastr.success(response.data.message, "Success", {
        //                     timeOut: "3000",
        //                     positionClass: "toast-bottom-right"
        //                 });
        //                 setTimeout(function () {
        //                     window.location.reload();
        //                 }, 200);
        //             }).catch(function (error) {
        //                 toastr.error(error.response.data.message, "Required!", {
        //                     timeOut: "3000",
        //                     positionClass: "toast-bottom-right"
        //                 })
        //             });
        //         }
        //     })
        // }
    </script>
@endsection
