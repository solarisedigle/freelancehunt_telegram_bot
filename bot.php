<?
	require_once(__DIR__."/library/general_functions.php");
	require_once(__DIR__."/library/variables.php");
	require_once(__DIR__."/library/variables_for_bot.php");
	require_once(__DIR__."/library/bot_functions.php");
	if (array_key_exists('callback_query', $telegram)) { 
		require_once(__DIR__."/library/callback_query.php");
		require_once(__DIR__."/library/listener_callback.php");
	}
	else if(array_key_exists('pre_checkout_query', $telegram)){
		require_once(__DIR__."/library/pre_checkout_query.php");	
	}
	else if (array_key_exists('message', $telegram) && array_key_exists('successful_payment', $telegram['message'])){
		require_once(__DIR__."/library/successful_payment.php");	
	}
	else{
		require_once(__DIR__."/library/text_query.php");
		require_once(__DIR__."/library/listener_text.php");
	}
	



	if ($err_msg != "") {
		run_query('sendMessage', ['chat_id' => $chat_2_id, 'text' => $err_msg]);
	}
?>