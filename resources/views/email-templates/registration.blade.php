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
    <style type="text/css">
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

        }
    </style>
</head>
<body style="font-family: 'Montserrat', sans-serif !important;">

<div class="container"
     style="margin: 0 auto; max-width: 600px; text-align: center; font-family: Montserrat;">

    <div style="background-color: #b90006;padding: 15px;"><img src="https://fcadmin77.falafelcorner.us/public/images/logo-falafel.png{{--{{asset('public/images/logo-falafel.png')}}--}}" style="width: 100%"/> </div>
    <div class="bg-white" style="min-height: 43rem;">
        <h2 style="margin: 0;padding: 15px;font-weight: 600;color: #b90006;padding-bottom: 0;">Welcome, {!! $name !!}! </h2>
        <hr style="border: 2px solid #b90006; width: 50px; margin-bottom: 0 auto">
        <p class="text-black" style="font-weight: normal">Your account is all set. </p>
        <p class="text-black">Now you can skip the lines and earn <br/>FREE Falafel Corner!</p>
        <div class="off-white" style="margin-top: 2rem; padding: 20px; margin-bottom: 0rem;">
            <h2 class="text-black" style="margin: 0;font-weight: 600;margin-bottom: 1.5rem;">Home Cooked Every Day</h2>
            <div style="width: 100%;">
                <div style="width: 50%; float: left;margin-top: 1.5rem;">
                    <img alt="" style="width: 94%;" src="https://fcadmin77.falafelcorner.us/public/images/em1.jpg{{--{{asset('public/images/4pc-chicken.jpg')}}--}}">
                </div>
                <div style="width: 50%; float: left;margin-top: 1.5rem;">
                    <img alt="" style="width: 94%;" src="https://fcadmin77.falafelcorner.us/public/images/em2.jpg{{--{{asset('public/images/dmac-chz.jpg')}}--}}">
                </div>
                <div style="width: 50%; float: left;margin-top: 1.5rem;">
                    <img alt="" style="width: 94%;" src="https://fcadmin77.falafelcorner.us/public/images/em3.jpg{{--{{asset('public/images/1588154792.png')}}--}}">
                </div>
                <div style="width: 50%; float: left;margin-top: 1.5rem;">
                    <img alt="" style="width: 94%;" src="https://fcadmin77.falafelcorner.us/public/images/em4.jpg{{--{{asset('public/images/1588154734.png')}}--}}">
                </div>
                <div style="width: 50%; float: left;margin-top: 1.5rem;margin-bottom: 1.5rem;">
                    <img alt="" style="width: 94%;" src="https://fcadmin77.falafelcorner.us/public/images/em5.jpg{{--{{asset('public/images/1588154792.png')}}--}}">
                </div>
                <div style="width: 50%; float: left;margin-top: 1.5rem;margin-bottom: 1.5rem;">
                    <img alt="" style="width: 94%;" src="https://fcadmin77.falafelcorner.us/public/images/em6.jpg{{--{{asset('public/images/1588154734.png')}}--}}">
                </div>
            </div>
            <div style="width: 100%;clear: both;padding: 5px 0px 2rem 0;">
                <a href="{{ url('/') }}/menu"
                   style="padding: 8px 40px; background-color: #b90006; color: #ffffff; font-size: 18px; font-weight: 600; border: none; margin: 2rem auto; text-decoration: none">
                    Order Now
                </a>
            </div>
            <div style="width: 100%;clear: both;background: #fff;">
                <div style="width: 100%; display: inline-flex; margin-top: 1rem">
                    <div style="width: 25%; display:block">
                        <img  alt="" style="width: 70%"
                              src="https://fcadmin77.falafelcorner.us/public/images/11.png{{--{{asset('public/images/11.png')}}--}}">
                    </div>
                    <div style="width: 70%; display:block; text-align: left;">
                        <p style="color: #b90006;font-weight: bold;">Order and earn 10 points for each $1 spent.</p>
                    </div>
                </div>
                <hr style="border: 1px solid #eee;">
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 25%; display:block">
                        <img alt="" style="width: 70%"
                             src="https://fcadmin77.falafelcorner.us/public/images/award(2).png{{--{{asset('public/images/award(2).png')}}--}}">
                    </div>
                    <div style="width: 70%; display:block; text-align: left">
                        <p style="color: #b90006;font-weight: bold;">Earn 2,000 points and get FREE Falafel Corner.</p>
                    </div>
                </div>
                <hr style="border: 1px solid #eee;">
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 25%; display:block">
                        <img alt="" style="width: 70%"
                             src="https://fcadmin77.falafelcorner.us/public/images/award.png{{--{{asset('public/images/award.png')}}--}}">
                    </div>
                    <div style="width: 70%; display:block; text-align: left">
                        <p style="color: #b90006;font-weight: bold;">Boost your points with special bonuses, and get a free birthday surprises.</p>
                    </div>
                </div>
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <a href="{{ url('/') }}"
                       style="padding: 8px 30px; background-color: #b90006; color: #ffffff; font-size: 18px; font-weight: 600; border: none; margin: 2rem auto; text-decoration: none">
                        Find the Nearest Falafel's
                    </a>
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
            <a href="https://falafelcorner.us/contact-us" style="color: #ffffff;
    text-decoration: none;">Contact Us</a>
            <span style="padding: 0 10px"> | </span> <a style="color: #ffffff;
    text-decoration: none;" href="https://falafelcorner.us/">Legal</a>
        </p>
    </div>
</div>
</body>
</html>
