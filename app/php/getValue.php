<?php
$m = new Memcached();
$m->addServer($_GET['ip'],$_GET['port']);
echo var_dump($m->get($_GET['key']));

