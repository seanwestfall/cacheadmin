<?php
$m = new Memcached();
$m->addServer('localhost', 11211);
$m->delete($_GET['key']);
