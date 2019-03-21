<?php

define("INSTANCE",     "https://dev72378.service-now.com");


$update_response = file_get_contents("php://input");
$update = json_decode($update_response, true);

if (isset($update["queryResult"]["action"])) {
    processMessage($update);
    $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
   fwrite($myfile, $update["queryResult"]["action"]);
    fclose($myfile);
}else{
     sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"Hello from webhook",
            "payload" => array(
                "items"=>[
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"Bad request"
                         )
                    )
                ],
                ),
           
        ));
}

function processMessage($update) {
    if($update["queryResult"]["action"] == "instance-details"){
		$instance_details = getInstnaceDetails();
		
        sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"Hello from webhook",
            "payload" => array(
                "items"=>[
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"response from host"
                         )
                    )
                ],
                ),
           
        ));
    }else if($update["queryResult"]["action"] == "convert"){
        if($update["queryResult"]["parameters"]["outputcurrency"] == "USD"){
           $amount =  intval($update["queryResult"]["parameters"]["amountToConverte"]["amount"]);
           $convertresult = $amount * 360;
        }
         sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"The conversion result is".$convertresult,
            "payload" => array(
                "items"=>[
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"The conversion result is".$convertresult
                         )
                    )
                ],
                ),
           
        ));
    }else{
        sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"Error",
            "payload" => array(
                "items"=>[
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"Bad request"
                         )
                    )
                ],
                ),
           
        ));
        
    }
}
 
function sendMessage($parameters) {
    echo json_encode($parameters);
}

function getInstnaceDetails(){
	$curl = curl_init();	
	curl_setopt_array($curl, array(
			  CURLOPT_URL => INSTANCE."/stats.do",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_CUSTOMREQUEST => "GET"			  
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			$instance_desc = "";	

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {			  
			  $response_array = explode ("<br/>", $response); 			  
			  $instance_name = "";
			  $insance_version = ""; 
			  $processing = 0; 
			  $cancelled = 0;
			  
			  foreach($response_array as $row){
				  if (strpos($row, 'Build name:') !== false){					  
						  $row_array = explode(':',$row);
						  $insance_version = $row_array[1];	
						  continue;
						  
				  }else if (strpos($row, 'Instance name:') !== false){					  
						  $row_array = explode(':',$row);
						  $instance_name = $row_array[1];	
						  continue;
						  
				  }else if (strpos($row, 'Processor transactions:') !== false){					  
						  $row_array = explode(':',$row);
						  $processing = $row_array[1];	
						  continue;
						  
				  }else if (strpos($row, 'Cancelled transactions:') !== false){					  
						  $row_array = explode(':',$row);
						  $cancelled = $row_array[1];	
						  continue;
						  
				  }				  
				  	
			  }//for
			}
			
			//The build name is - London. Currently, total transcation processing are - 32 and cancelled - 0.
			//$instance_desc = "The instance name is ".$instance_name." and the version is ".$insance_version." Currently, total ".$processing."  transactions  are processing and ".$cancelled." are cancelled;
			
			$instance_desc = "The instance name is".$instance_name." and the version is".$insance_version." . Currently, total".$processing." transactions are processing and".$cancelled." are cancelled.";
			return $instance_desc;
}

?>