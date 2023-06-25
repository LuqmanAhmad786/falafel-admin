<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Models\RestaurantOfflineDate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RestaurantOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restaurant:offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to check dates for restaurant offline';

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
        $today = Carbon::today()->format('Y-m-d');
        $restaurants = Restaurant::get();
        foreach ($restaurants AS $restaurant){
            $futureDates = RestaurantOfflineDate::where('restaurant_id',$restaurant->id)
                ->orderBy('start_date','asc')->get();
            foreach ($futureDates AS $futureDate){
                $startDate = Carbon::createFromFormat('Y-m-d', $futureDate->start_date);
                $endDate = Carbon::createFromFormat('Y-m-d', $futureDate->end_date);
                $isBetween = Carbon::now()->between($startDate, $endDate);
                if($isBetween){
                    Restaurant::where('id',$restaurant->id)
                        ->update([
                            'is_opened' => 0,
                            'offline_message' => $futureDate->offline_message
                        ]);
                    break;
                }else{
                    Restaurant::where('id',$restaurant->id)
                        ->update([
                            'is_opened' => 1
                        ]);
                }
            }
        }
    }
}
