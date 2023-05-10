<?
	function my_str_pad($str, $need, $str_add){
		$need = $need - mb_strlen($str);
		for ($i=0; $i < $need; $i++) { 
			$str .= $str_add;
		}
		return $str;
	}
	function html_formatter($txt){
		$txt = str_replace('<br />', "\n", $txt); 
		$txt = str_replace('&nbsp;', " ", $txt); 
		$txt = str_replace('</p>', "\n", $txt); 
		$txt = str_replace('<p>', "", $txt); 
		$txt = strip_tags($txt);
		return $txt;
	}
	function back_btn($last, $txt){
		$btn = [
			'text' => $txt,
			'callback_data' => $last
		];
		return $btn;
	}
	function file_get_contents_curl($url){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $data = curl_exec($ch);
	    return $data;
	}
	function secondsToTime($seconds) {
	    $dtF = new \DateTime('@0');
	    $dtT = new \DateTime("@$seconds");
	    return $dtF->diff($dtT)->format('%a дн. %h год. %i хв.');
	}
?>