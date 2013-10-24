<?

function prepareRow($v) {
  $r['id'] = $v['id'];
  $r['data'] = [];
  $r['tools'] = [
    'delete' => 'Удалить проект',
    'edit' => 'Редактировать проект',
    'backup' => 'Бэкап',
  ];
  if (empty($v['test']) and empty($v['testCopyInProgress'])) {
    $r['tools']['test'] = "Копировать проект в test.{$v['domain']}";
  } else {
    $r['tools']['dummy'] = '';
  }
  $r['tools']['versions'] = "Копировать проект в следующую версию";
  $r['data']['domain'] = [
    '<a href="http://'.$v['domain'].'" target="_blank">'.$v['domain'].'</a>',
    empty($v['test']) ? '' : ' test gray'
  ];
  $r['data'] = array_merge($r['data'], [
    'adminLink' => '<a href="http://'.$v['domain'].'/admin" target="_blank">A</a>',
    'timeCreate' => '<small>'.datetimeStr($v['timeCreate']).'</small>',
    'aliases' => '<small>'.(empty($v['aliases']) ? '' : Tt()->enumSsss2($v['aliases'], '<a href="http://$d" target="_blank">$d</a>')).'</small>',
    'admins' => Tt()->enumSsss($v['admins'], '<a href="mailto:$email">$login</a>'),
    'gods' => Tt()->enumSsss($v['gods'], '<a href="mailto:$email">$login</a>'),
    'files' => '<small>'.File::format($v['size']['files']).' / '.File::format($v['size']['db']).' / <u>'.File::format($v['size']['total']).'</u></small>'
  ]);
  return $r;
}

$items = [];
foreach ($d['items'] as $k => $v) {
  $items[$v['domain']] = $v;
  if (isset($v['testProject'])) $items[$v['testProject']['domain']] = $v['testProject'];
}

?>

<style>
#itemsTable td.test {
padding-left: 20px;
}
</style>

<div id="table"></div>

<script type="text/javascript" src="m/js/Ngn.ItemsProjects.js"></script>
<script type="text/javascript">

Ngn.cutUsers = function(v) {
  if (!v) return '';
  return v.substr(0, 20);
}
var opt = {
  eParent: 'table',
  data: <?= Arr::jsObj([
  'head' => ['', 'Домен', '', 'Создан', 'Алиасы', 'Админы', 'Боги', 'Объём'],
  'body' => array_map(prepareRow, $items)
]) ?>,
  formatters: {
    admins: Ngn.cutUsers,
    gods: Ngn.cutUsers
  }
};
new Ngn.ItemsProjects(opt);

// -------------- top buttons ----------------
$('top').getElement('.add').addEvent('click', function() {
  new Ngn.Dialog.RequestForm({
    title: 'Создание проекта',
    width: 500,
    url: Ngn.getPath(2) + '/json_new',
    onSubmitSuccess: function() {
      window.location.reload();
    }
  });
  return false;
});
</script>
