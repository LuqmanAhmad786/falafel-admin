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
                <h2>Menu</h2>
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
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{route('menu-type')}}">Menu Type(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('side-menu-categories')}}">Category(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('side-menu-list')}}">Item(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('items-availability')}}">Item(s) Availability</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('modifier-group')}}">Modifier Group</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('complete-meals')}}">Complete Meal(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('favorite-name')}}">Favorite Label</a>
        </li>
    </ul>
    {{--  <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">...</div>
          <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
          <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
      </div>--}}
    {{-- <div class="row">
         <div class="col-md-6">
             <h1 class="heading-white mb-4">Menu Types</h1>
         </div>
 --}}{{--        <div class="col-md-6 mt-1">--}}{{--
 --}}{{--            <button class="btn btn-primary float-right" data-target="#addMenuModal" data-toggle="modal">Add Menu--}}{{--
 --}}{{--            </button>--}}{{--
 --}}{{--        </div>--}}{{--
     </div>--}}
    <div class="row">
        @if(sizeof($all_menus))
            <div class="col-md-12">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th class="s_no">No.</th>
                        <th>Name</th>
                        <th>Serve From - Serve To</th>
                        <th>Category(s)</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($all_menus as $key=>$value)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td class="first-row">
                                {{$value->menu_name}}
                            </td>
                            <td>
                                {{date("g:i a", strtotime($value->from_time))}}
                                - {{date("g:i a", strtotime($value->to_time))}}
                            </td>
                            @if(sizeof($value->categories))
                                <td>
                                    @foreach($value->categories as $k => $item)
                                        @if($item->category_name)
                                            <p class="mb-0">{{$item->category_name}}
                                                {{-- @if(sizeof($value->categories) != $k+1)
                                                     ,
                                                 @endif--}}</p>
                                        @endif
                                    @endforeach
                                </td>
                            @else
                                <td>--</td>
                            @endif
                            <td class="td-width">
                                <a title="Edit" class="action-links"
                                   onclick="doEdit({{$value}})">Edit</a>
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
    <div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12"><label>Name</label></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="name" placeholder="name" required>
                                </div>
                            </div>
                            <div class="col-md-12"><label>From</label></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <select class="form-control selectpicker" id="from_time"
                                            onchange="restrictTime('start')"
                                            required>
                                        <option value="">Select Time</option>
                                        <option value="0:00">1:00am</option>
                                        <option value="0:30">1:30am</option>
                                        <option value="2:00">2:00am</option>
                                        <option value="2:30">2:30am</option>
                                        <option value="3:00">3:00am</option>
                                        <option value="3:30">3:30am</option>
                                        <option value="4:00">4:00am</option>
                                        <option value="4:30">4:30am</option>
                                        <option value="5:00">5:00am</option>
                                        <option value="5:30">5:30am</option>
                                        <option value="6:00">6:00am</option>
                                        <option value="6:30">6:30am</option>
                                        <option value="7:00">7:00am</option>
                                        <option value="7:30">7:30am</option>
                                        <option value="8:00">8:00am</option>
                                        <option value="8:30">8:30am</option>
                                        <option value="9:00">9:00am</option>
                                        <option value="9:30">9:30am</option>
                                        <option value="10:00">10:00am</option>
                                        <option value="10:30">10:30am</option>
                                        <option value="11:00">11:00am</option>
                                        <option value="11:30">11:30am</option>
                                        <option value="12:00">12:00am</option>
                                        <option value="12:30">12:30am</option>
                                        <option value="13:00">1:00pm</option>
                                        <option value="13:30">1:30pm</option>
                                        <option value="14:00">2:00pm</option>
                                        <option value="14:30">2:30pm</option>
                                        <option value="15:00">3:00pm</option>
                                        <option value="15:30">3:30pm</option>
                                        <option value="16:00">4:00pm</option>
                                        <option value="16:30">4:30pm</option>
                                        <option value="17:00">5:00pm</option>
                                        <option value="17:30">5:30pm</option>
                                        <option value="18:00">6:00pm</option>
                                        <option value="18:30">6:30pm</option>
                                        <option value="19:00">7:00pm</option>
                                        <option value="19:30">7:30pm</option>
                                        <option value="20:00">8:00pm</option>
                                        <option value="20:30">8:30pm</option>
                                        <option value="21:00">9:00pm</option>
                                        <option value="21:30">9:30pm</option>
                                        <option value="22:00">10:00pm</option>
                                        <option value="22:30">10:30pm</option>
                                        <option value="23:00">11:00pm</option>
                                        <option value="23:30">11:30pm</option>
                                        <option value="24:00">12:00pm</option>
                                        <option value="24:30">12:30pm</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12"><label>To</label></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <select class="form-control selectpicker" id="to_time"
                                            onchange="restrictTime('end')" required>
                                        <option value="">Select Time</option>
                                        <option value="1:00">1:00am</option>
                                        <option value="1:30">1:30am</option>
                                        <option value="2:00">2:00am</option>
                                        <option value="2:30">2:30am</option>
                                        <option value="3:00">3:00am</option>
                                        <option value="3:30">3:30am</option>
                                        <option value="4:00">4:00am</option>
                                        <option value="4:30">4:30am</option>
                                        <option value="5:00">5:00am</option>
                                        <option value="5:30">5:30am</option>
                                        <option value="6:00">6:00am</option>
                                        <option value="6:30">6:30am</option>
                                        <option value="7:00">7:00am</option>
                                        <option value="7:30">7:30am</option>
                                        <option value="8:00">8:00am</option>
                                        <option value="8:30">8:30am</option>
                                        <option value="9:00">9:00am</option>
                                        <option value="9:30">9:30am</option>
                                        <option value="10:00">10:00am</option>
                                        <option value="10:30">10:30am</option>
                                        <option value="11:00">11:00am</option>
                                        <option value="11:30">11:30am</option>
                                        <option value="12:00">12:00am</option>
                                        <option value="12:30">12:30am</option>
                                        <option value="13:00">1:00pm</option>
                                        <option value="13:30">1:30pm</option>
                                        <option value="14:00">2:00pm</option>
                                        <option value="14:30">2:30pm</option>
                                        <option value="15:00">3:00pm</option>
                                        <option value="15:30">3:30pm</option>
                                        <option value="16:00">4:00pm</option>
                                        <option value="16:30">4:30pm</option>
                                        <option value="17:00">5:00pm</option>
                                        <option value="17:30">5:30pm</option>
                                        <option value="18:00">6:00pm</option>
                                        <option value="18:30">6:30pm</option>
                                        <option value="19:00">7:00pm</option>
                                        <option value="19:30">7:30pm</option>
                                        <option value="20:00">8:00pm</option>
                                        <option value="20:30">8:30pm</option>
                                        <option value="21:00">9:00pm</option>
                                        <option value="21:30">9:30pm</option>
                                        <option value="22:00">10:00pm</option>
                                        <option value="22:30">10:30pm</option>
                                        <option value="23:00">11:00pm</option>
                                        <option value="23:30">11:30pm</option>
                                        <option value="24:00">12:00pm</option>
                                        <option value="24:30">12:30pm</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="addNewType()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editMenuModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12"><label>Name</label></div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="text" class="form-control" id="edit_name" placeholder="name" required>
                            </div>
                        </div>
                        <div class="col-md-12"><label>From</label></div>
                        <div class="col-md-12 form-group">
                            <select class="form-control selectpicker" id="edit_from_time"
                                    onchange="restrictTime('start')" required>
                                <option value="">Select Time</option>
                                <option value="1:00">1:00am</option>
                                <option value="1:30">1:30am</option>
                                <option value="2:00">2:00am</option>
                                <option value="2:30">2:30am</option>
                                <option value="3:00">3:00am</option>
                                <option value="3:30">3:30am</option>
                                <option value="4:00">4:00am</option>
                                <option value="4:30">4:30am</option>
                                <option value="5:00">5:00am</option>
                                <option value="5:30">5:30am</option>
                                <option value="6:00">6:00am</option>
                                <option value="6:30">6:30am</option>
                                <option value="7:00">7:00am</option>
                                <option value="7:30">7:30am</option>
                                <option value="8:00">8:00am</option>
                                <option value="8:30">8:30am</option>
                                <option value="9:00">9:00am</option>
                                <option value="9:30">9:30am</option>
                                <option value="10:00">10:00am</option>
                                <option value="10:30">10:30am</option>
                                <option value="11:00">11:00am</option>
                                <option value="11:30">11:30am</option>
                                <option value="12:00">12:00am</option>
                                <option value="12:30">12:30am</option>
                                <option value="13:00">1:00pm</option>
                                <option value="13:30">1:30pm</option>
                                <option value="14:00">2:00pm</option>
                                <option value="14:30">2:30pm</option>
                                <option value="15:00">3:00pm</option>
                                <option value="15:30">3:30pm</option>
                                <option value="16:00">4:00pm</option>
                                <option value="16:30">4:30pm</option>
                                <option value="17:00">5:00pm</option>
                                <option value="17:30">5:30pm</option>
                                <option value="18:00">6:00pm</option>
                                <option value="18:30">6:30pm</option>
                                <option value="19:00">7:00pm</option>
                                <option value="19:30">7:30pm</option>
                                <option value="20:00">8:00pm</option>
                                <option value="20:30">8:30pm</option>
                                <option value="21:00">9:00pm</option>
                                <option value="21:30">9:30pm</option>
                                <option value="22:00">10:00pm</option>
                                <option value="22:30">10:30pm</option>
                                <option value="23:00">11:00pm</option>
                                <option value="23:30">11:30pm</option>
                                <option value="24:00">12:00pm</option>
                                <option value="24:30">12:30pm</option>
                            </select>
                        </div>
                        <div class="col-md-12"><label>To</label></div>
                        <div class="col-md-12 form-group">
                            <select class="form-control selectpicker" id="edit_to_time" onchange="restrictTime('end')"
                                    required>
                                <option value="">Select Time</option>
                                <option value="1:00">1:00am</option>
                                <option value="1:30">1:30am</option>
                                <option value="2:00">2:00am</option>
                                <option value="2:30">2:30am</option>
                                <option value="3:00">3:00am</option>
                                <option value="3:30">3:30am</option>
                                <option value="4:00">4:00am</option>
                                <option value="4:30">4:30am</option>
                                <option value="5:00">5:00am</option>
                                <option value="5:30">5:30am</option>
                                <option value="6:00">6:00am</option>
                                <option value="6:30">6:30am</option>
                                <option value="7:00">7:00am</option>
                                <option value="7:30">7:30am</option>
                                <option value="8:00">8:00am</option>
                                <option value="8:30">8:30am</option>
                                <option value="9:00">9:00am</option>
                                <option value="9:30">9:30am</option>
                                <option value="10:00">10:00am</option>
                                <option value="10:30">10:30am</option>
                                <option value="11:00">11:00am</option>
                                <option value="11:30">11:30am</option>
                                <option value="12:00">12:00am</option>
                                <option value="12:30">12:30am</option>
                                <option value="13:00">1:00pm</option>
                                <option value="13:30">1:30pm</option>
                                <option value="14:00">2:00pm</option>
                                <option value="14:30">2:30pm</option>
                                <option value="15:00">3:00pm</option>
                                <option value="15:30">3:30pm</option>
                                <option value="16:00">4:00pm</option>
                                <option value="16:30">4:30pm</option>
                                <option value="17:00">5:00pm</option>
                                <option value="17:30">5:30pm</option>
                                <option value="18:00">6:00pm</option>
                                <option value="18:30">6:30pm</option>
                                <option value="19:00">7:00pm</option>
                                <option value="19:30">7:30pm</option>
                                <option value="20:00">8:00pm</option>
                                <option value="20:30">8:30pm</option>
                                <option value="21:00">9:00pm</option>
                                <option value="21:30">9:30pm</option>
                                <option value="22:00">10:00pm</option>
                                <option value="22:30">10:30pm</option>
                                <option value="23:00">11:00pm</option>
                                <option value="23:30">11:30pm</option>
                                <option value="24:00">12:00pm</option>
                                <option value="24:30">12:30pm</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="addNewType()">Save</button>
                    <button class="btn btn-primary ml-1" title="Delete"
                            onclick="deleteItems(menuId, 'menu')">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assignMealModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Meal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12"><label>Select Meal</label></div>
                            <div class="col-md-12">
                                <select id="selected_meals" required multiple>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="submitAssignMeal()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="setLocationModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Please Select a location to proceed</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row m-0">
                            <div class="col-md-12">
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
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
@endsection

