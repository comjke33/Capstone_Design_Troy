<script>
window.onload = function () {
    const pid = "<?php echo $problem_id; ?>";
    console.log("문제 번호:", pid);

    if (!pid) {
        alert("❌ 문제 번호가 지정되지 않았습니다.");
        return;
    }

    fetch(`get_random_defect_code.php?problem_id=${pid}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`서버 오류: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === "ok") {
                document.getElementById("source").value = data.code;
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("❌ fetch 오류 발생:", error);
            alert("서버에서 결함 코드를 불러오는 데 실패했습니다.");
        });
};
</script>