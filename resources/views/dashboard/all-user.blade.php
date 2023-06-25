@extends('layouts.master')

@section('page-css')
    <style>
        .select2-container {
            width: 100% !important;
            padding: 0;
        }

        /*
                .td-width {
                    min-width: 183px;
                }*/

        /* .table thead th {
             vertical-align: bottom;
             border-bottom: 2px solid #dee2e6;
             min-width: 155px !important;
         }*/
        .card {
            padding: 15px;
        }

        #found th.sort > i::before {
            content: "\f0bf" !important;
        }

        #found th.sort a {
            color: #63191b !important;
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
                                  <h5>Change Location:</h5>
                              </div>
                          @else
                              <div class="col-md-5">
                                  <h5 style="color: #ffffff">Location:</h5>
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
            <a class="nav-link active" href="{{route('customers')}}">Customer(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('guests')}}">Guest(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('managers')}}">Manager(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('server-users')}}">Server App User(s)</a>
        </li>
    </ul>
    <div class="row m-0 mt-2">
        <div class="col-md-12">
            <h1 class="heading-white mb-4">Customer(s) [{{$count}}]</h1>
        </div>
        <div class="col-md-12">
            <form action="{{route('customers')}}" method="GET" style="width: 100%">
                <div class="col-md-12 card p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="id"
                                   value="{{ app('request')->input('id') }}"
                                   placeholder="Filter by User ID">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="keyword"
                                   placeholder="Filter by keyword (Name, Phone-no, Email)"
                                   value="{{ app('request')->input('keyword') }}"
                            >
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary reset-button">
                                Search
                            </button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{route('customers')}}">
                                Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive mt-3" id="found">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th class="sort">User ID</th>
                    <th class="sort">Name</th>
                    <th class="sort">Email</th>
                    <th>Phone Number</th>
                    <th class="sort">Account Status</th>
                    <th class="">Last Login</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $key=>$value)
                    <tr onclick="window.location.href = './customer-details/'+{{$value->id}}">
                    <tr onclick="window.location.href = './customer-details/'+{{$value->id}}">
                        <td>{{$value->id}}</td>
                        <td>{{$value->first_name}} {{$value->last_name}}</td>
                        <td>{{$value->email}}</td>
                        <td>{{$value->mobile}}</td>
                        {{--                        <td>{{$value->orders ? $value->orders: 0}}</td>--}}
                        {{--                        <td>{{$value->rewards ? $value->rewards: 0}}</td>--}}
                        {{--negative symbol check for timestamp before 1970--}}
                        {{-- @if($value->date_of_birth)
                             <td>{{$value->date_of_birth}}</td>
                         @else
                             <td>{{date('m-d-Y', $value->date_of_birth)}}</td>
                         @endif--}}

                        {{--                        <td>{{$value->zip_code}}</td>--}}
                        <td>{{$value->is_account_deleted ? 'Deactivated': 'Active'}}</td>
                        {{-- @if(!$value->email_preference && !$value->phone_preference)
                             <td>No preference</td>
                         @elseif($value->email_preference && $value->phone_preference)
                             <td> Email and Text Message</td>
                         @else
                             @if($value->email_preference)
                                 <td> Email</td>
                             @elseif($value->phone_preference)
                                 <td> Text message</td>
                             @endif
                         @endif--}}
                        <td>@if($value->last_login) {{date('n/j/Y',$value->last_login)}} @else NA @endif</td>
                        <td>
                            <a href="{{url('users/customer-details/'.$value->id)}}" class="action-links ml-1">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$users->appends(request()->query())->links("pagination::bootstrap-4")}}
        </div>
        <div id="index_native" class="box"></div>
        @if(count($users) < 1)
            <div class="col-md-12 mt-5 text-center" id="not-found">
                <img alt="" height="150" src="{{asset('public/images/not-found.png')}}">
                <h5 class="mt-3 not-found">Not Found</h5>
            </div>
        @endif
        <div class="modal fade" id="editUser" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form>
                        <div class="modal-body">
                            <div class="row">
                                <form name="addItems">
                                    <div class="col-md-6 mb-3">
                                        <label>First Name</label>
                                        <input class="form-control" id="first_name"
                                               placeholder="Enter First Name..">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Last Name</label>
                                        <input class="form-control" id="last_name" placeholder="Enter Last Name..">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Email</label>
                                        <input class="form-control" id="email" readonly placeholder="Enter Email..">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Phone</label>
                                        <input class="form-control" id="mobile" placeholder="Enter Mobile">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Total Order</label>
                                        <input class="form-control" id="total_order" readonly
                                               placeholder="Total Order..">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Total Reward</label>
                                        <input class="form-control" id="total_reward" readonly
                                               placeholder="Enter Reward..">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Zip Code</label>
                                        <input class="form-control" id="zip_code" placeholder="Enter Zip Code..">
                                    </div>
                                    <div class="col-md-12 text-right mt-3">
                                        <button type="button" class="btn btn-primary" onclick="updateUser()">Save
                                        </button>
                                        <a class="btn btn-primary ml-1" style="color:#ffffff;"
                                           onclick="deleteUser(singleUserData.id)">
                                            Delete
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@endsection

