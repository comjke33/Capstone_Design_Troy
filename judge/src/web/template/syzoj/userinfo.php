<?php $show_title="$MSG_USERINFO - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<style>
#avatar_container {
    margin: 2rem auto 1.5rem auto;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(33, 133, 208, 0.4);
}
#avatar_container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
</style>
<?php 
    $calsed = '비단잉어';
    $calledid = -1;
        // 이미지 파일명 배열, 실제 이미지 경로에 맞게 경로 수정 필요
    $accall_img = [
    "../../image/iron.png",
    "../../image/bronze.png",
    "../../image/silver.png",
    "../../image/gold.png",
    "../../image/platinum.png",
    "../../image/emerald.png",
    "../../image/diamond.png",
    "../../image/master.png",
    "../../image/grandmaster.png",
    "../../image/challenger.png"
];

// 티어 결정 로직 (역순 검사)
for ($i = count($acneed) - 1; $i >= 0; $i--) {
    if ($AC >= $acneed[$i]) {
        $calsed = $accall_img[$i];
        $calledid = $i;
        break;
    }
}

// 기본 티어가 없다면 최하위 설정
if ($calledid == -1) {
    $calledid = 0;
    $calsed = $accall_img[0];
}

?>

<style>
    /* 카드 컨테이너 */
    #user_card {
        width: 100%;
        max-width: 400px;
        border-radius: 15px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #ffffff;
        overflow: hidden;
        margin: 0 auto;
        padding-bottom: 1rem;
    }

    /* 아바타 */
    #avatar_container {
        margin: 2rem auto 1.5rem auto;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(33, 133, 208, 0.4);
    }
    #avatar_container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* 닉네임 */
    #user_card .header {
        text-align: center;
        font-size: 1.8rem;
        font-weight: 700;
        color: #2185d0;
        margin-bottom: 0.3rem;
    }

    /* 학교 및 그룹명 */
    #user_card .meta {
        text-align: center;
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 1rem;
    }
    #user_card .meta a.group {
        margin: 0 10px;
        color: #666;
        font-weight: 500;
    }

    /* 티어 테이블 */
    #user_card table {
        width: 90%;
        margin: 0 auto 1rem auto;
        border-collapse: separate;
        border-spacing: 0 10px;
        font-size: 1rem;
        color: #444;
    }
    #user_card th {
        font-weight: 600;
        width: 25%;
        text-align: left;
        color: #333;
    }
    #user_card td {
        padding-left: 10px;
        color: #555;
    }

    /* 하단 정보 영역 */
    #user_card .extra.content {
        display: flex;
        justify-content: space-between;
        padding: 0 2rem;
        font-size: 1rem;
        color: #444;
    }
    #user_card .extra.content a {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #444;
    }
    #user_card .extra.content a i.check.icon {
        color: #21ba45;
        font-size: 1.2rem;
    }
    #user_card .extra.content a i.star.icon.active {
        color: #fbbd08;
        font-size: 1.3rem;
    }

    /* 이메일 */
    #user_card .email-container {
        text-align: center;
        margin-top: 1rem;
    }
    #user_card .email-container a {
        color: #2185d0;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 1rem;
    }
    #user_card .email-container a:hover {
        text-decoration: underline;
    }
    #user_card .email-container i.icon.large.envelope {
        font-size: 1.4rem;
    }
</style>

<div class="padding">
<div class="ui grid">
    <div class="row">
        <div class="five wide column">
            <div class="ui card" id="user_card">
                <div id="avatar_container" style="width:130px; height:130px; border-radius:50%; overflow:hidden; margin: 0 auto;">
                    <?php 
                        // 예시 아바타 URL (필요시 수정)
                        $grav_url = isset($grav_url) ? $grav_url : "https://images.unsplash.com/photo-1517423440428-a5a00ad493e8?auto=format&fit=crop&w=500&q=80";
                    ?>
                    <img src="<?php echo htmlspecialchars($grav_url); ?>" alt="User Avatar">
                </div>

                <div class="content">
                    <div class="header"><?php echo isset($nick) ? htmlspecialchars($nick) : '닉네임 없음'; ?></div>
                    <div class="meta">
                        <!-- 학교 및 그룹명 필요시 주석 해제하고 변수 선언 필요 -->
                        <!-- <a class="group"><?php echo htmlspecialchars($school); ?></a>
                        <a class="group"><?php echo htmlspecialchars($group_name); ?></a> -->
                    </div>
                </div>
                <table>
                    <tbody>
                        <tr>
                            <th>티어</th>
                            <td>
                                <img src="<?php echo htmlspecialchars($calsed); ?>" alt="티어 이미지" style="width: 50px; vertical-align: middle;">
                                <span style="margin-left:10px;"><?php echo htmlspecialchars($accall[$calledid]); ?></span>
                            </td>
                            <td>
                                <?php 
                                    $nextIndex = $calledid + 1;
                                    if ($nextIndex < count($accall) && $nextIndex < count($acneed)) {
                                        $remain = $acneed[$nextIndex] - $AC;
                                        if ($remain < 0) $remain = 0;
                                        echo "다음 티어: " . htmlspecialchars($accall[$nextIndex]) . " 남은 문제: " . $remain . " 문제";
                                    } else {
                                        echo "최고 티어입니다!";
                                    }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="extra content">
                    <a><i class="check icon"></i>통과한 문제 <?php echo htmlspecialchars($AC); ?> 문제</a>
                    <a style="float: right;">
                        <i class="star icon <?php echo (isset($starred) && $starred) ? "active" : ""; ?>" 
                           title="동일한 이름의 계정으로 hustoj 프로젝트에 별을 추가하면 별이 활성화됩니다"></i>
                        순위 <?php echo isset($Rank) ? htmlspecialchars($Rank) : '-'; ?>
                    </a>
                </div>

                <?php if (isset($email) && $email != "") { ?>
                    <div class="email-container">
                        <a href="mailto:<?php echo htmlspecialchars($email); ?>?body=CSPOJ" class="email-link">
                            <i class="icon large envelope"></i>
                            <span><?php echo htmlspecialchars($email); ?></span>
                        </a>
                    </div>
                <?php } ?>
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
            dayLabel: {
                firstDay: 1,
                nameMap: ['일', '월', '화', '수', '목', '금', '토'], // 한국어 요일
                margin: '8px'
            },
            monthLabel: {
                nameMap: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'], // 한국어 월
                margin: 15,
                fontSize: 14,
                color: 'gray'
            },

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
