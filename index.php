<?php

$req_parameter = $_REQUEST;
//print_r($req_parameter['api-type']);
$insttance = "https://dev72378.service-now.com";
$curl = curl_init();



switch ($req_parameter['api-type']) {
    case "create-incident":      
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $insttance."/stats.do",
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
			  //echo $response;
			  $response_array = explode ("<br/>", $response); 			  
			  //print_r($response_array);
			  foreach($response_array as $row){
				  if ((strpos($row, 'Build name:') !== false) || 
					  (strpos($row, 'Processor transactions:') !== false) || 
					  (strpos($row, 'Cancelled transactions:') !== false)
					  ){
						$instance_desc .= " ".$row.".";
						//break;
					}//if	
			  }//for
			}
			echo $instance_desc;
        break;
    
    default:
        echo "You are out of incidnent creation method";
}

?>