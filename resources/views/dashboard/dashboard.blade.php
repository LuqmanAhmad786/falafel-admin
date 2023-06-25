@extends('layouts.master')

@section('page-css')
    <style>
        .select2-container {
            width: 100% !important;
            padding: 0;
        }

        .td-width {
            min-width: 183px;
        }

        .colmd-4-right {
            height: 301px;
            /*background: #B9002D;*/
            background: #a92219;
            color: #ffff !important;
        }

        .colmd-4-right-first {
            background: #a92219;
            color: #ffff !important;
        }

        .colmd-4-right-first h2, h3, h4, h6, hr {
            color: #ffff !important;
            font-weight: 500;
            font-size: 22px;
        }

        .colmd-4-right h2, h3, h4, h6, hr {
            color: #ffff !important;
        }

        /*.colmd-4-right-first h2 {
            border-bottom: 1px solid #ffff !important;
            padding-bottom: 28px;
        }*/

        .colmd-4-right p {
            font-weight: inherit;
            font-size: 17px;
            /* border-bottom: 1px solid #ffff !important;
             padding-bottom: 28px;*/
        }

        .flex-grow-1 h5 {
            color: #000000;
        }

        .green-bg {
            background-color: #94c454;
            color: #ffffff !important;
        }

        .blue-bg {
            background-color: #25376a;
            color: #ffffff !important;
        }

        .orange-bg {
            background-color: #eba239;
            color: #ffffff !important;
        }

        .light-green-bg {
            background-color: #23adad;
            color: #ffffff !important;
        }

        .dashboard .card-icon-bg-primary [class^=i-] {
            /*color: #484848;*/
            color: #ffffff;

            font-size: 50px;
        }

        .dashboard .card-icon-bg-primary:hover [class^=i-] {
            color: #ffffff;
            font-size: 50px;
        }

        .dash-box-bg {
            background-color: #ffffff;
            color: #484848 !important;
            /*border: 1px solid #484848;*/
        }

        .dash-box-bg.Button {
            background-color: #b90006;
            color: #ffffff !important;
            /*border: 1px solid #b90006;*/
        }

        .dash-box-bg.Button:hover {
            background-color: #b90006;
            color: #ffffff !important;
            box-shadow: 1px 8px 20px grey;
            -webkit-transition: box-shadow .3s ease-in;
            /*border: 1px solid #b90006;*/
        }

        .dashboard .card-icon-bg-primary:hover {
            color: #fff;
        }

        .card-icon-bg .card-body p-0 .content_dw {
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .separator {
            display: flex;
            align-items: center;
            text-align: center;
        }

        .separator::before, .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #000;
        }

        .separator::before {
            margin-right: .25em;
        }

        .separator::after {
            margin-left: .25em;
        }

        #earning_graph select {
            padding: 5px !important;
            font-size: 14px !important;
            line-height: 1 !important;
            border: 0;
            border-bottom: 1px solid #000;
            border-radius: 0;
            height: 34px !important;
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat right transparent !important;
            -webkit-appearance: none !important;
            width: 277px;
        }

        .highcharts-exporting-group path {
            fill: rgb(255, 255, 255) !important;
            d: path("M 6 6.5 L 20 6.5 M 6 11.5 L 20 11.5 M 6 16.5 L 20 16.5");
            stroke: rgb(255, 255, 255) !important;
            stroke-width: 3;
        }

        .badge-light {
            color: #f6f6f6;
        }

        .card {
            padding: 15px;
        }

        .hide-loader {
            display: none;
        }

        .contacts_body::-webkit-scrollbar {
            height: 12px;
            width: 10px;
            background-color: #cccccc;
        }

        .contacts_body::-webkit-scrollbar-thumb {
            background: grey;
            -webkit-border-radius: 10px;
            -webkit-box-shadow: 0px 1px 2px rgba(255, 255, 255, .6);
        }

        .contacts_body::-webkit-scrollbar-corner {
            background: #CCCCCC;
        }

        .contacts_body {
            scrollbar-width: auto
        }

        .scrollable {
            overflow-y: auto;
            min-height: 480px;
            height: 480px;
            overflow-x: hidden;
        }

        .card-title {
            margin-bottom: 0.5rem;
        }

        #no_of_orders {
            max-width: 88vw;
            min-width: 78vw;
            width: 78vw;
        }

        #found th.sort > i::before {
            content: "\f0bf" !important;
        }

        #found th.sort a {
            color: #63191b !important;
        }

    </style>
@endsection

