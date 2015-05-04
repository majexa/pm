<?php

/**
 * Управление группами существующих проектов
 *
 * Создаёт соответственно экземпляры класса PmLocalProject (название класса PmLocalProjects без буквы s)
 * с элементов массива PmLocalProjects::records() в качестве конструктора
 */
class PmLocalProjects extends CliAccessOptionsMultiWrapper {

  protected function records() {
    return array_filter((new PmLocalProjectRecords)->getRecords(), function(array $record) {
      if (!(new PmLocalProjectConfig($record['name']))->isNgnProject()) return false;
      return true;
    });
  }

  /**
   * Инсталлирует/деинсталирует проектных демонов
   */
  function a_daemons() {
    // анинсталируем
    die2(ProjectDaemonInstaller::getInstalled());
  }

}
