<?php
include 'functions.php';
date_default_timezone_set('America/Chicago');
$dblink=db_connect("docStoragefa24");
$username="seu346";
$password="HnDrfxY9CzjP732";
$data="username=$username&password=$password";
$apiURL="https://cs4743.professorvaladez.com/api/clear_session"; // declares the endpoint we are connecting to
$nowBegin=time();
$results=curlHandlerCalls($data, $apiURL); // standard for every cURL handler
print_r("results being printed");
print_r($results);
if($results != 0){
	// results are already returned from curlHandlerCalls() so if json_decoded needed do it here
	$cinfo=json_decode($results, true); // converts JSON to assoc array
	print_r("printing the cinfo json_decode()");
	print_r($cinfo);
	// checking return values of result json object in assoc array
	$status_arr = explode(":", $cinfo[0]);
	$msg_arr = explode(":", $cinfo[1]);
	$sid_arr = explode(":", $cinfo[2]);
	print_r($status_arr); echo "<br>";
	print_r($msg_arr); echo "<br>";
	print_r($sid_arr); echo"<br>";
}
$nowEnd=time();
$entireProcessTime = $nowEnd - $nowBegin;
//log_info_DB("Session: $sid_arr cleared", $entireProcessTime, NULL, NULL, NULL, $dblink);
?>