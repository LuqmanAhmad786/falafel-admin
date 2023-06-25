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
                    <ul class="breadcrumb">
                        <li><a href="{{route('user_cards')}}">Card(s)</a></li>
                        <li>Details of #{{$card->id}}</li>
                    </ul>
            </div>
        </div>
    </div>
    <div style="" id="main-cont">
        <div class="col-md-12 pb-2 card mt-3">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5>Card Details</h5>
                </div>
                <div class="col-md-6 mb-2">
                    Card Number:<b class="font-16"> {{$card->card_number}}</b>
                </div>
                <div class="col-md-6 mb-2">
                    Balance:<b class="font-16"> ${{$card->balance}}</b>
                </div>
                <div class="col-md-6 mb-2">
                    Card Holder Name:<b class="font-14"><a href="{{$card->user->id}}"> {{$card->user->first_name}} {{$card->user->last_name}}</a></b>
                </div>
                <div class="col-md-6 mb-2">
                    Card Name:<b class="font-14"> {{$card->card_nickname}}</b>
                </div>
                <div class="col-md-6 mb-2">
                    Card Type:<b class="font-14"> {{$card->giftCard->card_name}}</b>
                </div>
                <div class="col-md-6 mb-2">
                    Non-Deletable: @if($card->non_deletable)
                                       <span class="badge badge-info">YES</span>
                                       @else
                                        <span class="badge badge-info">NO</span>
                                    @endif
                </div>
                <div class="col-md-6 mb-2">
                    Non-Transferable:@if($card->non_transferable)
                        <span class="badge badge-info">YES</span>
                    @else
                        <span class="badge badge-info">NO</span>
                    @endif
                </div>
                <div class="col-md-6 mb-2">
                    Default:@if($card->is_default)
                        <span class="badge badge-info">YES</span>
                    @else
                        <span class="badge badge-info">NO</span>
                    @endif
                </div>
                <div class="col-md-6 mb-2">
                    Lost Reported:@if($card->lost_reported)
                        <span class="badge badge-info">YES</span>
                    @else
                        <span class="badge badge-info">NO</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12 pb-2 card mt-3">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5>Recharge</h5>
                </div>
                <div class="col-md-6 mb-2">
                        <input type="text" placeholder="Enter Amount" class="form-control" id="amount">
                </div>
                <div class="col-md-6 mb-2">
                    <button class="btn btn-primary" onclick="cardRecharge()">Recharge</button>
                </div>
            </div>
        </div>
        <div class="col-md-12 pb-2 card mt-3">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5>Transactions</h5>
                </div>
                <div class="col-md-12 mb-2">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Date/Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($transactions) > 0)
                            @foreach($transactions AS $transaction)
                                <tr>
                                    <td>{{$transaction->id}}</td>
                                    <td><span class="text-capitalize">{{$transaction->action_type}}</span></td>
                                    <td>${{$transaction->transaction_amount}}</td>
                                    <td>{{$transaction->created_at->format('n/j/Y h:i a')}}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    <script type="text/javascript">
        function cardRecharge() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to recharge this card?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then(function (result) {
                if (result.value) {
                    axios.post(baseUrl + 'card/recharge', {
                        amount: $("#amount").val(),
                        card_id: '{{$card->id}}'
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
            })
        }
    </script>
@endsection
