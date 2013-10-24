<?php

require __DIR__.'/init.php';

if (count((new Req())->params) == 0) {
  header('Location: /c2/pm');
  return;
}

print Router::get()->dispatch()->getOutput();
