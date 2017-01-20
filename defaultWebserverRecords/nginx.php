<?php

// auth_basic            "Restricted";
// auth_basic_user_file  {ngnEnvPath}/pm/web/.htpasswd;
//
// $server - конфиг сервера

define('NGINX_PHP_RECORDS', '
      fastcgi_split_path_info ^(.+\.php)(.*)$;
      fastcgi_pass   '.(!empty($server['nginxFastcgiPassUnixSocket']) ? 'unix:/var/run/php/php5.6-fpm.sock' : '127.0.0.1:9000').';
      fastcgi_index  index.php;
      include fastcgi_params;
      fastcgi_param  QUERY_STRING     $query_string;
      fastcgi_param  REQUEST_METHOD   $request_method;
      fastcgi_param  CONTENT_TYPE     $content_type;
      fastcgi_param  CONTENT_LENGTH   $content_length;
      fastcgi_intercept_errors        on;
      fastcgi_ignore_client_abort     off;
      fastcgi_connect_timeout         60;
      fastcgi_send_timeout            180;
      fastcgi_read_timeout            180;
      fastcgi_buffer_size             128k;
      fastcgi_buffers                 2 256k;
      fastcgi_busy_buffers_size       256k;
      fastcgi_temp_file_write_size    256k;
');

$record = function ($d) {
  if (!isset($d['end'])) $d['end'] = '';
  return St::tttt('server {

  listen       {httpPort};
  server_name  {serverName}{aliases};
  access_log off;

  location @php {
    rewrite ^/(.*)$ /index.php?q=$1 last;
  }

  location / {
    root    {webroot};
    index   index.php;

    try_files $uri @php;

    {rootLocation}

    location ~ \.php$ {
#      access_log   {logsPath}/access.log vhosts;
      access_log   {logsPath}/access.log;
      fastcgi_param  SCRIPT_FILENAME  {webroot}$fastcgi_script_name;
'.NGINX_PHP_RECORDS.'
    }

    location ~* \.(jpg|jpeg|gif|css|png|js|ico|xml|html|htm|swf)$ {
      expires       30d;
      add_header    Cache-Control public;
    }

    location ~ /\.ht {
      deny all;
    }

  }

  {end}

}
', $d);
};

return [
  'webserverP'        => '/etc/init.d/nginx',
  'vhostTttt'         => '
server {
  listen       {httpPort};
  server_name  www.{domain};
  return       301 http://{domain}$request_uri;
}

server {

  listen       {httpPort};
  server_name  {domain}{aliases};
  access_log    off;

  location @php {
    rewrite ^/(.*)$ /index.php?q=$1 last;
  }
  
  location / {
    root    {webroot};
    index   index.php;
  
    try_files $uri @php;

    {rootLocation}
    
    location ~* \.(jpg|jpeg|gif|css|png|js|ico|xml|html|htm|swf)$ {
      expires       30d;
      add_header    Cache-Control public;
    }

    location ~ \.php$ {
#      access_log   {ngnEnvPath}/logs/access.log vhosts;
      access_log   {ngnEnvPath}/logs/access.log;
      fastcgi_param  SCRIPT_FILENAME  {webroot}$fastcgi_script_name;
      '.NGINX_PHP_RECORDS.'
    }
    
    location ~ /\.ht {
      deny all;
    }
    
  }

  location /i/ {
    access_log    off;
    expires       30d;
    add_header    Cache-Control public;
    root    {ngnPath};
  }

  {end}

}
',
  'abstractVhostTttt' => $record([
    'serverName' => '{domain}',
    'webroot'    => '{webroot}',
    'end'        => '{end}'
  ]),

];