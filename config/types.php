<?php

return [
  'common'    => [
    'afterCmdTttt' => 'php {runPath}/run.php site {name} {pmPath}/installers/common'
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
    'afterCmdTttt' => 'php {runPath}/run.php site {name} /home/user/sd/install'
  ],
  'sb'        => [
    'vhostAliases' => [
      '/sb/'  => '{ngnEnvPath}/sb/static/',
      '/cpm/' => '{ngnEnvPath}/sb/lib/cpm/'
    ],
    'afterCmdTttt' => 'php {runPath}/run.php site {name} {ngnEnvPath}/sb/install'
  ],
  'sd-paralax'        => [
    'extends' => 'sd',
    'vhostAliases' => [
      '/paralax/'  => '{ngnEnvPath}/sd-paralax/static/',
    ]
  ]
];