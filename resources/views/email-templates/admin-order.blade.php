<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900&amp;display=swap"
          rel="stylesheet">
    <title>FFK</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        body{
            font-family: 'Montserrat', sans-serif !important;
            text-transform: capitalize;
        }
        h1, h2, h3, h4, h5, h6, p, a, button{
            font-family: 'Montserrat', sans-serif !important;
        }
        .bonus{
            background: #b90006;
            padding: 0px 10px;
            color: #fff;
            border-radius: 15px;
        }
        .bg-white{
            background-color: #fffdfd !important;
        }
        .off-white{
            background-color: #f5f5f5 !important;
        }
        .text-black{
            color: #000000 !important;
        }
        .bonus{
            background: #b90006;
            padding: 0px 10px;
            color: #fff;
            border-radius: 15px;
        }

        @media (prefers-color-scheme: dark ) {

            body {
                background-color: #fffdfd !important;
            }

            h1, h2, h3, h4, h5, h6, p, a, button{
                font-family: 'Montserrat', sans-serif !important;
            }

            .off-white{
                background-color: #f5f5f5 !important;
            }

            .text-black{
                color: #000000 !important;
            }

            .bg-white{
                background-color: #fffdfd !important;
            }

            .bonus{
                background: #b90006;
                padding: 0px 10px;
                color: #fff;
                border-radius: 15px;
            }

        }
    </style>
</head>
<body style="background-color: #f2f2f2;font-family: 'Montserrat', sans-serif !important;">

