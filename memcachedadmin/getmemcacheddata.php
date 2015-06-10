<?php
$m = new Memcached();
$m->addServer('localhost', 11211);
$statsdata = $m->getStats();

echo json_encode($statsdata);
?>

