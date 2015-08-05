<?php
$m = new Memcached();
$m->addServer($_GET['ip'], $_GET['port']);
$statsdata = $m->getStats();

echo json_encode($statsdata);
?>

