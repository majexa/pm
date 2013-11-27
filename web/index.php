<?php

require __DIR__.'/init.php';

print (new DefaultRouter(['disableSession' => true]))->dispatch()->getOutput();
