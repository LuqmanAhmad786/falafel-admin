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
        <h1 style="margin: 0; padding: 15px; font-weight: bold;color: #b90006"> You have {{$total_reward_points}} Points</h1>
        <h1 style="margin: 0; padding: 15px; font-weight: bold;color: #b90006"> Keep earning.</h1>
        <hr style="border-bottom: 3px solid #b90006; width: 50px; margin: 0 auto">
        <p class="text-black" style="font-weight: normal;text-align: center;">Points Balance as of {{date('M d, Y')}} is {{$total_reward_points}}</p>
        <p class="text-black" style="font-weight: normal;text-align: left;">Way to go. You are close to getting delicious
            Rewards. At just 25 Points, you can redeem for a
            free dink.
            And at just 50 Points, you can redeem
            for a Drink and fries or Baklava.
        </p>
        <div style="background-color: #fff;margin-top: 2rem; padding: 20px; margin-bottom: 0rem;">
            <div style="width: 100%;clear: both;background: #fff;">
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 25%; display:block">
                        <img alt="" style="width: 70%"
                             src="https://fcadmin77.falafelcorner.us/public/images/award.png">
                    </div>
                    <div style="width: 70%; display:block; text-align: left">
                        <p style="color: #b90006;font-weight: bold;">Here are some ways you can earn more Points:
                            Earn Points at the register
                            or when you order ahead in the app.
                        </p>
                    </div>
                </div>
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 100%; display:block; text-align: center">
                        <p style="color: #b90006;font-weight: bold;">Earn 1 Point per $1 when you pay with cash,
                            credit or debit. Or when you link a payment
                            in the app.
                        </p>
                    </div>
                </div>
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 100%; display:block; text-align: center">
                        <p style="color: #b90006;font-weight: bold;">Earn Points faster—get 2 Points per $1 when you
                            pay with your Falafel Corner Card in the app. Add
                            funds to the Falafel Corner Card in the app using
                            any payment option.
                        </p>
                    </div>
                </div>
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 100%; display:block; text-align: center">
                        <p style="color: #b90006;font-weight: bold;">Learn more in the app.
                        </p>
                    </div>
                </div>
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 100%; display:block; text-align: center">
                        <p style="color: #b90006;font-weight: bold;">Order & earn Points At participating stores. Some restrictions apply. For details visit fcorner.com/rewards/terms.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div style="background-color: #fff;margin-top: 2rem; padding: 20px; margin-bottom: 0rem;">
            <div style="width: 100%;clear: both;background: #fff;">
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 25%; display:block">
                        <img alt="" style="width: 70%"
                             src="https://fcadmin77.falafelcorner.us/public/images/11.png">
                    </div>
                    <div style="width: 70%; display:block; text-align: left">
                        <p style="color: #b90006;font-weight: bold;">Redeeming Rewards
                        </p>
                    </div>
                </div>
                <div style="width: 100%; display: inline-flex; margin-top: 1rem;">
                    <div style="width: 100%; display:block; text-align: center">
                        <p style="color: #b90006;font-weight: bold;">Rewards may be redeemed at participating stores
                            beverages or multi-serve items. Not all stores honor tiered Rewards.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-black" style="font-weight: normal;text-align: left;font-size: 12px;">We’re contacting you because you have opted in to receive news,
            promotions, information and offers from Falafel Corner. Your address is listed as
            dromerhameed@hotmail.com. Unsubscribe.
            This email is sent from an account we use for sending messages only. If you
            want to contact us, reply to this email.
            or Contact us via this web form.</p>
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
