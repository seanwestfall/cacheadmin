<?php

$output = shell_exec('crontab -l');
file_put_contents('/tmp/crontab.txt', $output.'* * * * * /var/www/test/singular/app/php/cron.php'.PHP_EOL);
echo exec('crontab /tmp/crontab.txt');
?>
