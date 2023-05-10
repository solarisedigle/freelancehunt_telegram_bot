<?
	$apikey = '1063763112:AAGBAy4_1yS6_SXeZusEOA-0brY_aHOa65g'; 
	$chat_id = "@mytestcn";

	function file_get_contents_curl($url, $typereq = null) {
		global $root;
		require($root."user_agent.php");
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: beget=begetok; PHPSESSID=6d4cf699589f008ecdc254d09cc93898; _ga=GA1.2.1898184691.1572894570; _gid=GA1.2.1433091411.1572894570; _ym_uid=1572894570891019212; _ym_d=1572894570; _ym_visorc_31656146=w; _ym_isad=2"));
	    curl_setopt($ch, CURLOPT_COOKIE, 'ig_did=52AC1DCD-E2E0-4663-8557-21E498C1F04D; csrftoken=2FCWM0u7DxpK1djt1nSaw62349MIAR1e; rur=ATN; mid=Xea0xgAEAAEMydYgyqtbNlM32csM; urlgen="{\"178.212.111.39\": 24893}:1icDg3:mhwpO93StsL7JUe6-c-mJo0aMcc"');
	    if ($typereq) {
	    	curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest"));
	    	curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "hid=yes");
	    }
	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
	    curl_setopt($ch, CURLOPT_REFERER, 'https://www.instadp.com/');
	    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent[rand(0, count($user_agent) - 1)]);
	    $data = curl_exec($ch);
	    curl_close($ch);

	    return $data;
	}

	function run_query($method_name, $params){
		global $apikey, $chat_id;
		$str_params = "?shit=you";
		foreach ($params as $key => $value) {
			if ($key == 'photo') {
				if (count(explode("http", $value)) == 1){
					$value = 'https://'.$_SERVER['SERVER_NAME'].explode($_SERVER['SERVER_NAME'],dirname(__FILE__))[1].'/'.$value;
				}
			}
			$str_params .= "&".$key."=".urlencode($value);
			

		}
		return file_get_contents_curl('https://api.telegram.org/bot'.$apikey.'/'.$method_name.$str_params);
	}


	$body = json_decode(file_get_contents('php://input'), true); //WebHook updates

	if (array_key_exists('callback_query', $body)) { //if callback
		$callback_id = $body['callback_query']['id'];
		$callback_data = $body['callback_query']['data'];
		$callback_chat_id = $body['callback_query']['message']['chat']['id'];
		$callback_message_id = $body['callback_query']['message']['message_id'];
		$callback_replymarkup = $body['callback_query']['message']['reply_markup'];
		
		run_query('answerCallbackQuery', ['callback_query_id' => $callback_id]);

		$answer = run_query('sendMessage', ['chat_id' => $chat_id, 'text' => 'shit', 'reply_markup' => json_encode($callback_replymarkup)]);


	}
	

	
?>