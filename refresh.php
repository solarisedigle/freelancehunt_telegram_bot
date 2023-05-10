<?
	$timestart = microtime(true);
	date_default_timezone_set('Europe/Kiev');
	$m = date('i', $timestart);
	$texttimestart = date('H:', $timestart).str_pad(($m - $m%5), 2, "0", STR_PAD_LEFT);
	$textdatestart = date('d/m/Y', $timestart);
	$apikey = '867897377:AAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw'; 
	$chat_id = "-1001179542926";
	$info_msg = [
		"error" => "",
		"info" => [
			"new_projects" => 0,
			"new_employers" => 0,
			"old_employers" => 0,
			"time_details" => [
				"time_spent" => "",
				"query_time" => 0,
				"send_time" => ""
			],
			"users_notificated" => 0
		],
	];
	$notificated_list = [];
	$arr_pages = [];
	$arr_eployers = [];
	$endrayy = [];
	$mysqli = new mysqli("localhost", "shumik", "03102002Vitalik", "shumik_base");
	$mysqli->set_charset("utf8mb4");

//get users with subscription and check all stopphrases

	$array_all_keywords = [];
	$array_all_stopwords = [];
	$users_list = [];
	$premium_list = [];


	$res = $mysqli->query("SELECT * FROM `fhb_users` WHERE `status` != 'deleted'");
	if (mysqli_num_rows($res) != 0) {
		while($row = $res->fetch_assoc()){
			if ($row['key_texts'] != '') {
				$ar = array_flip(explode("|", $row['key_texts']));
				foreach ($ar as $key => $value) {
					if (!array_key_exists($key, $array_all_keywords)) {
						$array_all_keywords[$key] = [];
					}
					$array_all_keywords[$key][] = $row['chat_id'];
				}
			}
			if ($row['stop_texts'] != '') {
				$ar = array_flip(explode("|", $row['stop_texts']));
				foreach ($ar as $key => $value) {
					if (!array_key_exists($key, $array_all_stopwords)) {
						$array_all_stopwords[$key] = [];
					}
					$array_all_stopwords[$key][] = $row['chat_id'];
				}
			}
			$users_list[] = $row['chat_id'];
			$premium_list[$row['chat_id']] = $row['premium_time'] > time(true);
		}
		$users_list[] = "@ffh_ch";
		$premium_list["@ffh_ch"] = true;
		$res_kt = $mysqli->query("SELECT * FROM `fhb_keytext` WHERE `id` IN(".implode(', ',array_keys($array_all_keywords)).")");
		if ($res_kt) {
			while ($row = $res_kt->fetch_assoc()) {
				$tmp = $array_all_keywords[$row['id']];
				unset($array_all_keywords[$row['id']]);
				$array_all_keywords[$row['key_name']] = $tmp;
			}
		}
		else{
			echo "\n".$mysqli->error;
		}
		$res_skt = $mysqli->query("SELECT * FROM `fhb_stoptext` WHERE `id` IN(".implode(', ',array_keys($array_all_stopwords)).")");
		if ($res_skt) {
			while ($row = $res_skt->fetch_assoc()) {
				$tmp = $array_all_stopwords[$row['id']];
				unset($array_all_stopwords[$row['id']]);
				$array_all_stopwords[$row['stopkey_name']] = $tmp;
			}
		}
		else{
			echo "\n".$mysqli->error;
		}
		
		print_r($array_all_keywords);
		print_r($array_all_stopwords);
		print_r($users_list);
	}
	else{
		exit();
	}
	


	function get_employeer($id){
		global $arr_eployers;
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
	    curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/employers/".$id);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    "Content-Type: application/json",
		    "Accept-Language: uk",
		    "Authorization: Bearer 6590789f7ca416b9ed51a64a573b0a6eb77edac7"
		  ));
	    $data = curl_exec($ch);
	    
	    $data2 = curl_getinfo($ch);
	    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($data, 0, $header_size);
		$body = substr($data, $header_size);
		$arr_eployers[$id] = $body;
		curl_close($ch);
	    return array($header, $body);
	}	
	function get_new_progjects($page){
		global $info_msg;
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
	    curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/projects?page[number]=".$page);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, [
		    "Content-Type: application/json",
		    "Accept-Language: uk",
		    "Authorization: Bearer 6590789f7ca416b9ed51a64a573b0a6eb77edac7"
		  ]);
	    $data = curl_exec($ch);
	    $data2 = curl_getinfo($ch);
	    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($data, 0, $header_size);
		$body = substr($data, $header_size);

		curl_close($ch);
	    return array($header, $body);
	}	
	function my_htmlspecialchars($txt){
		global $mysqli;
		return $mysqli->real_escape_string($txt);
	}/*
	function prepare_c($txt){
		global $mysqli;
		return $mysqli->real_escape_string($txt);
		//return str_replace("'", "''", str_replace("`", "``",$txt));
	}*/
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
	function notificate_about_subscription(){
		global $mysqli;
		$query = "SELECT * FROM `fhb_users` WHERE `premium_time` < ".time()." AND `premium_time` != 0";
		$res = $mysqli->query($query);
		if ($res && (mysqli_num_rows($res) != 0)) {
			$inline_keyboard = [
					[
						[
							'text' => '🔔 Продовжити підписку',
							'callback_data' => 'premium'
						]
					]
				];
			$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
			$msg = "<b>Нажаль,</b> термін дії вашої підписки минув.\nНадіємося, що вам все подобається!\n\nТепер проекти будуть приходити одним повідомленням раз в 4 години.\n\n Підпишіться, щоб надалі отримувати сповіщення миттєво!";
			while ($row = $res->fetch_assoc()) {
				print_r($row);
				$query = "UPDATE `fhb_users` SET `premium_time` = 0 WHERE `chat_id` = ".$row['chat_id'];
				$res2 = $mysqli->query($query);
				if ($res2) {
					run_query('sendMessage', ['chat_id' => $row['chat_id'], 'text' => $msg, 'reply_markup' => $inline_keyboard, 'parse_mode'=> 'HTML']);
				}
			}
		}
	}
	function check_employer($empl){
		global $mysqli, $info_msg;
		$res = $mysqli -> query("SELECT * FROM `fhb_employers` WHERE `id` LIKE '".$empl['id']."'");
		$employer_answer = "";
		for ($i=0; $employer_answer == ""; $i++) { 
			if ($i >= 7) {
				return;
			}
			sleep(1);
			$employer_answer = get_employeer($empl['id'])[1];
		}
		

		$empl_detail = json_decode($employer_answer, true);
		if (array_key_exists("location", $empl_detail['data']['attributes'])) {
			$location = $empl_detail['data']['attributes']['location']['country']['id'].":".$empl_detail['data']['attributes']['location']['country']['name'];
			if (array_key_exists("city", $empl_detail['data']['attributes']['location'])) {
				$location .= "|".$empl_detail['data']['attributes']['location']['city']['id'].":".$empl_detail['data']['attributes']['location']['city']['name'];
			}
		}
		else{
			$location = "";
		}
		$creation_date = (new DateTime($empl_detail['data']['attributes']['visited_at']))->format('U');
		if (mysqli_num_rows($res) == 0){
			$info_msg['info']['new_employers'] += 1;
			$query = "INSERT INTO `fhb_employers` ("
				."`id`,"
				." `login`,"
				." `first_name`,"
				." `last_name`,"
				." `avatar_small`,"
				." `avatar_large`,"
				." `link`,"
				." `rating_position`,"
				." `arbitrages`,"
				." `positive_reviews`,"
				." `negative_reviews`,"
				." `creation_date`,"
				." `location`"
				.") VALUES ("
				.my_htmlspecialchars($empl['id'])
				.", '".my_htmlspecialchars($empl['login'])."'"
				.", '".my_htmlspecialchars($empl['first_name'])."'"
				.", '".my_htmlspecialchars($empl['last_name'])."'"
				.", '".my_htmlspecialchars($empl['avatar']['small']['url'])."'"
				.", '".my_htmlspecialchars($empl['avatar']['large']['url'])."'"
				.", '".my_htmlspecialchars($empl['self'])."'"
				.", '".my_htmlspecialchars($empl_detail['data']['attributes']['rating_position'])."'"
				.", '".my_htmlspecialchars($empl_detail['data']['attributes']['arbitrages'])."'"
				.", '".my_htmlspecialchars($empl_detail['data']['attributes']['positive_reviews'])."'"
				.", '".my_htmlspecialchars($empl_detail['data']['attributes']['negative_reviews'])."'"
				.", '".my_htmlspecialchars($creation_date)."'"
				.", '".my_htmlspecialchars($location)."'"
				.")";
		}
		else{
			$info_msg['info']['old_employers'] += 1;
			$query = "UPDATE `fhb_employers` SET "
				."`id`= ".my_htmlspecialchars($empl['id'])
				.", `login`= '".my_htmlspecialchars($empl['login'])."'"
				.", `first_name`= '".my_htmlspecialchars($empl['first_name'])."'"
				.", `last_name`= '".my_htmlspecialchars($empl['last_name'])."'"
				.", `avatar_small`= '".my_htmlspecialchars($empl['avatar']['small']['url'])."'"
				.", `avatar_large`= '".my_htmlspecialchars($empl['avatar']['large']['url'])."'"
				.", `link`= '".my_htmlspecialchars($empl['self'])."'"
				.", `rating_position`= '".my_htmlspecialchars($empl_detail['data']['attributes']['rating_position'])."'"
				.", `arbitrages`= '".my_htmlspecialchars($empl_detail['data']['attributes']['arbitrages'])."'"
				.", `positive_reviews`= '".my_htmlspecialchars($empl_detail['data']['attributes']['positive_reviews'])."'"
				.", `negative_reviews`= '".my_htmlspecialchars($empl_detail['data']['attributes']['negative_reviews'])."'"
				.", `creation_date`= '".my_htmlspecialchars($creation_date)."'"
				.", `location`= '".my_htmlspecialchars($location)."'"
				."WHERE `id` = ".my_htmlspecialchars($empl['id']);
		}
		$res = $mysqli -> query($query);
		if (!$res) {
			$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$query; 
		}	
	}
	function notificate_all($proj){
		global $mysqli, $endrayy, $info_msg, $notificated_list, $chat_id, $premium_list;
		if (array_key_exists('budget', $proj['attributes']) && $proj['attributes']['budget']) {
			if ($proj['attributes']['budget']['currency'] == "UAH") {
				$curr = "₴";
			}
			else{
				$curr = "₽";
			}
			$bga = ", 💵 ".$proj['attributes']['budget']['amount']." ".$curr;
		}
		else{
			$bga = '';
		}
		$wkb = [
					[
						[
							'text' => '🔽 Детально',
							'callback_data' => "mr|".$proj['id']
						],
						[
							'text' => '👨‍💼 Замовник',
							'callback_data' => "ed|".$proj['attributes']['employer']['id']
						]
					],
					[
						[
							'text' => '📚 Меню',
							'callback_data' => "headscreen_new"
						],
						[
							'text' => '📂 Відкрити',
							'url' => $proj['links']['self']['web']
						]	
					]
				];
		$inline_keyboard = $wkb;
		$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
		$skills = "";
		foreach ($proj['attributes']['skills'] as $skill) {
			$skills .= $skills == "" ? $skill['name'] : ", ".$skill['name'];
		}
		$msg = mb_strtoupper("❗️<b>Новий проект".$bga."❗️")."</b>\n\n☝️ ".$proj['attributes']['name']."\n\n<i>".$skills."</i>";

		$cleanrray = [];
		foreach ($endrayy['array_notificate_kt'] as $sbscrbr) {
			if (!in_array($sbscrbr, $endrayy['array_notificate_ct'])) {
				$cleanrray[count($cleanrray)."|kt"] = $sbscrbr;
			}
		}
		foreach ($endrayy['array_notificate_ct'] as $sbscrbr) {
			$cleanrray[count($cleanrray)."|ct"] = $sbscrbr;
		}
		foreach ($endrayy['array_notificate_dl'] as $sbscrbr) {
			if (in_array($sbscrbr, $cleanrray)) {
				unset($cleanrray[array_search($sbscrbr, $cleanrray)]);
			}
		}
		/*
		ob_start();
		print_r($cleanrray);
		run_query('sendMessage', ['chat_id' => $chat_id, 'text' => "INFO MESSAGE:\n".ob_get_clean()]);
		*/
		$empl_link = "https://freelancehunt.com/employer/".$proj['attributes']['employer']['login'].".html";
		$free_message = "\n\n<b>🔺 Проект: </b><a href=\"".$proj['links']['self']['web']."\">".$proj['attributes']['name']."</a> ".$bga."\n👨‍💼 <a href=\"".$empl_link."\">".$proj['attributes']['employer']['first_name']." ".$proj['attributes']['employer']['last_name']."</a>";

		foreach ($cleanrray as $usr) {
			if ($premium_list[$usr]) {
				if (count(explode("@", $usr)) == 2) {
					$inline_keyboard = [
						[
							[
								'text' => 'Відкрити',
								'url' => $proj['links']['self']['web']."?r=0JBWo"
							]
						]
					];
					$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				}
				run_query('sendMessage', ['chat_id' => $usr, 'text' => $msg, 'reply_markup' => $inline_keyboard, 'parse_mode'=> 'HTML']);
				$notificated_list[$usr] = 1;
				if (count(explode("@", $usr)) == 2) {
					$inline_keyboard = $wkb;
					$inline_keyboard = json_encode(['inline_keyboard' =>  $inline_keyboard, 'resize_keyboard' => true]);
				}
			}
			else{
				$mysqli->set_charset('utf8mb4');
				$query = "SELECT * FROM `fhb_free` WHERE `chat_id` = ".$usr." ORDER BY `id` DESC";
				$isit = $mysqli->query($query);
				if ($isit) {
					if ((mysqli_num_rows($isit) == 0) || (strlen($already_message['message']) > 2300)) {
						$already_message = $isit->fetch_assoc();
						$q_ins = "INSERT INTO `fhb_free` (`chat_id`, `message`, `size`) VALUES (".$usr.", '".my_htmlspecialchars($free_message."\n———————————————")."', ".strlen($already_message['message']).")";
					}
					else{
						$already_message = $isit->fetch_assoc();
						$mes_append = $already_message['message'];
						$q_ins = "UPDATE `fhb_free` SET `message` = '".my_htmlspecialchars($mes_append.$free_message."\n———————————————")."', `size` = ".strlen($already_message['message'])." WHERE `id` = ".($already_message['id']);
					}
					$free_q = $mysqli->query($q_ins);
					if (!$free_q) {
						$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\nQuery:".$q_ins;
					}
				}
				else{
					$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$query;
				}
				$mysqli->set_charset('utf8');
			}
			//delete later
			if($usr == 418289311){
				$mysqli->set_charset('utf8mb4');
				$query = "SELECT * FROM `fhb_free` WHERE `chat_id` = ".$usr." ORDER BY `id` DESC";
				$isit = $mysqli->query($query);
				if ($isit) {
					if ((mysqli_num_rows($isit) == 0) || (strlen($already_message['message']) > 2300)) {
						$q_ins = "INSERT INTO `fhb_free` (`chat_id`, `message`, `size`) VALUES (".$usr.", '".my_htmlspecialchars($free_message."\n———————————————")."', ".strlen($already_message['message']).")";
					}
					else{
						$already_message = $isit->fetch_assoc();
						$mes_append = $already_message['message'];
						$q_ins = "UPDATE `fhb_free` SET `message` = '".my_htmlspecialchars($mes_append.$free_message."\n———————————————")."', `size` = ".strlen($already_message['message'])." WHERE `id` = ".($already_message['id']);
					}
					$free_q = $mysqli->query($q_ins);
					if (!$free_q) {
						$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$free_q;
					}
				}
				else{
					$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$query;
				}
				$mysqli->set_charset('utf8');
			}

		}

	}
	function notificate_users($proj){
		global $mysqli, $info_msg, $array_all_keywords, $endrayy, $array_all_stopwords, $users_list;
		$info_msg['info']['new_projects'] += 1;
		$endrayy['array_notificate_ct'] = [];
		$endrayy['array_notificate_kt'] = [];
		$endrayy['array_notificate_dl'] = [];
		foreach ($proj['attributes']['skills'] as $skill) {
			$res = $mysqli -> query("SELECT * FROM `fhb_categories` WHERE `id` LIKE '".$skill['id']."'");
			if (mysqli_num_rows($res) != 0) {
				$res = $res->fetch_assoc();
				foreach (explode("|", $res['subscribers']) as $sbscrbr) {
					if (!in_array($sbscrbr, $endrayy['array_notificate_ct']) && in_array($sbscrbr, $users_list)) {
						$endrayy['array_notificate_ct'][] = $sbscrbr;
					}
				}
			}
			else{
				$info_msg['error'] .= "\n mySQL ERROR: Category not found:".$skill['id']."--".$skill['name'];
			}
		}
		foreach ($array_all_keywords as $prt => $value) {
			if ((strripos($proj['attributes']['description'], $prt) !== false) || (strripos($proj['attributes']['name'], $prt) !== false)) {
				foreach ($value as $sbscrbr) {
					if (!in_array($sbscrbr, $endrayy['array_notificate_kt'])) {
						$endrayy['array_notificate_kt'][] = $sbscrbr;
					}
				}
			}
		}
		foreach ($array_all_stopwords as $prt => $value) {
			if ((strripos($proj['attributes']['description'], $prt) !== false) || (strripos($proj['attributes']['name'], $prt) !== false)) {
				foreach ($value as $sbscrbr) {
					if (!in_array($sbscrbr, $endrayy['array_notificate_dl'])) {
						$endrayy['array_notificate_dl'][] = $sbscrbr;
					}
				}
			}
		}
		notificate_all($proj);
	}
	function check_project($proj){
		global $mysqli, $info_msg;
		check_employer($proj['attributes']['employer']);
		$res = $mysqli -> query("SELECT * FROM `fhb_projects` WHERE `id` LIKE '".$proj['id']."'");
		if (mysqli_num_rows($res) == 0){
			$skills = "";
			foreach ($proj['attributes']['skills'] as $skill) {
				$skills .= $skills == "" ? $skill['id'] : "|".$skill['id'];
			}
			if (array_key_exists('budget', $proj['attributes']) && $proj['attributes']['budget']) {
				$bga = $proj['attributes']['budget']['amount'];
				$bgc = $proj['attributes']['budget']['currency'];
			}
			else{
				$bga = 0;
				$bgc = '';
			}
			$query = "INSERT INTO `fhb_projects` ("
				."`id`,"
				." `name`,"
				." `description_html`,"
				." `skills`,"
				." `status`,"
				." `status_name`,"
				." `budget`,"
				." `budget_curr`,"
				." `employer_id`,"
				." `link`,"
				." `download_date`"
				.") VALUES ("
				.my_htmlspecialchars($proj['id'])
				.", '".my_htmlspecialchars($proj['attributes']['name'])
				."', '".my_htmlspecialchars($proj['attributes']['description_html'])
				."', '".my_htmlspecialchars($skills)
				."', '".my_htmlspecialchars($proj['attributes']['status']['id'])
				."', '".my_htmlspecialchars($proj['attributes']['status']['name'])
				."', ".my_htmlspecialchars($bga)
				.", '".my_htmlspecialchars($bgc)
				."', ".my_htmlspecialchars($proj['attributes']['employer']['id'])
				.", '".my_htmlspecialchars($proj['links']['self']['web'])
				."', ".time().")";
			$res = $mysqli -> query($query);
			if (!$res) {
				$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$query;
			}
			else{
				notificate_users($proj);
				/*$q2 = "INSERT INTO `fhb_new_projects` (`project_id`) VALUES (".$proj['id'].")";
				$r2 = $mysqli -> query($q2);
				if (!$r2) {
					$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$q2;
				}*/
			}
		} 
		else{
			$project = $res->fetch_assoc();
		}
	}
	function get_last_updates(){
		global $info_msg, $arr_pages;
		$start_time = microtime(true);
		for ($i=1; $i <= 2; $i++) { 
			$tme = microtime(true);
			$answ = get_new_progjects($i);
			$info_msg['info']['time_details']['query_time'] += microtime(true) - $tme;
			$arr_pages[$i] = $answ;
			$resp = json_decode($answ[1], true);
			echo $answ[0];
			echo "\n".$answ[1];
			var_dump($resp);
			if (json_last_error() === JSON_ERROR_NONE) {
				if (!array_key_exists('error', $resp)) {
					foreach ($resp['data'] as $proj) {
						if (!$proj['attributes']['is_only_for_plus']) {
							check_project($proj);
						}
					}	
				}
				else{
					$info_msg['error'] .= "\nError ".$resp['error']['code'].": ".$resp['error']['message'];
				}
			}
			else{
				$info_msg['error'] .= "\nError: no JSON";
			}
		}
		return microtime(true) - $start_time;
	}
	$info_msg['info']['time_details']['query_time'] = get_last_updates();

	notificate_about_subscription();

	$timeend = microtime(true) - $timestart;
	$floattime = $timeend - floor($timeend);
	$inttime = $timeend - $floattime;
	$info_msg['info']['time_details']['time_spent'] = intval($inttime/60)."m ".($inttime%60)."s ".intval($floattime*1000)."ms";
	$info_msg['info']['users_notificated'] = count($notificated_list);
	


	$res = $mysqli -> query("SELECT * FROM `fhb_activity` WHERE  `time` LIKE '".$texttimestart."'");
	if (mysqli_num_rows($res) == 0) {
		$query = "INSERT INTO `fhb_activity` ("
				."`time`,"
				." `date`,"
				." `projects`,"
				." `parse_time`,"
				." `query_time`"
				.") VALUES ("
				." '".my_htmlspecialchars($texttimestart)."'"
				.", '".my_htmlspecialchars($textdatestart)."'"
				.", ".$info_msg['info']['new_projects']
				.", ".round($timeend, 3)
				.", ".round($info_msg['info']['time_details']['query_time'], 3)
				.")";
			
			$res = $mysqli -> query($query);
			if (!$res) {
				$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$query;
			}
	}
	else{
			$query = "UPDATE `fhb_activity` SET "
				."`projects`= ".$info_msg['info']['new_projects']
				.", `parse_time`= ".round($timeend, 3)
				.", `query_time`= ".round($info_msg['info']['time_details']['query_time'], 3)
				.", `date`= '".$textdatestart."'"
				."WHERE `time` LIKE '".$texttimestart."'";
			
			$res = $mysqli -> query($query);
			if (!$res) {
				$info_msg['error'] .= "\n SQL ERROR:".basename(__FILE__).":".__LINE__." ".$mysqli->error."\n\n".$query;
			}
	}

	if ($info_msg['error'] != "") {
		run_query('sendMessage', ['chat_id' => $chat_id, 'text' => "ERROR MESSAGE:\n".$info_msg['error']]);
		$file_r = __DIR__.'/log/answer_refresh.txt';
		$current_r = "[".date("d/m/Y H:i:s")."]\n\n\n\n";
		ob_start();
		var_dump($arr_pages);
		var_dump($arr_eployers);
		$logf = ob_get_clean();
		$current_r .= $logf;
		file_put_contents($file_r, $current_r);
	}
	ob_start();
	print_r($info_msg['info']);
	$mess = ob_get_clean();
	run_query('sendMessage', ['chat_id' => $chat_id, 'text' => "INFO MESSAGE:\n".$mess]);
	
?>