<?
	function my_htmlspecialchars($txt){
		global $apikey, $err_msg, $mysqli;
		return $mysqli->real_escape_string($txt);
	}
	function run_query($method_name, $params){
		global $apikey, $err_msg, $mysqli;
		$str_params = "?shit=you";
		foreach ($params as $key => $value) {
			if ($key == 'photo' || $key == 'document') {
				if (count(explode("http", $value)) == 1){
					$value = 'https://'.$_SERVER['SERVER_NAME'].explode($_SERVER['SERVER_NAME'],dirname(__FILE__))[1].'/'.$value;
				}
			}
			$str_params .= "&".$key."=".urlencode($value);
		}
		$priority = 2;
		if ($method_name == 'answerPreCheckoutQuery') {
			$priority = 4;
			$params['chat_id'] = 101;
		}
		else if($method_name == 'answerCallbackQuery'){
			$priority = 3;
			$params['chat_id'] = 102;
		}
		$query = 'https://api.telegram.org/bot'.$apikey.'/'.$method_name.$str_params;
		$res = $mysqli->query("INSERT INTO `fhb_queue` (`query`, `priority`, `chat_id`) VALUES ('".$query."', $priority, '".$params['chat_id']."')");

	}
	function ban(){
		global $user, $mysqli, $err_msg;
		$query = "UPDATE `fhb_users` SET "
					."`ban` = ".(time() + 300)
					." WHERE `chat_id` = ".$user['chat_id'];
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					}
	}
	function get_my_profile($api_key){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	    curl_setopt($ch, CURLOPT_ENCODING, "");
	    curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/my/profile");
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    "Content-Type: application/json",
		    "Accept-Language: uk",
		    "Authorization: Bearer ".$api_key
		  ));
	    $data = curl_exec($ch);
	    
	    $data2 = curl_getinfo($ch);
	    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($data, 0, $header_size);
		$body = substr($data, $header_size);
		$arr_eployers[] = $body;
		curl_close($ch);
	    return array($header, $body);
	}	
	function check_user(){
		global $user, $mysqli, $err_msg, $subscribes_key_text, $chat_2_id;
		$res = $mysqli -> query("SELECT * FROM `fhb_users` WHERE `chat_id` LIKE ".$user['chat_id']);
		if (mysqli_num_rows($res) == 0){
			$prem_time = time() + 604800;
			$query = "INSERT INTO `fhb_users` ("
				."`chat_id`,"
				."`user_id`,"
				."`subscription_count`,"
				."`user_name`,"
				."`first_name`,"
				."`last_name`,"
				."`step`,"
				."`premium_time`"
				.") VALUES ("
				.my_htmlspecialchars($user['chat_id'])
				.", ".my_htmlspecialchars($user['user_id'])
				.", 0"
				.", '".my_htmlspecialchars($user['user_name'])
				."', '".my_htmlspecialchars($user['first_name'])
				."', '".my_htmlspecialchars($user['last_name'])
				."', '".'start_menu'."'"
				.", ".$prem_time
				.")";
			echo "\n\n>>QUERY ".$query."\n";
			$res = $mysqli -> query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			run_query('forwardMessage', ['chat_id' => $chat_2_id, 'from_chat_id' => $user['chat_id'], 'message_id' => $user['message_id']]);
			$user['mod'] = 'start_menu';
			$user['root'] = 0;
			$user['premium'] = false;
			$user['premium_time'] = $prem_time;
			$user['status'] = "active";
			$user['api_key'] = "";
			$user['fh_login'] = "";
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "Вас вітає freelancehunt bot!\nПідпишіться на категорії і отримуйте сповіщення про нові проекти першими!"]);
			sleep(1);
			run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "🎁 Вам нараховано 7 днів безкоштовної підписки!"]);
		} 
		else{
			$usr = $res->fetch_assoc();
			$user['subscribes_key_text'] = $usr['key_texts'];
			$user['subscription_count'] = $usr['subscription_count'];
			$user['subscribes_stop_key_text'] = $usr['stop_texts'];
			$user['mod'] = $usr['step'];
			$user['root'] = $usr['root'];
			$user['premium'] = $usr['premium_time']?((($usr['premium_time'] - time()) > 0)?true:false):false;
			$user['premium_time'] = $usr['premium_time'];
			$user['last_click'] = $usr['last_click'];
			$user['ban'] = $usr['ban'];
			$user['status'] = $usr['status'];
			$user['api_key'] = $usr['api_key'];
			$user['fh_login'] = $usr['fh_login'];

			if ($user['status'] == 'deleted') {
				$query = "UPDATE `fhb_users` SET "
					."`status` = 'active'"
					." WHERE `chat_id` = ".$user['chat_id'];
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						$err_msg .= "n".$query;
					}
			}
			if ($user['ban'] > time()) {
				exit;
			}
			if($user['last_click'] != ""){
				$user['last_click'] = explode("|", $usr['last_click']);
				if ($user['last_click'][0] == date("d/m/Y H:i") && $user['last_click'][1] >= 40) {
					ban();
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "❗️😔 Вас забанено на 5 хв. Не клікайте так часто!"]);
				}
				else if($user['last_click'][0] != date("d/m/Y H:i")){
					$query = "UPDATE `fhb_users` SET "
					."`last_click` = '".date("d/m/Y H:i")."|1"."'"
					." WHERE `chat_id` = ".$user['chat_id'];
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						$err_msg .= "\n".$query;
					}
				}
				else if($user['last_click'][0] == date("d/m/Y H:i") && $user['last_click'][1] <= 40){
					$query = "UPDATE `fhb_users` SET "
					."`last_click` = '".$user['last_click'][0]."|".($user['last_click'][1] + 1)."'"
					." WHERE `chat_id` = ".$user['chat_id'];
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						$err_msg .= "\n".$query;
					}
				}
			}
			else{
				$query = "UPDATE `fhb_users` SET "
					."`last_click` = '".date("d/m/Y H:i")."|1"."'"
					." WHERE `chat_id` = ".$user['chat_id'];
				$res = $mysqli -> query($query);
				if (!$res) {
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					$err_msg .= "\n".$query;
				}
			}

		}	
		$tmstmp = time();
		$m = date('i', $tmstmp);
		$texttimestart = date('H:', $tmstmp).str_pad(($m - $m%5), 2, "0", STR_PAD_LEFT);
		$textdatestart = date('d/m/Y', $tmstmp);
		$res = $mysqli -> query("SELECT * FROM `fhb_activity` WHERE `date` LIKE '".$textdatestart."' AND `time` LIKE '".$texttimestart."'");
		if (mysqli_num_rows($res) == 0) {
			$query = "INSERT INTO `fhb_activity` ("
					."`time`,"
					." `date`,"
					." `users_query`"
					.") VALUES ("
					." '".my_htmlspecialchars($texttimestart)."'"
					.", '".my_htmlspecialchars($textdatestart)."'"
					.", "."1"
					.")";
				$res = $mysqli -> query($query);
				if (!$res) {
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					$err_msg .= "\n".$query;
				}
		}
		else{
				$query = "UPDATE `fhb_activity` SET "
					."`users_query` = `users_query` + 1 "
					."WHERE `date` LIKE '".$textdatestart."' AND `time` LIKE '".$texttimestart."'";
				$res = $mysqli -> query($query);
				if (!$res) {
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					$err_msg .= "\n".$query;
				}
		}
	}
	function get_keyboard($type, $txt = NULL){
		global $user, $err_msg, $backbutt, $prices;
		switch ($type) {
			case 'head':
				$inline_keyboard = [
					[
						[
							'text' => "🕹 ".'Категорії',
							'callback_data' => 'category'
						]
					],
					[
						[
							'text' => "💬 ".'Фрази',
							'callback_data' => 'phrase'
						],
						[
							'text' => "⛔️ ".'Стоп-текст',
							'callback_data' => 'stopphrase'
						]
					],
					[
						[
							'text' => "📨 ".'Повідомлення',
							'callback_data' => 'threads|1'
						],
						[
							'text' => "👨‍🔧 ".'Профіль',
							'callback_data' => 'profile'
						]
					],
					[
						[
							'text' => "🔧 ".'Підтримка',
							'url' => 'https://t.me/Solarisedigle'
						],
						[
							'text' => "📋 ".'Опис',
							'callback_data' => 'bot_description'
						],
					],

				];
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'category':
				$inline_keyboard = [
					[
						[
							'text' => "➕ ".'Додати',
							'callback_data' => 'addCategory'
						],
						[
							'text' => "❌ ".'Видалити',
							'callback_data' => 'deleteCategory'
						]
					]
				];
				$inline_keyboard[][] = back_btn('headscreen', $backbutt);
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'phrase':
				$inline_keyboard = [
					[
						[
							'text' => "➕ ".'Додати',
							'callback_data' => 'addKey'
						],
						[
							'text' => "❌ ".'Видалити',
							'callback_data' => 'deleteKey'
						]
					]
				];
				$inline_keyboard[][] = back_btn('headscreen', $backbutt);
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'stopphrase':
				$inline_keyboard = [
					[
						[
							'text' => "➕ ".'Додати',
							'callback_data' => 'addStopKey'
						],
						[
							'text' => "❌ ".'Видалити',
							'callback_data' => 'deleteStopKey'
						]
					]
				];
				$inline_keyboard[][] = back_btn('headscreen', $backbutt);
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'profile':
				if ($user['premium']) {
					$text_prem = "Продовжити підписку";
				}
				else{
					$text_prem = "Купити підписку";
				}

				if ($user['api_key'] == "") {
					$text_key = "Додати API токен";
				}
				else{
					$text_key = "Змінити API токен";
				}
				$inline_keyboard = [
					[
						[
							'text' => "🔔 ".$text_prem,
							'callback_data' => 'premium'
						]
					],
					[
						[
							'text' => "🔑 ".$text_key,
							'callback_data' => 'api_key'
						]
					]
				];
				$inline_keyboard[][] = back_btn('headscreen', $backbutt);
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'premium':
				$inline_keyboard = [
					[
						[
							'text' => "🕐 1 тиждень - ".($prices['week']/100)." грн.",
							'callback_data' => 'bp|w'
						]
					],
					[
						[
							'text' => "🕖 1 місяць - ".($prices['mounth']/100)." грн.",
							'callback_data' => 'bp|m'
						]
					]
				];
				$inline_keyboard[] = [
						back_btn('profile', $backbutt),
						[
							'text' => "💹 Додатково",
							'callback_data' => 'pm_add'
						]
					];
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'api_key':
				$inline_keyboard[][] = back_btn('profile', $backbutt);
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'bp|':
				switch ($txt) {
					case 'w':
						$lp = json_encode([
											[
												'label' => 'Тиждень підписки',
												'amount' => $prices['week']
											]
										]);
						run_query('sendInvoice', [
							'chat_id' => $user['chat_id'], 
							'title'=> 'Telegram payment', 
							'description' => "🔔 Один тиждень підписки \nна freelancehunt_shumiks_bot", 
							'payload' => '604800',
							//'provider_token' => '635983722:LIVE:i73728111721',
							'provider_token' => '535936410:LIVE:867897377_700879e2-d75f-48a0-8485-a40c1a128b7b',
							'start_parameter' => 'shoo',
							'currency' => 'UAH',
							'prices' => $lp,
							//'photo_url' => "https://shumik.site/freelancehuntbotAAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw/images/liq_nov.png",
							'need_shipping_address' => false
						]);
						break;
					case 'm':
						$lp = json_encode([
											[
												'label' => 'Місяць підписки',
												'amount' => $prices['mounth']
											]
										]);
						run_query('sendInvoice', [
							'chat_id' => $user['chat_id'], 
							'title'=> 'Telegram payment', 
							'description' => "🔔 Один місяць підписки \nна freelancehunt_shumiks_bot", 
							'payload' => '2592000',
							//'provider_token' => '635983722:LIVE:i73728111721',
							'provider_token' => '535936410:LIVE:867897377_700879e2-d75f-48a0-8485-a40c1a128b7b',
							'start_parameter' => 'shoo',
							'currency' => 'UAH',
							'prices' => $lp,
							//'photo_url' => "https://shumik.site/freelancehuntbotAAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw/images/liq_nov.png",
							'need_shipping_address' => false
						]);
						break;
				}
				break;
			case 'pm_add':
				$inline_keyboard = [];
				$inline_keyboard[] = [
						back_btn('premium', $backbutt)
					];
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'bot_description':
				$inline_keyboard = [];
				$inline_keyboard[] = [
						back_btn('headscreen', $backbutt)
					];
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'ac':
				$inline_keyboard = [];
				foreach (get_categories() as $key => $value) {
					$inline_keyboard[][] = [
						'text' => $key,
						'callback_data' => 'ac|r'.$key
					];
				}
				$inline_keyboard[][] = back_btn('category', $backbutt);
				return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'ac|r':
				$txt_ans = "";
				foreach (get_categories()[$txt] as $value) {
					$txt_ans .= "\n/add".my_str_pad($value['id'],  5, "	&#160;")." ".$value['name'];
				}
				return $txt_ans;
				break;

			case 'dc':
				$inline_keyboard = [];
					foreach (get_categories_wtht() as $key => $value) {
						$inline_keyboard[][] = [
							'text' => $key,
							'callback_data' => 'dc|r'.$key
						];
					}
					$inline_keyboard[][] = back_btn('category', $backbutt);
					return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'dc|r':
				$txt_ans = "";
				foreach (get_categories_wtht()[$txt] as $value) {
					$txt_ans .= "\n/del".my_str_pad($value['id'],  5, "	&#160;")." ".$value['name'];
				}
				return $txt_ans;
				break;
			case 'mr|':
				answer_ret_question($txt);
				return 1;
				break;
			case 'ed|':
				employeer_details($txt);
				return 1;
				break;
			case 'dk':
				$inline_keyboard = [];
					foreach (get_keytext_wtht() as $value) {
						$inline_keyboard[][] = [
							'text' => $value['key_name'],
							'callback_data' => 'dk|r'.$value['id']
						];
					}
					$inline_keyboard[][] = back_btn('phrase', $backbutt);
					return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'dsk':
				$inline_keyboard = [];
					foreach (get_stop_keytext_wtht() as $value) {
						$inline_keyboard[][] = [
							'text' => $value['stopkey_name'],
							'callback_data' => 'dsk|r'.$value['id']
						];
					}
					$inline_keyboard[][] = back_btn('stopphrase', $backbutt);
					return json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				break;
			case 'dk|r':
				return del_key_text($txt);
				break;
			case 'dsk|r':
				return del_stop_key_text($txt);
				break;
			default:
				break;
		}
	}
	function statistic($type){
		global $mysqli, $err_msg;
		switch ($type) {
			case 1:
				$url = "https://shumik.site/freelancehuntbotAAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw/library/draw.php?t=".time();
				return $url;
				break;
			case 2:
				$url = "https://shumik.site/freelancehuntbotAAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw/library/draw_lines.php?t=".time();
				return $url;
				break;
			case 3:
				$url = "https://shumik.site/freelancehuntbotAAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw/library/draw_threads.php?t=".time();
				return $url;
				break;
			default:
				return "what_a_fuck";
				break;
		}	
	}
?>