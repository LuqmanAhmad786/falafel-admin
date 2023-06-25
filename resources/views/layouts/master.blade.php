<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Falafel Corner | Admin</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    @yield('before-css')
    {{-- theme css --}}
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/themes.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/fonts/iconsmind/iconsmind.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/styles/vendor/perfect-scrollbar.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    {{--    <link rel="shortcut icon" href="{{ asset('public/images/favicon-icon.png') }}">--}}
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/styles/css/custom.css') }}" />

    <link rel="stylesheet"
          href="{{asset('vendor/mervick/emojionearea/dist/emojionearea.min.css')}}"> {{-- page specific css --}}
    <style>

        .header-button i {
            color: #000000 !important;
            font-weight: bold !important;
        }

        .text-primary {
            color: #a92219;
        }

        /*#select-restaurant .form-control {
            outline: initial !important;
            !*background: #b90006 !important;*!
            background: url(






        {{url('/')}}






        /public/images/angle-arrow-down.png) no-repeat right #b90006 !important;
                                                                    color: #ffffff;
                                                                    border: none;
                                                                    border-bottom: 1px solid #fff;
                                                                    border-radius: 0;
                                                                    padding-left: 0;
                                                                    font-size: 15px;
                                                                    width: 250px;
                                                                }*/

        .not-found {
            color: gray !important;
        }

        .layout-sidebar-large .main-header .header-part-right .user img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        /* #select-restaurant select {
             width: 358px !important;
             padding: 5px !important;
             font-size: 16px !important;
             line-height: 1 !important;
             border: 0;
             border-radius: 5px;
             height: 34px !important;
             background: url(






        {{url('/')}}






        /public/images/angle-arrow-down.png) no-repeat right #a92219 !important;
                                                                    -webkit-appearance: none !important;
                                                                    background-position-x: 339px !important;
                                                                }*/

        #copy_menu select {
            width: 358px !important;
            padding: 5px !important;
            font-size: 16px !important;
            line-height: 1 !important;
            border: 0;
            border-bottom: 2px solid #000;
            border-radius: 0;
            height: 34px !important;
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat right transparent !important;
            -webkit-appearance: none !important;
            background-position-x: 339px !important;
        }

        #sales_graph select {
            width: 200px !important;
            padding: 5px !important;
            font-size: 14px !important;
            line-height: 1 !important;
            border: 0;
            border-bottom: 1px solid #000;
            border-radius: 0;
            height: 34px !important;
            background: url({{url('/')}}/public/images/angle-arrow-down-black.png) no-repeat right transparent !important;
            -webkit-appearance: none !important;
            float: right;
            /*background-position-x: 339px !important;*/
        }

        .header-button {
            background: #fff;
            border: none;
            padding: 8px;
            color: #a92219;
        }

        /* .layout-sidebar-large .sidebar-left-secondary, .layout-sidebar-large .sidebar-left {
             top: 0px !important
         }

         .layout-sidebar-large .main-header {
             left: 220px !important;
         }*/

        .layout-sidebar-large .sidebar-left.open {
            overflow: auto;
        }

        .main-content-wrap.sidenav-open {
            width: calc(100% - 222px);
            padding-left: 2rem !important;
        }

        i {
            cursor: pointer !important;
        }

        h1, h2, h3, h4, h5, h6, .card-title, .text-title {
            color: #303030;
            font-weight: 600;
        }

        .action-links {
            text-decoration: underline !important;
            color: #a92219 !important;
            cursor: pointer;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #63191b;
            font-weight: 100 !important;
        }

        .layout-sidebar-large .sidebar-left .navigation-left .nav-item .nav-item-hold {
            cursor: auto;
        }


        /************************style.css***************/

        .card-title {
            font-size: 28px;
            text-transform: capitalize;
            font-weight: normal;
        }


        .avatar-lg {
            object-fit: cover;
        }

        .modal-title {
            font-size: 28px;
            text-transform: capitalize;
            font-weight: normal;
            color: #a92219;
        }

        .heading-white {
            font-size: 28px;
            font-weight: normal;
            color: #000000;
        }

        .custom-files img {
            object-fit: cover;
            object-position: center;
        }

        .reset-button {
            margin-top: 0px !important;
            color: #ffffff !important;
        }

        .box button {
            background: #ffffff;
            border: 1px solid #C0C0C0;
            width: 26px;
            height: 25px;
            color: #C0C0C0;
        }

        .box select {
            margin-top: 10px;
            color: #fff;
            background-color: #b90006;
            border-color: #b90006;
            border-radius: 4px;
        }

        .color_page {
            background: #b90006 !important;
            color: #ffffff !important;
            border: #C0C0C0 !important;
            width: 26px;
            height: 25px;
        }

        .first-row {
            font-weight: 600 !important;
            font-size: 16px !important;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            /*min-width: 200px;*/
            cursor: pointer;
        }

        .note-text {
            color: darkgrey;
            font-weight: 100 !important;
        }

        body {
            font-family: sans-serif;
            font-size: 0.850rem !important;
            color: #000000 !important;
        }

        .s_no {
            max-width: 35px !important;
        }

        .box button {
            background: #ffffff;
            border: 1px solid #C0C0C0;
            width: 26px;
            height: 25px;
            color: #C0C0C0;
            border-radius: 50%;
            margin: 4px;
        }
    </style>
    @yield('page-css')
