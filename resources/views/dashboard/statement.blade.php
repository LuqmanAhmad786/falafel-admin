@extends('layouts.master')

@section('page-css')
    <style>
        ul.breadcrumb {
            padding: 10px 16px;
            list-style: none;
            background-color: transparent;
            border-bottom: 1px solid #a92219;
            border-radius: 0;
            padding-left: 0;
        }

        ul.breadcrumb li {
            display: inline;
            font-size: 18px;
        }

        ul.breadcrumb li + li:before {
            padding: 8px;
            color: black;
            content: "/\00a0";
        }

        ul.breadcrumb li a {
            color: #a92219;
            text-decoration: none;
        }

        ul.breadcrumb li a:hover {
            color: #a92219;
            text-decoration: underline;
        }

        .card-ecommerce-3 .card-img-left {
            height: 300px;
            -o-object-fit: cover;
            object-fit: cover;
            width: 500px;
        }

        .order-box {
            border: 1px solid #d1d1d1;
        }

        .text-primary {
            color: #a92219;
        }

        hr {
            margin-top: 12px;
            margin-bottom: 15px;
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            height: 0;
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
    <div class="col-md-12 text-center mt-5">
        <h1>Coming Soon</h1>
    </div>
@endsection

@section('page-js')
@endsection
