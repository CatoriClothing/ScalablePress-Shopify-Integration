<?php

/*$path = dirname(__FILE__);
$data = file_get_contents("php://input");

$file = $path."/scalable_order-".time().".txt";
file_put_contents($file, $data);
$content = file_get_contents($file);

//$content = file_get_contents("scalable_order.txt");
parse_str($content, $response);
$resp = $response;
echo "<pre>";print_r($resp);echo "</pre>";
*/

$path = dirname(__FILE__);
$data = file_get_contents("php://input");
parse_str($data, $resp);


include('functions.php');
$databaseconnect = $apiObj->databaseconnect();

if(isset($resp['orderId']) && $resp['orderId'] != ''){
	$orderId = trim(strip_tags($resp['orderId']));
	if(isset($resp['event']['name']) && $resp['event']['name'] == 'shipped'){
		if(isset($resp['event']['meta']['tracking']) && $resp['event']['meta']['tracking'] != ''){
			$tracking = $resp['event']['meta']['tracking'];
			$service = $resp['event']['meta']['service'];
			
			
			$orderInfoSql = "UPDATE `orderInfo` SET  `trackingId` = '".$tracking."' where `scaleOrderId` ='".$orderId ."' ";
			$result = $databaseconnect->query($orderInfoSql);
			if ($databaseconnect->query($orderInfoSql) === TRUE) {
				$response =  "Record updated successfully";
			} else {
				$response =  "Error updating record: " . $databaseconnect->error;
			} 
			echo $response;
				
			/***** GET SCALE ORDER *******/
			echo $urls ='https://api.scalablepress.com/v2/order/'.$orderId;
			$getOrder = $apiObj->scaleGet($urls);
			$getOrders = json_decode($getOrder,true);
			echo "<pre>";print_r($getOrders);echo "</pre>";

			if(isset($getOrders['items'][0]['name']) && $getOrders['items'][0]['name'] != ''){
				$name = $getOrders['items'][0]['name'];
				
				if (strpos($name, '/') !== false) {
					$shopifyOrder = explode('/',$name);
					echo $shopifyOrderId = trim($shopifyOrder['1']);
					$shopifyUrl = "https://.myshopify.com/admin/api/2020-10/orders/".$shopifyOrderId."/fulfillments.json";
					$post = '{"fulfillment": {"location_id": ,"tracking_number": "'.$tracking.'", "notify_customer": true}}';
					$getShopifyOrder = $apiObj->postShopifyProduct($shopifyUrl,$post);
					$getShopifyOrders = json_decode($getShopifyOrder,true);
					echo "<pre>";print_r($getShopifyOrders);echo "</pre>";
					
					if(isset($getShopifyOrders['fulfillment']['status']) && $getShopifyOrders['fulfillment']['status'] != ''){
						$status = $getShopifyOrders['fulfillment']['status'];
					}else{
						if(isset($getShopifyOrders['errors']['base']) && $getShopifyOrders['errors']['base'] != ''){
							foreach($getShopifyOrders['errors']['base'] as $error){
								$errorStatus[] =  mysqli_real_escape_string($databaseconnect,$error);
							}
							$status = implode('-next_error-', $errorStatus);
						}else{
							$status = '';
						}
					}
					/*************UPDATE STATUS AND TRACKING NO**************/
					$orderInfoSql = "UPDATE `orderInfo` SET  `trackingStatus` = '".$status."' where `scaleOrderId` ='".$orderId ."' ";
					$result = $databaseconnect->query($orderInfoSql);
					if ($databaseconnect->query($orderInfoSql) === TRUE) {
						$response =  "Record updated successfully";
					} else {
						$response =  "Error updating record: " . $databaseconnect->error;
					} 
					echo $response;
				}else{
					$statusNew = $name.'-Message- Order not updated because its an old order';
					$orderInfoSql = "UPDATE `orderInfo` SET  `trackingStatus` = '".$statusNew."' where `scaleOrderId` ='".$orderId ."' ";
					$result = $databaseconnect->query($orderInfoSql);
					if ($databaseconnect->query($orderInfoSql) === TRUE) {
						$response =  "Record updated successfully";
					} else {
						$response =  "Error updating record: " . $databaseconnect->error;
					} 
					echo $response;
				}
			}else{
				$statusNew = '-Message- Order not updated because its an old order';
				$orderInfoSql = "UPDATE `orderInfo` SET  `trackingStatus` = '".$statusNew."' where `scaleOrderId` ='".$orderId ."' ";
				$result = $databaseconnect->query($orderInfoSql);
				if ($databaseconnect->query($orderInfoSql) === TRUE) {
					$response =  "Record updated successfully";
				} else {
					$response =  "Error updating record: " . $databaseconnect->error;
				} 
				echo $response;
			}			
		}
	}
}

