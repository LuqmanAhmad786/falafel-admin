@extends('layouts.master')

@section('page-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.css">
@endsection
<style>
    .remainingCharacter {
        position: absolute;
        right: 3rem;
        border: transparent;
        background: transparent;
        color: rgba(0, 0, 0, 0.7);
    }

    .totalRemainingCharacter {
        position: absolute;
        right: 1.5rem;
        color: rgba(0, 0, 0, 0.7);
    }

    .card {
        padding: 15px;
    }
</style>
@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Settings</h2>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link " href="{{route('tax-settings')}}">Global Settings</a>
        </li>
        {{-- <li class="nav-item">
             <a class="nav-link" href="{{route('preparation-time')}}">Time Settings</a>
         </li>--}}
        <li class="nav-item">
            <a class="nav-link active" href="{{route('setting-restaurants')}}">Location Settings</a>
        </li>
    </ul>
    <div class="col-md-12">
        <div class="row mt-4">
            <div class="col-md-6">
                <h1 class="heading-white mb-2">Location(s)</h1>
            </div>
            <div class="col-md-6">
                <button class="btn btn-primary float-right" data-target="#addLocationModal" data-toggle="modal">Add New
                    Location
                </button>
            </div>
        </div>
        <div class="card p-2 mt-4">
            @if(sizeOf($restaurants))
                <div class="col-md-12 mt-3 table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Business Hours</th>
                            <th>Bank Details</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($restaurants as $key =>$item)
                            <tr>
                                <td>{{$key + 1}}</td>
                                <td>{{$item->name}}</td>
                                <td>@if($item->status == 1) Enabled @else Disabled @endif</td>
                                <td>{{$item->contact_number}}</td>
                                <td>{{$item->address}}</td>
                                <td>{{$item->additional_info ? $item->additional_info : '-'}}</td>
                                <td>
                                    @if($item->bankAccount)
                                        {{$item->bankAccount->account_number}}
                                    @else
                                        NA
                                    @endif
                                </td>
                                <td>
                                    <a class="action-links" onclick="editLocation({{$item}})">
                                        <i class="i-Pen-2"></i>
                                    </a> |
                                    @if($item->bankAccount)
                                        <a class="action-links" onclick="editBankDetails({{$item->id}})"><i class="fa i-Bank"></i></a>
                                    @else
                                        <a class="action-links" onclick="addBankDetails({{$item->id}})"><i class="fa i-Bank"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
            @endif
        </div>
    </div>
    <div class="modal fade" id="addLocationModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Location</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="name">Name*</label>
                                    <input type="text" class="form-control" id="name"
                                           placeholder="Name"
                                           name="nameLimit"
                                           onkeydown="limitAddName(this.form.nameLimit,this.form.countdownName, 30);"
                                           onkeyup='limitAddName(this.form.nameLimit,this.form.countdownName,30);'
                                           required/>
                                    <input readonly type="text" name="countdownName" size="1"
                                           class="text-right remainingCharacter" value="30 "><span
                                        class="totalRemainingCharacter">/ 30</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_contact">Prepration Time</label>
                                    <input type="text" class="form-control" id="preparation_time"
                                           placeholder="Prepration Time"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="contact">URL Slug*</label>
                                    <input type="text" class="form-control" id="slug"
                                           placeholder="URL Slug"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="status">Status*</label>
                                </div>
                                <div class="form-group d-flex">
                                    <label for="res_enabled" class="w-auto mr-3">
                                        <input type="radio" name="status" value="1" id="res_enabled" /> Enabled
                                    </label>
                                    <label for="res_disabled" class="w-auto mr-3">
                                        <input type="radio" name="status" value="0" id="res_disabled" /> Disabled
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="contact">Category Emoji*</label>
                                    <input type="text" class="form-control" id="category_emoji"
                                           placeholder="Category Emoji"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="contact">Contact Number*</label>
                                    <input type="text" class="form-control" id="contact"
                                           placeholder="Contact Number"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Address(Visible on website)*</label>
                                    <input class="form-control" placeholder="Write here.." type="text" rows="3"
                                              id="address" required></input>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Search latitude & Longitude by address(Internal use only)</label>
                                    <input class="form-control" placeholder="Write here.." type="text" rows="3"
                                           id="latLngAddress" required></input>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Latitude*</label>
                                    <input class="form-control" placeholder="Latitude" readonly type="text" rows="3"
                                           id="latitude" required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Longitude*</label>
                                    <input class="form-control" placeholder="Longitude" readonly type="text" rows="3"
                                           id="longitude" required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="additional_info">Business Hours*</label>
                                    <textarea class="form-control" placeholder="Business Hours" type="text" rows="5"
                                              id="additional_info" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="emails">Emails</label>
                                    <textarea class="form-control" placeholder="Enter comma separated emails"
                                              type="text" rows="5"
                                              id="emails" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="tax_rate">Tax Rate*</label>
                                    <input type="text" class="form-control" id="tax_rate"
                                           placeholder="Tax rate"
                                           required/>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="timezone">Timezone*</label>
                                    <select id="timezone" class="form-control" required>
                                        <option value="America/Chicago">Chicago (GMT-5)</option>
                                        <option value="America/Denver">Denver (GMT-6)</option>
                                        <option value="America/Phoenix">Phoenix (GMT-7)</option>
                                        <option value="America/Los_Angeles">Los Angeles (GMT-7)</option>
                                        <option value="America/Anchorage">Anchorage (GMT-8)</option>
                                        <option value="Pacific/Honolulu">Honolulu (GMT-10)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="time_interval">Time Interval (In Minutes)*</label>
                                    <input type="number" min="0" class="form-control" id="time_interval"
                                           placeholder="Time Interval"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="clover_mid">Clover Mid*</label>
                                    <input type="text" class="form-control" id="clover_mid"
                                           placeholder="Clover mid"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="clover_api_key">Clover Api Key*</label>
                                    <input type="text" class="form-control" id="clover_api_key"
                                           placeholder="Clover api key"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="clover_order_type_id">Clover Order Type Id*</label>
                                    <input type="text" class="form-control" id="clover_order_type_id"
                                           placeholder="Clover Order Type Id"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="clover_payment_api_key">Clover Payment Api Key*</label>
                                    <input type="text" class="form-control" id="clover_payment_api_key"
                                           placeholder="Clover payment api key"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="clover_payment_api_token">Clover Payment Api Token*</label>
                                    <input type="text" class="form-control" id="clover_payment_api_token"
                                           placeholder="Clover payment api token"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="clover_payment_api_token">Clover Employee ID*</label>
                                    <input type="text" class="form-control" id="clover_employee_id"
                                           placeholder="Clover Employee ID"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="clover_payment_api_token">Clover Tender ID*</label>
                                    <input type="text" class="form-control" id="clover_tender_id"
                                           placeholder="Clover Tender ID"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="status">Coming Soon*</label>
                                </div>
                                <div class="form-group d-flex">
                                    <label for="coming_soon_yes" class="w-auto mr-3">
                                        <input checked type="radio" name="coming_soon" value="1" id="coming_soon_yes" /> Yes
                                    </label>
                                    <label for="coming_soon_no" class="w-auto mr-3">
                                        <input type="radio" name="coming_soon" value="0" id="coming_soon_no" /> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="status">Commission Type*</label>
                                </div>
                                <div class="form-group">
                                    <label for="commission_type_fix" class="w-auto mr-3">
                                        <input checked type="radio" name="commission_type" value="1" id="commission_type_fix" /> Fix
                                    </label>
                                    <label for="commission_type_percentage" class="w-auto mr-3">
                                        <input type="radio" name="commission_type" value="2" id="commission_type_percentage" /> Percentage
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="commission">Commission Amount*</label>
                                    <input type="tel" class="form-control" id="commission" name="commission"
                                           placeholder="Commission Amount"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="add_background_image">Background Image* (Recommended Image Size 1280X850px)</label>
                                    <div class="custom-files" style="position: relative;">
                                        <input type="file" class="custom-file-input" id="add_background_image"
                                               style="position: absolute;width: 100%;cursor:pointer;height: 100% !important;">
                                        <img class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                             id="background_image"
                                             style="background-color: #eeeeee;width: 150px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="add_about">About</label>
                                    <textarea class="form-control" placeholder="About the restaurant."
                                              type="text" rows="5"
                                              id="add_about" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="updateLocationSubmit()">Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="edit_name" placeholder="name"
                                           name="nameEditLimit"
                                           onkeydown="limitEditName(this.form.nameEditLimit,this.form.countdownEditName, 30);"
                                           onkeyup='limitEditName(this.form.nameEditLimit,this.form.countdownEditName,30);'
                                           required/>
                                    <input readonly type="text" name="countdownEditName" size="1"
                                           class="text-right remainingCharacter" value="30 "><span
                                        class="totalRemainingCharacter">/ 30</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_contact">Prepration Time</label>
                                    <input type="text" class="form-control" id="edit_preparation_time"
                                           placeholder="Prepration Time"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="contact">URL Slug*</label>
                                    <input type="text" class="form-control" id="slug_edit"
                                           placeholder="URL Slug"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_status">Status*</label>
                                </div>
                                <div class="form-group d-flex">
                                    <label for="edit_res_enabled" class="w-auto mr-3">
                                        <input type="radio" name="status" value="1" id="edit_res_enabled" /> Enabled
                                    </label>
                                    <label for="edit_res_disabled" class="w-auto mr-3">
                                        <input type="radio" name="status" value="0" id="edit_res_disabled" /> Disabled
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="contact">Category Emoji*</label>
                                    <input type="text" class="form-control" id="category_emoji_edit"
                                           placeholder="Category Emoji"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_contact">Contact Number</label>
                                    <input type="text" class="form-control" id="edit_contact"
                                           placeholder="Contact Number"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_address">Address</label>
                                    <textarea class="form-control" placeholder="Write here.." type="text" rows="3"
                                              id="edit_address" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Search latitude & Longitude by address(Internal use only)</label>
                                    <input class="form-control" placeholder="Write here.." type="text" rows="3"
                                           id="edit_latLngAddress" required></input>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Latitude*</label>
                                    <input class="form-control" placeholder="Latitude" readonly type="text" rows="3"
                                           id="edit_latitude" required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="address">Longitude*</label>
                                    <input class="form-control" placeholder="Longitude" readonly type="text" rows="3"
                                    id="edit_longitude" required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_additional_info">Business Hours</label>
                                    <textarea class="form-control" placeholder="Business Hours" type="text" rows="5"
                                              id="edit_additional_info" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_emails">Emails</label>
                                    <textarea class="form-control" placeholder="Enter comma separated emails"
                                              type="text" rows="5"
                                              id="edit_emails" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_tax_rate">Tax Rate*</label>
                                    <input type="text" class="form-control" id="edit_tax_rate"
                                           placeholder="Tax rate"
                                           required/>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_timezone">Timezone*</label>
                                    <select id="edit_timezone" class="form-control" required>
                                        <option value="America/Chicago">Chicago (GMT-5)</option>
                                        <option value="America/Denver">Denver (GMT-6)</option>
                                        <option value="America/Phoenix">Phoenix (GMT-7)</option>
                                        <option value="America/Los_Angeles">Los Angeles (GMT-7)</option>
                                        <option value="America/Anchorage">Anchorage (GMT-8)</option>
                                        <option value="Pacific/Honolulu">Honolulu (GMT-10)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_time_interval">Time Interval (In Minutes)*</label>
                                    <input type="number" min="0" class="form-control" id="edit_time_interval"
                                           placeholder="Time Interval"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_clover_mid">Clover Mid*</label>
                                    <input type="text" class="form-control" id="edit_clover_mid"
                                           placeholder="Clover mid"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="edit_clover_api_key">Clover Api Key*</label>
                                    <input type="text" class="form-control" id="edit_clover_api_key"
                                           placeholder="Clover api key"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="edit_clover_order_type_id">Clover Order Type Id*</label>
                                    <input type="text" class="form-control" id="edit_clover_order_type_id"
                                           placeholder="Clover Order Type Id"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="edit_clover_payment_api_key">Clover Payment Api Key*</label>
                                    <input type="text" class="form-control" id="edit_clover_payment_api_key"
                                           placeholder="Clover payment api key"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2 d-none">
                                <div class="form-group">
                                    <label for="edit_clover_payment_api_token">Clover Payment Api Token*</label>
                                    <input type="text" class="form-control" id="edit_clover_payment_api_token"
                                           placeholder="Clover payment api token"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_clover_payment_api_token">Clover Employee ID*</label>
                                    <input type="text" class="form-control" id="edit_clover_employee_id"
                                           placeholder="Clover Employee ID"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_clover_payment_api_token">Clover Tender ID*</label>
                                    <input type="text" class="form-control" id="edit_clover_tender_id"
                                           placeholder="Clover Tender ID"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_status">Coming Soon*</label>
                                </div>
                                <div class="form-group d-flex">
                                    <label for="edit_coming_soon_yes" class="w-auto mr-3">
                                        <input type="radio" name="edit_coming_soon" value="1" id="edit_coming_soon_yes" /> Yes
                                    </label>
                                    <label for="edit_coming_soon_no" class="w-auto mr-3">
                                        <input type="radio" name="edit_coming_soon" value="0" id="edit_coming_soon_no" /> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="status">Commission Type*</label>
                                </div>
                                <div class="form-group d-flex">
                                    <label for="edit_commission_type_fix" class="w-auto mr-3">
                                        <input checked type="radio" name="edit_commission_type" value="1" id="edit_commission_type_fix" /> Fix
                                    </label>
                                    <label for="edit_commission_type_percentage" class="w-auto mr-3">
                                        <input type="radio" name="edit_commission_type" value="2" id="edit_commission_type_percentage" /> Percentage
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="commission">Commission Amount*</label>
                                    <input type="tel" class="form-control" name="edit_commission" id="edit_commission"
                                           placeholder="Commission Amount"
                                           required/>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_background_image">Background Image* (Recommended Image Size 1280X850px)</label>
                                    <div class="custom-files" style="position: relative;">
                                        <input type="file" class="custom-file-input" id="edit_background_image"
                                               style="position: absolute;width: 100%;cursor:pointer;height: 100% !important;">
                                        <img class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                             id="editbackgroundImage"
                                             style="background-color: #eeeeee;width: 150px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label for="edit_about">About</label>
                                    <textarea class="form-control" placeholder="About the restaurant."
                                              type="text" rows="5"
                                              id="edit_about" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="updateLocationSubmit()">Save
                            </button>
                            <button class="btn btn-primary ml-1"
                                    type="button" onclick="deleteLocation(menuId,'restaurants')">Delete
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cropImageModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
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

    <div class="modal fade" id="restaurantBankDetails" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restaurant Bank Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{url('/save-bank-details')}}" method="post">
                        <input type="hidden" name="restaurant_id" value=""/>
                        <div class="form-group">
                            <label for="account_holder_name">Account Holder Name</label>
                            <input type="text" name="account_holder_name" value="" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="account_number">Account Number</label>
                            <input type="text" name="account_number" value="" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="account_number">Routing Number</label>
                            <input type="text" name="routing_number" value="" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary saveBankDetails">Save Bank</button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
