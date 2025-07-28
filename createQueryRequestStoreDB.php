<?php
include 'functions.php';
$dblink=db_connect("docStoragefa24");
date_default_timezone_set('America/Chicago');
//$sdblink=db_connect("sessionsDB");
$username="seu346";
$password="HnDrfxY9CzjP732";
$data="username=$username&password=$password";
$apiURL="https://cs4743.professorvaladez.com/api/create_session";
$results=curlHandlerCalls($data, $apiURL); // standard for every cURL handler
$globalsid;
if($results != 0){
	echo "<h4>I am here results from curlHandlerCalls successful</h4>";
	$cinfo_createSession=json_decode($results, true); // converts JSON to assoc 
													  // array
	if($cinfo_createSession == true){
		echo "<h4>jsonDecode worked well</h4>";
	if($cinfo_createSession[0]=="Status: OK"){
		echo "<h4>I am now here</h4>";
		$status_arr = explode(":", $cinfo_createSession[0]);
		$msg_arr = explode(":", $cinfo_createSession[1]);
		$sid_arr = explode(":", $cinfo_createSession[2]);
		print_r($status_arr); echo "<br>";
		print_r($msg_arr); echo "<br>";
		print_r($sid_arr); echo"<br>";
		$sid=$cinfo_createSession[2];
		$globalsid = $sid;
		// new curl Handler
		$data1="uid=$username&sid=$sid";
		$apiURL1="https://cs4743.professorvaladez.com/api/query_files"; 
		echo "<h3>Separator</h3>";
		$results_QF=curlHandlerCalls($data1, $apiURL1);
		$cinfo_QF=json_decode($results_QF, true);
		$payload = formatting($results_QF, $username, $dblink, $sid);
		echo "<h3>FORMATTING() function has returned</h3>";
		if($payload == true){
			echo "<h3>Calling request_file_api_call_DB()</h3>";
			$apiURL2="https://cs4743.professorvaladez.com/api/request_file";
			request_file_api_call_DB($payload, $sid, $username, $apiURL2, $dblink);
		}
		else{
			echo "<h3>Payload was false logging info</h3>";
		//	log_info_DB($msg_arr, NULL, 0, 0, $dblink);
		}
		// TODO: CHECK IF RESULTS RETURNS TRUE
//		$status_arr_QF = explode(":", $cinfo_QF[0]);
//		$msg_arr_QF = explode(":", $cinfo_QF[1]);
//		$sid_arr_QF = explode(":", $cinfo_QF[2]);
//		print_r($status_arr_QF); echo "<br>";
//		print_r($msg_arr_QF); echo "<br>";
//		print_r($sid_arr_QF); echo"<br>";
		// CHECKING STATUS INFO
//		if(strstr($sid_arr_QF[0], "Status")){
//			if(strstr($sid_arr_QF[1], "ERROR")){
//				echo "<br>"; print_r("\nStatus = ERROR\n"); echo "<br>";
//			}
//			else if(strstr($sid_arr_QF[1], "OK")){
//				echo "<br>"; print_r("\nStatus = OK\n"); echo "<br>";
//			}
//		}
//		else{
//			print_r("Something other than status is here? What is it?");
//		}
		
		// CHECKING MSG INFO - [1] INDEX
//		if((strstr($msg_arr_QF[0], "MSG")) && (strstr($msg_arr_QF[1], "ERROR"))){
//			// save status ERROR in database
//			echo "<br>"; print_r("\nStatus = ERROR\n"); echo "<br>";
//		}
//		else if(strstr($msg_arr_QF[0], "MSG") && strstr($msg_arr_QF[1], "OK")){
//			// save status OK in database
//			echo "<br>"; print_r("\nStatus = OK\n"); echo "<br>";
//		}
//		if(strstr($msg_arr_QF[0], "MSG")){
//			if(strstr($msg_arr_QF[1], "Previous Session Found")){
//				// save Message in database
//				print_r("Messsage = Previous Session Found"); echo "<br>";
//			}
//			else if(strstr($msg_arr_QF[1], "No previous session found")){
//				print_r("Message = No previous session found"); echo "<br>";
//			}
//			else{
//				print "Message:"; print_r($msg_arr_QF[1]); echo "<br>";
//				$payload=json_decode($msg_arr_QF[1]);
//				//return $payload;
//				echo "<pre>";
//				print_r($payload);
//				echo "</pre>";
//				// request file api call
//				$apiURL2="https://cs4743.professorvaladez.com/api/request_file";
//				request_file_api_call_DB($payload, $sid, $username, $apiURL2, $dblink);
//			}
//		}
//		else{
//			print_r("Something other than MSG is here? What is it?"); echo "<br>";
//		}
//	
//		// CHECKING IF SID OR ACTION MSG GIVEN - [2] INDEX
//		if(strstr($sid_arr_QF[0], "Action")){
//			// $sid_arr[1] will the action to be taken
//			print_r("Action = $sid_arr_QF[1]"); echo "<br>";
//		}
//		else{
//			// if not action then it is the session id
//			print_r("Session id: $sid_arr_QF[0]"); echo "<br>";
//		}
	}
	else{
		//log_info_DB("No status message received from CREATE_SESSION api call - Clearing session", NULL, NULL, 0, $dblink); 
		clear_session($username, $password);
	}
	}
	else{
		//log_info_DB("CurlHandlerCalls function failed - Clearing session", NULL, NULL, 0, $dblink);
		
		clear_session($username, $password);
	}
	
	
}

//	echo "<pre";
//	print_r($cinfo);
//	echo "</pre>";
?>
