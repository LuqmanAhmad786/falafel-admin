@extends('layouts.master')

@section('page-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.css">
    <link type="text/css" rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css"/>
    <style>
        .select2-container {
            width: 100% !important;
            padding: 0;
        }

        .td-width {
            min-width: 183px;
        }

        /* select {
             background: url(
        {{url('/')}}
        /public/images/angle-arrow-down-black.png) no-repeat 27rem transparent !important;

                }*/
        .card {
            padding: 15px;
        }
    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Cards</h2>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{route('user_cards')}}">User Cards</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{route('gift_cards')}}">Gift Cards</a>
        </li>
    </ul>
    <div class="row">
        @if(sizeof($cards))
            <div class="col-md-12">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Card Name</th>
                        <th>Card Number</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Amount</th>
                        <th>Redeemed</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cards as $card)
                        <tr>
                            @if($card->card)
                            <td>{{$card->card->card_name}}</td>
                            @endif
                            <td>{{$card->card_number}}</td>
                            <td>{{ucwords($card->sender_name)}}<br/>{{$card->sender_email}}</td>
                            <td>{{ucwords($card->receiver_name)}}<br/>{{$card->receiver_email}}</td>
                            <td>${{$card->amount}}</td>
                            <td>
                                @if($card->is_redeemed)
                                    <span class="badge badge-success">Yes</span>
                                    @else
                                    <span class="badge badge-warning">No</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-12 pl-4">
                {{$cards->appends(request()->query())->links("pagination::bootstrap-4")}}
            </div>
        @else
            <div class="col-md-12 mt-5 text-center">
                <img alt="" height="150" src="{{asset('public/images/not-found.png')}}">
                <h5 class="mt-3 not-found">Not Found</h5>
            </div>
        @endif
    </div>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
@endsection

@section('bottom-js')

@endsection
