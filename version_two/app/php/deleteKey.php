<?php
$m = new Memcached();
$m->addServer($_GET['ip'],$_GET['port']);
$m->delete($_GET['key']);
