<?php $show_title = "$MSG_RANKLIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="padding-top: 2em; padding-bottom: 2em; max-width: 1200px;">

<!-- 랭킹 범위 선택 버튼 -->
<div class="ui stackable grid">
  <div class="eight wide column">
    <div class="ui mini buttons">
      <a href="ranklist.php?scope=d" class="ui button" style="background-color: #FFFFFF; color: #000000DE;"><?= $MSG_DAY ?></a>
      <a href="ranklist.php?scope=w" class="ui button" style="background-color: #FFFFFF; color: #000000DE;"><?= $MSG_WEEK ?></a>
      <a href="ranklist.php?scope=m" class="ui button" style="background-color: #FFFFFF; color: #000000DE;"><?= $MSG_MONTH ?></a>
      <a href="ranklist.php?scope=y" class="ui button" style="background-color: #FFFFFF; color: #000000DE;"><?= $MSG_YEAR ?></a>
    </div>
  </div>

    <!-- 사용자 검색 및 그룹 검색 -->
    <div class="eight wide column" style="background-color: transparent; padding: 1em 0;">
      <form class="ui mini form" method="get" action="ranklist.php">
        <div class="fields">
          <div class="field">
            <div class="ui action input">
              <input name="prefix" placeholder="<?= $MSG_USER ?>" type="text" value="<?= htmlentities($_GET['prefix'] ?? '', ENT_QUOTES, 'utf-8') ?>">
              <button class="ui mini blue button" type="submit"><?= $MSG_SEARCH ?></button>
            </div>
          </div>
          <div class="field">
            <div class="ui action input">
              <input name="group_name" placeholder="<?= $MSG_GROUP_NAME ?>" type="text" value="<?= htmlentities($_GET['group_name'] ?? '', ENT_QUOTES, 'utf-8') ?>">
              <button class="ui mini blue button" type="submit"><?= $MSG_SEARCH ?></button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- 랭킹 테이블 -->
  <div class="ui segment" style="margin-top: 1em;">
    <table class="ui celled striped compact center aligned table">
      <thead>
        <tr>
          <th style="width: 60px;"><?= $MSG_NUMBER ?></th>
          <th style="width: 160px;"><?= $MSG_USER ?></th>
          <th><?= $MSG_NICK ?></th>
          <th><?= $MSG_GROUP_NAME ?></th>
          <th style="width: 100px;"><?= $MSG_SOVLED ?></th>
          <th style="width: 100px;"><?= $MSG_SUBMIT ?></th>
          <th style="width: 100px;"><?= $MSG_RATIO ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($view_rank as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td><?= $cell ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- 페이지네이션 -->
  <div class="ui center aligned segment">
    <div class="ui pagination menu">
      <?php for ($i = 0; $i < $view_total; $i += $page_size):
        $start = $i + 1;
        $end = $i + $page_size;
        $link = "ranklist.php?start={$i}" . (isset($scope) ? "&scope=$scope" : "");
        ?>
        <a class="item" href="<?= $link ?>"><?= $start ?>-<?= $end ?></a>
      <?php endfor; ?>
    </div>
  </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
