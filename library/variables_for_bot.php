<?
	$mod = "";
	$request = "";
	$subscribes_key_text = "";
	$telegram = json_decode(file_get_contents('php://input'), true); 
	$user = [];
	$max_text_width = 50;
	$min_text_width = 5;
	
	
	$keyboard_h = json_encode(['inline_keyboard' =>  [[back_btn('headscreen', '$head_menubutt')]], 'resize_keyboard' => true]);
	$max_params = [
		'ct' => 25,
		'kt' => 14,
		'skt' => 14
	];
	$prices = [
		'week' => 1999,
		'mounth' => 4799,
	];
?>