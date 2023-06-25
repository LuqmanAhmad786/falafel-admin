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

        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        .remainingCharacter {
            position: absolute;
            right: 3.5rem;
            border: transparent;
            background: transparent;
            color: rgba(0,0,0,0.7);
        }
        .totalRemainingCharacter{
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
            <a class="nav-link active" href="{{route('side-menu-categories')}}">Category(s)</a>
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
    {{--<div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">...</div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
    </div>--}}
    <div class="row m-0">
        {{--<div class="col-md-9">
            <h1 class="heading-white mb-1">Category(s)</h1>
--}}{{--            <h6 class="mt-0 mb-4 note-text">Note: Drag and drop the row to adjust ordering of categories.</h6>--}}{{--
        </div>--}}
        <div class="col-md-12 mt-1 text-right">
            <button class="btn btn-primary float-right"
                    onclick="getMenuType(1);">Add New
                Category
            </button>
            <a href="javascript:void(0)" onclick="syncClover()"> <button class="btn btn-primary float-right ml-5 mr-5" id="syncClover">Sync from Clover
                Category
            </button>
            </a>
        </div>
        {{-- @if(sizeOf($category))--}}
        {{--<div class="col-md-12 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <label><b>Filter by keyword:</b></label>
                    <input class="form-control" type="text" placeholder="Enter here.." id="filter_by_keyword"
                           onchange="searchCategory()">
                </div>
                <div class="col-md-4">
                    <label><b>Filter by menu type :</b></label>
                    <select class="form-control" id="filter_by_menu_type" onchange="searchCategory()">
                        <option value="">Select</option>
                        @foreach($menu_type as $item)
                            <option value="{{$item->menu_id}}">{{$item->menu_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 p-0 text-left mt-4">
                    <a class="action-links mt-1" onclick="searchCategory(1)">Reset List</a>
                </div>
            </div>
        </div>--}}
        <div class="col-md-12 mt-3" id="found_0">
            <div class="row">
            <h4 class="text-primary col-md-6 text-left">Category(s)</h4>
            <h6 class="mt-0 mb-4 col-md-6 note-text text-right">Note: Drag and drop the row to adjust ordering of categories.</h6>
            </div>
            <table class="table table-hover sortable" id="itemTable">
                <thead>
                <tr>
                    <th class="s_no">No.</th>
                    <th>Category Name <i class="i-Up---Down"></i></th>
                    <th>Clover ID</th>
                    <th>Item(s) <i class="i-Up---Down"></i></th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody class="row_drag_0" id="category_list_0"></tbody>
            </table>
        </div>
        <div class="col-md-12" id="found_1">
            <h4 class="text-primary">Lunch</h4>
            <table class="table table-hover sortable" id="itemTable">
                <thead>
                <tr>
                    <th class="s_no">No.</th>
                    <th>Category Name</th>
                    <th>Clover ID</th>
                    <th>Item(s)</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody class="row_drag_1" id="category_list_1">
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" placeholder="Enter name here" class="form-control" id="name" name="category" onkeydown="limitText(this.form.category,this.form.countdownCat, 30);" onkeyup='limitText(this.form.category,this.form.countdownCat,30);' required>
                            <input readonly type="text" name="countdownCat" size="1" class="text-right remainingCharacter" value="30 "><span class="totalRemainingCharacter">/ 30</span>
                        </div>
                        {{-- <div class="form-group">
                             <label for="order_no">Order No</label>
                             <input type="number" class="form-control" id="orderingNo" placeholder="Enter order number"
                                    required>
                         </div>--}}
                        <div class="form-group">
                            <div id="target"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" onclick="addNewCategory()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="editName" required>
                        </div>
                            <input type="hidden" class="form-control" id="editOrderingNo"
                                   placeholder="Enter order number" required>
                        <div class="form-group">
                            <div id="editTarget"></div>
                        </div>
                        <div class="form-group">
                            <div> <strong>Clover ID : </strong> <span id="categoryCloverID"></span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" onclick="addNewCategory()">Save</button>
                        <button class="btn btn-primary ml-1"
                                type="button" onclick="deleteCategory(menuId,'categories')">Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    <script>
        searchCategory();
        $(document).ready(function () {
            $('form').on('submit', function (e) {
                e.preventDefault();
            });
        });

        var menuId;
        var allMenuTypes;
        var allCategoryMenus;
        var menuArray = [];
        var editMenuArray = [];

        function syncClover(){
            $('#syncClover').prop('disabled', true);
            axios.get("{{url('/')}}/clover/fetch-item/categories").then((response) => {
                $('#syncClover').prop('disabled', false);
                toastr.success("Synced successfully", "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                window.location.reload();
            })
        }

        function addNewCategory() {
            var data;

            $.each($('.menu-checked:checked'), function (i) {
                if (menuArray.indexOf($(this).val()) == -1) {
                    menuArray.push($(this).val());
                }
            });

            $.each($('.edit-menu-checked:checked'), function (i) {
                if (editMenuArray.indexOf($(this).val()) == -1) {
                    editMenuArray.push($(this).val());
                }
            });

            if (menuId) {
                data = {
                    'name': $('#editName').val(),
                    'order_no': $('#editOrderingNo').val(),
                    'menu_array': editMenuArray,
                    'menu_id': menuId
                };
            } else {
                data = {
                    'name': $('#name').val(),
                    'order_no': $('#orderingNo').val(),
                    'menu_array': menuArray,
                };
            }

            axios.post(baseUrl + 'add-side-menu-category', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                if (menuId) {
                    $('#editCategoryModal').modal('hide');
                }
                menuId = '';
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

        function doEdit(category_id) {
            $('#editTarget').html('');
            axios.get(baseUrl + 'single-category/' + category_id).then(function (response) {
                getMenuDetails(response.data.response);
            });
        }
        function limitText(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }

        function getMenuDetails(item) {
            axios.get(baseUrl + 'get-category-menus/' + item.category_id).then(function (response) {
                allCategoryMenus = response.data.response;
                for (var i = 0; i < allCategoryMenus.length; i++) {
                    var radioBtn;
                    if (allCategoryMenus[i].checked) {
                        radioBtn = $('' +
                            '<input type="checkbox" ' +
                            'id="edit_menu_type_' + allCategoryMenus[i].menu_id + '" ' +
                            'name="rbtnCount" ' +
                            'checked ' +
                            'class="edit-menu-checked" ' +
                            'value="' + allCategoryMenus[i].menu_id + '"/>' +
                            '<label for="edit_menu_type_' + allCategoryMenus[i].menu_id + '" style="padding: 10px;">' + allCategoryMenus[i].menu_name + '</label>');
                    } else {
                        radioBtn = $('' +
                            '<input type="checkbox" ' +
                            'id="edit_menu_type_' + allCategoryMenus[i].menu_id + '" ' +
                            'name="rbtnCount" ' +
                            'class="edit-menu-checked" ' +
                            'value="' + allCategoryMenus[i].menu_id + '"/>' +
                            '<label for="edit_menu_type_' + allCategoryMenus[i].menu_id + '" style="padding: 10px;">' + allCategoryMenus[i].menu_name + '</label>');
                    }

                    radioBtn.appendTo('#editTarget');
                }

                $('#editName').val(item.category_name);
                $('#editOrderingNo').val(item.order_no);
                let cloverId = 'N/A'
                if(item.clover_id != null){
                    cloverId = item.clover_id;
                }
                $('#categoryCloverID').text(cloverId);
                $('#editCategoryModal').modal('show');
                menuId = item.category_id;

            });
        }

        function deleteCategory(itemId, table) {
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
                        toastr.success('Category Deleted Successfully.', "Success", {
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

        function getMenuType(type) {
            axios.get(baseUrl + 'get-menu-types').then(function (response) {
                allMenuTypes = response.data.response;
                for (var i = 0; i < allMenuTypes.length; i++) {
                    var radioBtn = $('' +
                        '<input type="checkbox" ' +
                        'id="menu_type_' + allMenuTypes[i].menu_id + '" ' +
                        'name="rbtnCount" ' +
                        'class="menu-checked" ' +
                        'value="' + allMenuTypes[i].menu_id + '"/>' +
                        '<label for="menu_type_' + allMenuTypes[i].menu_id + '" style="padding: 10px;">' + allMenuTypes[i].menu_name + '</label>');
                    radioBtn.appendTo('#target');
                    if (type == 1) {
                        $('#addCategoryModal').modal('show');
                    }
                }
            });
        }

        function searchCategory(flag) {
            if (flag) {
                $('#filter_by_keyword').val('');
                $('#filter_by_menu_type').val('');
            }
            var data = {
                'keyword': $('#filter_by_keyword').val(),
                'menu_type': $('#filter_by_menu_type').val(),
            };
            $('#not-found').hide();
            $('#found_0').hide();
            $('#found_1').hide();
            $('#category_list_0').html('');
            $('#category_list_1').html('');
            axios.post(baseUrl + 'search-category', data).then(function (response) {
                if (response.data.response.length) {
                    if (response.data.response[0]) {
                        if (response.data.response[0].categories.length > 0) {
                            $('#found_0').show();
                            for (let i = 0; i < response.data.response[0].categories.length; i++) {
                                let obj = response.data.response[0].categories[i];

                                let itemArray = [];

                                if (obj.items[0]) {
                                    for (let it = 0; it < obj.items.length; it++) {
                                        itemArray.push('<div>' + obj.items[it].item_name + ' </div>');
                                    }
                                }

                                let cloverID = 'N/A';
                                if(obj.clover_id != null){
                                    cloverID = obj.clover_id
                                }
                                itemArray = itemArray.toString();
                                itemArray = itemArray.replace(/,/g, '');
                                itemArray = itemArray ? itemArray : '-';

                                $('#category_list_0').append('<tr menu_id="' + obj.menu_id + '" category_id="' + obj.category_id + '">\n' +
                                    '                        <td>' + (i + 1) + '</td>\n' +
                                    '                        <td class="first-row">' + obj.category_name + '</td>\n' +
                                    '                        <td>' + cloverID + '</td>\n' +
                                    '                        <td>' + itemArray + '</td>\n' +
                                    '                        <td><a class="action-links" onclick="doEdit(' + obj.category_id + ')">Edit</a></td>\n' +
                                    '                    </tr>'
                                )
                                ;
                            }
                        }
                    }
                    if (response.data.response[1]) {
                        if (response.data.response[1].categories.length > 0) {
                            $('#found_1').show();
                            for (let i = 0; i < response.data.response[1].categories.length; i++) {
                                let obj = response.data.response[1].categories[i];

                                let itemArray = [];
                                if (obj.items[0]) {
                                    for (let it = 0; it < obj.items.length; it++) {
                                        itemArray.push('<div>' + obj.items[it].item_name + ' </div>');
                                    }
                                }


                                itemArray = itemArray.toString();
                                itemArray = itemArray.replace(/,/g, '');
                                itemArray = itemArray ? itemArray : '-';
                                let cloverID = 'N/A';
                                if(obj.clover_id != null){
                                    cloverID = obj.clover_id
                                }

                                $('#category_list_1').append('<tr menu_id="' + obj.menu_id + '" category_id="' + obj.category_id + '">\n' +
                                    '                        <td>' + (i + 1) + '</td>\n' +
                                    '                        <td class="first-row">' + obj.category_name + '</td>\n' +
                                    '                        <td>' + cloverID + '</td>\n' +
                                    '                        <td>' + itemArray + '</td>\n' +
                                    '                        <td><a class="action-links" onclick="doEdit(' + obj.category_id + ')">Edit</a></td>\n' +
                                    '                    </tr>');
                            }
                        }
                    }
                    $('#not-found').hide();
                    /* for (let i = 0; i < response.data.response.length; i++) {
                         let obj = response.data.response[i];

                         let menuArray = [];
                         let itemArray = [];

                         for (let m = 0; m < obj.menu.length; m++) {
                             menuArray.push(obj.menu[m].menu_name);
                         }
                         for (let it = 0; it < obj.side_menu.length; it++) {
                             itemArray.push(obj.side_menu[it].item_name);
                         }

                         menuArray = menuArray.toString();
                         itemArray = itemArray.toString();

                         $('#category_list').append('<tr>\n' +
                             '                        <td>' + (i + 1) + '</td>\n' +
                             '                        <td>' + obj.category_name + '</td>\n' +
                             '                        <td>' + menuArray + '</td>\n' +
                             '                        <td>' + itemArray + '</td>\n' +
                             '                        <td><a class="action-links" onclick="doEdit(' + obj.category_id + ')">Edit</a></td>\n' +
                             '                    </tr>');
                     }*/
                } else {
                    $('#found_0').hide();
                    $('#found_1').hide();
                    $('#not-found').show();
                }
            });
        }

    </script>
    <script type="text/javascript">
        $(".row_drag_0").sortable({
            delay: 50,
            stop: function (current, position) {
                let selectedRow = new Array();
                $('.row_drag_0>tr').each(function (index, value) {
                    selectedRow.push({
                        'order': index + 1,
                        'category_id': $(this).attr("category_id"),
                        'menu_id': $(this).attr("menu_id")
                    })
                });
                axios.post(baseUrl + 'update-row-order', {
                    'select_rows': selectedRow,
                    'table': 'categories'
                }).then(function (response) {
                    if (response.data) {
                        selectedRow = new Array();
                    }
                    // searchCategory();
                }).catch(function (error) {
                    selectedRow = new Array();
                });
            }
        });
    </script>
    <script type="text/javascript">
        $(".row_drag_1").sortable({
            delay: 50,
            stop: function (current, position) {
                let selectedRow = new Array();
                $('.row_drag_1>tr').each(function (index, value) {
                    selectedRow.push({
                        'order': index + 1,
                        'category_id': $(this).attr("category_id"),
                        'menu_id': $(this).attr("menu_id")
                    })
                });
                axios.post(baseUrl + 'update-row-order', {
                    'select_rows': selectedRow,
                    'table': 'categories'
                }).then(function (response) {
                    if (response.data) {
                        selectedRow = new Array();
                    }
                    searchCategory();
                }).catch(function (error) {
                    selectedRow = new Array();
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
