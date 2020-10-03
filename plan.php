<?php

include_once "vk_funcs.php";
include_once "fileappender.php";

$date_day = date('N');
//$date_day = 5;

if ($date_day < 6) {
	$rasp = getRaspList();
	sendMessage((int)file_get_contents("peer.txt"), $rasp);

	$fa = new FileAppender("users.txt", ';');
	$users = $fa->getall();
	if (isset($users)) 
		sendMessageToIds($users, $rasp);
}
