<?php
function getStats(){
	$host = $_GET['ip'];
	$port = $_GET['port'];
        $mem = @fsockopen($host, (int)$port);
        if($mem === FALSE) return -1;
 
        // retrieve distinct slab
        $r = @fwrite($mem, 'stats slabs' . chr(10));
        if($r === FALSE) return -2;
 
        $slab = array();
        while( ($l = @fgets($mem, 1024)) !== FALSE){
                // sortie ?
                $l = trim($l);
                if($l=='END') break;
		array_push($slab, $l);
        }

	// close
        @fclose($mem);
        unset($mem);

        return $slab;
}

$stats = getStats();

echo json_encode($stats);

?>

