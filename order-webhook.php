<?php
$data = file_get_contents("php://input");
$order = json_decode($data, true);
include('functions.php');
$databaseconnect = $apiObj->databaseconnect();
$date = date('Y-m-d h:i:s');
$trackingId = '';
$trackingStatus = '';
$sizes =  Array( 'S' => 'sml', 'M' => 'med', 'L' =>'lrg', 'XL' => 'xlg', '2XL'=> 'xxl' , '3XL' => 'xxxl');


if(isset($order['id']) && $order['id'] != ''){
	$shopifyOrderIds = $order['id'];
	$shopifyOrderIdName = $order['name'];
	$shopifyOrderId = $shopifyOrderIdName .' / '.$shopifyOrderIds;
}

if($shopifyOrderId != ''){
	$sql2_select = "SELECT * from `orderInfo` where `shopifyOrderId` = '".$shopifyOrderId."' "; 
	$result_select = $databaseconnect->query($sql2_select);
	if (!empty($result_select) && $result_select->num_rows > 0){
		echo "Record Found";
	}else{	
					// Broken
		if(isset($order['tags']) && $order['tags'] != ''){
			$types = $order['tags'];
			if (strpos($types, 'embr') !== false) {
				$type = 'embr';
			}else{
				$type = 'dtg';
			}
		}else{
			$type = 'dtg';
		}

		if(isset($order['line_items']) && $order['line_items'] != ''){
			if(isset($order['customer']['default_address']) && $order['customer']['default_address'] != ''){
				$first_name = $order['customer']['default_address']['first_name'];
				$last_name = $order['customer']['default_address']['last_name'];
				$name = $first_name.' '.$last_name;
				$address1 = $order['customer']['default_address']['address1'];
				$address2 = $order['customer']['default_address']['address2'];
				$city = $order['customer']['default_address']['city'];
				if($order['customer']['default_address']['province'] == ""){
					$state = ".";
				}
				else{
					$state = $order['customer']['default_address']['province'];
				}
				$zip = $order['customer']['default_address']['zip'];
				$country_code = $order['customer']['default_address']['country_code'];
				$customer = array("name" => $name, "address1" =>$address1, "address2" =>$address2,"city" => $city, "state" => $state,  "zip" => $zip,  "country" => $country_code );
			}
			
			foreach($order['line_items'] as $line_items){
				
				$product_id = trim(strip_tags($line_items['product_id']));
				/******** GET Product Type  ************/
				$url = "https://.myshopify.com/admin/api/2020-10/products/".$product_id.".json";
				$getProduct = $apiObj->getShopifyProduct($url);
				$getProduct = json_decode($getProduct,true);
				if($getProduct['product']['product_type'] != "service"){
					/******** GET PRODUCT DESCRIPTION*******/
					$url = "https://.myshopify.com/admin/api/2020-10/products/".$product_id."/metafields.json";
					$getProduct = $apiObj->getShopifyProduct($url);
					$getProducts = json_decode($getProduct,true);
					if(isset($getProducts['metafields']) && $getProducts['metafields'] != ''){
						$key = array_search('productid' , array_column($getProducts['metafields'], 'key')); 
						
						$designIds = trim(strip_tags($line_items['sku'])); 
						$designId =preg_replace('/[^A-Za-z0-9\-]/', '', $designIds);
						
						$productIds = trim(strip_tags($getProducts['metafields'][$key]['value']));
						$productId =preg_replace('/[^A-Za-z0-9\-]/', '', $productIds);
						
						$quantity = $line_items['quantity'];
						$variant_title = $line_items['variant_title'];
						$variant_titles = explode('/', $variant_title);
						$size = $variant_titles[0];
						$color = $variant_titles[1];
						
						$siz = ucwords(trim($size));
						$clr = strtolower(trim($color));
						$products[] = array('type'=> $type, 'designId' =>$designId, "products"=> array(array('id'=>$productId, 'quantity'=>$quantity , 'size'=> $sizes[$siz], 'color'=>$clr)), 'address' => $customer,'name' => ''.$shopifyOrderId.'');
					}
				}
			}
		}

		if(isset($products) && $products != ''){
			/******** CREATE QUOTE USING API **********/
			$quoteData = array("items" => $products); 
			
			$url = "https://api.scalablepress.com/v2/quote/bulk";
			$jsonData = json_encode($quoteData);
			$scalePost = $apiObj->scalePost($url, $jsonData);
			$scalePosts = json_decode($scalePost,true);

			if(isset($scalePosts['orderToken']) && $scalePosts['orderToken'] != ''){
				/***** Place Order ******/
				$orderToken = $scalePosts['orderToken'];
				$orderTotal = $scalePosts['total'];
				$orderTax = $scalePosts['tax'];
				if(isset($scalePosts['warnings']) && $scalePosts['warnings']!= ''){
					foreach($scalePosts['warnings'] as $warnings){
						$quoteOrderWarning[] =  mysqli_real_escape_string($databaseconnect,$warnings);
					}
					$quoteOrderWarnings = implode('-next_warning-', $quoteOrderWarning);
				}else{
					$quoteOrderWarnings = '';
				}
				
				$orderUrl = "https://api.scalablepress.com/v2/order";
				$orderData = array('orderToken'=> $orderToken);
				$orderJson = json_encode($orderData);		
				$scalePostOrders = $apiObj->scalePost($orderUrl, $orderJson);
				$scalePostOrder = json_decode($scalePostOrders,true);

				if(isset($scalePostOrder['orderId']) && $scalePostOrder['orderId'] != ''){
					$scaleOrderId = $scalePostOrder['orderId'];
					$orderStatus = $scalePostOrder['status'];
				}else{
					$scaleOrderId = '';
					$orderStatus = '';
				}
				
				
				$insertOrder ="INSERT INTO orderInfo ( `shopifyOrderId`, `quoteOrderToken`, `quoteOrderWarnings` , `orderTotal`, `orderTax` , `orderStatus`, `scaleOrderId`,`trackingId`, `trackingStatus`, `date` )VALUES ('".$shopifyOrderId."','".$orderToken."','".$quoteOrderWarnings."','".$orderTotal."','".$orderTax."','".$orderStatus."','".$scaleOrderId ."','".$trackingId ."','".$trackingStatus ."','".$date."')";
				if (mysqli_query($databaseconnect, $insertOrder)) {
					echo $c =  "Records created successfully in orderInfo<br>"; 
				} else {
					echo $c= "Error creating record : " . mysqli_error($databaseconnect);
				}	
			}else{
				if(isset($scalePosts['statusCode']) && $scalePosts['statusCode'] == '400'){
					$scaleOrderId = '';
					$orderToken = '';
					$quoteOrderWarnings = '';
					$orderTotal = '';
					$orderTax = '';
					if(isset($scalePosts['issues']) && $scalePosts['issues'] != ''){
						foreach($scalePosts['issues'] as $issue){
							$orderStatuses[] = mysqli_real_escape_string($databaseconnect,$issue['message']);
						}
						$orderStatus = implode('-next_issue-',$orderStatuses);
					}else{
						$orderStatus = '';
					}
					$insertOrder ="INSERT INTO orderInfo ( `shopifyOrderId`, `quoteOrderToken`, `quoteOrderWarnings` , `orderTotal`, `orderTax` , `orderStatus`, `scaleOrderId`,`trackingId`, `trackingStatus`, `date` )VALUES ('".$shopifyOrderId."','".$orderToken."','".$quoteOrderWarnings."','".$orderTotal."','".$orderTax."','".$orderStatus."','".$scaleOrderId ."','".$trackingId ."','".$trackingStatus ."','".$date."')";
					if (mysqli_query($databaseconnect, $insertOrder)) {
						echo $c =  "Records created successfully in orderInfo<br>"; 
					} else {
						echo $c= "Error creating record : " . mysqli_error($databaseconnect);
					}		
				}else{
					$scaleOrderId = '';
					$orderToken = '';
					if(isset($scalePosts['total']) && $scalePosts['total'] != ''){
						$orderTotal = $scalePosts['total'];
					}else{
						$orderTotal = '';
					}
					if(isset($scalePosts['total']) && $scalePosts['total'] != ''){
						$orderTax = $scalePosts['tax'];
					}else{
						$orderTax = '';
					}
			
					
					if(isset($scalePosts['warnings']) && $scalePosts['warnings']!= ''){
						foreach($scalePosts['warnings'] as $warnings){
							$quoteOrderWarning[] =  mysqli_real_escape_string($databaseconnect,$warnings);
						}
						$quoteOrderWarnings = implode('-next_warning-', $quoteOrderWarning);
					}else{
						$quoteOrderWarnings = '';
					}
				
					if(isset($scalePosts['orderIssues']) && $scalePosts['orderIssues'] != ''){
						foreach($scalePosts['orderIssues'] as $issue){
							$orderStatuses[] = mysqli_real_escape_string($databaseconnect,$issue['message']);
						}
						$orderStatus = implode('-next_issue-',$orderStatuses);
					}else{
						$orderStatus = '';				
					}
					$insertOrder ="INSERT INTO orderInfo ( `shopifyOrderId`, `quoteOrderToken`, `quoteOrderWarnings` , `orderTotal`, `orderTax` , `orderStatus`, `scaleOrderId`,`trackingId`, `trackingStatus`, `date` )VALUES ('".$shopifyOrderId."','".$orderToken."','".$quoteOrderWarnings."','".$orderTotal."','".$orderTax."','".$orderStatus."','".$scaleOrderId ."','".$trackingId ."','".$trackingStatus ."','".$date."')";
					if (mysqli_query($databaseconnect, $insertOrder)) {
						echo $c =  "Records created successfully in orderInfo<br>"; 
					} else {
						echo $c= "Error creating record : " . mysqli_error($databaseconnect);
					}		
					
				}
			}
		}
	}
}