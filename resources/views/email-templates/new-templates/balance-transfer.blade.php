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
        <h1 style="margin: 0;padding: 15px;font-weight: 600;color: #b90006;padding-bottom: 0;">Confirmation of Falafel Corner Card Balance Transfer</h1>
        <hr style="border: 2px solid #b90006; width: 50px; margin-bottom: 0 auto">
        <p class="text-black" style="font-weight: normal;text-align: left;">This email is confirmation that your transfer of {{$amount}} USD from Card
            <b>{{$card_1}}</b> to Card <b>{{$card_2}}</b> has been completed.</p>
        <p class="text-black" style="text-align: left;">If you did not authorize this transfer, please reset your password by going to Password Reset and reply to this email saying "fraud".</p>
        <p class="text-black" style="text-align: left;">Warm regards,<br/>Falafel Corner</p>
        <p class="text-black" style="text-align: left;"></p>
        <div style="background-color: #fff;margin-top: 2rem;padding: 10px;margin-bottom: 0rem;">
            <div style="width: 100%;clear: both;background: #fff;">
                <div style="width: 100%;display: inline-block;margin-top: 0rem;">
                    <div style="display:block; text-align: center">
                        <p style="color: #b90006;font-weight: bold;">Questions? Please visit our <a href="https://fcorner.com/contact" target="_blank" style="color: #b90006;">Customer Support</a> page.</p>
                    </div>
                </div>
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
