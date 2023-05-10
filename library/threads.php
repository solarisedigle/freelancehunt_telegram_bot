<?
	function get_threads($api_key, $page){
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
		    curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/threads/?page[number]=".$page);
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
		function get_threads_list($api_key, $thread_id){
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
		    curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/threads/".$thread_id);
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
	function cansel_prepare_to_message(){
			global $user, $mysqli;
			$query = "UPDATE `fhb_users` SET `step` = 'start_menu' WHERE `chat_id` = ".$user['chat_id'];
			$res = $mysqli -> query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			return $res;
		}
	function prepare_to_message($id){
			global $user, $mysqli;
			$query = "UPDATE `fhb_users` SET `step` = 'get|ms_add:".$id."' WHERE `chat_id` = ".$user['chat_id'];
			$res = $mysqli -> query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			return $res;
		}
	function threads($txt){
			global $mysqli, $user, $err_msg, $menubutt, $backbutt;
			$text = "";
			$keyboard = [];
			if ($user['api_key'] != "") {
				if (count(explode("|", $txt)) == 2) {
					cansel_prepare_to_message();
					$page = explode("|", $txt)[1];
					$threads = json_decode(get_threads($user['api_key'], $page)[1], true);
					if (array_key_exists("error", $threads)) {
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
						$keyboard = json_encode(['inline_keyboard' =>  $keyboard, 'resize_keyboard' => true]);
					}
					else{
						$keyboard = [];
						foreach ($threads['data'] as $thread) {
							if ($thread['attributes']['is_unread']) {
								$let_icon = "📩";
							}
							else{
								$let_icon = "📨";
							}
							if ($thread['attributes']['participants']['from']['login'] == $user['fh_login']) {
								$side = 'to';
							}
							else{
								$side = 'from';
							}
							if ($thread['attributes']['subject'] != "") {
								$subject = " ".$thread['attributes']['subject'];
							}
							else{
								$subject = " -";
							}
							$ss = " 🔸 ";
							$sender = $thread['attributes']['participants'][$side]['first_name']." ".$thread['attributes']['participants'][$side]['last_name'].$ss;
							$textt = $let_icon." ".$sender.$subject;
							$magicstr = "";
							for ($i = 0; $i < (200 - strlen($textt)); $i++) {
						      $magicstr .= "  ";
						    }
						    $magicstr .= ".";
							$keyboard[] = [
								[
									'text' => $textt.$magicstr,
									'callback_data' => 'threads:'.$thread['id'].":0"
								]
							];
						}
						if (array_key_exists("next", $threads['links'])) {
							$next = "▶️";
							$next_cb = "threads|".($page + 1);
						}
						else{
							$next = "🚫";
							$next_cb = ".";
						}
						if ($page != 1) {
							$prev = "◀️";
							$prev_cb = "threads|".($page - 1);
						}
						else{
							$prev = "🚫";
							$prev_cb = ".";
						}
						$keyboard[] = [
								[
									'text' => $prev,
									'callback_data' => $prev_cb
								],
								[
									'text' => $page." 📬🔄",
									'callback_data' => $txt
								],
								[
									'text' => $next,
									'callback_data' => $next_cb
								]
							];
						$keyboard[] = [
								[
									'text' => $backbutt,
									'callback_data' => 'headscreen'
								]
							];
						$text = "📨 <b>Повідомлення | ".$page."</b>";
						$text .= "\n - <i>Оновлено: ".date('H:i:s d/m/Y')."</i>";
						$keyboard = json_encode(['inline_keyboard' =>  $keyboard, 'resize_keyboard' => true]);
					}
				}
				else{
					$id = explode(":", $txt)[1];
					prepare_to_message($id);
					$threads = json_decode(get_threads_list($user['api_key'], $id)[1], true);
					$start = explode(":", $txt)[2];
					if (array_key_exists("error", $threads)) {
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
						$keyboard = json_encode(['inline_keyboard' =>  $keyboard, 'resize_keyboard' => true]);
					}
					else{
						$keyboard = [];
						if ($threads['meta']['thread']['attributes']['participants']['from']['login'] == $user['fh_login']) {
							$employer = $threads['meta']['thread']['attributes']['participants']['to']['first_name']." ".$threads['meta']['thread']['attributes']['participants']['to']['last_name'];
						}
						else{
							$employer = $threads['meta']['thread']['attributes']['participants']['from']['first_name']." ".$threads['meta']['thread']['attributes']['participants']['from']['last_name'];
						}
						$text = "📨 <b>Чат  | ".$employer."</b>";
						$text .= "\n - <i>Оновлено: ".date('H:i:s d/m/Y')."</i>";
						$max_index = count($threads['data']) - $start;
						$min_index = $max_index - 10;
						$i = 0;
						foreach ($threads['data'] as $key => $value) {
							if ($key >= $min_index && $key < $max_index) {
								$thread = $value;
								if ($thread['attributes']['participants']['from']['login'] == $user['fh_login']) {
									$side = "👨‍💻";
								}
								else{
									$side = "👨‍💼";
								}
								$posted = (new DateTime($thread['attributes']['posted_at']))->format('H:i d/m/Y');
								$text .= "\n\n ".$side." ".substr(html_entity_decode($thread['attributes']['message']), 0, 1000).(strlen(html_entity_decode($thread['attributes']['message'])) > 1000?"\n<b> (...) </b>":"")."\n<code>————————".$posted."</code>";
								$i++;
							}
						}
						$text .= "\n\n❗️<i>Щоб надісліти повідомлення, відправте його сюди</i>";
						if ($start >= 10) {
							$next = "🔻";
							$next_cb = "threads:".$id.":".($start - 10);
						}
						else{
							$next = "🚫";
							$next_cb = ".";
						}
						if ($i >= 10) {
							$prev = "🔺";
							$prev_cb = "threads:".$id.":".($start + 10);
						}
						else{
							$prev = "🚫";
							$prev_cb = ".";
						}
						$keyboard[] = [
								[
									'text' => $prev,
									'callback_data' => $prev_cb
								],
								[
									'text' => ($start/10 + 1)." 📑🔄",
									'callback_data' => $txt
								],
								[
									'text' => $next,
									'callback_data' => $next_cb
								]
							];
						$keyboard[] = [
								[
									'text' => $backbutt,
									'callback_data' => 'threads|1'
								]
							];
						
						$keyboard = json_encode(['inline_keyboard' =>  $keyboard, 'resize_keyboard' => true]);
					}
				}
			}
			else{
				$text = "<b>🗝 Щоб отримувати отримувати особисті повідомлення</b> з freelancehunt.com Вам потрібно додати API токен вашого аккаунту";
						$keyboard = [
								[
									[
										'text' => "🔑 Додати API токен",
										'callback_data' => 'api_key'
									],
									[
										'text' => $backbutt,
										'callback_data' => 'headscreen'
									]
								]
							];
				$keyboard = json_encode(['inline_keyboard' =>  $keyboard, 'resize_keyboard' => true]);
			}
			return [$text, $keyboard];
		}
?>