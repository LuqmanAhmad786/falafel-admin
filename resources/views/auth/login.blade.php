<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Falafel Corner| Admin</title>

    <!-- Scripts -->
    <script src="{{ asset('public/assets/js/laravel/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
{{--    <link rel="shortcut icon" href="{{ asset('public/images/favicon-icon.png') }}">--}}

    <!-- Styles -->
    <link href="{{ asset('public/assets/styles/css/themes.min.css') }}" rel="stylesheet">
    <style>
        .bg-img {
            /*background-image: url('./public/images/admin-backgroud.jpg');*/
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: 100vh;
            position: relative;
            padding: 4rem;
            background: #000000;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px 1px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.08);
            border: 1px solid #fff;
            padding: 14px;
            margin-top: 3rem;
            background: transparent;
        }

        .form-control {
            border: initial;
            outline: initial !important;
            background: transparent;
            border: 1px solid #ced4da;
            color: #ffffff;
        }

        .form-control:focus {
            color: #ffffff;
            background: transparent;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(185, 0, 6, 0.25);
        }
    </style>
</head>
<body>
{{--<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent"></div>
    </div>
</nav>--}}
<div class="py-4 bg-img">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="row mt-5">
                    <div class="col-md-12 text-center logo mb-3">
                        <img width="200" src="{{asset('public/assets/images/logo.png')}}" alt="">
                    </div>
                </div>
                <div class="card">
                    <div class="card-header text-center" style="color: #ffffff;
    font-size: 22px;
    font-weight: bold;">Admin Login
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group row">
                                <input id="email" type="text"
                                       class="form-control @error('email') is-invalid @enderror" name="email"
                                       placeholder="Email Or Username"
                                       value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group mt-3 row">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror" name="password"
                                       placeholder="Password"
                                       required autocomplete="current-password">

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group row mt-3 mb-0">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Login') }}
                                    </button>
                                </div>
                            </div>
                            <div class="form-group row mt-3 mb-0">
                                <div class="col-md-12 text-center">
                                    <div class="text-right">
                                        <a href="{{url('/forget-password')}}">Forget Password?</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

