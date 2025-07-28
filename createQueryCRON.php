<?php
include 'functions.php';
$username="seu346";
$password="HnDrfxY9CzjP732";
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
		// now calling query files
		//echo "\r\nCalling query_files\r\n";
		$nowQueryFiles=date("Y-m-d H:i:s"); 
		$data1="uid=$username&sid=$sid";
		$apiURL1="https://cs4743.professorvaladez.com/api/query_files";
		$results_QF=curlHandlerCalls($data1, $apiURL1);
		if($results_QF != 0){
			$cinfo_QF=json_decode($results_QF, true);
			// $cinfo_QF[0] -> should be Status
			// $cinfo_QF[1] -> should be files payload (if successful) errors otherwise
			// $cinfo_QF[2] -> should be Action: Continue (if succesful) Action: (if unsuccessful)
			$status_arr_QF = explode(":", $cinfo_QF[0]);
			$msg_arr_QF = explode(":", $cinfo_QF[1]);
			$action_arr_QF = explode(":", $cinfo_QF[2]);
			print_r($status_arr_QF); echo "\r\n";
			print_r($msg_arr_QF); echo "\r\n";
			print_r($action_arr_QF); echo "\r\n";

		
			// CHECKING STATUS INFO
			if((strstr($status_arr_QF[0], "Status")) && (strstr($status_arr_QF[1], "ERROR")) ){
				echo "\r\n$nowQueryFiles:createQueryCRON.php:failure:success:Status:$status_arr[1]:MSG:$msg_arr_QF[1]:Action:$action_arr_QF[1]\r\n";
				close_session($sid); // logging out 
				die(); // finishing the script DONE
			}
			else if((strstr($status_arr_QF[0], "Status")) && (strstr($status_arr_QF[1], "OK")) ){
				echo "\r\n$nowQueryFiles:createQueryCRON.php:success:Status:$status_arr[1]:MSG:$msg_arr_QF[1]:Action:$action_arr_QF[1]\r\n";
				// need to check msg - because it can save no new files found
				if((strstr($msg_arr_QF[1], "No new files found"))){
					echo "\r\n$nowQueryFiles:createQueryCRON.php:success:Status:$status_arr[1]:MSG:$msg_arr_QF[1]:Action:$action_arr_QF[1]\r\n";
					// since we don't know what is going on we will just log out
					close_session($sid); // logging out 
					die(); // finishing the script DONE
				}
				else{
					$payload=json_decode($msg_arr_QF[1]);
					//return $payload;
					echo "\r\n";
					print_r($payload);
					echo "\r\n";
					// writing to /tmp directory here
					$nowSavingtoTmp=date("Y-m-d H:i:s");
					foreach($payload as $key=>$value){	
						try{
							
							$fp=fopen("/var/www/files/tmp/$value", "w");
							if(!$fp){
								throw new Exception('File open failed');
							}
							try{
								$content = "Request file content";
								$fw=fwrite($fp, $content);
								if(!$fw){
									throw new Exception("Writing file $value failed!");
								}
								else{
									echo ' ' . $nowSavingtoTmp . ':createQueryCRON.php:success:MSG:File ' . $value . 'written to file system\r\n';
								}
							} catch (Exception $e){
								echo ' ' . $nowSavingtoTmp . ':createQueryCRON.php:failure:MSG:' .  $e->getMessage() . '\r\n';
							}
							fclose($fp);
							echo ' ' . $nowSavingtoTmp . ':createQueryCRON.php:success:MSG:File ' . $value . ' opened\r\n';
						} catch (Exception $e){
							echo ' ' . $nowSavingtoTmp . ':createQueryCRON.php:failure:MSG:' .  $e->getMessage() . '\r\n';
						}
					}
				}
				// since status is OK we know out file payload is here so now here write it to the /tmp directory
				print "Message:"; print_r($msg_arr_QF[1]); echo "\r\n";
				
			}
			else{
				echo "\r\n$nowQueryFiles:createQueryCRON.php:unknown:Status:$status_arr[1]:MSG:$msg_arr_QF[1]:Action:$action_arr_QF[1]\r\n";
				// since we don't know what is going on we will just log out
				close_session($sid); // logging out 
				die(); // finishing the script DONE
			}
		}
		else{ // this means that the query files was not a successful call
			$nowFail=date("Y-m-d H:i:s");
			echo "\r\n$nowFail:createQueryCRON.php:failure:MSG:$cinfo_createSession[2] calling clear_session()\r\n";
			clear_session($username, $password);
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