<?php

header('Content-type: text/plain');

print_r($_COOKIE);

echo 'x';
//print_r(split('\|', $_COOKIE['LoginUser']));
  print_r( explode("|", $_COOKIE["LoginUser"]);
echo 'z';

