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

        h2 {
            font-weight: 600 !important;
            color: #a92219;
            font-size: 36px;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            min-width: 168px;
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
                <h2 class="heading-white">Transaction(s)</h2>
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
    <div class="card p-2 mt-4">
{{--        <div class="col-md-3 mt-2">
            <h1 class="heading-white mb-3">Transaction(s)</h1>
        </div>--}}
        @if(sizeof($all_transaction))
            <div id="found" class="mt-2 table-responsive">
                <table class="table table-hover sortable" id="transactionTable">
                    <thead>
                    <tr>
                        <th style=" min-width: 20px">Order ID<i class="i-Up---Down"></i></th>
                        <th>Transaction ID<i class="i-Up---Down"></i></th>
                        <th>Amount<i class="i-Up---Down"></i></th>
                        <th>Card Number<i class="i-Up---Down"></i></th>
                        <th>Card Type<i class="i-Up---Down"></i></th>
                        <th>Payment Status<i class="i-Up---Down"></i></th>
                        <th>Date<i class="i-Up---Down"></i></th>
                    </tr>
                    </thead>
                    <tbody id="transaction_list">
                         @foreach($all_transaction as $key =>$item)
                             <tr>
                                 <td>{{$key + 1}}</td>
                                 <td>#{{$item->order_code}}</td>
                                 <td>${{$item->amount ? $item->amount : 0}}</td>
                                 <td>{{$item->masked_card_number}}</td>
                                 <td>{{$item->expiry_month}} / {{$item->expiry_year}}</td>
                                 <td>{{$item->card_type}}</td>
                                 <td>{{$item->payment_status}}</td>
                                 <td>{{$item->order_description}}</td>
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
            {{$all_transaction->appends(request()->query())->links("pagination::bootstrap-4")}}
        </div>
    </div>
@endsection

@section('page-js')
    <script>
        //getTransactions();

        function getTransactions(flag) {
            axios.get(baseUrl + 'get-transaction').then(function (response) {
                if (response.data.response.length) {
                    $('#found').show();
                    $('#not-found').hide();
                    for (let i = 0; i < response.data.response.length; i++) {
                        console.log(response.data.response);
                        let obj = response.data.response[i];
                        console.log(obj.created_at);
                        var dt1 = new Date(obj.created_at);
                        obj.total_amount = obj.total_amount ? obj.total_amount : 0;
                        obj.customer_id = obj.customer_id != null ? obj.customer_id : '-';
                        $('#transaction_list').append('<tr>\n' +
                            '                        <td><a target="_blank" title="Click to view details" class="action-links" style="color: #ffffff" href="../orders/details/' + obj.order_id + '" ">#' + obj.order_id + '</a></td>\n' +
                            '                        <td>' + obj.order_code + '</td>\n' +
                            '                        <td>$' + obj.total_amount.toFixed(2) + '</td>\n' +
                            '                        <td>' + obj.masked_card_number + '</td>\n' +
                            '                        <td>' + obj.card_type + '</td>\n' +
                            '                        <td> <span class="badge badge-sm badge-pill badge-success">' + obj.payment_status + '</span></td>\n' +
                            '                        <td>' + (dt1.getMonth()+ 1) + "/" + dt1.getDate() + "/" + dt1.getFullYear() + '</td>\n' +
                            '                    </tr>');
                    }
                    paginator({
                        table: document.getElementById("found").getElementsByTagName("table")[0],
                        box: document.getElementById("index_native"),
                        active_class: "color_page",
                        rows_per_page: response.data.pagination_limit
                    });
                } else {
                    $('#found').hide();
                    $('#not-found').show();
                }
            });
        }
    </script>
    <script>
        var table = $('#transactionTable');
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
                        // parentNode is the element we want to move
                        return this.parentNode;
                    });
                    inverse = !inverse;
                });
            });
        function formatPhoneNumber(phone) {
            phone = '+1'+phone;
            var cleaned = ('' + phone).replace(/\D/g, '')
            var match = cleaned.match(/^(1|)?(\d{3})(\d{3})(\d{4})$/)
            if (match) {
                var intlCode = (match[1] ? '+1 ' : '')
                return ['(', match[2], ') ', match[3], '-', match[4]].join('')
            }
            return null
        }
    </script>
@endsection

@section('bottom-js')
@endsection
