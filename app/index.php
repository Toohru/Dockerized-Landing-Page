<?php
require 'includes/db.php';
require 'includes/theme.php';
require 'includes/backgrounds.php';
require 'includes/auth.php';

$pdo = getDb('viewer');

// Redirect to setup wizard if not configured yet
if (!isSetupComplete($pdo)) {
    header('Location: /setup/');
    exit;
}

// Identify the current user
$username = getCurrentUsername($pdo);
$userProfile = getOrCreateUserProfile($pdo, $username);

// Load user-specific or default settings
$activeTheme = getThemeForUser($pdo, $userProfile);
$activeBgKey = getBackgroundForUser($pdo, $userProfile);
$links = getLinksForUser($pdo, $userProfile);

// School name
$stmtSchool = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'school_name'");
$schoolName = ($stmtSchool->fetch())['setting_value'] ?? 'Landing Page';

// Display name
$displayName = null;
if ($userProfile) {
    $displayName = $userProfile['display_name'] ?: $userProfile['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($schoolName) ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <?php if ($activeTheme): ?>
        <?= themeCSS($activeTheme) ?>
    <?php endif; ?>
</head>
<body>

    <div class="background-container">
        <?= renderBackground($activeBgKey) ?>
    </div>

    <div class="ui-container">
        
        <div class="col-left">
            <div class="glass-box">
                <h2><div id="currentdd"></div></h2>
                <h4><div id="currenttt"></div></h4>
                <h2><div id="week"></div></h2>
            </div>
            <div class="glass-box">
                <?php if ($displayName): ?>
                    <p style="opacity:0.5;font-size:0.75rem;">Signed in as</p>
                    <h3 style="margin:0.3rem 0 0 0;"><?= htmlspecialchars($displayName) ?></h3>
                <?php else: ?>
                    <h2><?= htmlspecialchars($schoolName) ?></h2>
                    <p>Welcome</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-mid">
            <!-- Search Bar -->
            <div class="search-bar-container">
                <div class="search-bar glass-box">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search links or the web..." autocomplete="off" autofocus>
                    <span class="search-hint" id="searchHint"></span>
                </div>
                <div class="search-results" id="searchResults"></div>
            </div>

            <div class="links-wrapper">
                <div class="links-grid" id="linksGrid">
                    <?php foreach ($links as $link): 
                        $parsedUrl = parse_url($link['url']);
                        $domain = $parsedUrl['host'] ?? '';
                        $hasCustomIcon = !empty($link['icon_path']);
                        if ($hasCustomIcon) {
                            $iconSrc = htmlspecialchars($link['icon_path']);
                        } else {
                            $iconSrc = "https://www.google.com/s2/favicons?domain=" . urlencode($domain) . "&sz=128";
                        }
                    ?>
                    <a class="link-card glass-box"
                       href="<?= htmlspecialchars($link['url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       data-name="<?= htmlspecialchars(strtolower($link['name'])) ?>">
                        <img class="link-icon" 
                             src="<?= $iconSrc ?>" 
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
                <p>Manage links & theme</p>
            </a>
        </div>

    </div>

    <script src="js/load_time&weather.js"></script>
    <script>
    (function() {
        const searchInput = document.getElementById('searchInput');
        const searchHint = document.getElementById('searchHint');
        const linksGrid = document.getElementById('linksGrid');
        const allCards = Array.from(linksGrid.querySelectorAll('.link-card'));

        searchInput.focus();

        searchInput.addEventListener('input', function() {
            filterLinks(this.value.trim().toLowerCase());
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (!query) return;
                const visibleCards = allCards.filter(card => !card.classList.contains('search-hidden'));
                if (visibleCards.length === 1) {
                    visibleCards[0].click();
                } else if (visibleCards.length === 0 || (visibleCards.length === allCards.length && query)) {
                    window.open('https://www.google.com/search?q=' + encodeURIComponent(query), '_blank');
                } else if (visibleCards.length > 0 && visibleCards.length < allCards.length) {
                    visibleCards[0].click();
                } else {
                    window.open('https://www.google.com/search?q=' + encodeURIComponent(query), '_blank');
                }
            }
            if (e.key === 'Escape') {
                this.value = '';
                filterLinks('');
                this.blur();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                searchInput.focus();
            }
        });

        function filterLinks(query) {
            if (!query) {
                allCards.forEach(card => { card.classList.remove('search-hidden'); card.style.display = ''; });
                searchHint.textContent = '';
                searchHint.classList.remove('visible');
                return;
            }
            let matchCount = 0;
            allCards.forEach(card => {
                const name = card.dataset.name || '';
                if (name.includes(query)) {
                    card.classList.remove('search-hidden'); card.style.display = ''; matchCount++;
                } else {
                    card.classList.add('search-hidden'); card.style.display = 'none';
                }
            });
            searchHint.textContent = matchCount === 0 ? 'Enter ↵ to search the web' : matchCount + ' match' + (matchCount !== 1 ? 'es' : '');
            searchHint.classList.add('visible');
        }
    })();
    </script>
</body>
</html>