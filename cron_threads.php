<?
	require_once(__DIR__."/library/variables.php");
	$timestart = microtime(true);
	$info_ar = [
		'time' => "",
		'date' => "",
		'iterations' => 0,
		'time_count' => 0
	];
	$min = date('i', $timestart);
	$info_ar['time'] = date('H:i');
	$info_ar['date'] = date('d/m/Y', $timestart);
	function console_log($line){
		global $mysqli;
		$aux =  microtime(true);
		$now = DateTime::createFromFormat('U.u', $aux);        
		if (is_bool($now)) $now = DateTime::createFromFormat('U.u', $aux += 0.001);
		$mysqli->query("INSERT INTO `fhb_log` (`line`, `time`) VALUES ('".$line."', '".$now->format("H:i:s.u")."')");
	}
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
		$res = $mysqli->query("INSERT INTO `fhb_queue` (`query`, `priority`, `chat_id`) VALUES ('".$query."', 1, '".$params['chat_id']."')");
		//return "???? - ".file_get_contents($query);
	}
	function run_my_last(){	
		global $mysqli, $chat_2_id, $err_msg, $info_ar, $timestart;
		$info_ar['time_count'] = time() - $timestart;
		$res = $mysqli -> query("SELECT * FROM `fhb_thread_iterations` WHERE  `time` LIKE '".$info_ar['time']."'");
		if (mysqli_num_rows($res) == 0) {
			$query = "INSERT INTO `fhb_thread_iterations` ("
					."`time`,"
					." `date`,"
					." `time_count`,"
					." `iterations`"
					.") VALUES ("
					."'".$info_ar['time']."'"
					.", '".$info_ar['date']."'"
					.", ".$info_ar['time_count']
					.", ".$info_ar['iterations']
					.")";
				
				$res = $mysqli -> query($query);
				if (!$res) {
					$err_msg .= "\n".$query;
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
				}
		}
		else{
				$query = "UPDATE `fhb_thread_iterations` SET "
					."`time_count`= ".$info_ar['time_count']
					.", `iterations`= ".$info_ar['iterations']
					.", `date`= "."'".$info_ar['date']."'"
					." WHERE `time` LIKE '".$info_ar['time']."'";
				
				$res = $mysqli -> query($query);
				if (!$res) {
					$err_msg .= "\n".$query;
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
				}
		}
		$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'free' WHERE `param` LIKE 'threads_cron'");
		if ($err_msg != "") {
			run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => $err_msg]);
		}
	}
	register_shutdown_function('run_my_last');
	function get_threads($api_key){
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
	    curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/threads");
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
	function check_threads($chat_id, $api_key, $fh_login){	
		global $mysqli, $err_msg, $apikey, $menubutt;
		$threads = json_decode(get_threads($api_key)[1], true);
		if (array_key_exists("error", $threads)) {
			if ($threads['error']['status'] == 401) {
				$query = "UPDATE `fhb_users` SET `api_key` = '' WHERE `chat_id` = ".$chat_id;
				$res = $mysqli -> query($query);
				if ($res) {
						$inline_keyboard = [
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
						$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
						$message = "<b>🗝 Здається, ваш API токен більше не дійсний.</b>\n\n<i>Вкажіть коректний API токен, щоб надалі отримувати особисті повідомлення з freelancehunt.com</i>";
						run_query('sendMessage', ['chat_id' => $chat_id, 'text' => $message, 'reply_markup' => $inline_keyboard, 'parse_mode'=> 'HTML']);
				}
				else{
					$err_msg .= "\n".$query;
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
				}
			}
			return;
		}
		foreach ($threads['data'] as $thread) {
			if ($thread['attributes']['is_unread']) {
				$query = "SELECT * FROM `fhb_threads` WHERE `thread_id` = ".$thread['id']." AND `chat_id` = ".$chat_id;
				$res = $mysqli->query($query);
				if (!$res) {
					$err_msg .= "\n".$query;
					$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
				}
				else{
					if (mysqli_num_rows($res) != 0){
						$row = $res->fetch_assoc();
 						if($row['messages_num'] != $thread['attributes']['messages_count']){
							$query = "UPDATE `fhb_threads` SET `messages_num` = ".$thread['attributes']['messages_count']." WHERE `thread_id` = ".$thread['id']." AND `chat_id` = ".$chat_id;
							$res = $mysqli->query($query);
							if (!$res) {
								$err_msg .= "\n".$query;
								$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
							}
						}
						else{
							return;
						}
					}
					else if(mysqli_num_rows($res) == 0){
						$query = "INSERT INTO `fhb_threads` (`thread_id`,`messages_num`, `chat_id`) VALUES (".$thread['id'].", ".$thread['attributes']['messages_count'].", ".$chat_id.")";
						$res = $mysqli->query($query);
						if (!$res) {
							$err_msg .= "\n".$query;
							$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
						}
					}

					$inline_keyboard = [
								[
									[
										'text' => $menubutt,
										'callback_data' => 'headscreen_new'
									],
									[
										'text' => 'Відкрити',
										'url' => $thread['links']['self']['web']
									]
								],
								[
									[
										'text' => '📨 Відкрити тут',
										'callback_data' => 'threads:'.$thread['id'].":0"
									]
								]
							];
					$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
					if ($thread['attributes']['participants']['from']['login'] == $fh_login) {
						$side = 'to';
					}
					else{
						$side = 'from';
					}
					if ($thread['attributes']['subject'] != "") {
						$subject = "\n<code>Чат:</code><i>".$thread['attributes']['subject']."</i>";
					}
					else{
						$subject = "";
					}
					$message = "<b>📩 Нове повідомлення!</b>\n———".$subject."\n<code>Від:</code> ".$thread['attributes']['participants'][$side]['first_name']." ".$thread['attributes']['participants'][$side]['last_name'];
					run_query('sendMessage', ['chat_id' => $chat_id, 'text' => $message, 'reply_markup' => $inline_keyboard, 'parse_mode'=> 'HTML']);

				}
			}
		}
	}
	function is_free(){
		global $mysqli, $chat_2_id;
		$res = $mysqli->query("SELECT * FROM `fhb_custom_fields` WHERE `param` LIKE 'threads_cron'");
		if ($res && (mysqli_num_rows($res) != 0)) {
			$res = $res->fetch_assoc();
			if ($res['value'] == 'free') {
				return true;
			}
		}
		return false;
	}
	function is_wait(){
		global $mysqli, $chat_2_id;
		$res = $mysqli->query("SELECT * FROM `fhb_custom_fields` WHERE `param` LIKE 'threads_cron'");
		if ($res && (mysqli_num_rows($res) != 0)) {
			$res = $res->fetch_assoc();
			if ($res['value'] == 'wait') {
				exit;
			}
		}
		return false;
	}
	function recheck_threads(){
		global $mysqli, $err_msg, $info_ar;
		$info_ar['iterations'] += 1;
		$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = '".date('H:i:s')."' WHERE `param` LIKE 'time_threads_query'");
		$res = $mysqli->query("SELECT * FROM `fhb_users` WHERE `premium_time` > ".time(true)." AND `status` != 'deleted' AND `api_key` != ''");
		$users = [];
		if ($res && mysqli_num_rows($res) != 0) {
			while ($row = $res->fetch_assoc()) {
				check_threads($row['chat_id'], $row['api_key'], $row['fh_login']);
			}
		}
		else if(!$res){
			$err_msg .= "\n".$query;
			$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
		}
	}

	function run(){
		global $mysqli, $chat_2_id, $info_msg;
		$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'work' WHERE `param` LIKE 'threads_cron'");
		while (1) {
			sleep(6);
			is_wait();
			recheck_threads();
		}

	}
	
	$res = $mysqli->query("SELECT * FROM `fhb_custom_fields` WHERE `param` LIKE 'threads_cron'");
	if ($res && (mysqli_num_rows($res) != 0)) {
		$res = $res->fetch_assoc();
		if (($res['value'] == 'free') || ($res['value'] == 'wait')) {
			run();
		}
		else if($res['value'] == 'work'){
			$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'wait' WHERE `param` LIKE 'threads_cron'");
			while (!is_free()) {
				usleep(500000);
			}
			run();
		}
	}
	else{
		run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => mysqli_num_rows($res)." ERROR: ".$mysqli->error]);
	}


	
?>