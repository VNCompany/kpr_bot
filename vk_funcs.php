<?php

include_once "dbworker.php";
include_once "fileappender.php";
include_once "functions.php";
include_once "passwd.php";

function sendRequest($method, $params){
	global $access_token;
	$params['access_token'] = $access_token;
	return file_get_contents("https://api.vk.com/method/".$method."?" . http_build_query($params));
}

function userGet($id) {
	$q = [
		"v" => "5.124",
		"name_case" => "Nom",
		"user_ids" => $id
	];
	$result = json_decode(sendRequest("users.get", $q), true);
	if (!empty($result['response']))
		return $result['response'][0];
	else
		return null;
}

function sendMessage($peer_id, $message, $attachment = null, $keyboard = null, $callback = null){
	$q = [
		"v" => "5.126",
		"random_id" => rand(0, getrandmax()),
		"peer_ids" => $peer_id,
		"message" => $message
	];

	if ($attachment != null)
		$q['attachment'] = $attachment;

	if ($keyboard != null)
		$q['keyboard'] = $keyboard;

	return sendRequest("messages.send", $q);
}

function sendMessageToIds($user_ids, $message, $keyboard = null) {
	$q = [
		"v" => "5.126",
		"random_id" => rand(0, getrandmax()),
		"peer_ids" => implode(',', $user_ids),
		"message" => $message
	];
	if (isset($keyboard)) $q['keyboard'] = $keyboard;
	sendRequest("messages.send", $q);
}

function getWeekType($reverse = false){
	if(date('W') % 2 == 0)
		return $reverse ? "even" : "odd";
	else
		return $reverse ? "odd" : "even";
}

function getRasp($week, $day){
	$rasp = json_decode(file_get_contents("rasp.json"), true);
	return $rasp[$week][$day];	
}

function getRaspList($msg = true, $date = "сегодня", $day_n = null, $week = null){
	$other_week = false;
	if ($day_n == null) $day_n = date('w');
	if ($week == null) $week = getWeekType();
	else $other_week = true;

	$rasp = getRasp($week, (string)$day_n);
	$sdate = toRusMonth(getDateFromDay($day_n, $other_week, true), true);

	$hw_api = file_get_contents("http://mistersandman.ru/kpr_hw/index.php?date=" . getDateFromDay($day_n, $other_week));

	$hw = json_decode($hw_api, true);
	$hw = $hw['data'];

	$result = sprintf("&#128203; Расписание на %s (%s):\n", $date, $sdate);
	foreach($rasp["items"] as $index => $item){
		if ($item[0] == "")
			$result .= "&#127379; Нет пары\n";
		else {
			$item_hw = empty($hw[$index + 1]) ? "" : ": " . $hw[$index + 1];
			if (trim($item_hw) != ": !free")
				$result .= sprintf("%d&#8419; %s -- %s [%s]%s\n", $index + 1, 
										    $item[0], 
										    $item[1], 
										    $item[2],
										    $item_hw
				);
			else
				$result .= "&#127379; Нет пары\n";
		}
	}

	$reminds = readReminds(true);
	if($reminds !== null && $msg === true){
		$result .= "\nНапоминания:\n";
		foreach($reminds as $remind){
			$result .= "&#10071; " . $remind . "\n";
		}
	}

	if($msg) {
		$dbw = new DbWorker();
		$result .= "\n" . $dbw->getText();
		$result .= "\nХорошего дня! &#129302;";
	}

	$result .= "\n\n" . sprintf("Добавить домашнее задание: http://mistersandman.ru/kpr/ \n© Mr.Sandman, " . date("Y"));

	if(file_exists("reminds.txt") && $msg === true)
		unlink("reminds.txt");
	return $result;
}

function is_admin($from_id, $valid_ids, $peer_id){
	if (!in_array($from_id, $valid_ids)){
		sendMessage($peer_id, "&#128683; Вы не можете использовать данную команду.");
		return false;
	}else
		return true;

}

function writeRemind($text){
	$fa = new FileAppender("reminds.txt", ';');
	
	if ($text == "") return false;

	$fa->append($text);

	return true;
}

function readReminds($clear = false){
	$fa = new FileAppender("reminds.txt", ';');
	$reminds = $fa->getAll();

	if ($clear)
		$fa->clear();

	return $reminds;
}

function getHelp(){
	return <<<_END
- - - Справка - - -

!keyboard - получить/обновить клавиатуру

!рассылка - получать сообщения в лс (расписание на каждый день, новости и т.д.)

!новость (ответ на сообщение или пересылаемое сообщение) - добавить новость

новости - новости &#128528;

!help - справка;

!расписание - посмотреть полное расписание

!сегодня - расписание сегодня; 

!завтра - расписание завтра;

!понедельник[-пятница] - расписание на заданный день;

!!понедельник[-пятница] - расписание на след.неделю;

!напоминание текст - создать напоминание.
- - - - - - - - - -
_END;
}
