<?php

/**
 * Управление базовыми проектами
 */
class PmBasicProject extends ArrayAccessebleOptions {

  /**
   * @var PmLocalServer
   */
  protected $server;

  function init() {
    $this->server = new PmLocalServer;
  }


  /**
   * Создаёт базовый проект
   *
   * @options name
   */
  function a_create() {
    $cwd = getcwd();
    if ($cwd != NGN_ENV_PATH and $cwd != dirname(NGN_ENV_PATH)) {
      throw new Exception("You can create basic projects only in home or ngn-env folders. Your current folder: $cwd");
    }
    $c = <<<C
<?php

require dirname(__DIR__).'/ngn/init/cli-standalone.php';
Lib::addFolder(__DIR__.'/lib');
C;

    $webFolder = $cwd.'/'.$this->options['name'].'/web';
    Dir::make($webFolder);
    file_put_contents($webFolder.'/index.php', $c);
    $this->server->updateHosts();
    PmWebserver::get()->restart();
    $this->done('Created new project: '.$this->server->systemDomain($this->options['name']));
  }

  protected function done($t = null) {
    print "--\n".($t ? $t : 'Done.')."\n";
  }

}