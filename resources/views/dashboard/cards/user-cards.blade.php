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
            <a class="nav-link active" href="{{route('user_cards')}}">User Cards</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('gift_cards')}}">Gift Cards</a>
        </li>
    </ul>
    <div class="row">
        @if(sizeof($cards))
            <div class="col-md-12">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Card Name</th>
                        <th>Card Number</th>
                        <th>Card Type</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cards as $card)
                        <tr>
                            <td><a href="{{url('/users/customer-details/')}}/{{$card->user_id}}">{{ucwords($card->user->first_name)}} {{ucwords($card->user->last_name)}}</a></td>
                            <td>
                                {{$card->card_nickname}}
                                @if($card->is_default)
                                    <br/><span class="badge badge-info">Default</span>
                                @endif
                            </td>
                            <td>{{$card->card_number}}</td>
                            <td>{{$card->giftCard->card_name}}</td>
                            <td>${{$card->balance}}</td>
                            <td>
                                <a href="{{route('user_card',['id'=>$card->id])}}">View</a>
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
