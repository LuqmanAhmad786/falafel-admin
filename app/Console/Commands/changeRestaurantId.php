<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Item;
use App\Models\ModifierGroup;
use Illuminate\Console\Command;

class changeRestaurantId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'res:change-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update restaurant IDs';

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
        $this->updateCategoryTable(10,9);
        $this->updateCategoryTable(11,10);
        $this->updateCategoryTable(12,11);
        $this->updateCategoryTable(13,12);
        $this->updateCategoryTable(14,13);
        $this->updateCategoryTable(15,14);
        $this->updateCategoryTable(16,15);
        $this->updateCategoryTable(17,16);
        $this->updateCategoryTable(20,17);
        $this->updateCategoryTable(21,18);
        $this->updateCategoryTable(22,19);
        $this->updateCategoryTable(23,20);
        $this->updateCategoryTable(24,21);
        $this->updateCategoryTable(25,22);
        $this->updateCategoryTable(26,23);
    }

    public function updateCategoryTable($from, $to){
        Category::where('restaurant_id',$from)->update(['restaurant_id'=>$to]);
        Item::where('restaurant_id',$from)->update(['restaurant_id'=>$to]);
        ModifierGroup::where('restaurant_id',$from)->update(['restaurant_id'=>$to]);
    }

    public function updateItemsTable($from, $to){
        Item::where('restaurant_id',$from)->update(['restaurant_id'=>$to]);
    }

    public function updateModifiersTable($from, $to){
        ModifierGroup::where('restaurant_id',$from)->update(['restaurant_id'=>$to]);
    }
}