<div class="container"
     style="margin: 2rem auto; max-width: 600px; text-align: center; font-family: Montserrat;">
    <div style="background-color: #b90006;padding: 15px;"><img src="https://fcadmin77.falafelcorner.us/public/images/logo-falafel.png{{--{{asset('public/images/logo-falafel.png')}}--}}" style="width: 100%"/> </div>
    <div style="padding: 10px 1rem;">
        <h1 style="margin-top: 0; margin-bottom: 0; padding: 15px; font-weight: bold;color: #b90006"> NEW ORDER #{!! $order_id !!} </h1>
        <hr style="border-bottom: 3px solid #b90006; width: 50px; margin: 0 auto">
        <p class="text-black" style="text-align: left; font-size: 20px; font-weight: 600;margin-top:2rem">Customer Information</p>
        <div style="background-color: #ffffff; border-radius: 10px 10px 0 0; padding: 10px">
            <p style="font-size: 18px; margin: 10px; color: #666666;font-weight: 500">{!! $user_name  !!} </p>
            <p style="font-size: 18px; margin: 10px; color: #666666;font-weight: 500">{!! $user_email  !!} </p>
            <p style="font-size: 18px; margin: 10px; color: #666666;font-weight: 500">{!!  $user_number !!} </p>
        </div>
        <p style="text-align: left; font-size: 20px; font-weight: 600;">Pickup Time</p>
        <div style="background-color: #ffffff; border-radius: 10px; padding: 10px">
            <h1 style="margin: 10px;color: #b90006;font-size: 40px;">{!! $pickup_time !!}</h1>
            <p style="color: gray;font-weight: bold;">{{date('M d, Y', strtotime($order_details['pickup_date']))}}</p>
        </div>

        <p style="text-align: left; font-size: 20px; font-weight: 600;margin-top:2rem">Pickup Location</p>
        <div style="background-color: #ffffff; border-radius: 10px 10px 0 0; padding: 10px">
            @if($restaurant_id == 1)
                <h1 style="margin: 10px;color: #b90006;font-size: 40px;">Falafel Corner</h1>
            @endif
            @if($restaurant_id == 2)
                <h1 style="margin: 10px;color: #b90006;font-size: 40px;">Falafel Corner</h1>
            @endif
            <p style="font-size: 18px; margin: 10px; color: #666666;font-weight: 500">{!! $restaurant  !!} </p>
            <p style="font-size: 18px; margin: 10px; color: #666666;font-weight: 500">
                Contact: {!! $restaurant_contact  !!} </p>
        </div>
        <p style="text-align: left; font-size: 20px; font-weight: 600;margin-top:2rem">Order Summary</p>
        <div style="background-color: #ffffff; padding: 10px;">
            @foreach($order_details['orderDetails'] as $item)
                <div style="display: flex; width: 100%;">
                    <div style="max-width: 70%; width: 100%; text-align: left">
                        <p style="font-size: 18px; margin: 10px; color: #b90006;font-weight: 500">{!! $item['item_count']  !!}  {!! $item['item_name']  !!}</p>
                        <p style="margin: 10px;">
                            @foreach($item['order_item'] as $subItem)
                                {!! $subItem['item_name']  !!}
                                @if($subItem['item_price'] > 0)
                                <span>(${!! $item['item_count'] * $subItem['item_price']  !!})</span>
                                    @endif,
                            @endforeach
                        </p>
                    </div>
                    <div style="max-width: 30%; width: 100%;  text-align: right">
                        <h5 style="font-size: 18px; margin: 10px; color: #666666;font-weight: 600">
                            ${!! $item['item_count'] * $item['item_price']  !!}</h5>
                    </div>
                </div>
                <hr style="border: 1px solid #f2f2f2;">
            @endforeach
            <div style="display: flex; width: 100%;">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #000000;font-weight: 500">Order total</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #000000;font-weight: 600">
                        ${!! $order_details['order_total'] !!}</h5>
                </div>
            </div>
                @if($order_details['discount_amount'] > 0)
                    <div style="display: flex; width: 100%;">
                        <div style="max-width: 70%; width: 100%; text-align: left">
                            <p style="font-size: 18px; margin: 10px; color: #000000;font-weight: 500">Discount
                                @if($order_details['coupon_id'])
                                    <span class="bonus">Free entry</span>
                                @endif
                                @if($order_details['bonus_id'])
                                    <span class="bonus">Bonus</span>
                                @endif
                            </p>
                        </div>
                        <div style="max-width: 30%; width: 100%;  text-align: right">
                            <h5 style="font-size: 18px; margin: 10px; color: #000000;font-weight: 600">-${!! $order_details['discount_amount'] !!}</h5>
                        </div>
                    </div>
                @endif
            <div style="display: flex; width: 100%;">
                <div style="max-width: 70%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #000000;font-weight: 500">Tax</p>
                </div>
                <div style="max-width: 30%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #000000;font-weight: 600">${!! $order_details['total_tax'] !!}</h5>
                </div>
            </div>
            <hr style="border: 1px dashed #cccccc; margin: 1rem 0">
            <div style="display: flex; width: 100%; margin-bottom: 2rem">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 22px; margin: 10px; color: #b90006;font-weight: 600">Total</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 24px; margin: 10px; color: #000000;font-weight: 600">
                        ${!! $order_details['total_amount'] !!}</h5>
                </div>
            </div>
        </div>
        <div class="footer"
             style="clear: both;padding: 20px 2rem 5px 2rem; margin-top: 0rem; background-color: #b90006; color: #ffffff;">
            <div style="width: 100%; display:block;text-align: center;">
                <h3 style="margin-top: 0;font-weight: bold;font-size: 12px;">Our Location</h3>
                <div style="margin-top: 0; margin-bottom: 7px;">
                    <a href="{{ url('/') }}"><img src="https://fcadmin77.falafelcorner.us/public/images/locations.png{{--{{asset('public/images/locations.png')}}--}}"
                                                  style="width: 70%;height: auto;"></a>
                </div>
            </div>
            <div style="width: 100%; display:block; margin-top: 1rem;">
                <h3 style="margin-top: 0; font-weight: normal">Connect With Us</h3>
                <a href="https://www.facebook.com/"><img alt="" src="https://fcadmin77.falafelcorner.us/public/images/fbe1.png{{--{{asset('public/images/fbe1.png')}}--}}" style="max-width: 25px; height: auto; margin-right: 1rem"></a>
                <a href="https://www.instagram.com/"><img alt="" src="https://fcadmin77.falafelcorner.us/public/images/fbe.png{{--{{asset('public/images/fbe.png')}}--}}" style="max-width: 25px; height: auto"></a>
            </div>
            <p style="font-size: 16px; margin-top: 1rem; margin-bottom: 0">
                <a href="https://falafelcorner.us/contact-us" style="color: #ffffff;
    text-decoration: none;">Contact Us</a>
                <span style="padding: 0 10px"> | </span> <a style="color: #ffffff;
    text-decoration: none;" href="https://falafelcorner.us/">Legal</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
