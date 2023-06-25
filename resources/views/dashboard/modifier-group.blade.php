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

        .i-Speach-Bubble-Asking {
            color: #a92219;
            font-size: 17px;
            font-weight: 200;
        }

        .add-item-box {
            border: 1px solid #d1d1d1;
            margin: 1px;
            padding: 15px;
        }

        .note-text {
            color: darkgrey !important;
        }
        .remainingCharacterName {
            position: absolute;
            right: 3.5rem;
            border: transparent;
            background: transparent;
            color: rgba(0,0,0,0.7);
        }
        .totalRemainingCharacterName{
            position: absolute;
            right: 2rem;
            color: rgba(0,0,0,0.7);
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
            <a class="nav-link" href="{{route('side-menu-list')}}">Item(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('items-availability')}}">Item(s) Availability</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{route('modifier-group')}}">Modifier Group</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('complete-meals')}}">Complete Meal(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('favorite-name')}}">Favorite Label</a>
        </li>
    </ul>
    <div class="row p-3">
        <div class="col-md-9">
            <h1 class="heading-white mb-1">Modifier(s)</h1>
        </div>
        <div class="col-md-12 mt-1 text-right">
            <button class="btn btn-primary float-right" data-target="#addGroupModal" data-toggle="modal">
                Add new modifier
            </button>

            <a href="javascript:void(0)" onclick="syncClover()">
                <button class="btn btn-primary float-right ml-5 mr-5" id="syncClover">Sync from Clover
                    Modifier
                </button>
            </a>
        </div>
    </div>
    <div class="row m-0">
        <div class="col-md-12 card p-3">
            <div class="row">
                <div class="col-md-6">
                    {{--<label><b>Filter by keyword:</b></label>--}}
                    <input type="input" class="form-control" name="keyword" id="keyword"
                           placeholder="Filter by keyword" onchange="searchModifiers()">
                </div>
                <div class="col-md-6 p-0">
                    <a class="btn btn-primary reset-button" onclick="searchModifiers(1)"><i class="i-Repeat-3"></i>
                        Reset</a>
                </div>
            </div>
        </div>
        <h6 class="mt-0 mb-1 mt-3 col-md-12 note-text text-right">Note: Drag and drop the row to adjust ordering of modifier groups.</h6>
        <div class="col-md-12 mt-3" id="found">
            <table class="table table-hover sortable" id="itemTable">
                <thead>
                <tr>
                    <th style="max-width: 43px">No.</th>
                    <th>Name <i class="i-Up---Down"></i></th>
                    <th>Unique Identifier <i class="i-Up---Down"></i></th>
                    <th>Clover ID</th>
                    <th>Main Item(s) Using This <i class="i-Up---Down"></i></th>
                    <th>Modifier Item(s) <i class="i-Up---Down"></i></th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody class="row_drag" id="modifier_list"></tbody>
            </table>
        </div>
        <div id="index_native" class="box"></div>
        <div class="col-md-12 mt-5 text-center" id="not-found">
            <img alt="" height="150" src="{{asset('public/images/not-found.png')}}">
            <h5 class="mt-3 not-found">Not Found</h5>
        </div>
    </div>
    <div class="modal fade" id="addGroupModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="modifier_name">Name(This will be visible on frontend)*</label>
                                    <input type="text" class="form-control" id="modifier_name"
                                           placeholder="Name" name="addName" onkeydown="limitTextAdd(this.form.addName,this.form.countdownAddName, 60);" onkeyup='limitTextAdd(this.form.addName,this.form.countdownAddName,60);' required>
                                    <input readonly type="text" name="countdownAddName" size="1" class="text-right remainingCharacterName" value="60 "><span class="totalRemainingCharacterName">/ 60</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="modifier_identifier">Unique Identifier(This will be unique to manage same group name for different items)*</label>
                                    <input type="text" class="form-control" id="modifier_identifier"
                                           placeholder="Identifier" name="identifier" onkeydown="limitTextAdd(this.form.identifier,this.form.countdownIdentifier, 60);" onkeyup='limitTextAdd(this.form.identifier,this.form.countdownIdentifier,60);' required>
                                    <input readonly type="text" name="countdownIdentifier" size="1" class="text-right remainingCharacterName" value="60"><span class="totalRemainingCharacterName">/ 60</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="modifier_items">Item(s)*</label>
                                    <select id="modifier_items" multiple="multiple" class="form-control  selectpicker" title="Choose one of the following..." required
                                            onchange="getSingleMenu(1,'modifier_items')">
                                        <option value="0">Select Item</option>
                                        @foreach($all_items as $value)
                                            <option value="{{$value->item_id}}">{{$value->item_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12" id="order_box">
                                <div class="row add-item-box">
                                    <div class="col-md-12 p-0">
                                        <h4>Modifiers</h4>
                                    </div>
                                    <div class="col-md-5 pl-0 pt-0 d-none">
                                        <label>Item Image*</label>
                                        <div class="custom-files" style="position: relative;">
                                            <img alt="" style="width: 290px; height: 168px; background-color: #eeeeee;"
                                                 src="{{asset('public/assets/images/menu-default.png')}}"
                                                 id="item_image">
                                        </div>
                                    </div>
                                    <div class="col-md-12 pr-0 pt-0">
                                        <div class="row pr-3">
                                            <div class="col-md-6 form-group">
                                                <label for="item_name">Name*</label>
                                                <input type="text" class="form-control" id="item_name"
                                                       placeholder="Enter name" name="addItemName" onkeydown="limitTextAddItem(this.form.addItemName,this.form.countdownAddItemName, 60);" onkeyup='limitTextAddItem(this.form.addItemName,this.form.countdownAddItemName,60);' required>
                                                <input readonly type="text" name="countdownAddItemName" size="1" class="text-right remainingCharacterName" value="60 "><span class="totalRemainingCharacterName">/ 60</span>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="item_price">Price*</label>
                                                <input type="text" class="form-control" id="item_price"
                                                       placeholder="Enter price">
                                            </div>
                                            <div class="col-md-12 mt-3 form-group d-none">
                                                <label for="item_description">Description*</label>
                                                <textarea style="resize: none" type="text" rows="4" class="form-control"
                                                          id="item_description"
                                                          placeholder="Enter description" name="AddItemDesc" onkeydown="limitTextAddDesc(this.form.AddItemDesc,this.form.countdownAddItemDesc, 60);" onkeyup='limitTextAddDesc(this.form.AddItemDesc,this.form.countdownAddItemDesc,60);' required></textarea>
                                                <input readonly type="text" name="countdownAddItemDesc" size="1" class="text-right remainingCharacterName" value="60 "><span class="totalRemainingCharacterName">/ 60</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center mt-3">
                                        <button type="button" class="btn btn-primary" onclick="pushItem()">Add
                                            item to this group
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" id="selected_items_show">
                                <div class="row">
                                    <div class="col-md-6 mt-1 mb-2 text-left">
                                        <b>Added Item(s)</b>
                                    </div>
                                    <div class="col-md-6 mt-1 mb-2 text-right">
                                        <h6 class="mt-0 note-text">Note: Drag and drop the row to adjust ordering
                                            of items.</h6>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody class="row_drag_modifier" id="selected_items"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2"><b>Rules</b></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <p>Set rules to control how customers select items in this modifier group</p>
                                    <p>
                                        <input type="checkbox" value="1" id="require_customer"> <b>Require customers to
                                            select an item? <i class="i-Speach-Bubble-Asking"
                                                               title="Example: If customers must choose something from this list, mark this box."></i></b>
                                    </p>
                                </div>
                                <div class="row" id="require_customer_item">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="item_count" required
                                                    onchange="countChange()">
                                                <option class="form-control" value="1">Exactly</option>
{{--                                                <option class="form-control" value="2">A Range</option>--}}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="exactly">
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Enter"
                                                   id="item_exactly" required value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row" id="range">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" placeholder="Enter"
                                                           id="item_range_from" required value="1">
                                                </div>
                                            </div>
                                            <div class="col-md-1 pl-1">
                                                and
                                            </div>
                                            <div class="col-md-5 pl-0">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" placeholder="Enter"
                                                           id="item_range_to" required value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="customer_maximum_amount">
                                    <div class="row">
                                        <div class="col-md-7 pr-0">
                                            <input checked type="checkbox" value="1" id="maximum_amount">
                                            <b>
                                                What's the maximum amount of items customers can select?
                                                <i class="i-Speach-Bubble-Asking"
                                                   title="Example: If there are 5 items in a modifier group and you want customers to only choose 3, you'd enter 3 here"></i>
                                            </b>
                                        </div>
                                        <div class="col-md-4 pl-0">
                                            <input type="text" class="form-control" id="item_maximum"
                                                   placeholder="Enter" required value="1">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="customer_maximum_amount" style="visibility: hidden">
                                    <div class="row">
                                        <div class="col-md-6 pr-0">
                                            <b>
                                                How many times can customers select any single item?
                                                <i class="i-Speach-Bubble-Asking"
                                                   title="Example: If you want customers to be able to select the same item 5 times, you'd enter 5 here."></i>
                                            </b>
                                        </div>
                                            <input type="text" class="form-control" id="single_item_maximum"
                                                   placeholder="Enter" required value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="item_submit" onclick="addNewGroup()">Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editGroupModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="edit_modifier_odering">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_modifier_name">Name*</label>
                                    <input type="text" class="form-control" id="edit_modifier_name" placeholder="name"
                                           name="editName" onkeydown="limitTextEdit(this.form.editName,this.form.countdownEditName, 30);" onkeyup='limitTextEdit(this.form.editName,this.form.countdownEditName,30);' required>
                                    <input readonly type="text" name="countdownEditName" size="1" class="text-right remainingCharacterName" value="30 "><span class="totalRemainingCharacterName">/ 30</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <div> <strong>Clover ID : </strong> <span id="itemCloverID"></span></div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_modifier_identifier">Unique Identifier(This will be unique to manage same group name for different items)*</label>
                                    <input type="text" class="form-control" id="edit_modifier_identifier"
                                           placeholder="Identifier" name="editIdentifier" onkeydown="limitTextEdit(this.form.editIdentifier,this.form.countdownEditIdentifier, 60);" onkeyup='limitTextEdit(this.form.editIdentifier,this.form.countdownEditIdentifier,60);' required>
                                    <input readonly type="text" name="countdownEditIdentifier" size="1" class="text-right remainingCharacterName" value="60 "><span class="totalRemainingCharacterName">/ 60</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_modifier_items">Item(s)*</label>
                                    <select id="edit_modifier_items" class="form-control" required
                                            onchange="getSingleMenu(2,'edit_modifier_items')" multiple="multiple">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row add-item-box">
                                    <div class="col-md-12 p-0 d-none">
                                        <p>You have selected this item. You can modify details below or can directly
                                            add to this group.</p>
                                    </div>
                                    <div class="col-md-5 pl-0 pt-0 form-group d-none">
                                        <label>Item Image</label>
                                        <div class="custom-files" style="position: relative;">
                                            <img alt="" style="width: 290px; height: 170px; background-color: #eeeeee;"
                                                 src="{{asset('public/assets/images/menu-default.png')}}"
                                                 id="edit_item_image">
                                        </div>
                                    </div>
                                    <div class="col-md-12 pr-0 pt-0">
                                        <div class="row pr-3">
                                            <div class="col-md-6 form-group">
                                                <label for="edit_item_name">Name*</label>
                                                <input type="text" class="form-control" id="edit_item_name"
                                                       placeholder="Enter name" name="editItemName" onkeydown="limitTextEditItem(this.form.editItemName,this.form.countdownEditItemName, 30);" onkeyup='limitTextEditItem(this.form.editItemName,this.form.countdownEditItemName,30);' required>
                                                <input readonly type="text" name="countdownEditItemName" size="1" class="text-right remainingCharacterName" value="30 "><span class="totalRemainingCharacterName">/ 30</span>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="edit_item_price">Price*</label>
                                                <input type="text" class="form-control" id="edit_item_price"
                                                       placeholder="Enter price">
                                            </div>
                                            <div class="col-md-12 mt-3 form-group d-none">
                                                <label for="edit_item_description">Description*</label>
                                                <textarea style="resize: none" type="text" rows="4" class="form-control"
                                                          id="edit_item_description"
                                                          placeholder="Enter description" name="editItemDesc" onkeydown="limitTextEditDesc(this.form.editItemDesc,this.form.countdownEditItemDesc, 60);" onkeyup='limitTextEditDesc(this.form.editItemDesc,this.form.countdownEditItemDesc,60);' required></textarea>
                                                <input type="text" name="countdownEditItemDesc" size="1" class="text-right remainingCharacterName" value="60 "><span class="totalRemainingCharacterName">/ 60</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center mt-3">
                                        <button type="button" class="btn btn-primary" onclick="pushItemEdit()">
                                            Add item to this group
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3" id="edit_selected_items_show">
                                <div class="row">
                                    <div class="col-md-6 mt-1 mb-2 text-left form-group">
                                        <b>Selected Item(s)</b>
                                    </div>
                                    <div class="col-md-6 mt-1 mb-2 text-right">
                                        <h6 class="mt-0 note-text">Note: Drag and drop the row to adjust ordering
                                            of items.</h6>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody class="row_drag_modifier" id="edit_selected_items"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2"><b>Rules</b></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <p>Set rules to control how customers select items in this modifier group</p>
                                    <p>
                                        <input type="checkbox" value="1" id="edit_require_customer"> <b>Require
                                            customers to
                                            select an item? <i class="i-Speach-Bubble-Asking"
                                                               title="Example: If customers must choose something from this list, mark this box."></i></b>
                                    </p>
                                </div>
                                <div class="row" id="edit_require_customer_item">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="edit_item_count" required
                                                    onchange="editCountChange()">
                                                <option class="form-control" value="1">Exactly</option>
{{--                                                <option class="form-control" value="2">A Range</option>--}}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="edit_exactly">
                                        <div class="form-group">
                                            <input type="text" class="form-control"
                                                   placeholder="Enter" id="edit_item_exactly"
                                                   required value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row" id="edit_range">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <input type="text" class="form-control"
                                                           placeholder="Enter" id="edit_item_range_from"
                                                           required value="1">
                                                </div>
                                            </div>
                                            <div class="col-md-1 pl-1">
                                                and
                                            </div>
                                            <div class="col-md-5 pl-0">
                                                <div class="form-group">
                                                    <input type="text" class="form-control"
                                                           placeholder="Enter" id="edit_item_range_to"
                                                           required value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="edit_customer_maximum_amount">
                                    <div class="row">
                                        <div class="col-md-7 pr-0">
                                            <input type="checkbox" value="1" id="edit_maximum_amount"> <b>What's the
                                                maximum
                                                amount
                                                of items customers can select? <i class="i-Speach-Bubble-Asking"
                                                                                  title="Example: If there are 5 items in a modifier group and you want customers to only choose 3, you'd enter 3 here"></i></b>
                                        </div>
                                        <div class="col-md-4 pl-0">
                                            <input type="text" class="form-control" id="edit_item_maximum"
                                                   placeholder="Enter" required value="1">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="edit_single_item_count" style="visibility: hidden">
                                    <div class="row">
                                        <div class="col-md-6 pr-0">
                                            <b>How many times can customers select any single item? <i
                                                    class="i-Speach-Bubble-Asking"
                                                    title="Example: If you want customers to be able to select the same item 5 times, you'd enter 5 here."></i></b>
                                        </div>
                                        <div class="col-md-4 pl-0">
                                            <input type="text" class="form-control" id="edit_single_item_maximum"
                                                   placeholder="Enter" required value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="edit_item_submit" class="btn btn-primary" onclick="addNewGroup()">
                                Save
                            </button>
                            <button class="btn btn-primary ml-1"
                                    onclick="deleteItems(menuId, 'modifier_groups')">
                                Delete
                            </button>
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
        function syncClover(){
            $('#syncClover').prop('disabled', true);
            axios.get("{{url('/')}}/clover/fetch-item/modifier").then((response) => {
                $('#syncClover').prop('disabled', false);
                toastr.success("Synced successfully", "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                window.location.reload();
            })
        }

        var isAction = 'Add';
        var menuId;
        var allModifierItems;
        var itemExaclty;
        var itemRangeFrom;
        var itemRangeTo;
        let itemArray = [];
        let itemArrayEdit = [];
        let currentPage = 1;
        searchModifiers();
        $(document).ready(function () {
            $('#modifier_items').select2({});
            $('#require_customer_item').hide();
            $('#edit_order_box').hide();
            $('#item_range_from').val('');
            $('#item_range_to').val('');
            $('#item_maximum').val(1);
            $('#item_exactly').val('')
            $('#selected_items_show').hide();
            $('#edit_selected_items_show').hide();
            $('#range').hide();
            $('#require_customer').change(function (e) {
                if (this.checked) {
                    $('#customer_maximum_amount').hide();
                    $('#require_customer_item').show();
                    $('#item_maximum').val('')
                    $('#item_exactly').val(1)
                } else {
                    $('#customer_maximum_amount').show();
                    $('#require_customer_item').hide();
                    $('#item_maximum').val(1)
                    $('#item_exactly').val('')
                }
            });
            $('#maximum_amount').change(function (e) {
                if (this.checked) {
                    $('#item_exactly').val('')
                } else {
                    $('#item_exactly').val(1)
                }
                console.log($('#item_exactly').val());
            });
            $('#edit_require_customer').change(function (e) {
                if (this.checked) {
                    $('#edit_customer_maximum_amount').hide();
                    $('#edit_require_customer_item').show();
                    $('#edit_range').hide();
                    $('#edit_item_maximum').val('')
                } else {
                    $('#edit_customer_maximum_amount').show();
                    $('#edit_require_customer_item').hide();
                    $('#edit_item_maximum').val($('#edit_item_maximum').val());
                }
            });
            $('#edit_maximum_amount').change(function (e) {
                if (this.checked) {
                    $('#edit_item_exactly').val('')
                } else {
                    $('#edit_item_exactly').val($('#edit_item_exactly').val());
                }
            });

            // $('#item_image').attr('disabled', true);
            // $('#item_name').attr('readonly', true);
            // $('#item_description').attr('readonly', true);
            // $('#item_price').attr('readonly', true);
        });

        function addNewGroup() {
            var data;

            if (isAction == 'Add') {
                data = {
                    name: $('#modifier_name').val(),
                    identifier: $("#modifier_identifier").val(),
                    items: itemArray,
                    item_exactly: $('#item_exactly').val(),
                    item_range_from: $('#item_range_from').val(),
                    item_range_to: $('#item_range_to').val(),
                    item_maximum: $('#item_maximum').val(),
                    single_item_maximum: $('#single_item_maximum').val(),
                    order_no: 0,
                    selected_items: $('#modifier_items').val(),
                };
            } else if (isAction == 'Edit') {
                data = {
                    name: $('#edit_modifier_name').val(),
                    identifier: $("#edit_modifier_identifier").val(),
                    items: itemArrayEdit,
                    item_exactly: $('#edit_item_exactly').val(),
                    item_range_from: $('#edit_item_range_from').val(),
                    item_range_to: $('#edit_item_range_to').val(),
                    item_maximum: $('#edit_item_maximum').val(),
                    single_item_maximum: $('#edit_single_item_maximum').val(),
                    order_no: $('#edit_modifier_odering').val(),
                    menu_id: menuId,
                    selected_items: $('#edit_modifier_items').val(),
                };
            }
            // console.log(data);return false;
            axios.post(baseUrl + 'add-modifier-group', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                searchModifiers();
                $("#editGroupModal").modal('hide');
                $("#addGroupModal").modal('hide');
                // if (isAction == 'Add') {
                //     setTimeout(function () {
                //         window.location.reload();
                //     }, 500);
                // }
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
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

        function doEdit(itemId) {
            axios.get(baseUrl + 'single-modifier/' + itemId).then(function (response) {
                $('#edit_modifier_items').select2({});
                $('#edit_selected_items').html('');
                itemArrayEdit = response.data.response.items;
                for (let i = 0; i < response.data.response.items.length; i++) {
                    $('#edit_selected_items_show').show();
                    if (response.data.response.items[i].added_from == 2) {
                        $('#edit_selected_items').append('<tr ' +
                            'item_name="' + itemArrayEdit[i].item_name + '"' +
                            'item_description="' + itemArrayEdit[i].item_description + '"' +
                            'item_price="' + itemArrayEdit[i].item_price + '"' +
                            'item_image="' + itemArrayEdit[i].item_image + '"' +
                            'item_id="' + itemArrayEdit[i].item_id + '"' +
                            'its_own="' + itemArrayEdit[i].its_own + '"' +
                            'type="2"' +
                            '>' +
                            '<td>' + response.data.response.items[i].item_name + '</td>' +
                            '<td><input type="tel" name="price" value="'+response.data.response.items[i].item_price+'"/></td>' + /*$' + response.data.response.items[i].item_price + '*/
                            '<td><a class="action-links" onclick="updateSelectedItemPrice(' + i + ',this)">Update</a> | <a class="action-links" onclick="removeSelectedItem(' + i + ',2)">Delete</a></td>' +
                            '</tr>');
                    }
                }
                setSingleModifier(response.data.response);
            });
        }

        function setSingleModifier(item) {
            axios.get(baseUrl + 'get-modifier-items/' + item.modifier_group_id + '/modifier').then(function (response) {
                allModifierItems = response.data.response;
                $('#edit_modifier_items').html('');
                for (let i = 0; i < allModifierItems.length; i++) {
                    if (allModifierItems[i].checked) {
                        $('#edit_modifier_items').append('<option selected value="' + allModifierItems[i].item_id + '">' +
                             allModifierItems[i].item_name +
                             '</option>');
                    } else {
                        $('#edit_modifier_items').append('<option value="' + allModifierItems[i].item_id + '">' + allModifierItems[i].item_name + '</option>');
                    }
                }
                $('#editGroupModal').modal('show');
                $('#edit_modifier_name').val(item.modifier_group_name);
                $('#itemCloverID').text(item.clover_id ? item.clover_id : 'N/A');
                $('#edit_modifier_identifier').val(item.modifier_group_identifier);
                $('#edit_modifier_odering').val(item.order_no);
                if (item.is_rule == 1) {
                    $('#edit_item_count').val(1);
                    $('#edit_item_range_from').val('');
                    $('#edi_item_range_to').val('');
                    $('#edit_item_maximum').val('');
                    $('#edit_item_exactly').val(item.item_exactly);
                    $('#edit_require_customer').prop("checked", true);
                    $('#edit_exactly').show();
                    $('#edit_range').hide();
                    $('#edit_customer_maximum_amount').hide();
                } else if (item.is_rule == 2) {
                    $('#edit_item_count').val(2);
                    $('#edit_item_exactly').val('');
                    $('#edit_item_maximum').val('');
                    $('#edit_item_range_from').val(item.item_range_from);
                    $('#edit_item_range_to').val(item.item_range_to);
                    $('#edit_require_customer').prop("checked", true);
                    $('#edit_exactly').hide();
                    $('#edit_range').show();
                    $('#edit_customer_maximum_amount').hide();
                } else if (item.is_rule == 3) {
                    $('#edit_item_range_from').val('');
                    $('#edi_item_range_to').val('');
                    $('#edit_item_exactly').val('');
                    $('#edit_maximum_amount').prop("checked", true);
                    $('#edit_item_maximum').val(item.item_maximum);
                    $('#edit_require_customer_item').hide();
                }
                $('#edit_single_item_maximum').val(item.single_item_maximum);
                menuId = item.modifier_group_id;
                itemExaclty = item.item_exactly;
                itemRangeFrom = item.item_range_from;
                itemRangeTo = item.item_range_to;
                isAction = 'Edit';
            });
        }

        function countChange() {
            if ($('#item_count').val() == 1) {
                $('#exactly').show();
                $('#range').hide();
                $('#item_range_from').val('');
                $('#item_range_to').val('');
                $('#item_exactly').val(1);
            } else {
                $('#range').show();
                $('#exactly').hide();
                $('#item_exactly').val('');
                $('#item_range_from').val(1);
                $('#item_range_to').val(1);
            }
        }

        function editCountChange() {
            if ($('#edit_item_count').val() == 1) {
                $('#edit_exactly').show();
                $('#edit_range').hide();
                $('#edit_item_range_from').val('');
                $('#edit_item_range_to').val('');
                $('#edit_item_exactly').val(itemExaclty);
            } else {
                $('#edit_range').show();
                $('#edit_exactly').hide();
                $('#edit_item_exactly').val('');
                $('#edit_item_range_from').val(itemRangeFrom);
                $('#edit_item_range_to').val(itemRangeTo);
            }
        }

        function searchModifiers(flag) {
            if (flag) {
                $('#keyword').val('');
            }
            var data = {
                'keyword': $('#keyword').val(),
            };
            $('#not-found').hide();
            $('#modifier_list').html('');
            axios.post(baseUrl + 'search-modifier', data).then(function (response) {
                if (response.data.response.length) {
                    $('#found').show();
                    $('#not-found').hide();
                    for (let i = 0; i < response.data.response.length; i++) {
                        let obj = response.data.response[i];
                        let mainItem = [];
                        let modifierItem = [];
                        for (let x = 0; x < obj.items.length; x++) {
                            if (obj.items[x].added_from == 1) {
                                mainItem.push('<div>' + obj.items[x].item_name + '</div>');
                            } else if (obj.items[x].added_from == 2) {
                                modifierItem.push('<div>' + obj.items[x].item_name + '</div>');
                            }
                        }

                        mainItem = mainItem.toString();
                        mainItem = mainItem.replace(/,/g, '');
                        modifierItem = modifierItem.toString();
                        modifierItem = modifierItem.replace(/,/g, '');
                        console.log(modifierItem.replace(/,/g, ' '));

                        let cloverId = 'N/A';
                        if(obj.clover_id != null){
                            cloverId = obj.clover_id;
                        }
                        obj.clover_id ? obj.clover_id : 'N/A'
                        $('#modifier_list').append('<tr modifier_group_id="' + obj.modifier_group_id + '">\n' +
                            '                        <td>' + (i + 1) + '</td>\n' +
                            '                        <td class="first-row">' + obj.modifier_group_name + '</td>\n' +
                            '                        <td>' + obj.modifier_group_identifier + '</td>\n' +
                            '                        <td>' + cloverId + '</td>\n' +
                            '                        <td>' + mainItem + '</td>\n' +
                            '                        <td>' + modifierItem + '</td>\n' +
                            '                        <td><a class="action-links" onclick="doEdit(' + obj.modifier_group_id + ')">Edit</td>\n' +
                            '                    </tr>');
                    }
                    if(response.data.response.length > 10){
                        paginator({
                            table: document.getElementById("found").getElementsByTagName("table")[0],
                            box: document.getElementById("index_native"),
                            active_class: "color_page",
                            rows_per_page: response.data.pagination_limit,
                            page: currentPage,
                            tail_call: function(config){
                                currentPage = config.page;
                            }
                        });
                    }
                } else {
                    $('#found').hide();
                    $('#not-found').show();
                }
            });
        }

        let itemImage;
        let itemId;
        let itsOwn;

        function getSingleMenu(status, elementId) {
            elementId = '#' + elementId;
            if (status == 1) {
                itemId = selectedItem.item_id;
                itemImage = selectedItem.item_image;
                itsOwn = selectedItem.its_own;
                $('#order_box').show();
            } else if (status == 2) {
                //onItemSelect($(elementId).val());
            }
        }

        function cancelItem(status) {
            console.log(status);
            if (status == 1) {
                $('#item_name').val(' ');
                $('#item_description').val(' ');
                $('#item_price').val(' ');
                $('#item_image').attr('src', baseUrl + 'public/assets/images/menu-default.png');
                $('#item_image').removeAttr('disabled', false);
                $('#item_name').removeAttr('readonly', false);
                $('#item_description').removeAttr('readonly', false);
                $('#item_price').removeAttr('readonly', false);
                $('#item_submit').attr('disabled', false);
            } else if (status == 2) {
                $('#edit_item_name').val(' ');
                $('#edit_item_description').val(' ');
                $('#edit_item_price').val(' ');
                $('#edit_item_image').attr('src', baseUrl + 'public/assets/images/menu-default.png');
                $('#edit_item_image').removeAttr('disabled', false);
                $('#edit_item_name').removeAttr('readonly', false);
                $('#edit_item_description').removeAttr('readonly', false);
                $('#edit_item_price').removeAttr('readonly', false);
                $('#edit_item_submit').attr('disabled', false);
            }
        }

        function pushItem() {
            if (!$('#item_name').val()) {
                alert('Please enter item name');
                return false;
            }
            if(isNaN($('#item_price').val()) || !$('#item_price').val()) {
                alert('Price should be a numeric value');
                return false;
            }
            else {
                itemArray.push({
                    item_name: $('#item_name').val(),
                    item_description: $('#item_description').val(),
                    item_price: $('#item_price').val(),
                    item_image: itemImage,
                    item_id: itemId,
                    its_own: itsOwn,
                    order_no: itemArray.length + 1,
                    image_src: $('#item_image').attr('src'),
                });

                $('#selected_items').html('');
                $('#item_submit').attr('disabled', false);

                for (let i = 0; i < itemArray.length; i++) {
                    $('#selected_items_show').show();
                    if (itemArray[i].added_from != 1) {
                        $('#selected_items').append('<tr ' +
                            'item_name="' + itemArray[i].item_name + '"' +
                            'item_description="' + itemArray[i].item_description + '"' +
                            'item_price="' + itemArray[i].item_price + '"' +
                            'item_image="' + itemArray[i].item_image + '"' +
                            'item_id="' + itemArray[i].item_id + '"' +
                            'its_own="' + itemArray[i].its_own + '"' +
                            'type="1"' +
                            '>' +
                            '<td>' + itemArray[i].item_name + '</td>' +
                            '<td>$' + itemArray[i].item_price + '</td>' +
                            '<td><a class="action-links" onclick="removeSelectedItem(' + i + ',1)">Delete</a></td>' +
                            '</tr>');
                    }
                    /* $('#selected_items').append('<div class="col-md-4"><div class="card mb-4">\n' +
                        '                        <div class="d-flex flex-column flex-sm-row">\n' +
                        '                            <div class="">\n' +
                        '                                <img class="card-img-left" width="100" height="80"\n' +
                        '                                     src="' + itemArray[i].image_src + '" alt="">\n' +
                        '                            </div>\n' +
                        '                            <div class="flex-grow-1 pt-2 pl-2">\n' +
                        '                                <p class="pb-0 mb-0">' + itemArray[i].item_name +
                        '<i class="i-Remove ml-3" onclick="removeSelectedItem(' + i + ',1)"></i>' +
                        '</p>\n' +
                        '                                <p class="pt-0 mt-0">Price : $' + itemArray[i].item_price + '</p>\n' +
                        '                            </div>\n' +
                        '                        </div>\n' +
                        '                    </div></div>'
                    );*/
                }

                /*Reset the fields*/
                // $('#modifier_items').val('');
                $('#item_name').val('');
                $('#item_price').val('');
                $('#item_description').val('');
                // $('#item_image').attr('src', baseUrl + 'public/' + 'assets/images/menu-default.png');
                // $('#item_image').attr('disabled', true);
                // $('#item_description').attr('disabled', true);
                // $('#item_name').attr('readonly', true);
                // $('#item_price').attr('readonly', true);
                // $('#item_submit').removeAttr('disabled', true);
                // $('#order_box').hide();
            }
        }

        function pushItemEdit() {
            if (!$('#edit_item_name').val()) {
                alert('Please enter item name');
                return false;
            }
            if(isNaN($('#edit_item_price').val()) || !$('#edit_item_price').val()) {
                alert('Price should be a numeric value');
                return false;
            } else {
                itemArrayEdit.push({
                    item_name: $('#edit_item_name').val(),
                    item_description: $('#edit_item_description').val(),
                    item_price: $('#edit_item_price').val(),
                    item_image: itemImage,
                    item_id: itemId,
                    its_own: itsOwn,
                    order_no: itemArrayEdit.length + 1,
                    image_src: $('#edit_item_image').attr('src'),
                });

                $('#edit_item_submit').attr('disabled', false);
                $('#edit_selected_items').html(' ');
                $('#edit_selected_items_show').hide();
                for (let i = 0; i < itemArrayEdit.length; i++) {
                    $('#edit_selected_items_show').show();
                    if (itemArrayEdit[i].added_from != 1) {
                        $('#edit_selected_items').append('<tr ' +
                            'item_name="' + itemArrayEdit[i].item_name + '"' +
                            'item_description="' + itemArrayEdit[i].item_description + '"' +
                            'item_price="' + itemArrayEdit[i].item_price + '"' +
                            'item_image="' + itemArrayEdit[i].item_image + '"' +
                            'item_id="' + itemArrayEdit[i].item_id + '"' +
                            'its_own="' + itemArrayEdit[i].its_own + '"' +
                            'type="2"' +
                            '>' +
                            '<td>' + itemArrayEdit[i].item_name + '</td>' +
                            '<td>$' + itemArrayEdit[i].item_price + '</td>' +
                            '<td><a class="action-links" onclick="removeSelectedItem(' + i + ',2)">Delete</a></td>' +
                            '</tr>');
                    }
                }

                /*Reset the fields*/
                // $('#edit_modifier_items').val('');
                $('#edit_item_name').val('');
                $('#edit_item_price').val('');
                $('#edit_item_description').val('');
                // $('#edit_item_image').attr('src', baseUrl + 'public/' + 'assets/images/menu-default.png');
                // $('#edit_item_image').attr('disabled', true);
                // $('#edit_item_description').attr('disabled', true);
                // $('#edit_item_name').attr('readonly', true);
                // $('#edit_item_price').attr('readonly', true);
                // $('#edit_item_submit').removeAttr('disabled', true);
                $('#edit_order_box').hide();
            }
        }

        function onItemSelect(selectedId) {
            axios.get(baseUrl + 'single-side-menu/' + selectedId).then(function (response) {
                $('#edit_order_box').show();
                let selectedItem = response.data.response;
                let itemName = 'You have selected ' + selectedItem.item_name + ' Modify details below and Add';
                $('#edit_selected_text').text(itemName);
                $('#edit_item_name').val(selectedItem.item_name);
                $('#edit_item_description').val(selectedItem.item_description);
                $('#edit_item_price').val(0);
                $('#edit_item_image').attr('src', baseUrl + 'public/storage/' + selectedItem.item_image);
                $('#edit_item_image').removeAttr('disabled', true);
                $('#edit_item_name').removeAttr('readonly', true);
                $('#edit_item_description').removeAttr('readonly', true);
                $('#edit_item_price').removeAttr('readonly', true);
                $('#edit_item_submit').attr('disabled', true);
                itemId = selectedItem.item_id;
                itemImage = selectedItem.item_image;
                itsOwn = selectedItem.its_own;
            });
        }

        function removeSelectedItem(i, status) {
            if (status == 1) {
                itemArray.splice(i, 1);
                $('#selected_items').html('');
                $('#selected_items_show').hide();
                $('#item_submit').attr('disabled', false);
                $('#selected_items_show').hide();
                for (let i = 0; i < itemArray.length; i++) {
                    $('#selected_items_show').show();
                    $('#selected_items').append('<tr ' +
                        'item_name="' + itemArray[i].item_name + '"' +
                        'item_description="' + itemArray[i].item_description + '"' +
                        'item_price="' + itemArray[i].item_price + '"' +
                        'item_image="' + itemArray[i].item_image + '"' +
                        'item_id="' + itemArray[i].item_id + '"' +
                        'its_own="' + itemArray[i].its_own + '"' +
                        'type="1"' +
                        '>' +
                        '<td>' + itemArray[i].item_name + '</td>' +
                        '<td>$' + itemArray[i].item_price + '</td>' +
                        '<td><a class="action-links" onclick="removeSelectedItem(' + i + ',1)">Delete</a></td>' +
                        '</tr>');
                }
            } else if (status == 2) {
                var item_id = itemArrayEdit[i].item_id;
                deleteSelectedItem(item_id);
                itemArrayEdit.splice(i, 1);
                $('#edit_item_submit').attr('disabled', false);
                $('#edit_selected_items').html(' ');
                $('#edit_selected_items_show').hide();
                for (let i = 0; i < itemArrayEdit.length; i++) {
                    $('#edit_selected_items_show').show();
                    if (itemArrayEdit[i].added_from != 1) {
                        $('#edit_selected_items').append('<tr ' +
                            'item_name="' + itemArrayEdit[i].item_name + '"' +
                            'item_description="' + itemArrayEdit[i].item_description + '"' +
                            'item_price="' + itemArrayEdit[i].item_price + '"' +
                            'item_image="' + itemArrayEdit[i].item_image + '"' +
                            'item_id="' + itemArrayEdit[i].item_id + '"' +
                            'its_own="' + itemArrayEdit[i].its_own + '"' +
                            'type="2"' +
                            '>' +
                            '<td>' + itemArrayEdit[i].item_name + '</td>' +
                            '<td>$' + itemArrayEdit[i].item_price + '</td>' +
                            '<td><a class="action-links" onclick="removeSelectedItem(' + i + ',2)">Delete</a></td>' +
                            '</tr>');
                    }
                }
            }
        }

        function deleteSelectedItem(item_id){
            var data = {
                'menu_id': menuId,
                'item_id': item_id
            };
            axios.post(baseUrl + 'delete-modifiergroup-item', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            });
        }

        function updateSelectedItemPrice(i,el){
            var updatePrice = $(el).parent().parent().find("input[name=price]").val();
            itemArrayEdit[i].item_price = updatePrice;
            var item_id = itemArrayEdit[i].item_id;
            var item_price = updatePrice;
            var data = {
                'menu_id': menuId,
                'item_price': item_price,
                'item_id': item_id
            };
            axios.post(baseUrl + 'upadate-modifiergroup-item-price', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            });
        }

        function limitTextAddDesc(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
        function limitTextAddItem(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
        function limitTextAdd(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
        function limitTextEdit(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
        function limitTextEditDesc(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }
        function limitTextEditItem(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }

    </script>
    <script type="text/javascript">
        $(".row_drag").sortable({
            delay: 50,
            stop: function (current, position) {
                let selectedRow = new Array();
                $('.row_drag>tr').each(function (index, value) {
                    selectedRow.push({
                        'order': index + 1,
                        'modifier_group_id': $(this).attr("modifier_group_id")
                    })
                });
                axios.post(baseUrl + 'update-row-order', {
                    'select_rows': selectedRow,
                    'table': 'modifier_groups'
                }).then(function (response) {
                    if (response.data) {
                        selectedRow = new Array();
                    }
                    // searchModifiers();
                }).catch(function (error) {
                    selectedRow = new Array();
                });
            }
        });
    </script>
    <script type="text/javascript">
        $(".row_drag_modifier").sortable({
            delay: 50,
            stop: function (current, position) {
                let selected = new Array();
                let editSelected = new Array();
                $('.row_drag_modifier>tr').each(function (index, value) {
                    if (selected.indexOf($(this).attr("modifier_group_id") != -1)) {
                        if ($(this).attr("type") == 1) {
                            selected.push({
                                'order_no': index + 1,
                                'item_id': $(this).attr("item_id"),
                                'item_name': $(this).attr("item_name"),
                                'item_description': $(this).attr("item_description"),
                                'item_price': $(this).attr("item_price"),
                                'item_image': $(this).attr("item_image"),
                                'its_own': $(this).attr("its_own")
                            });
                        } else if ($(this).attr("type") == 2) {
                            editSelected.push({
                                'order_no': index + 1,
                                'item_id': $(this).attr("item_id"),
                                'item_name': $(this).attr("item_name"),
                                'item_description': $(this).attr("item_description"),
                                'item_price': $(this).attr("item_price"),
                                'item_image': $(this).attr("item_image"),
                                'its_own': $(this).attr("its_own")
                            });
                        }
                    }
                    itemArray = selected;
                    itemArrayEdit = editSelected;
                    console.log(selected, editSelected);
                });
            }
        });
    </script>
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
