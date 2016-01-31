<?php

$debug = TRUE;
set_time_limit(0);
ini_set('memory_limit', '2048M');

$i = 1;
$sleep_time = 5; // seconds
$file = 'timestamp.txt';

while(1){
  require_once ('forcesql.php');
  file_put_contents($file, "\nLast Run: " . date('l jS \of F Y h:i:s A'), FILE_APPEND);
  if($debug){
    exit();
  }
//sleep($sleep_time);
}
