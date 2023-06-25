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

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px 1px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.08);
            border: 0;
            height: 230px;
        }
    </style>
@endsection

@section('main-content')
    <ul class="nav nav-tabs p-0" role="tablist">
        @foreach($all_menus as $value)
            <li class="nav-item col-md-2 text-center">
                <a class="nav-link {{ Route::currentRouteName()==$value->name ? 'active' : '' }}">
                    {{$value->name}}
                </a>
            </li>
        @endforeach
    </ul>
@endsection

@section('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
@endsection

@section('bottom-js')
@endsection
