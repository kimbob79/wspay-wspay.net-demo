<?php

function send_tran_to_wanpas($payment_data) {
    $api_endpoint = 'https://wanpas.mycafe24.com/api/v1/secta/rcv-tran.php';
    
    // Set API key for authentication
    $api_key = 'pJbvkpGCZ4fyT-mQVQY2PzfF2c9EqRsDwL8hN5AX';
	
    $payment_data['api_key'] = $api_key;
    
    // Setup cURL
    $ch = curl_init($api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payment_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'X-API-Key: ' . $api_key
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if(curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    // Process response
    $result = json_decode($response, true);
    
    if($http_code != 200 || !isset($result['success']) || !$result['success']) {
        error_log('API Error: ' . ($result['message'] ?? 'Unknown error'));
        return false;
    }
    
    return $result;
}

?>