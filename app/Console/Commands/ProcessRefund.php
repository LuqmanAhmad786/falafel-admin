<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\StripePaymentController;
use App\Models\Order\Order;
use Braintree\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Braintree\Transaction;
use Braintree\Configuration;
use Illuminate\Support\Facades\Log;
use App\OrderRefunds;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;

class ProcessRefund extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:refund';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process refund in batch';

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
        $stripeController = new StripePaymentController();
        $apiKey = $stripeController->getApiKey();
        Stripe::setApiKey($apiKey['secret_key']);
        $orders = DB::table('order_refund_queue')->where('status',0)->get();
        foreach ($orders as $order) {
                try{
                    $refund = Refund::create([
                        'charge' => $order->transaction_id,
                        'amount' => $order->amount*100,
                        'reason' => 'requested_by_customer',
                        'reverse_transfer' => true
                    ]);
                    if($refund->status === 'succeeded'){
                        $orderRefund = new OrderRefunds();
                        $orderRefund->order_id = $order->order_id;
                        $orderRefund->transaction_id = $refund->id;
                        $orderRefund->transaction_status = $refund->status;
                        $orderRefund->approval_number = 0;
                        $orderRefund->refund_amount = $order->amount;
                        $orderRefund->sub_total_refund = $order->order_total;
                        $orderRefund->tax_refund = $order->total_tax;
                        $orderRefund->discount_adjust = $order->discount_amount;
                        $orderRefund->reference_number = $refund->id;
                        $orderRefund->save();

                        Order::where('order_id', $order->order_id)->update(['payment_status' => 2]);

                        DB::table('order_refund_queue')->where('id', $order->id)->update(['status' => 1]);
                    } else{
                        Log::debug($refund->error);
                    }
                }
                catch (ApiErrorException $e){
                    Log::debug($e);
                }
        }
        return true;
    }
}
