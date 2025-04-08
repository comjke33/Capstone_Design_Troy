<?php 
// ini_set("display_errors", "Off");  // 오류 디버깅을 위해 "On"으로 설정 (화면이 갑자기 비었을 때 원인 파악에 유용함)
// error_reporting(E_ALL);
// header('X-Frame-Options:SAMEORIGIN');
// 중국 외 지역에서 hustoj를 사용하는 경우, 주석은 번역 프로그램으로 확인하세요
// 이 파일은 시스템 전체 설정 파일입니다. 매우 중요하므로 저장 시 주의하세요. 세미콜론이나 따옴표 하나 빠져도 사이트 전체가 작동하지 않을 수 있습니다.
// 만약 사이트가 열리지 않는 경우, 이 파일을 백업 후 삭제하고 /home/judge/src/install/fixing.sh 스크립트를 실행하여 복원할 수 있습니다.
// $DB_PASS="..."; 는 설치 시 무작위로 생성된 비밀번호입니다. 유출하지 마시고, 약한 비밀번호로 변경하지 않는 것이 좋습니다. root로 접속하려면 리눅스에서 `sudo mysql`을 사용하세요.

// DB 연결 정보
static $DB_HOST = "localhost";     // 데이터베이스 서버 IP 또는 도메인
static $DB_NAME = "jol";           // 데이터베이스 이름
static $DB_USER = "hustoj";        // 데이터베이스 계정
static $DB_PASS = "JGqRe4pltka5e5II4Di3YZdmxv7SGt"; // 데이터베이스 비밀번호

// 사이트 설정
static $OJ_NAME = "TroyOJ"; // 좌측 상단에 표시될 시스템 이름 (가급적 짧고 영문으로, 긴 문자열이나 이미지 사용 시 template/syzoj/header.php 직접 수정)
static $OJ_HOME = "./";     // 홈 디렉토리
static $OJ_ADMIN = "root@localhost";  // 관리자 이메일, 시스템 메일 기능을 사용할 경우 SMTP 설정 필요

// 이메일 관련 설정
static $SMTP_SERVER = "smtp.qq.com";  // SMTP 서버 주소
static $SMTP_PORT = 587;              // SMTP 포트 (일반적으로 25, 465, 587 등 사용)
static $SMTP_USER = "mailer@qq.com";  // 메일 발신자 주소
static $SMTP_PASS = "your_smpt_auth_password"; // SMTP 인증 비밀번호 (일반적으로 메일 서비스에서 생성)

// 기본 경로 설정
static $OJ_DATA = "/home/judge/data"; // 테스트 데이터 저장 경로
static $OJ_BBS = false; // 토론 게시판 설정 ("discuss3", "bbs", "discuss" 또는 false)
static $OJ_ONLINE = false; // 온라인 상태 추적 여부
static $OJ_LANG = "ko"; // 기본 언어 (cn=중국어, ko=한국어, en=영어)
static $OJ_SIM = false; // 코드 유사도 표시 여부
static $OJ_DICT = false; // 온라인 사전 표시 여부
static $OJ_LANGMASK = 33554356; // 언어 마스크 설정
static $OJ_ACE_EDITOR = true;  // 코드 제출 시 ACE 편집기 사용 여부
static $OJ_AUTO_SHARE = false; // 문제를 맞힌 경우 코드 공유 여부
static $OJ_CSS = "white.css";  // 테마 CSS 선택
static $OJ_SAE = false;        // SAE 엔진 사용 여부
static $OJ_VCODE = false;      // 로그인 시 CAPTCHA 사용 여부
static $OJ_REG_SPEED = 60;     // 시간당 IP당 등록 제한 수 (0은 무제한)
static $OJ_APPENDCODE = true;  // 코드 템플릿 삽입 여부

if (!$OJ_APPENDCODE) ini_set("session.cookie_httponly", 1); // 코드 템플릿 모드에서는 JS가 쿠키 접근 가능해야 함
@session_start();

static $OJ_CE_PENALTY = false; // 컴파일 에러 시 시간 패널티 부여 여부
static $OJ_PRINTER = false;    // 출력 기능 사용 여부
static $OJ_MAIL = false;       // 내부 메일 사용 여부
static $OJ_MARK = "mark";      // 점수 표시 방식 ("mark": 정답 수, "percent": 오답 비율)
static $OJ_MEMCACHE = true;    // 메모리 캐시 사용 여부
static $OJ_MEMSERVER = "127.0.0.1";
static $OJ_MEMPORT = 11211;

// 판정 서버 UDP 설정
static $OJ_UDP = true;
static $OJ_UDPSERVER = "127.0.0.1"; // 여러 서버 사용 시 쉼표로 구분, 포트 지정 시 콜론 사용
static $OJ_UDPPORT = 1536;
static $OJ_JUDGE_HUB_PATH = "../judge";

// Redis 큐 설정
static $OJ_REDIS = false;
static $OJ_REDISSERVER = "127.0.0.1";
static $OJ_REDISPORT = 6379;
static $OJ_REDISQNAME = "hustoj";

// 클라우드 스토리지 / CDN
static $SAE_STORAGE_ROOT = "http://hustoj-web.stor.sinaapp.com/";
static $OJ_CDN_URL = ""; // 정적 리소스 CDN 주소

