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

        .order-statistics {
            height: 100px;
            background: #a92219;
            padding: 10px;
            border-radius: 50%;
            color: #fff;
        }

        .col-md-3.card {
            max-width: 270px;
            margin: 3px;
        }

        h5 {
            font-weight: 600 !important;
            color: #000000;
        }

       /* h2 {
            font-weight: 600 !important;
            color: #a92219;
            font-size: 36px;
        }*/

        select.orderFilter {
            {{--width: 100%;--}}
             background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 22rem #f8f9fa !important;
        {{-- position: absolute;
         content: "";
         top: 14px;
         right: 10px;
         width: 0;
         height: 0;
         border: 6px solid transparent!important;
         border-color: #fff transparent transparent transparent;--}}

        }

       /* select.moreTime {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 27rem #f8f9fa !important;
        }*/

        .card {
            padding: 15px;
        }
    </style>

@endsection

@section('main-content')
    {{--<div class="row m-0">
        <div class="col-md-3">
            <h1 class="heading-white mb-1">Order(s)</h1>
        </div>
        <div class="col-3  mt-3 text-center">
        </div>--}}
    <div class="card mb-3" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Order(s) [{{$count}}]</h2>
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
        <form action="" method="GET" style="width: 100%">
            <div class="col-md-12 card p-3 mt-3">
                <div class="row">
                    <div class="col-md-4 mt-3">
                        <input type="text" class="form-control" name="name"
                               placeholder="Filter by Name or Number"
                               value="{{ request()->get('name') }}"
                        >
                    </div>
                    <div class="col-md-4 mt-3">
                        <input type="number" class="form-control" name="order_id"
                               placeholder="Filter by Order ID"
                               value="{{ request()->get('order_id') }}">
                    </div>
                    <div class="col-md-4 mt-3">
                        <input type="text" placeholder="Filter by date range" class="form-control" id="date_range"
                               name="orderrange" value="{{request()->get('orderrange')}}"/>
                    </div>
                    <div class="col-md-4 mt-3">
                        <select name="type" class="form-control selectpicker">
                            <option value="">Select Order Type</option>
                            <option {{request()->get('type') == '1' ? 'selected' : ''}} value="1">Pickup</option>
                            <option {{request()->get('type') == '2' ? 'selected' : ''}} value="2">Delivery
                            </option>
                        </select>
                    </div>
                    <div class="offset-8 col-md-4 mt-3 text-right">
                        <button type="submit" class="btn btn-primary reset-button">
                            Search
                        </button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{url('/orders/list')}}">
                            Reset</a>
                    </div>
                </div>
            </div>
        </form>
        @if(count($orders) > 0)
            <div class="mt-3 table-responsive" id="found">
                <table class="table table-hover sortable" id="itemTable">
                    <thead>
                    <tr>
                          <th>Order Id<i class="i-Up---Down"></i></th>
                        <th>Ordered By<i class="i-Up---Down"></i></th>
                        <th>Date<i class="i-Up---Down"></i></th>
                         <th>Time<i class="i-Up---Down"></i></th>
                         <th>Order Status<i class="i-Up---Down"></i></th>
                         <th>Order Type<i class="i-Up---Down"></i></th>
                         <th>Payment Status<i class="i-Up---Down"></i></th>
                         <th>Total Amount<i class="i-Up---Down"></i></th>
                         <th>Order Date<i class="i-Up---Down"></i></th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $key=>$value)
                        <tr>
                            <td><a style="text-decoration: underline;"
                                   href="{{url('orders/details/'.$value->order_id)}}">#{{$value->order_id}}
                                </a></td>
                            <td>
                                @if($value->user_id != 0)
                                    <a style="text-decoration: underline;" target="_blank"
                                       href="{{url('/users/customer-details/'.$value->user_id)}}">
                                        {{$value->user_first_name}} {{$value->user_last_name}}</a>
                                    @if($value->is_server_order == 1)
                                        <span class="badge badge-info">BY SERVER</span>
                                    @endif
                                @endif
                                @if($value->user_id == 0)
                                <!-- <a style="text-decoration: underline;" target="_blank"
                                       href="{{url('/users/customer-details/'.$value->user_id)}}">
                                        {{$value->user_first_name}} {{$value->user_last_name}}</a> -->
                                    {{$value->user_first_name}} {{$value->user_last_name}}
                                @endif
                            </td>
                            <td>{{date('n/j/Y',strtotime($value->pickup_date))}}</td>
                            <td>{{date("g:i a",$value->pickup_time)}}</td>
                            <td id="order_{{$value->order_id}}">
                                @if($value->status==1)
                                    <span class="badge badge-warning">New Order</span>
                                @elseif($value->status==2)
                                    <span class="badge badge-primary">Ready For Pickup</span>
                                @elseif($value->status==3)
                                    <span class="badge badge-success">Picked Up</span>
                                @endif
                                @if($value->order_type==2)
                                    <span class="badge badge-outline-primary">{{$value->delivery_status}}</span>
                                    @endif
                            </td>
                            <td id="order_{{$value->order_id}}">
                                @if($value->order_type==1)
                                    PICKUP
                                @elseif($value->order_type==2)
                                    DELIVERY
                                @endif
                            </td>
                            <td>
                                @if($value->payment_status==1)
                                    <span class="badge badge-success">Paid</span>
                                @elseif($value->payment_status==2)
                                    <span class="badge badge-warning">Refunded</span>
                                @elseif($value->payment_status==3)
                                    <span class="badge badge-danger">Pending Payment</span>
                                @elseif($value->payment_status==4)
                                    <span class="badge badge-warning">Processing Refund</span>
                                @endif
                            </td>
                            <td>${{number_format($value->total_amount,2)}}</td>
                            <td>{{$value->created_at->format('n/j/Y')}} <br>{{$value->created_at->format('(h:i a)')}}</td>
                            <td class="td-width">
                                @if($value->status==1)
                                    <a title="Edit Preparation Time"
                                       style="color: #a92219;text-decoration: underline;cursor: pointer;"
                                       onclick="editTime('{{$value->order_id}}')">Extend Time</a>
                                @endif
                                <a class="ml-3" title="Print"
                                   style="color: #a92219;text-decoration: underline;cursor: pointer;"
                                   onclick="printQueue('{{$value->order_id}}')">Print</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div id="index_native" class="box"></div>
            <div class="col-md-12 mt-5 text-center" id="not-found">
                <img height="150" src="{{asset('public/images/not-found.png')}}">
                <h5 class="mt-3 not-found">Not Found</h5>
            </div>
        @endif
        <div class="col-md-12 pl-4">
            {{$orders->appends(request()->query())->links("pagination::bootstrap-4")}}
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
                                <select class="form-control selectpicker" id="preparation_time" required>
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
                    <button type="button" class="btn btn-primary" id="edit-btn" onclick="editPreparationTime()">Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script type="text/javascript">
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

        function refundPayment(orderId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to refund this order.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, refund it!'
            }).then(function (result) {
                if (result.value) {
                    window.location.href = baseUrl + 'order-refund/' + orderId;
                }
            })
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

        function printQueue(orderId) {
            axios.get(baseUrl + 'set-print-queue/' + orderId).then(function (response) {
                // printBill(response.data.response);
                toastr.success('Added to print queue', "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            });
        }
    </script>
    {{--    <script type="text/javascript">--}}
    {{--        $('#not-found').hide();--}}
    {{--        let selectedOrderId;--}}
    {{--        let today = new Date();--}}
    {{--        let dd = today.getDate();--}}
    {{--        let mm = today.getMonth() + 1;--}}
    {{--        let yyyy = today.getFullYear();--}}
    {{--        today = mm + '/' + dd + '/' + yyyy;--}}
    {{--        //searchOrder(2);--}}

    {{--        function searchOrder(flag) {--}}
    {{--            if (flag == 1) {--}}
    {{--                $('#keyword').val('');--}}
    {{--                $('#date_single').val(today);--}}
    {{--                $('input[name="order_status"]').val('');--}}
    {{--            }--}}
    {{--            if (flag == 2) {--}}
    {{--                $('#date_range').val('');--}}
    {{--            }--}}
    {{--            if (flag == 3) {--}}
    {{--                $('#date_single').val('');--}}
    {{--            }--}}
    {{--            let range = $('#date_range').val();--}}
    {{--            range = range.split("-");--}}
    {{--            let data = {--}}
    {{--                'keyword': $('#keyword').val(),--}}
    {{--                'date_range': range,--}}
    {{--                'date': $('#date_single').val(),--}}
    {{--                'order_status': $('input[name="order_status"]:checked').val()--}}
    {{--            };--}}
    {{--            $('#not-found').hide();--}}
    {{--            $('#orders_list').html('');--}}
    {{--            $('#index_native').html('');--}}
    {{--            axios.post(baseUrl + 'search-order', data).then(function (response) {--}}
    {{--                console.log(response.data.response.length);--}}
    {{--                if (response.data.response.length) {--}}
    {{--                    $('#found').show();--}}
    {{--                    for (let i = 0; i < response.data.response.length; i++) {--}}
    {{--                        let obj = response.data.response[i];--}}
    {{--                        let status;--}}
    {{--                        if (obj.status == 1) {--}}
    {{--                            status = '<span class="badge badge-danger">Received</span>';--}}
    {{--                        } else if (obj.status == 2) {--}}
    {{--                            status = '<span class="badge badge-warning">Ready to pickup</span>';--}}
    {{--                        } else if (obj.status == 3) {--}}
    {{--                            status = '<span class="badge badge-success">Picked Up</span>';--}}
    {{--                        }--}}

    {{--                        paymentStatus = 'NA';--}}
    {{--                        refundLink = '';--}}
    {{--                        if (obj.payment_status == 1) {--}}
    {{--                            paymentStatus = '<span class="badge badge-success">Paid</span>';--}}
    {{--                            refundLink = '<a class="ml-3" title="Refund Payment" style="color: #a92219;text-decoration: underline;cursor: pointer;" onclick="refundPayment(' + obj.order_id + ')">Refund</a>';--}}
    {{--                        }--}}
    {{--                        else if (obj.payment_status == 2) {--}}
    {{--                            paymentStatus = '<span class="badge badge-warning">Refunded</span>';--}}
    {{--                        }--}}
    {{--                        $('#orders_list').append(--}}
    {{--                            '<tr>' +--}}
    {{--                            // '<td>' + (i + 1) + '</td>' +--}}
    {{--                            '<td>' +--}}
    {{--                            '<h6 class="text-primary">' +--}}
    {{--                            '<a title="Click to view details" class="action-links" style="color: #ffffff" href="./details/' + obj.order_id + '" ">#' + obj.order_id + '</a>' +--}}
    {{--                            '</h6>' +--}}
    {{--                            '</td>' +--}}
    {{--                            '<td>' + obj.user_first_name + '  ' + obj.user_last_name + '</td>' +--}}
    {{--                            '<td>' + obj.pickup_time + '</td>' +--}}
    {{--                            '<td>' + status + '</td>' +--}}
    {{--                            '<td>' + paymentStatus +--}}
    {{--                            '</td>\n' +--}}
    {{--                            // '<td>' + obj.order_date + '</td>' +--}}
    {{--                            '<td>$' + obj.total_amount + '</td>' +--}}
    {{--                            '<td>+ refundLink +'</td></tr>');--}}
    {{--                    }--}}
    {{--                    paginator({--}}
    {{--                        table: document.getElementById("found").getElementsByTagName("table")[0],--}}
    {{--                        box: document.getElementById("index_native"),--}}
    {{--                        active_class: "color_page",--}}
    {{--                        // rows_per_page: response.data.pagination_limit--}}
    {{--                    });--}}
    {{--                } else {--}}
    {{--                    $('#found').hide();--}}
    {{--                    $('#not-found').show();--}}
    {{--                }--}}
    {{--            });--}}
    {{--        }--}}

    {{--        --}}


    {{--    </script>--}}
    <script>
        var table = $('#itemTable');

        $('.sortable th')
            .wrapInner('<span title="sort this column"/>')
            .each(function () {

                var th = $(this),
                    thIndex = th.index(),
                    inverse = false;

                th.click(function () {

                    table.find('td').filter(function () {

                        return $(this).index() === thIndex;

                    }).sortElements(function (a, b) {

                        if ($.text([a]) == $.text([b]))
                            return 0;

                        return $.text([a]) > $.text([b]) ?
                            inverse ? -1 : 1
                            : inverse ? 1 : -1;

                    }, function () {

                        // parentNode is the element we want to move
                        return this.parentNode;

                    });

                    inverse = !inverse;

                });

            });
    </script>
@endsection

@section('bottom-js')
@endsection
