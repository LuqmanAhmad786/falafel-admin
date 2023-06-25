@extends('layouts.master')

@section('page-css')
    <style>
    .remainingCharacter {
    position: absolute;
    right: 3rem;
    border: transparent;
    background: transparent;
    color: rgba(0,0,0,0.7);
    }
    .totalRemainingCharacter{
    position: absolute;
    right: 0.9rem;
    color: rgba(0,0,0,0.7);
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
                <h2>Menu</h2>
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
            <a class="nav-link" href="{{route('menu-type')}}">Menu Type(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('side-menu-categories')}}">Category(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('side-menu-list')}}">Item(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('items-availability')}}">Item(s) Availability</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('modifier-group')}}">Modifier Group</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('complete-meals')}}">Complete Meal(s)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{route('favorite-name')}}">Favorite Label</a>
        </li>
    </ul>
    <div class="col-md-12">
    <div class="row p-3">
        <div class="col-md-6">
            <h1 class="heading-white mb-4">Favorite Label(s)</h1>
        </div>
        <div class="col-md-6 mt-1">
            <button class="btn btn-primary float-right" data-target="#addNewName" data-toggle="modal">Add New Label
            </button>
        </div>
        @if(sizeof($all_label))
            <div class="col-md-12">
                <table class="table table-hover sortable" id="favoriteTable">
                    <thead>
                    <tr>
                        <th>Name<i class="i-Up---Down"></i></th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($all_label as $key=>$value)
                            <tr>
                                <td>{{$value->label_name}}</td>
                                <td>
                                    <a class="action-links ml-1"
                                       onclick="deleteLabel({{$value->favorite_label_id}})">Delete
                                    </a>
                                </td>
                            </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="col-md-12 mt-5 text-center">
                <img height="150" src="{{asset('public/images/not-found.png')}}">
                <h5 class="mt-3 not-found">Not Found</h5>
            </div>
        @endif
    </div>
    </div>
    <div class="modal fade" id="addNewName" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Label</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="addNewLabel();return false;">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Favorite Label Name</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="label_name"
                                           placeholder="Enter Label Name" required  name="message" onkeydown="limitText(this.form.message,this.form.countdown,20);" onkeyup='limitText(this.form.message,this.form.countdown,20);'></textarea>
                                    <input readonly type="text" name="countdown" size="1" class="text-right remainingCharacter" value="20"><span class="totalRemainingCharacter">/ 20</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    <script>
        function addNewLabel() {
            axios.post(baseUrl + 'add-favorite-label', {'label_name': $('#label_name').val()}).then(function (response) {
                console.log($('#label_name').val());
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                $('#addNewName').modal('hide');
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
        function limitText(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
                return false
            }
        }

        function deleteLabel(labelId) {
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
                    axios.get(baseUrl + 'delete-favorite-label/' + labelId).then(function (response) {
                        toastr.success('Label Deleted Successfully.', "Success", {
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
    </script>
    <script>
        var table = $('#favoriteTable');
        $('.sortable th')
            .wrapInner('<span title="sort this column"/>')
            .each(function(){
                var th = $(this),
                    thIndex = th.index(),
                    inverse = false;
                th.click(function(){
                    table.find('td').filter(function(){
                        return $(this).index() === thIndex;
                    }).sortElements(function(a, b){
                        if( $.text([a]) == $.text([b]) )
                            return 0;
                        return $.text([a]) > $.text([b]) ?
                            inverse ? -1 : 1
                            : inverse ? 1 : -1;
                    }, function(){
                        return this.parentNode;
                    });
                    inverse = !inverse;
                });
            });
    </script>
@endsection