@section('bottom-js')
    <script>
        let singleUserData = {};
        searchUsers();

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
                        toastr.success('User Deleted Successfully.', "Success", {
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

        function singleUser(item) {
            singleUserData = item;
            $('#first_name').val(item.first_name);
            $('#last_name').val(item.last_name);
            $('#mobile').val(item.mobile);
            $('#total_order').val(item.orders);
            $('#email').val(item.email);
            $('#total_reward').val(item.rewards);
            $('#zip_code').val(item.zip_code);
            $('#editUser').modal('show');
        }

        // function searchUsers(flag) {
        //     $('#not-found').hide();
        //     $('#customer_list').html('');
        //     if (flag) {
        //         $('#keyword').val('');
        //     }
        //     var data = {
        //         'keyword': $('#keyword').val(),
        //     };
        //     axios.post(baseUrl + 'search-user', data).then(function (response) {
        //         if (response.data.response.length) {
        //             $('#found').show();
        //             $('#not-found').hide();
        //             for (let i = 0; i < response.data.response.length; i++) {
        //                 let obj = response.data.response[i];
        //                 var dt1 = new Date(obj.date_of_birth);
        //                 let accountStatus = obj.is_account_deleted ? 'Deleted': 'Active';
        //                 obj.rewards = obj.rewards != null ? obj.rewards : 0;
        //                 obj.zip_code = obj.zip_code != null ? obj.rewards : 'Not Added';
        //                 obj.customer_id = obj.customer_id != null ? obj.customer_id : '-';
        //                 $('#customer_list').append('<tr>\n' +
        //                     '                        <td style="text-decoration: underline; cursor: pointer;" onclick="window.location.href = \'./customer-details/\'+' + obj.id + '">' + '#' + obj.id + '</td>\n' +
        //                     '                        <td class="first-row" style="text-decoration: underline; cursor: pointer;" onclick="window.location.href = \'./customer-details/\'+' + obj.id + '">' + obj.first_name + ' ' + obj.last_name + '</td>\n' +
        //                     '                        <td>' + obj.email + '</td>\n' +
        //                     '                        <td>' + formatPhoneNumber(obj.mobile) + '</td>\n' +
        //                     '                        <td>' + obj.orders + '</td>\n' +
        //                     '                        <td>' + obj.rewards + '</td>\n' +
        //                     '                        <td>' + (dt1.getMonth() + 1) + "/" + dt1.getDate() + "/" + dt1.getFullYear() + '</td>\n' +
        //                     '                        <td>' + obj.zip_code + '</td>\n' +
        //                     '                        <td>' + accountStatus + '</td>\n' +
        //                     '                        <td>' + preference + '</td>\n' +
        //                     '                        <td><a class="action-links">View</td>\n' +
        //                     '                    </tr>');
        //             }
        //             paginator({
        //                 table: document.getElementById("found").getElementsByTagName("table")[0],
        //                 box: document.getElementById("index_native"),
        //                 active_class: "color_page",
        //                 rows_per_page: response.data.pagination_limit
        //             });
        //         } else {
        //             $('#found').hide();
        //             $('#not-found').show();
        //         }
        //     });
        // }

        function updateUser() {
            axios.post(baseUrl + 'update-user', {
                'first_name': $('#first_name').val(),
                'last_name': $('#last_name').val(),
                'mobile': $('#mobile').val(),
                'zip_code': $('#zip_code').val(),
                'id': singleUserData.id,
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
    </script>
    <script>
        // var table = $('#itemTable');
        //
        // $('.sortable th')
        //     .wrapInner('<span title="sort this column"/>')
        //     .each(function () {
        //
        //         var th = $(this),
        //             thIndex = th.index(),
        //             inverse = false;
        //
        //         th.click(function () {
        //
        //             table.find('td').filter(function () {
        //
        //                 return $(this).index() === thIndex;
        //
        //             }).sortElements(function (a, b) {
        //
        //                 if ($.text([a]) == $.text([b]))
        //                     return 0;
        //
        //                 return $.text([a]) > $.text([b]) ?
        //                     inverse ? -1 : 1
        //                     : inverse ? 1 : -1;
        //
        //             }, function () {
        //
        //                 // parentNode is the element we want to move
        //                 return this.parentNode;
        //
        //             });
        //
        //             inverse = !inverse;
        //
        //         });
        //
        //     });

        function formatPhoneNumber(phone) {
            phone = '+1' + phone;
            var cleaned = ('' + phone).replace(/\D/g, '');
            var match = cleaned.match(/^(1|)?(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                var intlCode = (match[1] ? '+1 ' : '');
                return ['(', match[2], ') ', match[3], '-', match[4]].join('')
            }
            return null
        }
    </script>
@endsection

