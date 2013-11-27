<?

$day = isset($_GET['date']) ? $_GET['date'][0] : date('d');
$month = isset($_GET['date']) ? $_GET['date'][1] : date('n');
$year = isset($_GET['date']) ? $_GET['date'][2] : date('Y');
for ($i = 0; $i < 24; $i++) {
  $times[$i] = mktime($i, 0, 0, $month, $day, $year);
  $counts[$i] = 0;
}
$to = $times[$i] = mktime(23, 59, 59, $month, $day, $year);
foreach (Dir::getFilesR(dirname(NGN_ENV_PATH)) as $file) {
  if (strstr($file, '/data/')) continue;
  if (strstr($file, '/logs/')) continue;
  if (strstr($file, '/cache/')) continue;
  $filemtime = filemtime($file);
  for ($i = 23; $i >= 0; $i--) {
    if ($filemtime > $times[$i] and $filemtime < $to) {
      if ($counts[$i] < 50) $counts[$i]++;
      if (!isset($files[$i])) $files[$i] = [];
      $files[$i][] = basename($file);
      break;
    }
  }
}
$form = new Form([[
  'type' => 'date',
  'name' => 'date',
  'default' => [$day, $month, $year]
]], ['submitTitle' => 'Обновить']);
$form->methodPost = false;

?>
<h1><?= date('d.m.Y', $to) ?></h1>

<?= $form->html() ?>


<script>
  var data = {
    labels: [<?= implode(', ', array_keys($counts)) ?>],
    datasets: [
      {
        fillColor: 'rgba(151,187,205,0.5)',
        strokeColor: 'rgba(151,187,205,1)',
        pointColor: 'rgba(151,187,205,1)',
        pointStrokeColor: '#fff',
        data: [<?= implode(', ', $counts) ?>]
      }
    ]
  };
</script>
<canvas id="activity" width="1200" height="400"></canvas>
<script src="/m/js/Chart.js"></script>
<script src="/m/js/Ngn.activity.js"></script>
<style>
  .files {
    margin-left: 48px;
  }
  .files .item {
    width: <?= 1200/24-1 ?>px;
    overflow-x: hidden;
    border-left: 1px solid #CCC;
    font-size: 9px;
    font-family: Tahoma;
    box-sizing: border-box;
    float: left;
  }
  .files .item:hover {
    overflow-x: visible;
  }
  .files .cont {
    padding-left: 5px;
    background: #fff;
  }
</style>
<div class="files">
  <? for ($i = 0; $i < 24; $i++) { ?>
    <div class="item">
      <div class="cont"><?= isset($files[$i]) ? implode('<br>', $files[$i]) : '' ?>&nbsp;</div>
    </div>
  <? } ?>
</div>
