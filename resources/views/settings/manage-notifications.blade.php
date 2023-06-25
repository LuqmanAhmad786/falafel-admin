@extends('layouts.master')

@section('page-css')
@endsection

@section('main-content')
    <div class="row">
        <div class="col-md-6">
            <h1 class="heading-white mb-4">Notifications(s)</h1>
        </div>
        <div class="col-md-6 mt-1">
            <button class="btn btn-primary float-right" data-target="#addNew" data-toggle="modal">Add New</button>
        </div>
        @if(sizeof($list))
            <div class="col-md-12">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Notification Type</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $key=>$value)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$value->type_name}}</td>
                            <td>{{$value->message_text}}</td>
                            <td>
                                <a class="action-links ml-1"
                                   onclick="doEdit({{$value}})">Edit
                                </a>
                            </td>
                        </tr>
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
    <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="addNewSubmit();return false;">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Notification Type</label>
                                <div class="form-group">
                                    <select class="form-control" id="notification_type">
                                        <option value="">Select</option>
                                        <option value="1_New User Registration">New User Registration</option>
                                        <option value="2_Change Password">Change Password</option>
                                        <option value="3_Order Confirmation">Order Confirmation</option>
                                        <option value="4_Order Complete">Order Complete</option>
                                        <option value="5_After 1.5hrs Of Order">After 1.5hrs Of Order</option>
                                        <option value="6_2000 Rewards Points">2000 Rewards Points</option>
                                        <option value="7_Admin Reward">Admin Reward</option>
                                        <option value="8_Happy Birthday Reward">Happy Birthday Reward</option>
                                        <option value="9_Up To 60 Days Order Not Found">Up To 60 Days Order Not Found</option>
                                        <option value="10_Before Pickup Time">Before Pickup Time</option>
                                        <option value="11_After Pickup Time">After Pickup Time</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Notification Message</label>
                                <div class="form-group">
                                    <textarea type="text" class="form-control" id="message_text"
                                              placeholder="Enter Here.." rows="4"
                                              required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editList" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="addNewSubmit();return false;">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Notification Type</label>
                                <div class="form-group">
                                    <select class="form-control" id="edit_notification_type" disabled>
                                        <option value="">Select</option>
                                        <option value="1_New User Registration">New User Registration</option>
                                        <option value="2_Change Password">Change Password</option>
                                        <option value="3_Order Confirmation">Order Confirmation</option>
                                        <option value="4_Order Complete">Order Complete</option>
                                        <option value="5_After 1.5hrs Of Order">After 1.5hrs Of Order</option>
                                        <option value="6_2000 Rewards Points">2000 Rewards Points</option>
                                        <option value="7_Admin Reward">Admin Reward</option>
                                        <option value="8_Happy Birthday Reward">Happy Birthday Reward</option>
                                        <option value="9_Up To 60 Days Order Not Found">Up To 60 Days Order Not Found</option>
                                        <option value="10_Before Pickup Time">Before Pickup Time</option>
                                        <option value="11_After Pickup Time">After Pickup Time</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Notification Message</label>
                                <div class="form-group">
                                    <textarea type="text" class="form-control" id="edit_message_text"
                                              placeholder="Enter Here.." rows="4"
                                              required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-primary" onclick="deleteLabel()">Delete</button>
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
        let rowId;

        function addNewSubmit() {
            let splitText = rowId ? $('#edit_notification_type').val().split("_") : $('#notification_type').val().split("_");
            axios.post(baseUrl + 'set-notification-text', {
                'id': rowId,
                'type_id': splitText[0],
                'type_name': splitText[1],
                'message_text': rowId ? $('#edit_message_text').val() : $('#message_text').val(),
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

        function deleteLabel() {
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
                    axios.get(baseUrl + 'delete-managed-notification/' + rowId).then(function (response) {
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

        function doEdit(obj) {
            rowId = obj.id;
            $('#edit_notification_type').val(obj.type_id + '_' + obj.type_name);
            $('#edit_message_text').val(obj.message_text);
            $('#editList').modal('show');
        }
    </script>
@endsection
