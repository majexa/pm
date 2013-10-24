<?php

$options = R::get('options');
SiteConfig::updateVar('gods', [DbModelCore::create('users', [
  'login' => 'admin',
  'pass' => empty($options['adminPass']) ? '1234' : $options['adminPass'],
  'email' => 'dummy@test.com',
  'active' => 1
])]);
print "User created";