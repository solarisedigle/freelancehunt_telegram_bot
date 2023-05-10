<?
	require_once(__DIR__."/library/variables.php");

	function file_get_contents_curl($url){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $data = curl_exec($ch);
	    return $data;
	}
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
		$res = $mysqli->query("INSERT INTO `fhb_queue` (`query`, `priority`, `chat_id`) VALUES ('".$query."', 0, '".$params['chat_id']."')");
		if ($res) {
			return true;
		}
		else{
			return false;
			//echo file_get_contents_curl($query);
		}
	}


	$mysqli->query("DELETE FROM `fhb_projects` WHERE `download_date` < ".(time() - 8640000));
	$err_msg = "Start ".date('H:i:s')."\nDeleted ".$mysqli->affected_rows." rows";
	run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => $err_msg]);
	
	$title = "<b>ОСТАННІ ПРОЕКТИ:</b>";
	$end = "\n\nКупіть підписку, щоб отримувати сповіщення миттєво! ❤️";
	$inline_keyboard = [
					[
						[
							'text' => $menubutt,
							'callback_data' => 'headscreen_new'
						]
					],
					[
						[
							'text' => '🔔 Купити підписку',
							'callback_data' => 'premium_new'
						]
					]
				];
	$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
	$mysqli->set_charset('utf8mb4');
	$free_us = $mysqli->query("SELECT * FROM `fhb_free`");
	if ($free_us) {
		while ($user = $free_us->fetch_assoc()) {
			$mdg = $user['message'];
			$sendm = $title.$mdg.$end;
			if (run_query('sendMessage', ['chat_id' => $user['chat_id'], 'text' => $sendm, 'reply_markup' => $inline_keyboard, 'parse_mode'=> 'HTML', 'disable_web_page_preview' => true])) {
				usleep(600000);
				$mysqli->query("DELETE FROM `fhb_free` WHERE `chat_id` = ".$user['chat_id']);
			}
			else{
				echo $mysqli->error;
			}
		}
	}
	
	
?>