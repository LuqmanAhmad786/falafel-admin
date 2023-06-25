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

        .category-tab {
            background: #EDF3F9;
            padding: 10px;
        }
    </style>
@endsection

@section('main-content')
    <div class="row">
        <div class="col-md-6">
            <h1 class="heading-white mb-3">Pagination Limit</h1>
        </div>
        <div class="col-md-6">
            @if(!sizeof($list))
                <button class="btn btn-primary float-right ml-3" onclick="addLimit()" data-toggle="modal">Add
                    Pagination
                    Limit
                </button>
            @endif
        </div>
        @if(sizeof($list))
            <div class="col-md-12 mt-3">
                <h4 style="font-weight: inherit">Current pagination limit is :
                    <b>{{$list[0]->limit}} </b>
                    <a class="ml-3 btn btn-primary" style="cursor: pointer;color: #ffffff;font-size: 15px;"
                       onclick="addLimit()" data-toggle="modal">Change</a></h4>
            </div>
        @endif
    </div>
    <div class="modal fade" id="addLimitModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pagination Limit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="addTimeForm" onsubmit="addLimitSubmit();return false;">
                        <div class="row">
                            <div class="col-md-12">
                                <input class="form-control" id="limit"
                                       type="number"
                                       value="{{sizeof($list)? $list[0]->limit: ''}}"
                                       placeholder="Enter preparation time in minute's">
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

        function addLimit() {
            $('#addLimitModal').modal('show');
        }

        function addLimitSubmit() {
            axios.post(baseUrl + 'set-pagination-limit', {
                'limit': $('#limit').val(),
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
