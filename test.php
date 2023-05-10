<?
	$mysqli = new mysqli("localhost", "shumik", "03102002Vitalik", "shumik_base");
	$apikey = '867897377:AAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw'; 
	//$chat_2_id = "-1001179542926";
	$chat_2_id = "418289311";
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
		echo $query;
		$answer = file_get_contents_curl($query);
		echo "resp: ".$answer;
		if ($answer !== FALSE) {
			$answer_d = json_decode($answer, true);
			if (!$answer_d['ok']) {
				$err_msg .= "\nERROR: ".$answer;
				return false;
			}
		}
		return true;
	}
	function get_new_progjects($page){
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
	print_r(get_new_progjects(1));
	echo "\n".run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => "I'm ok"]);
	//echo file_get_contents("https://api.telegram.org/bot867897377:AAGShOmppLb1cCwQkpqIPqwHiML3WwJyrfw/sendMessage?shit=you&chat_id=418289311&text=I%27m+ok");

	//echo "\n".run_query('sendAudio', ['chat_id' => $chat_2_id, 'audio' => "https://apihost.ru/php/app.php?&text=%D1%85%D0%B5%D1%80&format=mp3&lang=ru-RU&speed=1.0&emotion=neutral&speaker=ermilov&robot=1"]);
?>