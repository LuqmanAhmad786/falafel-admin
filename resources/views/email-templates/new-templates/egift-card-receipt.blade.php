<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900&amp;display=swap"
          rel="stylesheet">
    <title>Falafel Corner</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        body {
            font-family: Montserrat !important;
        }
        body{
            font-family: 'Montserrat', sans-serif !important;
            text-transform: capitalize;
        }
        h1, h2, h3, h4, h5, h6, p, a, button{
            font-family: 'Montserrat', sans-serif !important;
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
<body style="background-color: #fff;font-family: 'Montserrat', sans-serif !important;">

<div class="container"
     style="background-color: #f2f2f2;margin: 2rem auto; max-width: 600px; text-align: center; font-family: Montserrat;">

    <div style="padding: 15px;"><img src="{{asset('public/images/logo.93799a1f.png')}}" style="width: 75px"/> </div>
    <div style="background-color: #f2f2f2;padding: 10px 1rem; min-height: 22rem;">
        <h1 style="margin: 0; font-weight: bold;color: #b90006"> Hi {!! $name !!},</h1>
        <h1 style="margin: 0;padding: 15px;font-weight: 600;color: #b90006;padding-bottom: 0;">Your Falafel Corner eGift Card receipt is enclosed</h1>
        <hr style="border: 2px solid #b90006; width: 50px; margin-bottom: 0 auto">
        <p class="text-black" style="font-weight: normal;text-align: center;">Thank you for brightening someone's day with a Falafel Corner eGift Card.
            We're in the process of delivering your eGift Card and will let you know once it has been opened.</p>
        <div class="bg-white" style=" padding: 10px;">
            <div style="display: flex;width: 100%;">
                @if($gift_card_image)
                    <img src="{{asset('public/storage/'.$gift_card_image)}}" class="img-fluid" style="width: 100%;">
                @endif
            </div>
            <div style="display: flex; width: 100%;">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #b90006;font-weight: 500">Order Date</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #666666;font-weight: 600">
                        {{date('M d, Y')}}</h5>
                </div>
            </div>
            <div style="display: flex; width: 100%;">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #b90006;font-weight: 500">Order Number</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #666666;font-weight: 600">{{$gift_card_number}}</h5>
                </div>
            </div>
            <div style="display: flex; width: 100%;">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #b90006;font-weight: 500">Recipient Email</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #666666;font-weight: 600">{{$recipient_email}}</h5>
                </div>
            </div>
            <div style="display: flex; width: 100%;">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #b90006;font-weight: 500">Amount</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #666666;font-weight: 600">{{$amount}}</h5>
                </div>
            </div>
            <hr style="border: 1px solid #f2f2f2;">
            <div style="display: flex; width: 100%;">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #000000;font-weight: 500">Sub total</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #000000;font-weight: 600">
                        ${{$amount}}</h5>
                </div>
            </div>
            <div style="display: flex; width: 100%;">
                <div style="max-width: 70%; width: 100%; text-align: left">
                    <p style="font-size: 18px; margin: 10px; color: #000000;font-weight: 500">Tax</p>
                </div>
                <div style="max-width: 30%; width: 100%;  text-align: right">
                    <h5 style="font-size: 18px; margin: 10px; color: #000000;font-weight: 600">${!! $tax !!}</h5>
                </div>
            </div>
            <hr style="border: 1px dashed #cccccc; margin: 1rem 0">
            <div style="display: flex; width: 100%; margin-bottom: 2rem">
                <div style="max-width: 50%; width: 100%; text-align: left">
                    <p style="font-size: 22px; margin: 10px; color: #b90006;font-weight: 600">Total</p>
                </div>
                <div style="max-width: 50%; width: 100%;  text-align: right">
                    <h5 style="font-size: 24px; margin: 10px; color: #000000;font-weight: 600">
                        ${!! $amount !!}</h5>
                </div>
            </div>
        </div>
        <p class="text-black" style="font-weight: normal;text-align: left;">
            Best,<br/>
            The Falafel Corner Card Team
        </p>
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
            <a href="https://fcorner.com/privacy-policy" style="color: #ffffff;
    text-decoration: none;">Privacy Statement</a>
            <span style="padding: 0 10px"> | </span> <a style="color: #ffffff;
    text-decoration: none;" href="https://fcorner.com/falafelcorner-terms-of-use">Terms Of Use</a>
        </p>
        <p style="font-size: 12px; margin-top: 1rem; margin-bottom: 0;text-align: center">
            ©{{date('Y',time())}} Falafel Corner Mediterranean Grill. All rights reserved.
        </p>
    </div>
</div>
</body>
</html>
