<?php

return [
'vhostTttt' => '<VirtualHost *>
  ServerAdmin {adminEmail}
  ServerName {domain}
  ServerAlias {aliases}
  Alias /i "{ngnPath}/i"
  {end}
  DocumentRoot {webroot}
  ErrorLog  {logsPath}/{domain}.error.log
  CustomLog {logsPath}/{domain}.access.log combined
  AddDefaultCharset utf-8
</VirtualHost>
',
'pmVhostTttt' => '<VirtualHost *>
  ServerAdmin {adminEmail}
  ServerName {domain}
  Alias /i "{ngnPath}/i"
  DocumentRoot {ngnEnvPath}/pm/web
  ErrorLog  {logsPath}/pm.error.log
  CustomLog {logsPath}/pm.access.log combined
  AddDefaultCharset utf-8
</VirtualHost>
',
'myadminVhostTttt' => '<VirtualHost *>
  ServerAdmin {adminEmail}
  ServerName {domain}
  DocumentRoot {ngnEnvPath}/myadmin
  ErrorLog  {logsPath}/myadmin.error.log
  CustomLog {logsPath}/myadmin.access.log combined
  AddDefaultCharset utf-8
</VirtualHost>
',
];