@section('main-content')
    <div class="card card-head mb-3" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Dashboard</h2>
            </div>
            <div class="col-md-6">
                <div style="margin: auto" id="select-restaurant">
                    <div class="row">
                        @if(Auth::guard('admin')->user()->type ==1)
                            <div class="col-md-5 text-right mt-2 p-0">
                                <h5>Change Location:</h5>
                            </div>
                        @else
                            <div class="col-md-5">
                                <h5 style="color: #ffffff">Location:</h5>
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
    <div class="col-md-12">
        <div class="row dashboard">
            <div class="col-lg-6 col-md-6 col-sm-6">
                <a target="_blank" href="{{route('order-list')}}">
                    <div class="card card-icon-bg dash-box-bg Button card-icon-bg-primary o-hidden">
                        <div class="card-body p-0 text-center">
                            <i class="i-Checkout-Basket"></i>
                            <div class="content">
                                <p class="mt-2 mb-0">Orders</p>
                                <p class="text-24 line-height-1 mb-2">{{$details['total_orders'] ? $details['total_orders']: 0}}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                <a target="_blank" href="{{route('customers')}}">
                    <div class="card dash-box-bg Button card-icon-bg card-icon-bg-primary o-hidden">
                        <div class="card-body p-0 text-center">
                            <i class="i-Add-User"></i>
                            <div class="content">
                                <p class="mt-2 mb-0">Users</p>
                                <p class="text-24 line-height-1 mb-2">{{$details['total_users'] ? $details['total_users']: 0}}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
{{--            <div class="col-lg-4 col-md-6 col-sm-6">--}}
{{--                <a target="_blank" href="{{route('side-menu-list')}}">--}}
{{--                    <div class="card card-icon-bg dash-box-bg Button card-icon-bg-primary o-hidden">--}}
{{--                        <div class="card-body p-0 text-center">--}}
{{--                            <i class="i-Shopping-Cart"></i>--}}
{{--                            <div class="content">--}}
{{--                                <p class="mt-2 mb-0">Items</p>--}}
{{--                                <p class="text-24 line-height-1 mb-2">{{$details['total_items'] ? $details['total_items']: 0}}</p>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </a>--}}
{{--            </div>--}}
            {{--        <div class="col-lg-3 col-md-6 col-sm-6">--}}
            {{--            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">--}}
            {{--                <div class="card-body p-0 text-center">--}}
            {{--                    <i class="i-Download"></i>--}}
            {{--                    <div class="content_dw">--}}
            {{--                        <p class="mt-2 mb-0">App Downloads</p>--}}
            {{--                        <p class="text-24 line-height-1 mb-2">0</p>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
            {{--        </div>--}}
            <div class="col-md-12 col-lg-12 mt-3">
                <div class="card">
                    <div class="row card-body p-0 text-center" id="stats_row">
                        <div class="col-md-9 text-left">
                        </div>
                        <div class="col-md-3 text-right" id="sales_graph">
                            <input type="text" name="daterange"
                                   value="{{date('m/d/Y', strtotime('-7 days')).' - '.date('m/d/Y')}}" id="dashboard_stats"
                                   class="form-control" onchange="onStatsChange()"/>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">
                                <div class="card-body p-0 row">
                                    <div class="col-auto icon-dash">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="col text-left">
                                        <p class="m-0 p-0">Gross Revenue</p>
                                        <p class="m-0 p-0" style="font-size: 20px" id="gross_stat">
                                            ${{number_format($details['gross_revenue']['total_amount'], 2, '.', ',')}}</p>
                                    </div>
                                    <div class="col-md-8">
                                        {{--                        <p class="m-0 p-0">Previous Year: $86,663</p>--}}
                                    </div>
                                    {{--                    <div class="col-md-4 text-right ">--}}
                                    {{--                        <p class="badge badge-danger" style="font-size: 15px">$ -26%</p>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">
                                <div class="card-body p-0 row">
                                    <div class="col-auto icon-dash">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="col text-left">
                                        <p class="m-0 p-0">Discount</p>
                                        <p class="m-0 p-0" style="font-size: 20px" id="discount_stat">
                                            ${{number_format($details['discount_amount']['discount_amount'], 2, '.', ',')}}</p>
                                    </div>
                                    <div class="col-md-8">
                                        {{--                        <p class="m-0 p-0">Previous Year: $ 0</p>--}}
                                    </div>
                                    {{--                    <div class="col-md-4 text-right ">--}}
                                    {{--                        <p class="badge badge-success" style="font-size: 15px">$ -0%</p>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">
                                <div class="card-body p-0 row">
                                    <div class="col-auto icon-dash">
                                        <i class="fas fa-undo"></i>
                                    </div>$details
                                    <div class="col text-left">
                                        <p class="m-0 p-0">Refunds</p>
                                        <p class="m-0 p-0" style="font-size: 20px" id="refund_stat">
                                            ${{$details['refund_amount']['refund_amount'] ? number_format($details['refund_amount']['refund_amount'], 2, '.', ','): 0}}</p>
                                    </div>
                                    <div class="col-md-8">
                                        {{--                        <p class="m-0 p-0">Previous Year: $ 0.00</p>--}}
                                    </div>
                                    {{--                    <div class="col-md-4 text-right ">--}}
                                    {{--                        <p class="badge badge-light" style="font-size: 15px">$ -0%</p>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">
                                <div class="card-body p-0 row">
                                    <div class="col-auto icon-dash">
                                        <i class="fas fa-at"></i>
                                    </div>
                                    <div class="col text-left">
                                        <p class="m-0 p-0">Taxes</p>
                                        <p class="m-0 p-0" style="font-size: 20px" id="tax_stat">
                                            ${{number_format($details['total_tax']['total_tax'], 2, '.', ',')}}</p>
                                    </div>
                                    <div class="col-md-8">
                                        {{--                        <p class="m-0 p-0">Previous Year: $ 0.00</p>--}}
                                    </div>
                                    {{--                    <div class="col-md-4 text-right ">--}}
                                    {{--                        <p class="badge badge-light" style="font-size: 15px">$ -0%</p>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">
                                <div class="card-body p-0 row">
                                    <div class="col-auto icon-dash">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="col text-left">
                                        <p class="m-0 p-0">Today's Sales</p>
                                        <p class="m-0 p-0" style="font-size: 20px">
                                            ${{$details['today_sales']['total_amount'] ? number_format($details['today_sales']['total_amount'], 2, '.', ','): 0}}</p>
                                    </div>
                                    <div class="col-md-8">
                                        {{--                        <p class="m-0 p-0">Previous Year: $ 0.00</p>--}}
                                    </div>
                                    {{--                    <div class="col-md-4 text-right ">--}}
                                    {{--                        <p class="badge badge-light" style="font-size: 15px">$ -0%</p>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">
                                <div class="card-body p-0 row">
                                    <div class="col-auto icon-dash">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="col text-left">
                                        <p class="m-0 p-0">Yesterday's Sales</p>
                                        <p class="m-0 p-0" style="font-size: 20px">
                                            ${{$details['yesterday_sales']['total_amount'] ? number_format($details['yesterday_sales']['total_amount'], 2, '.', ','): 0}}</p>
                                    </div>
                                    <div class="col-md-8">
                                        {{--                        <p class="m-0 p-0">Previous Year: $ 0.00</p>--}}
                                    </div>
                                    {{--                    <div class="col-md-4 text-right ">--}}
                                    {{--                        <p class="badge badge-light" style="font-size: 15px">$ -0%</p>--}}
                                    {{--                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--        <div class="col-md-4 mt-3">--}}
            {{--            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">--}}
            {{--                <div class="card-body p-0 row">--}}
            {{--                    <div class="col-md-12">--}}
            {{--                        <p class="m-0 p-0">Shipping</p>--}}
            {{--                    </div>--}}
            {{--                    <div class="col-md-8">--}}
            {{--                        <p class="m-0 p-0" style="font-size: 20px">$ 150.00</p>--}}
            {{--                        <p class="m-0 p-0">Previous Year: $ 0.00</p>--}}
            {{--                    </div>--}}
            {{--                    <div class="col-md-4 text-right ">--}}
            {{--                        <p class="badge badge-light" style="font-size: 15px">$ -0%</p>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
            {{--        </div>--}}
            {{--        <div class="col-md-4 mt-3">--}}
            {{--            <div class="card card-icon-bg dash-box-bg card-icon-bg-primary o-hidden">--}}
            {{--                <div class="card-body p-0 row">--}}
            {{--                    <div class="col-md-12">--}}
            {{--                        <p class="m-0 p-0">Net Revenue</p>--}}
            {{--                    </div>--}}
            {{--                    <div class="col-md-8">--}}
            {{--                        <p class="m-0 p-0" style="font-size: 20px">$ 32,897.00</p>--}}
            {{--                        <p class="m-0 p-0">Previous Year: $ 41,008.22</p>--}}
            {{--                    </div>--}}
            {{--                    <div class="col-md-4 text-right ">--}}
            {{--                        <p class="badge badge-danger" style="font-size: 15px">$ -17%</p>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
            {{--        </div>--}}
            {{-- <div class="col-lg-6 col-md-6 mt-3">
                 <div class="card">
                     <div class="row card-body p-0 text-center">
                         <div class="col-md-9 text-left">
                             <h4 class="text-primary" style="color: #a92219;font-size: 28px;text-transform: capitalize;">
                                 Earnings:</h4>
                         </div>
                         <div class="col-md-3 text-right" id="sales_graph">
                             <select id="sales_type" class="form-control selectpicker" onchange="getSales()">
                                 <option value="0">Current Day</option>
                                 <option value="1">Yesterday</option>
                                 <option value="2" selected>Last 7 Days</option>
                                 <option value="7">This month</option>
                                 <option value="3">Last month</option>
                                 <option value="4">Last 6 months</option>
                                 <option value="5">Current Year</option>
                                 <option value="6">All Years</option>
                             </select>
                         </div>
                         <div class="col-md-12 loader" id="loader">
                             <img alt="" src="./public/assets/images/loader.gif">
                         </div>
                         <div class="col-md-12 mt-3" id="total_earnings"></div>
                     </div>
                 </div>
             </div>
             <div class="col-lg-6 col-md-6 mt-3">
                 <div class="card">
                     <div class="row card-body p-0 text-center">
                         <div class="col-md-9 text-left">
                             <h4 class="text-primary" style="color: #a92219;font-size: 28px;text-transform: capitalize;">
                                 Orders:</h4>
                         </div>
                         <div class="col-md-3 text-right" id="sales_graph">
                             <select id="sales_type" class="form-control selectpicker" onchange="getTotalOrders()">
                                 <option value="0">Current Day</option>
                                 <option value="1">Yesterday</option>
                                 <option value="2" selected>Last 7 Days</option>
                                 <option value="7">This month</option>
                                 <option value="3">Last month</option>
                                 <option value="4">Last 6 months</option>
                                 <option value="5">Current Year</option>
                                 <option value="6">All Years</option>
                             </select>
                         </div>
                         <div class="col-md-12 loader" id="loaderOrders">
                             <img alt="" src="./public/assets/images/loader.gif">
                         </div>
                         <div class="col-md-12 mt-3" id="total_orders"></div>
                     </div>
                 </div>
             </div>--}}
            <div class="col-md-12 col-lg-12 mt-3">
                <div class="card">
                    <div class="row card-body p-0 text-center">
                        <div class="col-md-9 text-left p-0">
                            <div class="card-title text-primary">
                                <div class="col-md-12 ">
                                    Total Sales:
                                </div>
                            </div>
                        </div>
                        {{--                    <div class="col-md-5 text-right">--}}
                        {{--                        <div class="dropdown float-right">--}}
                        {{--                            <button class="btn btn-secondary dropdown-toggle btn-drop" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
                        {{--                                Filter By--}}
                        {{--                            </button>--}}
                        {{--                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">--}}
                        {{--                                <a class="dropdown-item" href="#">Today</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Yesterday</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Last Week</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Last Month</a>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        {{--                    </div>--}}
                        <div class="col-md-3 text-right" id="sales_graph">
                            <i class="icon-fields fas fa-calendar-alt"></i>
                            <input type="text" id="sales_type" name="daterange"
                                   value="{{date('m/d/Y', strtotime('-7 days')).' - '.date('m/d/Y')}}" class="form-control"
                                   onchange="f()"/>

                            {{--   <select id="sales_type" class="form-control selectpicker" onchange="f()">
                                   <option value="0">Current Day</option>
                                   <option value="1">Yesterday</option>
                                   <option value="2" selected>Last 7 Days</option>
                                   <option value="7">This month</option>
                                   <option value="3">Last month</option>
                                   <option value="4">Last 6 months</option>
                                   <option value="5">Current Year</option>
                                   <option value="6">All Years</option>
                               </select>--}}
                        </div>
                        <div class="col-md-12 loader" id="loaderLineWithColmn">
                            <img alt="" src="./public/assets/images/loader.gif">
                        </div>
                        <div class="col-md-12 mt-3" id="both"></div>
                    </div>
                </div>
            </div>
            {{--        --}}{{--<div class="col-lg-4 col-md-12 mt-3">--}}
            {{--            <div class="card mb-3">--}}
            {{--                <div class="col-md-12 mt-0 card-body p-0">--}}
            {{--                    --}}{{----}}{{--<h4 class="text-primary" style="color: #a92219;font-size: 28px;text-transform: capitalize;">Total--}}
            {{--                        Earnings</h4>--}}{{----}}{{----}}
            {{--                    <div class="col-md-12 text-center " id="earning_graph">--}}
            {{--                        <select id="earning_type" class="form-control selectpicker" onchange="getEarning()">--}}
            {{--                            <option value="" >Today's Earning</option>--}}
            {{--                            <option value="1" selected>Weekly Earning</option>--}}
            {{--                            <option value="2">Monthly Earning</option>--}}
            {{--                            <option value="3">Yearly Earning</option>--}}
            {{--                        </select>--}}
            {{--                    </div>--}}
            {{--                    <div class="col-md-12 text-center loaderPie" id="loaderPie">--}}
            {{--                        <img alt="" src="./public/assets/images/loader.gif">--}}
            {{--                    </div>--}}
            {{--                    <div class="col-md-12 mt-3" id="total_earning"></div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
            {{--        </div>--}}
            @if(sizeof($details['recent_orders']))
                <div class="col-lg-6 col-md-12 mt-3">
                    <div class="card mb-4">
                        <div class="card-body p-0" style="min-height: 510px">
                            <div class="card-title text-primary">
                                <div class="col-md-12 p-0">
                                    Recent Orders
                                </div>
                            </div>
                            @foreach($details['recent_orders'] as $order)
                                @if(sizeof($order->orderDetails))
                                    <div class="d-flex flex-column mt-2 flex-sm-row align-items-sm-center">

                                        <div class="flex-grow-1">
                                            <div class="row">
                                                <div class="col-md-8 text-left">
                                                    <h1 class="m-0 text-small">
                                                        <b class="first-row" style="text-decoration: underline;">
                                                            <a href="/orders/details/{{$order->order_id}}">{{$order->user_first_name}} {{$order->user_last_name}}</a></b>
                                                    </h1>
                                                    <p class="m-0 p-0 text-small"><b>Billing Amount: </b>
                                                        ${{$order->total_amount}}</p>
                                                    <p class="m-0 p-0 text-small">
                                                        <b>Date: </b>{{date("n/j/Y", strtotime($order->order_date))}}</p>
                                                    <p class="m-0 p-0 text-small">
                                                        <b>Time: </b>{{date("g:i a", strtotime($order->order_time))}}</p>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <p style="text-decoration: underline;"><a
                                                            href="/orders/details/{{$order->order_id}}">#{{$order->order_id}}</a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="mt-2 mb-2"/>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            @if(sizeof($details['top_orders']))
                <div class="col-lg-6 col-md-12 mt-3">
                    <div class="card topCustomers mb-4">
                        <div class="row card-body p-0 text-center">
                            <div class="col-md-6 text-left">
                                <div class="card-title text-primary">
                                    <div class="col-md-12 p-0">
                                        Top Customers:
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <i class="icon-fields fas fa-calendar-alt"></i>
                                <input type="text" name="daterange" id="top_customer"
                                       value="{{date('m/d/Y', strtotime('-7 days')).' - '.date('m/d/Y')}}"
                                       onchange="topCustomerChange()"
                                       class="form-control"/>
                            </div>
                        </div>
                        <div class="col-md-12 p-0">
                            <div class="table-responsive scrollable contacts_body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Total Orders</th>
                                        <th scope="col">Earnings</th>
                                        <th scope="col">Last Order</th>
                                    </tr>
                                    </thead>
                                    <tbody id="top_orders">
                                    {{--                                @foreach($details['top_orders'] as $ord)--}}
                                    {{--                                    <tr>--}}
                                    {{--                                        <td><a style="text-decoration: underline"--}}
                                    {{--                                               href="/users/customer-details/{{$ord->user_id}}">{{$ord->name}}</a></td>--}}
                                    {{--                                        <td>{{$ord->order_count}}</td>--}}
                                    {{--                                        <td>$ {{$ord->total_amount}}</td>--}}
                                    {{--                                        <td>{{date("n/j/Y", strtotime($ord->last_order_at))}}</td>--}}
                                    {{--                                    </tr>--}}
                                    {{--                                @endforeach--}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if(sizeof($details['top_selling_item']))
                <div class="col-lg-6 col-md-12 mt-3">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="card-title text-primary">
                                <div class="col-md-12 p-0">
                                    Top Selling Items
                                </div>
                            </div>
                            <div class="table-responsive scrollable contacts_body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">No. of Orders</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($details['top_selling_item'] as $key => $item)
                                        @if($item->item)
                                            <tr>
                                                <td>{{$item->item->item_name}}
                                                        (${{$item->item->item_price}})</td>
                                                <td>{{$item->top_count}}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- @foreach($details['top_selling_item'] as $item)
                                 @if($item->item)
                                     <div class="d-flex flex-column flex-sm-row align-items-sm-center mb-3">
                                         <img class="avatar-lg mb-3 mb-sm-0 rounded mr-sm-3"
                                              src="{{asset('public/storage/'.$item->item->item_image)}}" alt="">
                                         <div class="flex-grow-1">
                                             <h5 class=""><a>{{$item->item->item_name}}</a></h5>
                                             <p class="m-0 text-small">{{$item->item->item_description}}</p>
                                             <p style="font-size: 20px;font-weight: 600; color: #a92219">
                                                 ${{$item->item->item_price}}</p>
                                         </div>
                                         <div class="flex-grow-1 text-right">
                                             <h5 class="">{{$item->top_count}}</h5>
                                         </div>
                                     </div>
                                 @endif
                             @endforeach--}}
                        </div>
                    </div>
                </div>
            @endif
            @if(sizeof($details['top_zipcodes']))
                <div class="col-lg-6 col-md-12 mt-3">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="card-title text-primary">
                                <div class="col-md-12 p-0">
                                    Top Zip codes
                                </div>
                            </div>
                            <div class="table-responsive scrollable contacts_body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Zip Code</th>
                                        <th scope="col">No. of Orders</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($details['top_zipcodes'] as $key => $item)
                                        @if($item->zip_code)
                                            <tr>
                                                <td><a>{{$item->zip_code}}</a></td>
                                                <td>{{$item->top_count}}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- @foreach($details['top_selling_item'] as $item)
                                 @if($item->item)
                                     <div class="d-flex flex-column flex-sm-row align-items-sm-center mb-3">
                                         <img class="avatar-lg mb-3 mb-sm-0 rounded mr-sm-3"
                                              src="{{asset('public/storage/'.$item->item->item_image)}}" alt="">
                                         <div class="flex-grow-1">
                                             <h5 class=""><a>{{$item->item->item_name}}</a></h5>
                                             <p class="m-0 text-small">{{$item->item->item_description}}</p>
                                             <p style="font-size: 20px;font-weight: 600; color: #a92219">
                                                 ${{$item->item->item_price}}</p>
                                         </div>
                                         <div class="flex-grow-1 text-right">
                                             <h5 class="">{{$item->top_count}}</h5>
                                         </div>
                                     </div>
                                 @endif
                             @endforeach--}}
                        </div>
                    </div>
                </div>
            @endif
            {{--        top orders via zipcode end--}}

            <div class="col-lg-12 col-md-12 mt-3">
                <div class="card mb-3">
                    <div class="row card-body p-0 text-center">
                        <div class="col-md-9 text-left p-0">
                            <div class="card-title text-primary">
                                <div class="col-md-12 ">
                                    Order Timing Chart:
                                </div>
                            </div>
                        </div>
                        {{--                    <div class="col-md-5 text-right">--}}
                        {{--                        <div class="dropdown float-right">--}}
                        {{--                            <button class="btn btn-secondary dropdown-toggle btn-drop" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
                        {{--                                Filter By--}}
                        {{--                            </button>--}}
                        {{--                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">--}}
                        {{--                                <a class="dropdown-item" href="#">Today</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Yesterday</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Last Week</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Last Month</a>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        {{--                    </div>--}}
                        <div class="col-md-3 text-right " id="earning_graph">
                            <input type="text" id="earning_type2" name="daterange"
                                   value="{{date('m/d/Y', strtotime('-7 days')).' - '.date('m/d/Y')}}" class="form-control"
                                   onchange="getRepeatOrders()"/>
                            {{--<select id="earning_type2" class="form-control selectpicker" onchange="getRepeatOrders()">
                                <option value="0">Current Day</option>
                                <option value="1" selected>Last 7 Days</option>
                                <option value="2">Last 30 Days</option>
                                <option value="3">Current Year</option>
                                <option value="4">All Years</option>
                            </select>--}}
                        </div>
                    </div>
                    {{--                <div class="col-md-12 mt-0 card-body p-0">--}}
                    <div class="col-md-12">
                        {{--  <div class="col-md-12 loaderPie" id="loaderPie">
                              <img alt="" src="./public/assets/images/loader-2_food.gif">
                          </div>--}}
                        <div class="col-md-12 mt-3" id="repeatOrders"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 mt-3">
                <div class="card mb-3">
                    <div class="row card-body p-0 text-center">
                        <div class="col-md-9 text-left p-0">
                            <div class="card-title text-primary">
                                <div class="col-md-12">
                                    Order Sales:
                                </div>
                            </div>
                        </div>
                        {{--                    <div class="col-md-5 text-right">--}}
                        {{--                        <div class="dropdown float-right">--}}
                        {{--                            <button class="btn btn-secondary dropdown-toggle btn-drop" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
                        {{--                                Filter By--}}
                        {{--                            </button>--}}
                        {{--                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">--}}
                        {{--                                <a class="dropdown-item" href="#">Today</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Yesterday</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Last Week</a>--}}
                        {{--                                <a class="dropdown-item" href="#">Last Month</a>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        {{--                    </div>--}}
                        <div class="col-md-3 text-right " id="earning_graph">
                            <input type="text" id="earning_type3" name="daterange"
                                   class="form-control" value="{{date('m/d/Y', strtotime('-7 days')).' - '.date('m/d/Y')}}"
                                   onchange="NoOfOrders()"/>

                            {{--<select id="earning_type3" class="form-control selectpicker" onchange="NoOfOrders()">
                                <option value="0">Current Day</option>
                                <option value="1" selected>Last 7 Days</option>
                                <option value="2">Last 30 Days</option>
                                <option value="3">Current Year</option>
                            </select>--}}
                        </div>
                    </div>
                    <div class="col-md-12">
                        {{--  <div class="col-md-12 loaderPie" id="loaderPie">
                              <img alt="" src="./public/assets/images/loader-2_food.gif">
                          </div>--}}
                        <div class="col-md-12 mt-3" id="no_of_orders"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--    <div class="col-lg-8 card card-icon-bg"></div>--}}
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    <script>
        $(function () {

            $('input[name="daterange"]').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                "alwaysShowCalendars": true,
                opens: 'left'
            }, function (start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            });
        });
    </script>
    <script>
        onStatsChange();
        topCustomerChange()
        f();
        getSales();
        // getEarning();
        getRepeatOrders();
        NoOfOrders();
        getTotalOrders();


        $(document).ready(function () {
        });

        function onRestaurantChange() {
            axios.get(baseUrl + 'on-restaurant-change/' + $('#my_restaurant').val()).then(function (response) {
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            }).catch(function (error) {
            });
        }

        //When Date filter applied to change dashboard stats between two dates
        function onStatsChange() {
            axios.post(baseUrl + 'on-stats-change', {'full_date': $('#dashboard_stats').val()}).then(function (response) {
                setTimeout(function () {
                    var gross = response.data.response.gross_revenue.total_amount ? (response.data.response.gross_revenue.total_amount).toLocaleString() : 0;
                    var discount = response.data.response.discount_amount.discount_amount ? (response.data.response.discount_amount.discount_amount).toLocaleString() : 0;
                    var refund = response.data.response.refund_amount.refund_amount ? (response.data.response.refund_amount.refund_amount).toLocaleString() : 0;
                    var tax = response.data.response.total_tax.total_tax ? (response.data.response.total_tax.total_tax).toLocaleString() : 0;

                    $("#gross_stat").html('$' + gross);
                    $("#discount_stat").html('$' + discount);
                    $("#refund_stat").html('$' + refund);
                    $("#tax_stat").html('$' + tax);

                }, 500);
            }).catch(function (error) {
            });
        }

        //When Date filter applied to top customers
        function topCustomerChange() {
            axios.post(baseUrl + 'on-top-customer-change', {'full_date': $('#top_customer').val()}).then(function (response) {
                setTimeout(function () {
                    var obj = response.data.response.top_customers;
                    if (obj.length > 0) {
                        $('#top_orders').empty();
                        for (var i = 0; i < obj.length; i++) {
                            $('#top_orders').append('<tr>\n' +
                                '                        <td><a style="text-decoration: underline" href="users/customer-details/' + obj[i]['user_id'] + '">' + obj[i]['name'] + '</a></td>\n' +
                                '                        <td>' + obj[i]['order_count'] + '</td>\n' +
                                '                        <td>' + '$ ' + obj[i]['total_amount'].toFixed(2) + '</td>\n' +
                                '                        <td>' + obj[i]['last_order_at'] + '</td>\n' +
                                '                    </tr>');
                        }
                    }
                }, 500);
            }).catch(function (error) {
            });
        }

        function getTotalOrders() {
            let titleText = '';
            if ($('#sales_type').val() == 0) {
                titleText = 'Today Sales';
            } else if ($('#sales_type').val() == 1) {
                titleText = 'Yesterday Sales';
            } else if ($('#sales_type').val() == 2) {
                titleText = 'Sales Based On Last Week';
            } else if ($('#sales_type').val() == 3) {
                titleText = 'Sales Based On Last Month';
            } else if ($('#sales_type').val() == 4) {
                titleText = 'Sales Based On Last 6 Month';
            } else if ($('#sales_type').val() == 5) {
                titleText = 'Sales Based On Last Year';
            } else if ($('#sales_type').val() == 6) {
                titleText = 'Sales Based On All Years';
            } else if ($('#sales_type').val() == 7) {
                titleText = 'Sales Based On This Month';
            }
            console.log($('#sales_type').val());
            axios.post(baseUrl + 'get-sales', {'sales_type': $('#sales_type').val()}).then(function (response) {
                $('#loaderOrders').addClass("hide-loader");
                setTimeout(function () {
                    /*   if ($('#sales_type').val() == 1) {
                           Highcharts.chart('sales', {
                               chart: {
                                   type: 'area'
                               },
                               accessibility: {
                                   description: ''
                               },
                               title: {
                                   text: titleText
                               },
                               subtitle: {
                                   text: ''
                               },
                               xAxis: {
                                   allowDecimals: false,
                                   labels: {
                                       formatter: function () {
                                           return this.value; // clean, unformatted number for year
                                       }
                                   }
                               },
                               yAxis: {
                                   title: {
                                       text: 'Nuclear weapon states'
                                   },
                                   labels: $('#sales_type').val() == 1 ? [response.data.response.type] : response.data.response.type
                               },
                               tooltip: {
                                   pointFormat: '{series.name} had stockpiled <b>{point.y:,.0f}</b><br/>warheads in {point.x}'
                               },
                               plotOptions: {
                                   area: {
                                       pointStart: 1940,
                                       marker: {
                                           enabled: false,
                                           symbol: 'circle',
                                           radius: 2,
                                           states: {
                                               hover: {
                                                   enabled: true
                                               }
                                           }
                                       }
                                   }
                               },
                               series: [{
                                   name: 'Total Orders',
                                   data: response.data.response.series[0]
                               }, {
                                   name: 'Total Earning',
                                   data: response.data.response.series[1]
                               }]
                           });
                       } else {*/
                    Highcharts.chart('total_orders', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: titleText
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: {
                            categories: ($('#sales_type').val() == 1 || $('#sales_type').val() == 0) ? [response.data.response.type] : response.data.response.type
                        },
                        yAxis: {
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            line: {
                                dataLabels: {
                                    enabled: true
                                },
                                enableMouseTracking: false
                            }
                        },
                        credits: {
                            enabled: false
                        },
                        series: [/*{
                            name: 'Total Earning',
                            data: response.data.response.series[1],
                            color: "#A92219"
                        },*/ {
                            name: 'Total Orders',
                            data: response.data.response.series[0],
                            color: '#454545'
                        }]
                    });
                    /* }*/
                }, 500)
            }).catch(function (error) {
                $('#loaderOrders').addClass("hide-loader");
            });
        }

        function getSales() {
            let titleText = '';
            if ($('#sales_type').val() == 0) {
                titleText = 'Today Sales';
            } else if ($('#sales_type').val() == 1) {
                titleText = 'Yesterday Sales';
            } else if ($('#sales_type').val() == 2) {
                titleText = 'Sales Based On Last Week';
            } else if ($('#sales_type').val() == 3) {
                titleText = 'Sales Based On Last Month';
            } else if ($('#sales_type').val() == 4) {
                titleText = 'Sales Based On Last 6 Month';
            } else if ($('#sales_type').val() == 5) {
                titleText = 'Sales Based On Last Year';
            } else if ($('#sales_type').val() == 6) {
                titleText = 'Sales Based On All Years';
            } else if ($('#sales_type').val() == 7) {
                titleText = 'Sales Based On This Month';
            }
            console.log($('#sales_type').val());
            axios.post(baseUrl + 'get-sales', {
                'sales_type': $('#sales_type').val()
            }).then(function (response) {
                $('#loader').addClass("hide-loader");
                setTimeout(function () {
                    console.log(response.data.response.series[1]);
                    /*   if ($('#sales_type').val() == 1) {
                           Highcharts.chart('sales', {
                               chart: {
                                   type: 'area'
                               },
                               accessibility: {
                                   description: ''
                               },
                               title: {
                                   text: titleText
                               },
                               subtitle: {
                                   text: ''
                               },
                               xAxis: {
                                   allowDecimals: false,
                                   labels: {
                                       formatter: function () {
                                           return this.value; // clean, unformatted number for year
                                       }
                                   }
                               },
                               yAxis: {
                                   title: {
                                       text: 'Nuclear weapon states'
                                   },
                                   labels: $('#sales_type').val() == 1 ? [response.data.response.type] : response.data.response.type
                               },
                               tooltip: {
                                   pointFormat: '{series.name} had stockpiled <b>{point.y:,.0f}</b><br/>warheads in {point.x}'
                               },
                               plotOptions: {
                                   area: {
                                       pointStart: 1940,
                                       marker: {
                                           enabled: false,
                                           symbol: 'circle',
                                           radius: 2,
                                           states: {
                                               hover: {
                                                   enabled: true
                                               }
                                           }
                                       }
                                   }
                               },
                               series: [{
                                   name: 'Total Orders',
                                   data: response.data.response.series[0]
                               }, {
                                   name: 'Total Earning',
                                   data: response.data.response.series[1]
                               }]
                           });
                       } else {*/
                    Highcharts.chart('total_earnings', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: titleText
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: {
                            categories: ($('#sales_type').val() == 1 || $('#sales_type').val() == 0) ? [response.data.response.type] : response.data.response.type
                        },
                        yAxis: {
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            line: {
                                dataLabels: {
                                    enabled: true
                                },
                                enableMouseTracking: false
                            }
                        },
                        credits: {
                            enabled: false
                        },
                        series: [{
                            name: 'Total Earning',
                            data: response.data.response.series[1],
                            color: "#A92219"
                        }/*, {
                            name: 'Total Orders',
                            data: response.data.response.series[0],
                            color: '#454545'
                        }*/]
                    });
                    /* }*/
                }, 500)
            }).catch(function (error) {
                $('#loader').addClass("hide-loader");
            });
        }

        function getEarning() {
            axios.post(baseUrl + 'get-earning', {'earning_type': $('#earning_type').val()}).then(function (response) {
                $('#loaderPie').addClass("hide-loader");
                setTimeout(function () {
                    Highcharts.chart('total_earning', {
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            type: 'pie',
                        },
                        title: {
                            text: ''
                        },
                        credits: {
                            enabled: false
                        },
                        //     {
                        //     pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        // },
                        tooltip: false,
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.name}: <b>{point.percentage:.1f}%</b>'
                                },
                                showInLegend: true
                            }
                        },
                        series: [{
                            name: 'Earning',
                            colorByPoint: true,
                            data: [{
                                name: 'Breakfast',
                                y: response.data.response.breakfast.earned,
                                color: "#A92219",
                                sliced: true,
                                selected: true
                            }, {
                                name: 'Lunch',
                                y: response.data.response.lunch.earned,
                                color: '#454545'
                            }]
                        }]
                    });
                }, 500)
            }).catch(function (error) {
                $('#loaderPie').addClass("hide-loader");
            });
        }

        function getRepeatOrders() {
            // axios.post(baseUrl + 'get-earning', {'earning_type': $('#earning_type').val()}).then(function (response) {

            axios.post(baseUrl + 'order-timing-chart', {
                'full_date': $('#earning_type2').val(),
            }).then(function (response) {
                $('#loader').addClass("hide-loader");
                setTimeout(function () {
                    Highcharts.chart('repeatOrders', {
                        chart: {
                            type: 'column',
                        },
                        exporting: {
                            enabled: false
                        },
                        title: {
                            text: ''
                        },
                        colors: ['#63191b'],
                        subtitle: {
                            text: ''
                        },
                        credits: {
                            enabled: false
                        },
                        xAxis: {
                            type: 'category',
                            labels: {
                                rotation: -45,
                                style: {
                                    fontSize: '13px',
                                    fontFamily: 'Verdana, sans-serif'
                                }
                            },
                            /* dateTimeLabelFormats:{
                             millisecond: '%H:%M:%S.%L',
                             second: '%H:%M:%S',
                             minute: '%H:%M',
                             hour: '%H:%M',
                             day: '%e. %b',
                             week: '%e. %b',
                             month: '%b \'%y',
                             year: '%Y'
                             },*/
                            categories: ['07:00-8:59', '09:00-10:59', '11:00-12:59', '13:00-14:59', '15:00-16:59', '17:00-18:59', '19:00-20:59', '21:00-22:59', '23:00-00:59', '01:00-02:59', '03:00-04:59', '05:00-06:59']
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'No Of Orders'
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        tooltip: {
                            pointFormat: '<b>{point.y} orders</b>'
                        },
                        series: [{
                            name: 'Orders',
                            data: [response.data.response.seven_to_nine, response.data.response.nine_to_eleven, response.data.response.eleven_to_thirteen, response.data.response.thirteen_to_fifteen, response.data.response.fifteen_to_seventeen, response.data.response.seventeen_to_nineteen, response.data.response.nineteen_to_twentyone, response.data.response.twentyone_to_twentythree, response.data.response.twentythree_to_one, response.data.response.one_to_three, response.data.response.three_to_five, response.data.response.five_to_seven],
                            dataLabels: {
                                enabled: true,
                                rotation: -0,
                                color: '#ccc',
                                align: 'center',
                                format: '{point.y}', // one decimal
                                y: 30, // 10 pixels down from the top
                                style: {
                                    fontSize: '13px',
                                    textOutline: 'none'
                                }
                            }
                        }]
                    });
                }, 500)
            }).catch(function (error) {
                $('#loader').addClass("hide-loader");
            });
        }

        function NoOfOrders() {
            // axios.post(baseUrl + 'get-earning', {'earning_type': $('#earning_type').val()}).then(function (response) {
            axios.post(baseUrl + 'no-of-orders', {
                'full_date': $('#earning_type3').val()
                /*'sales_type': $('#earning_type3').val()*/
            }).then(function (response) {
                $('#loader').addClass("hide-loader");
                var items = [];
                var itemsCount = [];
                // if (response.data.response.length > 0) {
                for (var i = 0; i < response.data.response.length; i++) {
                    if (response.data.response[i].item_name != null && response.data.response[i].count != 0) {
                        items.push(response.data.response[i].item_name);
                        itemsCount.push(response.data.response[i].count);
                    }
                }
                setTimeout(function () {
                    Highcharts.chart('no_of_orders', {

                        chart: {
                            scrollablePlotArea: {
                                minWidth: 1800,
                                scrollPositionX: 1
                            },
                        },
                        exporting: {
                            enabled: false
                        },
                        title: {
                            text: ''
                        },
                        credits: {
                            enabled: false
                        },
                        subtitle: {
                            text: ''
                        },

                        yAxis: {
                            title: {
                                text: 'No Of Orders'
                            }
                        },

                        xAxis: {
                            categories: items/*['Turkey Wings ', 'Beef Tips', 'Brownie', 'Tea Cakes', 'Medium Breakfast', 'Simply Orange Juice', 'Pecan Pie', '7up Pound Cake', 'Calypso Lemonade']*/,
                        },

                        legend: {
                            layout: 'horizontal',
                            align: 'right',
                            verticalAlign: 'middle'
                        },

                        /* plotOptions: {
                         series: {
                         label: {
                         connectorAllowed: false
                         },
                         pointStart: 2010
                         }
                         },*/

                        series: [{
                            name: 'No Of Orders',
                            data: itemsCount/*[43, 52, 57, 69, 97, 11, 13, 54]*/,
                            dataLabels: {
                                enabled: true,
                            },
                            color: '#454545',
                            style: {
                                textOutline: 'none'
                            }
                        }],

                        responsive: {
                            rules: [{
                                condition: {
                                    maxWidth: 500
                                },
                                chartOptions: {
                                    legend: {
                                        layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom'
                                    }
                                }
                            }]
                        }

                    });
                }, 500)
                // }
            }).catch(function (error) {
                $('#loader').addClass("hide-loader");
            });
        }

        function f() {
            // let titleText = '';
            // if ($('#sales_type').val() == 0) {
            //     titleText = 'Today Sales';
            // } else if ($('#sales_type').val() == 1) {
            //     titleText = 'Yesterday Sales';
            // } else if ($('#sales_type').val() == 2) {
            //     titleText = 'Sales Based On Last Week';
            // } else if ($('#sales_type').val() == 3) {
            //     titleText = 'Sales Based On Last Month';
            // } else if ($('#sales_type').val() == 4) {
            //     titleText = 'Sales Based On Last 6 Month';
            // } else if ($('#sales_type').val() == 5) {
            //     titleText = 'Sales Based On Last Year';
            // } else if ($('#sales_type').val() == 6) {
            //     titleText = 'Sales Based On All Years';
            // } else if ($('#sales_type').val() == 7) {
            //     titleText = 'Sales Based On This Month';
            // }
            // console.log($('#sales_type').val());
            axios.post(baseUrl + 'get-sales', {'full_date': $('#sales_type').val()}).then(function (response) {
                $('#loaderLineWithColmn').addClass("hide-loader");
                setTimeout(function () {
                    Highcharts.chart('both', {
                        chart: {
                            zoomType: 'xy'
                        },
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: [{
                            categories: ($('#sales_type').val() === 1 || $('#sales_type').val() === 0) ? [response.data.response.type] : response.data.response.type,
                            crosshair: true
                        }],
                        yAxis: [{ // Secondary yAxis
                            title: {
                                text: 'Total Orders',
                                style: {
                                    color: '#63191b'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#63191b'
                                }
                            }
                        },
                            { // Primary yAxis
                                labels: {
                                    style: {
                                        color: '#454545'
                                    }
                                },
                                title: {
                                    text: 'Total Earnings',
                                    style: {
                                        color: '#454545'
                                    }
                                },
                                opposite: true
                            }],
                        tooltip: {
                            shared: true
                        },
                        legend: {
                            layout: 'horizontal',
                            align: 'left',
                            x: 120,
                            verticalAlign: 'top',
                            y: 100,
                            floating: true
                        },
                        plotOptions: {
                            series: {
                                stacking: 'normal'
                            }
                        },
                        credits: {
                            enabled: false
                        },
                        /* series: [{
                             name: 'Rainfall',
                             type: 'column',
                             yAxis: 1,
                             data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
                             tooltip: {
                                 valueSuffix: ' mm'
                             }

                         }, {
                             name: 'Temperature',
                             type: 'spline',
                             data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                             tooltip: {
                                 valueSuffix: '°C'
                             }
                         }]*/
                        series: [{
                            showInLegend: false,
                            name: 'Total Orders',
                            type: 'column',
                            data: response.data.response.series[0],
                            color: '#3a5359'
                        }, {
                            showInLegend: false,
                            name: 'Total Earning',
                            type: 'spline',
                            yAxis: 1,
                            data: response.data.response.series[1],
                            color: '#454545'
                        }]
                    });

                }, 500)
            }).catch(function (error) {
                $('#loaderLineWithColmn').addClass("hide-loader");
            });
        }
    </script>
@endsection
