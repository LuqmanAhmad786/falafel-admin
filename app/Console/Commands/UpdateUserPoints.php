<?php

namespace App\Console\Commands;

use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\ManageNotifications;
use App\Models\RewardCoupon;
use App\Models\SubscriptionPreference;
use App\Models\UserRewards;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUserPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:refresh_points';

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
        $users = User::leftJoin('user_rewards','user_rewards.user_id','users.id')
            ->select(DB::raw('SUM(total_rewards) as points'),'users.id','users.first_name','users.email')
            ->having('points','>=','2000')
            ->groupBy('users.id')
            ->get();
        foreach ($users AS $user){
            if($user['points'] && $user['points'] >= 2000){
                UserRewards::create([
                    'order_id' => 0,
                    'user_id' => $user['id'],
                    'total_rewards' => -2000,
                    'month' => strtotime(date('Y-m', time()) . '-1'),
                    'type' => 2
                ]);

                /*coupon creation*/
                RewardCoupon::create([
                    'user_id' => $user['id'],
                    'expiry' => date(strtotime("+" . 6 . "Months")),
                    'coupon_type' => 1,
                ]);

                /*Email Notification*/
                $template = view('email-templates.order-reward-points',
                    [
                        'name' => $user['first_name'],
                    ])->render();

                $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $user['id'])->first();
                if ($subscriptionPreference['email_subscription']) {
                    sendEmail($template, $user['email'], 'Congratulations! Get your FREE Falafel Corner');
                }

                /*Push Notification*/
                $devicePreference = DevicePreference::where('user_id', '=', $user['id'])->first();
                $tokens = FirebaseToken::where('user_id', '=', $user['id'])->get();
                $receivers = FirebaseToken::where('user_id', '=', $user['id'])->get();
                $notificationInfo = ManageNotifications::where('type_id', '=', 6)->first();

                $notification = array(
                    'message' => $notificationInfo['message_text'],
                    'title' => 'You got a free entree!',
                    'body' => $notificationInfo['message_text'],
                    'type' => $notificationInfo['type_id'],
                    'data' => 0,
                    'sound' => 'default'
                );
                if ($devicePreference['push_notification']) {
                    foreach ($tokens as $tk) {
                        app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'],$notification['body'],[$tk['token']],['notification_type'=>6]);
                    }
                }
            }
        }
    }
}
