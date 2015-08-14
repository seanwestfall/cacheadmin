<?php
function getMemcacheKeys(){
	$host = $_GET['ip'];
	$port = (int) $_GET['port'];
        $mem = @fsockopen($host, $port);
        if($mem === FALSE) return -1;
 
	$r = @fwrite($mem, 'stats cachedump ' . $_GET['slabid'] . ' 20000' . chr(10));
	if($r === FALSE) return -4;

	$data = array();
	while( ($l = @fgets($mem, 1024)) !== FALSE){
		// sortie ?
		$l = trim($l);
		if($l=='END') break;

		array_push($data, $l);
	}

	// close
        @fclose($mem);
        unset($mem);

        return $data;
}

$data = getMemcacheKeys();

echo json_encode($data);

