<!DOCTYPE html>
<html lang="ko">

<head>
    <?php $page_title = $view_cid.' '.$view_title; ?>
    <?php include('_includes/head.php'); ?>
</head>

<body class="mdui-drawer-body-left mdui-theme-primary-indigo mdui-theme-accent-indigo mdui-appbar-with-toolbar">
    <?php include('_includes/header.php'); ?>
    <?php include('_includes/sidebar.php'); ?>
    <div class="mdui-container">
        <div class="mdui-card" style="text-align: center;">
            <div class="mdui-card-primary">
                <div class="mdui-card-primary-title"><?php echo $view_title ?></div>
                <div class="mdui-card-primary-subtitle">대회 ID: <?php echo $view_cid?></div>
            </div>
            <div class="mdui-card-content"><?php echo $view_description?></div>
            <div class="mdui-chip mdui-m-y-1" align="center">
                <span class="mdui-chip-title">시스템 시간: <span id="nowdate" style="font-weight: 700;"></span></span>
            </div>
            <br>

            <?php if (isset($OJ_RANK_LOCK_PERCENT)&&$OJ_RANK_LOCK_PERCENT!=0) { ?>
                점수판 잠금 시간: <?php echo date("Y-m-d H:i:s", $view_lock_time) ?><br />
            <?php } ?>

            <div class="mdui-chip">
                <span class="mdui-chip-icon"><i class="mdui-icon material-icons">face</i></span>
                <span class="mdui-chip-title">
                    <span>대회 상태:</span>
                    <?php if ($now > $end_time) { ?>
                        <b class="">종료됨</b>
                    <?php } else if ($now < $start_time) { ?>
                        <b class="">시작 전</b>
                    <?php } else { ?>
                        <b class="">진행 중</b>
                    <?php } ?>
                </span>
            </div>

            <div class="mdui-chip">
                <span class="mdui-chip-icon"><i class="mdui-icon material-icons">remove_red_eye</i></span>
                <span class="mdui-chip-title">
                    <?php if ($view_private=='0') { ?>
                        <b class="">공개</b>
                    <?php } else { ?>
                        <b class="">비공개</b>
                    <?php } ?>
                </span>
            </div>
            
            <div class="mdui-chip">
                <span class="mdui-chip-icon"><i class="mdui-icon material-icons">access_time</i></span>
                <span class="mdui-chip-title">
                    <span>시작 시간:</span>
                    <b><?php echo $view_start_time?></b>
                </span>
            </div>
            <div class="mdui-chip">
                <span class="mdui-chip-icon"><i class="mdui-icon material-icons">access_time</i></span>
                <span class="mdui-chip-title">
                    <span>종료 시간:</span>
                    <b><?php echo $view_end_time?></b>
                </span>
            </div>

            <div class="mdui-card-actions mdui-m-y-2">
                <a href="contest.php?cid=<?php echo $cid?>"
                    class="mdui-btn mdui-ripple mdui-color-blue-600">문제</a>
                <a href="status.php?cid=<?php echo $view_cid?>"
                    class="mdui-btn mdui-ripple mdui-color-blue-600">제출</a>
                <a href="contestrank.php?cid=<?php echo $view_cid?>"
                    class="mdui-btn mdui-ripple mdui-color-blue-600">순위</a>
                <a href="contestrank-oi.php?cid=<?php echo $view_cid?>"
                    class="mdui-btn mdui-ripple mdui-color-blue-600">OI 순위</a>
                <a href="conteststatistics.php?cid=<?php echo $view_cid?>"
                    class="mdui-btn mdui-ripple mdui-color-blue-600">통계</a>
                <a href="suspect_list.php?cid=<?php echo $view_cid?>"
                    class="mdui-btn mdui-ripple mdui-color-purple">IP 검증</a>
                <?php if(  isset($_SESSION[$OJ_NAME.'_'.'administrator'])
                        || isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])) { ?>
                    <a href="user_set_ip.php?cid=<?php echo $view_cid?>"
                        class="mdui-btn mdui-ripple mdui-color-red">IP 지정</a>
                    <a target="_blank" href="../../admin/contest_edit.php?cid=<?php echo $view_cid?>"
                        class="mdui-btn mdui-ripple mdui-color-red">편집</a>
                <?php } ?>
            </div>
        </div>

        <table id="problemset" class="mdui-table mdui-table-hoverable mdui-m-y-3" width="90%">
            <thead>
                <tr>
                    <th></th>
                    <th style="cursor: hand;" onclick="sortTable('problemset', 1, 'int');">문제 번호</th>
                    <th>제목</th>
                    <!-- <th>분류</th> -->
                    <th style="cursor: hand;" onclick="sortTable('problemset', 4, 'int');">맞힌 수</th>
                    <th style="cursor: hand;" onclick="sortTable('problemset', 5, 'int');">제출 수</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $cnt=0;
                    foreach ($view_problemset as $row) {
                        echo "<tr>";
                        
                        foreach ($row as $table_cell) {
                            echo "<td>";
                            echo "\t".$table_cell;
                            echo "</td>";
                        }
                        echo "</tr>";
                        $cnt=1-$cnt;
                    }
                ?>
            </tbody>
        </table>
    </div>
