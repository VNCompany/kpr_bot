<?php

header("Content-Type: text/json");
header("Access-Control-Allow-Origin: *");

if (!isset($_GET['date'])) { 
	echo '{"status": "error", "message": "Paramater date is empty"}';
	die();
}

$time = strtotime($_GET['date']);

if ($time === false) {
	echo '{"status": "error", "message": "Invalid parameter: date"}';
	die();
}	

$rasp = json_decode(file_get_contents("../rasp.json"), true);

$week = date("W", $time) % 2 == 0 ? "odd" : "even";

$day_n = date("N", $time);

if ($day_n > 5) {
	echo '{"status": "ok", "object": {"day": "Weekend", items: []}}';
	die();
}

$result = ["status" => "ok"];
$result["object"] = ["day" => $rasp[$week][$day_n]['name']];
$result["object"]['items'] = array_map(function ($item) {
	return [
		"name" => $item[0],
		"cabinet" => $item[1],
		"type" => $item[2]
	];
}, $rasp[$week][$day_n]['items']);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