@endsection

@section('bottom-js')
    <script>
        var crop;
        var isAction;
        var itemIndex;
        var imageSelected = false;
        var changeImage = 0;
        $(document).ready(function () {
            $("#category_emoji").emojioneArea({
                search: true,
                pickerPosition: "bottom",
                filtersPosition: "bottom",
                tones: false
            });
            $("#category_emoji_edit").emojioneArea({
                search: true,
                pickerPosition: "bottom",
                filtersPosition: "bottom",
                tones: false
            });
        });
        var menuId;

        function editLocation(item) {
            $('#editLocationModal').modal('show');
            $('#edit_name').val(item.name);
            $('#slug_edit').val(item.slug);
            $('#category_emoji_edit').val(item.category_emoji);
            $('#edit_contact').val(item.contact_number);
            $('#edit_address').val(item.address);
            $('#edit_additional_info').val(item.additional_info);
            $('#edit_emails').val(item.emails);
            $('#edit_tax_rate').val(item.tax_rate);
            $('#edit_timezone').val(item.timezone);
            $('#edit_time_interval').val(item.time_interval);
            $('#edit_about').val(item.about);
            $('#edit_clover_mid').val(item.clover_mid);
            $('#edit_clover_api_key').val(item.clover_api_key);
            $('#edit_clover_order_type_id').val(item.clover_order_type_id);
            $('#edit_clover_payment_api_key').val(item.clover_payment_api_key);
            $('#edit_clover_payment_api_token').val(item.clover_payment_api_token);
            $('#edit_clover_employee_id').val(item.clover_employee_id);
            $('#edit_clover_tender_id').val(item.clover_tender_id);
            $('input[name=edit_coming_soon][value="'+item.is_comingsoon+'"]').prop('checked',true);
            $('input[name=edit_commission_type][value="'+item.commission_type+'"]').prop('checked',true);
            $('input[name=edit_commission]').val(item.commission);
            $('#edit_latitude').val(item.latitude);
            $('#edit_longitude').val(item.longitude);
            $('#editLocationModal input[name="status"][value="'+item.status+'"]').prop('checked',true);
            $('#editLocationModal #edit_preparation_time').val(item.preparation_time);
            if(item.background_image){
                $('#editbackgroundImage').attr('src',"{{asset('public/storage/')}}/"+item.background_image);
            } else{
                $('#editbackgroundImage').attr('src',"{{asset('public/assets/images/menu-default.png')}}");
            }
            menuId = item.id;
        }

        function editBankDetails(item){
            axios.get(baseUrl+'setting/get-bank-details/'+item).then(function (response) {
                jQuery('#restaurantBankDetails input[name=restaurant_id]').val(response.data.response.restaurant.id);
                jQuery('#restaurantBankDetails input[name=account_holder_name]').val(response.data.response.bankinfo.individual.first_name + ' '+ response.data.response.bankinfo.individual.last_name );
                jQuery('#restaurantBankDetails input[name=account_number]').val(response.data.response.restaurant.bank_account.account_number);
                jQuery('#restaurantBankDetails input[name=routing_number]').val(response.data.response.restaurant.bank_account.routing_number);
                jQuery('#restaurantBankDetails').modal('show');
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                jQuery('#restaurantBankDetails').modal('show');
            });
        }

        function addBankDetails(item){
            jQuery('#restaurantBankDetails input[name=restaurant_id]').val(item);
            jQuery('#restaurantBankDetails inputp[name=account_holder_name], #restaurantBankDetails input[name=account_number], #restaurantBankDetails input[name=routing_number]').val('');
            jQuery('#restaurantBankDetails').modal('show');
        }

        jQuery(document).on('click','.saveBankDetails',function (){
            var data = {
                'restaurant_id' : jQuery('#restaurantBankDetails input[name=restaurant_id]').val(),
                'account_holder_name' : jQuery('#restaurantBankDetails input[name=account_holder_name]').val(),
                'account_number' : jQuery('#restaurantBankDetails input[name=account_number]').val(),
                'routing_number' : jQuery('#restaurantBankDetails input[name=routing_number]').val(),
            }
            axios.post(baseUrl + 'setting/save-bank-details', data).then(function (response) {
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
        });

        function limitEditName(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }

        function limitAddName(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }

        function updateLocationSubmit() {
            let data;
            if (menuId) {
                data = {
                    id: menuId,
                    address: $('#edit_address').val(),
                    name: $('#edit_name').val(),
                    slug: $('#slug_edit').val(),
                    category_emoji: $('#category_emoji_edit').val(),
                    contact_number: $('#edit_contact').val(),
                    additional_info: $('#edit_additional_info').val(),
                    emails: $('#edit_emails').val(),
                    tax_rate: $('#edit_tax_rate').val(),
                    timezone: $('#edit_timezone').val(),
                    time_interval: $('#edit_time_interval').val(),
                    clover_mid: $('#edit_clover_mid').val(),
                    clover_api_key: $('#edit_clover_api_key').val(),
                    clover_order_type_id : $('#edit_clover_order_type_id').val(),
                    clover_payment_api_key: $('#edit_clover_payment_api_key').val(),
                    clover_payment_api_token: $('#edit_clover_payment_api_token').val(),
                    clover_employee_id: $('#edit_clover_employee_id').val(),
                    clover_tender_id: $('#edit_clover_tender_id').val(),
                    is_comingsoon : $('input[name=edit_coming_soon]:checked').val(),
                    latitude: $('#edit_latitude').val(),
                    longitude: $('#edit_longitude').val(),
                    about: $('#edit_about').val(),
                    background_image : changeImage ? $('#editbackgroundImage').attr('src') : '',
                    status: $('#editLocationModal input[name="status"]:checked').val(),
                    commission_type : $('#editLocationModal input[name=edit_commission_type]:checked').val(),
                    commission : $('#editLocationModal input[name=edit_commission]').val(),
                    preparation_time : $('#editLocationModal #edit_preparation_time').val()
                };
            } else {
                data = {
                    address: $('#address').val(),
                    name: $('#name').val(),
                    slug: $('#slug').val(),
                    category_emoji: $('#category_emoji_edit').val(),
                    contact_number: $('#contact').val(),
                    additional_info: $('#additional_info').val(),
                    emails: $('#emails').val(),
                    tax_rate: $('#tax_rate').val(),
                    timezone: $('#timezone').val(),
                    time_interval: $('#time_interval').val(),
                    clover_mid: $('#clover_mid').val(),
                    clover_api_key: $('#clover_api_key').val(),
                    clover_order_type_id: $('#clover_order_type_id').val(),
                    clover_payment_api_key: $('#clover_payment_api_key').val(),
                    clover_payment_api_token: $('#clover_payment_api_token').val(),
                    clover_employee_id: $('#clover_employee_id').val(),
                    clover_tender_id: $('#clover_tender_id').val(),
                    is_comingsoon : $('input[name=coming_soon]:checked').val(),
                    latitude: $('#latitude').val(),
                    longitude: $('#longitude').val(),
                    about: $('#add_about').val(),
                    background_image : changeImage ? $('#background_image').attr('src') : '',
                    status: $('#addLocationModal input[name="status"]:checked').val(),
                    commission_type : $('#addLocationModal input[name=commission_type]:checked').val(),
                    commission : $('#addLocationModal input[name=commission]').val(),
                    preparation_time : $('#addLocationModal #preparation_time').val()
                };
            };

            axios.post(baseUrl + 'update-location', data).then(function (response) {
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

        function deleteLocation(itemId, table) {
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

        function restaurantStatus(restaurantId, isOpened) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to change status of this restaurant.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Change it!'
            }).then(function (result) {
                if (result.value) {
                    axios.get(baseUrl + 'set-restaurant-status/' + restaurantId + '/' + isOpened).then(function (response) {
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

        $(document).ready(function () {
            $('#add_background_image').on('change', function (e) {
                changeImage = 1;
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);
                    $('#cropImageModal').modal('show');
                };
                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Add';
            });

            $('#edit_background_image').on('change', function (e) {
                changeImage = 1;
                $('#editLocationModal').modal('hide');
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);
                    setTimeout(function(){
                        $('#cropImageModal').modal('show');
                    },500);
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Edit';
            });
            $('#crop_image_img').on('load', function () {
                var image = document.getElementById('crop_image_img');
                crop = new Cropper(image, {
                    minContainerWidth: 466,
                    minContainerHeight: 400,
                    viewMode: 2,
                    aspectRatio: 1280 / 850,
                    minCropBoxWidth: 1280,
                    minCropBoxHeight: 1280,
                    cropBoxResizable: true,
                    zoomable:true,
                    crop(event) {
                        console.log(event.detail.x);
                        console.log(event.detail.y);
                        console.log(event.detail.width);
                        console.log(event.detail.height);
                        console.log(event.detail.rotate);
                        console.log(event.detail.scaleX);
                        console.log(event.detail.scaleY);
                    },
                });
            });
        });

        function getCroppedImage() {
            var image = crop.getCroppedCanvas().toDataURL('image/jpg', '');
            if (isAction == 'Add') {
                $('#background_image').attr('src', image);
                $('#cropImageModal').modal('hide');
            } else if (isAction == 'Edit') {
                $('#editbackgroundImage').attr('src', image);
                $('#cropImageModal').modal('hide');
                setTimeout(function(){
                    $('#editLocationModal').modal('show');
                },500);

            }
            imageSelected = true;
        }
    </script>
@endsection
