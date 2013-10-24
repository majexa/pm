<tr data-domain="<?= $d['domain'] ?>" id="<?= 'item_'.$d['id'] ?>">
  <td class="tools">
    <a href="" class="iconBtn delete" title="Удалить проект"><i></i></a>
    <? if (empty($d['test'])) { ?>
      <a href="" class="iconBtn edit" title="Редактировать"><i></i></a>
      <a href="" class="iconBtn backup" title="Бэкап"><i></i></a>
      <? if (empty($d['testCopyInProgress'])) { ?>
        <a href="" class="iconBtn test" title="Копировать проект в test.<?= $d['domain'] ?>"><i></i></a>
      <? } ?>
    <? } ?>
    <a href="" class="iconBtn version move" title="Копировать проект в следующую версию"><i></i></a>
  </td>
  <td class="loader<?= empty($d['test']) ? '' : ' test gray' ?>">
    <a href="http://<?= $d['domain'] ?>" target="_blank"><?= $d['domain'] ?></a></td>
  <td><a href="http://<?= $d['domain'] ?>/admin" target="_blank">A</a></td>
  <td><small><?= datetimeStr($d['timeCreate']) ?></small></td>
  <td><small><?= empty($d['aliases']) ? '' :
    $this->enumSsss2($d['aliases'], '<a href="http://$d" target="_blank">$d</a>') ?></small></td>
  <td><?= $this->enumSsss($d['admins'], '<a href="mailto:$email">$login</a>') ?></td>
  <td><?= $this->enumSsss($d['gods'], '<a href="mailto:$email">$login</a>') ?></td>
  <td><?= File::format($d['size']['files']).' / '.File::format($d['size']['db']).
    ' / <u>'.File::format($d['size']['total']).'</u>' ?></td>
  <td><? //prr($d['backups']) ?></td>
</tr>
