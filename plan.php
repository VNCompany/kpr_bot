<?php

include_once "vk_funcs.php";
include_once "fileappender.php";
include_once "vk_buttons.php";

$date_day = date('N');
//$date_day = 4;

if ($date_day < 6) {
	$rasp = getRaspList();
	$response = sendMessage((int)file_get_contents("peer.txt"), $rasp, null, json_encode($msg_buttons));
//	$response = sendMessage('2000000005', $rasp, null, json_encode($msg_buttons));
	$response = json_decode($response, true);

	sendRequest('messages.pin', [
		'v' => '5.126',
		'peer_id' => $response['response'][0]['peer_id'],
		'conversation_message_id' => $response['response'][0]['conversation_message_id']
	]);

	$fa = new FileAppender("users.txt", ';');
	$users = $fa->getall();
	if (isset($users)) 
		sendMessageToIds($users, $rasp);
}
