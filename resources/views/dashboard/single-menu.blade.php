@extends('layouts.master')

@section('page-css')
@endsection

@section('main-content')
    <div class="row pl-3 pr-3 pb-3">
        <div class="card">
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" value="{{$details->id}}" id="menu_id">
                    <img class="img-fluid" style="height: 300px;width: 1000px;" src="{{$details->image}}">
                </div>
                <div class="col-md-6">
                    <div class="pt-3">
                        <h2>{{$details->name}}</h2>
                        <h5 class="mt-3">{{$details->description}}</h5>
                        <h2 class="mt-3">${{$details->price}}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 col-md-12">
            @if(!sizeof($categories))
                No categories found
            @endif
        </div>
        <table class="table table-hover">
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>
                        <h4>{{$category->name}}</h4>
                    </td>
                    <td>
                        @if($category->selected)
                            <button class="btn btn-secondary float-right" onclick="removeCategories({{$category->category_id}})">Remove</button>
                        @else
                            <button class="btn btn-primary float-right" onclick="addCategories({{$category->category_id}})">Add</button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    <script>
        $(document).ready(function () {
            $('form').on('submit', function (e) {
                e.preventDefault();
            });
        });

        function removeCategories(id) {
            let data = {
                'menu_id': $('#menu_id').val(),
                'category': id,
                'status': 0
            };

            axios.post(baseUrl + 'add-side-menu-category', data).then(function () {
                toastr.success("Time updated successfully", "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });

                setTimeout(function () {
                    window.location.reload();
                }, 500);
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }

        function addCategories(id) {
            let data = {
                'menu_id': $('#menu_id').val(),
                'category': id,
                'status': 1
            };
        }
    </script>
@endsection
