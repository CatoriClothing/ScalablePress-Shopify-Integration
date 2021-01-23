<?php
class api{
	
	public function __construct()
	{
		$config =  [
			'Database' => [
					'HOST'=>'',
					'DATABASE'=>'',
					'USER'=>'',
					'PASSWORD'=>''
			]
		];

		return $this->config = $config;

	} 
	
	function databaseconnect(){
		$host = $this->config['Database']['HOST'];
		$database = $this->config['Database']['DATABASE'];
		$user = $this->config['Database']['USER'];
		$password =$this->config['Database']['PASSWORD'];
		$mysqli =  mysqli_connect($host, $user,$password,$database);
		
		/* if ($mysqli -> connect_errno) {
			echo "Failed to connect to MySQL: " d. $mysqli -> connect_error;
			exit();
		} */
		
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			exit();
		}
		return $mysqli;
	}
	
	function getShopifyProduct($url){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"authorization: Basic ",
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return $response;
	}
	
	function postShopifyProduct($url,$post){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $post,
			CURLOPT_HTTPHEADER => array(
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return $response;
	}
	
	function scalePost($url, $jsonData){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return $response;
	}
	
	function scaleGet($url){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"authorization: Basic ",
				"cache-control: no-cache",
				"postman-token: "
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return $response;
	}
}
$apiObj = new api;