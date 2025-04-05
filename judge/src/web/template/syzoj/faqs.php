<?php $show_title="$MSG_FAQ - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">
    <h1 class="ui center aligned header"><?php echo $MSG_FAQ ?></h1>
    <div style="font-content">
        <h2 class="ui header">평가</h2>
        <p>
            <br> C는 <code>gcc 11.4.0</code>을 사용하여 컴파일되며, 명령어는
            &nbsp;<code>gcc Main.c -o Main -fno-asm -Wall -lm --static -O2 -std=c99 -DONLINE_JUDGE</code>
            <br> C++는 <code>g++ 11.4.0</code>을 사용하여 컴파일되며, 명령어는
            &nbsp;<code>g++ -fno-asm -Wall -lm --static -O2 -std=c++17 -DONLINE_JUDGE -o Main Main.cc</code>입니다;
            <br> Pascal은 <code>fpc 3.2.2</code>을 사용하여 컴파일되며, 명령어는
            &nbsp;<code>fpc Main.pas -oMain -O1 -Co -Cr -Ct -Ci</code>입니다.
            <br> Java는 <code>OpenJDK 17.0.4</code>을 사용하여 컴파일되며, 명령어는
            <code>javac -J-Xms64m -J-Xmx128m Main.java</code>입니다. 코드에 <code>public class</code>가 없다면, 엔트리 클래스 이름을 <code>Main</code>으로 지정하십시오. 평가 시에는 추가로 2초의 실행 시간과 64MB의 실행 메모리를 제공합니다.
            <br>
            여기에 제공된 컴파일러 버전은 참고용이며, 실제 컴파일러 버전을 기준으로 사용해야 합니다.
        </p>
        <p>표준 입력/출력을 사용하십시오.</p>
<h2 class="ui header">Q: cin/cout이 왜 시간 초과(TLE)가 발생하나요?</h2>
<p>A: cin/cout은 기본적으로 stdin/stdout과 동기화되어 성능이 저하되고, 시스템 호출이 더 많이 발생하여 성능에 영향을 미칩니다. 이를 개선하려면 main 함수의 시작 부분에 아래 코드를 추가하여 속도를 개선할 수 있습니다:
       <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">ios::sync_with_stdio(false);
cin.tie(0);</pre>
        </div>

        * 또한, endl 대신 '\n'을 사용하는 것이 좋습니다. endl은 기본적으로 플러시(flush) 작업을 추가하여 출력 버퍼를 비우고 성능을 떨어뜨립니다.
    </p>

<h2 class="ui header">Q: gets 함수가 없어졌나요?</h2>
<p>A: gets 함수는 입력 길이를 제한할 수 없어서 많은 버퍼 오버플로우 취약점이 발생했기 때문에 최신 버전에서 완전히 제거되었습니다. 대신 fgets 함수를 사용하십시오. 또는 아래와 같은 매크로 정의로 대체할 수 있습니다:
    <div class="ques-view">   #define gets(S) fgets(S,sizeof(S),stdin)  </div>
</p>
<h2 class="ui header">Q: 왜 내 코드가 로컬에서 잘 실행되는데 제출 후 틀렸다고 나왔나요?</h2>
<p>A: 입력 버퍼를 지우기 위해 rewind를 사용하지 마십시오. OJ의 입력은 파일과 관련이 있으며, 키보드 입력과는 다릅니다. 다른 사람들이 문제를 올바르게 해결했다면, 당신의 코드가 모든 경우를 고려하지 않았을 가능성이 있습니다.
    샘플 데이터만으로는 정답을 보장할 수 없습니다. 만약 모든 사람이 정답을 제출하지 못했다면, 테스트 데이터에 문제가 있을 수 있습니다. 이 경우 관리자 <?php echo $OJ_ADMIN ?>에게 문의해 주세요.
    
</p>
        
