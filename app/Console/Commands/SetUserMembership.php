<?php

namespace App\Console\Commands;

use App\Models\User\Membership;
use App\Models\User\UserMembership;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class SetUserMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-membership';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init set membership of existing users';

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
//        $users = User::all();
//
//        foreach ($users AS $user){
//            $membership = new UserMembership();
//            $membership->user_id = $user->id;
//            $membership->membership_id = 1;
//            $membership->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
//            $membership->save();
//        }

        $user = User::find(1);
        $membershipPoints = $user->membership_points;

        // CURRENT MEMBERSHIP
        $userMembershipPlan = UserMembership::where('user_id',1)->first();

        $userMembershipPlanId = $userMembershipPlan->membership_id;

        $basicMembership = Membership::where('membership_title', '=', 'Basic')->first();
        $silverMembership = Membership::where('membership_title', '=', 'Silver')->first();
        $goldMembership = Membership::where('membership_title', '=', 'Gold')->first();

        if(($userMembershipPlanId == $silverMembership->id || $userMembershipPlanId == $basicMembership->id) && $membershipPoints >= $goldMembership->membership_points_required){
            $userMembershipPlan->membership_id = $goldMembership->id;
            $userMembershipPlan->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
            $userMembershipPlan->save();

            $user->membership_points = $user->membership_points - $goldMembership->membership_points_required;
            $user->save();
        }elseif($userMembershipPlanId == $basicMembership->id && $membershipPoints >= $silverMembership->membership_points_required){
            $userMembershipPlan->membership_id = $silverMembership->id;
            $userMembershipPlan->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
            $userMembershipPlan->save();

            $user->membership_points = $user->membership_points - $silverMembership->membership_points_required;
            $user->save();
        }
    }
}
