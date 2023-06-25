<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cart\CartList;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Postmates\Client;

class DeliveryController extends Controller
{
    public function createView(){
        return view('dashboard.delivery-create');
    }

    public function getDeliveryRestaurant(Request $request){
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $distance = DB::raw('3961 * 2 * ASIN(SQRT(POWER(SIN((latitude - abs(' . $request->input('latitude') . ')) * pi()/180 / 2),2) + COS(latitude * pi()/180 ) * COS(abs(' . $request->input('latitude') . ') * pi()/180) * POWER(SIN((longitude - ' . $request->input('longitude') . ') * pi()/180 / 2), 2) )) as distance');

        $nearestRes = (new Restaurant())->select('restaurants.*', $distance)->orderBy('distance', 'ASC')->first();

        $data = $this->generateQuotePostmates($nearestRes['address'], $request->input('address'));

        if($data){
            if($request->input('cart_id') != ''){
                CartList::where('cart_id', $request->input('cart_id'))->update(['delivery_fee'=>$data['fee']/100]);
                $cart = CartList::where('cart_id', $request->input('cart_id'))->first();
                cartCalculation($cart['cart_list_id']);
            }
            return response()->json(apiResponseHandler($nearestRes));
        }else{
            return response()->json(apiResponseHandler([],'Delivery not available.', 400), 400);
        }
    }

    public function getQuote(Request $request){
        $data = $this->generateQuotePostmates($request->input('pickup'),$request->input('dropoff'));
        $html = '<p>Fee: $'.$data['fee']/100 .'</p>';
        $html .= '<p>Delivery ETA: '.$data['dropoff_eta'].'</p>';
        $html .= '<p>Total Delivery Time: '.$data['duration'].' minutes</p>';
        $html .= '<p>Pickup In: '.$data['pickup_duration'].' minutes</p>';
        $html .= '<p>This quote will expire in next 5 minutes</p>';
        return response()->json(apiResponseHandler(['quote_id'=>$data['id'],'html'=>$html], 'Quote Generated Successfully'));
    }

    public function getDelivery(Request $request){
        $data = $this->generateDelivery();
        $html = '<a href="'.$data['tracking_url'].'">'.$data['tracking_url'].'</a>';
        return response()->json(apiResponseHandler(['html'=>$html], 'Delivery Generated Successfully'));
    }

    public function generateQuotePostmates($pickup,$dropoff){

        $post = 'pickup_address='.urlencode($pickup);
        $post .='&dropoff_address='.urlencode($dropoff);


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.postmates.com/v1/customers/cus_MdxFMssJp-mp4F/delivery_quotes",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,//"dropoff_address=701%20Mission%20St.%20San%20Francisco%2C%20CA&pickup_address=201%20Third%20St.%20San%20Francisco%2C%20CA",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic MDRiOTIyYWQtMWNiMS00NmNjLWIwYjktYWZmMGM1M2FhN2VmOg==",
            ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($httpcode == 200){
            return json_decode($response,true);
        }
        return false;
    }

    public function generateDelivery($dropOffAddress,$delivery_notes,$delivery_date,$delivery_time,$restaurant, $order)
    {
        $date = $delivery_date;
        $time = $delivery_time;
        $date = explode('-',$date);
        Log::debug("$date[2]-$date[1]-$date[0] $time");
        $timestamp =  strtotime("$date[2]-$date[1]-$date[0] $time");
        $pickup_ready_dt = date('Y-m-d\TH:i:s\Z', ($timestamp+25200) - 2400);
        $dropoff_deadline_dt = date('Y-m-d\TH:i:s\Z', $timestamp+25200);
//        $pickup_ready_dt = '2021-04-25T13:15:00Z';
//        $dropoff_deadline_dt = '2021-04-25T14:00:00Z';
        $post = [
            'dropoff_address' => $dropOffAddress,
            'pickup_address' => $restaurant->address,
            'dropoff_name' => $order->user_first_name .' '.$order->user_last_name,
            'dropoff_phone_number'=> $order->user_number,
            'pickup_ready_dt' => $pickup_ready_dt,
            'dropoff_ready_dt' => $pickup_ready_dt,
            'dropoff_deadline_dt' => $dropoff_deadline_dt,
            'pickup_name'=>'Falafel Corner',
            'pickup_phone_number'=>$restaurant->contact_number,
            'manifest'=>'Food',
            'manifest_reference'=>"Falafel Order number ".$order->order_id,
            'manifest_items'=>'[{"name": "Grocery","quantity": 1,"size": "small"}]',
            'dropoff_notes'=> $delivery_notes
        ];

        Log::debug($post);
        $post = http_build_query($post);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.postmates.com/v1/customers/cus_MdxFMssJp-mp4F/deliveries",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic MDRiOTIyYWQtMWNiMS00NmNjLWIwYjktYWZmMGM1M2FhN2VmOg==",
            ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        Log::debug($response);
        if($httpcode == 200){
            return json_decode($response);
        }
        return false;
    }

    public function getStatusFromPM(Request $request){
        Order::where('postmates_delivery_id', $request->input('delivery_id'))->update([
            'delivery_status' => $request->input('status')
        ]);
    }
}
