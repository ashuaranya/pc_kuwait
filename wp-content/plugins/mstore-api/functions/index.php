<?php
define("ACTIVE_API", "https://license.fluxstore.app/api/v1");
define("ACTIVE_TOKEN", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJmb28iOiJiYXIiLCJpYXQiOjE1ODY5NDQ3Mjd9.-umQIC6DuTS_0J0Jj8lcUuUYGjq9OXp3cIM-KquTWX0");

function verifyPurchaseCode ($code) {
    $website = get_home_url();
    $response = wp_remote_get( ACTIVE_API."/active?code=".$code."&website=".$website."&token=".ACTIVE_TOKEN."&isPlugin=true");
    $statusCode = wp_remote_retrieve_response_code($response);
    $success = $statusCode == 200;
    if($success){
        update_option("mstore_purchase_code", true);
        update_option("mstore_purchase_code_key", $code);
    }else{
        $body = wp_remote_retrieve_body($response);
        $body = json_decode($body, true);
        return $body["error"];
    }
    return $success;
}

function checkCurrentPurchaseCode () {
    // $code = get_option("mstore_purchase_code_key");
    // if(isset($code) && $code != false){
    //     $website = get_home_url();
    //     $response = wp_remote_get( ACTIVE_API."/active?code=".$code."&website=".$website."&token=".ACTIVE_TOKEN."&isPlugin=true");
    //     $statusCode = wp_remote_retrieve_response_code($response);
    //     update_option("mstore_purchase_code", $statusCode == 200);
    // }else{
    //     update_option("mstore_purchase_code", false);
    // }
}

function pushNotification ($title, $message, $deviceToken) {
    $serverKey = get_option("mstore_firebase_server_key");
    if (isset($serverKey) && $serverKey != false) {
        $body = ["notification" => ["title" => $title, "body" => $message, "click_action" => "FLUTTER_NOTIFICATION_CLICK"], "to" => $deviceToken];
        $headers = ["Authorization" => "key=".$serverKey, 'Content-Type' => 'application/json; charset=utf-8'];
        $response = wp_remote_post("https://fcm.googleapis.com/fcm/send", ["headers" => $headers, "body" => json_encode($body)]);
        $statusCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        return $statusCode == 200;
    }
    return false;
}

function sendNotificationToUser($userId, $orderId, $previous_status, $next_status){
    $user = get_userdata($userId);
    $deviceToken = get_option("mstore_device_token_".$userId);
    if (isset($deviceToken) && $deviceToken != false) {
        $itle = get_option("mstore_status_order_title");
        if (!isset($itle) || $itle == false) {
            $itle = "Order Status Changed";
        }
        $message = get_option("mstore_status_order_message");
        if (!isset($message) || $message == false) {
            $message = "Hi {{name}}, Your order: #{{orderId}} changed from {{prevStatus}} to {{nextStatus}}";
        }
        $message = str_replace("{{name}}", $user->display_name, $message);
        $message = str_replace("{{orderId}}", $orderId, $message);
        $message = str_replace("{{prevStatus}}",$previous_status, $message);
        $message = str_replace("{{nextStatus}}",$next_status, $message);
        pushNotification($itle, $message, $deviceToken);
    }
}

function trackOrderStatusChanged($id, $previous_status, $next_status){
    $order = wc_get_order( $id );
    $userId = $order->get_customer_id();
    sendNotificationToUser($userId, $id, $previous_status, $next_status);
}

function trackNewOrder($order_id){
    $order = wc_get_order( $order_id );
    if( is_plugin_active( 'dokan-lite/dokan.php' ) ) {
        if ( dokan_is_order_already_exists( $order_id ) ) {
            return;
        }
        /*
        $product_id = current( $items )->get_product_id();
        $seller_id  = get_post_field( 'post_author', $product_id );
        $seller_id  = $seller_id ? absint( $seller_id ) : 0;
        */
        $order_seller_id  = dokan_get_seller_id_by_order( $order_id );
        if (isset($order_seller_id) && $order_seller_id != false) {
            $user = get_userdata($order_seller_id);
            $deviceToken = get_option("mstore_device_token_".$order_seller_id);
            if (isset($deviceToken) && $deviceToken != false) {
                $title = get_option("mstore_new_order_title");
                if (!isset($title) || $title == false) {
                    $title = "New Order";
                }
                $message = get_option("mstore_new_order_message");
                if(!isset($message) || $message == false){
                    $message = "Hi {{name}}, Congratulations, you have received a new order! ";
                }
                $message = str_replace("{{name}}", $user->display_name, $message);
                pushNotification($title, $message, $deviceToken);
            }
        }
    }
}

function getAddOns($categories){
    $addOns = [];
    if( is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
        $addOnGroup = WC_Product_Addons_Groups::get_all_global_groups();
        foreach ($addOnGroup as $addOn){
            $cateIds = array_keys($addOn["restrict_to_categories"]);
            if (count($cateIds) == 0) {
                $addOns = array_merge($addOns, $addOn["fields"]);
                break;
            }
            $isSupported = false;
            foreach ($categories as $cate){
                if(in_array($cate["id"], $cateIds)){
                    $isSupported = true;
                    break;
                }
            }
            if ($isSupported) {
                $addOns = array_merge($addOns, $addOn["fields"]);
            }
        }
    }
    
    return $addOns;
}
?>