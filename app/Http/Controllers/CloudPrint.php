<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PrinterDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloudPrint extends Controller
{
    public function handlePrinterCalls(Request $request)
    {
        $method = $request->method();
        if ($method == 'POST') {
            $data = $this->handleCloudPRNTPoll($request->all());
            return response()->json($data, 200);
        } elseif ($method == 'GET') {
            $this->handleCloudPRNTGetJob($request);
        } elseif ($method == 'DELETE') {

        }

        return response()->json([], 200);
    }

    public function handleCloudPRNTPoll($request)
    {
        $pollResponse = array();
        $pollResponse['jobReady'] = false;

        $printer = $this->validatePrinter($request['printerMAC']);

        if ($printer) {
            $this->setDeviceStatus($request);

            if ($printer->dot_width === 0) {
                $pollResponse['clientAction'] = array();
                $pollResponse['clientAction'][0] = array("request" => "PageInfo", "options" => "");
                $pollResponse['clientAction'][1] = array("request" => "ClientType", "options" => "");
                $pollResponse['clientAction'][2] = array("request" => "ClientVersion", "options" => "");
            } else {
                $printing = $printer->is_printing;
                $queue = $printer->queue_id;
                if (isset($printing) && !empty($printing) && isset($queue)) {
                    $pollResponse['jobReady'] = true;
                    $pollResponse['mediaTypes'] = $this->getCPSupportedOutputs("text/vnd.star.markup");
                }
            }
        }
        return $pollResponse;
    }

    public function validatePrinter($macAddress)
    {
        $printer = PrinterDevice::where('device_mac_address', $macAddress)->first();
        if ($printer) {
            return $printer;
        }
        return false;
    }

    public function setDeviceStatus($request)
    {
        PrinterDevice::where('device_mac_address', $request['printerMAC'])
            ->update([
                'status' => urldecode($request['statusCode']),
                'last_poll' => time(),
            ]);
        return true;
    }

    public function getCPSupportedOutputs($mediaType)
    {
        if (file_exists(dirname(__FILE__) . '/cputil/cputil')) {
            $cputilpath = 'cputil/cputil';
        } else {
            $cputilpath = 'cputil';
        }
        $file = popen($cputilpath . " mediatypes-mime \"text/vnd.star.markup\"", "r");

        if ($file != FALSE) {
            $output = fread($file, 8192);

            pclose($file);
            return json_decode($output);
        }

        return "";
    }

    public function handleCloudPRNTGetJob($request)
    {
        $content_type = 'application/vnd.star.line';

        $basefile = tempnam('/var/www/falafel-admin/public/prints', "markup");
        $markupfile = $basefile . ".stm";
        $outputfile = tempnam('/var/www/falafel-admin/public/prints', "output");

        $printer = $this->validatePrinter('00:11:62:0d:79:67');

        if ($printer) {
            $position = $printer->is_printing;
            $queue = $printer->queue_id;
            $width = $printer->dot_width;
            $ticketDesign = $this->getQueuePrintParameters();

            $this->renderMarkupJob($markupfile, $position, $queue, $ticketDesign);

            $this->getCPConvertedJob($markupfile, $content_type, $width, $outputfile);

            header("Content-Type: ".$content_type);
            header("Content-Length: ".filesize($outputfile));
            readfile($outputfile);

            unlink($basefile);
            unlink($markupfile);
            unlink($outputfile);

            PrinterDevice::where('device_mac_address','00:11:62:0d:79:67')->update(['is_printing' => 0]);
        }
    }

    public function getQueuePrintParameters()
    {
        $qfields = array();
        $qfields['Header'] = "[align: center][image: url https://falafel.qualwebs.com/static/media/logo.93799a1f.png; width 30%; min-width 48mm]";
        $qfields['Footer'] = "Thank you for your order.";
        $qfields['Logo'] = "";
        $qfields['Coupon'] = "";

        return $qfields;
    }

    public function renderMarkupJob($filename, $position, $queue, $design)
    {

        // ORDER AND RES DATA
        $orderDetails = $this->singleOrderDetail($queue);

        $orderDetails = json_decode($orderDetails, true);
        $restaurantAddress = $orderDetails['restaurant_details']['address'];
        $restaurantPhone = $orderDetails['restaurant_details']['contact_number'];
        $orderId = $orderDetails['order_id'];
        $customer = $orderDetails['user_first_name'] . ' ' . $orderDetails['user_last_name'];
        $customerPhone = $orderDetails['user_number'];
        $orderDate = $orderDetails['order_date'];
        $pickupTime = $orderDetails['pickup_time'];
        $orderType = $orderDetails['order_type'] == 1 ? 'Pickup' : 'Delivery';

        $orderItems = $orderDetails['order_details'];
        $orderSubtotal = $orderDetails['order_total'];
        $orderTax = $orderDetails['total_tax'];
        $orderTotal = $orderDetails['total_amount'];

        $file = fopen($filename, 'w+');

        if ($file != FALSE && $orderDetails) {
            fwrite($file, "[align: centre]");

            if (isset($design['Header'])) {
                fwrite($file, $design['Header'] . "\n");
            }

            fwrite($file, "[align: centre]");
            fwrite($file, "[mag: w 2; h 2]" . $restaurantAddress . "[mag]\n");
            fwrite($file, "[mag: w 2; h 2]Phone: " . $restaurantPhone . "[mag]\n\n");
            fwrite($file, "[mag: w 3; h 3]Order #" . $orderId . "[mag]\n\n");

            fwrite($file, "[mag: w 2; h 2]" . $customer . "[mag]\n");
            fwrite($file, "[mag: w 2; h 2]" . $customerPhone . "[mag]\n\n");

            fwrite($file, "[mag: w 4; h 4]" . $orderType . "[mag]\n\n");

            fwrite($file, "[mag: w 2; h 2]Date: " . $orderDate . "[mag]\n");
            fwrite($file, "[mag: w 2; h 2]Time: " . $pickupTime . "[mag]\n\n");

            fwrite($file, "[mag: w 3; h 3]SALES INVOICE[mag]\n");

            fwrite($file, "[align: left]");

            if (count($orderItems)) {
                foreach ($orderItems AS $orderItem) {
                    fwrite($file, "[column: left: ".$orderItem['item_name'].";      right: $".$orderItem['item_price']."]\n");

                    if(count($orderItem['order_items'])){
                        foreach ($orderItem['order_items'] AS $subItem){
                            fwrite($file,"[column: left ".$subItem['item_name']."; right $".$subItem['item_price']."; indent 5mm]\n");
                        }
                    }
                }
            }

            fwrite($file, "[align: centre]");
            fwrite($file, "[align: left]");
            fwrite($file, "[mag: w 2; h 2]");
            fwrite($file, "[column: left: Subtotal;      right: $".$orderSubtotal."]\n");
            fwrite($file, "[column: left: Tax;      right: $".$orderTax."]\n");
            fwrite($file, "[mag: w 3; h 3]");
            fwrite($file, "[column: left: Total;      right: $".$orderTotal."]\n");

            fwrite($file, "[mag]");
            if (isset($design['Footer'])) {
                fwrite($file, $design['Footer'] . "\n");
            }

            fwrite($file, "[cut]");

            fclose($file);
        }
    }

    public function getCPConvertedJob($inputFile, $outputFormat, $deviceWidth, $outputFile)
    {
        if (file_exists(dirname(__FILE__) . '/cputil/cputil')) {
            $cputilpath = 'cputil/cputil';
        } else {
            $cputilpath = 'cputil';
        }

        $options = "";

        if ($deviceWidth <= (58 * 8)) {
            $options = $options . "thermal2";
        } elseif ($deviceWidth <= (72 * 8)) {
            $options = $options . "thermal3";
        } elseif ($deviceWidth <= (82 * 8)) {
            $options = $options . "thermal82";
        } elseif ($deviceWidth <= (112 * 8)) {
            $options = $options . "thermal4";
        }

        $options = $options . " scale-to-fit dither ";

        system($cputilpath . " " . $options . " decode \"" . $outputFormat . "\" \"" . $inputFile . "\" \"" . $outputFile . "\"", $retval);
    }

    public function singleOrderDetail($orderId)
    {
        $orderDate = DB::raw("DATE_FORMAT(orders.pickup_date,'%m-%d-%Y') as order_date");
        $pickUpTime = DB::raw("orders.pickup_time as pickup_time");
        $preparationTime = DB::raw("LOWER(TIME_FORMAT(FROM_UNIXTIME(orders.preparation_time),'%l:%i %p')) as preparation_time");

        $response = \App\Models\Order\Order::with(['userDetails', 'userReward', 'restaurantDetails', 'orderDetails' => function ($query) {
            $query->with(['orderItems'])->leftJoin('items', 'items.item_id', 'order_details.item_id')
                ->whereNotNull('items.item_id')
                ->get();
        }, 'transaction'])->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'orders.coupon_id')
            ->where('order_id', '=', $orderId)
            ->select('orders.order_id', 'orders.reference_id', 'orders.user_id',
                'orders.restaurant_id',
                'orders.user_first_name',
                'orders.user_last_name',
                'orders.user_number',
                'orders.order_device',
                'orders.order_type',
                DB::raw('FORMAT(orders.total_tax,2) as total_tax'),
                DB::raw('FORMAT(orders.order_total,2) as order_total'),
                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
                'orders.discount_amount',
                'reward_coupons.coupon_id',
                'reward_coupons.coupon_type',
                'orders.discount_amount',
                'orders.status', 'orders.created_at',
                $pickUpTime, $preparationTime, $orderDate)->first();
        if ($response['pickup_time']) {
            $response['pickup_time'] = date('g:i a', $response['pickup_time']);
        }
        return $response;
    }

    public function addPrintQueue($orderId){
        PrinterDevice::where('device_mac_address','00:11:62:0d:79:67')->update(['queue_id' => $orderId,'is_printing' => 1]);

        return response()->json(apiResponseHandler([], 'success', 200));
    }
}
