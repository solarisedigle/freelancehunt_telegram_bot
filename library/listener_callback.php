<?
	if ($user['text'] == 'category') {
		$keyboard = get_keyboard('category');
		$text = "";
		foreach (get_categories_wtht() as $key => $value) {
			$text .= "\n<code>".$key.":</code>";
			foreach ($value as $category) {
				$text .= "\n<code>   >>".$category['name']."</code>";
			}
		}
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Категорії:</b>\n———".$text, 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == 'phrase'){
		$keyboard = get_keyboard('phrase');
		$text = "";
		$i = 1;
		foreach (get_keytext_wtht() as $value) {
			$text .= "\n<code> $i. \"".$value['key_name']."\"</code>";
			$i++;
		}
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Фрази:</b>\n———".$text, 'reply_markup' => $keyboard]);
		$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
		$res = $mysqli -> query($query);
		if (!$res) {
			$err_msg .= "\n".$query;
			$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
		}
	}
	else if($user['text'] == 'stopphrase'){
		$keyboard = get_keyboard('stopphrase');
		$text = "";
		$i = 1;
		foreach (get_stop_keytext_wtht() as $value) {
			$text .= "\n<code> $i. \"".$value['stopkey_name']."\"</code>";
			$i++;
		}
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Стоп-текст:</b>\n———".$text, 'reply_markup' => $keyboard]);
		$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
		$res = $mysqli -> query($query);
		if (!$res) {
			$err_msg .= "\n".$query;
			$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
		}
	}
	else if($user['text'] == 'profile'){
		$keyboard = get_keyboard('profile');
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => profile_info(), 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == 'profile_new'){
		$keyboard = get_keyboard('profile');
		run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => profile_info(), 'reply_markup' => $keyboard]);
	}
	else if(count(explode("threads", $user['text'])) == 2){
		/*if ($user['root'] != 3) {
			$back_btn[][] = back_btn('headscreen', $backbutt);
			$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "В розробці, скоро буде 😉", 'reply_markup' => $btn_back]);
		}
		else{*/
			$response = threads($user['text']);
			$keyboard = $response[1];
			$message = $response[0];
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => $message, 'reply_markup' => $keyboard, 'disable_web_page_preview' => true]);
		//}
	}
	else if($user['text'] == 'pm_add'){
		$keyboard = get_keyboard('pm_add');
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => additional_info(), 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == 'bot_description'){
		$keyboard = get_keyboard('bot_description');
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => bot_description(), 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == 'premium'){
		if ($user['premium']) {
			$text_prem = "<b>Продовжити підписку</b>";
		}
		else{
			$text_prem = "<b>Купити підписку</b>";
		}
		$prem_date = "\n".$user['premium_time']>=time()?(secondsToTime($user['premium_time'] - time())):"Не активна";
		$message = "\n<b>Ми будемо вдячні вам за вклад в цей проект!</b>❤️ \nКошти підуть на  купівлю сервера, що підвищить продуктивність";
		$keyboard = get_keyboard('premium');
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "🔔 ".$text_prem."\n———\n<code>".$prem_date."</code>".$message, 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == 'premium_new'){
		if ($user['premium']) {
			$text_prem = "<b>Продовжити підписку</b>";
		}
		else{
			$text_prem = "<b>Купити підписку</b>";
		}
		$prem_date = "\n".$user['premium_time']>=time()?(secondsToTime($user['premium_time'] - time())):"Не активна";
		$message = "\n<b>Ми будемо вдячні вам за вклад в цей проект!</b>❤️ \nКошти підуть на  купівлю сервера, що підвищить продуктивність";
		$keyboard = get_keyboard('premium');
		run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "🔔 ".$text_prem."\n———\n<code>".$prem_date."</code>".$message, 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == 'api_key'){
			if (prepare_to_api_key()) {
				$message = "\n<b>Введіть свій API токен</b> \nВи можете знайти його на своїй сторінці за посиланням:\nhttps://freelancehunt.com/my/api";
				$keyboard = get_keyboard('api_key');
			}
			else{
				$message = "\nУпс.. щось пішло не так</b>";
				$back_btn[][] = back_btn('profile', "$backbutt");
				$keyboard = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
			}
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => $message, 'reply_markup' => $keyboard]);
	}
	else if ($user['text'] == 'addCategory') {
		$keyboard = get_keyboard('ac');
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Виберіть розділ:</b>", 'reply_markup' => $keyboard]);
	}
	else if ($user['text'] == 'deleteCategory') {
		$keyboard = get_keyboard('dc');
		if (count(json_decode($keyboard, true)['inline_keyboard']) != 1) {
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Виберіть розділ:</b>", 'reply_markup' => $keyboard]);
	}
	else{
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Ви не підписані на жодну категорію</b>", 'reply_markup' => $keyboard]);
		}
		
	}
	else if ($user['text'] == 'addKey') {

		$back_btn[][] = back_btn('phrase', "$backbutt");
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);

		if(prepare_to_keytext($user['chat_id'])){
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Напишіть ключову фразу, щоб отримувати проекти в яких вона буде присутня.</b>\n\n<i>Обмеження: ".$min_text_width." - ".$max_text_width." символів</i>", 'reply_markup' => $btn_back]);
		}
		else{
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Упс.. щось пішло не так...</b>", 'reply_markup' => $btn_back]);
		}
		
	}
	else if ($user['text'] == 'addStopKey') {
			$back_btn[][] = back_btn('stopphrase', "$backbutt");
			$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
			if(prepare_to_stop_keytext($user['chat_id'])){
				run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Напишіть стоп-фразу, щоб ігнорувати не цікаві вам проекти, в яких вона зустрічається</b>\n\n<i>Обмеження: ".$min_text_width." - ".$max_text_width." символів</i>", 'reply_markup' => $btn_back]);
			}
			else{
				run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Упс.. щось пішло не так...</b>", 'reply_markup' => $btn_back]);
			}
	}
	else if ($user['text'] == 'deleteKey') {
		$keyboard = get_keyboard('dk');
		if (count(json_decode($keyboard, true)['inline_keyboard']) != 1) {
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Яку фразу бажаєте видалити?:</b>", 'reply_markup' => $keyboard]);
		}
		else{
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Ви не додали жодну фразу</b>", 'reply_markup' => $keyboard]);
		}
	}
	else if ($user['text'] == 'deleteStopKey') {
		$keyboard = get_keyboard('dsk');
		if (count(json_decode($keyboard, true)['inline_keyboard']) != 1) {
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Який стоп-текст ви бажаєте видалити?:</b>", 'reply_markup' => $keyboard]);
		}
		else{
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Ви не додали жоднен стоп-текст</b>", 'reply_markup' => $keyboard]);
		}
	}
	else if (count(explode('ac|r', $user['text'])) == 2) {
		$add_text = get_keyboard('ac|r', explode('ac|r', $user['text'])[1]);
		$back_btn[][] = back_btn('categories', "$backbutt");
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Натисніть, щоб вибрати категорію:\n</b>".$add_text, 'reply_markup' => $btn_back]);
	}
	else if (count(explode('dc|r', $user['text'])) == 2) {
		$add_text = get_keyboard('dc|r', explode('dc|r', $user['text'])[1]);
		$back_btn[][] = back_btn('categories_del', "$backbutt");
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Натисніть, щоб видалити категорію:\n</b>".$add_text, 'reply_markup' => $btn_back]);
	}
	else if (count(explode("mr|", $user['text'])) == 2) {
		get_keyboard('mr|', explode('mr|', $user['text'])[1]);
	}
	else if (count(explode("ed|", $user['text'])) == 2) {
		get_keyboard('ed|', explode('ed|', $user['text'])[1]);
	}
	else if (count(explode('dk|r', $user['text'])) == 2) {
		$add_text = get_keyboard('dk|r', explode('dk|r', $user['text'])[1]);
		$back_btn[][] = back_btn('headscreen', "$backbutt");
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
		if ($add_text !== 101 && $add_text !== 404) {
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Ключову фразу видалено\n</b>", 'reply_markup' => $btn_back]);
		}
		else{
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Упс... щось пішло не так\n</b>", 'reply_markup' => $btn_back]);
		}
	}
	else if (count(explode('dsk|r', $user['text'])) == 2) {
		$add_text = get_keyboard('dsk|r', explode('dsk|r', $user['text'])[1]);
		$back_btn[][] = back_btn('stopphrase', "$backbutt");
		$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
		if ($add_text !== 101 && $add_text !== 404) {
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Стоп-текст видалено. Тепер ви знову будете отримувати проекти, що його містять\n</b>", 'reply_markup' => $btn_back]);
		}
		else{
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Упс... щось пішло не так\n</b>", 'reply_markup' => $btn_back]);
		}
	}
	else if (count(explode('bp|', $user['text'])) == 2) {
		get_keyboard('bp|', explode('bp|', $user['text'])[1]);
	}

	else if ($user['text'] == "headscreen") {
		$premium = $user['premium_time']>=time()?(secondsToTime($user['premium_time'] - time())):"Не активна";
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>$menubutt</b>\n———\n<code>Підписка: ".$premium."</code>", 'reply_markup' => get_keyboard('head')]);
		$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
		$res = $mysqli -> query($query);
		if (!$res) {
			$err_msg .= "\n".$query;
			$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
		}
	}
	else if ($user['text'] == "headscreen_new") {
		$premium = $user['premium_time']>=time()?(secondsToTime($user['premium_time'] - time())):"Не активна";
		run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>$menubutt</b>\n———\n<code>Підписка: ".$premium."</code>", 'reply_markup' => get_keyboard('head')]);
		$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
		$res = $mysqli -> query($query);
		if (!$res) {
			$err_msg .= "\n".$query;
			$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
		}
	}
	else if($user['text'] == "categories"){
		$keyboard = get_keyboard('ac');
		run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Виберіть розділ:</b>", 'reply_markup' => $keyboard]);
	}
	else if($user['text'] == "categories_del"){
		$keyboard = get_keyboard('dc');
		if (count(json_decode($keyboard, true)['inline_keyboard']) != 1) {
			run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Виберіть розділ:</b>", 'reply_markup' => $keyboard]);
		}
		else{
				run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => "<b>Ви не підписані на жодну категорію</b>", 'reply_markup' => $keyboard]);
		}
	}
?>