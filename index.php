<?php
    if (!class_exists('Paymentwall_Config')) {
        require_once(__DIR__ . '/lib/paymentwall.php');
    }
    
    Paymentwall_Config::getInstance()->set(array(
        'private_key' => 'YOUR_PRIVATE_KEY',
        'public_key' => 'YOUR_PUBLIC_KEY'
    ));

    $webhook = file_get_contents('php://input');
    $fulfillment = json_decode($webhook, TRUE);

    callDeliveryApi($fulfillment);
    
    /**
     * Call Delivery API
     * @param  $fulfillment [description]
     * @return 
     */
    function callDeliveryApi ($fulfillment)
    {
        $delivery = new Paymentwall_GenerericApiObject('delivery');

        return $delivery->post(prepareDeliveryData($fulfillment));
    }

    /**
     * Prepare Delivery Data
     * @param  $fulfillment
     * @return array
     */
    function prepareDeliveryData ($fulfillment)
    {
        $data = array(
            'payment_id' => $fulfillment['order_id'],
            'merchant_reference_id' => $fulfillment['order_id'],
            'status' => 'delivered',
            'estimated_delivery_datetime' => $fulfillment['created_at'],
            'estimated_update_datetime' => $fulfillment['updated_at'],
            'refundable' => true,
            'details' => 'Item will be delivered via email by ' . $fulfillment['created_at'],
            'shipping_address[email]' => $fulfillment['email'],
            'shipping_address[firstname]' => $fulfillment['destination']['first_name'],
            'shipping_address[lastname]' => $fulfillment['destination']['last_name'],
            'shipping_address[country]' => $fulfillment['destination']['country'],
            'shipping_address[street]' => $fulfillment['destination']['address1'],
            'shipping_address[state]' => $fulfillment['destination']['province_code'] ? $fulfillment['destination']['province_code'] : 'NA',
            'shipping_address[phone]' => $fulfillment['destination']['phone'] ? $fulfillment['destination']['phone'] : 'NA',
            'shipping_address[zip]' => $fulfillment['destination']['zip'],
            'shipping_address[city]' => $fulfillment['destination']['city'],
            'carrier_type' => $fulfillment['tracking_company'],
            'reason' => 'none',
            'carrier_trackind_id' => $fulfillment['tracking_number'],
            'is_test' => 1
        );
        
        if(!empty($fulfillment['destination'])) {
            $data['type'] = 'physical';
        } else {
            $data['type'] = 'digital';
        }

        return $data;
    }

?>
