<?php

namespace App\Console\Commands;

use App\Models\User\UserMembership;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:expire-membership';

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
        $yesterday = Carbon::today()->subDays(1)->format('Y-m-d');
        $this->info($yesterday);
        $users = UserMembership::where('membership_id', '!=', 1)
            ->where('membership_expiry', $yesterday)
            ->get();
        if(count($users) > 0){
            foreach ($users AS $user){
                $user->membership_id = 1;
                $user->membership_expiry = Carbon::today()->addYears(25)->format('Y-m-d');
                $user->save();
            }
        }
    }
}
