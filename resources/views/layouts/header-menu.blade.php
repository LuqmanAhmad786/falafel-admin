<style>
    .star option {
        background-color: darkgrey;
    }
    .star option:hover {
        background-color: darkgrey!important;
    }
    .star option:active {
        background-color: gray!important;
    }
    .bootstrap-select>.dropdown-toggle {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
    }
    .btn-light:not(:disabled):not(.disabled):active, .btn-light:not(:disabled):not(.disabled).active, .show > .btn-light.dropdown-toggle {
        color: unset;
        background-color: unset;
        border-color: unset;
    }
    .bootstrap-select .dropdown-toggle:focus, .bootstrap-select>select.mobile-device:focus+.dropdown-toggle {
    outline: #cccccc!important;
        box-shadow: none!important;
    }
    .dropdown-item:hover, .dropdown-item:focus {
        color: #4f1416;
        text-decoration: none;
        background-color: #ccc;
    }
    /*.bootstrap-select>.dropdown-toggle.header {
        color: white;
        background-color: #b90006;
        border: 1px solid #ced4da;
        font-size: 13px;
    }
    .bootstrap-select .dropdown-toggle:focus, .bootstrap-select>select.mobile-device:focus+.dropdown-toggle {
        outline: unset !important;
        outline: unset;
        outline-offset: unset;
    }
    .btn-light:not(:disabled):not(.disabled):active, .btn-light:not(:disabled):not(.disabled).active, .show > .btn-light.dropdown-toggle {
        color: white;
        background-color: #b90006;
        border: 1px solid #ced4da;
    }
    .bootstrap-select>.dropdown-toggle.bs-placeholder, .bootstrap-select>.dropdown-toggle.bs-placeholder:active, .bootstrap-select>.dropdown-toggle.bs-placeholder:focus, .bootstrap-select>.dropdown-toggle.bs-placeholder:hover {
        color: #63191b;
    }*/
    </style>
{{--<div class="main-header">
    <div class="logo">
        <a href="{{url('/')}}">
            <img src="{{asset('public/assets/images/logo.png')}}" alt="">
        </a>
    </div>
    <div class="d-flex align-items-center">

    </div>
    <div style="margin: auto" id="select-restaurant">
        <div class="row">
            @if(Auth::guard('admin')->user()->type ==1)
                <div class="col-md-5 mt-2 p-0">
                    <h5 style="color: #ffffff;float: right">Change Location :</h5>
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
<div class="row">
            <div class="col-md-4 mt-2 pr-0">
                <h5 style="color: #ffffff;float: right">Change Location :</h5>
            </div>
            <div class="col-md-8">
                @if(Auth::guard('admin')->user()->type ==1)
                    <select class="form-control" id="my_restaurant" onchange="onRestaurantChange()">
                        @foreach($header_restaurant as $item)
                            <option
                                value="{{$item->id}}"
                                {{$item->id==Session::get('my_restaurant') ? 'selected':''}}>
                                {{$item->address}}
                            </option>
                        @endforeach
                    </select>
                @else
                    <select class="form-control" id="my_restaurant" disabled>
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
    <div class="header-part-right">
        <div class="dropdown">
            <div class="user col align-self-end mr-0">
 <img src="{{asset('public/images/avatar.png')}}" id="userDropdown" alt="" data-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false">

                @if(Auth::guard('admin')->user()->type !=1)
                    <a id="userDropdown" data-toggle="dropdown" style="    margin-right: 1px;"
                       aria-haspopup="true"
                       aria-expanded="false" class="header-button">{{Auth::guard('admin')->user()->name}}</a>
                @endif
                <a class="header-button" onclick="adminLogout()" title="Logout" ><i class="i-Power-2"></i></a>
 <a class="header-button"  ><i class="i-Right"></i>  Logout</a>

<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown" style="left:38px!important;">
                    <a class="dropdown-item" href="{{route('logout')}}">Sign out</a>
                </div>

            </div>
        </div>
    </div>
</div>--}}
<script>
    function onRestaurantChange() {
        axios.get(baseUrl + 'on-restaurant-change/' + $('#my_restaurant').val()).then(function (response) {
            setTimeout(function () {
                window.location.reload();
            }, 500);
        }).catch(function (error) {
        });
    }
    /*function adminLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to logout.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Logout!'
        }).then(function (result) {
            if (result.value
            ) {
                axios.get(baseUrl + 'logout').then(function (response) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 200);
                }).catch(function (error) {
                });
            }
        })
    }*/
</script>
