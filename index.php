<?php

include("config.php");


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
		$fulfillmentText = getInstnaceDetails();		
      
    }else if($update["queryResult"]["action"] == "create-incident"){
		$input_parameter = $update["queryResult"]["parameters"];
		$severity 	= $input_parameter['severity'];
		$desc 		= $input_parameter['desc'];
		$fulfillmentText = createIncident($severity,$desc);		
    }else{
		$fulfillmentText = "Error";
        
        
    }
	  sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>$fulfillmentText,
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
			
			$instance_desc = "The instance name is".$instance_name." and the version is".$insance_version." . Currently, total".$processing." transactions are processing and".$cancelled." are cancelled.";
			return $instance_desc;
}


function createIncident($sev,$desc){

	$curl = curl_init();
	$number = 0;
	curl_setopt_array($curl, array(
	  CURLOPT_URL => INSTANCE."/api/now/table/incident",
	  CURLOPT_USERPWD => USERNAME . ":" . PASSWORD,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "{\"severity\":\"$sev\",\"description\":\"$desc\"}",
	  CURLOPT_HTTPHEADER => array(
		"authorization: Basic YWRtaW46VmlrYXNAMTIz",
		"content-type: application/json",		
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);


	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  $response_json = json_decode($response);
	  $number = $response_json->result->number;	  
	}
	
	return "Incident has been created for you. The number is ->".$number;
}
?>