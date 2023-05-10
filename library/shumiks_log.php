<?
	function console_log($line){
		global $mysqli;
		$mysqli->query("INSERT INTO `fhb_log` (`line`, `time`) VALUES ('".$line."','".microtime(true)."')");
	}
?>