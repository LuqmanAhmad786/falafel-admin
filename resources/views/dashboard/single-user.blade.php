@extends('layouts.master')

@section('page-css')
    <style>
        ul.breadcrumb {
            list-style: none;
            background-color: transparent;
            /*border-bottom: 1px solid #a92219;*/
            border-radius: 0;
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

        .category-tab {
            background: #EDF3F9;
            padding: 10px;
        }

        h3 span {
            font-size: 24px;
            color: #a92219;
            font-weight: normal;
        }

        h5 span {
            color: #a92219;
            font-weight: normal;
        }

        h3, h5 {
            font-weight: normal;
        }

        .i-Visa {
            font-size: 25px;
        }

        .table th, .table td {
            vertical-align: middle;
        }
        .card {
            padding: 15px;
        }

    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
    <ul class="breadcrumb">
        <li><a href="{{route('customers')}}">User(s)</a></li>
        <li>Details of {{$single_user->first_name}}  {{$single_user->last_name}}</li>
    </ul>
    </div>
    <div class="row pl-3 pr-3 pb-3 mt-4">
        <div class="col-md-12 card">
            <div class="row">
                <div class="col-md-8 mt-2 pt-1 pb-2">
                    <div class="mb-2"><b>Name:</b> <span style="font-size: 15px"><b> {{$single_user->first_name}} {{$single_user->last_name}}</b></span></div>
                    <div class="mb-2"><b>Contact No:</b> <span style="font-size: 15px"><b> {{phoneFormat($single_user->mobile)}}</b> </span></div>
                    <div class="mb-2"><b>Email:</b> <span> {{$single_user->email}} </span></div>
                    <div class="mb-2"><b>Zip Code:</b> <span> {{$single_user->zip_code}} </span></div>
                </div>
                <div class="col-md-4 text-right mt-2 pt-1 pb-2">
                    <div class="mb-2">Current Rewards Count: {{$rewards['total_rewards']['points'] ? $rewards['total_rewards']['points'] : 0}}</div>
                    <div class="mb-2">Total Orders: {{sizeof($all_orders)}}</div>
                    <div class="mb-2">Last Login: @if($single_user->last_login) {{date('n/j/Y',$single_user->last_login)}} @else NA @endif</div>
                </div>
                <div class="col-md-12 mb-2 text-right">
                    <a class="action-links ml-1" onclick="singleUser({{$single_user}})">Edit</a><span style="border-left: 3px solid #a92219 !important; margin-left: 5px"></span>
                    <a class="action-links ml-1" onclick="deleteUser({{$single_user->id}})">Delete</a><span style="border-left: 3px solid #a92219 !important; margin-left: 5px"></span>
                    <a class="action-links ml-1" data-toggle="modal" data-target="#resetPassword">Reset Password</a><span style="border-left: 3px solid #a92219 !important; margin-left: 5px"></span>
                    <a class="action-links ml-1" data-toggle="modal" data-target="#addRewards">Add Reward Points</a>
                </div>
            </div>
        </div>
        <div class="col-md-12 mt-3 card p-3">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab"
                       aria-controls="nav-home" aria-selected="true">Order(s)</a>
                    <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab"
                       aria-controls="nav-profile" aria-selected="false">Reward History</a>
                    <a class="nav-item nav-link" id="nav-saved-reward-tab" data-toggle="tab" href="#nav-saved-reward"
                       role="tab"
                       aria-controls="nav-saved-reward" aria-selected="false">Saved Reward(s)</a>
                    <a class="nav-item nav-link" id="nav-saved-bonus-tab" data-toggle="tab" href="#nav-saved-bonus"
                       role="tab"
                       aria-controls="nav-saved-bonus" aria-selected="false">Bonus(s)</a>
                    <a class="nav-item nav-link" id="nav-current-cart-tab" data-toggle="tab" href="#nav-current-cart"
                       role="tab"
                       aria-controls="nav-current-cart" aria-selected="false">Current Cart</a>
                    <a class="nav-item nav-link" id="nav-user-cards-tab" data-toggle="tab" href="#nav-user-cards"
                       role="tab"
                       aria-controls="nav-user-cards" aria-selected="false">User Cards</a>
                </div>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    @if(sizeof($all_orders))
                        @foreach($all_orders as $item)
                            @if(sizeof($item['orderDetails']))
                                <div class="row pt-3">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <a style="text-decoration: underline" href="{{url('/')}}/orders/details/{{$item->order_id}}"><h5 class="text-primary"><b
                                                            style="font-weight: 600">Order
                                                            #{{$item->order_id}} ({{date("n/j/Y", strtotime($item->order_date))}})</b>
                                                    </h5></a>
                                                <h5 class="text-primary">{{$item->name}}</h5>
                                            </div>
                                            <div class="table">
                                                <table class="table table-bordered">
                                                    <thead>
                                                    {{-- <tr>
                                                         <td colspan="2" class="text-center">
                                                             <h5 class="text-primary"><b
                                                                     style="font-weight: 600">Order Id:
                                                                     #{{$item->reference_id}}</b>
                                                             </h5>
                                                         </td>
                                                         <td colspan="2" class="text-center">Placed
                                                             On: {{$item->order_date}}</td>
                                                     </tr>--}}
                                                    <tr>
                                                        <th style="width: 170px;" scope="col">Image</th>
                                                        <th style="width: 370px;" scope="col">Name</th>
                                                        <th style="width: 670px;" scope="col">Customization</th>
                                                        <th scope="col" class="text-right">Price</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($item['orderDetails'] as $details)
                                                        <tr>
                                                            <td class="text-center"><img alt="" height="100" style="width: 100px;height: 100px;"
                                                                                         src="{{asset('public/storage/'.$details->item_image)}}">
                                                            </td>
                                                            <td>{{$details->item_name}}</td>
                                                            <td>
                                                                @if(sizeof($details['order_item'] ))
                                                                    @foreach($details['order_item'] as $k => $itm)
                                                                        <div class="row pl-3">
                                                                            {{$itm->item_name}} ({{$itm->item_count}}{{$itm->item_price ? ', $'.$itm->item_price : ''}})
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </td>
                                                            <td class="text-right">${{$details->item_price}}</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="4">
                                                            <div class="row">
                                                                {{--<div class="col-md-6">
                                                                    <b>Order Summary :</b>
                                                                </div>--}}
                                                                <div class="col-md-12 text-right">
                                                                    <div>Sub Total: ${{$item->order_total}}</div>
                                                                    <hr class="m-1">
                                                                    <div>Tax Rate: ${{$item->total_tax}}</div>
                                                                    <hr class="m-1">
                                                                    <div><h6>Total Amount: ${{ number_format($item->total_amount, 2)}}</h6></div>
                                                                    <hr class="m-1">
                                                                    <div><p style="color: #262626; margin: 0">Earned Rewards: 21</p></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            {{-- <div class="col-md-6 mb-3">
                                                 <div class="row">
                                                     <div class="col-md-3">
                                                         <img height="70"
                                                              src="http://159.65.142.31/farmers-fresh-kitchen/public/storage/images/menu-images/1577365844.png">
                                                     </div>
                                                     <div class="col-md-9 p-0">
                                                         <div class="row">
                                                             <div class="col-md-8"><h4>{{$details->item_name}}</h4>
                                                             </div>
                                                             <div class="col-md-4"><h5>${{$details->item_price}}</h5>
                                                             </div>
                                                         </div>
                                                         @foreach($details['order_item'] as $k => $itm)
                                                             <div class="row pl-3">
                                                                 {{$itm->item_name}} ({{$itm->item_count}}
                                                                 ,${{$itm->item_price}})
                                                             </div>
                                                             --}}{{--@if(sizeof($details['order_item']) != $k+1),@endif--}}{{--
                                                         @endforeach
                                                     </div>
                                                 </div>
                                             </div>--}}
                                        </div>
                                        {{--<div class="row">
                                            <div class="col-md-8 text-left"></div>
                                            <div class="col-md-4">
                                                <div class="text-left mb-3"><b>Order Summary </b></div>
                                                <div class="row">
                                                    <div class="col-md-8">Sub Total</div>
                                                    <div class="col-md-4">${{$item->order_total}}</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-8">Tax Rate</div>
                                                    <div class="col-md-4">{{$item->total_tax}}</div>
                                                </div>
                                                <hr class="mb-2 mt-2">
                                                <div class="row">
                                                    <div class="col-md-8"><b>Total Amount</b></div>
                                                    <div class="col-md-4"><b>${{$item->total_amount}}</b></div>
                                                </div>
                                                --}}{{-- <div><b>Sub Total</b> : {{$item->order_total}}</div>
                                                 <div><b>Total Tax Rate</b> : {{$item->total_tax}}</div>
                                                 <div><b>Total Amount</b> : {{$item->total_amount}}</div>--}}{{--
                                            </div>
                                        </div>--}}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="col-md-12 mt-5 text-center">
                            <img alt="" height="150" src="{{asset('public/images/not-found.png')}}">
                            <h5 class="mt-3 not-found">Not Found</h5>
                        </div>
                    @endif
                </div>
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    <div class="col-md-12 p-0">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Purchase Credit At</th>
                                        <th scope="col">Total Reward</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($rewards['list'] as $rwd)
                                        <tr>
                                            <td>{{date("n/j/Y", strtotime($rwd->created_at))}}</td>
                                            @if($rwd->type==1)
                                                <td>{{$rwd->total_rewards}}</td>
                                            @elseif($rwd->type==4 || $rwd->type==3)
                                                <td><b class="text-primary" style="font-weight:600">{{$rwd->total_rewards}} (admin gifts)</b></td>
                                            @elseif($rwd->type==2)
                                                <td><b class="text-primary" style="font-weight:600">{{$rwd->total_rewards}} Redeemed</b></td>
                                            @elseif($rwd->type==5)
                                                <td><b class="text-primary" style="font-weight:600">{{$rwd->total_rewards}} Refund Deduction</b></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{--@if(sizeof($rewards['list']))
                                    @foreach($rewards['list'] as $rwd)
                                        <div class="col-md-12 text-center category-tab">
                                            <h5 class="mb-0">{{$rwd->month_name}}</h5>
                                        </div>
                                        @foreach($rwd['rewards'] as $item)
                                            <div class="col-md-6 p-0 text-left mt-3">
                                                <h5>Purchase Credit,</h5>
                                                <p>{{$item->order_date}}</p>
                                            </div>
                                            <div class="col-md-6 p-0 text-right mt-3">
                                                <h2 class="text-primary">+ {{$item->total_rewards}}</h2>
                                            </div>
                                        @endforeach
                                    @endforeach
                                @endif--}}
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-saved-reward" role="tabpanel" aria-labelledby="nav-saved-reward-tab">
                    <div class="col-md-12 p-0">
                        @if(sizeof($rewards['saved_rewards']))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Created on</th>
                                        <th scope="col">Expire on</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($rewards['saved_rewards'] as $key => $item)
                                        <tr>
                                            <td>{{date("n/j/Y", strtotime($item['created_at']))}}</td>
                                            <td>{{date("n/j/Y", $item['expiry'])}}</td>
                                            <td>{{$item['status']==1 ? 'Available': 'Redeemed'}}</td>
                                            <td><a href="javascript:void(0)" onclick="deleteReward({{$item['coupon_id']}}, 1)">Delete</a></td>
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
                <div class="tab-pane fade" id="nav-saved-bonus" role="tabpanel" aria-labelledby="nav-saved-bonus-tab">
                    <div class="col-md-12 p-0">
                        @if(sizeof($rewards['saved_bonus']))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Expire on</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($rewards['saved_bonus'] as $key => $item)
                                        <tr>
                                            <td>{{$item['bonus_name']}}</td>
                                            <td>{{date("n/j/Y", strtotime($item['bonus_expiry']))}}</td>
                                            <td>{{$item['is_used']==0 ? 'Available': 'Redeemed'}}</td>
                                            <td><a href="javascript:void(0)" onclick="deleteReward({{$item['id']}},2)">Delete</a></td>
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
                <div class="tab-pane fade" id="nav-current-cart" role="tabpanel" aria-labelledby="nav-current-cart-tab">
                    <div class="col-md-12 p-0">
                        @if(sizeof($single_user->cart_data))
                            <div class="pull-right">
                                <h5 class="text-primary">
                                    <b style="font-weight: 600">Last Updated At: {{date('n/j/Y',strtotime($single_user->cart_details->updated_at))}}</b>
                                </h5>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Item Image</th>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Item Qty</th>
                                        <td scope="col">Item Price</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($single_user->cart_data as $key => $item)
                                        <tr>
                                            <td>
                                                @if($item['item_image'])
                                                    <img src="{{asset('').'/'.$item['item_image']}}" width="80px"/>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{$item['item_name']}}</td>
                                            <td>{{$item['item_count']}}</td>
                                            <td>${{$item['item_price']}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <th>Total Tax : </th>
                                            <td>${{$single_user->cart_details->total_tax}}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <th>Sub Total : </th>
                                            <td>${{$single_user->cart_details->order_total}}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <th>Order Total : </th>
                                            <td>${{$single_user->cart_details->total_amount}}</td>
                                        </tr>
                                    </tfoot>
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

                <div class="tab-pane fade" id="nav-user-cards" role="tabpanel" aria-labelledby="nav-user-cards-tab">
                    <div class="col-md-12 p-0">
                        @if(sizeof($single_user->user_cards))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">Card Image</th>
                                        <th scope="col">Card Name</th>
                                        <th scope="col">Balance</th>
                                        <th scope="col">Default</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($single_user->user_cards as $key => $item)
                                            <tr>
                                                <td>
                                                    @if($item->giftCard->card_image != '')
                                                        <img src="{{asset('/public/').'/'.$item->giftCard->card_image}}" width="80px"/>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{$item['card_nickname']}}</td>
                                                <td>${{$item['balance']}}</td>
                                                <td>
                                                    @if($item['is_default'])
                                                        Yes
                                                    @else
                                                        No
                                                    @endif
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
        </div>
    </div>
    <div class="modal fade" id="editUser" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit {{$single_user->first_name}} {{$single_user->last_name}} Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="editCustomerForm" onsubmit="updateUser();">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>First Name*</label>
                                <input class="form-control" id="first_name" placeholder="Enter First Name.." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Last Name*</label>
                                <input class="form-control" id="last_name" placeholder="Enter Last Name.." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email*</label>
                                <input class="form-control" id="email" type="email" placeholder="Enter Email.." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone*</label>
                                <input class="form-control" id="mobile" placeholder="Enter Mobile" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Birthday*</label>
                                <input class="form-control" id="birthday" type="date" placeholder="Select Date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Zip Code*</label>
                                <input class="form-control" type="tel" id="zip_code" maxlength="5" onkeypress="return isNumberKey(event)"  placeholder="Enter Zip Code..">
                            </div>
                            <div class="col-md-12 text-right mt-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="resetPassword" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset {{$single_user->first_name}} {{$single_user->last_name}} Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="resetPasswordForm" onsubmit="resetPassword();">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Password*</label>
                                <input class="form-control"
                                       type="password"
                                       id="reset_password"
                                       placeholder="Enter Password.."
                                       required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Confirm Password*</label>
                                <input class="form-control"
                                       id="reset_confirm_password"
                                       type="password"
                                       placeholder="Confirm Password.."
                                       required>
                            </div>
                            <div class="col-md-12 text-right mt-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addRewards" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reward Points</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="addRewardForm" onsubmit="addRewardPoints();">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Enter Reward Points*</label>
                                <input class="form-control"
                                       type="number"
                                       id="reward_points"
                                       placeholder="Enter Points"
                                       required>
                            </div>
                            <div class="col-md-12 text-right mt-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.5.10/cleave.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojione/2.2.7/lib/js/emojione.min.js"></script>
    <script type="text/javascript" src="{{asset('public/assets/js/cleave-phone.us.js')}}"></script>