</head>
<body class="text-left">
<div class="app-admin-wrap layout-sidebar-large clearfix">
    @include('layouts.header-menu')
    @include('layouts.sidebar')
    <div class="main-content-wrap sidenav-open d-flex flex-column p-0">
        <div class="main-content">
            @yield('main-content')
        </div>
        @include('layouts.footer')
    </div>
    {{-- common js --}}
</div>
<button style="visibility: hidden" onclick="playAudio()" id="play-audio" type="button">Play Audio</button>
<audio id="myAudio" loop>
    <source src="{{asset('public/sound/order-alert.ogg')}}" type="audio/ogg">
    <source src="{{asset('public/sound/order-alert.mp3')}}" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>
<script src="{{asset('public/assets/js/common-bundle-script.js')}}"></script>
<script src="{{asset('public/assets/js/laravel/app.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
{{-- page specific javascript --}}
@yield('page-js')

<script src="{{asset('public/assets/js/es5/script.min.js')}}"></script>
<script src="{{asset('public/assets/js/paginator.js')}}"></script>

<script src="{{asset('public/assets/js/es5/sidebar.large.script.min.js')}}"></script>
{{--<script src="{{asset('node_module/sweetalert2/dist/sweetalert2.min.js')}}"></script>--}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.2/dist/sweetalert2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment-with-locales.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/6.6.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/6.6.1/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/6.6.1/firebase-firestore.js"></script>
<script src="https://www.gstatic.com/firebasejs/6.6.1/firebase-database.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/paginationjs/2.1.5/pagination.js"></script>
<script src="https://rawgit.com/padolsey/jQuery-Plugins/master/sortElements/jquery.sortElements.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
<script src="/node_modules/readmore-js/readmore.min.js"></script>
<script src="{{asset('vendor/mervick/emojionearea/dist/emojionearea.min.js')}}"></script>
<script src="https://cdn.tutorialjinni.com/Readmore.js/2.2.1/readmore.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAu8XCn19KUmYjZD5zXgDqqdhrWtTjyAJQ&v=3.exp&sensor=false&libraries=places"></script>

<script>
    $(document).ready(function () {
        $('input.timepicker').timepicker({});
    });
    var url = window.location;
    $('.sub-menu ul').hide();
    $(".sub-menu a").click(function () {
        $(this).parent(".sub-menu").children("ul").slideToggle("slow");
        $(this).find(".right").toggleClass("fa-caret-up fa-caret-down");
    });
    /*    $('ul.sidebar-menu a').filter(function() {
        return this.href == url;
    }).parent().addClass('active');

    // for treeview
    $('ul.tree a').filter(function() {
        return this.href == url;
    }).parentsUntil(".side-menu > .tree").addClass('active');*/
    function initialize() {
        var input = document.getElementById('latLngAddress');
        var options = {
            componentRestrictions: {country: "us"}
        };
        var autocomplete = new google.maps.places.Autocomplete(input,options);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('latitude').value = place.geometry.location.lat();
            document.getElementById('longitude').value = place.geometry.location.lng();
        });

        var input2 = document.getElementById('edit_latLngAddress');
        var autocomplete2 = new google.maps.places.Autocomplete(input2,options);
        google.maps.event.addListener(autocomplete2, 'place_changed', function () {
            var place = autocomplete2.getPlace();
            document.getElementById('edit_latitude').value = place.geometry.location.lat();
            document.getElementById('edit_longitude').value = place.geometry.location.lng();
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>
{{--<script>
    $(function(){

        var url = window.location.pathname,
            urlRegExp = new RegExp(url.replace(/\/$/,'') + "$"); // create regexp to match current url pathname and remove trailing slash if present as it could collide with the link in navigation in case trailing slash wasn't present there
        // now grab every link from the navigation
        console.log(url);
        $('.sub-menu a').each(function(){
            // and test its normalized href against the url pathname regexp
            if(urlRegExp.test(this.href.replace(/\/$/,''))){
                $(this).addClass('active');
            }
        });
    });
</script>--}}
<script>
    const orderRange = "{{request()->get('orderrange')}}";
    $(function () {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
        }, function (start, end, label) {
            console.log(start, end);
        });
    });
    $(function () {
        $('input[name="date_single"]').daterangepicker({
            opens: 'left',
            singleDatePicker: true
        }, function (start, end, label) {
        });
        $('input[name="orderrange"]').daterangepicker({
            opens: 'left'
        }, function (start, end, label) {
        });
        if(!orderRange){
            $('input[name="orderrange"]').val('');
            $('input[name="orderrange"]').attr("placeholder", "Please select date");
        }
    });
