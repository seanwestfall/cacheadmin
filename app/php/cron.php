<?php
$string = file_get_contents("../../data/instances.json");
$json_a = json_decode($string, true);
$json_b = json_decode($json_a['instances'], true);
var_dump($json_b);

foreach($json_b as $key => $val) {
	
}
?>
