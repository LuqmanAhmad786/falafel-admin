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

        .card-title {
            font-size: 13px !important;
            margin-bottom: 0.5rem;
        }
    </style>
@endsection

@section('main-content')
    <ul class="nav nav-tabs p-0" role="tablist">
        <li class="nav-item col-md-2 text-center">
            <a class="nav-link active" href="javascript:void(0)">
                Breakfast
            </a>
        </li>
        <li class="nav-item col-md-2 text-center">
            <a class="nav-link" href="{{route('menu-lunch')}}">
                Lunch
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <div class="row">
                <div class="col-md-3">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="text-center mt-5 text-20" id="add_new_action" onclick="clickAddMenu()">
                                <a href="javascript:void(0)">
                                    <i class="i-Add"></i>
                                    <div>Add New Item</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($menu as $item)
                    <div class="col-md-3">
                        {{--style="height: 335px;"--}}
                        <div class="card mb-4">
                            <div class="card-body" id="view_break_fast_{{$item->id}}">
                                <div class="card-title mb-0" title="{{$item->name}}">
                                    {{Str::limit($item->name, 25)}}
                                    <div class="float-right">${{$item->price}}</div>
                                </div>
                                <div class="row mt-1 mb-2 pl-3 text-primary">
                                    {{--@if(sizeof($item->subMenu))
                                        @foreach($item->subMenu as $key => $cat)
                                            <label
                                                class="badge badge-primary ml-1">{{$cat->name}}{{$key !== (sizeof($item->subMenu) - 1) ? ',' : ''}}</label>
                                        @endforeach
                                    @endif--}}
                                </div>
                                @if($item->thumbnail)
                                    <img src="{{asset('public/storage/'.$item->thumbnail)}}">
                                @else
                                    <img src="{{asset('public/assets/images/menu-default.png')}}">
                                @endif
                                <div class="row">
                                    {{--<div class="col-md-12 my-2" title="{{$item->description}}">
                                        {{Str::limit($item->description, 100)}}
                                    </div>--}}
                                    <div class="col-md-12 text-center mt-2 ml-2">
                                        <button title="Edit" type="button" class="btn btn-primary"
                                                onclick="editBreakfast({{$item}})"><i
                                                class="i-Pen-2"></i>
                                        </button>
                                        <a title="View" href="{{route('menu-details', ['id' => $item->id])}}"
                                           class="btn btn-primary"><i
                                                class="i-Eye"></i>
                                        </a>
                                        <button title="Delete" class="btn btn-primary"
                                                onclick="deleteMenu({{$item->id}},'meals')"><i
                                                class="i-Close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade"></div>
    </div>
    <div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label>Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="price">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="price">Tax Rate (in %)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tax_rate" placeholder="Tax Rate"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label>Menu Image</label>
                                <div class="custom-files" style="position: relative;">
                                    <input type="file" class="custom-file-input" id="menu_image_input"
                                           style="position: absolute;width: 100%;padding-bottom: 60%;cursor:pointer">
                                    <img class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                         id="menu_image"
                                         style="background-color: #eeeeee;width: 100%;">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label>Menu Description</label>
                                <div class="input-group">
                                        <textarea style="resize: none" class="form-control" placeholder="Description"
                                                  id="description"></textarea>
                                </div>
                            </div>
                            {{-- <div class="col-md-12 mb-2">
                                 <label>Sub Menu</label>
                                 <div class="input-group">
                                     <select class="form-control" id="submenu" multiple>
                                         <option value="">Select</option>
                                         @foreach($category as $value)
                                             <option value="{{$value->id}}">{{$value->name}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>--}}
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary m-1" onclick="addMenu()">Add</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editMenuModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="editName" placeholder="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="text" class="form-control" id="editPrice" placeholder="$" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Tax Rate (in %)</label>
                                    <input type="text" class="form-control" id="editTaxRate" placeholder="Tax Rate"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Menu Image</label>
                                    <div class="custom-files" style="position: relative;">
                                        <input type="file" class="custom-file-input" id="edit_menu_image_input"
                                               style="position: absolute;width: 100%;padding-bottom: 60%;cursor:pointer">
                                        <img class="p-1" src="{{asset('public/assets/images/menu-default.png')}}"
                                             id="editMenuImage"
                                             style="background-color: #eeeeee;width: 100%;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="price">Description</label>
                                    <textarea type="text" class="form-control" id="editDescription" placeholder="$"
                                              required></textarea>
                                </div>
                            </div>
                            {{--<div class="col-md-12">
                                <div class="form-group">
                                    <label for="price">Sub Menu</label>
                                    <select class="form-control" id="editCategory" multiple></select>
                                </div>
                            </div>--}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="addMenu()">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="cropImageModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="" alt="crop image" id="crop_image_img">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="getCroppedImage()">Save changes</button>
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
        var crop;
        var isAction;
        var itemIndex;
        var imageSelected = false;
        var allCategories;

        $(document).ready(function () {
            $('#submenu').select2();
            $('#menu_image_input').on('change', function (e) {
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);
                    $('#cropImageModal').modal('show');
                };
                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Add';
            });
            $('#edit_menu_image_input').on('change', function (e) {
                if (crop) {
                    crop.destroy();
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    $('#crop_image_img').attr('src', event.target.result);
                    $('#editMenuModal').modal('hide');
                    $('#cropImageModal').modal('show');
                };

                reader.readAsDataURL(e.target.files[0]);
                isAction = 'Edit';
            });
            $('#crop_image_img').on('load', function () {
                var image = document.getElementById('crop_image_img');
                crop = new Cropper(image, {
                    minContainerWidth: 466,
                    minContainerHeight: 400,
                    aspectRatio: 5 / 3,
                    viewMode: 1,
                    crop(event) {
                        console.log(event.detail.x);
                        console.log(event.detail.y);
                        console.log(event.detail.width);
                        console.log(event.detail.height);
                        console.log(event.detail.rotate);
                        console.log(event.detail.scaleX);
                        console.log(event.detail.scaleY);
                    },
                });
            });
        });

        function getCroppedImage() {
            var image = crop.getCroppedCanvas().toDataURL('image/jpg', '');
            if (isAction == 'Add') {
                $('#menu_image').attr('src', image);
            } else if (isAction == 'Edit') {
                $('#editMenuImage').attr('src', image);
                $('#editMenuModal').modal('show');
            }
            $('#cropImageModal').modal('hide');
            imageSelected = true;
        }

        function addMenu() {
            let data;
            if (isAction == 'Add') {
                data = {
                    name: $('#name').val(),
                    price: $('#price').val(),
                    description: $('#description').val(),
                    submenu: $('#submenu').val(),
                    image: $('#menu_image').attr('src'),
                    category_id: 1,
                    tax_rate: $('#tax_rate').val()
                };
            } else if (isAction == 'Edit') {
                data = {
                    name: $('#editName').val(),
                    price: $('#editPrice').val(),
                    description: $('#editDescription').val(),
                    submenu: $('#editCategory').val(),
                    category_id: 1,
                    menu_id: itemIndex,
                    image: $('#editMenuImage').attr('src'),
                    tax_rate: $('#editTaxRate').val()
                };
            }

            axios.post(baseUrl + 'add-menu', data).then(function (response) {
                toastr.success(response.data.message, "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                setTimeout(function () {
                    window.location.reload();
                }, 500);
                hideAddMenu();
                $('#name').val('');
                $('#price').val('');
                $('#description').val('');
                $('#menu_image').attr('src', baseUrl + 'public/assets/images/menu-default.png');
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }

        function clickAddMenu() {
            // $('#add_new_action').hide();
            $('#addMenuModal').modal('show');
        }

        function hideAddMenu() {
            $('#add_new_menu_form').hide();
            $('#add_new_action').show();
        }

        function editBreakfast(item) {
            $('#editMenuModal').modal('show');
            $('#editName').val(item.name);
            $('#editPrice').val(item.price);
            $('#editDescription').val(item.description);
            $('#editTaxRate').val(item.tax_rate);
            $('#editMenuImage').attr('src', baseUrl + 'public/storage/' + item.image);
            $('#editCategory').select2();
            itemIndex = item.id;
            if (item.image) {
                imageSelected = true;
            }
            isAction = 'Edit';
        }

        function deleteMenu(itemId, table) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.value
                ) {
                    axios.get(baseUrl + 'delete-items/' + itemId + '/' + table).then(function (response) {
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
                        });
                    });
                }
            })
        }



    </script>
@endsection
