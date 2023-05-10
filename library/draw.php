<?php
	$mysqli = new mysqli("localhost", "shumik", "03102002Vitalik", "shumik_base");
	$res = $mysqli -> query("SELECT * FROM `fhb_activity` ORDER BY `date` DESC, `time` DESC");
	$finalrray = [];
	while (($row = $res->fetch_assoc())) {
		$finalrray[$row['time']] = 
			[
				$row['parse_time'],
				[
					"query_time" => round(($row['parse_time']==0)?0:($row['query_time']/$row['parse_time']), 3)
				]
			];
	}
	$array_test = array_reverse($finalrray);

	function my_twins_array_reverse($array){
		$arr_new = [];
		for ($i = count($array) - 1; $i >= 0; $i -= 2) { 
			array_push($arr_new, $array[$i - 1], $array[$i]);
		}
		return $arr_new;
	}
	function my_($param, $arr, $key){
		$arr2 = [];
		foreach ($arr as $value) {
			$arr2[] = $value[$key];
		}
		if ($param == "med"){
			sort($arr2);
			$middle = count($arr2)/2;
			if ($middle%2 == 0) {
				return ($arr2[$middle - 1] + $arr2[$middle])/2;
			}
			else{
				return $arr2[intval($middle) + 1];
			}
		}
		else{
			return $param($arr2);
		}	
	}

// ---------------------------
// variables & settings
// ---------------------------
	$maximal = my_('max', $array_test, '0');
	$minimal = my_('min', $array_test, '0');
	if (count($array_test) != 0) {
		$average = my_('array_sum', $array_test, '0')/count($array_test);
	}
	else{
		$average = 0;
	}
	$mediana = my_('med', $array_test, '0');

	$scale = 5;
	$info_width = 100;
	$width = 1440*$scale + $info_width;
	$height = 400;

	$padding = ($width>$height?$height:$width)*0.1;

	$nakladka = $padding/2;
	$arrow_width = $nakladka;
	$img = ImageCreateTrueColor($width, $height);
	$workzone_x = $width - $padding*2 - $arrow_width - 10 - $info_width;
	$workzone_y = $height - $padding*2 - $arrow_width - 10;

  
   	$x_step = $workzone_x/(count($array_test) - 1);
	$y_step = $workzone_y/($maximal);

	$background = imagecolorallocate( $img, 0, 0, 0 );
	$basis = imagecolorallocate($img, 255, 255, 255);
	$red = imagecolorallocate($img, 255, 0, 0);
	$x_average = imagecolorallocatealpha($img, 206, 73, 241, 90);
    $x_mediana = imagecolorallocatealpha($img, 139, 128, 248, 90);

    $bt_colors = [
    	[74, 160, 216],
    	[123, 192, 67],
    	[253, 244, 152],
    	[243, 119, 54],
    	[238, 64, 53],
    ];

// ---------------------------
// _graphic
// ---------------------------

   	$last_point = [$padding, $padding];
   	$i = 0;
   	$array_additional_lines = [];
   	foreach ($array_test as $key => $value) {
   		$x = $padding + $x_step*$i;
   		$y = $height - $padding - $y_step*$value[0] ;
   		$tme = explode(":", $key);
   		$point = $nakladka*((($tme[1]) == 0)?1:((($tme[1])%30 == 0)?0.5:((($tme[1])%15 == 0)?0.25:0)));
      if ($point == $nakladka) imagestring($img, 3, $padding + $x_step*$i - 17, $height - $padding + 10, $key, $basis);
      if ($point == $nakladka*0.5) imagestring($img, 2, $padding + $x_step*$i - 6, $height - $padding + 10, '30', $basis);
   		ImageLine( 
			$img, 
			$x,
			$y, 
			$padding + $x_step*$i,
			$height - $padding + $point, 
			$red 
		);
		ImageLine( 
			$img, 
			$last_point[0], 
			$last_point[1], 
			$x,
			$y, 
			$basis 
		);
		$last_point = [$x, $y];
		$ar_sum = 0;
		foreach ($value[1] as $key2 => $value2) {
			if (!array_key_exists($key2, $array_additional_lines)) {
				$array_additional_lines[$key2] = [];
				array_unshift($array_additional_lines[$key2], $padding, $height - $padding);
			}
			$x = $padding + $x_step*$i;
   			$y = $height - $padding - $y_step*$value[1][$key2]*$value[0] - $ar_sum;
   			$ar_sum += $y_step*$value[1][$key2]*$value[0];
			array_unshift($array_additional_lines[$key2], $x, $y);
		}
		$i++;
   	}
