<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Restaurant\RestaurantMenuTiming;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateMenuTiming extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:menu-timing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to update menu timing for the day';

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
        // RES 1 BF TIME
        $menus = Menu::all();
        foreach($menus AS $menu){
            $this->updateTiming($menu->menu_id);
        }
        return true;
    }

    public function updateTiming($menuId){
        $day = Carbon::today()->getTranslatedDayName();
        $dayTiming = RestaurantMenuTiming::where('restaurant_menu_id',$menuId)
            ->where('day',strtolower($day))
            ->first();
        if($dayTiming){
            Menu::where('menu_id', $menuId)->update([
                'from' => $dayTiming->from_1,
                'to' => $dayTiming->to_1,
                'from_2' => $dayTiming->from_2,
                'to_2' => $dayTiming->to_2,
            ]);
        }
    }
}
