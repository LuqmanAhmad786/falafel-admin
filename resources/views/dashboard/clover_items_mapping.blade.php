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

        .table th, .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }

        .remainingCharacter {
            position: absolute;
            right: 3rem;
            top: 6rem;
            border: transparent;
            background: transparent;
            color: rgba(0, 0, 0, 0.7);
        }

        .totalRemainingCharacter {
            position: absolute;
            right: 1.3rem;
            top: 6.09rem;
            color: rgba(0, 0, 0, 0.7);
        }

        .remainingCharacterName {
            position: absolute;
            right: 3rem;
            top: 3.6rem;
            border: transparent;
            background: transparent;
            color: rgba(0, 0, 0, 0.7);
        }

        .totalRemainingCharacterName {
            position: absolute;
            right: 1.3rem;
            top: 3.66rem;
            color: rgba(0, 0, 0, 0.7);
        }

        /*  select.filter{
              background: url(












        {{url('/')}}












        /public/images/angle-arrow-down-black.png) no-repeat 22rem transparent !important;
                                                                                                                }*/
        /* select#category{
             background: url(












        {{url('/')}}












        /public/images/angle-arrow-down-black.png) no-repeat right transparent !important;
                                                                                                                }*/
        select#editCategory {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat right transparent !important;
        }

        .select2-container--default .select2-selection--multiple {
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat 46rem #f8f9fa !important;
        }

        .card {
            padding: 15px;
        }
    </style>
@endsection

@section('main-content')
    {{--  <div class="row">
          <div class="col-md-12">
              <h1 class="heading-white">Item(s)</h1>
          </div>
      </div>--}}
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Clover Item Mapping</h2>
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


    <div class="row p-3" id="top-area">
        <div class="col-md-12">
            <p>Note: Relationship made between your online menu and your clover menu, will <span
                    class="font-weight-bolder"> ONLY synchronize pricing from clover</span>.
                This relationship settings will not synchronized any other changes you made in your clover,such as item
                name change,modifier adding or removal or anything else.</p>
        </div>
    </div>

    <div class="row p-3">
        <div class="col-md-12 text-right">
            <a href="{{url('/')}}/clover/fetch-item/items">
                <button class="btn btn-primary float-right ml-5">Import all clover menu items
                </button>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 pl-4">
            <select class="form-control" name="category" id="select_cat">
                <option>Select Category</option>
                @foreach($categories as $category)
                    <option
                        value="{{$category->category_id}}" {{$category->category_id == 1 ? "selected" : ""}}>{{$category->category_name}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-10 text-right">
            <p><span class="font-weight-bolder">{{$clover_items_count}}</span> menu items are imported from clover</p>
        </div>
    </div>
    <form onsubmit="mapCloverItem()">
    <div class="col-md-12 mt-3" id="found">
        <table class="table table-bordered text-center">
            <thead>
            <tr>
                <th>Natomas current online menu item</th>
                <th>Choose related clover menu item</th>
            </tr>
            </thead>
            <tbody id="item_list">
            <td>Please choose category from dropdown above</td>
            <td><select class="form-control" style="max-width:30%;" disabled>
                    <option>Select</option>
                </select></td>
            </tbody>
        </table>
    </div>
    <div class="col-md-12 text-right">
        <button class="btn btn-success" type="submit" id="relation_btn" disabled>Save Relationship</button>
    </div>
    </form>
    <div class="col-md-12" id="warn_div" style="display:none">
        <p><span class="font-weight-bolder" style="color:red">WARNING: </span>By clicking "Save Relationship" button,
            your <span class="font-weight-bolder">online menu item pricing information</span> will be change and pricing
            information will be taken from your selected clover menu item under "Choose Related Clover Menu Item"
            column.</p>
        <br>
        <p>Any change made on pricing in future,will still required admin to manually clicking the sync button to push
            the new item pricing from clover.</p>
    </div>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
@endsection

@section('bottom-js')
    <script type="text/javascript">
        function mapCloverItem() {
            // axios.post(baseUrl + '', data).then(function (response) {
            //     toastr.success(response.data.message, "Success", {
            //         timeOut: "3000",
            //         positionClass: "toast-bottom-right"
            //     });
            //     setTimeout(function () {
            //         window.location.reload();
            //     }, 500);
            // }).catch(function (error) {
            //     //
            // });
        }

        $('#select_cat').change(function () {
            var cat_id = $('#select_cat').val();
            var baseUrl = "{{URL::to('/')}}";
            var cloverArray = {!! json_encode($clover_html) !!};

            $.ajax({
                url: baseUrl + '/get-category-items/' + cat_id,
                type: "GET",
                success: function (data) {
                    $('#item_list').empty();
                    $.each(data.response, function () {
                        $('#item_list').append('<tr><td>' + this.item_name + ' - ' + this.item_price + '</td><td>' + cloverArray + '')
                    });
                }
            })
            $('#relation_btn').removeAttr('disabled');
            $('#warn_div').show();
        })


    </script>
@endsection
