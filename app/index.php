<?php
require 'includes/db.php';

$stmt = $pdo->query("
    SELECT name, url
    FROM def_links
    ORDER BY name
");
$links = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glass Bento Layout</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div class="background-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="ui-container">
        
        <div class="col-left">
            <div class="glass-box">
                <h2><div id="currentdd"></div></h2>
                <h4><div id="currenttt"></div></h4>
                <h2><div id="week"></div><h2>
            </div>
            <div class="glass-box">
                <h2>Sidebar Top</h2>
                <p>Widget 1</p>
            </div>
        </div>

        <div class="col-mid">
            <div class="glass-box">
                <?php foreach ($links as $link): ?>
    <a class="shortcut"
       href="<?= htmlspecialchars($link['url']) ?>"
       target="_blank">
        <?= htmlspecialchars($link['name']) ?>
    </a>
<?php endforeach; ?>
            </div>
        </div>

        <div class="col-right">
            <div class="glass-box">
                <img src="" alt="" srcset="" id="weather-icon">
                <div class="description">
                    <h2><div class="desc"></div></h2>
                    <p><div class="c"></div></p>
                </div>
            </div>
            <div class="glass-box">
                <h4><div id="wearenow"></div></h4>
                <h2><div id="wearenowlabel"></div></h2>
            </div>
            <div class="glass-box">
                <h4><div id="countdown"></div></h4>
                <h2><div id="countdowntolabel"></div></h2>
            </div>
            <div class="glass-box">
                <h2>Settings</h2>
                <p>at some point</p>
            </div>
        </div>

    </div>

    <script src="js/load_time&weather.js"></script>
</body>
</html>