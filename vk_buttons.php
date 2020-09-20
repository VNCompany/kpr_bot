<?php

$buttons = [
	"one_time" => false,
	"buttons" => [
		[
			[
				"action" => [
					"type" => "text",
					"label" => "&#128240; Новости",
					"payload" => json_encode(["button" => "news"])
				],
				"color" => "primary"
			]
		],

		[
			[
				"action" => [
					"type" => "text",
					"label" => "&#128216; Расписание",
					"payload" => json_encode(["button" => "rasp"])
				],
				"color" => "positive"
			]
		],

		[
			[
				"action" => [
					"type" => "text",
					"label" => "Сегодня",
					"payload" => json_encode(["button" => "today"])
				],
				"color" => "secondary"
			],

			[
				"action" => [
					"type" => "text",
					"label" => "Завтра",
					"payload" => json_encode(["button" => "tomorrow"])
				],
				"color" => "secondary"
			]
		]
	]
];

$msg_buttons = [
	"one_time" => false,
	"inline" => true,
	"buttons" => [
		[
			[
				"action" => [
					"type" => "text",
					"label" => "Полное",
					"payload" => json_encode(["button" => "fullrasp"])
				],
				"color" => "primary"
			],

			[

				"action" => [
					"type" => "text",
					"label" => "Завтра",
					"payload" => json_encode(["button" => "tomorrow"])
				],
				"color" => "positive"
			]
		]
	]
];

$buttons_clear = [
	"buttons" => [],
	"one_time" => true
];
