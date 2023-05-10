<?
		$user['text'] = $telegram['callback_query']['data'];
		$user['chat_id'] = $telegram['callback_query']['message']['chat']['id'];
		$user['user_id'] = $telegram['callback_query']['from']['id'];
		$user['message_id'] = $telegram['callback_query']['message']['message_id'];
		$user['callback_replymarkup'] = $telegram['callback_query']['message']['reply_markup'];
		$user['callback_text'] = $telegram['callback_query']['message']['text'];
		$user['user_name'] = $telegram['callback_query']['from']['username'];
		$user['first_name'] = $telegram['callback_query']['from']['first_name'];
		$user['last_name'] = $telegram['callback_query']['from']['last_name'];
		$callback_id = $telegram['callback_query']['id'];
		run_query('answerCallbackQuery', ['callback_query_id' => $callback_id]);
		check_user();
		function get_country_flag($id){
			global $mysqli;
			$res = $mysqli->query("SELECT * FROM `fhb_countries` WHERE `id` = ".$id);
			if (mysqli_num_rows($res) != 0) {
				$res = $res->fetch_assoc();
				return $res['html'];
			}
		}
		function employeer_details($id){
			global $mysqli, $apikey, $user, $err_msg;
			$employeer = $mysqli->query('SELECT * FROM `fhb_employers` WHERE `id` = '.$id);
			if ($employeer) {
				$edata = $employeer->fetch_assoc();
				$country = "";
				$city = "";
				$flag = "";
				if ($edata['location'] != "") {
					$country = explode(":", explode("|", $edata['location'])[0])[1];
					$flag = get_country_flag(explode(":", explode("|", $edata['location'])[0])[0]);
					if (count(explode("|", $edata['location'])) == 2) {
						$city = explode(":", explode("|", $edata['location'])[1])[1];	
					}
					if ($city != "") {
						$city = ", ".$city;
					}
				}
				$message = 
					"<b>".$edata['first_name']." ".$edata['last_name']."|</b>"."<i>".$edata['login']."</i>"
					."\n".$flag." <code>".$country.$city."</code>"
					."\n———\n<b>⚠️ Арбітражі: </b>".$edata['arbitrages']
					."\n———\n<b>🔖 Відгуки: </b>"
					."✅ ".$edata['positive_reviews'].", 🚫 ".$edata['negative_reviews']
					."\n———\n<b>📊 Позиція в рейтингу: </b> ".$edata['rating_position']
					."\n———";
				$keyboard = [
					[
						[
							'text' => '📂 Відкрити',
							'url' => "https://freelancehunt.com/employer/".$edata['login'].".html?r=0JBWo"
						]
					]
				];
				$keyboard = json_encode(['inline_keyboard' => $keyboard, 'resize_keyboard' => true]);
				run_query('sendPhoto', ['chat_id' => $user['chat_id'], 'photo'=> $edata['avatar_large'], 'parse_mode'=> 'HTML', 'caption' => $message, 'reply_markup' => $keyboard]);
			}
			else{
				$err_msg .= $mysqli->error;
			}
		}
		function get_categories(){
			global $user, $mysqli;
			$res = $mysqli -> query("SELECT * FROM `fhb_categories`");
			$arr = [];
			while ($row = $res->fetch_assoc()) {
				$arr[$row['parent_category']][] = [
					'name' => $row['name'],
					'id' => $row['id']
				];
			}
			return $arr;
		}
		function profile_info(){
			global $mysqli, $err_msg, $user, $max_params;
			$message = "";
			$res = $mysqli -> query("SELECT * FROM `fhb_users` WHERE `chat_id` = ".$user['chat_id']);
			if ($res && (mysqli_num_rows($res) != 0)) {
				$res = $res->fetch_assoc();
				$kt_num = $res['key_texts']!=""?count(explode("|", $res['key_texts'])):0;
				$skt_num = $res['stop_texts']!=""?count(explode("|", $res['stop_texts'])):0;
				$premium = $res['premium_time']>=time()?(secondsToTime($res['premium_time'] - time())):"Не активна";
				$message .= "👨‍🔧 <i>".$res['first_name']."</i>";
				$message .= "\n———\n<code>".my_str_pad("Категорії: ", 18, " ")."</code>".$res['subscription_count']."/".$max_params['ct'];
				$message .= "\n<code>".my_str_pad("Ключові фрази: ", 18, " ")."</code>".$kt_num."/".$max_params['kt'];
				$message .= "\n<code>".my_str_pad("Стоп-текст: ", 18, " ")."</code>".$skt_num."/".$max_params['skt'];
				$message .= "\n\n<b>"."Підписка: "."</b>".$premium;
			}
			else{
				$err_msg .= "mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			return $message;
		}
		function additional_info(){
			global $mysqli, $err_msg, $user, $max_params;
			$message = "";
			$message .= "<b>Контакти:</b>";
			$message .= "\n<i>тел: +380507346735</i>";
			$message .= "\n<i>e-mail: vitalik.shumanskyi@gmail.com</i>";
			$message .= "\n<i>telegram: @Solarisedigle</i>";
			$message .= "\n<a href=\"https://docs.google.com/document/d/1s38mCQTPGJvZ86n9CWJJvIMHnbXlsKr-Ol0YD6cAXIA/edit?usp=sharing\">Договір-оферта</a>";
			return $message;
		}
		function bot_description(){
			global $mysqli, $err_msg, $user, $max_params;
			$message = "";
			$message .= "<b>freelancehunt_shumiks_bot</b> - телеграм-бот, розроблений для інформування підписаних користувачів про нещодавно опубліковані проекти на сайті freelancehunt.com\n\nДля того щоб отримувати сповіщення потрібно мати активну підписку, яку можна купити/продовжити перейшовши на вкладку оплати (/start -> Профіль -> Купити/продовжити підписку), вибравши потрібний тариф (тиждень/місць) та оплативши її.\n\nТелекрам-канал: t.me/fhb_news\n\nІнстаграм: https://www.instagram.com/freelancehunt_bot";
			return $message;
		}
		function get_categories_wtht(){
			global $user, $mysqli;
			$res = $mysqli -> query("SELECT * FROM `fhb_categories`");
			$arr = [];
			while ($row = $res->fetch_assoc()) {
				$subscribers_list = $row['subscribers'];
				if (array_search($user['chat_id'], explode("|", $subscribers_list)) !== FALSE) {
					$arr[$row['parent_category']][] = [
						'name' => $row['name'],
						'id' => $row['id']
					];
				}
			}
			return $arr;
		}
		function get_keytext_wtht(){
			global $user, $mysqli, $err_msg;
			$arr = [];
			if ($user['subscribes_key_text'] != "") {
				foreach (explode("|", $user['subscribes_key_text']) as $key => $value) {
					$res = $mysqli -> query("SELECT * FROM `fhb_keytext` WHERE `id` = ".$value);
					if (!$res) {
						$err_msg .= "mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					}
					else{
						$res = $res->fetch_assoc();
						$arr[] = [
							'key_name' => $res['key_name'],
							'id' => $res['id']
						];
					}
				}
			}
			return $arr;
		}
		function get_stop_keytext_wtht(){
			global $user, $mysqli, $err_msg;
			$arr = [];
			if ($user['subscribes_stop_key_text'] != "") {
				foreach (explode("|", $user['subscribes_stop_key_text']) as $key => $value) {
					$query = "SELECT * FROM `fhb_stoptext` WHERE `id` = ".$value;
					$res = $mysqli -> query($query);
					if (!$res) {
						$err_msg .= $query;
						$err_msg .= "mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
					}
					else{
						$res = $res->fetch_assoc();
						$arr[] = [
							'stopkey_name' => $res['stopkey_name'],
							'id' => $res['id']
						];
					}
				}
			}
			return $arr;
		}
		function del_key_text($id){
			global $user, $err_msg, $mysqli;
			$res_u = $mysqli -> query("SELECT * FROM `fhb_users` WHERE `chat_id` = ".$user['chat_id']);
			if (mysqli_num_rows($res_u) != 0){
				$res_u = $res_u->fetch_assoc();
				$subscribes_list_u = $res_u['key_texts'];
				if(array_search($id, explode("|", $subscribes_list_u)) === FALSE){
						$err_msg .= "\nTEXTKEY not found";
						return 101;
				}
				else{
					$sl_u_exp = explode("|", $subscribes_list_u);
					if (($key = array_search($id, $sl_u_exp)) !== false) {
					    unset($sl_u_exp[$key]);
					    $subscribes_list_u = implode("|", $sl_u_exp);
					}
					$mysqli -> query("UPDATE `fhb_users` SET `key_texts` = '$subscribes_list_u' WHERE `chat_id` = ".$user['chat_id']);
					return true;
				}
			}
			else{
				return 404;
				$err_msg .= "\nERROR add: keytext not found";
			}
		}
		function del_stop_key_text($id){
			global $user, $err_msg, $mysqli;
			$res_u = $mysqli -> query("SELECT * FROM `fhb_users` WHERE `chat_id` = ".$user['chat_id']);
			if (mysqli_num_rows($res_u) != 0){
				$res_u = $res_u->fetch_assoc();
				$subscribes_list_u = $res_u['stop_texts'];
				if(array_search($id, explode("|", $subscribes_list_u)) === FALSE){
						$err_msg .= "\nSTOPTEXTKEY not found";
						return 101;
				}
				else{
					$sl_u_exp = explode("|", $subscribes_list_u);
					if (($key = array_search($id, $sl_u_exp)) !== false) {
					    unset($sl_u_exp[$key]);
					    $subscribes_list_u = implode("|", $sl_u_exp);
					}
					$mysqli -> query("UPDATE `fhb_users` SET `stop_texts` = '$subscribes_list_u' WHERE `chat_id` = ".$user['chat_id']);
					return true;
				}
			}
			else{
				return 404;
				$err_msg .= "\nERROR add: keytext not found";
			}
		}
		function prepare_to_keytext(){
			global $user, $mysqli;
			$query = "UPDATE `fhb_users` SET `step` = 'get|kt_add' WHERE `chat_id` = ".$user['chat_id'];
			$res = $mysqli -> query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			return $res;
		}
		function prepare_to_stop_keytext(){
			global $user, $mysqli;
			$query = "UPDATE `fhb_users` SET `step` = 'get|skt_add' WHERE `chat_id` = ".$user['chat_id'];
			$res = $mysqli -> query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			return $res;
		}
		function prepare_to_api_key(){
			global $user, $mysqli;
			$query = "UPDATE `fhb_users` SET `step` = 'get|ak_add' WHERE `chat_id` = ".$user['chat_id'];
			$res = $mysqli -> query($query);
			if (!$res) {
				$err_msg .= "\n".$query;
				$err_msg .= "\n mySqli ERROR: ".basename(__FILE__).":".__LINE__." ".$mysqli->error;
			}
			return $res;
		}
		function answer_ret_question($id){
			global $user, $mysqli, $err_msg, $chat_2_id;
			$kb = $user['callback_replymarkup'];
			$kb["inline_keyboard"][0][0] = $kb["inline_keyboard"][0][1];
			unset($kb["inline_keyboard"][0][1]);
			$kb = json_encode($kb);
			$res = $mysqli -> query("SELECT * FROM `fhb_projects` WHERE `id` LIKE '".$id."'");

			if (mysqli_num_rows($res) == 0){
				$err_msg .= "ERROR: Project not found:".$id;
			} 
			else{
				$res = $res->fetch_assoc();
				$content = html_formatter(html_entity_decode($res['description_html']));
				$content = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $content));
				$msg = $user['callback_text']."\n______________\n".$content;
				run_query('editMessageText', ['chat_id' => $user['chat_id'], 'parse_mode'=> 'HTML', 'message_id' => $user['message_id'], 'text' => $msg, 'reply_markup' => $kb]);
			}	
		}
		require_once(__DIR__."/threads.php");
?>