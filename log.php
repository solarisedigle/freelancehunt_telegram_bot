<?
	$mysqli = new mysqli("localhost", "shumik", "03102002Vitalik", "shumik_base");
	$res = $mysqli->query("SELECT * FROM `fhb_log` ORDER BY `time` ASC");
	while ($row = $res->fetch_assoc()) {
		echo "\n[".$row['time']."] ".$row['line'];
	} 
?>