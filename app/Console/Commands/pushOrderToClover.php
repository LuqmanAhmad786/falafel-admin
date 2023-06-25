<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\CloverController;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class pushOrderToClover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clover:order:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push order to clover';

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
        $orders = Order::where('payment_status',1)
            ->whereNull('clover_id')
            ->orderby('order_id','desc')
            ->get();

        if(count($orders)){
            foreach ($orders AS $order){
                $this->pushToClover($order);
            }
        }else{
            $this->info('no order');
        }
    }

    public function pushToClover($order){
        $restaurant = Restaurant::find($order->restaurant_id);

        if($restaurant){
            $cloverClient = new CloverController($restaurant->clover_mid);

            $cloverClient->createOrder($order, $restaurant);
        }
    }
}
