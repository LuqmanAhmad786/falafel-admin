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
    </style>
@endsection

@section('main-content')
    <div class="row p-1">
        <div class="col-md-9">
            <h1 class="heading-white mb-1">Create Delivery</h1>
        </div>
    </div>
    <div class="row p-1">
        <div class="offset-3 col-md-6 card ">
            <div class="row p-3">
                <div class="col-md-12">
                    <h3>Generate a new delivery quote</h3>
                    <hr/>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Select Pickup Restaurant</label>
                        <select id="pickup" class="form-control selectpicker">
                            <option value="8630 Cullen Blvd, Houston, TX 77051">Falafel Corner</option>
                            <option value="9541 Mesa Dr, Houston, TX 77078">Falafel Corner</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Enter Customer Address</label>
                        <input placeholder="Enter Customer Address" name="customer_address" id="dropoff" type="text" class="form-control"/>
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-danger" onclick="getQuote();">Get Quote</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row p-1" style="display: none" id="delivery-box">
        <div class="offset-3 col-md-6 card ">
            <div class="row p-3">
                <div class="col-md-12">
                    <h3>Delivery Quote By Postmates</h3>
                    <hr/>
                    <div id="quote-info">
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-danger" onclick="getDelivery();">Generate Delivery with this Quote</button>
                </div>
                <div style="margin: 20px" id="delivery-info">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
<script type="application/javascript">
    function getQuote() {
        let data = {
            'pickup':$("#pickup").val(),
            'dropoff':$("#dropoff").val(),
        };

        axios.post(baseUrl + 'delivery/get-quote', data).then(function (response) {
            toastr.success(response.data.message, "Success", {
                timeOut: "3000",
                positionClass: "toast-bottom-right"
            });
            $("#delivery-box").show();
            $("#quote-info").html(response.data.response.html);
        }).catch(function (error) {

        });
    }
    function getDelivery() {
        let data = {
            'pickup':$("#pickup").val(),
            'dropoff':$("#dropoff").val(),
        };

        axios.post(baseUrl + 'delivery/get-delivery', data).then(function (response) {
            toastr.success(response.data.message, "Success", {
                timeOut: "3000",
                positionClass: "toast-bottom-right"
            });
            $("#delivery-info").html(response.data.response.html);
        }).catch(function (error) {

        });
    }
</script>
@endsection
