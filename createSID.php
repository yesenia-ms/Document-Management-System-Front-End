<?php
include 'functions.php';
$username="seu346";
$password="HnDrfxY9CzjP732";
$data="username=$username&password=$password";
$apiURL="https://cs4743.professorvaladez.com/api/create_session"; // declares the endpoint we are connecting to
//$ch=curl_init($apiURL);
$results=curlHandlerCalls($data, $apiURL); // standard for every cURL handler
if($results != 0){
	// RESULTS ARE GOOD SESSION CREATED NOTHING ELSE TO DO
	// results are already returned from curlHandlerCalls() so if json_decoded needed do it here
	$cinfo=json_decode($results, true); // converts JSON to assoc array
	// checking return values of result json object in assoc array
	$status_arr = explode(":", $cinfo[0]);
	$msg_arr = explode(":", $cinfo[1]);
	$sid_arr = explode(":", $cinfo[2]);
	print_r($status_arr); echo "<br>";
	print_r($msg_arr); echo "<br>";
	print_r($sid_arr); echo"<br>";
	
	// CHECKING STATUS INFO
	if(strstr($status_arr[0], "Status")){
		if(strstr($status_arr[1], "ERROR")){
			echo "<br>"; print_r("\nStatus = ERROR\n"); echo "<br>";
		}
		else if(strstr($status_arr[1], "OK")){
			echo "<br>"; print_r("\nStatus = OK\n"); echo "<br>";
		}
	}
	else{
		print_r("Something other than status is here? What is it?");
	}
	
	// CHECKING MSG INFO - [1] INDEX
	if((strstr($msg_arr[0], "MSG")) && (strstr($msg_arr[1], "ERROR"))){
		// save status ERROR in database
		echo "<br>"; print_r("\nStatus = ERROR\n"); echo "<br>";
	}
	else if(strstr($msg_arr[0], "MSG") && strstr($msg_arr[1], "OK")){
		// save status OK in database
		echo "<br>"; print_r("\nStatus = OK\n"); echo "<br>";
	}
	if(strstr($msg_arr[0], "MSG")){
		if(strstr($msg_arr[1], "Previous Session Found")){
			// save Message in database
			print_r("Messsage = Previous Session Found"); echo "<br>";
		}
		else if(strstr($msg_arr[1], "No previous session found")){
			print_r("Message = No previous session found"); echo "<br>";
		}
		else{
			print "Message:"; print_r($msg_arr[1]); echo "<br>";
			$payload=json_decode($msg_arr[1]);
			//return $payload;
			echo "<pre>";
			print_r($payload);
			echo "</pre>";
		}
	}
	else{
		print_r("Something other than MSG is here? What is it?"); echo "<br>";
	}
	
	// CHECKING IF SID OR ACTION MSG GIVEN - [2] INDEX
	if(strstr($sid_arr[0], "Action")){
		// $sid_arr[1] will the action to be taken
		print_r("Action = $sid_arr[1]"); echo "<br>";
	}
	else{
		// if not action then it is the session id
		print_r("Session id: $sid_arr[0]"); echo "<br>";
	}
}
?>