<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-with");
header("Content-Type: application/json; charset = utf-8");

include "config.php";

$postjson = file_get_contents('php://input');

$card_uid = $postjson[uid];
//$card_uid = $postjson['uid'];

$query = mysqli_query($mysqli, "SELECT * FROM cards WHERE unique_id = $card_uid ");
$check = mysqli_num_rows($query);

if($check > 0 ){
	$result = json_encode(array('success' => true, 'UID' => $card_uid));
}

else{
	$result = json_encode(array('success' => false, 'UID' => $postjson['uid']));
}

echo $result;