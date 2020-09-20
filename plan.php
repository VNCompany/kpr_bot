<?php

include_once "vk_funcs.php";

$date_day = date('w');

if (date('w') != 0 && date('w') != 6)
	echo sendMessage((int)file_get_contents("/var/www/service/kpr/peer.txt"), getRaspList());
