<?php $show_title="$MSG_RANKLIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<!-- 랭킹 범위 선택 및 검색 기능 -->
<div class="ui container" style="padding-top: 2em; padding-bottom: 2em; max-width: 1200px;">
  <div class="ui stackable grid">
    <div class="eight wide column">
      <div class="ui mini buttons">
        <a href="ranklist.php?scope=d" class="ui button <?php if ($_GET['scope']=='d') echo 'blue'; ?>"><?php echo $MSG_DAY ?></a>
        <a href="ranklist.php?scope=w" class="ui button <?php if ($_GET['scope']=='w') echo 'blue'; ?>"><?php echo $MSG_WEEK ?></a>
        <a href="ranklist.php?scope=m" class="ui button <?php if ($_GET['scope']=='m') echo 'blue'; ?>"><?php echo $MSG_MONTH ?></a>
        <a href="ranklist.php?scope=y" class="ui button <?php if ($_GET['scope']=='y') echo 'blue'; ?>"><?php echo $MSG_YEAR ?></a>
      </div>
    </div>
    <div class="eight wide column">
      <form class="ui mini form" action="ranklist.php" method="get">
        <div class="ui action input" style="margin-bottom: 0.5em;">
          <input name="prefix" placeholder="<?php echo $MSG_USER ?>" type="text" value="<?php echo htmlentities(isset($_GET['prefix']) ? $_GET['prefix'] : '', ENT_QUOTES, 'utf-8') ?>">
          <button class="ui mini blue button" type="submit"><?php echo $MSG_SEARCH ?></button>
        </div>
        <div class="ui action input">
          <input name="group_name" placeholder="<?php echo $MSG_GROUP_NAME ?>" type="text" value="<?php echo htmlentities(isset($_GET['group_name']) ? $_GET['group_name'] : '', ENT_QUOTES, 'utf-8') ?>">
          <button class="ui mini blue button" type="submit"><?php echo $MSG_SEARCH ?></button>
        </div>
      </form>
    </div>
  </div>

  <!-- 랭킹 테이블 -->
  <table class="ui celled striped table center aligned">
    <thead>
      <tr>
        <th style="width: 60px; "><?php echo $MSG_Number ?></th>
        <th style="width: 180px; "><?php echo $MSG_USER ?></th>
        <th><?php echo $MSG_NICK ?></th>
        <th><?php echo $MSG_GROUP_NAME ?></th>
        <th style="width: 100px; "><?php echo $MSG_SOVLED ?></th>
        <th style="width: 100px; "><?php echo $MSG_SUBMIT ?></th>
        <th style="width: 100px; "><?php echo $MSG_RATIO ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($view_rank as $row): ?>
        <tr>
          <?php foreach ($row as $cell): ?>
            <td><?php echo $cell; ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- 페이지네이션 -->
  <div class="ui center aligned segment">
    <div class="ui pagination menu">
      <?php
        for ($i = 0; $i < $view_total; $i += $page_size) {
          $start = $i + 1;
          $end = $i + $page_size;
          $link = "ranklist.php?start={$i}" . ($scope ? "&scope=$scope" : "");
          echo "<a class='item' href='$link'>$start-$end</a>";
        }
      ?>
    </div>
  </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
