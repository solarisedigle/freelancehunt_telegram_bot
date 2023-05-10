<?
	if (($user['text'] == "/start") || ($user['text'] == "$head_menubutt")) {
		$premium = $user['premium_time']>=time()?(secondsToTime($user['premium_time'] - time())):"Не активна";
		run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>$menubutt</b>\n———\n<code>Підписка: ".$premium."</code>", 'reply_markup' => get_keyboard('head')]);
		$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
		$res = $mysqli -> query($query);
		if (!$res) {
			$err_msg .= "\n".$query;
			$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
		}
	}
	else if (count(explode("/add", $user['text'])) == 2) {
		$answer = add_category(explode("/add", $user['text'])[1]);
		$back_btn[][] = back_btn('category', $backbutt);
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
		if ($answer == 101) {
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Здається ви вже підписані на цю категорію"/*, 'reply_markup' => $keyboard_h*/]);
		}
		else if ($answer == 102) {
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<i>Ліміт (".$max_params['kt'].") вичерпано, видаліть менш потрібні.</i>", 'reply_markup' => $btn_back]);
		}
		else if ($answer == 404) {
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Упс.. Не вдалося знайти категорію"]);
		}
		else{
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Успіх! тепер ви підписані на категорію <b>".$answer."</b>"/*, 'reply_markup' => $keyboard_h*/]);
		}
	}
	else if (count(explode("/del", $user['text'])) == 2) {
		$answer = del_category(explode("/del", $user['text'])[1]);
		if ($answer == 101) {
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Здається ви не підписані на цю категорію"/*, 'reply_markup' => $keyboard_h*/]);
		}
		else if ($answer == 404) {
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Упс.. Не вдалося знайти категорію"]);
		}
		else{
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Ви відписались від сповіщень з категорією <b>".$answer."</b>"/*, 'reply_markup' => $keyboard_h*/]);
		}
	}
	else if(count(explode("/stat", $user['text'])) == 2){
		$url = statistic(explode("/stat", $user['text'])[1]);
		run_query('sendDocument', ['chat_id' => $user['chat_id'], 'document'=> $url, 'parse_mode'=> 'HTML', 'caption' => 'stat. for today: ']);
	}
?>