@section('bottom-js')
    <script>
        var menuId;
        var menuType;
        var allMenuTypes;

        $(document).ready(function () {
            $('#selected_meals').select2({});
        });

        function addNewType() {
            var data;
            if (menuId) {
                data = {
                    'menu_name': $('#edit_name').val(),
                    'from': $('#edit_from_time').val(),
                    'to': $('#edit_to_time').val(),
                    'menu_id': menuId
                };
            } else {
                data = {
                    'menu_name': $('#name').val(),
                    'from': $('#from_time').val(),
                    'to': $('#to_time').val()
                };
            }

            axios.post(baseUrl + 'add-menu-type', data).then(function (response) {
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

        function deleteItems(itemId, table) {
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
                    axios.get(baseUrl + 'delete-items/' + itemId + '/' + table).then(function (response) {
                        toastr.success('Menu Deleted Successfully.', "Success", {
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

        function doEdit(item) {
            const selectedTime = item.from.slice(0, 5);
            console.log(item);
            console.log(item.from.slice(0, 5));
            console.log(item.to.slice(0, 5));
            $('#editMenuModal').modal('show');
            $('#edit_name').val(item.menu_name);
            $('#edit_from_time').val(item.from_time.slice(0, 5));
            $('#edit_to_time').val(item.to_time.slice(0, 5));
            menuId = item.menu_id;
        }

        function openAssignMeal(menuTypeId) {
            menuType = menuTypeId;
            axios.get(baseUrl + 'get-selected-meal/' + menuTypeId).then(function (response) {
                allMenuTypes = response.data.response;
                for (let i = 0; i < allMenuTypes.length; i++) {
                    if (allMenuTypes[i].checked) {
                        $('#selected_meals').append('<option selected value="' + allMenuTypes[i].id + '">' + allMenuTypes[i].name + '</option>');
                    } else {
                        $('#selected_meals').append('<option value="' + allMenuTypes[i].id + '">' + allMenuTypes[i].name + '</option>');
                    }
                }
                $('#assignMealModal').modal('show');
            });
        }

        function submitAssignMeal() {
            var data = {
                'menu_type_id': menuType,
                'selected_meals': $('#selected_meals').val()
            };
            axios.post(baseUrl + 'add-menu-type-meal', data).then(function (response) {
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

        function restrictTime(type) {
            /*console.log($('#from_time').val(), $('#to_time').val(), $('#from_time').val() <= $('#to_time').val(), $('#from_time').val() >= $('#to_time').val());
            if ($('#to_time').val()) {
                if ($('#from_time').val() >= $('#to_time').val()) {
                    console.log('tesh');
                    toastr.error('Start shouldn\'t be greater than end time', "Required!", {
                        timeOut: "3000",
                        positionClass: "toast-bottom-right"
                    });
                }
            }*/
        }
    </script>
@endsection