<h2 class="ui header">개인 프로필<br></h2>
        <p>이 사이트는 아바타 저장 서비스를 제공하지 않으며, QQ 아바타를 사용하여 표시됩니다. QQ 이메일로 등록하면 시스템이 자동으로 QQ의 아바타를 사용합니다.</p>
        <h2 class="ui header">결과 설명<br></h2>
        <div class="ques-view">
            <p>문제의 답안을 제출한 후, 평가 시스템에서 즉시 점수를 부여하고, 제출 결과는 즉시 알림으로 제공됩니다. 시스템에서 제공할 수 있는 피드백 메시지는 다음과 같습니다:</p>
            <li>대기 중: 평가 시스템이 아직 이 제출을 평가하지 않았습니다. 잠시 기다려 주세요.</li>
            <li>평가 중: 평가 시스템이 평가 중입니다. 결과를 기다려 주세요.</li>
            <li>컴파일 오류: 제출한 코드가 컴파일되지 않았습니다. "컴파일 오류"를 클릭하여 컴파일러의 오류 메시지를 확인할 수 있습니다.</li>
            <li>정답: 축하합니다! 문제를 해결했습니다.</li>
            <li>형식 오류: 프로그램 출력 형식이 요구 사항과 맞지 않습니다 (예: 공백 및 줄바꿈이 맞지 않음).</li>
            <li>답안 오류: 프로그램이 평가 시스템의 데이터를 잘못 처리했습니다.</li>
            <li>시간 초과: 프로그램이 주어진 시간 내에 실행을 마치지 못했습니다.</li>
            <li>메모리 초과: 프로그램이 허용된 메모리 용량을 초과했습니다.</li>
            <li>실행 오류: 프로그램 실행 중에 크래시가 발생했습니다. 예: 세그멘테이션 오류, 부동소수점 오류 등.</li>
            <li>출력 초과: 프로그램이 너무 많은 데이터를 출력했습니다. 일반적으로 무한 루프 출력이 원인입니다.</li>
        </div>


        <h2>샘플 프로그램</h2>
        <p>다음은 이 간단한 문제를 해결하는 데 사용할 수 있는 샘플 프로그램입니다: <strong>두 정수 A와 B를 입력받고, 그 합을 출력하는 문제입니다.</strong></p>
        <p><strong>gcc (.c)</strong></p>
        <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">
<code class="lang-c">#include &lt;stdio.h&gt;
int main(){
    int a, b;
    while(scanf("%d %d",&amp;a, &amp;b) != EOF){
        printf("%d\n", a + b);
    }
    return 0;
}</code></pre>
        </div>
        <p><strong>g++ (.cpp)</strong></p>
        <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">
<code class="lang-c++">#include &lt;iostream&gt;
using namespace std;
int main(){
    // io 속도 향상
    const char endl = '\n';
    std::ios::sync_with_stdio(false);
    cin.tie(nullptr);

    int a, b;
    while (cin &gt;&gt; a &gt;&gt; b){
        cout &lt;&lt; a+b &lt;&lt; endl;
    }
    return 0;
}</code></pre>
        </div>
        <p><strong>fpc (.pas)</strong></p>
        <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">
<code class="lang-pascal">var
a, b: integer;
begin
    while not eof(input) do begin
        readln(a, b);
        writeln(a + b);
    end;
end.</code></pre>
        </div>
        <p><strong>javac (.java)</strong></p>
        <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">
<code class="lang-java">import java.util.Scanner;	
public class Main {
    public static void main(String[] args) {
        Scanner in = new Scanner(System.in);
        while (in.hasNextInt()) {
            int a = in.nextInt();
            int b = in.nextInt();
            System.out.println(a + b);
        }
    }
}</code></pre>
        </div>
        <p><strong>python3 (.py)</strong></p>
        <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">
<code class="lang-c">import io
import sys
sys.stdout = io.TextIOWrapper(sys.stdout.buffer,encoding='utf8')
for line in sys.stdin:
    a = line.split()
    print(int(a[0]) + int(a[1]))</code></pre>
        </div>
        <p><strong>仓颉 (.cj)</strong></p>
        <div class="ui existing segment">
            <pre style="margin-top: 0; margin-bottom: 0; ">
<code class="lang-python">import std.core.*
import std.console.*
import std.collection.*
import std.convert.*

main(): Int64 {
    while (let Some(line) <- Console.stdIn.readln()) {
        let nums = map(Int.parse)(line.split(" "))
        let sum = reduce({a: Int64, b: Int64 => a + b})(nums)
        if (let Some(r) <- sum) {
            Console.stdOut.writeln(r)
        }
    }
    return 0
}</code></pre>
        </div>
    </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
