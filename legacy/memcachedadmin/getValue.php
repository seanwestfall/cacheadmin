<?php
$m = new Memcached();
$m->addServer('localhost', 11211);
echo var_dump($m->get($_GET['key']));

