<?
	$shipping_query_id = $telegram['pre_checkout_query']['id'];
	run_query('answerPreCheckoutQuery', ['pre_checkout_query_id' => $shipping_query_id, 'ok' => true]);
?>