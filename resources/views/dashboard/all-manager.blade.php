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
        .card {
            padding: 15px;
        }
    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Users</h2>
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
            <a class="nav-link" href="{{route('customers')}}">Customer(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('guests')}}">Guest(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{route('managers')}}">Manager(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('server-users')}}">Server App User(s)</a>
        </li>
    </ul>
    <div class="row">
        <div class="col-md-6">
            <h1 class="heading-white mb-4">Manager(s) [{{$count}}]</h1>
        </div>
        <div class="col-md-6 mt-1">
            <button class="btn btn-primary float-right" data-target="#addNewManager" data-toggle="modal">Add Manager
            </button>
        </div>
        <div class="col-md-12">
            <table class="table table-hover sortable" id="managerTable">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>Name<i class="i-Up---Down"></i></th>
                    <th>Username<i class="i-Up---Down"></i></th>
                    <th>Type<i class="i-Up---Down"></i></th>
                    <th>Location<i class="i-Up---Down"></i></th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($managers as $key=>$value)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td class="first-row">{{$value->name}}</td>
                        <td>{{$value->username}}</td>
                        <td>
                            @if($value->type==2)
                                <span class="badge badge-success">Sub Admin</span>
                            @elseif($value->type==3)
                                <span class="badge badge-primary">Manager</span>
                            @endif
                        </td>
                        <td>@if($value->location) {{$value->location['name'] ? $value->location['name'] : '-'}} @else - @endif</td>
                        <td>
                            @if($value->type!=1)
                                <a class="action-links ml-1" onclick="selectedRow({{$value}})">Edit</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="addNewManager" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Manager</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Name</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="manager_name" placeholder="Name"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Username</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="add_manager_username" placeholder="Username"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Email</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="manager_email" placeholder="Name"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Location</label>
                                <div class="form-group">
                                    <select class="form-control selectpicker" id="manager_location" required>
                                        <option value="">Select</option>
                                        @foreach($location as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="mb-0">Password</label>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="add_manager_password" placeholder="Password" autocomplete="false" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="addNewManager()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateManager" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Manager</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Username<small class=""> (Enter username without space.)</small></label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="edit_manager_username" placeholder="Username"
                                           required>
                                </div>
                            </div>
                            {{--<div class="col-md-12">
                                <label>Email</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" readonly id="edit_manager_email" placeholder="Name"
                                           required>
                                </div>
                            </div>--}}
                            <div class="col-md-12">
                                <label>Location</label>
                                <div class="form-group">
                                    <select class="form-control selectpicker" id="edit_manager_location" required>
                                        <option value="">Select</option>
                                        @foreach($location as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="mb-0">New Password <small class="">(Leave empty if don't want to update.)</small></label>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="edit_manager_password" placeholder="Password" autocomplete="false">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="updateManager()">Save</button>
                        <button class="btn btn-primary ml-1"
                                type="button" onclick="deleteManager(userId,'admins')">Delete
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
        let userId;
        function addNewManager() {
            var data;
            data = {
                'name': $('#manager_name').val(),
                'email': $('#manager_email').val(),
                'assigned_location': $('#manager_location').val(),
                'username' : $('#add_manager_username').val(),
                'password': $('#add_manager_password').val()
            };
            axios.post(baseUrl + 'add-new-manager', data).then(function (response) {
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

        function selectedRow(data) {
            userId = data.id;
            $('#edit_manager_name').val(data.name);
            $('#edit_manager_email').val(data.email);
            $('#edit_manager_location').val(data.assigned_location);
            $('#edit_manager_location').selectpicker('refresh');
            $('#edit_manager_username').val(data.username);
            $('#updateManager').modal('show');
        }

        function updateManager() {
            let data;
            data = {
                'id': userId,
                /*'name': $('#edit_manager_name').val(),*/
                'assigned_location': $('#edit_manager_location').val(),
                'username': $('#edit_manager_username').val(),
                'password': $('#edit_manager_password').val()
            };
            axios.post(baseUrl + 'update-manager', data).then(function (response) {
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

        function deleteManager(itemId, table) {
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
                        toastr.success('Manager Deleted Successfully.', "Success", {
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
    <script>
        var table = $('#managerTable');

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
        // function formatPhoneNumber(phone) {
        //     phone = '+1'+phone;
        //     var cleaned = ('' + phone).replace(/\D/g, '')
        //     var match = cleaned.match(/^(1|)?(\d{3})(\d{3})(\d{4})$/)
        //     if (match) {
        //         var intlCode = (match[1] ? '+1 ' : '')
        //         return ['(', match[2], ') ', match[3], '-', match[4]].join('')
        //     }
        //     return null
        // }
    </script>
@endsection
