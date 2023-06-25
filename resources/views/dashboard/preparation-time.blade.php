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

        /*i {
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
        }*/

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

        /*.table th, .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            min-width: 200px;
        }*/

    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Settings</h2>
            </div>
            {{-- <div class="col-md-6">
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
                                 <select class="form-control  selectpicker" id="my_restaurant" onchange="onRestaurantChange()">
                                     @foreach($header_restaurant as $item)
                                         <option
                                             value="{{$item->id}}"
                                             {{$item->id==Session::get('my_restaurant') ? 'selected':''}}>
                                             {{$item->address}}
                                         </option>
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
             </div>--}}
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{route('tax-settings')}}">Global Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{route('preparation-time')}}">Time Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('setting-restaurants')}}">Location Settings</a>
        </li>
    </ul>
    <div class="col-md-12">
    <div class="row mt-4">
        <div class="col-md-6">
                <h1 class="heading-white mb-3">Time settings (Preparation & Feedback)</h1>
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
    <div class="card p-2 mt-4">
        <div class="col-md-12 mt-3 table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Timing For</th>
                    <th>Mins</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @if(!$response[0]->preparation_time)
                    <tr>
                        <td>
                            <div class="col-md-3">
                                @if(!$response[0]->preparation_time)
                                    <button class="btn btn-primary float-right ml-3" onclick="addTimeModal(1)"
                                            data-toggle="modal">Add
                                        Preparation
                                        Time
                                    </button>
                                @endif
                                @if(!$response[0]->pickup_time)
                                    <button class="btn btn-primary float-right" onclick="addTimeModal(2)"
                                            data-toggle="modal">Add Pickup
                                        Time
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endif
                </tbody>
                <tbody>
                @if($response[0]->preparation_time)
                    <tr>

                        @if($response[0]->preparation_time)
                            <td>1</td>
                            <td>
                               Current Preparation time
                            </td>
                            <td>
                                <b>{{$response[0]->preparation_time / 60}} Mins</b>
                            </td>
                            <td>
                                <a class=" btn action-links ml-0 pl-0"
                                   onclick="addTimeModal(1)" data-toggle="modal">Change</a>
                            </td>
                            @endif
                    </tr>
                    <tr>

                                @if($response[0]->pickup_time)
                                    <td>2</td>
                                    <td>Feedback duration after pickup should be</td>
                        <td>
                                            <b>{{$response[0]->pickup_time / 60}} Mins</b>
                        </td>
                        <td>
                                            <a class=" btn action-links ml-0 pl-0"
                                               onclick="addTimeModal(2)" data-toggle="modal">Change</a>
                        </td>
                                @endif

                    </tr>
                @endif
                </tbody>
                @If(!$response[0]->pickup_time && !$response[0]->preparation_time)
                    <div class="col-md-12 mt-5 text-center">
                        <img height="150" src="{{asset('public/images/not-found.png')}}">
                        <h5 class="mt-3 not-found">Not Found</h5>
                    </div>
                @endif
            </table>
        </div>
    </div>
    </div>
        <div class="modal fade" id="addTime" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal_title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form name="addTimeForm" onsubmit="addPreparationTime();return false;">
                            <div class="row">
                                <div class="col-md-12" id="show_preparation">
                                    <input class="form-control" id="preparation"
                                           type="number"
                                           value="{{$response[0]->preparation_time ? $response[0]->preparation_time / 60 : ''}}"
                                           placeholder="Enter preparation time in minute's">
                                </div>
                                <div class="col-md-12" id="show_pickup">
                                    <input class="form-control" id="pickup"
                                           type="number"
                                           value="{{$response[0]->pickup_time ? $response[0]->pickup_time / 60 : ''}}"
                                           placeholder="Enter pickup time in minute's">
                                </div>
                                <div class="col-md-12 mt-3 text-right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
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
                let isFlag;

                function addTimeModal(flag) {
                    isFlag = flag;
                    $('#addTime').modal('show');
                    if (isFlag == 1) {
                        $('#modal_title').text('Preparation Time');
                        $('#show_preparation').show();
                        $('#show_pickup').hide();
                        ``
                    } else if (isFlag == 2) {
                        $('#modal_title').text('Pickup Time');
                        $('#show_pickup').show();
                        $('#show_preparation').hide();
                    }
                }

                function addPreparationTime() {
                    axios.post(baseUrl + 'preparation-time-setting', {
                        'preparation_time': $('#preparation').val(),
                        'pickup_time': $('#pickup').val(),
                        'flag': isFlag,
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
@endsection
