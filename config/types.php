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
];