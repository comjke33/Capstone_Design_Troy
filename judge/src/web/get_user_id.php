<?php
require_once "include/db_info.inc.php";
header("Content-Type: application/json");

// 선택할 user_id 목록
$allowed_user_ids = [
    'admin',
    'endtoend',
    'end_to_end',
    'ERRORMAN',
    'ERRORMON',
    'errortest',
    'error_test',
    'error_test1',
    'guideline_user1',
    'guideline_user2',
    // 'notguideline_user1',
    // 'notguideline_user2',
    'notification_test',
    'notification_test2',
    'sonson',
    'test',
    'test1',
    'test2',
    'test3',
    'test4',
    'test5',
    'zxccyh',
    'zxczxc'
];

return $allowed_user_ids;