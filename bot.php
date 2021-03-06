<?php

include_once "vk_funcs.php";
include_once "cmdman.php";
include_once "dbworker.php";
include_once "vk_buttons.php";
include_once "fileappender.php";

$rasp_last_update = "14 сен 2020 г. 19:17";
$rasp_doc = "doc-186344202_568844688";

if (!isset($_REQUEST)) return;

$req = json_decode(file_get_contents("php://input"));

if ($req->secret != $secret_key && $req->type != "confirmation") return;

$admins = [
	"297082709",
	"319081681",
	"268192504"
];

$day_cmd = [
	"!понедельник" => "1",
	"!вторник" => "2",
	"!среда" => "3",
	"!четверг" => "4",
	"!пятница" => "5"
];

$iday_cmd = [
	"!!понедельник" => "1",
	"!!вторник" => "2",
	"!!среда" => "3",
	"!!четверг" => "4",
	"!!пятница" => "5"
];

switch($req->type){
	case "confirmation":
		echo $confirmation;
		break;
	case "message_new":
		$obj = $req->object;
		$text = $obj->text;
		$peer = $obj->peer_id;

		if (!empty($obj->payload)) {
			$result = json_decode($obj->payload);
			switch($result->button){
				case "news":
					$text = "Новости";
					break;
				case "rasp":
					$text = "!сегодня";
					break;
				case "today":
					$text = "!сегодня";
					break;
				case "tomorrow":
					$text = "!завтра";
					break;
				case "fullrasp":
					$text = "!расписание";
			}
		}

		if ($text == "!fast") {
			sendMessage($peer, "ok");
			echo "ok";
			die();
		}

		if ($text == "10-4" || $text == "10-4."){
			sendMessage($peer, "Это значит принято");
			echo "ok";
			die();
		}

		if ($text == "!новость"){
			if (is_admin($obj->from_id, $admins, $peer)) {

				function makeNewsletter($msg) {
					$res = "Свежая новость! \n";
					$res .= $msg;
					$fa = new FileAppender("users.txt", ';');
					$users = $fa->getall();
					sendMessageToIds($users, $res);
				}

				if (!empty($obj->fwd_messages)) {
					$dbw = new DbWorker();
					$res = "";
					foreach ($obj->fwd_messages as $msg) {
						$res .= $msg->text . "\n";
					}
					$dbw->add($res);
					
					makeNewsletter($res);
					sendMessage($peer, "&#9989; Новость успешно добавлена");
				} elseif (!empty($obj->reply_message)) {
					$dbw = new DbWorker();
					$dbw->add($obj->reply_message->text);

					makeNewsletter($obj->reply_message->text);
					sendMessage($peer, "&#9989; Новость успешно добавлена");
				} else{
					sendMessage($peer, "Пустое сообщение");
				}
			}
			echo "ok";
			die();
		} elseif (mb_strtolower($text) == "новости") {
			$dbw = new DbWorker();
			sendMessage($peer, $dbw->getText());
		}

		if (mb_strtolower($text) == "бан" && !empty($obj->reply_message)) {
			$ban_text = "ушёл в бан нахуй";
			$u = userGet($obj->reply_message->from_id);
			if (isset($u))
				$print = sprintf("[id%d|%s. %s] %s", $u['id'], mb_substr($u['first_name'], 0, 1), $u['last_name'], $ban_text);
			else
				$print = "Себя забань!";
			sendMessage($peer, $print);
		}


		$c = new Commander([
			"register" => function() use ($peer, $obj, $admins){
				if (is_admin($obj->from_id, $admins, $peer)) {
					file_put_contents("peer.txt", $peer);
					sendMessage($peer, "Беседа активирована.");
				}
			},

			"help" => function() use ($peer){
				sendMessage($peer, getHelp());
			},

			"debug" => function($command) use ($obj, $peer, $admins){
				if(is_admin($obj->from_id, $admins, $peer)) {
					switch ($command) {
						case "reminds":
							sendMessage($peer, json_encode(readReminds(true), JSON_UNESCAPED_UNICODE));
							break;
						case "rasp":
							//sendMessage($peer, getRaspList());
							include_once "plan.php";
							break;
						case "temp":
							sendMessage($peer, shell_exec("python3 /home/victor/temp.py"));
							break;
						case "test":
							sendMessage($peer, "Testing successful. " . $peer . " / " . file_get_contents("peer.txt"));
							break;
						default:
							sendMessage($peer, "OK");
					}
				}
			},

			"завтра" => function() use ($peer){
				$day_n = date('w');
				switch($day_n){
					case 5:
					case 6:
						sendMessage($peer, getRaspList(false, "след. неделю", 1, getWeekType(true)));
						break;
					case 0:
						sendMessage($peer, getRaspList(false, "завтра", 1, getWeekType(true)));
						break;
					default:
						sendMessage($peer, getRaspList(false, "завтра", $day_n + 1));
				}
			},

			"сегодня" => function() use ($peer, $msg_buttons){
				$day_n = date('w');
				switch($day_n){
					case 6:
					case 0:
						sendMessage($peer, "Сегодня выходной день &#127379;", null, json_encode($msg_buttons));
						break;
					default:
						sendMessage($peer, getRaspList(false, "сегодня", $day_n), null, json_encode($msg_buttons));
				}
			},

			"напоминание" => function($text) use ($peer){
				if ($text == null) {
					sendMessage($peer, "Пустое напоминание");
					return;
				}
				writeRemind($text);
				sendMessage($peer, "&#9989; Напоминание успешно добавлено!");
			},

			"newdel" => function ($id) use ($peer, $obj) {
				if (is_admin($obj->from_id, ["297082709"], $peer)) {
					if (is_numeric($id)) {
						$dbw = new DbWorker();
						$dbw->delete($id);
						sendMessage($peer, "Запись " . $id . " удалена.");
					} else {
						sendMessage($peer, "&#9888; Введите правильный id");
					}
				}
			},

			"расписание" => function ($id) use ($peer, $rasp_last_update, $rasp_doc) {
				sendMessage($peer, "Расписание КПР-21. Последнее обновление: " . $rasp_last_update . ".", $rasp_doc);
			},

			"keyboard" => function () use ($peer, $buttons) {
				$dbw = new DbWorker();
				sendMessage($peer, "Клавиатура включена", null, json_encode($buttons)); 
			},

			"keyboarddel" => function () use ($peer, $buttons_clear) {
				sendMessage($peer, "Клавиатура выключена", null, json_encode($buttons_clear));
			},

			"рассылка" => function() use ($obj) {
				$fa = new FileAppender("users.txt", ';');
				if(!$fa->itemExists($obj->from_id)) {
					$fa->append($obj->from_id);
					sendMessage($obj->peer_id, sprintf("[id%d|Вы] успешно подписались на рассылку", $obj->from_id));
				} else {
					sendMessage($obj->peer_id, sprintf("[id%d|Вы] уже подписаны на рассылку", $obj->from_id));
				}
			}
		]);

		$c->execute($text);

		if (in_array($text, array_keys($day_cmd)))
			sendMessage($peer, getRaspList(false, "день", $day_cmd[$text]));

		elseif (in_array($text, array_keys($iday_cmd)))
			sendMessage($peer, getRaspList(false, "день", $iday_cmd[$text], getWeekType() == "odd" ? "even" : "odd"));

		else
			$c->errors(function() use ($peer) { sendMessage($peer, "&#9888; Неизвестная команда. Введите !help, чтобы посмотреть список доступных команд."); });

		echo "ok";
		break;
}
