<?php
include 'functions.php';
$username="seu346";
$password="HnDrfxY9CzjP732";
// before calling create session always check if there is another session open by calling clear session
clear_session($username, $password);
// standard for every cURL handler
$sid = create_session($username, $password);
echo "Session id returned from create_session: $sid";
$nowQueryFiles=date("Y-m-d H:i:s"); 
$payload=request_all_documents($username, $sid); // this will return the payload of documents
echo "Printing payload from the auditDocsCRON.php\r\n";
print_r($payload);
echo "\r\n";
// writing to /tmp directory here
$nowSavingtoTmp=date("Y-m-d H:i:s");
foreach($payload as $key=>$value){	
	// request file here and get the content 
	// and write the content to the file
//	echo "Inside the foreach loop";
//	echo "sid:$sid";
//	echo "username:$username";
//	echo "filename: $value";
//	$returned=request_file($sid, $username, $password, $value);
//	$content = $returned[0];
//	$sid = $returned[1];
//	if(strstr($content, "ERROR")){
//		continue;
//	}
//	else{
		// now write to the file system
		try{
			$fp=fopen("/var/www/files/audit/$value", "w");
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
					echo ' ' . $nowSavingtoTmp . ':auditDocsCRON.php:success:MSG:File ' . $value . 'written to file system\r\n';
				}
			} catch (Exception $e){
				echo ' ' . $nowSavingtoTmp . ':auditDocsCRON.php:failure:MSG:' .  $e->getMessage() . '\r\n';
			}
			fclose($fp);
			echo ' ' . $nowSavingtoTmp . ':auditDocsCRON.php:success:MSG:File ' . $value . ' opened\r\n';
		} catch (Exception $e){
			echo ' ' . $nowSavingtoTmp . ':auditDocsCRON.php:failure:MSG:' .  $e->getMessage() . '\r\n';
		}
//	}
	

}
clear_session($username, $password);
?>