// ---------------------------
// other_graphic
// ---------------------------
   	$j = 0;
   	$last_arr = null;
   	foreach ($array_additional_lines as $key => $value) {
   		if (!$last_arr) {
   			$last_arr = $value;
   			$lx = ((count($value) - 4)/2)*$x_step + $padding;
	   		$ly = $height - $padding;
	   		array_push($value, $lx, $ly);
   		}
   		else{
   			$tmp = $last_arr;
   			$last_arr = $value;
   			array_push($value, ...my_twins_array_reverse($tmp));
   		}
   		$x_orange = imagecolorallocatealpha($img, ...$bt_colors[$j], ...[60]);
    	imagefilledpolygon($img, $value, count($value)/2, $x_orange);
    	$arr_info_right[] = [
	   		'color' => $x_orange,
	   		'value' => '',
	   		'description' => $key
	   	];
	   	
    	$j++;
   	}
   	

// ---------------------------
// basis
// ---------------------------
   	ImageLine( $img, $padding, $padding + $nakladka, $padding, ($height - $padding + $nakladka), $basis );
	ImageLine( $img, ($padding - $nakladka), ($height - $padding), ($workzone_x + $padding*2), ($height - $padding), $basis );
	$values = [
            $padding, $padding,
            ($padding - 3), ($padding + $arrow_width),
            ($padding + 3), ($padding + $arrow_width),
        ];
    imagefilledpolygon($img, $values, 3, $red);
	$values = [
            ($padding*2 + $workzone_x), ($height - $padding),
            (($padding*2 + $workzone_x) - $arrow_width), (($height - $padding) - 3),
            (($padding*2 + $workzone_x) - $arrow_width), (($height - $padding) + 3)
        ];
    imagefilledpolygon($img, $values, 3, $red);

// ---------------------------
// interface text
// ---------------------------
	$info_width += $padding;
   	

    imagestring($img, 2, $padding - 45, ($height - $padding - 6 - $y_step*$minimal), number_format($minimal, 3, '.', ''), $red);
   	imagestring($img, 2, $padding - 45, ($height - $padding - 6 - $y_step*$maximal), number_format($maximal, 3, '.', ''), $red);
   	$nul = $width - $info_width;
   	$arr_info_right[] = [
   		'color' => $x_average,
   		'value' => number_format($average, 3, '.', ''),
   		'description' => 'average'
   	];
   	$arr_info_right[] = [
   		'color' => $x_mediana,
   		'value' => number_format($mediana, 3, '.', ''),
   		'description' => 'mediana'
   	];
   	
   	$point = [5, 2];
   	foreach ($arr_info_right as $value) {
   		imagefilledrectangle($img,$nul + 5, $point[0], $nul + 15, $point[0] + 6, $value['color']);	
   		imagestring($img, 2,$nul + 20, $point[1], $value['value']." - ".$value['description'], $red);
   		$point[0] += 13;
   		$point[1] += 13;
   	}

// ---------------------------
// help lines
// ---------------------------

   	
    imagefilledrectangle($img, $padding, ($height - $padding - 2 - $y_step*$mediana), $padding*2 + $workzone_x - $arrow_width - $x_step, ($height - $padding - 2 - $y_step*$mediana + 5), $x_mediana);
    imagefilledrectangle($img, $padding, ($height - $padding - 2 - $y_step*$average), $padding*2 + $workzone_x - $arrow_width - $x_step, ($height - $padding - 2 - $y_step*$average + 5), $x_average);
    imagedashedline($img, $padding, $height - $padding - $y_step*$minimal, $padding + $workzone_x, $height - $padding - $y_step*$minimal, $basis);
    imagedashedline($img, $padding, $height - $padding - $y_step*$maximal, $padding + $workzone_x, $height - $padding - $y_step*$maximal, $basis);


	header('Content-type: image/png' );
	ImagePng( $img );
?>