<?php $show_title="$MSG_USERINFO - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<style>
#avatar_container:before {
    content: "";
    display: block;
    height:80%;
    width:80%;
}
</style>
<?php 
    $calsed = '비단잉어';
    $calledid = -1;
    $acneed = [10,20,30,50,80,100,200,300,500,800,1000];
    $accall = ["아이언","브론즈","실버","골드","플래티넘","에메랄드","다이아몬드","마스터","그랜드마스터","챌린저"];
    for ($i = count($accall);$i > 0; $i--) {
        if ($AC < $acneed[$i]) {$calsed = $accall[$i - 1];$calledid=$i-1;}
    }
?>

<div class="padding">
<div class="ui grid">
    <div class="row">
        <div class="five wide column">
            <div class="ui card" style="width: 100%; " id="user_card">
                <div class="blurring dimmable image" id="avatar_container">
                    <?php $default = ""; $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=500"; ?>
                    <?php  
                        $qq=stripos($email,"@qq.com");
                        if($qq>0){
                             $qq=urlencode(substr($email,0,$qq));
                             $grav_url="https://q1.qlogo.cn/g?b=qq&nk=$qq&s=5";
                        };
                    ?>

                    <img src="<?php echo $grav_url; ?>">
                </div>
                <div class="content">
                    <div class="header"><?php echo $nick?></div>
                    <div class="meta">
                        <a class="group"><?php echo $school?></a>
                        <a class="group"><?php echo $group_name?></a>
                    </div>
                </div>
                <table class="table table-hover" style="width:100%">
                <tbody>
                    <tr>
                        <th width=20%>티어</th>
                        <td width=20%><?php echo $calsed;?></td>
                        <td width=55%>다음 티어: <?php echo $accall[$calledid+1];?> 남은 문제: <?php echo $acneed[$calledid+1]-$AC;?> 문제</td>
                    </tr>
                </tbody>
                </table>
                <div class="extra content">
                    <a><i class="check icon"></i>통과한 문제 <?php echo $AC ?> 문제</a>
                    <a style="float: right;"><i class="star icon <?php if($starred) echo "active"?>" 
                        title='동일한 이름의 계정으로 hustoj 프로젝트에 별을 추가하면 별이 활성화됩니다'></i>순위 <?php echo $Rank ?></a> <!-- ✅ 변경됨 -->
                    
                    <?php if ($email != "") { ?>
                        <div style="margin-top:10px;margin-bottom:10px">
                            <a href="mailto:<?php echo "Hello" ?>?body=CSPOJ">
                                <i class="icon large envelope"></i>
                                <span style="display:inline-block; vertical-align:middle"><?php echo $email?></span>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="eleven wide column">
            <div class="ui grid" style="padding-left: 20px;">
                <div class="row">
                    <div class="column">
                        <h4 class="ui top attached block header">제출 기록</h4>
                        <div class="ui bottom attached segment">
                            <div id="sub_date_chart" style="width:100%;height:210px"></div>
                            <a href="/status.php?user_id=<?php echo $user?>"><i class="search icon"></i>이 사용자의 제출 조회</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="column">
                        <h4 class="ui top attached block header">통계</h4>
                        <div class="ui bottom attached segment">
                            <div class="ui grid">
                                <div class="row">
                                    <div id="pie_chart_legend" class="six wide column"></div>
                                    <div class="ten wide column">
                                        <canvas id="pie_chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                  
                <div class="row">
                    <div class="column">
                        <h4 class="ui top attached block header">통과한 문제</h4>
                        <div class="ui bottom attached segment">
                            <script language='javascript'>
                                function p(id, c) {
                                    if(c>0)document.write("<a title=\"U've Passed!\" href=problem.php?id=" + id + " class=\"ui green basic label\" id=\"show-problem-id\">" + id + " </a>");
                                    else document.write("<a title=\"U've Not Passed Yet!\" href=problem.php?id=" + id + " class=\"ui red basic label\" id=\"show-problem-id\">" + id + " </a>");
                                }
                                function ptot(len) {
                                    document.write("<div style='text-align:right;margin-bottom:10px'><div class='ui green small horizontal statistic'><div class='value'>" + len + "</div><div class='label'>AC</div></div></div>")
                                }
                                <?php
                                $ac=array();
                                $sql = "select `problem_id`,count(1) from solution where `user_id`=? and result=4 and problem_id != 0 $not_in_noip group by `problem_id` ORDER BY `problem_id` ASC";
                                if ($ret = mysql_query_cache($sql, $user)) {
                                    $len = count($ret);
                                    echo "ptot($len);";
                                    foreach ($ret as $row){
                                        if (isset($acc_arr[$row['problem_id']]))
                                            echo "p($row[0],$row[1]);";
                                        else
                                            echo "p($row[0],0);";
                                        array_push($ac,$row[0]);
                                    }
                                }
                                ?>
                            </script>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="column">
                        <h4 class="ui top attached block header">통과하지 못한 문제</h4>
                        <div class="ui bottom attached segment">
                            <script language='javascript'>
                                function p(id, c) {
                                    document.write("<a href=problem.php?id=" + id + " class=\"ui basic label\" id=\"show-problem-id\">" + id + " </a>");
                                }
                                <?php
                                $sql = "select `sol`.`problem_id`, count(1) from solution sol where `sol`.`user_id`=? and `sol`.`result`!=4 and sol.problem_id != 0  $not_in_noip group by `sol`.`problem_id` ORDER BY `sol`.`problem_id` ASC";
                                if ($result = mysql_query_cache($sql, $user)) {
                                    foreach ($result as $row)
                                        if(!in_array($row[0],$ac))echo "p($row[0],$row[1]);";
                                }
                                ?>
                            </script>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <h4 class="ui top attached block header">문제목록</h4>
                        <div class="ui bottom attached segment">
<?php 
echo "<table class='ui striped table '>";
foreach($plista as $plist){
	echo "<tr>";
	$name=$plist["name"];
	echo "<td>$name</td>";
	$list=explode(",",$plist['list']);
	foreach($list as $pid){
		if (in_array($pid,$ac)) $color="green"; else $color="red";
	 	echo "<td class='ui $color basic label'><a href=problem.php?id=$pid>".$bible[$pid]."</a></td>";
	}
	echo "</tr>";
}
echo "</table>";
?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <h4 class="ui top attached block header">최근 로그인 기록</h4>
                        <div class="ui bottom attached segment">
                        <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])){ ?>
                            <table border=1 class='ui table'>
                            <thead><tr class=toprow><th>UserID</th><th>Password</th><th>IP</th><th>Time</th></tr></thead>
                            <tbody>
                            <?php
                            $cnt=0;
                            foreach($view_userinfo as $row){
                                if ($cnt)
                                    echo "<tr class='oddrow'>";
                                else
                                    echo "<tr class='evenrow'>";
                                for($i=0;$i<count($row)/2;$i++){
                                    echo "<td>".$row[$i]."</td>";
                                }
                                echo "</tr>";
                                $cnt=1-$cnt;
                            }
                            ?>
                            </tbody>
                            </table>
                        <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>

<script>
    $(function() {
        $('#user_card .image').dimmer({
            on: 'hover'
        });

        var pie = new Chart(document.getElementById('pie_chart').getContext('2d'), {
            aspectRatio: 1,
            type: 'pie',
            data: {
                datasets: [{
                    data: [<?php foreach ($view_userstat as $row) echo $row[1] . ",\n"; ?>],
                    backgroundColor: ["#32CD32", "#FA8072", "#DC143C", "#FF9912", "#8A2BE2", "#4169E1", "#DB7093", "#082E54", "#FFFF00"]
                }],
                labels: [<?php foreach ($view_userstat as $row) echo "\"" . $jresult[$row[0]] . "\",\n"; ?>]
            },
            options: {
                responsive: true,
                legend: { display: false },
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul style="list-style: none; padding-left: 20px; margin-top: 0;" class="' + chart.id + '-legend">');
                    var data = chart.data;
                    var datasets = data.datasets;
                    var labels = data.labels;

                    if (datasets.length) {
                        for (var i = 0; i < datasets[0].data.length; ++i) {
                            text.push('<li style="font-size: 15px; color: #666; margin:10px 20px"><span style="width: 12px; height: 12px; display: inline-block; border-radius: 50%; margin-right: 5px; background-color: ' + datasets[0].backgroundColor[i] + '; "></span>');
                            if (labels[i]) {
                                text.push(labels[i] + ' : ' + datasets[0].data[i]);
                            }
                            text.push('</li>');
                        }
                    }

                    text.push('</ul>');
                    return text.join('');
                }
            },
        });

        document.getElementById('pie_chart_legend').innerHTML = pie.generateLegend();
    });
