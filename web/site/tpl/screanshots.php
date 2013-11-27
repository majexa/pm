<style>
  .items img {
    width: 200px;
    display: block;
    border: 1px solid #ccc;
  }
  .items .item {
    position: relative;
    float: left;
    width: 200px;
    height: 100%;
    overflow: hidden;
  }
  .items .title {
    position: absolute;
    padding: 1px 4px;
    top: 5px;
    left: 5px;
    background: #f00;
    color: #fff;
  }
</style>
<div class="items">
<? foreach ($d['items'] as $v) { ?>
  <div class="item">
    <div class="title"><?= $v ?></div>
    <img src="/m/captures/<?= $v ?>" />
  </div>
<? } ?>
</div>