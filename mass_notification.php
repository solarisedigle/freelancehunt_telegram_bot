<?/*
	date_default_timezone_set('Europe/Kiev');
	$mysqli = new mysqli("localhost", "shumik", "03102002Vitalik", "shumik_base");
	$apikey = '867897377:AAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw'; 
	//$chat_2_id = "-1001179542926";
	$chat_2_id = "418289311";

	function run_query($method_name, $params){
		global $apikey, $mysqli;
		$str_params = "?shit=you";
		foreach ($params as $key => $value) {
			if ($key == 'photo') {
				if (count(explode("http", $value)) == 1){
					$value = 'https://'.$_SERVER['SERVER_NAME'].explode($_SERVER['SERVER_NAME'],dirname(__FILE__))[1].'/'.$value;
				}
			}
			$str_params .= "&".$key."=".urlencode($value);
		}
		$query = 'https://api.telegram.org/bot'.$apikey.'/'.$method_name.$str_params;
		$res = $mysqli->query("INSERT INTO `fhb_queue` (`query`, `priority`) VALUES ('".$query."', 1)");
		//return "???? - ".file_get_contents($query);
	}

	$res = $mysqli->query("SELECT * FROM `fhb_users` WHERE `status` != 'deleted'");
	$message = "<b>Update 0.2.1❗️</b>\nБезкоштовний тариф!\n\nВідтепер користувачі без підписки також зможуть отримувати нові проекти! \nРозсилка відбуватиметься кожні 4 години одним повідомленням.\n\nДля тих, хто хоче отримувати сповіщення миттєво і в зручній формі - потрібно як і раніше, оформити підписку.\n----\nНагадаємо, переглядати і відправляти повідомлення в чаті можна <b>без</b> підписки!";
	$inline_keyboard = [
					[
						[
							'text' => "📚 Меню",
							'callback_data' => 'headscreen_new'
						],
						[
							'text' => "👨‍🔧 ".'Профіль',
							'callback_data' => 'profile_new'
						]
					]
				];
	$reply_markup = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
	if ($res) {
		while ($user = $res->fetch_assoc()) {
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'text' => $message, 'parse_mode'=> 'HTML', 'reply_markup' => $reply_markup]);
			echo "\n".$user['first_name']." - ".$user['premium_time'];
			/*
			if ($user['premium_time'] >= time()) {
				$new_prem = $user['premium_time'] + 604800;
			}
			else{
				$new_prem = time() + 604800;
			}
			$query = "UPDATE `fhb_users` SET `premium_time` = $new_prem WHERE `chat_id` = ".$user['chat_id'];
			$res2 = $mysqli -> query($query);
			if (!$res2) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".__LINE__." ".$mysqli->error;
			}
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "🎁 Вам нараховано 7 днів безкоштовної підписки!"]);
			echo "\n".$user['first_name']." - ".$user['premium_time'];
			*/
		}
	}
*/
?>