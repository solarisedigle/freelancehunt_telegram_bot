<?
	date_default_timezone_set('Europe/Kiev');
	$mysqli = new mysqli("localhost", "shumik", "03102002Vitalik", "shumik_base");
	$chat_2_id = "-1001179542926";
	$apikey = '867897377:AAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw';
	$timestart = microtime(true);
	$m = date("H:i");
	$info_msg = [
		"error" => "",
		"info" => [
			"time" => 0,
			"date" => 0,
			"average_time" => 0,
			"queries" => [
				"success" => 0,
				"fail" => 0
			],
			"loops_num" => 0
		],
	];
	$min = date('i', $timestart);
	$info_msg['info']['time'] = date('H:i');
	$info_msg['info']['date'] = date('d/m/Y', $timestart);
	function file_get_contents_curl($url){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $data = curl_exec($ch);
	    return $data;
	}
	function run_query($method_name, $params){
		global $apikey;
		$str_params = "?shit=you";
		foreach ($params as $key => $value) {
			if ($key == 'photo' || $key == 'document') {
				if (count(explode("http", $value)) == 1){
					$value = 'https://'.$_SERVER['SERVER_NAME'].explode($_SERVER['SERVER_NAME'],dirname(__FILE__))[1].'/'.$value;
				}
			}
			$str_params .= "&".$key."=".urlencode($value);
		}
		$query = 'https://api.telegram.org/bot'.$apikey.'/'.$method_name.$str_params;
		$answer = file_get_contents_curl($query);
		if ($answer !== FALSE) {
			$answer_d = json_decode($answer, true);
			if (!$answer_d['ok']) {
				$err_msg .= "\nERROR: ".$answer;
				return false;
			}
		}
		return true;
	}
	function console_log($line){
		global $mysqli;
		$aux =  microtime(true);
		$now = DateTime::createFromFormat('U.u', $aux);        
		if (is_bool($now)) $now = DateTime::createFromFormat('U.u', $aux += 0.001);
		//$mysqli->query("INSERT INTO `fhb_log` (`line`, `time`) VALUES ('".$line."', '".$now->format("H:i:s.u")."')");
	}
	function run_my_last(){	
		global $mysqli, $timestart, $chat_2_id, $m, $info_msg;
		$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'free' WHERE `param` LIKE 'queue_cron_status'");
		$aux =  microtime(true);
		$now = DateTime::createFromFormat('U.u', $aux);        
		if (is_bool($now)) $now = DateTime::createFromFormat('U.u', $aux += 0.001);
		//run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => "[$m]Stop: ".$now->format("i:s.u")]);
		$sum = $info_msg['info']['queries']['success'] + $info_msg['info']['queries']['fail'];
		if ($sum != 0) {
			$info_msg['info']['average_time'] = $info_msg['info']['average_time']/$sum;
		}
		else{
			$info_msg['info']['average_time'] = 0;
		}
		$res = $mysqli -> query("SELECT * FROM `fhb_queue_activity` WHERE  `time` LIKE '".$info_msg['info']['time']."'");
		if (mysqli_num_rows($res) == 0) {
			$query = "INSERT INTO `fhb_queue_activity` ("
					."`time`,"
					." `date`,"
					." `average_time`,"
					." `loops_num`,"
					." `fail`,"
					." `success`"
					.") VALUES ("
					."'".$info_msg['info']['time']."'"
					.", '".$info_msg['info']['date']."'"
					.", ".$info_msg['info']['average_time']
					.", ".$info_msg['info']['loops_num']
					.", ".$info_msg['info']['queries']['fail']
					.", ".$info_msg['info']['queries']['success']
					.")";
				
				$res = $mysqli -> query($query);
				if (!$res) {
					run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => " ERROR: ".$mysqli->error."\n".$query]);
				}
		}
		else{
				$query = "UPDATE `fhb_queue_activity` SET "
					."`average_time`= ".$info_msg['info']['average_time']
					.", `loops_num`= ".$info_msg['info']['loops_num']
					.", `fail`= ".$info_msg['info']['queries']['fail']
					.", `success`= ".$info_msg['info']['queries']['success']
					.", `date`= "."'".$info_msg['info']['date']."'"
					." WHERE `time` LIKE '".$info_msg['info']['time']."'";
				
				$res = $mysqli -> query($query);
				if (!$res) {
					run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => " ERROR: ".$mysqli->error."\n".$query]);
				}
		}
	}
	register_shutdown_function('run_my_last');

	

	function is_free(){
		global $mysqli, $chat_2_id;
		$res = $mysqli->query("SELECT * FROM `fhb_custom_fields` WHERE `param` LIKE 'queue_cron_status'");
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
		$res = $mysqli->query("SELECT * FROM `fhb_custom_fields` WHERE `param` LIKE 'queue_cron_status'");
		if ($res && (mysqli_num_rows($res) != 0)) {
			$res = $res->fetch_assoc();
			if ($res['value'] == 'wait') {
				exit;
			}
		}
		return false;
	}
	require_once(__DIR__."/library/multi_curl.php");
	function send_queries(){
		global $mysqli, $chat_2_id, $m, $info_msg;
		$mysqli->query("UPDATE `fhb_custom_fields` SET `value` = '".date('H:i:s')."' WHERE `param` LIKE 'time_query'");
		$query = "
			SELECT 
				*
			FROM
				`fhb_queue`
			ORDER BY
				`priority` DESC,
				`id` ASC
			LIMIT 10
		";
		$res = $mysqli->query($query);
		if ($res && (mysqli_num_rows($res) != 0)) {
			$send = [];
			while ($row = $res->fetch_assoc()) {
				$send[$row['chat_id']."|".$row['id']."|".$row['priority']] = [
					'url' => $row['query']
				];
			}
			console_log($info_msg['info']['loops_num'].' - sending queries start: '.mysqli_num_rows($res));
			$tme = microtime(true);
			//$mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'sends | ".count($send)." | ".date('H:i:s')."' WHERE `param` LIKE 'last_result'");
			$responses = multi($send);
			$mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'responses | ".count($responses)." | ".date('H:i:s')."' WHERE `param` LIKE 'last_result'");
			$info_msg['info']['average_time'] += microtime(true) - $tme;
			console_log($info_msg['info']['loops_num'].' - sending queries end: '.count($responses));
			$query_refresh_base = [];
			$query_reject_base = [];
			$query_deleted_base = [];
			$query_log_base = [];
			foreach ($responses as $key => $value) {
				if (!$value['info']['http_code']) continue;
				$id = explode("|", $key)[1];
				$priority = explode("|", $key)[2];
				$chat_id = explode("|", $key)[0];
				$query_refresh_base[] = $id;
				$key_need = "success";
				if (($value['info']['http_code'] != 200) && $value['info']['http_code']) {
					$query_reject_base[] = "('".$value['info']['url']."', ".$id.", '".$priority."', '".$value['info']['http_code'].": ".$value['data']."', ".$chat_id.")";
					$key_need = "fail";
					if ($value['info']['http_code'] == 403) {
						$query_deleted_base[] = $chat_id;
					}
				}
				$query_log_base[] = "('".$value['info']['url']."', ".time().", '".$priority."', '".$value['info']['http_code'].": ".$value['data']."', ".$chat_id.", ".$id.", ".$info_msg['info']['loops_num'].")";
				$info_msg['info']['queries'][$key_need] += 1;
			}
			if (count($query_refresh_base) != 0) {
				console_log($info_msg['info']['loops_num'].' - DELETE: '.count($query_refresh_base));
				$query_refresh_base = "DELETE FROM `fhb_queue` WHERE `id` IN(".implode(",", $query_refresh_base).")";
				if (!$mysqli->query($query_refresh_base)) {
					console_log($mysqli->error);
				}
				$how_least = $mysqli->query("SELECT * FROM `fhb_queue`");
				console_log($info_msg['info']['loops_num'].' - LEAST: '.mysqli_num_rows($how_least));
			}
			if (count($query_deleted_base) != 0) {
				$query_deleted_base = "UPDATE `fhb_users` SET `status` = 'deleted' WHERE `chat_id` IN(".implode(",", $query_deleted_base).")";
				if (!$mysqli->query($query_deleted_base)) {
					console_log($mysqli->error);
				}
			}
			if (count($query_reject_base) != 0) {
				$query_reject_base = "INSERT INTO `fhb_rejected` (`query`, `id`, `priority`, `reason`, `chat_id`) VALUES ".implode(", ", $query_reject_base);
				if (!$mysqli->query($query_reject_base)) {
					console_log($mysqli->error);
				}
			}
			/*if (count($query_log_base) != 0) {
				$query_log_base = "INSERT INTO `fhb_queries_log` (`query`, `time`, `priority`, `answer`, `chat_id`, `id`, `loop_num`) VALUES ".implode(", ", $query_log_base);
				if (!$mysqli->query($query_log_base)) {
					console_log($mysqli->error);
				}
			}*/
		}
		else{
			$mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'no queries | ".date('H:i:s')."' WHERE `param` LIKE 'last_result'");
		}
	}
	function run(){
		global $mysqli, $chat_2_id, $m, $info_msg;
		$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'work' WHERE `param` LIKE 'queue_cron_status'");
		while (1) {
			//$res = $mysqli->query("DELETE FROM `fhb_log`");
			is_wait();
			usleep(500000);
			send_queries();
			$info_msg['info']['loops_num'] += 1;
		}

	}
	$res = $mysqli->query("SELECT * FROM `fhb_custom_fields` WHERE `param` LIKE 'queue_cron_status'");
	if ($res && (mysqli_num_rows($res) != 0)) {
		$res = $res->fetch_assoc();
		if (($res['value'] == 'free') || ($res['value'] == 'wait')) {
			run();
		}
		else if($res['value'] == 'work'){
			$res = $mysqli->query("UPDATE `fhb_custom_fields` SET `value` = 'wait' WHERE `param` LIKE 'queue_cron_status'");
			while (!is_free()) {
				usleep(1000000);
			}
			run();
		}
	}
	else{
		run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => mysqli_num_rows($res)." ERROR: ".$mysqli->error]);
	}


	
?>