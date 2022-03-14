<?php
require_once '../functions.php';

header("Content-Type:application/json");

$conn = dbConnect();

$check = validate($conn);

if($check["res"]){

	if($items = searchDishes($conn)) {
	echo json_encode(createResponse($items, $conn), JSON_PRETTY_PRINT);
	}

} else {
	echo json_encode($check["msg"]);
}

 dbDisconnect($conn);

 ?>
