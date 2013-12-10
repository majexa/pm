<?php

$lastCheckTime = 0;
$count = 0;
foreach (Activity::files() as $file) {
  $filemtime = filemtime($file);
  if ($lastmtime > $lastCheckTime) $count++;

}