</script>

<?php 
$sub_data = [];
$max_count = 0;
$sql = "select DATE(in_date),count(*) FROM solution WHERE user_id=? AND in_date >= DATE_SUB(CURDATE(),INTERVAL 1 YEAR) AND result < 13 $not_in_noip GROUP BY DATE(in_date);";
$ret = mysql_query_cache($sql, $user);
foreach ($ret as $row) {
    array_push($sub_data, [$row[0], (int)$row[1]]);
    $max_count = max($max_count, (int)$row[1]);
}
date_default_timezone_set('PRC');
$today = date('Y-m-d', time());
$beg_time = date('Y-m-d', strtotime("-6 month"));
?>

<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE"?>/js/echarts.min.js"></script>
<script type="text/javascript">
    var chartDom = document.getElementById('sub_date_chart');
    var myChart = echarts.init(chartDom);
    var option;
    option = {
        title: { top: 30, left: 'center' },
        tooltip: {
            formatter: function(params) {
                return params.value[0] + '<br>제출 수: ' + params.value[1]; // ✅ 변경됨
            }
        },
        visualMap: {
            min: 0,
            max: <?php echo $max_count ?>,
            show: false,
            type: 'piecewise',
            orient: 'horizontal',
            left: 'center',
            top: 10,
            inRange: { color: ['#80d596', '#156344'] }
        },
        calendar: {
            top: 30,
            left: 40,
            right: 30,
            cellSize: [20, 20],
            range: ['<?php echo $beg_time ?>', '<?php echo $today ?>'],
            itemStyle: { borderWidth: 0.5 },
            lineStyle: { color: '#D10E00', width: 1, opacity: 1 },
            yearLabel: { show: false },
            dayLabel: { firstDay: 1, nameMap: 'cn', margin: '8px' },
            monthLabel: { nameMap: 'cn', margin: 15, fontSize: 14, color: 'gray' },
            splitLine: { show: false }
        },
        series: {
            name: '제출 수',
            type: 'heatmap',
            coordinateSystem: 'calendar',
            data: <?php echo json_encode($sub_data, false); ?>,
        }
    };

    option && myChart.setOption(option);
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
