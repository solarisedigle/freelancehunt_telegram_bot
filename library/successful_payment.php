<?
	function thanks_for_buying($amount){
		global $user, $backbutt;
		$back_btn[][] = back_btn('premium', "$backbutt");
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
		run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Дякуємо! Вам нараховано ".secondsToTime($amount)." підписки", 'reply_markup' => $btn_back]);
	}

	$user['add'] = intval($telegram['message']['successful_payment']['invoice_payload']);
	$user['chat_id'] = $telegram['message']['chat']['id'];
	check_user();
	if ($user['premium_time'] >= time()) {
		$new_prem = $user['premium_time'] + $user['add'];
	}
	else{
		$new_prem = time() + $user['add'];
	}
	$query = "INSERT INTO `fhb_payments` (`chat_id`, `amount`, `date`) VALUES (".$user['chat_id'].", ".$user['add'].", '".time()."')";
	$res = $mysqli -> query($query);
	if (!$res) {
		$err_msg .= "\n".$query;
		$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
	}
	$query = "UPDATE `fhb_users` SET `premium_time` = $new_prem WHERE `chat_id` = ".$user['chat_id'];
	$res = $mysqli -> query($query);
	if (!$res) {
		$err_msg .= "\n".$query;
		$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
	}
	$keyboard = get_keyboard('profile');
	thanks_for_buying($user['add']);
?>