<?php

$req_parameter = $_REQUEST;
//print_r($req_parameter['api-type']);


switch ($req_parameter['api-type']) {
    case "create-incident":
        echo "You are in incidnent creation method";
        break;
    
    default:
        echo "You are out of incidnent creation method";
}

?>