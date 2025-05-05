<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/Parsedown.php');
require_once(__DIR__ . '/ParsedownExtra.php');
require_once(__DIR__ . '/ParsedownWithAnchor.php');

$md = file_get_contents(__DIR__ . '/ref.md');
$Parsedown = new ParsedownWithAnchor();
$Parsedown->setSafeMode(true);
var_dump(get_class($Parsedown)); exit;
$html = $Parsedown->text($md);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>문법 개념 레퍼런스</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            max-width: 1200px;
            margin: 2rem auto;
        }
        #toc {
            width: 250px;
            position: sticky;
            top: 20px;
            padding-right: 20px;
            border-right: 1px solid #ccc;
            height: 90vh;
            overflow-y: auto;
        }
        #content {
            flex: 1;
            padding-left: 20px;
        }
        .toc-list {
            list-style: none;
            padding-left: 0;
        }
        .toc-list li {
            margin-bottom: 0.3em;
        }
        .toc-link {
            text-decoration: none;
            color: #333;
        }
        .toc-link:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tocbot@4.17.1/dist/tocbot.css">
</head>
<body>
    <nav id="toc"></nav>
    <main id="content">
        <?= $html ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/tocbot@4.17.1/dist/tocbot.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        tocbot.init({
            tocSelector: '#toc',
            contentSelector: '#content',
            headingSelector: 'h2, h3',
            collapseDepth: 6
        });
    });
</script>
</body>
</html>