// 템플릿 및 배경 설정
static $OJ_TEMPLATE = "syzoj";
static $OJ_BG = "/image/background.jpg";
// static $OJ_BG = "http://cdn.hustoj.com/upload/bg/bing".date('H').".jpg"; // 시간대별 배경

// 로그인 및 등록 관련
static $OJ_LOGIN_MOD = "hustoj";
static $OJ_REGISTER = true;
static $OJ_REG_NEED_CONFIRM = false;
static $OJ_EMAIL_CONFIRM = false;
static $OJ_EXPIRY_DAYS = 365;

// 로그인 보안
static $OJ_NEED_LOGIN = false;
static $OJ_LONG_LOGIN = false;
static $OJ_KEEP_TIME = "30"; // 자동 로그인 유지 일수

// 문제 편집기 자동 열기 여부
static $OJ_AUTO_SHOW_OFF = false;

// 랭킹 설정
static $OJ_RANK_LOCK_PERCENT = 0;
static $OJ_RANK_LOCK_DELAY = 3600;
static $OJ_SHOW_METAL = true;

// 결과 상세 보기
static $OJ_SHOW_DIFF = true;
static $OJ_HIDE_RIGHT_ANSWER = true;
static $OJ_DL_1ST_WA_ONLY = false;
static $OJ_DOWNLOAD = false;
static $OJ_TEST_RUN = false;
static $OJ_MATHJAX = true;
static $OJ_BLOCKLY = false;
static $OJ_ENCODE_SUBMIT = false;
static $OJ_OI_1_SOLUTION_ONLY = false;
static $OJ_OI_MODE = false;

// 벤치마크 모드
static $OJ_BENCHMARK_MODE = false;
static $OJ_CONTEST_RANK_FIX_HEADER = false;
static $OJ_NOIP_KEYWORD = "noip";
static $OJ_NOIP_HINT = false;
static $OJ_CONTEST_LIMIT_KEYWORD = "限时";
static $OJ_OFFLINE_ZIP_CCF_DIRNAME = true;
static $OJ_BEIAN = false;

// 랭킹에 관리자 제외
static $OJ_RANK_HIDDEN = "'admin','zhblue'";

// 사용자 친화도
static $OJ_FRIENDLY_LEVEL = 1;

// 연습 및 냉각 시간 설정
static $OJ_FREE_PRACTICE = false;
static $OJ_SUBMIT_COOLDOWN_TIME = 10;
static $OJ_POISON_BOT_COUNT = 10;

// 마크다운 설정
static $OJ_MARKDOWN = "marked.js";
static $OJ_INDEX_NEWS_TITLE = 'HelloWorld!';
static $OJ_DIV_FILTER = false;
static $OJ_LIMIT_TO_1_IP = false;
static $OJ_REMOTE_JUDGE = false;
static $OJ_NO_CONTEST_WATCHER = false;
static $OJ_CONTEST_TOTAL_100 = false;
static $OJ_OLD_FASHINED = false;
static $OJ_AI_HTML = false;
static $OJ_PUBLIC_STATUS = true;
static $OJ_FANCY_RESULT = false;
static $OJ_FANCY_MP3 = 'http://cdn.hustoj.com/mp3.php';

// 시험 모드 예시 (주석 처리되어 있음)
// static $OJ_EXAM_CONTEST_ID = 1000;
// static $OJ_ON_SITE_CONTEST_ID = 1000;

/* 코드 공유 */
static $OJ_SHARE_CODE = false;

/* 최근 대회 */
static $OJ_RECENT_CONTEST = false;

static $OJ_ON_SITE_TEAM_TOTAL = 0;
static $OJ_OPENID_PWD = '8a367fe87b1e406ea8e94d7d508dcf01';

/* SNS 로그인 설정 */
static $OJ_WEIBO_AUTH = false;
static $OJ_WEIBO_AKEY = '1124518951';
static $OJ_WEIBO_ASEC = 'df709a1253ef8878548920718085e84b';
static $OJ_WEIBO_CBURL = 'http://192.168.0.108/JudgeOnline/login_weibo.php';

static $OJ_RR_AUTH = false;
static $OJ_RR_AKEY = 'd066ad780742404d85d0955ac05654df';
static $OJ_RR_ASEC = 'c4d2988cf5c149fabf8098f32f9b49ed';
static $OJ_RR_CBURL = 'http://192.168.0.108/JudgeOnline/login_renren.php';

static $OJ_QQ_AUTH = false;
static $OJ_QQ_AKEY = '1124518951';
static $OJ_QQ_ASEC = 'df709a1253ef8878548920718085e84b';
static $OJ_QQ_CBURL = '192.168.0.108';

/* 로그 설정 */
static $OJ_LOG_ENABLED = false;
static $OJ_LOG_DATETIME_FORMAT = "Y-m-d H:i:s";
static $OJ_LOG_PID_ENABLED = false;
static $OJ_LOG_USER_ENABLED = false;
static $OJ_LOG_URL_ENABLED = false;
static $OJ_LOG_URL_HOST_ENABLED = false;
static $OJ_LOG_URL_PARAM_ENABLED = false;
static $OJ_LOG_TRACE_ENABLED = false;

static $OJ_SaaS_ENABLE = false;
static $OJ_MENU_NEWS = true;

require_once(dirname(__FILE__) . "/pdo.php");
require_once(dirname(__FILE__) . "/init.php");
