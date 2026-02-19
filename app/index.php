<?php
require 'includes/db.php';

$pdo = getDb('viewer');
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
            <div class="links-grid">
                <?php foreach ($links as $link): 
                    $parsedUrl = parse_url($link['url']);
                    $domain = $parsedUrl['host'] ?? '';
                    $faviconUrl = "https://www.google.com/s2/favicons?domain=" . urlencode($domain) . "&sz=128";
                ?>
                <a class="link-card glass-box"
                   href="<?= htmlspecialchars($link['url']) ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                    <img class="link-icon" 
                         src="<?= htmlspecialchars($faviconUrl) ?>" 
                         alt="<?= htmlspecialchars($link['name']) ?>"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="link-icon-fallback" style="display:none;">
                        <?= strtoupper(mb_substr($link['name'], 0, 1)) ?>
                    </div>
                    <span class="link-name"><?= htmlspecialchars($link['name']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-right">
            <div class="glass-box">
                <h4><div id="wearenow"></div></h4>
                <h2><div id="wearenowlabel"></div></h2>
            </div>
            <div class="glass-box">
                <h4><div id="countdown"></div></h4>
                <h2><div id="countdowntolabel"></div></h2>
            </div>
            <a href="/admin/" class="glass-box settings-link">
                <h2>⚙ Settings</h2>
                <p>Manage links</p>
            </a>
        </div>

    </div>

    <script src="js/load_time&weather.js"></script>
</body>
</html>