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

        .card-title {
            font-size: 13px !important;
            margin-bottom: 0.5rem;
        }

        .card-body {
            flex: 1 1 auto;
            padding: 1.25rem;
            height: 250px;
        }

        #addMenuModal .modal-content, #editMenuModal .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            pointer-events: auto;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 0.3rem;
            outline: 0;
            left: 284px;
            top: -29px;
            border-radius: 0;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: text;
        }

        .card-body {
            flex: 1 1 auto;
            padding-left: 2rem;
            height: auto;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        #top-area .btn-primary {
            color: #fff;
            background-color: #a92219;
            border-color: #a92219;
            min-width: 130px;
        }

        #all-side-menus h6 {
            color: #000;
            font-weight: normal;
            font-size: 18px;
            text-transform: capitalize;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        #all-side-menus img {
            object-fit: cover;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            min-width: 200px;
        }

        .reset-button {
            margin-top: 0px !important;
            color: #ffffff !important;
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
            <a class="nav-link active" href="{{route('side-menu-list')}}">Item(s)</a>
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
    <div class="row p-3"  id="top-area">
        <div class="col-md-6">
            <h1 class="heading-white mb-3">Item(s)</h1>
        </div>
        <div class="col-md-6 text-right">
        <button class="btn btn-primary" data-target="#addMenuModal" data-toggle="modal">
            Add Item
        </button>
        </div>
    <div class="col-md-12 mt-0 card p-3">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                        <div class="col-md-3">
                            {{--<label><b>Filter by keyword:</b></label>--}}
                            <input class="form-control" type="text" placeholder="Filter by keyword"
                                   id="filter_by_keyword"
                                   onchange="searchMenu()">
                        </div>
                        <div class="col-md-3">
                            {{--<label><b>Filter by menu type :</b></label>--}}
                            <select class="form-control" id="filter_by_menu_type" onchange="searchMenu()">
                                <option value="">Filter by menu type</option>
                                @foreach($menu_type as $item)
                                    <option value="{{$item->menu_id}}">{{$item->menu_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" id="menu_category">
                            {{--<label><b>Filter by category :</b></label>--}}
                            <select class="form-control" id="filter_by_menu_category" onchange="searchMenu(2)">
                                <option value="">Filter by category</option>
                            </select>
                        </div>
                        <div class="col-md-3 p-0 text-left">
                            <a class="btn btn-primary reset-button" onclick="searchMenu(1)"><i
                                    class="i-Repeat-3 pr-2"></i> Reset</a>
                        </div>
                       {{-- <div class="col-md-8 mt-3 text-left">
                            <h6 class="mt-0 mb-1 mt-3 note-text">Note: Drag and drop the row to adjust ordering of items in website and
                                application.</h6>
                        </div>
                        <div class="col-md-12 mt-3 text-right">
                            <a title="Edit" class="action-links pr-3" href="{{route('side-menu-list')}}">List View</a>
                            <button class="btn btn-primary" data-target="#addMenuModal" data-toggle="modal">
                                Add Item
                            </button>
                        </div>--}}

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mt-3" id="found">
        <div class="row">
            <div class="col-md-6 text-left">
        <a title="Edit" class="action-links pr-3" href="{{route('side-menu-list')}}">List View</a>
    </div>
    <div class="col-md-6 text-right">
        <h6 class="mb-2 mt-1 note-text">Note: Drag and drop the row to adjust ordering of items in website and
            application.</h6>
    </div>
    </div>
    <div class="row mt-3 row_drag" id="all-side-menus"></div>
    <div class="row mt-1" id="not-found">
        <div class="col-md-12 mt-5 text-center">
            <img alt="" height="150" src="{{asset('public/images/not-found.png')}}">
            <h5 class="mt-3 not-found">Not Found</h5>
        </div>
    </div>
    <div class="row mt-0 mt-5" id="menus-not-found" style="padding-left: 10rem;padding-right: 10rem">
        <div class="col-md-12 mt-5 text-center" style="background:#EDF3F9;height: 330px;">
            <h5 class="mt-5"><b>"{{$restaurant['address'] ? $restaurant['address'] : ''}}"</b> has no menu added.</h5>
            <h5>Do you want to copy the complete menu
                from any other location?</h5>
            <h5 class="mt-3">
                Select Location <b>
                    <div class="row text-center mt-3 mb-3" id="copy_menu">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <select id="other_restaurant" class="form-control" onchange="getSelectedRestaurant()">
                                @foreach($another_restaurant as $res)
                                    <option value="{{$res->id}}">{{$res->address}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"></div>
                    </div>
                </b><a class="text-primary" onclick="goToMenu()">(Check menu here)</a>
            </h5>
            <p class="mt-3">(Note - This action will copy all menu types, categories, modifiers from another
                location).</p>
            <div class="row">
                <div class="col-md-12 text-center mt-2" id="copy_inprogress">
                    <button class="btn btn-primary" type="button" onclick="copyFromAnotherLocation()">
                        Yes, Copy all
                    </button>
                    <button class="btn btn-primary" data-target="#addMenuModal" data-toggle="modal">
                        Add Item
                    </button>
                </div>
                <div class="col-md-12 text-center mt-2" id="copy_completed">
                    <button class="btn btn-primary" type="button">
                        Copying...
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="add_action">
                    <form onsubmit="addNewMenu()">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" placeholder="Name" required>
                                    </div>
                                </div>
                                {{--                                <div class="col-md-12">--}}
                                {{--                                    <div class="form-group">--}}
                                {{--                                        <label for="price">Sell Item It's Own?</label>--}}
                                {{--                                    </div>--}}
                                {{--                                    <div class="row">--}}
                                {{--                                        <div class="col-md-1">--}}
                                {{--                                            <label class="radio radio-outline-primary">--}}
                                {{--                                                <input type="radio" class="" name="its_own" value="1">--}}
                                {{--                                                <span>Yes</span>--}}
                                {{--                                                <span class="checkmark"></span>--}}
                                {{--                                            </label>--}}
                                {{--                                        </div>--}}
                                {{--                                        <div class="col-md-1">--}}
                                {{--                                            <label class="radio radio-outline-secondary">--}}
                                {{--                                                <input checked type="radio" name="its_own" value="2">--}}
                                {{--                                                <span>No</span>--}}
                                {{--                                                <span class="checkmark"></span>--}}
                                {{--                                            </label>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                                <div class="col-md-12" id="showCategory">
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select id="category">
                                            @foreach($category as $value)
                                                <option
                                                    value="{{$value->category_id}}">{{$value->category_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
{{--                                <div class="col-md-12">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label for="price">Is it common item among all menus?</label>--}}
{{--                                    </div>--}}
{{--                                    <div class="row">--}}
{{--                                        <div class="col-md-1">--}}
{{--                                            <label class="radio radio-outline-primary">--}}
{{--                                                <input type="radio" id="is_common_yes" name="is_common" value="1">--}}
{{--                                                <span>Yes</span>--}}
{{--                                                <span class="checkmark"></span>--}}
{{--                                            </label>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-1">--}}
{{--                                            <label class="radio radio-outline-secondary">--}}
{{--                                                <input type="radio" id="is_common_no" name="is_common" value="2">--}}
{{--                                                <span>No</span>--}}
{{--                                                <span class="checkmark"></span>--}}
{{--                                            </label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="price">Item is in Stock?</label>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-1">
                                            <label class="radio radio-outline-primary">
                                                <input type="radio" id="is_in_stock_yes" name="is_in_stock" value="1">
                                                <span>Yes</span>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="radio radio-outline-primary">
                                                <input type="radio" id="is_in_stock_no" name="is_in_stock" value="0">
                                                <span>No</span>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price">Price In $</label>
                                        <input type="text" class="form-control" id="price" placeholder="Price in $"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax_rate">Tax Rate (in %)</label>
                                        <input type="text" class="form-control" id="tax_rate" value="08.25"
                                               placeholder="Tax Rate">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Single Page Image</label>
                                        <div class="custom-files" style="position: relative;">
                                            <input type="file" class="custom-file-input" id="menu_image_input1"
                                                   style="position: absolute;width: 1000px; height: 356px; margin-top: 4px;cursor:pointer">
                                            <img alt="" class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                                 id="menu_image1"
                                                 style="background-color: #eeeeee;width: 1000px; height: 356px; margin-top: 4px">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Menu Image</label>
                                        <div class="custom-files" style="position: relative;">
                                            <input type="file" class="custom-file-input" id="menu_image_input"
                                                   style="position: absolute;width: 1000px; height:356px; margin-top: 4px;cursor:pointer">
                                            <img alt="" class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                                 id="menu_image"
                                                 style="background-color: #eeeeee;width: 1000px; height: 356px;margin-top: 4px;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea style="resize: none" rows="4" class="form-control" placeholder="Description"
                                                  id="description" rows="18"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="modifier">Modifier Group</label>
                                        <select id="modifier" multiple>
                                            @foreach($all_groups as $value)
                                                <option
                                                    value="{{$value->modifier_group_id}}">{{$value->modifier_group_name}}</option>
                                            @endforeach
                                        </select>
                                        {{--<select id="modifier_copy" multiple></select>--}}
                                    </div>
                                    {{--                                    <div class="accordion mb-3" id="selected_modifiers">--}}
                                    {{--                                        <h6 class="mt-0 mb-4 note-text">Note: Drag and drop the row to adjust ordering--}}
                                    {{--                                            of items in--}}
                                    {{--                                            website and application.</h6>--}}
                                    {{--                                        <div class="table-responsive">--}}
                                    {{--                                            <table class="table table-bordered">--}}
                                    {{--                                                <thead>--}}
                                    {{--                                                <tr>--}}
                                    {{--                                                    <th scope="col">Name</th>--}}
                                    {{--                                                </tr>--}}
                                    {{--                                                </thead>--}}
                                    {{--                                                <tbody class="row_drag_modifier" id="accordion"></tbody>--}}
                                    {{--                                            </table>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}
                                </div>
                                {{--<div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Complete Meal</label>
                                        <select class="form-control" id="complete_meal" multiple>
                                            @foreach($menu_type as $item)
                                                <option value="{{$item->menu_id}}">{{$item->menu_name}}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-control" id="complete_meal_copy" multiple></select>
                                    </div>
                                </div>--}}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editMenuModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="addNewMenu()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="editName" placeholder="name" required>
                                </div>
                            </div>
                            {{--<div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Order No</label>
                                    <input type="number" class="form-control" id="editOrderNo"
                                           placeholder="Order number" required>
                                </div>
                            </div>--}}
                            {{--                            <div class="col-md-12">--}}
                            {{--                                <div class="form-group">--}}
                            {{--                                    <label for="price">Sell Item It's Own?</label>--}}
                            {{--                                </div>--}}
                            {{--                                <div class="row">--}}
                            {{--                                    <div class="col-md-1">--}}
                            {{--                                        <label class="radio radio-outline-primary">--}}
                            {{--                                            <input type="radio" id="edit_its_own_yes" name="edit_its_own" value="1">--}}
                            {{--                                            <span>Yes</span>--}}
                            {{--                                            <span class="checkmark"></span>--}}
                            {{--                                        </label>--}}
                            {{--                                    </div>--}}
                            {{--                                    <div class="col-md-1">--}}
                            {{--                                        <label class="radio radio-outline-secondary">--}}
                            {{--                                            <input type="radio" id="edit_its_own_no" name="edit_its_own" value="2">--}}
                            {{--                                            <span>No</span>--}}
                            {{--                                            <span class="checkmark"></span>--}}
                            {{--                                        </label>--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                            <div class="col-md-12" id="showEditCategory">
                                <div class="form-group">
                                    <label for="name">Category</label>
                                    <select id="editCategory" multiple>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
{{--                            <div class="col-md-12">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label for="price">Is it common item among all menus?</label>--}}
{{--                                </div>--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-1">--}}
{{--                                        <label class="radio radio-outline-primary">--}}
{{--                                            <input type="radio" id="edit_is_common_yes" name="edit_is_common" value="1">--}}
{{--                                            <span>Yes</span>--}}
{{--                                            <span class="checkmark"></span>--}}
{{--                                        </label>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-1">--}}
{{--                                        <label class="radio radio-outline-secondary">--}}
{{--                                            <input type="radio" id="edit_is_common_no" name="edit_is_common" value="2">--}}
{{--                                            <span>No</span>--}}
{{--                                            <span class="checkmark"></span>--}}
{{--                                        </label>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="price">Item is in Stock?</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-1">
                                        <label class="radio radio-outline-primary">
                                            <input type="radio" id="edit_is_in_stock_yes" name="edit_is_in_stock" value="1">
                                            <span>Yes</span>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="radio radio-outline-secondary">
                                            <input type="radio" id="edit_is_in_stock_no" name="edit_is_in_stock" value="0">
                                            <span>No</span>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="text" class="form-control" id="editPrice" placeholder="$" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Tax Rate (in %)</label>
                                    <input type="text" class="form-control" id="editTaxRate" placeholder="Tax Rate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Single Page Image</label>
                                    <div class="custom-files" style="position: relative;">
                                        <input type="file" class="custom-file-input" id="edit_menu_image_input1"
                                               style="position: absolute;width: 1000px; height: 356px; margin-top: 4px;cursor:pointer">
                                        <img class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                             id="editMenuImage1"
                                             style="background-color: #eeeeee;width: 1000px; height: 356px; margin-top: 4px">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Menu Image</label>
                                    <div class="custom-files" style="position: relative;">
                                        <input type="file" class="custom-file-input" id="edit_menu_image_input"
                                               style="position: absolute;width: 1000px; height: 356px; margin-top: 4px;cursor:pointer">
                                        <img class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                             id="editMenuImage"
                                             style="background-color: #eeeeee;width: 1000px; height: 356px; margin-top: 4px">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Description</label>
                                <div class="input-group">
                                        <textarea style="resize: none" class="form-control" placeholder="Description"
                                                  id="editDescription" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Modifier Groups</label>
                                    <select id="edit_modifier_groups" multiple>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                {{--                                <div class="accordion mb-3" id="edit_selected_modifiers">--}}
                                {{--                                    <h6 class="mt-0 mb-4">Note: Drag and drop the row to adjust ordering of items in--}}
                                {{--                                        website and application.</h6>--}}
                                {{--                                    <div class="table-responsive">--}}
                                {{--                                        <table class="table table-bordered">--}}
                                {{--                                            <thead>--}}
                                {{--                                            <tr>--}}
                                {{--                                                <th scope="col">Name</th>--}}
                                {{--                                            </tr>--}}
                                {{--                                            </thead>--}}
                                {{--                                            <tbody class="row_drag_modifier_edit" id="edit_accordion"></tbody>--}}
                                {{--                                        </table>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                            </div>
                            {{--<div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Complete Meal</label>
                                    <select class="form-control" id="edit_complete_meal" multiple></select>
                                </div>
                            </div>--}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-primary" onclick="deleteMenu(menuId , 'items')">Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="cropImageModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="" alt="crop image" id="crop_image_img">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="getCroppedImage()">Save changes</button>
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
        var crop;
        var imageSelected = false;
        var isAction = 'Add';
        var menuId;
        var allCategories;
        var allModifierGroups;
        var isiTsOwn;
        var editIsiTsOwn;
        var copyIsiTsOwn;
        var selectedModifierItems;
        var allMenuTypes;
        var selectedRestaurant = '{{$another_restaurant[0]['id']}}';
        var selectedModifiers = [];
        var aspectRatio = 1 / 1;

        $(document).ready(function () {
            $('#complete_meal').select2({});
            $('#copy_category').select2({});
            $('#copy_complete_meal').select2({});
            $('#copy_modifier').select2({});
            $('#menus-not-found').hide();
            $('#not-found').hide();
            $('#top-area').hide();
            $('#copy_completed').hide();
            $('#menu_category').hide();
            $('#showCategory').show();
            $('#selected_modifiers').hide();
            searchMenu();
            getRestaurantMenu({{$another_restaurant[0]->id}});
            /*
                        let action_type = $('input[name=action_type]:checked').val()
                        if (action_type == 1) {
                            $('#add_action').hide();
                            $('#copy_name').attr('readonly', true);
                            $('#copy_price').attr('readonly', true);
                            $('#copy_tax_rate').attr('readonly', true);
                            $('#copy_description').attr('readonly', true);
                            $('#copy_category').attr('disabled', true);
                            $('#copy_modifier').attr('disabled', true);
                            $('#copy_complete_meal').attr('disabled', true);
                        }
            */
        });

        // $('#modifier').on('select2:select', function (e) {
        //     var data = e.params.data;
        //     var modifier = JSON.parse(data.id);
        //     /*$('#accordion').append('<div class="card">' +
        //         '<div class="card-header" id="headingOne">' +
        //         '<h2 class="mb-0">\n' +
        //         '<button class="btn btn-link" type="button">' + modifier.modifier_group_name + '</button>' +
        //         '<i style="cursor:pointer" class="i-Arrow-Down float-right" data-toggle="collapse" data-target="#items_' + modifier.modifier_group_id + '" aria-expanded="false" aria-controls="collapseOne"></i>' +
        //         '</h2>' +
        //         '</div>' +
        //         '<div id="items_' + modifier.modifier_group_id + '" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">' +
        //         '</div>' +
        //         '</div>'
        //     );*/
        //
        //     $('#accordion').append('<div class="row selected-modifiers" id="main_' + modifier.modifier_group_id + '">' +
        //         '<div class="col-md-8 mb-3">' +
        //         '<input class="form-control modifier_id" type="text" readonly modifierId="' + modifier.modifier_group_id + '" value="' + modifier.modifier_group_name + '">' +
        //         '</div>' +
        //         '<div class="col-md-4 mb-3">' +
        //         '<input class="form-control order_no" type="number" id="modifier_order_' + modifier.modifier_group_id + '" placeholder="Order no"></div>' +
        //         '</div>');
        //
        //     var itemNo = '#items_' + modifier.modifier_group_id;
        //
        //     /*axios.get(baseUrl + 'get-modifier-side-menu/' + modifier.modifier_group_id).then(function (response) {
        //         selectedModifierItems = response.data.response;
        //         for (var i = 0; i < selectedModifierItems.length; i++) {
        //             var isIndex = selectedModifierItems[i];
        //             for (var j = 0; j < isIndex.items.length; j++) {
        //                 $(itemNo).append('<div class="card-body"> - ' + isIndex.items[j].item_name + '</div>');
        //             }
        //         }
        //     });*/
        // });
        //
        // $('#modifier').on('select2:unselect', function (e) {
        //     var data = e.params.data;
        //     var modifier = JSON.parse(data.id);
        //     var id = '#main_' + modifier.modifier_group_id;
        //     $(id).remove();
        //     /* var itemIndex;
        //      for (var k = 0; k < selectedModifierItems.length; k++) {
        //          if (selectedModifierItems[k].id == data.id) {
        //              itemIndex = k;
        //          }
        //          break;
        //      }
        //      selectedModifierItems.splice(itemIndex, 1);*/
        // });
        //
        // $('#edit_modifier_groups').on('select2:select', function (e) {
        //     var data = e.params.data;
        //     data.id = data.id.replace(/'/g, '"');
        //     var modifier = JSON.parse(data.id);
        //     $('#edit_accordion').append('<div class="row edit-selected-modifiers" id="edit_main_' + modifier.modifier_group_id + '">' +
        //         '<div class="col-md-8 mb-3">' +
        //         '<input class="form-control modifier_id" type="text" readonly modifierId="' + modifier.modifier_group_id + '" value="' + modifier.modifier_group_name + '">' +
        //         '</div>' +
        //         '<div class="col-md-4 mb-3">' +
        //         '<input class="form-control order_no" type="number" id="modifier_order_' + modifier.modifier_group_id + '" placeholder="Order no"></div>' +
        //         '</div>');
        // });
        //
        // $('#edit_modifier_groups').on('select2:unselect', function (e) {
        //     let data = e.params.data;
        //     data.id = data.id.replace(/'/g, '"');
        //     const modifier = JSON.parse(data.id);
        //     let elementId = '#edit_main_' + modifier.modifier_group_id;
        //     $(elementId).remove();
        // });

        $('input[name=its_own]').change(function () {
            isiTsOwn = $('input[name=its_own]:checked').val();
            if (isiTsOwn == 2) {
                $('#showCategory').show();
            } else {
                $('#showCategory').hide();
            }
        });

        $('input[name=copy_its_own]').change(function () {
            copyIsiTsOwn = $('input[name=copy_its_own]:checked').val();
            if (copyIsiTsOwn == 2) {
                $('#copyShowCategory').show();
            } else {
                $('#copyShowCategory').hide();
            }
        });

        $('input[name=action_type]').change(function () {
            let action_type = $('input[name=action_type]:checked').val();
            if (action_type == 2) {
                $('#copy_action').hide();
                $('#add_action').show();
            } else if (action_type == 1) {
                $('#copy_action').show();
                $('#add_action').hide();
            }
        });

        function onItemSelect() {
            /*hide the existing field*/
            $('#copy_modifier').html('');
            $('#copy_category').html('');
            $('#copy_complete_meal').html('');

            /*Getting and binding the selected item details*/

            axios.get(baseUrl + 'single-side-menu/' + $('#selected_item').val()).then(function (response) {
                $('#copy_name').val(response.data.response.item_name);
                $('#copy_price').val(response.data.response.item_price);
                $('#copy_tax_rate').val(response.data.response.tax_rate);
                $('#copy_description').val(response.data.response.item_description);
                $('#copy_menu_image').attr('src', baseUrl + 'public/storage/' + response.data.response.item_image);
                $('input[name=copy_its_own]').val(response.data.response.its_own);
                $('input[name=copy_its_own]').prop('checked', true);

                $('#copy_name').removeAttr('readonly', true);
                $('#copy_price').removeAttr('readonly', true);
                $('#copy_tax_rate').removeAttr('readonly', true);
                $('#copy_description').removeAttr('readonly', true);
                $('#copy_category').removeAttr('disabled', true);
                $('#copy_modifier').removeAttr('disabled', true);
                $('#copy_complete_meal').removeAttr('disabled', true);
                getModifierItems($('#selected_item').val());
            });
        }

        $('input[name=edit_its_own]').change(function () {
            editIsiTsOwn = $('input[name=edit_its_own]:checked').val();
            if (editIsiTsOwn == 2) {
                $('#showEditCategory').show();
            } else {
                $('#showEditCategory').hide();
            }
        });

        $(document).ready(function () {
            $('#category').select2({
                placeholder: "Select category"
            });
            $('#modifier').select2({
                placeholder: "Select modifier"
            });
            $('form').on('submit', function (e) {
                e.preventDefault();
            });
            $('#menu_image_input').on('change', function (e) {
                aspectRatio = 1 / 1;
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    console.log(event);
                    $('#crop_image_img').attr('src', event.target.result);
                    $('#addMenuModal').modal('hide');
                    $('#cropImageModal').modal('show');
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Add';
            });
            $('#menu_image_input1').on('change', function (e) {
                aspectRatio = 3 / 2;
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    console.log(event);
                    $('#crop_image_img').attr('src', event.target.result);
                    $('#addMenuModal').modal('hide');
                    $('#cropImageModal').modal('show');
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Add1';
            });
            $('#copy_menu_image_input').on('change', function (e) {
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);
                    $('#addMenuModal').modal('hide');
                    $('#cropImageModal').modal('show');
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'copy';
            });
            $('#edit_menu_image_input').on('change', function (e) {
                aspectRatio = 1 / 1;
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);

                    $('#editMenuModal').modal('hide');
                    $('#cropImageModal').modal('show');
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Edit';
            });
            $('#edit_menu_image_input1').on('change', function (e) {
                aspectRatio = 3 / 2;
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);

                    $('#editMenuModal').modal('hide');
                    $('#cropImageModal').modal('show');
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Edit1';
            });
            $('#crop_image_img').on('load', function () {
                var image = document.getElementById('crop_image_img');
                crop = new Cropper(image, {
                    minContainerWidth: 466,
                    minContainerHeight: 400,
                    aspectRatio: aspectRatio,
                    viewMode: 2,
                    crop(event) {
                    }
                });
            });
            $('#cropImageModal').on('hidden.bs.modal', function (event) {
                if (isAction == 'Add' || isAction == 'copy' || isAction == 'Add1') {
                    $('#addMenuModal').modal('show');
                } else if (isAction == 'Edit') {
                    $('#editMenuModal').modal('show');
                }
                else if (isAction == 'Edit1') {
                    $('#editMenuModal').modal('show');
                }
            })
        });

        function getCroppedImage() {
            var image = crop.getCroppedCanvas().toDataURL('image/jpg', '');
            if (isAction == 'Add') {
                $('#menu_image').attr('src', image);
            }
            if (isAction == 'Add1') {
                $('#menu_image1').attr('src', image);
            }
            else if (isAction == 'Edit') {
                $('#editMenuImage').attr('src', image);
            } else if (isAction == 'copy') {
                $('#copy_menu_image').attr('src', image);
            }
            else if (isAction == 'Edit1') {
                $('#editMenuImage1').attr('src', image);
            }
            $('#cropImageModal').modal('hide');
            imageSelected = true;
        }

        function addNewMenu() {
            var data;
            if (isAction == 'Add') {
                data = {
                    name: $('#name').val(),
                    price: $('#price').val(),
                    image: $('#menu_image').attr('src'),
                    image1: $('#menu_image1').attr('src'),
                    category: $('#category').val(),
                    modifier: $('#modifier').val(),
                    tax_rate: $('#tax_rate').val(),
                    description: $('#description').val(),
                    complete_meal_of: $('#complete_meal').val(),
                    its_own: 2,
                    is_common: $('input[name="is_common"]:checked').val(),
                    is_in_stock: $('input[name="is_in_stock"]:checked').val()

                };
            } else if (isAction == 'Edit' || isAction == 'Edit1') {
                /* $('.edit-selected-modifiers').each(function (index, obj) {
                     modifiers.push({
                         'modifier_group_id': obj.getElementsByClassName('modifier_id')[0].getAttribute('modifierid'),
                         'order_no': obj.getElementsByClassName('order_no')[0].value,
                     });
                 });*/
                data = {
                    name: $('#editName').val(),
                    price: $('#editPrice').val(),
                    image: $('#editMenuImage').attr('src'),
                    image1: $('#editMenuImage1').attr('src'),
                    category: $('#editCategory').val(),
                    modifier: $('#edit_modifier_groups').val(),
                    tax_rate: $('#editTaxRate').val(),
                    description: $('#editDescription').val(),
                    complete_meal_of: $('#edit_complete_meal').val(),
                    menu_id: menuId,
                    its_own: 2,
                    is_common: $('input[name="edit_is_common"]:checked').val(),
                    is_in_stock: $('input[name="edit_is_in_stock"]:checked').val()
                };
            }
            console.log(data);
            axios.post(baseUrl + 'add-side-menu', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                imageSelected = false;
                $('#name').val('');
                $('#price').val('');
                $('#menu_image').attr('src', baseUrl + 'public/assets/images/menu-default.png');
                $('#addMenuModal').modal('hide');
                setTimeout(function () {
                    //window.location.reload();
                }, 500);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }

        function copyMenu() {
            var data = {
                name: $('#copy_name').val(),
                price: $('#copy_price').val(),
                image: $('#copy_menu_image').attr('src'),
                category: $('#copy_category').val(),
                modifier: $('#copy_modifier').val(),
                tax_rate: $('#copy_tax_rate').val(),
                description: $('#copy_description').val(),
                complete_meal_of: $('#copy_complete_meal').val(),
                its_own: copyIsiTsOwn
            };

            axios.post(baseUrl + 'add-side-menu', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                imageSelected = false;
                $('#copy_name').val('');
                $('#copy_price').val('');
                $('#copy_tax_rate').val('');
                $('#copy_description').val('');
                $('#copy_modifier').val('');
                $('#copy_category').val('');
                $('#copy_complete_meal').val('');
                $('#copy_menu_image').attr('src', baseUrl + 'public/assets/images/menu-default.png');
                $('#addMenuModal').modal('hide');
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }

        function doEdit(itemId) {
            $('#edit_is_common_yes').prop('checked',false);
            $('#edit_is_common_no').prop('checked',false);
            $('#edit_is_in_stock_yes').prop('checked',false);
            $('#edit_is_in_stock_no').prop('checked',false);
            axios.get(baseUrl + 'single-side-menu/' + itemId).then(function (response) {
                $('#editMenuModal').modal('show');
                $('#editName').val(response.data.response.item_name);
                $('#editOrderNo').val(response.data.response.order_no);
                $('#editPrice').val(response.data.response.item_price);
                $('#editTaxRate').val(response.data.response.tax_rate);
                $('#editDescription').val(response.data.response.item_description);
                $('#editCategory').select2();
                $('#edit_modifier_groups').select2();
                $('#editMenuImage').attr('src', baseUrl + 'public/storage/' + response.data.response.item_image);
                $('#editMenuImage1').attr('src', baseUrl + 'public/storage/' + response.data.response.item_image_single);
                menuId = itemId;
                if (response.data.response.item_image) {
                    imageSelected = true;
                }
                isAction = 'Edit';
                if (response.data.response.is_common == 1) {
                    $('#edit_is_common_yes').prop('checked',true);
                } else if (response.data.response.is_common == 2) {
                    document.getElementById('edit_is_common_no').setAttribute('checked', true);
                    $('#edit_is_common_no').prop('checked',true);
                } else {
                    $('#edit_is_common_yes').prop('checked',false);
                    $('#edit_is_common_no').prop('checked',false);
                }

                if (response.data.response.is_in_stock == 1) {
                    $('#edit_is_in_stock_yes').prop('checked',true);
                } else if (response.data.response.is_in_stock == 0) {
                    document.getElementById('edit_is_in_stock_no').setAttribute('checked', true);
                    $('#edit_is_in_stock_no').prop('checked',true);
                } else {
                    $('#edit_is_in_stock_yes').prop('checked',false);
                    $('#edit_is_in_stock_no').prop('checked',false);
                }
                getModifierItems(itemId);
            });
        }

        function getModifierItems(itemId, isFrom) {
            $('#edit_modifier_groups').html('');
            $('#edit_accordion').html('');
            axios.get(baseUrl + 'get-modifier-items/' + itemId + '/item').then(function (response) {
                allModifierGroups = response.data.response;
                $('#edit_complete_meal').select2({});
                for (var i = 0; i < allModifierGroups.length; i++) {
                    // let isObj = JSON.stringify(allModifierGroups[i]);
                    // isObj = isObj.replace(/"/g, "'");
                    if (allModifierGroups[i].checked) {
                        if (isFrom) {
                            $('#copy_modifier').append('<option selected value="' + allModifierGroups[i].modifier_group_id + '">' +
                                allModifierGroups[i].modifier_group_identifier + '</option>');
                        }
                        /* $('#edit_accordion').append('<div class="row edit-selected-modifiers" id="edit_main_' + allModifierGroups[i].modifier_group_id + '">' +
                             '<div class="col-md-8 mb-3">' +
                             '<input class="form-control modifier_id" type="text" readonly modifierId="' + allModifierGroups[i].modifier_group_id + '" value="' + allModifierGroups[i].modifier_group_identifier + '">' +
                             '</div>' +
                             '<div class="col-md-4 mb-3">' +
                             '<input class="form-control order_no" type="number" id="modifier_order_' + allModifierGroups[i].modifier_group_id + '" value="' + allModifierGroups[i].order_no + '" placeholder="Order no"></div>' +
                             '</div>');*/
                        $('#edit_accordion').append('<tr class="edit-selected-modifiers" modifier_group_id="' + allModifierGroups[i].modifier_group_id + '"  id="edit_main_' + allModifierGroups[i].modifier_group_id + '">' +
                            '<td class="modifier_id" modifierId="' + allModifierGroups[i].modifier_group_id + '">' + allModifierGroups[i].modifier_group_identifier + '</td>' +
                            '</tr>');
                        $('#edit_selected_modifiers').show();
                        selectedModifiers.push({
                            'modifier_group_id': allModifierGroups[i].modifier_group_id,
                            'order_no': allModifierGroups[i].order_no,
                        });
                        $('#edit_modifier_groups').append('<option selected  value="' + allModifierGroups[i].modifier_group_id + '">' +
                            allModifierGroups[i].modifier_group_identifier + '</option>');
                    } else {
                        if (isFrom) {
                            $('#copy_modifier').append('<option value="' + allModifierGroups[i].modifier_group_id + '">' + allModifierGroups[i].modifier_group_identifier + '</option>');
                        }
                        $('#edit_modifier_groups').append('<option value="' + allModifierGroups[i].modifier_group_id + '">' + allModifierGroups[i].modifier_group_identifier + '</option>');
                    }
                }

                getSelectedMeal(itemId, isFrom);
                getCategories(itemId, isFrom);
            });
        }

        function getSelectedMeal(menuId, isFrom) {
            $('#edit_complete_meal').html('');
            $('#copy_complete_meal').html('');
            axios.get(baseUrl + 'get-selected-meal/' + menuId).then(function (response) {
                allMenuTypes = response.data.response;
                for (let i = 0; i < allMenuTypes.length; i++) {
                    if (allMenuTypes[i].checked) {
                        if (isFrom) {
                            $('#copy_complete_meal').append('<option selected value="' + allMenuTypes[i].menu_id + '">' + allMenuTypes[i].menu_name + '</option>');
                        } else {
                            $('#edit_complete_meal').append('<option selected value="' + allMenuTypes[i].menu_id + '">' + allMenuTypes[i].menu_name + '</option>');
                        }
                    } else {
                        if (isFrom) {
                            $('#copy_complete_meal').append('<option value="' + allMenuTypes[i].menu_id + '">' + allMenuTypes[i].menu_name + '</option>');
                        } else {
                            $('#edit_complete_meal').append('<option value="' + allMenuTypes[i].menu_id + '">' + allMenuTypes[i].menu_name + '</option>');
                        }
                    }
                }
                $('#assignMealModal').modal('show');
            });
        }

        function deleteMenu(itemId, table) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.valu) {
                    axios.get(baseUrl + 'delete-items/' + itemId + '/' + table).then(function (response) {
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

        function getCategories(menuId, isFrom) {
            $('#editCategory').html('');
            axios.get(baseUrl + 'get-categories/' + menuId + '/side').then(function (response) {
                allCategories = response.data.response;
                for (let i = 0; i < allCategories.length; i++) {
                    if (allCategories[i].checked) {
                        if (isFrom) {
                            $('#copy_category').append('<option selected value="' + allCategories[i].category_id + '">' + allCategories[i].category_name +
                                '</option>');
                            $('#copyShowCategory').show();
                        } else {
                            $('#editCategory').append('<option selected value="' + allCategories[i].category_id + '">' + allCategories[i].category_name +
                                '</option>');
                            $('#showEditCategory').show();
                        }
                    } else {
                        if (isFrom) {
                            $('#copy_category').append('<option value="' + allCategories[i].category_id + '">' + allCategories[i].category_name + '</option>');
                        } else {
                            $('#editCategory').append('<option value="' + allCategories[i].category_id + '">' + allCategories[i].category_name + '</option>');
                        }
                    }
                }
            });
        }

        function searchMenu(flag) {
            if (flag==1) {
                $('#filter_by_keyword').val('');
                $('#filter_by_menu_type').val('');
                $('#filter_by_menu_category').val(0);
            }
            var data = {
                'keyword': $('#filter_by_keyword').val(),
                'menu_type': $('#filter_by_menu_type').val(),
                'category_id': $('#filter_by_menu_category').val(),
            };
            if (!$('#filter_by_keyword').val()) {

            } else {
                $('#searching').show();
            }
            $('#all-side-menus').html('');
            axios.post(baseUrl + 'search-side-menu', data).then(function (response) {
                $('#searching').hide();
                if (response.data.response.length) {
                    $('#top-area').show();
                    $('#menus-not-found').hide();
                    $('#not-found').hide();
                    for (i = 0; i < response.data.response.length; i++) {

                        var obj = response.data.response[i];
                        var itemCategories = [];
                        var categories;

                        if (obj.item_thumbnail) {
                            obj.item_thumbnail = baseUrl + 'public/storage/' + obj.item_thumbnail;
                        } else {
                            obj.item_thumbnail = baseUrl + 'public/assets/images/menu-default.png';
                        }

                        for (j = 0; j < obj.category.length; j++) {
                            itemCategories.push(obj.category[j].category_name);
                        }

                        obj.item_description = obj.item_description ? obj.item_description : '';
                        categories = itemCategories.toString();
                        categories = categories ? categories : 'Not Added'

                        $('#all-side-menus').append('  <div class="col-md-4 mt-0 mb-0" item_id="' + obj.item_id + '">\n' +
                            '                                    <div class="card mb-4">\n' +
                            '                                        <div class="row" style="height: 120px;">\n' +
                            '                                            <div class="col-md-4">\n' +
                            '                                                <img class="card-img-left"\n' +
                            '                                                     src="' + obj.item_thumbnail + '"\n' +
                            '                                                     alt="" style="height:120px">\n' +
                            '                                            </div>\n' +
                            '                                            <div class="col-md-8 pt-2 pl-2">\n' +
                            '                                                      <div class="row">' +
                            ' <div class="col-md-8 text-left"><h6><b>' + obj.item_name + '</b></h6></div>' +
                            ' <div class="col-md-4 text-center pl-5"><i class="i-Pen-2" onclick="doEdit(' + obj.item_id + ')"></i> ' +
                            ' </div>' +
                            '</div>' +
                            '                            <div class="row mt-0">' +
                            '                                <div class="col-md-12 mt-0" id="menu-categories" style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap">' +
                            '                                Categories : ' + categories +
                            '                                </div>' + '                                            </div>\n' +
                            '                                <div class="col-md-12 pl-0">' +
                            '                               <h4 class="p-0 mt-4 text-right">  $' + obj.item_price + '</h4>' +
                            '                                </div>' + '                                            </div>\n' +
                            '                                        </div>\n' +
                            '                                    </div>\n' +
                            '                                </div>'
                        );

                        $('#all-side-menus-list').append('<tr>' +
                            '<td><img class="card-img-left" src="' + obj.item_thumbnail + '" alt="" style="height: 60px;\n' +
                            '    border-radius: 50%;\n' +
                            '    width: 60px;"></td>' +
                            '<td>' + obj.item_name + '</td>' +
                            '<td>' + obj.item_price + '</td>' +
                            '<td>' + categories + '</td>' +
                            '<td><i class="i-Pen-2" onclick="doEdit(' + obj.item_id + ')"></i> ' +
                            ' <i class="i-Close" onclick="deleteMenu(' + obj.item_id + ',' + '\'items\')"></i></td>' +
                            '</tr>');
                    }
                } else if ((!response.data.response.length) && (!$('#filter_by_menu_type').val()) && (!$('#filter_by_keyword').val())) {
                    $('#menus-not-found').show();
                } else if ((!response.data.response.length) && ($('#filter_by_menu_type').val() || $('#filter_by_keyword').val())) {
                    $('#not-found').show();
                }
                if ($('#filter_by_menu_type').val() && flag != 2) {
                    getMenuCategory($('#filter_by_menu_type').val());
                }
            });
        }

        function copyFromAnotherLocation() {
            $('#copy_inprogress').hide();
            $('#copy_completed').show();
            axios.post(baseUrl + 'copy-from-another-location', {
                'restaurant_id': '{{$restaurant['id']}}',
                'another_restaurant_id': $('#other_restaurant').val()
            }).then(function (response) {
                $('#copy_inprogress').show();
                $('#copy_completed').hide();
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

        function getSelectedRestaurant() {
            selectedRestaurant = $('#other_restaurant').val();
        }

        function getRestaurantMenu(restaurantId) {
            axios.get(baseUrl + 'get-restaurant-menu/' + restaurantId).then(function (response) {
                for (let i = 0; i < response.data.response.length; i++) {
                    $('#selected_item').append('<option value="' + response.data.response[i].item_id + '">' + response.data.response[i].item_name + '</option>');
                }
            }).catch(function (error) {
            });
        }

        function getMenuCategory(menuId) {
            $('#filter_by_menu_category').html('');
            axios.get(baseUrl + 'get-menu-category/' + menuId).then(function (response) {
                if (response.data.response.length > 0) {
                    for (let i = 0; i < response.data.response.length; i++) {
                        if (i < 1) {
                            $('#filter_by_menu_category').append('<option value="0">All Category</option>');
                        }
                        $('#filter_by_menu_category').append('' +
                            '<option value="' + response.data.response[i].category.category_id + '">' +
                            '' + response.data.response[i].category.category_name + '</option>'
                        );
                    }
                    $('#menu_category').show();
                }
            }).catch(function (error) {
            });
        }

        function goToMenu() {
            window.location.href = baseUrl + 'menu/other-location-menu/' + selectedRestaurant;
        }
    </script>
    <script type="text/javascript">
        $(".row_drag").sortable({
            delay: 50,
            stop: function (current, position) {
                let selectedRow = new Array();
                $('.row_drag>.col-md-4').each(function (index, value) {
                    console.log($(this).attr("item_id"));
                    selectedRow.push({
                        'order': index + 1,
                        'item_id': $(this).attr("item_id")
                    })
                });
                axios.post(baseUrl + 'update-row-order', {
                    'select_rows': selectedRow,
                    'table': 'items'
                }).then(function (response) {
                    if (response.data) {
                        selectedRow = new Array();
                    }
                    searchMenu();
                }).catch(function (error) {
                    selectedRow = new Array();
                });
            }
        });
    </script>
@endsection
