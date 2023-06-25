@extends('layouts.master')

@section('page-css')
    <style>
        .select2-container {
            width: 100% !important;
            padding: 0;
        }

        .td-width {
            min-width: 183px;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            min-width: 155px !important;
        }
        .card {
            padding: 15px;
        }
    </style>
@endsection

@section('main-content')
    <div class="card" style="border-radius: 0">
        <div class="row m-0">
            <div class="col-md-6">
                <h2>Users</h2>
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
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{route('customers')}}">Customer(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{route('guests')}}">Guest(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('managers')}}">Manager(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('server-users')}}">Server App User(s)</a>
        </li>
    </ul>
    <div class="row p-1">
        <div class="col-md-12">
            <h1 class="heading-white mb-3">Guest(s)</h1>
        </div>
        <div class="col-md-12">
            <form action="{{route('guests')}}" method="GET" style="width: 100%">
                <div class="col-md-12 card p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="keyword"
                                   placeholder="Filter by keyword (Name, Phone-no, Email)"
                                   value="{{ app('request')->input('keyword') }}"
                            >
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary reset-button">
                                Search
                            </button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{route('guests')}}">
                                Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-12 mt-3" id="found">
            <table class="table table-responsive table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <td>Last Order At</td>
                </tr>
                </thead>
                <tbody id="customer_list">
                  @foreach($users as $key=>$value)
                      <tr>
                          <td>{{$value->user_first_name}} {{$value->user_last_name}}</td>
                          <td>{{$value->user_email}}</td>
                          <td>{{phoneFormat($value->user_number)}}</td>
                          <td>{{date('n/j/Y',strtotime($value->created_at))}}</td>
                      </tr>
                  @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-12 pl-4">
            {{$users->appends(request()->query())->links("pagination::bootstrap-4")}}
        </div>
    </div>
@endsection

@section('page-js')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@endsection

@section('bottom-js')

@endsection

