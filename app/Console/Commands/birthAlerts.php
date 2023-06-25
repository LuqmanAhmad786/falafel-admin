<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\ManageNotifications;
use App\Models\RewardCoupon;
use App\Models\SubscriptionPreference;
use App\Models\UserRewards;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class birthAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $allUsers = User::select(
            'id',
            'first_name',
            'last_name',
            'email',
            'date_of_birth',
            DB::raw('DATE_FORMAT(FROM_UNIXTIME(date_of_birth), "%d-%m") as date_of_birth'))
            ->get();
        foreach ($allUsers as $item) {
            if ($item['date_of_birth'] == date('d-m')) {
                $isAlready = RewardCoupon::where('user_id', '=', $item['id'])
                    ->where('coupon_type', '=', 2)
                    ->get();

                if (!count($isAlready)) {
                    RewardCoupon::create([
                        'user_id' => $item['id'],
                        'expiry' => $item['id'],
                        'coupon_type' => 2,
                    ]);
                }

                UserRewards::create([
                    'order_id'=>0,
                    'user_id' => $item['id'],
                    'total_rewards'=>0,
                    'month'=>strtotime(date('Y-m', time()) . '-1'),
                    'type'=>3
                ]);

                $token = FirebaseToken::where('user_id', '=', $item['id'])->get();
                $receivers = FirebaseToken::where('user_id', '=', $item['id'])->get();

                /*Email Notification*/
                $template = view('email-templates.birthday-reward',
                    ['name' => $item['first_name']]
                )->render();

                $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $item['id'])->first();
                if ($subscriptionPreference['email_subscription']) {
                    sendEmail($template, $item['email'], 'A Birthday Surprise from Falafel Corner');
                }


                /*Push Notification*/
                $devicePreference = DevicePreference::where('user_id', '=', $item['id'])->first();
                $notificationInfo = ManageNotifications::where('type_id', '=', 8)->first();
                $notification = array(
                    'message' => $notificationInfo['message_text'],
                    'title' => 'Birthday',
                    'body' => $notificationInfo['message_text'],
                    'type' => $notificationInfo['type_id'],
                    'data' => (object)array(),
                    'sound' => 'default'
                );
                if ($devicePreference['push_notification']) {
                    foreach ($token as $tk) {
                        app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'],$notification['body'],[$tk['token']],['notification'=>8]);
                    }
                }
                app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);
            }
        }
    }
}
