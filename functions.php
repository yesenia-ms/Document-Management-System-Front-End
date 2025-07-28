<?php
$username="seu346";
$password="HnDrfxY9CzjP732";
date_default_timezone_set('America/Chicago');
function curlHandlerCalls($data, $apiURL){
	$nowAPICall=date("Y-m-d H:i:s");
	$ch=curl_init($apiURL);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
	curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded',
		'content-length: '. strlen($data)));
	$time_start=microtime(true);
	$result=curl_exec($ch);
	$time_end=microtime(true);
	if(curl_errno($ch)){
		$execution_time_failed=($time_end-$time_start)/60;
		echo '\r\n' . $nowAPICall . ':functions.php - curlHandlerCalls():failure:MSG:' . curl_error($ch) . ':Execution time:' . $execution_time_success . '\r\n';
		curl_close($ch);
		return 0; // return false for failed curl execution
	}
	else{
		$execution_time_success=($time_end-$time_start)/60;
		echo "\r\n$nowAPICall:functions.php - curlHandlerCalls():success:MSG:Successful execution of $apiURL cURL session:Execution time: $execution_time_success\r\n";
//		print_r("Successful execution of given cURL session"); echo "\r\n";
//		print_r("execution time: $execution_time_success"); echo "\r\n";
		curl_close($ch);
		return $result; // returns results for successful curl execution
	}
	
	
}
function redirect($uri)
{ ?> 
	<script type="text/javascript">
	<!--
	document.location.href="<?php echo $uri; ?>";
	-->
	</script>
<?php die;
}
function workInsertAUDIT($content, $nowPayload, $value, $dblink){
		// value is the file name that is passed to this function
		$call="workInsertAUDIT";
		//successfull call
		$nowInserting=date("Y-m-d H:i:s");
		$docName=addslashes($value);
		//need to extract upload date and time
		//and the doc_type (finacial, mou, etc)
		//and loan number
		$titleEx=explode(".", $docName); //0 name 1 .pdf
		$loanTypeDate=explode("-",$titleEx[0]);
		$docTypeVersion=explode("_",$loanTypeDate[1]);
		$dateYMD=explode("_",$loanTypeDate[2]); //0 ymd
		$strdate=strval($dateYMD[0]);
		$day=substr($strdate,6);
		$month=substr($strdate,4,-2);
		$year=substr($strdate,0,4);
		// variables to be inserted into DB
		$docTitle=$titleEx[0];
		$docSize=strlen($content);
		$uploadDate=$year."-".$month."-".$day;
		$uploadTime=$dateYMD[1].":".$dateYMD[2].":".$dateYMD[3];
		$docEx=$titleEx[1];
		$docType=$docTypeVersion[0];
		//mime type here logic below
		$loanId=$loanTypeDate[0];
		$uploadType="CRON";	
		$contentClean=addslashes($content);
		
		//now check mime type and erase from file system
		// don't need to write it to the file system again it is already there just check the mime type and then unlink it in the logic
	
	
		try{
			$fp=fopen("/var/www/files/audit/$value", "w");
			if(!$fp){
				throw new Exception('File open failed');
			}
			try{
				$fw=fwrite($fp, $content);
				if(!$fw){
					throw new Exception("Writing file $value failed!");
				}
				else{
					echo ' ' . $nowInserting . ':workInsertAUDIT.php:success:MSG:File ' . $value . 'written to file system\r\n';
				}
			} catch (Exception $e){
				echo ' ' . $nowInserting . ':workInsertAUDIT.php:failure:MSG:' .  $e->getMessage() . '\r\n';
			}
			fclose($fp);
			echo ' ' . $nowInserting . ':workInsertAUDIT.php:success:MSG:File ' . $value . ' opened\r\n';
		} catch (Exception $e){
			echo ' ' . $nowInserting . ':workInsertAUDIT.php:failure:MSG:' .  $e->getMessage() . '\r\n';
		}
		$check="/var/www/html/files/audit/$value";
		$mimeType=mime_content_type($check);
		try{
			$checkstmtDoc = mysqli_query($dblink, "Select DOCUMENT_TITLE from AUDIT_DOCUMENTS where `DOCUMENT_TITLE`='$docTitle'");
			if(!$checkstmtDoc){
				echo "\r\n$nowPayload:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table checking if record already exists FAILED!";
				throw new Exception("Checking if DOCUMENT_TITLE title already exists in AUDIT_DOCUMENTS table failed\n");
			}
			else{
				if((mysqli_num_rows($checkstmtDoc) > 0)){
				// this means that this document title already exists in the database 
				// this means that it is in the DB good continue
					echo "\r\n$nowInserting:functions.php - checking if doc title exists in AUDIT_DOCS:success:MSG:$value already exists in AUDIT_DOCUMENTS table";
					unlink($check);
					echo "\r\n$nowInserting:functions.php - requestfilebyLoan:success:MSG:$value successfully unlinked\r\n";
				}
				else if (mysqli_num_rows($checkstmtDoc) == 0){ // this means this file does not exist in either DB so insert it
					try{
						$now=date("Y-m-d H:i:s");
						$stmt2=mysqli_query($dblink, "Insert into `AUDIT_DOCUMENTS` (`DOCUMENT_TITLE`, `DOC_SIZE`, `UPLOAD_DATE`, `UPLOAD_DATE_TIME`, `DOC_EXTENSION`, `DOC_TYPE`, `DOC_MIME_TYPE`, `LOAN_NUMBER`, `UPLOAD_TYPE`, `INSERT_DATE`) values ('$docTitle', '$docSize', '$uploadDate', '$uploadTime', '$docEx', '$docType', '$mimeType','$loanId', '$uploadType','$now')");
						//$executeSuccess2=mysqli_stmt_execute($stmt2);
						if(!$stmt2){
							echo "\r\n$nowInserting:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table inserting FAILED</h4>";
							throw new Exception('Inserting into AUDIT_DOCUMENTS table failed!');
						}
						else{
							echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully inserted into DOCUMENTS table\r\n";
							unlink($check);
							echo "\r\n$nowInserting:functions.php - workInsertAUDIT after the insertion:success:MSG:$value successfully unlinked\r\n";
						}
					}catch (Exception $e){
						echo "\r\n$nowInserting:functions.php - $call:failure:MSG:" .$e->getMessage()."\r\n";
					}
				}
			}
		}catch (Exception $e){ // checks if document exists in AUDIT_DoCUMENTS table
			echo "\r\n$nowInserting:functions.php - $call:failure:MSG:". $e->getMessage(). "\r\n";

		}
	// trying to insert into AUDIT_DOCS_CONTENT
	try{
		$checkstmtDoc = mysqli_query($dblink, "Select DOCUMENT_TITLE from AUDIT_DOCS_CONTENT where `DOCUMENT_TITLE`='$docTitle'");
		if(!$checkstmtDoc){
			echo "\r\n$nowPayload:functions.php - $call:failure:MSG:AUDIT_DOCS_CONTENT table checking if record already exists FAILED!";
			throw new Exception("Checking if DOCUMENT_TITLE title already exists in AUDIT_DOCS_CONTENT table failed\n");
		}
		else{
			if((mysqli_num_rows($checkstmtDoc) > 0)){
				// this means that this document title already exists in the database 
				// this means that it is in the DB good continue
				echo "\r\n$nowInserting:functions.php - checking if doc title exists in AUDIT_DOCS_CONTENT:success:MSG:$value already exists in AUDIT_DOCS_CONTENT table";
			}
			else if (mysqli_num_rows($checkstmtDoc) == 0){ // this means this file does not exist in either DB so insert it
				try{
					$now=date("Y-m-d H:i:s");
					$stmt3=mysqli_query($dblink, "Insert into `AUDIT_DOCS_CONTENT` (`DOCUMENT_TITLE`, `DOC_CONTENT`, `UPLOAD_DATE`) values ('$docTitle', '$contentClean', '$nowInserting')");
					if(!$stmt3){
						echo "\r\n$nowInserting:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table inserting FAILED</h4>";
						throw new Exception('Inserting into AUDIT_DOCS_CONTENT table failed!');
					}
					else{
						echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully inserted into AUDIT_DOCS_CONTENT table\r\n";
					}
				}catch(Exception $e){
					echo "\r\n$nowInserting:functions.php - $call:failure:MSG:Failed to insert into AUDIT_DOCS_CONTENT".$e->getMessage()."\r\n";

				}
			}
		}	      
	}catch(Exception $e){
		echo "\r\n$nowInserting:functions.php - $call:failure:MSG:" .$e->getMessage()."\r\n";		
	}	
	return;
}
function workInsert($result, $nowPayload, $value, $dblink, $username, $password, $call){
// response is a PDF file - if you get an error you get a JSON response<br>
	$numFiles = 0;
	$fileSizePayload = 0;
	if(strstr($result, "Status")){
		$cinfo=json_decode($result, true); // converts JSON to assoc array
		$status_arr = explode(":", $cinfo[0]);
		$msg_arr = explode(":", $cinfo[1]);
		$action_arr = explode(":", $cinfo[2]);
		if(strstr($msg_arr[1], "SID not found")){
			// create a new session
			echo "\r\n$nowPayload:functions.php - $call:failure:MSG:$msg_arr[1]\r\n";
			close_session($username, $password);
			create_session($username, $password);
			//continue;
		}
		else{
			echo "\r\n$nowPayload:functions.php - $call:unknown:MSG:$msg_arr[1]\r\n";
		}
		//continue;
	}
	else{ // THIS MEANS SUCCESSFUL
		$content=$result;
		$nowInserting=date("Y-m-d H:i:s");
		// write to the db
		$docName = addslashes($value);
		$docSize = strlen($content);
		$now=date("Y-m-d H:i:s");
		$compIncomp="complete";
		$copies=1;
		$docType=explode(".", $docName);
		$title=explode("-", $docName);
		$requireDN=strtolower($title[1]);
		$loanNum=$title[0];
		$contentClean=addslashes($content);
		// inserting into loan table variables
		$insertedDocType = ""; $titleC = 0; $creditC = 0; $closingC = 0; $financialC = 0; $personalC = 0;$internalC = 0;$legalC = 0;$mouC = 0;
		$disclosureC = 0; $taxC = 0;
		// logging info variables
		$numFiles++;
		$fileSizePayload += $docSize;
		$name;
		// getting the file type
		if(str_contains($requireDN, "title")){
			$insertedDocType = "Title";
			$titleC = 1;
		}else if(str_contains($requireDN, "credit")){
			$insertedDocType = "Credit Report";
			$creditC = 1;
		}
		else if(str_contains($requireDN, "closing")){
			$insertedDocType = "Closing";
			$closingC = 1;
		}
		else if(str_contains($requireDN, "financial")){
			$insertedDocType = "Financial";
			$financialC = 1;
		}
		else if(str_contains($requireDN, "personal")){
			$insertedDocType = "Personal";
			$personalC = 1;
		}
		else if(str_contains($requireDN, "internal")){
			$insertedDocType = "Internal";
			$internalC = 1;
		}
		else if(str_contains($requireDN, "legal")){
			$insertedDocType = "Legal";
			$legalC = 1;
		}
		else if(str_contains($requireDN, "mou")){
			$insertedDocType = "MOU";
			$mouC = 1;
		}
		else if(str_contains($requireDN, "disclosure")){
			$insertedDocType = "Disclosure";
			$disclosureC = 1;
		}
		else if(str_contains($requireDN, "tax")){
			$insertedDocType = "Tax Record";
			$taxC = 1;
		}
		// now insert into loan table 
		try{
			$checkstmt = mysqli_query($dblink, "Select LOAN_NUMBER from LOAN where `LOAN_NUMBER`='$loanNum'");
			if(!$checkstmt){
				echo "\r\n$nowPayload:functions.php - $call:failure:MSG:LOAN table checking if record already exists FAILED!";
				throw new Exception("Checking if LOAN number already exists failed\n");
			}
			else{
				// check the results returned from the query
				if(mysqli_num_rows($checkstmt) > 0){
					// this means that this loan number already exists in the database so do not insert this file into LOAN table
					echo "\r\n$nowPayload:functions.php - $call:success:MSG:$loanNum already exists in LOAN table in DB";
				}
				else if (mysqli_num_rows($checkstmt) == 0){
					// this means that this loan number is not in the database yet, so insert it
					// we need to determine the document type based on the title of the document
					try{
						$name = "Insert new LOAN NUMBER into LOAN table";
						$stmt1=mysqli_query($dblink, "Insert into `LOAN` (`LOAN_NUMBER`, `COMPLETE`, `NUM_DOCUMENT`, `TITLE_COMPLETE`, 	`CREDIT_COMPLETE`, `CLOSING_COMPLETE`, `FINANCIAL_COMPLETE`, `PERSONAL_COMPLETE`, `INTERNAL_COMPLETE`, `LEGAL_COMPLETE`, `MOU_COMPLETE`, `DISCLOSURE_COMPLETE`, `TAX_RECORD_COMPLETE`, `UPLOAD_DATE`) values ('$loanNum', '0', '1', '$titleC', '$creditC', '$closingC', '$financialC', '$personalC', '$internalC', '$legalC', '$mouC', '$disclosureC', '$taxC', '$nowInserting')");
						// as of right now we are only inserting into LOAN table the loan number
						// as we get the documents we will update the loan table
						//$executeSuccess=mysqli_stmt_execute($stmt1);
						echo "\r\n$nowInserting:functions.php - $call:success:MSG:$loanNum successfully inserted into DB";
						if(!$stmt1){
							echo "\r\n$nowPayload:functions.php - $call:failure:MSG:LOAN table inserting FAILED!";
							throw new Exception("Inserting to LOAN table failed!");
						}
					}catch (Exception $e){
						echo "\r\n$nowInserting:functions.php - $call:failure:MSG:". $e->getMessage()."\r\n";
					}
				}
			}
		}catch (Exception $e){
			echo "\r\n$nowInserting:functions.php - $call:failure:MSG:". $e->getMessage()."\r\n";
		}
		// the user executing this code is www-data: web user for nginx 
		// when this script is executed to the web 
		if(strlen($content)== 0){
			echo "\r\n$nowPayload:functions.php - $call:failure:MSG:File $value received zero length\r\n";
			//continue; // don't insert the file into document it will request it again - IDK
		}
		else{
			if((strcmp($call, "request_all_docs"))== 0){
				try{
					$fp=fopen("/var/www/files/tmp/$value", "w");
					if(!$fp){
						throw new Exception('File open failed');
					}
					try{
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
			// CHECK MIME TYPE
			// saving it to the file system
			//request_file_api_call($payload, $sid, $username, $apiURL);
			// check the mime type
			$filecheckpath="/var/www/html/files/tmp/$value";
			$mimetype = mime_content_type($filecheckpath);
		//	$mimetype = mime_content_type($value);
			if(strcmp($mimetype, 'application/pdf')){
				$nowBeginInsert=time();
				$nowEndInsert;
				$entireDocInsertion;
				try{
					$checkstmtDoc = mysqli_query($dblink, "Select DOCUMENT_TITLE from DOCUMENTS where `DOCUMENT_TITLE`='$value'");
					$checkstmtUNKNOWN =  mysqli_query($dblink, "Select DOCUMENT_TITLE from UNKNOWN_DOCS where `DOCUMENT_TITLE`='$value'");
					if(!$checkstmtDoc){
						echo "\r\n$nowPayload:functions.php - $call:failure:MSG:DOCUMENTS table checking if record already exists FAILED!";
						throw new Exception("Checking if DOCUMENT_TITLE title already exists in DOCUMENTS table failed\n");
					}
					if(!$checkstmtUNKNOWN){
						echo "\r\n$nowPayload:functions.php - $call:failure:MSG:UNKNOWN_DOCS table checking if record already exists FAILED!";
						throw new Exception("Checking if DOCUMENT_TITLE already exists in UNKNOWN_DOCS table failed\n");
					}
					else{
						if((mysqli_num_rows($checkstmt) > 0) || (mysqli_num_rows($checkstmtUNKNOWN) > 0)){
							// this means that this document title already exists in the database 
							// this means that it is in the DB good continue
							echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value already exists in DOCUMENTS table in DB or UNKNOWN table in DB";
							update_table($dblink, $username, $password, $requireDN, $loanNum);
							unlink($filecheckpath);
							echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully unlinked\r\n";
						}
						else if (mysqli_num_rows($checkstmtDoc) == 0 && mysqli_num_rows($checkstmtUNKNOWN) == 0){ // this means this file does not exist in either DB so insert it
							try{
								$name = "Inserting $value DOCUMENTS table";
								$stmt2=mysqli_query($dblink, "Insert into `DOCUMENTS` (`DOCUMENT_TITLE`, `DOC_SIZE`, `UPLOAD_DATE`, `STATUS`, `DOC_TYPE`, `LOAN_NUMBER`, `DOC_CONTENT`, `UPLOAD_TYPE`) values ('$docName', '$docSize', '$now', '$compIncomp', '$docType[1]', '$loanNum', '$contentClean', 'cron')");
								//$executeSuccess2=mysqli_stmt_execute($stmt2);
								if(!$stmt2){
									echo "\r\n$nowInserting:functions.php - $call:failure:MSG:DOCUMENTS table inserting FAILED</h4>";
									throw new Exception('Inserting into DOCUMENTS table failed!');
								}
								else{
									$nowEndInsert=time();
									echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully inserted into DOCUMENTS table\r\n";
									$entireDocInsertion = $nowEndInsert - $nowBeginInsert;
									// after this successfull insertion update the loan table if complete
									// if we have this document of not etc.// after inserted into documents table then we need to update the loan table // if it is not updated
									//$updatestmt = "Update `LOAN` SET (``)";
									try{
										update_table($dblink, $username, $password, $requireDN, $loanNum);											
									}catch(Exception $e){
										echo "\r\n$nowInserting:functions.php - $call:failure:MSG:Failed to check if $loanNum exists in DB\r\n";
									}
								}
							}catch (Exception $e){
								$nowEndInsert=time();
								$entireDocInsertion = $nowEndInsert - $nowBeginInsert;
								echo "\r\n$nowInserting:functions.php - $call:failure:MSG:" .$e->getMessage()."\r\n";
								//log_info_DB("Failed insertion of $value", $entireDocInsertion, $docSize, 1, $dblink); //failed insertion doc-insertion log 
							}

						}
					}
				}catch (Exception $e){
					echo "\r\n$nowPayload:functions.php - $call:failure:MSG:". $e->getMessage(). "\r\n";
				}	
			}
			else{  // this means that the mime type is not application/pdf - insert into UNKNOWN table in db
				$nowInsertingUK=date("Y-m-d H:i:s");
//					$docName=addslashes($value);
//					$docSize=strlen($content);
//					$now=date("Y-m-d H:i:s");
//					$compIncomp="complete";
//					$docType=explode(".",$docName);
//					$title=explode("-",$docName);
//					$loanNum=$title[0];
//					$contentClean=addslashes($content);
				// write this file to db DOCUMENTS table in unknown file table
				$nowBeginInsertUK=time();
				$nowEndInsertUK;
				$entireDocInsertionUK;

				// now inserting into UNKNOWN DOCS table
				try{
					$stmt3=mysqli_query($dblink, "Insert into `UNKNOWN_DOCS` (`DOCUMENT_TITLE`, `DOC_SIZE`, `UPLOAD_DATE`, `STATUS`, `DOC_TYPE`, `LOAN_NUMBER`, `DOC_CONTENT`) values ('$docName', '$docSize', '$now', '$compIncomp', '$mimetype', '$loanNum', '$contentClean')");
					//$executeSuccess3=mysqli_stmt_execute($stmt2);
					if(!$stmt3){
						echo "\r\n$nowInsertingUK:functions.php - $call:failure:MSG:UNKNOWN_DOCS table inserting FAILED\r\n";
						throw new Exception('Inserting into UNKNOWN_DOCS table failed!');
					}
					else{
						// successful insertion of document into table
						$nowEndInsertUK=time();
						echo "\r\n$nowInsertingUK:functions.php - $call:success:MSG:UNKNOWN_DOCS table SUCCESSFULLY inserted\r\n";
						$entireDocInsertionUK = $nowEndInsertUK - $nowBeginInsertUK;
						try{
							update_table($dblink, $username, $password, $requireDN, $loanNum);											
						}catch(Exception $e){
							echo "\r\n$nowInserting:functions.php - $call:failure:MSG:Failed to check if $loanNum exists in DB\r\n";
						} 
					}
				}catch (Exception $e){
					$nowEndInsertUK=time();
					echo "\r\n$nowInsertingUK:functions.php - $call:failure:MSG:".$e->getMessage()."\r\n";
					$entireDocInsertionUK = $nowEndInsertUK - $nowBeginInsertUK;
					//log_info_DB($e->getMessage(), $entireDocInsertionUK, $docSize, 1, $dblink); // successful insertion of doc into insertion log
				}
			}
			// if(correct mime type then write to db and then unlink from file system)
			// else (insert into table of unknown file types in documents table)
		}
	}
}
function request_file_api_call($payload, $sid, $username, $apiURL){
	foreach($payload as $key=>$value){
		$data2="sid=$sid&uid=$username&fid=$value";
		$result=curlHandlerCalls($data2, $apiURL);
		// response is a PDF file - if you get an error you get a JSON response
		if(strstr($result, "Status")){
			echo "<h2>There was an error with file: $value</h2>";
			echo "<pre>";
			echo $result;
			echo "</pre>";
//			close_session($sid);
//			die();
			continue; // CHANGE THIS FIGURE OUT WHAT TO DO
			// should we ask for this file again or WHAT???
		}
		else{
			$content=$result;
			// the user executing this code is www-data: web user for nginx 
			// when this script is executed to the web 
			if(strlen($content)== 0){
				echo "<h4>File $value received zero length</h4>";
				continue; // CHANGE THIS FIGURE OUT WHAT TO DO
			}
			else{
				try{
					$fp=fopen("/var/www/files/tmp/$value", "wb");
					if(!$fp){
						throw new Exception('File open failed!');
						//log_error_DB();
					}
					try{
						$fw=fwrite($fp, $content);
						if(!$fw){
							throw new Exception("Writing file $value failed!");
							//log_error_DB();
						}
					}
					catch (Exception $e){
						echo 'Caught exception: ', $e->getMessage(), "\n";
						//log_error_DB();
					}
					fclose($fp);
				} catch (Exception $e){
					echo 'Caught exception: ', $e->getMessage(), "\n";
					//log_error_DB();
				}
				echo "<h3>File $value written to file system</h3>";
				//log_info_DB();
			}
		}
	}
	
}
function request_file_api_call_DB($payload, $sid, $username, $apiURL, $dblink){
	$username="seu346";
	$password="HnDrfxY9CzjP732";
	$numFiles = 0;
	$fileSizePayload = 0;
	$nowBegin=time();
	$nowStart=date("Y-m-d H:i:s");
	echo "\r\n$nowStart:functions.php - request_file_api_call_DB:success:MSG:Session id - $sid\r\n";
	foreach($payload as $key=>$value){
		if(((strcmp($value, '.')) == 0) || ((strcmp($value, '..')) == 0)){
			continue; // invalid files
		}
		$nowPayload=date("Y-m-d H:i:s");
		$data2="sid=$sid&uid=$username&fid=$value";
		// Calling request file for each file in the json array returned from the query_files API call
		$result=curlHandlerCalls($data2, $apiURL);
		$call = "request_file_api_call_DB";
		workInsert($result, $nowPayload, $value, $dblink, $username, $password, $call);
	}
	// log the insert data which is 
	// time it takes to complete entire process
	// # of files received
	// file size of the entire payload
	// how long it takes upload each file individually
	// call log function
	$nowEnd=time();
	$entireProcessTime = $nowEnd - $nowBegin;
	//log_info_DB("Empty payload received", $entireProcessTime, $fileSizePayload, $numFiles, $dblink);
	close_session($sid);
}
function request_all_documents($username, $sid){
	$nowCreate=date("Y-m-d H:i:s");
	echo "Session id in request_all_documents: $sid\n";
	echo "Username in request_all_documents: $username\n";
	$data="sid=$sid&uid=$username";
	echo "data: $data\n";
	$apiURL="https://cs4743.professorvaladez.com/api/request_all_documents";
	$results=curlHandlerCalls($data, $apiURL);
	$new;
	$results_requestAll=json_decode($results, true); // converts JSON to assoc array
	// TODO: if results return successfully
	if(str_contains($results_requestAll[1], "SID not found")){
		echo "\r\n$nowCreate:functions.php - request_all_documents():failure:MSG:SID not found\r\n";
		die; // its hard to do this one here so just give up and try again - this is uncommon
	}
	else if(str_contains($results_requestAll[0], "OK")){
		echo "\r\n$nowCreate:functions.php - request_all_documents():success:MSG:$results_requestAll[2]\r\n";
		$msg_arr = explode(":", $results_requestAll[1]);
		$payload = json_decode($msg_arr[1]);
		echo "\r\nPrinting payload from the request_all_documents function\r\n";
		print_r($payload);
		return $payload; // return the $payload of all the documents
	}
	else{
		echo "\r\n$nowCreate:functions.php - request_all_documents():failure:$results_requestAll[1]:$results_requestAll[2]\r\n";
		die;
	}
}
function request_file($sid, $username, $password, $fid){
	$nowCreate=date("Y-m-d H:i:s");
	$data="sid=$sid&uid=$username&fid=$fid";
	$apiURL="https://cs4743.professorvaladez.com/api/request_file";
	// request the file and then if there is an error SID not found you need to return 
	$results=curlHandlerCalls($data, $apiURL);
	$content=$results;
	if(strstr($results, "ERROR")){ //error
		$results_requestFile=json_decode($results, true); // converts JSON to assoc array
		$status_arr = explode(":", $results_requestFile[0]);
		$msg_arr = explode(":", $results_requestFile[1]);
		$action_arr = explode(":", $results_requestFile[2]);
		if(strstr($msg_arr[1], "SID not found")){
			// create a new session
			echo "\r\n$nowCreate:functions.php - auditDocsCRON:failure:MSG:$msg_arr[1]\r\n";
			clear_session($username, $password);
			//close_session($oldSID);
			$sid=create_session($username, $password);
			// need to return an array with content and sid
			$returned = request_file($sid, $username, $password, $fid);
			return $returned;  // need to return an array with content and sid

		}
		else{ // different kind of error
			echo "\r\n$nowCreate:functions.php - request_all_documents():failure:MSG:$msg_arr[1]:Action:$action_arr[1]\r\n";
			echo 'Content:'.$content.'\r\n';
			echo 'SID:'.$sid.'\r\n';
			return [$content, $sid]; // need to return an array with content and sid
		}
	}
	else{ // successful
		echo "\r\n$nowCreate:functions.php - request_all_documents():success:MSG:$fid was generated now can be written to file system in auditDocsCRON.php\r\n";
		return [$content, $sid]; // need to return an array with content and sid
	}
	
}
function request_all_docs($payload, $sid, $username, $apiURL, $dblink){
	$username="seu346";
	$password="HnDrfxY9CzjP732";
	$numFiles = 0;
	$fileSizePayload = 0;
	$nowBegin=time();
	$nowStart=date("Y-m-d H:i:s");
	$newSID;
	echo "\r\n$nowStart:functions.php - request_file_api_call_DB:success:MSG:Session id - $sid\r\n";
	foreach($payload as $key=>$value){
		if(((strcmp($value, '.')) == 0) || ((strcmp($value, '..')) == 0)){
			continue; // invalid files
		}
		$nowPayload=date("Y-m-d H:i:s");
		$data2="sid=$sid&uid=$username&fid=$value";
		$apiURL2="https://cs4743.professorvaladez.com/api/request_file";
		// Calling request file for each file in the json array returned from the query_files API call
		$result=curlHandlerCalls($data2, $apiURL2);
		$call = "request_file from request_all_docs from auditRequestAllDocumentsCRON";
		workInsertAUDIT($result, $nowPayload, $value, $dblink, $username, $password, $call);
		//echo "File name to request ".$value."\n";
	}
	
	// log the insert data which is 
	// time it takes to complete entire process
	// # of files received
	// file size of the entire payload
	// how long it takes upload each file individually
	//close_session($newSID);
	return;
}
function request_all_loans($sid, $username, $apiURL, $dblink){	
	$username="seu346";
	$password="HnDrfxY9CzjP732";
	$nowStart=date("Y-m-d H:i:s");
	$newSID;
	$newsession=0;
	echo "\r\n$nowStart:functions.php - request_all_loans:success:MSG:Session id - $sid\r\n";
	$nowPayload=date("Y-m-d H:i:s");
	$data2="sid=$sid&uid=$username";
	// Calling request file for each file in the json array returned from the query_files API call
	$result=curlHandlerCalls($data2, $apiURL);

	//print_r($result);
	$call = "request_all_loans";
	
	// start inserting into AUDIT_LOANS table
	$cinfo=json_decode($result);
	print_r($cinfo);
	$status_arr=explode(":",$cinfo[0]);
	$msg_arr=explode(":",$cinfo[1]);
	$action_arr=explode(":",$cinfo[2]);
	if(strstr($status_arr[1], "OK")){
		//this means bo errors
	}
	else{
		//errors otherwise - check which type;of error
		if(strstr($msg_arr[1], "SID not found")){
			close_session($sid);
			$newSID=create_session($username, $password);
			$newsession=1;
			$result=curlHandlerCalls($data2, $apiURL);
			
			$cinfo=json_decode($result);
			print_r($cinfo);
			$status_arr=explode(":",$cinfo[0]);
			$msg_arr=explode(":",$cinfo[1]);
			$action_arr=explode(":",$cinfo[2]);
		}
		else{
			echo "\r\n$nowStart:functions.php - request_all_loans:failure:MSG:Unknown Error\r\n";
			close_session($sid);
			die;
		}
	}	
	//right here all errors should be fixed everyone;can;do the next part
	$payload=json_decode($msg_arr[1]);
	$nowInserting=date("Y-m-d H:i:s");
	foreach($payload as $key=>$value){
		$loanNum=$value;
		//print_r($value);/
		echo "\r\n$nowInserting:functions.php - request_all_loans:value loanNum:$value\r\n";
		// now insert into loan table
		
		try{
			$checkstmt = mysqli_query($dblink, "Select LOAN_NUMBER from AUDIT_LOAN where `LOAN_NUMBER`='$loanNum'");
			if(!$checkstmt){
				echo "\r\n$nowPayload:functions.php - $call:failure:MSG:AUDIT_LOAN table checking if record already exists FAILED!";
				throw new Exception("Checking if LOAN number already exists in AUDIT_LOAN failed\n");
			}
			else{
				// check the results returned from the query
				if(mysqli_num_rows($checkstmt) > 0){
					// this means that this loan number already exists in the database so do not insert this file into LOAN table
					echo "\r\n$nowPayload:functions.php - $call:success:MSG:$loanNum already exists in AUDIT_LOAN table in DB";
				}
				else if (mysqli_num_rows($checkstmt) == 0){
					// this means that this loan number is not in the database yet, so insert it
					// we need to determine the document type based on the title of the document
					try{
						$stmt1=mysqli_query($dblink, "Insert into `AUDIT_LOAN` (`LOAN_NUMBER`, `COMPLETE`, `UPLOAD_DATE`) values ('$loanNum', 'No', '$nowInserting')");
						// as of right now we are only inserting into AUDIT_LOAN table the loan number
						// as we get the documents we will update the loan table
						echo "\r\n$nowInserting:functions.php - $call:success:MSG:$loanNum successfully inserted into AUDIT_LOAN_DB";
						if(!$stmt1){
							echo "\r\n$nowPayload:functions.php - $call:failure:MSG:AUDIT_LOAN table inserting FAILED!";
							throw new Exception("Inserting to AUDIT_LOAN table failed!");
						}
					}catch (Exception $e){
						echo "\r\n$nowInserting:functions.php - $call:failure:MSG:". $e->getMessage()."\r\n";
					}
				}
			}
		}catch (Exception $e){
			echo "\r\n$nowInserting:functions.php - $call:failure:MSG:". $e->getMessage()."\r\n";
		}

	// log the insert data which is 
	// time it takes to complete entire process
	// # of files received
		// file size of the entire payload
	}
	if($newsession){
		close_session($newSID);
		echo "\r\n$nowInserting:functions.php - requestallloans:success:MSG:closed session new;sid\r\n";
	}
	else
		close_session($sid);

}
function request_file_by_loan($sid, $username, $apiURL, $dblink){	
	$username="seu346";
	$password="HnDrfxY9CzjP732";
	$nowStart=date("Y-m-d H:i:s");
	$apiURL2="https://cs4743.professorvaladez.com/api/request_file";
	$newSID;
	$newsession=0;
	echo "\r\n$nowStart:functions.php - request_file_by_loan:success:MSG:Session id - $sid\r\n";
	$nowPayload=date("Y-m-d H:i:s");
	//need to get all loan numbers in db and then each loan id call the API call
	$sql="Select LOAN_NUMBER from AUDIT_LOAN";
	try{
		$execute=mysqli_query($dblink, $sql);
		if(!$execute){
			echo "\r\n$nowStart:functions.php - request_filebyloan:failure:MSG:Getting all loans from DB FAILED!";
			throw new Exception("Getting loans from AUDIT_LOAN failed\n");

		}
		else{
			//loop through the results and request files for each loan make the api call
			//save it to audit doc db
			while($row=mysqli_fetch_assoc($execute)){
				//print_r($row);
				$loanNum=$row["LOAN_NUMBER"];
				echo "loanNum: ".$loanNum."\n";
				$dataFile="sid=$sid&uid=$username&lid=$loanNum";
				$filesLoan=curlHandlerCalls($dataFile,$apiURL);
				$filesReceived=json_decode($filesLoan);
				$msg_arr=explode(":",$filesReceived[1]);
				if(strstr($filesReceived[0],"OK")){ //successful
					echo "\r\n$nowStart:Starting print the decoded api call\r\n";
					print_r($filesReceived);
					echo "\r\n$nowStart:Just printed the decoded api call\r\n";
					$files=json_decode($msg_arr[1]);
					echo "\r\n$nowStart:Starting to print the files\r\n";
					print_r($files);
					echo "\r\n$nowStart:Just printed the files\r\n";

					// with this information i can insert into AUDIT_DOCS
					foreach($files as $key=>$value){
						$call="requesting each file indiv.";
						$nowInserting=date("Y-m-d H:i:s");
						// request file individually here 
						$dataRequestFile="sid=$sid&uid=$username&fid=$value";
						$actualFile=curlHandlerCalls($dataRequestFile,$apiURL2);
						//check if successful
						$response=json_decode($actualFile);
						print_r($response);
						//$status_arr=explode(":",$response[0]);
						//$msg_arr=explode(":",$response[1]);
						//$action_arr=explode(":",$response[2]);
						//if(strstr($status_arr[1],"OK")){
						if(strstr($response[0], "Status")){
							$status_arr=explode(":",$response[0]);
							$msg_arr=explode(":",$response[1]);
							$action_arr=explode(":",$response[2]);
							if(strstr($msg_arr[1],"SID not found")){
								echo "\r\n$nowStart:functions.php - requesting each file sudden error:failure:MSG:". $msg_arr[1]."\r\n";
								clear_session($username,$password);
								$sid=create_session($username,$password);
								$dataRequestFile="sid=$sid&uid=$username&fid=$value";
								$actualFile=curlHandlerCalls($dataRequestFile,$apiURL2);
								goto logic;
							}		
						}
						else{
							logic:
							//successfull call
							$docName=addslashes($value);
							$content=$actualFile;
							//need to extract upload date and time
							//and the doc_type (finacial, mou, etc)
							//and loan number
							$titleEx=explode(".", $docName); //0 name 1 .pdf
							$loanTypeDate=explode("-",$titleEx[0]);
							$docTypeVersion=explode("_",$loanTypeDate[1]);
							$dateYMD=explode("_",$loanTypeDate[2]); //0 ymd
							$strdate=strval($dateYMD[0]);
							$day=substr($strdate,6);
							$month=substr($strdate,4,-2);
							$year=substr($strdate,0,4);

							// variables to be inserted into DB
							$docTitle=$titleEx[0];
							$docSize=strlen($content);
							$uploadDate=$year."-".$month."-".$day;
							$uploadTime=$dateYMD[1].":".$dateYMD[2].":".$dateYMD[3];
							$docEx=$titleEx[1];
							$docType=$docTypeVersion[0];
							//mime type here logic below
							$loanId=$loanTypeDate[0];
							$uploadType="CRON";	
							$contentClean=addslashes($content);
							//write file /var/www/html/file/auditLoan/$value
							try{
								$fp=fopen("/var/www/html/files/auditLoan/$value","w");
								if(!$fp){
									throw new Exception("File open failed");
								}
								try{
									$fw=fwrite($fp, $content);
									if(!$fw)
										throw new Exception("File writing failed");	
									else
										echo "\r\n$nowStart:functions.php - request_filebyiloan:success:MSG:Successfully wrote". $value."to file system\r\n";
								}catch(Exception $e){
									echo "\r\n$nowStart:functions.php - request_filebyloan - writing to file system:failure:MSG:". $e->getMessage()."\r\n";
								}
								fclose($fp);
							}catch(Exception $e){
								echo "\r\n$nowStart:functions.php - request_filebyloan:failure:MSG:". $e->getMessage()."\r\n";

							}
							//now check mime type and erase from file system
							$check="/var/www/html/files/auditLoan/$value";
							$mimeType=mime_content_type($check);
							try{
							  $checkstmtDoc = mysqli_query($dblink, "Select DOCUMENT_TITLE from AUDIT_DOCUMENTS where `DOCUMENT_TITLE`='$docTitle'");
							    if(!$checkstmtDoc){
							      echo "\r\n$nowPayload:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table checking if record already exists FAILED!";
							      throw new Exception("Checking if DOCUMENT_TITLE title already exists in AUDIT_DOCUMENTS table failed\n");
							    }
							    else{
							      if((mysqli_num_rows($checkstmtDoc) > 0)){
							        // this means that this document title already exists in the database 
								// this means that it is in the DB good continue
							        echo "\r\n$nowInserting:functions.php - checking if doc title exists in AUDIT_DOCS:success:MSG:$value already exists in AUDIT_DOCUMENTS table";
							        unlink($check);
								echo "\r\n$nowInserting:functions.php - requestfilebyLoan:success:MSG:$value successfully unlinked\r\n";
							      }
							      else if (mysqli_num_rows($checkstmtDoc) == 0){ // this means this file does not exist in either DB so insert it
							        try{
								  $now=date("Y-m-d H:i:s");
								  $stmt2=mysqli_query($dblink, "Insert into `AUDIT_DOCUMENTS` (`DOCUMENT_TITLE`, `DOC_SIZE`, `UPLOAD_DATE`, `UPLOAD_DATE_TIME`, `DOC_EXTENSION`, `DOC_TYPE`, `DOC_MIME_TYPE`, `LOAN_NUMBER`, `UPLOAD_TYPE`, `INSERT_DATE`) values ('$docTitle', '$docSize', '$uploadDate', '$uploadTime', '$docEx', '$docType', '$mimeType','$loanId', '$uploadType','$now')");
								//$executeSuccess2=mysqli_stmt_execute($stmt2);
								  if(!$stmt2){
								    echo "\r\n$nowInserting:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table inserting FAILED</h4>";
								    throw new Exception('Inserting into AUDIT_DOCUMENTS table failed!');
								  }
								  else{
								    echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully inserted into DOCUMENTS table\r\n";
							 	  // after this successfull insertion update the loan table if complete
								  // if we have this document of not etc.// after inserted into documents table then we need to update the loan table // if it is not updated
									// NOW INSERTING DOC CONTENT INTO AUDIT_DOCS_CONTENT
								    try{
								      $now=date("Y-m-d H:i:s");
								      $stmt3=mysqli_query($dblink, "Insert into `AUDIT_DOCS_CONTENT` (`DOCUMENT_TITLE`, `DOC_CONTENT`, `UPLOAD_DATE`) values ('$docTitle', '$contentClean', '$nowInserting')");
								      if(!$stmt3){
								        echo "\r\n$nowInserting:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table inserting FAILED</h4>";
								        throw new Exception('Inserting into AUDIT_DOCS_CONTENT table failed!');
								      }
								      else{
								        echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully inserted into AUDIT_DOCS_CONTENT table\r\n";
								      }
								    }catch(Exception $e){
								      echo "\r\n$nowInserting:functions.php - $call:failure:MSG:Failed to insert into AUDIT_DOCS_CONTENT".$e->getMessage()."\r\n";
									}
								  }
							        }catch (Exception $e){
							          echo "\r\n$nowInserting:functions.php - $call:failure:MSG:" .$e->getMessage()."\r\n";
							        }
						              }
							    }
							}catch (Exception $e){ // checks if document exists in AUDIT_DoCUMENTS table
							  echo "\r\n$nowInserting:functions.php - $call:failure:MSG:". $e->getMessage(). "\r\n";

							}
							try{
							  $checkstmtDoc = mysqli_query($dblink, "Select DOCUMENT_TITLE from AUDIT_DOCS_CONTENT where `DOCUMENT_TITLE`='$docTitle'");
							    if(!$checkstmtDoc){
							      echo "\r\n$nowPayload:functions.php - $call:failure:MSG:AUDIT_DOCS_CONTENT table checking if record already exists FAILED!";
							      throw new Exception("Checking if DOCUMENT_TITLE title already exists in AUDIT_DOCS_CONTENT table failed\n");
							    }
							    else{
							      if((mysqli_num_rows($checkstmtDoc) > 0)){
							        // this means that this document title already exists in the database 
								// this means that it is in the DB good continue
							        echo "\r\n$nowInserting:functions.php - checking if doc title exists in AUDIT_DOCS_CONTENT:success:MSG:$value already exists in AUDIT_DOCS_CONTENT table";
							      }
							      else if (mysqli_num_rows($checkstmtDoc) == 0){ // this means this file does not exist in either DB so insert it
								    try{
								      $now=date("Y-m-d H:i:s");
								      $stmt3=mysqli_query($dblink, "Insert into `AUDIT_DOCS_CONTENT` (`DOCUMENT_TITLE`, `DOC_CONTENT`, `UPLOAD_DATE`) values ('$docTitle', '$contentClean', '$nowInserting')");
								      if(!$stmt3){
								        echo "\r\n$nowInserting:functions.php - $call:failure:MSG:AUDIT_DOCUMENTS table inserting FAILED</h4>";
								        throw new Exception('Inserting into AUDIT_DOCS_CONTENT table failed!');
								      }
								      else{
								        echo "\r\n$nowInserting:functions.php - $call:success:MSG:$value successfully inserted into AUDIT_DOCS_CONTENT table\r\n";
								      }
								    }catch(Exception $e){
								      echo "\r\n$nowInserting:functions.php - $call:failure:MSG:Failed to insert into AUDIT_DOCS_CONTENT".$e->getMessage()."\r\n";

							           }
							      }
							    }	      
							}catch(Exception $e){
							          echo "\r\n$nowInserting:functions.php - $call:failure:MSG:" .$e->getMessage()."\r\n";		
							}
						} //else for succesfull call
					} //foreach

				}   // checks status request_file_by_loan
				else{
					echo "\r\n$nowStart:functions.php - request_filebyloan:failure:MSG:". $filesReceived[1]."\r\n";
					break;
				}
				//break;
			} // while loop to loop through loan received from the query

		} //else close bracket for when execute query is successful
	}catch(Exception $e){  // try catch for sekect select alll loans from AUDIT_LOAN
		echo "\r\n$nowStart:functions.php - request_filebyloan:failure:MSG:". $e->getMessage()."\r\n";
			
	}

	
	if($newsession){
		close_session($newSID);
		echo "\r\n$nowInserting:functions.php - requestallloans:success:MSG:closed session new;sid\r\n";
	}
	else
		close_session($sid);
	 
}
function update_table($dblink, $username, $password, $requireDN, $loanNum){
	$nowUpdate=date("Y-m-d H:i:s");
	$titleC=0; $creditC=0; $closingC=0; $financialC=0; $personalC=0; $internalC=0; $legalC=0; $mouC=0; $disclosureC=0; $taxC=0; $taxC2=0;
	$getNumDocs;
	$numDocs;
	$docsAllLoan;
	$checkstmt = 0;
	try{
		$getNumDocs = mysqli_query($dblink, "Select NUM_DOCUMENT FROM LOAN where `LOAN_NUMBER`='$loanNum'");
		if(!$getNumDocs){
			throw new Exception("Getting the NUM_DOCUMENTS field from LOAN table failed!\n");
		}
		else{
			echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB:success:MSG:Getting NUM_DOCUMENTS from LOAN table success\r\n";
			$numDocs = mysqli_fetch_object($getNumDocs);
			$number = $numDocs->NUM_DOCUMENT;
			if($numDocs->NUM_DOCUMENT == 0){
				// this means that the number of documents is 0 in the table currently - SO add 1 to it because we inserted a 
				// document into the db - identify which doc it is and add 1 to num_document and set the document title to 1
				// save the value of num_documents receives
				$number++;
				$cleanUnder=explode("_", $requireDN);
				$requireDNUPPER = strtoupper($cleanUnder[0]);
				$columnName = $requireDNUPPER . '_COMPLETE';
				try{
					$updateComplete = mysqli_query($dblink, "Update LOAN set `NUM_DOCUMENT`='$number', set `$columnName`='1' where `LOAN_NUMBER`='$loanNum'");
					if(!$updateComplete){
						throw new Exception("Failed to update LOAN to complete");
					}
					else{
						// successful execution of update
						echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:success:MSG:Successfully updated LOAN to complete\r\n";
					}
				}
				catch(Exception $e){
					echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:failure:MSG:".$e->getMessage()."\r\n";
				}
			}
			else if($numDocs->NUM_DOCUMENT == 0){
				// this means it was a default entry
				// and then verify this by querying the db and checking if there are all the 7 documents in the documents table
				try{
					$getallDocsLoan = mysqli_query($dblink, "Select DOCUMENT_TITLE from DOCUMENTS where `LOAN_NUMBER`='$loanNum'");
					if(!$getallDocsLoan){
						throw new Exception("Getting the DOCUMENT_TITLES field from LOAN table failed!\n");
					}
					else{
						while($docsAllLoan = mysqli_fetch_assoc($getallDocsLoan)){
							// getting the file type
							if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "title")){ $titleC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "credit")){ $creditC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "closing")){ $closingC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "financial")){ $financialC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "personal")){ $personalC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "internal")){ $internalC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "legal")){ $legalC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "mou")){ $mouC = 1; } 
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "disclosure")){ $disclosureC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "tax")){ $taxC = 1; }
							else if(str_contains($docsAllLoan['DOCUMENT_TITLE'], "tax_returns_2")){ $taxC2 = 1; }
						}
						if($titleC && $creditC && $closingC && $financialC && $personalC && $internalC && $legalC && $mouC && $disclosureC && $taxC && $tacC2){
							// this means that that all the files have been found in the db
							try{
								$updateComplete = mysqli_query($dblink, "Update LOAN set `COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
								if(!$updateComplete){
									throw new Exception("Failed to update LOAN to complete");
								}
								else{
									// successful execution of update
									echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:success:MSG:Successfully updated LOAN to complete\r\n";
								}
							}
							catch(Exception $e){
								echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:failure:MSG:".$e->getMessage()."\r\n";
							}
						}
						else{
							// this means that not all the files are in the db - this means that the loan is incomplete
							try{
								$updateIncomplete = mysqli_query($dblink, "Update LOAN set `COMPLETE`='0' where `LOAN_NUMBER`='$loanNum'");
								if(!$updateIncomplete){
									throw new Exception("Failed to update LOAN to incomplete");
								}else{
									echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:success:MSG:Successfully updated LOAN to incomplete\r\n";
								}
							}catch(Exception $e){
								echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:failure:MSG:".$e->getMessage()."\r\n";
							}
						}
					}
				}catch(Exception $e){
					echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB - update_table:failure:MSG:".$e->getMessage()."\r\n";
				}
			}
			else if($numDocs ){
				// this me
			}
		}
	}catch(Exception $e){
		echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB:failure:MSG:".$e->getMessage()."\r\n";
	}
	try{
		if(str_contains($requireDN, "title")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `TITLE_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}else if(str_contains($requireDN, "credit")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `CREDIT_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "closing")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `CLOSING_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "financial")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `FINANCIAL_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "personal")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `PERSONAL_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "internal")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `INTERNAL_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "legal")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `LEGAL_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "mou")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `MOU_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "disclosure")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `DISCLOSURE_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		else if(str_contains($requireDN, "tax")){
			$checkstmt = mysqli_query($dblink, "Update LOAN set `TAX_RECORD_COMPLETE`='1' where `LOAN_NUMBER`='$loanNum'");
		}
		if(!$checkstmt){
			echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB:failure:MSG:Updating LOAN table FAILED\r\n";
			throw new Exception('Inserting into UNKNOWN_DOCS table failed!');
		}else{
			echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB:success:MSG:Updated LOAN table SUCCESSFULLY\r\n";
		}
	}catch(Exception $e){
		echo "\r\n$nowUpdate:functions.php - request_file_api_call_DB:failure:MSG:".$e->getMessage()."\r\n";
	}
}
function create_session($username, $password){
	$nowCreate=date("Y-m-d H:i:s");
	$data="username=$username&password=$password";
	$apiURL="https://cs4743.professorvaladez.com/api/create_session";
	$results=curlHandlerCalls($data, $apiURL);
	$new;
	$results_createSession=json_decode($results, true); // converts JSON to assoc array
	// TODO: if results return successfully
	if(str_contains($results_createSession[2], "clear session first")){
		echo "\r\n$nowCreate:functions.php - create_session():failure:MSG:Need to clear session first\r\n";
		clear_session($username, $password);
		$new=create_session($username, $password);
		echo "\r\n$nowCreate:functions.php - create_session():success:MSG:$results_createSession[2]\r\n";
		return $new;
	}
	else if(str_contains($results_createSession[0], "OK")){
		echo "\r\n$nowCreate:functions.php - create_session():success:MSG:$results_createSession[2]\r\n";
		return $results_createSession[2];
	}
	else{
		echo "\r\n$nowCreate:functions.php - create_session():failure:MSG:$results_createSession[2]\r\n";
		die;
	}
}
function close_session($sid){
	$nowClose=date("Y-m-d H:i:s");
	$data="sid=$sid";
	$apiURL="https://cs4743.professorvaladez.com/api/close_session";
	$results=curlHandlerCalls($data, $apiURL);
	$results_closedSession=json_decode($results, true); // converts JSON to assoc array
	// TODO: if results return successfully
	if(str_contains($results_closedSession[2], "try again")){
		echo "\r\n$nowClose:functions.php - close_session():failure:MSG:$results_closedSession[1]:Action:$results_closedSession[2]\r\n";
	}
	else{
		echo "\r\n$nowClose:functions.php - close_session():success:MSG:$results_closedSession[2]\r\n";
	}
}
function clear_session($username, $password){
	$nowClear=date("Y-m-d H:i:s");
	$data="username=$username&password=$password";
	$apiURL="https://cs4743.professorvaladez.com/api/clear_session"; // declares the endpoint we are connecting to
	$results=curlHandlerCalls($data, $apiURL);
	$results_clearedSession=json_decode($results, true); // converts JSON to assoc array
	// TODO: if results return successfully
	if(str_contains($results_clearedSession[0], "OK")){
		echo "\r\n$nowClear:functions.php - clear_session():success:Status:$results_clearedSession[0]:MSG:$results_clearedSession[1]:Action:$results_clearedSession[2]\r\n";	
		return $results_clearedSession[0]; // is this the sid? check it
	}
	else{
		echo "\r\n$nowClear:functions.php - clear_session():failure:Action:$results_clearedSession[2]\r\n";
		return $results_clearedSession[0]; // is this the sid? check it
	}
}
function db_connect($db){
	$hostname="localhost";
	$username="webuser";
	$password="webuser123*";
	$dblink=mysqli_connect($hostname, $username, $password, $db);
	if(mysqli_connect_error()){
		die("Error connecting to the database".mysqli_connect_error());
	}
	else{
		return $dblink;
	}
}
?>
