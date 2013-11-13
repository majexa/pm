<?php

return [
  'common'    => [
    'afterCmdTttt' => 'php {runPath}/site.php {name} {pmPath}/installers/common'
  ],
  'formatron' => [
    'vhostAliases' => [
      '/formatron/' => '/home/user/formatron/static/',
    ],
    'afterCmdTttt' => 'php {scriptsPath}/updateStartStopScripts.php'
  ],
  'sd'        => [
    'noDb'         => true,
    'vhostAliases' => [
      '/sd/' => '/home/user/sd/static/'
    ],
    'afterCmdTttt' => 'php {runPath}/run.php /home/user/sd/install {name}'
  ],
  'sb'        => [
    'vhostAliases' => [
      '/sb/'  => '{ngnEnvPath}/sb/static/',
      '/cpm/' => '{ngnEnvPath}/sb/lib/cpm/'
    ],
    'afterCmdTttt' => 'php {runPath}/run.php {ngnEnvPath}/sb/install {name}'
  ]
];