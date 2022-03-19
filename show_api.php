<?php
//Author: Triptolemusew

date_default_timezone_set('Asia/Singapore');
if (defined('STDIN')){
	$state = $argv[1];
} else {
	$state_state = $_GET['state'];
	$area_state = $_GET['area'];
	if ($state_state != ""){
		$state = "state=".$state_state;
	} else if ($area_state != ""){
		$state = "area=".$area_state;
	}
}
$state_arr = explode("=", $state);
$state_arr[0] = strtolower($state_arr[0]);
$state_arr[1] = strtolower($state_arr[1]);
//Check the string input after the API for a valid instruction
if ($state_arr[0] != "state" and $state_arr[0] != "area"){
	echo "You need to input state or area for your instruction";
	exit;
} else if ($state_arr[1] == ""){
	echo "You need to put the the state or area location after the equal sign";
	exit;
}

$cur_url = getCurrentURL();
$url = "http://apims.doe.gov.my/v2/".$cur_url; 
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$page = curl_exec($curl);
if(curl_errno($curl)){
	echo 'Error while searching; '.curl_error($curl);
	exit;
}

curl_close($curl);
$regex = '/<tr>(.*?)<\/tr>/s'; // Getting list of tables
$regex2 = '/<td(.*?)<\/td>/s'; // Getting list of each table row
$regex3 = '/<b>(.*?)<\/b>/s'; // Getting list of API for each table row

if (preg_match_all($regex, $page, $list) ){
    foreach($list[1] as $table){
		preg_match_all($regex2, $table, $list2);
		$list2[1][0] = strtolower($list2[1][0]);
		$list2[1][1] = strtolower($list2[1][1]);
		$list2[1][0] = str_replace(">","", $list2[1][0]);
		$list2[1][1] = str_replace(">", "", $list2[1][1]);
		switch($state_arr[0]){
			case "state": {
				if($list2[1][0] == $state_arr[1]){
					$res_arr = array();
					foreach($list2[1] as $table2){
						preg_match_all($regex3, $table2, $result);
						$fin_arr = array_filter($result);
						if ($fin_arr != NULL){
							array_push($res_arr, $fin_arr[1][0]);
						}
					}
					echo $list2[1][1]." with API value of "."\n";
					printAPIValue($res_arr);
				}
				break;
			}
			case "area": {
				if($list2[1][1] == $state_arr[1]){
					$res_arr = array();
					foreach($list2[1] as $table2){
						preg_match_all($regex3, $table2, $result);
						$fin_arr = array_filter($result);
						if($fin_arr != NULL){
							array_push($res_arr, $fin_arr[1][0]);
						}
					}
					echo $state_arr[1]." in ".$list2[1][0]." with API value of "."\n";
					printAPIValue($res_arr);
				}
				break;
			}
			default: {
				echo "Nothing!";
				break;
			}
		}
    }
}
else{
    echo "Nothing in the table!\n";
    echo "The site is not up-to-date with the current time, Try Again next time!\n";
}

//this will return a new redirected url based on the time and date
function getCurrentURL(){
	$date = date("Y-m-d");
	$hour = date("H");
	$append_url = "";
	if ($hour < 7 )
		$append_url = "hour1_".$date.".html";
	else if($hour < 13)
		$append_url = "hour2_".$date.".html";
	else if($hour < 19)
		$append_url = "hour3_".$date.".html";
	else
		$append_url = "hour4_".$date.".html";
	return $append_url;
}

//Void function to print the value of the API based on the hour
function printAPIValue($api){
	$time = array("hour1","hour2","hour3","hour4","hour5","hour6");
	$fin_time = array();
	$hour = date("H");
	if($hour < 7){
		$i = 12.00;
		foreach($time as $time){
			if($i > 12.00)
				$i = 01.00;
			$time = (string)$i.".00AM";
			$i++;
			array_push($fin_time, $time);
		}
	} else if($hour < 13){
		$i = 6.00;
		foreach($time as $time){
			$time = (string)$i.".00AM";
			$i++;
			array_push($fin_time, $time);
		}
	} else if($hour < 19){
		$i = 12.00;
		foreach($time as $time){
			if($i > 12.00)
				$i = 1.00;
			$time = (string)$i.".00PM";
			$i++;
			array_push($fin_time, $time);
		}
	} else {
		$i = 6.00;
		foreach($time as $time){
			$time = (string)$i.".00PM";
			$i++;
			array_push($fin_time, $time);
		}
	}
	// Echo-ing will be done below
	for($i = 0; $i < sizeof($fin_time); $i++){
		if($api[$i] != 0){
			echo $api[$i]." at ".$fin_time[$i]."\n";
		}
	}	
}
?>