@endsection

@section('bottom-js')
    <script type="text/javascript">

        let singleUserData = {};

        $(document).ready(function () {
            $('form').on('submit', function (e) {
                e.preventDefault();
            });
        });

        function singleUser(item) {
            console.log(item.date_of_birth);
            var date = new Date(item.date_of_birth*1000);

            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();

            if (month < 10) month = "0" + month;
            if (day < 10) day = "0" + day;

            var today = year + "-" + month + "-" + day;

            singleUserData = item;
            $('#first_name').val(item.first_name);
            $('#last_name').val(item.last_name);
            $('#mobile').val(item.mobile);
            $('#total_order').val(item.orders);
            $('#email').val(item.email);
            $('#total_reward').val(item.rewards);
            $('#zip_code').val(item.zip_code);
            $('#birthday').val(today);
            $('#editUser').modal('show');
            new Cleave('#mobile', {
                phone: true,
                phoneRegionCode: 'US'
            });
        }

        function updateUser() {
            const mobile = $('#mobile').val();
            axios.post(baseUrl + 'update-user', {
                'first_name': $('#first_name').val(),
                'last_name': $('#last_name').val(),
                'email': $('#email').val(),
                'mobile': mobile.replace(/\s+/g,''),
                'zip_code': $('#zip_code').val(),
                'birthday': $('#birthday').val(),
                'id': singleUserData.id,
            }).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                window.location.reload();
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }
        function isNumberKey(evt)
        {
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode > 31 && (charCode < 48 || charCode > 57))
                return false;

            return true;
        }

        function resetPassword() {
            if ($('#reset_password').val() == $('#reset_confirm_password').val()) {
                axios.post(baseUrl + 'reset-user-password', {
                    'id': '{{$single_user->id}}',
                    'email': '{{$single_user->email}}',
                    'password': $('#reset_password').val()
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
            } else {
                toastr.error('Password and confirm password should be same.', "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }
        }

        function addRewardPoints() {
            axios.post(baseUrl + 'add-user-reward-points', {
                'id': '{{$single_user->id}}',
                'reward_points': $('#reward_points').val()
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

        function deleteUser(itemId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this user.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.value) {
                    axios.get(baseUrl + 'user-delete/' + itemId).then(function (response) {
                        toastr.success('Menu Deleted Successfully.', "Success", {
                            timeOut: "3000",
                            positionClass: "toast-bottom-right"
                        });
                        setTimeout(function () {
                            window.location.href = baseUrl + 'users/customers'
                        }, 200);
                    }).catch(function (error) {
                        toastr.error(error.response.data.message, "Required!", {
                            timeOut: "3000",
                            positionClass: "toast-bottom-right"
                        });
                    });
                }
            });
        }

        function deleteReward(itemId,type) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.value) {
                    axios.get(baseUrl + 'reward-delete/' + itemId + '/' + type).then(function (response) {
                        toastr.success('Deleted Successfully.', "Success", {
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
            });
        }

    </script>
@endsection
