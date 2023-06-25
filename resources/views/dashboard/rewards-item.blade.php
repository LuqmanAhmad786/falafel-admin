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

        i {
            font-family: 'iconsmind' !important;
            speak: none;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-size: 16px;
            padding: 0;
            position: relative;
            top: 2px;
        }

        /*.card {
            border-radius: 10px;
            box-shadow: 0 4px 20px 1px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.08);
            border: 0;
            height: 230px;
        }*/
        .card {
            padding: 15px;
        }

        .category-tab {
            background: #EDF3F9;
            padding: 10px;
        }

        .table th, .table td {
            vertical-align: middle;
        }
        select.moreTime {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 27rem #f8f9fa !important;
        }
    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Rewards / Birthday</h2>
            </div>
        </div>
    </div>
    <div class="row p-3">
        <div class="col-md-12 mt-0 card p-3" style="height: auto">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab"
                       aria-controls="nav-home" aria-selected="true">Free Entree</a>
                    <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab"
                       aria-controls="nav-profile" aria-selected="false">Birthday</a>
{{--                    <a class="nav-item nav-link" id="nav-admin-rewards-tab" data-toggle="tab" href="#nav-admin-rewards"--}}
{{--                       role="tab"--}}
{{--                       aria-controls="nav-admin-rewards" aria-selected="false">Admin Reward's</a>--}}
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Item(s)</h3>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary float-right" onclick="openReward(1)">
                                Add Category
                            </button>
                        </div>
                        <div class="col-md-12 mt-2">
                            <h5>What are Rewards?</h5>
                            <p>Rewards are perquisite that user can claim after achieving 2,000 points.</p>
                        </div>
                        <div class="col-md-12 mt-1">
                            <h5>How does it work?</h5>
                            <p>Once user has 2,000 points in his/her account when he visit reward section in his/her account. He/She will get an option to redeem reward, As soon as he/she clicks redeem,  If he/she has any of the items from below in his/her bag then the item will be free & if there are two from below in users bag, then the one with lower price will be free.</p>
                        </div>
                    @if(sizeof($reward_item))
                            <div class="col-md-12 mt-3">
                                <table class="table table-hover sortable" id="rewardTable">
                                    <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Item Name<i class="i-Up---Down"></i></th>
                                        <th>Price<i class="i-Up---Down"></i></th>
                                        <th>Category<i class="i-Up---Down"></i></th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reward_item as $key=>$value)
                                        @if($value->item!=null)
                                            <tr>
                                                <td>
                                                    @if($value->item)
                                                        <img alt="" width="50" height="50"
                                                             src="{{asset('public/storage/'.$value->item['item_image'])}}">
                                                    @endif
                                                </td>
                                                <td>
                                                    {{$value->item['item_name']}}
                                                </td>
                                                <td>
                                                    ${{$value->item['item_price']}}
                                                </td>
                                                <td>
                                                    {{$value->category_name}}
                                                </td>
                                                <td class="td-width">
                                                        <a class="action-links ml-1" title="Delete"
                                                           onclick="changeStatus({{$value->reward_item_id}}, 0)">
                                                            Delete
                                                        </a>
                                                </td>
                                            </tr>
                                        @endif
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
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    <div class="col-md-12 p-0">
                        <div class="row">
                            <div class="col-md-6">
                                <h3>Item(s)</h3>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary float-right" onclick="openReward(2)"
                                        data-toggle="modal">
                                    Add Category
                                </button>
                            </div>
                            <div class="col-md-12 mt-2">
                                <h5>What is a birthday reward? </h5>
                                <p>Birthday rewards are perquisite that user can claim on their birthday.</p>
                            </div>
                            <div class="col-md-12 mt-1">
                                <h5>How does it work?</h5>
                                <p>User can claim for any item from below to get as free dessert entree on their birth date added in their account. If today's her/his birthday and visit the reward section in his/her account, They will get an option to redeem free dessert reward. As soon as he/she clicks redeem, If he/she has any of the items from below in his/her bag then the item will be free & if there are two from below in users bag, then the one with lower price will be free.</p>
                            </div>
                            @if(sizeof($birthday_item))
                                <div class="col-md-12 mt-3">
                                    <table class="table table-hover sort" id="birthdayTable">
                                        <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name<i class="i-Up---Down"></i></th>
                                            <th>Price<i class="i-Up---Down"></i></th>
                                            <th>Category<i class="i-Up---Down"></i></th>
                                            {{--<th>Action</th>--}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($birthday_item as $key=>$value)
                                            @if($value->item!=null)
                                                <tr>
                                                    <td>
                                                        <img alt="" width="50" height="50"
                                                             src="{{asset('public/storage/'.$value->item->item_image)}}">
                                                    </td>
                                                    <td>
                                                        {{$value->item->item_name}}
                                                    </td>
                                                    <td>
                                                        ${{$value->item->item_price}}
                                                    </td>
                                                    <td>
                                                        {{$value->category_name}}
                                                    </td>
                                                    <td class="td-width">
                                                            <a class="action-links ml-1" title="Delete"
                                                               onclick="changeStatus({{$value->reward_item_id}}, 0)">
                                                                Delete
                                                            </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="col-md-12 mt-5 text-center">
                                    <img height="150" src="{{asset('public/images/not-found.png')}}">
                                    <h5 class="mt-3 not-found">Not Found</h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-admin-rewards" role="tabpanel"
                     aria-labelledby="nav-admin-rewards-tab">

                    <div class="col-md-12 p-0 mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h3>Admin Rewards(s)</h3>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary float-right" onclick="addAdminModal()">
                                    Add New
                                </button>
                            </div>
                        </div>
                    </div>
                    @if(sizeof($admin_reward_list))
                        <div class="col-md-12 mt-3 p-0">
                            <table class="table table-hover mt-3">
                                <thead>
                                <tr>
                                    <th style="min-width: 130px;">Name</th>
                                    <th style="min-width: 130px;">Expiry</th>
                                    <th style="min-width: 130px;">Item/Reward Points</th>
                                    <th style="min-width: 130px;">Users</th>
                                    <th style="min-width: 130px;">Description</th>
                                    <th style="min-width: 130px;">Type</th>
                                    <th style="min-width: 130px;">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($admin_reward_list as $key=>$value)
                                    <tr>
                                        <td>
                                            {{$value->name}}
                                        </td>
                                        <td>
                                            {{date('n/j/Y',$value->expiry)}}
                                        </td>
                                        @if($value['reward_point']['reward_point'])
                                            <td>
                                                {{$value['reward_point']['reward_point']}}
                                            </td>
                                        @else
                                            <td>
                                                {{implode(",",  $value['all_items'])}}
                                            </td>
                                        @endif
                                        <td>
                                            @foreach($value->users as $k => $v)
                                                {{$v->full_name}}
                                                @if(sizeof($value->users) != $k+1)
                                                    ,
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            {{$value->description}}
                                        </td>
                                        <td>
                                            {{!$value['reward_point']['reward_point'] == 1 ? 'Free Entry' : 'Extra Points' }}
                                        </td>
                                        <td>
                                            <a class="action-links ml-1"
                                               onclick="deleteAdminReward({{$value->admin_reward_id}})">Delete
                                            </a>
                                            {{--<a class="action-links ml-1"
                                               onclick="editAdminReward({{$value}})">Edit
                                            </a>--}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
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
                        <div class="row">
                            <form name="addItems">
                                <div class="col-md-12">
                                    <select class="form-control selectpicker" id="selected_item" required>
                                        <option value="">Select Category</option>
                                        @foreach($category as $v)
                                            <option value="{{$v->category_id}}">{{$v->category_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 text-right mt-3">
                                    <button type="button" class="btn btn-primary" onclick="addRewardsItem()">Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addAdminRewardModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row p-3">
                            <form name="addItems">
                                <div class="row mt-3">
                                    <div class="col-md-2">
                                        <label class="radio radio-outline-primary">
                                            <input checked type="radio" class="" name="admin_reward_type"
                                                   value="1">
                                            <span>Free Item</span>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="radio radio-outline-primary">
                                            <input type="radio" class="" name="admin_reward_type" value="2">
                                            <span>Extra Points</span>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6 mb-3">
                                        <label>Enter Name</label>
                                        <input class="form-control" id="name" placeholder="Enter Name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Select User</label>
                                        <select class="form-control" id="users" multiple>
                                            @foreach($admin_reward['users'] as $user)
                                                <option
                                                    value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="is_reward_point">
                                        <label>Reward Points</label>
                                        <input class="form-control" id="reward_points" placeholder="Enter Points">
                                    </div>
                                    <div class="col-md-6 mb-3" id="is_category_id">
                                        <label>Select Category</label>
                                        <select class="form-control" id="category_id"
                                                onchange="getCategoryItem($('#category_id').val())">
                                            <option value="">Select</option>
                                            @foreach($category as $cat)
                                                <option value="{{$cat->category_id}}">{{$cat->category_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="is_item_id">
                                        <label>Select Item (Optional)</label>
                                        <select class="form-control" id="item_id">
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="is_expiry_date">
                                        <label>Expire In (Days)</label>
                                        <input class="form-control" id="expiry" placeholder="Enter No Of Days">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Description</label>
                                        <textarea
                                            type="text"
                                            class="form-control"
                                            placeholder="Write here.."
                                            id="description" rows="4"></textarea>
                                    </div>
                                    <div class="col-md-12 text-center mb-3">
                                        <button class="btn btn-primary" type="button" onclick="addAdminReward()">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{--    <div class="modal fade" id="editAdminRewardModal" tabindex="-1" role="dialog" aria-hidden="true"
             data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form>
                        <div class="modal-body">
                            <div class="row p-3">
                                <form name="addItems" onsubmit="addAdminReward()">
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label class="radio radio-outline-primary">
                                                <input checked type="radio" class="" name="edit_admin_reward_type"
                                                       value="1">
                                                <span>Free Item</span>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="radio radio-outline-primary">
                                                <input type="radio" class="" name="edit_admin_reward_type" value="2">
                                                <span>Extra Points</span>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6 mb-3">
                                            <label>Enter Name</label>
                                            <input class="form-control" id="edit_name" placeholder="Enter Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Select User</label>
                                            <select class="form-control" id="edit_users" multiple>
                                                @foreach($admin_reward['users'] as $user)
                                                    <option
                                                        value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3" id="edit_is_reward_point">
                                            <label>Reward Points</label>
                                            <input class="form-control" id="edit_reward_points" placeholder="Enter Points">
                                        </div>
                                        <div class="col-md-6 mb-3" id="edit_is_category_id">
                                            <label>Select Category</label>
                                            <select class="form-control" id="edit_category_id"
                                                    onchange="getCategoryItem($('#category_id').val())">
                                                <option value="">Select</option>
                                                @foreach($category as $cat)
                                                    <option value="{{$cat->category_id}}">{{$cat->category_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3" id="edit_is_item_id">
                                            <label>Select Item (Optional)</label>
                                            <select class="form-control" id="edit_item_id">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Description</label>
                                            <textarea
                                                type="text"
                                                class="form-control"
                                                placeholder="Write here.."
                                                id="edit_description" rows="4"></textarea>
                                        </div>
                                        <div class="col-md-12 text-center mb-3">
                                            <button class="btn btn-primary" type="submit">
                                                Submit
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>--}}
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
@endsection

@section('bottom-js')
    <script>
        let selectedFlag;
        let admin_reward_type;
        let edit_admin_reward_type;
        let rdId;
        $('input[name=admin_reward_type]').change(function () {
            admin_reward_type = $('input[name=admin_reward_type]:checked').val();
            if (admin_reward_type == 1) {
                $('#is_reward_point').hide();
                $('#is_category_id').show();
            } else if (admin_reward_type == 2) {
                $('#is_reward_point').show();
                $('#is_category_id').hide();
                $('#is_item_id').hide();
                $('#is_expiry_date').hide();
            }
        });

        function openReward(flag) {
            selectedFlag = flag;
            $('#addItemModal').modal('show');
        }

        function addAdminModal() {
            $('#users').select2({});
            admin_reward_type = $('input[name=admin_reward_type]:checked').val();
            $('#is_reward_point').hide();
            $('#is_item_id').hide();
            $('#addAdminRewardModal').modal('show');
        }

        function addRewardsItem() {
            axios.post(baseUrl + 'add-reward-item', {
                'category_id': $('#selected_item').val(),
                'flag': selectedFlag
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

        function changeStatus(itemId, status) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this item.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.value) {
                    axios.get(baseUrl + 'reward-item-status/' + itemId + '/' + status).then(function (response) {
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

        function getCategoryItem(categoryId) {
            axios.get(baseUrl + 'get-category-items/' + categoryId).then(function (response) {
                $('#is_item_id').show();
                for (var i = 0; i < response.data.response.length; i++) {
                    $('#item_id').append('<option value="' + response.data.response[i].item_id + '">' + response.data.response[i].item_name + '</option>');
                }
            });
        }

        function addAdminReward() {
            let data = {};
            if (rdId) {
                data = {
                    'category_id': $('#edit_category_id').val(),
                    'item_id': $('#edit_item_id').val(),
                    'name': $('#edit_name').val(),
                    'description': $('#edit_description').val(),
                    'expiry': $('#edit_expiry').val(),
                    'reward_points': $('#edit_reward_points').val(),
                    'users': $('#edit_users').val(),
                    'type': admin_reward_type,
                    'id': rdId
                }
            } else {
                data = {
                    'category_id': $('#category_id').val(),
                    'item_id': $('#item_id').val(),
                    'name': $('#name').val(),
                    'description': $('#description').val(),
                    'expiry': $('#expiry').val(),
                    'reward_points': $('#reward_points').val(),
                    'users': $('#users').val(),
                    'type': admin_reward_type
                }
            }
            axios.post(baseUrl + 'add-admin-reward', data)
            .then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                setTimeout(function () {
                    window.location.href = baseUrl + 'promotions/rewards-items'
                }, 200);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }

        function deleteAdminReward(rewardId) {
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
                    axios.get(baseUrl + 'delete-admin-reward/' + rewardId).then(function (response) {
                        toastr.success('Label Deleted Successfully.', "Success", {
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

        function editAdminReward(obj) {
            console.log(obj);
            $('#edit_users').select2({});
            let editUser = [];
            for (let x = 0; x < obj.users.length; x++) {
                editUser.push(obj.users[x].user_id);
            }
            $('#edit_users').val(editUser);
            if (obj.reward_point == null) {
                edit_admin_reward_type = $('input[name=edit_admin_reward_type]:checked').val(1);
            } else {
                edit_admin_reward_type = $('input[name=edit_admin_reward_type]:checked').val(2);
            }
            rdId = obj.admin_reward_id;
            $('#edit_category_id').val(obj.category_id);
            $('#edit_name').val(obj.name);
            $('#edit_description').val(obj.description);
            $('#edit_expiry').val(obj.expiry);
            $('#edit_reward_points').val(obj.reward_points);
            $('#edit_category_id').val(obj.category_id);
            $('#edit_is_reward_point').hide();
            $('#edit_is_item_id').hide();
            $('#editAdminRewardModal').modal('show');
        }
    </script>
    <script>
        var table = $('#rewardTable');
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
                        return this.parentNode;
                    });
                    inverse = !inverse;
                });
            });
    </script>
    <script>
        var btable = $('#birthdayTable');
        $('.sort th')
            .wrapInner('<span title="sort this column"/>')
            .each(function(){
                var th = $(this),
                    thIndex = th.index(),
                    inverse = false;
                th.click(function(){
                    btable.find('td').filter(function(){
                        return $(this).index() === thIndex;
                    }).sortElements(function(a, b){
                        if( $.text([a]) == $.text([b]) )
                            return 0;
                        return $.text([a]) > $.text([b]) ?
                            inverse ? -1 : 1
                            : inverse ? 1 : -1;
                    }, function(){
                        return this.parentNode;
                    });
                    inverse = !inverse;
                });
            });
    </script>
@endsection
