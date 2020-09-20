<?php

function getDateFromDay($day, $next_week, $return_time = false) {
	$current_day = date("N");
	if (!$next_week) {
		$result = time() + 60 * 60 * 24 * ($day - $current_day);
		return $return_time ? $result : date("Y-m-d", $result);
	} 
	else {
		$result = time() + 60 * 60 * 24 * (7 - $current_day + $day);
		return $return_time ? $result : date("Y-m-d", $result);
	}
}

$month_names = [
	"1" =>  "января",  "7" => "июля",
	"2" => "февраля",  "8" => "августа",
	"3" =>   "марта",  "9" => "сентября",
	"4" =>  "апреля", "10" => "октября",
	"5" =>     "мая", "11" => "ноября",
	"6" =>    "июня", "12" => "декабря"
];
function toRusMonth($time, $only_month = false) {
	global $month_names;
	
	$date = date('j-n-Y', $time);
	$m = mb_split('-', $date);

	return $m[0] . ' ' . $month_names[$m[1]] . ($only_month ? "" : ' ' . $m[2] . " г.");
}

