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
        body{
            font-family: 'Montserrat', sans-serif !important;
            background-color: #fffdfd;
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
<body style="font-family: 'Montserrat', sans-serif !important;">

<div class="container"
     style="background: #f2f2f2;margin: 2rem auto; max-width: 600px; text-align: center; font-family: Montserrat;">
    <div style="padding: 15px;"><img src="{{asset('public/images/logo.93799a1f.png')}}" style="width: 75px"/> </div>
    <div style="padding: 10px 1rem;">
        <h1 style="margin: 0; font-weight: bold;color: #b90006"> Hi {!! $name !!},</h1>
        <h1 style="margin: 0; padding: 15px; font-weight: bold;color: #b90006"> You received a Gift card from {!! $sender !!}.</h1>
        <hr style="border-bottom: 3px solid #b90006; width: 50px; margin: 0 auto">
        <p class="text-black" style="text-align: left; font-size: 20px; font-weight: 600;">Gift card info</p>
        <div class="bg-white" style="border-radius: 10px; padding: 10px">
            <p style="color: gray;font-weight: bold;">Card Number: {{$gift_card_number}}</p>
            <p style="color: gray;font-weight: bold;">Card Code: {{$gift_card_code}}</p>
        </div>
    </div>
    <div style="width: 100%;padding-bottom: 2rem;margin-top: 1.5rem;">
        <a href="{{url('/')}}"
           style="padding: 8px 30px; background-color: #b90006; color: #ffffff; font-size: 18px; font-weight: 600; border: none; margin: 1rem auto 2rem; text-decoration: none">
            Redeem Now
        </a>
    </div>
    <div class="footer"
         style="clear: both;padding: 20px 2rem 5px 2rem; margin-top: 0rem; background-color: #b90006; color: #ffffff;">
        <div style="width: 100%; display:block;text-align: center;">
            <h3 style="margin-top: 0;font-weight: bold;font-size: 12px;">Our Location</h3>
            <div style="margin-top: 0; margin-bottom: 7px;">
                <a href="#"><img src="https://fcadmin77.falafelcorner.us/public/images/locations.png"
                                 style="width: 70%;height: auto;"></a>
            </div>
        </div>
        <div style="width: 100%; display:block; margin-top: 1rem;">
            <h3 style="margin-top: 0; font-weight: normal">Connect With Us</h3>
            <a href="https://www.facebook.com/"><img alt="" src="https://fcadmin77.falafelcorner.us/public/images/fbe1.png" style="max-width: 25px; height: auto; margin-right: 1rem"></a>
            <a href="https://www.instagram.com/"><img alt="" src="https://fcadmin77.falafelcorner.us/public/images/fbe.png" style="max-width: 25px; height: auto"></a>
        </div>
        <p style="font-size: 16px; margin-top: 1rem; margin-bottom: 0">
            <a href="https://falafelcorner.us/contact-us" style="color: #ffffff;
    text-decoration: none;">Contact Us</a>
            <span style="padding: 0 10px"> | </span> <a style="color: #ffffff;
    text-decoration: none;" href="https://falafelcorner.us/">Legal</a>
        </p>
    </div>
</div>
</body>
</html>
