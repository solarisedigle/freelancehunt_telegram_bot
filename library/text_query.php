<?
		$user['chat_id'] = $telegram['message']['chat']['id'];
		$user['user_id'] = $telegram['message']['from']['id'];
		$user['message_id'] = $telegram['message']['message_id'];
		$user['text'] = $telegram['message']['text'];
		$user['user_name'] = $telegram['message']['from']['username'];
		$user['first_name'] = $telegram['message']['from']['first_name'];
		$user['last_name'] = $telegram['message']['from']['last_name'];
		check_user();
		function add_category($id){
			global $user, $err_msg, $mysqli, $max_params;
			if ($user['subscription_count'] < $max_params['ct']) {
				$res = $mysqli -> query("SELECT * FROM `fhb_categories` WHERE `id` = ".$id);
				if (mysqli_num_rows($res) != 0){
					$res = $res->fetch_assoc();
					$subscribers_list = $res['subscribers'];
					if (array_search($user['chat_id'], explode("|", $subscribers_list)) !== FALSE) {
							return 101;
					}
					else{
						$subscribers_list .= "|".$user['chat_id'];
						$mysqli -> query("UPDATE `fhb_categories` SET `subscribers` = '$subscribers_list' WHERE `id` = ".$id);
						$mysqli -> query("UPDATE `fhb_users` SET `subscription_count` = `subscription_count` + 1 WHERE `chat_id` = ".$user['chat_id']);
						return $res['name'];
					}
				}
				else{
					return 404;
					$err_msg .= "\nERROR add: category not found";
				}
			}
			else{
				return 102;
			}
		}
		function plusplus($id){
			global $mysqli, $err_msg, $user;
			$query = "UPDATE `fhb_threads` SET `messages_num` = `messages_num` + 1 WHERE `thread_id` = ".$id." AND `chat_id` = ".$user['chat_id'];
			$res = $mysqli->query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
		}
		function del_category($id){
			global $user, $err_msg, $mysqli;
			$res = $mysqli -> query("SELECT * FROM `fhb_categories` WHERE `id` = ".$id);
			if (mysqli_num_rows($res) != 0){
				$res = $res->fetch_assoc();
				$subscribers_list = $res['subscribers'];
				if (array_search($user['chat_id'], explode("|", $subscribers_list)) === FALSE) {
						return 101;
				}
				else{
					$sl_exp = explode("|", $subscribers_list);
					if (($key = array_search($user['chat_id'], $sl_exp)) !== false) {
					    unset($sl_exp[$key]);
					    $subscribers_list = implode("|", $sl_exp);
					}
					$mysqli -> query("UPDATE `fhb_categories` SET `subscribers` = '$subscribers_list' WHERE `id` = ".$id);
					$mysqli -> query("UPDATE `fhb_users` SET `subscription_count` = `subscription_count` - 1 WHERE `chat_id` = ".$user['chat_id']);
					return $res['name'];
				}
			}
			else{
				return 404;
				$err_msg .= "\nERROR add: category not found";
			}
		}
		function send_message($api_key, $id, $text){
			global $err_msg;
		    $ch = curl_init();
		    $json = json_encode([
		    	"message_html" => $text
		    ]);
		    curl_setopt_array($ch, array(
			  CURLOPT_URL => "https://api.freelancehunt.com/v2/threads/".$id,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_HEADER => 1,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>$json,
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/json",
			    "Authorization: Bearer ".$api_key
			  ),
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
		function add_key_text($txt){
			global $user, $mysqli, $err_msg, $max_params;
			if (count(explode("|", $user['subscribes_key_text'])) < $max_params['kt']) {
				$res = $mysqli -> query("SELECT * FROM `fhb_keytext` WHERE `key_name` LIKE '".my_htmlspecialchars($txt)."'");
				if (mysqli_num_rows($res) == 0){
					$query = "INSERT INTO `fhb_keytext` ("
						."`key_name`"
						.") VALUES ("
						."'".my_htmlspecialchars($txt)."'"
						.")";
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= "\n".$query;
						$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					}
					if ($user['subscribes_key_text'] == "") {
						$kt = $mysqli->insert_id;
					}
					else{
						$kt = $user['subscribes_key_text']."|".$mysqli->insert_id;
					}
					$res = $mysqli -> query("UPDATE `fhb_users` SET `key_texts` = '".$kt."' WHERE `chat_id` = ".$user['chat_id']);
						if (!$res) {
							$err_msg .= "\n".$query;
							$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						}
				}
				else{
					$res = $res->fetch_assoc();
					$neededid = $res['id'];
					if (array_search($neededid, explode("|", $user['subscribes_key_text'])) !== FALSE) {
							return 101;
					}
					else{
						if ($user['subscribes_key_text'] == "") {
							$kt = $neededid;
						}
						else{
							$kt = $user['subscribes_key_text']."|".$neededid;
						}
						$res = $mysqli -> query("UPDATE `fhb_users` SET `key_texts` = '".$kt."' WHERE `chat_id` = ".$user['chat_id']);
						if (!$res) {
							$err_msg .= "\n".$query;
							$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						}
					}
				}
			}
			else{
				return 102;
			}
		}
		function add_stop_key_text($txt){
			global $user, $mysqli, $err_msg, $max_params;
			if (count(explode("|", $user['subscribes_stop_key_text'])) < $max_params['skt']) {
				$res = $mysqli -> query("SELECT * FROM `fhb_stoptext` WHERE `stopkey_name` LIKE '".my_htmlspecialchars($txt)."'");
				if (mysqli_num_rows($res) == 0){
					$query = "INSERT INTO `fhb_stoptext` ("
						."`stopkey_name`"
						.") VALUES ("
						."'".my_htmlspecialchars($txt)."'"
						.")";
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= "\n".$query;
						$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					}
					if ($user['subscribes_stop_key_text'] == "") {
						$kt = $mysqli->insert_id;
					}
					else{
						$kt = $user['subscribes_stop_key_text']."|".$mysqli->insert_id;
					}
					$res = $mysqli -> query("UPDATE `fhb_users` SET `stop_texts` = '".$kt."' WHERE `chat_id` = ".$user['chat_id']);
						if (!$res) {
							$err_msg .= "\n".$query;
							$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						}
				}
				else{
					$res = $res->fetch_assoc();
					$neededid = $res['id'];
					if (array_search($neededid, explode("|", $user['subscribes_stop_key_text'])) !== FALSE) {
						return 101;
					}
					else{
						if ($user['subscribes_stop_key_text'] == "") {
							$kt = $neededid;
						}
						else{
							$kt = $user['subscribes_stop_key_text']."|".$neededid;
						}
						$res = $mysqli -> query("UPDATE `fhb_users` SET `stop_texts` = '".$kt."' WHERE `chat_id` = ".$user['chat_id']);
						if (!$res) {
							$err_msg .= "\n".$query;
							$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						}
					}
				}
			}
			else{
				return 102;
			}
		}
		function add_api_key_text($txt){
			global $mysqli, $user, $err_msg;
			$freelancer_info = json_decode(get_my_profile($txt)[1], true);
			if (array_key_exists("error", $freelancer_info)) {
				return 101;
			}
			else{
				$query = "UPDATE `fhb_users` SET `step` = 'start_menu', `api_key` = '".$txt."', `fh_login` = '".$freelancer_info['data']['attributes']['login']."' WHERE `chat_id` = ".$user['chat_id'];
				$res = $mysqli -> query($query);
				if ($res) {
					return $freelancer_info;
				}
				else{
					$err_msg .= "\n".$query;
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
				}
			}
			return 202;
		}
		require_once(__DIR__."/threads.php");
		if (count(explode("get|", $user['mod'])) == 2){
			$msg = explode("get|", $user['mod'])[1];
			if($user['text'] == $head_menubutt || (count(explode("/", $user['text'])) == 2 && explode("/", $user['text'])[0] == "")){
				$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
				$res = $mysqli -> query($query);
				if (!$res) {
					$err_msg .= "\n".$query;
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
				}
			}
			else if ($msg == "kt_add") {
				$back_btn[][] = back_btn('phrase', "$backbutt");
				$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
				if ((mb_strlen($user['text']) <= $max_text_width) && (mb_strlen($user['text']) >= $min_text_width)) {
					$result = add_key_text(mb_strtolower($user['text']));
					if($result == 101){
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<i>Ви вже підписані на цю фразу...</i>", 'reply_markup' => $btn_back]);
					}
					else if($result == 102){
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<i>Ліміт (".$max_params['kt'].") вичерпано, видаліть менш потрібні.</i>", 'reply_markup' => $btn_back]);
					}
					else{
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Успіх! тепер ви підписані на цю фразу</b>\n\n<i>Яка фраза наступна?</i>", 'reply_markup' => $btn_back]);
					}
				}
				else if(mb_strlen($user['text']) < $min_text_width){
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Надто короткий текст</b>\n\n<i>Введіть хоча б ".$min_text_width." символів</i>", 'reply_markup' => $btn_back]);
				}
				else{
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Надто довгий текст</b>\n\n<i>Максимум ".$max_text_width." символів</i>", 'reply_markup' => $btn_back]);
				}
			}
			else if ($msg == "skt_add") {
				$back_btn[][] = back_btn('stopphrase', "$backbutt");
				$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
				if ((mb_strlen($user['text']) <= $max_text_width) && (mb_strlen($user['text']) >= $min_text_width)) {
					$result = add_stop_key_text(mb_strtolower($user['text']));
					if($result == 101){
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<i>Ви вже ігноруєте цю фразу...</i>", 'reply_markup' => $btn_back]);
					}
					else if($result == 102){
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<i>Ліміт (".$max_params['kt'].") вичерпано, видаліть менш потрібні.</i>", 'reply_markup' => $btn_back]);
					}
					else{
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Успіх! тепер ви не отримуватимете проекти з таким текстом</b>\n\n<i>Яка фраза наступна?</i>", 'reply_markup' => $btn_back]);
					}
				}
				else if(mb_strlen($user['text']) < $min_text_width){
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Надто короткий текст</b>\n\n<i>Введіть хоча б ".$min_text_width." символів</i>", 'reply_markup' => $btn_back]);
				}
				else{
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Надто довгий текст</b>\n\n<i>Максимум ".$max_text_width." символів</i>", 'reply_markup' => $btn_back]);
				}
			}
			else if ($msg == "ak_add") {
				$back_btn[][] = back_btn('profile', "$backbutt");
				$btn_back = json_encode(['inline_keyboard' =>  $back_btn, 'resize_keyboard' => true]);
				$result = add_api_key_text(trim($user['text']));
				if ($result == 101) {
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Не вірний API ключ, перевірте та відправте ще раз</b>", 'reply_markup' => $btn_back]);
				}
				else if($result == 202){
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>Щось пішло не так, перевірте та відправте ще раз</b>", 'reply_markup' => $btn_back]);
				}
				else{
					$message = $result['data']['attributes']['first_name']." ".$result['data']['attributes']['last_name'];
					run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => "<b>API ключ успішно додано! </b>".$message.", тепер вам стануть доступні розширені можливості!", 'reply_markup' => $btn_back]);
				}
				
			}
			else if (count(explode("ms_add:", $msg)) == 2) {
				$id = explode("ms_add:", $msg)[1];
				if ($user['api_key'] != "") {
					$res = send_message($user['api_key'], $id, $user['text']);
					plusplus($id);
					$res = json_decode($res[1], true);
					if (array_key_exists("error", $res)) {
						if ($res['error']['status'] == 401) {
							$text = "<b>🗝 Здається, ваш API токен більше не дійсний.</b>\n\n<i>Вкажіть коректний API токен, щоб надалі отримувати особисті повідомлення з freelancehunt.com</i>";
							$keyboard = [
									[
										[
											'text' => "🔑 Додати API токен",
											'callback_data' => 'api_key'
										],
										[
											'text' => $menubutt,
											'callback_data' => 'headscreen_new'
										]
									]
								];
						}
						else{
							$text = "<b>Здається виникли проблеми з доступом до freelancehunt.com</b>\n\n<i>Спробуйте пізніше</i>";
							$err_msg .= "\nFreelancehunt: ".$res['error']['status'].": ".$res['error']['title'];
							$keyboard = [
									[
										[
											'text' => $backbutt,
											'callback_data' => 'threads|1'
										],
										[
											'text' => $menubutt,
											'callback_data' => 'headscreen_new'
										]
									]
								];
						}
						$keyboard = json_encode(['inline_keyboard' =>  $keyboard, 'resize_keyboard' => true]);
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => $text, 'reply_markup' => $keyboard]);
					}
					else{
						$response = threads("threads:".$id.":0");
						$keyboard = $response[1];
						$message = $response[0];
						run_query('sendMessage', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'text' => $message, 'reply_markup' => $keyboard, 'disable_web_page_preview' => true]);
					}
				}
			}
		}

?>