<?php

namespace App\Console\Commands;

use App\Models\User\Membership;
use App\Models\User\UserMembership;
use App\Models\User\UserRewardItems;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class updateUserMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:membership-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto update user membership based on points';

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
        $users = DB::table('users')->where('is_account_deleted',0)->get();

        foreach ($users AS $user){
            $this->validateAndUpdateMembership($user);
        }
    }

    public function validateAndUpdateMembership($user){

        $membershipPoints = $user->membership_points;

        // CURRENT MEMBERSHIP
        $userMembershipPlan = UserMembership::where('user_id',$user->id)->first();

        if(!$userMembershipPlan){
            return true;
        }
        $userMembershipPlanId = $userMembershipPlan->membership_id;

        $this->info($userMembershipPlanId);
        $basicMembership = Membership::where('membership_title', '=', 'Basic')->first();
        $silverMembership = Membership::where('membership_title', '=', 'Silver')->first();
        $goldMembership = Membership::where('membership_title', '=', 'Gold')->first();

        if(($userMembershipPlanId == $silverMembership->id || $userMembershipPlanId == $basicMembership->id) && $membershipPoints >= $goldMembership->membership_points_required){
            $userMembershipPlan->membership_id = $goldMembership->id;
            $userMembershipPlan->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
            $userMembershipPlan->save();

            $user->membership_points = $user->membership_points - $goldMembership->membership_points_required;
            $user->save();
            UserRewardItems::where('user_id',$user->id)->delete();
        }elseif($userMembershipPlanId == $basicMembership->id && $membershipPoints >= $silverMembership->membership_points_required){
            $userMembershipPlan->membership_id = $silverMembership->id;
            $userMembershipPlan->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
            $userMembershipPlan->save();

            $user->membership_points = $user->membership_points - $silverMembership->membership_points_required;
            $user->save();
            UserRewardItems::where('user_id',$user->id)->delete();
        }

        return true;
    }
}