</script>
<script>
    var x = document.getElementById("myAudio");

    function playAudio() {
        // x.play();
    }

    function pauseAudio() {
        // x.pause();
    }
</script>
<script>
    const firebaseConfig = {
        apiKey: "AIzaSyBo-7hZ9RxMX5CIghEb1PrjGmxID_iMmEM",
        authDomain: "",
        databaseURL: "",
        projectId: "",
        storageBucket: "",
        messagingSenderId: "397325777982",
        appId: "1:397325777982:web:14f64ea6f55a7be3b0256f"
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
</script>
<script>
    let assignedLocation = '{{ Auth::user()->assigned_location }}';
    let ignoreOrderStatus = true;
    let ordersArray = [];
    var restaurantId = '{{Session::get('my_restaurant')}}';

    if (assignedLocation && assignedLocation != '') {
        restaurantId = assignedLocation;
    }
    console.log('res ' + restaurantId);
    // Get a reference to the database service
    firebase.database().ref().child('orders').on('child_added', function (snapshot) {
        if (ignoreOrderStatus) {
            firebase.database().ref().child('orders')
        } else if (!ignoreOrderStatus) {
            console.log(snapshot.val().restaurant_id);
            console.log(restaurantId);
            if (restaurantId == snapshot.val().restaurant_id) {
                $("#play-audio").trigger("click");
                toastr.info(snapshot.val().message, snapshot.val().title, {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                ordersArray.push(snapshot.val().order_id);
                pushOrderList(snapshot.val());
                localStorage.setItem('orders', JSON.stringify(ordersArray));
                console.log('Pushing order to generate bill', ordersArray);
            }
        }
    });

    function pushOrderList(data) {
        let html = ``;
        if (data.user_id != 0) {
            html = `<tr>
                            <td><a style="text-decoration: underline;" href="/orders/details/${data.order_id}">#${data.order_id}
                                </a></td>
                            <td><a style="text-decoration: underline;" target="_blank" href="http://localhost/falafel-backend/users/customer-details/${data.user_id}">
                                   ${data.ordered_by}</a>
                                                                                            </td>
                            <td>${data.pickup_time}</td>
                            <td id="order_${data.order_id}">
                                                                    <span class="badge badge-warning">Received</span>
                                                            </td>
                            <td>
                                                                    <span class="badge badge-success">Paid</span>
                                                            </td>
                            <td>${data.order_total}</td>
                            <td>
                                                                    NA
                                                            </td>
                            <td class="td-width">
                                                                    <a title="Edit Preparation Time" style="color: #a92219;text-decoration: underline;cursor: pointer;" onclick="editTime(${data.order_id})">Extend Time</a>
                                                                <a class="ml-3" title="Print" style="color: #a92219;text-decoration: underline;cursor: pointer;" onclick="getSingleOrder(${data.order_id})">Print</a>
                                                            </td>
                        </tr>`;
        } else {
            html = `<tr>
                            <td><a style="text-decoration: underline;" href="/orders/details/${data.order_id}">#${data.order_id}
                                </a></td>
                            <td>${data.ordered_by}
                                                                                            </td>
                            <td>${data.pickup_time}</td>
                            <td id="order_${data.order_id}">
                                                                    <span class="badge badge-warning">Received</span>
                                                            </td>
                            <td>
                                                                    <span class="badge badge-success">Paid</span>
                                                            </td>
                            <td>${data.order_total}</td>
                            <td>
                                                                    NA
                                                            </td>
                            <td class="td-width">
                                                                    <a title="Edit Preparation Time" style="color: #a92219;text-decoration: underline;cursor: pointer;" onclick="editTime(${data.order_id})">Extend Time</a>
                                                                <a class="ml-3" title="Print" style="color: #a92219;text-decoration: underline;cursor: pointer;" onclick="getSingleOrder(${data.order_id})">Print</a>
                                                            </td>
                        </tr>`;
        }
        $('#itemTable').prepend(html);
    }

    // firebase order status updation
    firebase.database().ref().child('orders_list').on('child_changed', function (snapshot) {
        console.log(snapshot.val().order_id);
        if (snapshot.val().status == 1) {
            $('#order_' + snapshot.val().order_id).html("<span class='badge badge-warning'>Received</span>");
        } else if (snapshot.val().status == 2) {
            $('#order_' + snapshot.val().order_id).html("<span class='badge badge-primary'>Ready For Pickup</span>");
        } else if (snapshot.val().status == 3) {
            $('#order_' + snapshot.val().order_id).html("<span class='badge badge-success'>Picked Up</span>");
        }
    });

    $(document).ready(function () {
        setInterval(function () {
            let orders = JSON.parse(localStorage.getItem('orders'));
            console.log('Start to generate', orders);
            if (orders.length > 0) {
                for (x = 0; x < orders.length; x++) {
                    console.log(orders[x]);
                    getSingleOrder(orders[x]);
                }
            }
        }, 10000);
    });

    firebase.database().ref().child('orders').once('value', function (snapshot) {
        ignoreOrderStatus = false;
    });

    function getSingleOrder(orderId) {
        axios.get(baseUrl + 'get-single-order/' + orderId).then(function (response) {
            if (response.data.response) {
                printBill(response.data.response, orderId);
                printBill(response.data.response, orderId);
            } else {
                let orders = JSON.parse(localStorage.getItem('orders'));
                console.log('Checking', orders);
                if (orders.length > 0) {
                    console.log('Checking Order Id', orderId);
                    for (x = 0; x < orders.length; x++) {
                        if (orders[x] == orderId) {
                            orders.splice(x, 1);
                        }
                    }
                    localStorage.setItem('orders', JSON.stringify(orders));
                    ordersArray = [];
                    // axios.get(baseUrl + 'remove-fcm-table').then(function (response) {
                    // });
                }
            }
        });
    }

    function printBill(data, orderId) {
        $.ajax({
            type: "POST",
            url: 'http://localhost/print-php/example/receipt-with-logo.php',
            data: JSON.stringify(data),
            contentType: "application/json",
            dataType: "json",
            success: function (result) {
                toastr.success('Receipt Printed Successfully.', '', {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                let orders = JSON.parse(localStorage.getItem('orders'));
                console.log('Checking', orders);
                if (orders.length > 0) {
                    console.log('Checking Order Id', orderId);
                    for (x = 0; x < orders.length; x++) {
                        if (orders[x] == orderId) {
                            orders.splice(x, 1);
                        }
                    }
                    localStorage.setItem('orders', JSON.stringify(orders));
                    ordersArray = [];
                    axios.get(baseUrl + 'remove-fcm-table').then(function (response) {
                    });
                }
            }
        });
    }

    $(document).ready(function () {
        if (window.location.href == baseUrl + 'menu/menu-type') {
            var location = '{{Session::get('my_restaurant')}}';
            if (location == 0) {
                $('#setLocationModal').modal('show');
            }
        }
    });

</script>

@yield('bottom-js')
</body>

</html>
