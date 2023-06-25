<?php

use Illuminate\Database\Seeder;
use App\Models\User\Membership;

class CreateMemberships extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('memberships')->truncate();

        $membership = new Membership();
        $membership->membership_title = 'Basic';
        $membership->membership_points_required = 0;
        $membership->save();

        $membership = new Membership();
        $membership->membership_title = 'Silver';
        $membership->membership_points_required = 600;
        $membership->save();

        $membership = new Membership();
        $membership->membership_title = 'Gold';
        $membership->membership_points_required = 1000;
        $membership->save();
    }
}
