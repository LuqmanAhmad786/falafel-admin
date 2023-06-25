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
        .card {
            padding: 15px;
        }
       /* select.meals {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 27rem #f8f9fa !important;
        }*/
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
            <a class="nav-link" href="{{route('menu-type')}}">Menu Type(s)</a>
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
            <a class="nav-link active" href="{{route('complete-meals')}}">Complete Meal(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('favorite-name')}}">Favorite Label</a>
        </li>
    </ul>
    <div class="col-md-12 p-3">
    <div class="row">
        <div class="col-md-6">
            <h1 class="heading-white mb-4">Complete Meal(s)</h1>
        </div>
        <div class="col-md-6 mt-1">
            <button class="btn btn-primary float-right" data-toggle="modal"
                    data-target="#addMealModal"
            >Add Meal
            </button>
        </div>
    </div>
    <div class="row m-0">
        @if(sizeOf($response))
            <div class="col-md-12">
{{--                <p><b>Note:</b>If you deactivate any item from below, It will not appear in complete you meal section in website and application.</p>--}}
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th style="max-width: 15px">No.</th>
                        <th>Name</th>
                        <th>Complete Meal(s)</th>
                        <th>Item(s)</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($response as $key=>$value)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td class="first-row">
                                {{$value->menu_name}}
                            </td>
                            @if(sizeof($value->completeMeal))
                                <td>
                                    @foreach($value->completeMeal as $k => $item)
                                        @if($item->category_name)
                                            {{$item->category_name}}
                                            @if(sizeof($value->completeMeal) != $k+1)
                                                ,
                                            @endif
                                        @endif
                                    @endforeach
                                </td>
                            @else
                                <td>--</td>
                            @endif
                            <td>@foreach($value->completeMeal as $k => $item)
                                {{$item->item_name}}
                                @endforeach
                            </td>
                            <td>
                                @if(sizeof($value->completeMeal))
                                    <button class="btn action-links ml-0 pl-0"
                                            onclick="deleteMenu({{$value->menu_id}})">Delete
                                    </button>
                                @endif
                                @if(sizeof($value->completeMeal))
                                    <button class="btn action-links ml-0 pl-0"
                                            onclick="editMenu({{$value->menu_id}})">Edit Items
                                    </button>
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
    <div class="modal fade" id="addMealModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Meal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="menu_type">Category</label>
                                    <select class="form-control selectpicker" id="menu_type" onchange="getMenuCategory()">
                                        <option value="">Select</option>
                                        @foreach($response as $item)
                                            <option value="{{$item->menu_id}}">{{$item->menu_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="menu_category">Sub-Category</label>
                                    <select class="form-control" id="menu_category">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="addCompleteMeal()">Submit</button>
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
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_name">Name</label>
                                    <input type="text" class="form-control" id="edit_name" placeholder="name" required>
                                </div>
                            </div>
                            <div class="col-md-12"><label>From</label></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select class="form-control" id="edit_from_hour" required>
                                        <option value="">Hour</option>
                                        <option value="01">1</option>
                                        <option value="02">2</option>
                                        <option value="03">3</option>
                                        <option value="04">4</option>
                                        <option value="05">5</option>
                                        <option value="06">6</option>
                                        <option value="07">7</option>
                                        <option value="08">8</option>
                                        <option value="09">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select class="form-control" id="edit_from_minutes" required>
                                        <option value="">Minute</option>
                                        <option value="00">00</option>
                                        <option value="30">30</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12"><label>To</label></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select class="form-control" id="edit_to_hour" required>
                                        <option value="">Hour</option>
                                        <option value="01">1</option>
                                        <option value="02">2</option>
                                        <option value="03">3</option>
                                        <option value="04">4</option>
                                        <option value="05">5</option>
                                        <option value="06">6</option>
                                        <option value="07">7</option>
                                        <option value="08">8</option>
                                        <option value="09">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select class="form-control" id="edit_to_minutes" required>
                                        <option value="">Minute</option>
                                        <option value="00">00</option>
                                        <option value="30">30</option>
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
    <div class="modal fade" id="editMealModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Complete Meal Items</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover" id="meal-items">
                        <p><b>Note:</b>If you deactivate any item from below, It will not appear in complete you meal section in website and application.</p>
                    </table>
                </div>
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
        function getMenuCategory() {
            $('#menu_category').html(' ');
            axios.get(baseUrl + 'get-menu-category/' + $('#menu_type').val()).then(function (response) {
                if (response.data.response.length > 0) {
                    for (let i = 0; i < response.data.response.length; i++) {
                        if (response.data.response[i].category.category_id) {
                            $('#menu_category').append('<option value="' + response.data.response[i].category.category_id + '">' + response.data.response[i].category.category_name + '</option>');
                        }
                    }
                }
            }).catch(function (error) {
            });
        }

        function addCompleteMeal() {
            axios.post(baseUrl + 'add-complete-meal', {
                'menu_id': $('#menu_type').val(),
                'category_id': $('#menu_category').val(),
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
                });
            });
        }

        function deleteMenu(itemId) {
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
                    axios.get(baseUrl + 'delete-complete-meal/' + itemId).then(function (response) {
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

        function editMenu(itemId) {
            axios.get(baseUrl + 'get-complete-meal-items/' + itemId).then(function (response) {
                $("#editMealModal").modal('show');
                $("#meal-items").html(response.data.response);
            }).catch(function (error) {

            });
        }

        function deactivateMeal(menuId,itemId,status) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to change status?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then(function (result) {
                if (result.value
                ) {
                    axios.get(baseUrl + 'update-complete-meal/' + itemId +'/'+status).then(function (response) {
                        axios.get(baseUrl + 'get-complete-meal-items/' + menuId).then(function (response) {
                            $("#meal-items").html(response.data.response);
                        }).catch(function (error) {

                        });
                    }).catch(function (error) {

                    });
                }
            })
        }
    </script>
@endsection
