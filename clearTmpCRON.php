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
	if($cinfo_createSession[0]=="Status: OK"){ // this means successfully created session
		$nowCreatedSession=date("Y-m-d H:i:s");
		print_r($cinfo_createSession);
		$status_arr = explode(":", $cinfo_createSession[0]); // we already know this is OK - not needed
		$msg_arr = explode(":", $cinfo_createSession[1]); // this should be "Session created"
		$sid = $cinfo_createSession[2]; // this should be the sid of the created session
		//$sid_arr = explode(":", $cinfo_createSession[2]); // this should be the sid
		echo "\r\n$nowCreatedSession:createQueryCRON.php:success:MSG:$msg_arr[1]:$sid\r\n";
//		print_r($status_arr); echo "\r\n";
//		print_r($msg_arr); echo "\r\n";
//		print_r($sid_arr); echo "\r\n";
		
		// here query the db if ther documents in /tmp are already in DB
		$dir = '/var/www/files/tmp';
		$files = array_diff(scandir($dir), array('..', '.'));
		print_r($files);
		foreach($files as $key=>$value){	
			$nowInsertingFile=date("Y-m-d H:i:s");
			try{
				$checkstmt = mysqli_query($dblink, "Select DOCUMENT_TITLE from DOCUMENTS where `DOCUMENT_TITLE`='$value'");
				$checkstmtUNKNOWN =  mysqli_query($dblink, "Select DOCUMENT_TITLE from UNKNOWN_DOCS where `DOCUMENT_TITLE`='$value'");
				if(!$checkstmt){
					throw new Exception("Checking if DOCUMENT_TITLE already exists in DOCUMENTS failed\n");
				}
				if(!$checkstmtUNKNOWN){
					throw new Exception("Checking if DOCUMENT_TITLE already exists in UNKNOWN_DOCS failed\n");
				}
				else{
					// check the results returned from the query
					if((mysqli_num_rows($checkstmt) > 0) || (mysqli_num_rows($checkstmtUNKNOWN) > 0)){
						// this means that this document title already exists in the database 
						// UNLINK FILE FROM file system
						$path = "/var/www/html/files/tmp/$value";
						unlink($path);
						echo "\r\n$nowInsertingFile:createTmpCRON.php:success:MSG:File found in DB successfully unlinked!\r\n";

					}
					else if (mysqli_num_rows($checkstmt) == 0){
						// this means that there is no document with this name in the DOCUMENTS database leave as is the AUDIt will fix it later 
						// clear the session
						$nowDone=date("Y-m-d H:i:s");
						echo "\r\n$nowDone:createTmpCRON.php:failure:MSG: this file in not in the DB but audit will fix this later\r\n";
						continue;
					}
				}
			}catch (Exception $e){
				echo "$nowInsertingFile:clearTmpCRON.php:failure:MSG:LOAN table checking if record already exists FAILED " . $e->getMessage() . "\n";
			}
		}
	}
	else{ // this means that the session was not successfully created
		if($cinfo_createSession[2] == "Action = Must clear session first"){
			// clear the session
			$nowFail=date("Y-m-d H:i:s");
			echo "\r\n$nowFail:createQueryCRON.php:failure:MSG:$cinfo_createSession[2] calling clear_session()\r\n";
			close_session($sid);
		}	
	}	
}
else{ // this means unsuccessful API call execution
	$nowFail=date("Y-m-d H:i:s");
	echo "\r\n$nowFail:createQueryCRON.php:failure:MSG:Must clear session first\r\n";
	clear_session($username, $password);
	die();
}
clear_session($username, $password);

?>