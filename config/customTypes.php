<?php

return [
  'pageMaker' => [
    'extends' => 'common',
    'vhostAliases' => [
      '/pageMaker/' => '{ngnEnvPath}/pageMaker/static/',
    ]
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