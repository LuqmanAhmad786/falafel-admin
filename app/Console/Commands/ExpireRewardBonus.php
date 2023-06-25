<?php

namespace App\Console\Commands;

use App\Models\Bonus;
use App\Models\BonusAppliedFor;
use App\Models\RewardCoupon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireRewardBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expireRewardBonus:run';

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
        $coupons = RewardCoupon::whereRaw("DATE_FORMAT(FROM_UNIXTIME(expiry),'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')")->pluck('coupon_id');
        RewardCoupon::whereIn('coupon_id',$coupons)->update(['status'=>4]);

        $bonuses = Bonus::where('bonus_expiry','<=',date('Y-m-d'))->pluck('bonus_id');
        BonusAppliedFor::whereIn('bonus_id',$bonuses)->update(['is_used'=>4]);
    }
}
