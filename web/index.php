<?php

require __DIR__.'/init.php';
require dirname(dirname(__DIR__)).'/ngn/init/web-standalone.php';

print (new DefaultRouter(['disableSession' => true]))->dispatch()->getOutput();
