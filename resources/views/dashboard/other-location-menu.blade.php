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

        .category-tab {
            background: #EDF3F9;
            padding: 10px;
        }

        .item-bottom {
            border-bottom: 1px solid #d1d1;
        }
    </style>
@endsection

@section('main-content')
    <ul class="breadcrumb">
        <li><a href="{{route('side-menu')}}">Item(s)</a></li>
        <li>Other Location Menu</li>
    </ul>
    <div class="row pl-3 pr-3 pb-3">
        @if(sizeof($menus))
            <div class="col-md-12 mt-3 card p-3">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        @foreach($menus as $key =>$type)
                            <a class="nav-item nav-link {{ $key == 0 ? 'active' : '' }}"
                               id="nav-home-tab"
                               data-toggle="tab"
                               href="#{{$type->menu_name.'_'.$type->menu_id}}"
                               role="tab"
                               aria-controls="nav-home"
                               aria-selected="true">
                                {{$type->menu_name}}
                            </a>
                        @endforeach
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    @foreach($menus as $key =>$type)
                        <div class="tab-pane fade show active"
                             id="{{$type->menu_name.'_'.$type->menu_id}}"
                             role="tabpanel"
                             aria-labelledby="nav-home-tab">
                            @if(sizeof($type['categories']))
                                @foreach($type['categories'] as $category)
                                    <div class="row">
                                        <div class="col-md-12 category-tab">
                                            {{$category->category_name}}
                                        </div>
                                        @foreach($category['items'] as $k => $item)
                                            <div
                                                class="col-md-12 mt-2 {{ sizeof($category['items']) == $k+1 ? 'mb-2' : '' }}">
                                                <div class="row">
                                                    <div class="col-md-2 p-0">
                                                        <img src="{{asset('public/storage/'.$item['item_image'])}}">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <h5>{{$item['item_name']}}</h5>
                                                        <h5>Price : ${{$item['item_price']}}</h5>
                                                        <h6>{{$item['item_description']}}</h6>
                                                        <h6><a class="text-primary"
                                                               onclick="singleItemModifiers({{$item}})">View
                                                                More >></a></h6>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @else
                                <div class="col-md-12 mt-5 text-center">
                                    <img height="150" src="{{asset('public/images/not-found.png')}}">
                                    <h5 class="mt-3 not-found">Not Found</h5>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <div class="modal fade" id="singleMenuModifiers" tabindex="-1" role="dialog" aria-hidden="true"
         data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-text"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mt-2" id="item_modifiers"></div>
                        </div>
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
        let singleItemData;

        function singleItemModifiers(item) {
            $('#modifier_items').html('');
            axios.get(baseUrl + 'single-item-modifiers/' + item.item_id).then(function (response) {
                singleItemData = response.data.response;
                $('#modal-title-text').text('Details of' + ' ' + item.item_name);
                $('#singleMenuModifiers').modal('show');
                console.log(singleItemData);
                for (i = 0; i < singleItemData.length; i++) {

                    let obj = singleItemData[i];
                    $('#item_modifiers').append('' +
                        '<div class="col-md-12 category-tab">' + obj.modifier_group_name + '</div>' +
                        '  <div class="col-md-12 mt-2" id="modifier_sub_item"></div>'
                    );

                    for (j = 0; j < obj.meals.length; j++) {

                        let subObj = obj.meals[j];
                        if (subObj.item_thumbnail) {
                            subObj.item_thumbnail = baseUrl + 'public/storage/' + subObj.item_thumbnail;
                        } else {
                            subObj.item_thumbnail = baseUrl + 'public/assets/images/menu-default.png';
                        }
                        $('#item_modifiers').append('<div class="row pl-3 pr-3">\n' +
                            '                                        <div class="col-md-2 p-0">\n' +
                            '                                            <img src="' + subObj.item_thumbnail + '">\n' +
                            '                                        </div>\n' +
                            '                                        <div class="col-md-8">\n' +
                            '                                            <h5>' + subObj.item_name + '</h5>\n' +
                            '                                            <h5>Price :$' + subObj.item_price + '</h5>\n' +
                            '                                            <h6></h6>\n' +
                            '                                        </div>\n' +
                            '                                    </div>')
                    }

                }
            }).catch(function (error) {
            });
        }
    </script>
@endsection
