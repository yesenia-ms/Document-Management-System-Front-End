<?php
include 'functions.php';
date_default_timezone_set('America/Chicago');
$username="seu346";
$password="HnDrfxY9CzjP732";
$dblink=db_connect("docStoragefa24");
// before calling create session always check if there is another session open by calling clear session
clear_session($username, $password);
$data="username=$username&password=$password";
// calling create session - "logging in"
$apiURL="https://cs4743.professorvaladez.com/api/create_session";
$results=curlHandlerCalls($data, $apiURL); // standard for every cURL handler
if($results != 0){ // this means successful api call and execution from API call
	//$cinfo[0] -> should be Status
	//$cinfo[1] -> should be MSG
	//$cinfo[2] -> should be sid (if successful) or errors (if unsuccessful)
	$cinfo_createSession=json_decode($results, true); // converts JSON to assoc array
	if($cinfo_createSession == true){
		print_r('$cinfo is true');
		print_r($cinfo_createSession);
		if($cinfo_createSession[0]=="Status: OK"){ // this means successfully created session
			$nowCreatedSession=date("Y-m-d H:i:s");
			$status_arr = explode(":", $cinfo_createSession[0]); // we already know this is OK - not needed
			$msg_arr = explode(":", $cinfo_createSession[1]); // this should be "Session created"
			$sid = $cinfo_createSession[2]; // this should be the sid of the created session
			//$sid_arr = explode(":", $cinfo_createSession[2]); // this should be the sid
			echo "\r\n$nowCreatedSession:auditFilesByLoan.php:success:MSG:$msg_arr[1]:$sid\r\n";
//			print_r($status_arr); echo "\r\n";
//			print_r($msg_arr); echo "\r\n";
//			print_r($sid_arr); echo "\r\n";
			// now we need to read the /tmp directory and request those files and save it into a the DB
			// now for payload foreach loop and call request file
			$apiURL2="https://cs4743.professorvaladez.com/api/request_file_by_loan";
			request_file_by_loan($sid, $username, $apiURL2, $dblink);
		}
		else{ // this means that the session was not successfully created
			if($cinfo_createSession[2] == "Action: Must clear session first"){
				// clear the session
				$nowFail=date("Y-m-d H:i:s");
				echo "\r\n$nowFail:auditFilebyLoan.php:failure:MSG:$cinfo_createSession[2] calling clear_session()\r\n";
				$oldsid=clear_session($username, $password);
				close_session($oldsid);
				die();
			}	
		}	
	}
	else if ($cinfo_createSession == false || $cinfo_createSession == null){
		print_r('$cinfo is false or null');
		print_r($cinfo_createSession);
		clear_session($username, $password);
	}	
}
else{ // this means unsuccessful API call execution
	$nowFail=date("Y-m-d H:i:s");
	echo "\r\n$nowFail:auditFileByLoan.php:failure:MSG:Must clear session first\r\n";
	clear_session($username, $password);
	die();
}
clear_session($username, $password);

?>
