<?php
/**
 * Setup Wizard — First-run configuration.
 *
 * Steps:
 *   1. School Info — name
 *   2. Authentication — AD/LDAP connection or none
 *   3. Default Links — populate the site-wide link grid
 *   4. Theme & Background — pick defaults
 *   5. Done — mark setup complete
 */

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/theme.php';
require __DIR__ . '/../includes/backgrounds.php';

$pdo = getDb('admin');

// If already set up, redirect to home
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'setup_complete'");
$row = $stmt->fetch();
if ($row && $row['setting_value'] === '1') {
    header('Location: /');
    exit;
}

$message = '';
$messageType = '';

// Icon upload directory
$iconDir = __DIR__ . '/../uploads/icons/';
if (!is_dir($iconDir)) {
    mkdir($iconDir, 0755, true);
}

function handleSetupIconUpload(array $file, string $iconDir): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) return null;
    $allowed = ['image/png','image/jpeg','image/gif','image/svg+xml','image/webp','image/x-icon','image/vnd.microsoft.icon'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true) || $file['size'] > 2*1024*1024) return null;
    $ext = match($mime) {
        'image/png'=>'png','image/jpeg'=>'jpg','image/gif'=>'gif',
        'image/svg+xml'=>'svg','image/webp'=>'webp',default=>'png'
    };
    $filename = uniqid('icon_',true).'.'.$ext;
    if (move_uploaded_file($file['tmp_name'], $iconDir.$filename)) {
        return '/uploads/icons/'.$filename;
    }
    return null;
}

// Determine current step
$step = (int)($_GET['step'] ?? 1);
if ($step < 1) $step = 1;
if ($step > 5) $step = 5;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postStep = (int)($_POST['step'] ?? 0);

    switch ($postStep) {
        case 1: // School Info
            $schoolName = trim($_POST['school_name'] ?? '');
            if ($schoolName !== '') {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'school_name'");
                $stmt->execute([$schoolName]);
                header('Location: /setup/?step=2');
                exit;
            }
            $message = 'Please enter a school name.';
            $messageType = 'error';
            break;

        case 2: // Authentication
            $authMode = $_POST['auth_mode'] ?? 'none';
            $validModes = ['none', 'remote_user', 'header'];
            if (!in_array($authMode, $validModes)) $authMode = 'none';

            $fields = [
                'auth_mode' => $authMode,
                'ldap_host' => trim($_POST['ldap_host'] ?? ''),
                'ldap_port' => trim($_POST['ldap_port'] ?? '389'),
                'ldap_base_dn' => trim($_POST['ldap_base_dn'] ?? ''),
                'ldap_bind_dn' => trim($_POST['ldap_bind_dn'] ?? ''),
                'ldap_bind_password' => $_POST['ldap_bind_password'] ?? '',
                'ldap_user_filter' => trim($_POST['ldap_user_filter'] ?? '(sAMAccountName={username})'),
                'ldap_domain' => trim($_POST['ldap_domain'] ?? ''),
            ];

            foreach ($fields as $key => $val) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$val, $key]);
            }

            // Test LDAP connection if configured
            if ($authMode === 'remote_user' && $fields['ldap_host'] !== '') {
                $testResult = testLdapConnection($fields);
                if ($testResult !== true) {
                    $message = 'Settings saved but LDAP test failed: ' . $testResult . ' — You can fix this later in Admin settings.';
                    $messageType = 'error';
                    // Still proceed
                }
            }

            header('Location: /setup/?step=3');
            exit;

        case 3: // Default Links
            $action = $_POST['action'] ?? '';
            if ($action === 'add_link') {
                $name = trim($_POST['name'] ?? '');
                $url = trim($_POST['url'] ?? '');
                if ($name !== '' && $url !== '') {
                    $iconPath = null;
                    if (isset($_FILES['icon'])) {
                        $iconPath = handleSetupIconUpload($_FILES['icon'], $iconDir);
                    }
                    $maxOrder = $pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM def_links")->fetchColumn();
                    $stmt = $pdo->prepare("INSERT INTO def_links (name, url, icon_path, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $url, $iconPath, $maxOrder]);
                    $message = 'Link added.';
                    $messageType = 'success';
                }
                header('Location: /setup/?step=3');
                exit;
            } elseif ($action === 'delete_link') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("SELECT icon_path FROM def_links WHERE id = ?");
                    $stmt->execute([$id]);
                    $r = $stmt->fetch();
                    if ($r && $r['icon_path']) {
                        $fp = __DIR__.'/..'.$r['icon_path'];
                        if (file_exists($fp)) unlink($fp);
                    }
                    $stmt = $pdo->prepare("DELETE FROM def_links WHERE id = ?");
                    $stmt->execute([$id]);
                }
                header('Location: /setup/?step=3');
                exit;
            } elseif ($action === 'next') {
                header('Location: /setup/?step=4');
                exit;
            }
            break;

        case 4: // Theme & Background
            $themeId = (int)($_POST['theme_id'] ?? 1);
            $bgKey = $_POST['bg_key'] ?? 'blobs';

            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_theme'");
            $stmt->execute([$themeId]);
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_background'");
            $stmt->execute([$bgKey]);

            header('Location: /setup/?step=5');
            exit;

        case 5: // Finish
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = '1' WHERE setting_key = 'setup_complete'");
            $stmt->execute();
            header('Location: /');
            exit;
    }
}

function testLdapConnection(array $cfg): string|true
{
    if (!function_exists('ldap_connect')) {
        return 'PHP LDAP extension not installed.';
    }
    $conn = @ldap_connect($cfg['ldap_host'], (int)$cfg['ldap_port']);
    if (!$conn) return 'Could not connect to ' . $cfg['ldap_host'] . ':' . $cfg['ldap_port'];
    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
    if ($cfg['ldap_bind_dn'] !== '') {
        $bind = @ldap_bind($conn, $cfg['ldap_bind_dn'], $cfg['ldap_bind_password']);
        if (!$bind) {
            $err = ldap_error($conn);
            ldap_close($conn);
            return 'Bind failed: ' . $err;
        }
    }
    ldap_close($conn);
    return true;
}

// Fetch current data for each step
$settings = [];
$stmtAll = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($r = $stmtAll->fetch()) {
    $settings[$r['setting_key']] = $r['setting_value'];
}
$links = $pdo->query("SELECT * FROM def_links ORDER BY sort_order, name")->fetchAll();
$themes = $pdo->query("SELECT * FROM themes ORDER BY name")->fetchAll();
$allBackgrounds = getBackgrounds();

// Load a default theme for styling the wizard
$activeTheme = loadActiveTheme($pdo);
$activeBgKey = loadActiveBackground($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/setup/setup_styles.css">
    <?php if ($activeTheme): ?>
        <?= themeCSS($activeTheme) ?>
    <?php endif; ?>
</head>
<body>

    <div class="background-container">
        <?= renderBackground($activeBgKey) ?>
    </div>

    <div class="setup-container">
        <div class="setup-card glass-box">

            <!-- Progress -->
            <div class="setup-progress">
                <?php
                $stepLabels = ['School', 'Auth', 'Links', 'Theme', 'Done'];
                foreach ($stepLabels as $i => $label):
                    $num = $i + 1;
                    $cls = $num < $step ? 'done' : ($num === $step ? 'active' : '');
                ?>
                    <div class="progress-step <?= $cls ?>">
                        <div class="step-dot"><?= $num < $step ? '✓' : $num ?></div>
                        <span class="step-label"><?= $label ?></span>
                    </div>
                    <?php if ($num < 5): ?>
                        <div class="step-line <?= $num < $step ? 'done' : '' ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($message): ?>
                <div class="setup-toast <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- ===== STEP 1: School Info ===== -->
            <?php if ($step === 1): ?>
                <div class="setup-step">
                    <h2>Welcome! Let's set up your landing page.</h2>
                    <p class="setup-desc">First, tell us about your school.</p>
                    <form method="POST" action="/setup/?step=1">
                        <input type="hidden" name="step" value="1">
                        <div class="form-group">
                            <label class="form-label">School Name</label>
                            <input type="text" name="school_name" class="form-input"
                                   value="<?= htmlspecialchars($settings['school_name'] ?? '') ?>"
                                   placeholder="e.g. Coodanup College" required autofocus>
                        </div>
                        <div class="setup-actions">
                            <span></span>
                            <button type="submit" class="btn btn-primary">Next →</button>
                        </div>
                    </form>
                </div>

            <!-- ===== STEP 2: Authentication ===== -->
            <?php elseif ($step === 2): ?>
                <div class="setup-step">
                    <h2>User Authentication</h2>
                    <p class="setup-desc">How should we identify users? This enables per-user link customisation.</p>
                    <form method="POST" action="/setup/?step=2">
                        <input type="hidden" name="step" value="2">

                        <div class="form-group">
                            <label class="form-label">Authentication Mode</label>
                            <div class="auth-options">
                                <?php $curAuth = $settings['auth_mode'] ?? 'none'; ?>
                                <label class="auth-option <?= $curAuth==='remote_user'?'selected':'' ?>">
                                    <input type="radio" name="auth_mode" value="remote_user"
                                           <?= $curAuth==='remote_user'?'checked':'' ?>
                                           onchange="toggleLdapFields()">
                                    <div class="auth-option-content">
                                        <strong>Windows Authentication (Recommended for AD schools)</strong>
                                        <span>IIS Windows Authentication identifies users automatically from their Windows login — no passwords, no extra config. Works out of the box on any domain-joined school server. install.ps1 enables this automatically.</span>
                                    </div>
                                </label>
                                <label class="auth-option <?= $curAuth==='header'?'selected':'' ?>">
                                    <input type="radio" name="auth_mode" value="header"
                                           <?= $curAuth==='header'?'checked':'' ?>
                                           onchange="toggleLdapFields()">
                                    <div class="auth-option-content">
                                        <strong>Proxy Header (X-Forwarded-User)</strong>
                                        <span>If your network proxy injects a user header. Ensure only trusted proxies can reach this server.</span>
                                    </div>
                                </label>
                                <label class="auth-option <?= $curAuth==='none'?'selected':'' ?>">
                                    <input type="radio" name="auth_mode" value="none"
                                           <?= $curAuth==='none'?'checked':'' ?>
                                           onchange="toggleLdapFields()">
                                    <div class="auth-option-content">
                                        <strong>None (Shared)</strong>
                                        <span>Everyone sees the same links and theme. No user profiles.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="ldapFields" class="ldap-fields" style="<?= $curAuth==='remote_user'?'':'display:none' ?>">
                            <p class="setup-desc" style="margin-bottom:0.8rem;">
                                <strong>Optional:</strong> LDAP details for looking up display names. Not required for REMOTE_USER auth itself, 
                                but allows showing "Welcome, John Smith" instead of just the username.
                            </p>
                            <div class="ldap-grid">
                                <div class="form-group">
                                    <label class="form-label">LDAP/AD Host</label>
                                    <input type="text" name="ldap_host" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_host'] ?? '') ?>"
                                           placeholder="e.g. dc01.school.wa.edu.au">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Port</label>
                                    <input type="text" name="ldap_port" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_port'] ?? '389') ?>"
                                           placeholder="389">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Base DN</label>
                                    <input type="text" name="ldap_base_dn" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_base_dn'] ?? '') ?>"
                                           placeholder="e.g. DC=school,DC=wa,DC=edu,DC=au">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Domain Prefix</label>
                                    <input type="text" name="ldap_domain" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_domain'] ?? '') ?>"
                                           placeholder="e.g. EDUCATION">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Bind DN (service account)</label>
                                    <input type="text" name="ldap_bind_dn" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_bind_dn'] ?? '') ?>"
                                           placeholder="e.g. CN=svc_web,OU=Service,DC=school...">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Bind Password</label>
                                    <input type="password" name="ldap_bind_password" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_bind_password'] ?? '') ?>"
                                           placeholder="••••••••">
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">User Filter</label>
                                    <input type="text" name="ldap_user_filter" class="form-input"
                                           value="<?= htmlspecialchars($settings['ldap_user_filter'] ?? '(sAMAccountName={username})') ?>"
                                           placeholder="(sAMAccountName={username})">
                                </div>
                            </div>
                        </div>

                        <div class="setup-actions">
                            <a href="/setup/?step=1" class="btn">← Back</a>
                            <button type="submit" class="btn btn-primary">Next →</button>
                        </div>
                    </form>
                </div>

            <!-- ===== STEP 3: Default Links ===== -->
            <?php elseif ($step === 3): ?>
                <div class="setup-step">
                    <h2>Default Links</h2>
                    <p class="setup-desc">Add the links that all users will see. They can customise their own later<?= ($settings['auth_mode'] ?? 'none') !== 'none' ? '' : ' (auth is off, so all users share these)' ?>.</p>

                    <!-- Add link form -->
                    <form method="POST" action="/setup/?step=3" enctype="multipart/form-data" class="setup-add-link">
                        <input type="hidden" name="step" value="3">
                        <input type="hidden" name="action" value="add_link">
                        <div class="add-link-row">
                            <input type="text" name="name" class="form-input" placeholder="Name" required>
                            <input type="url" name="url" class="form-input" placeholder="https://..." required>
                            <label class="icon-upload-mini" title="Custom icon (optional)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <input type="file" name="icon" accept="image/*" style="display:none">
                            </label>
                            <button type="submit" class="btn btn-primary btn-sm">Add</button>
                        </div>
                    </form>

                    <!-- Current links list -->
                    <?php if (!empty($links)): ?>
                        <div class="setup-links-list">
                            <?php foreach ($links as $link):
                                $domain = parse_url($link['url'], PHP_URL_HOST) ?? '';
                                $iconSrc = $link['icon_path'] ?: "https://www.google.com/s2/favicons?domain=".urlencode($domain)."&sz=64";
                            ?>
                                <div class="setup-link-row">
                                    <img src="<?= htmlspecialchars($iconSrc) ?>" width="20" height="20" class="setup-link-icon" onerror="this.style.display='none'">
                                    <span class="setup-link-name"><?= htmlspecialchars($link['name']) ?></span>
                                    <span class="setup-link-url"><?= htmlspecialchars(mb_strimwidth($link['url'], 0, 50, '…')) ?></span>
                                    <form method="POST" action="/setup/?step=3" style="margin:0;">
                                        <input type="hidden" name="step" value="3">
                                        <input type="hidden" name="action" value="delete_link">
                                        <input type="hidden" name="id" value="<?= $link['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">✕</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="setup-empty">No links yet — add some above, or skip and add them later via Admin.</div>
                    <?php endif; ?>

                    <form method="POST" action="/setup/?step=3">
                        <input type="hidden" name="step" value="3">
                        <input type="hidden" name="action" value="next">
                        <div class="setup-actions">
                            <a href="/setup/?step=2" class="btn">← Back</a>
                            <button type="submit" class="btn btn-primary">Next →</button>
                        </div>
                    </form>
                </div>

            <!-- ===== STEP 4: Theme & Background ===== -->
            <?php elseif ($step === 4): ?>
                <div class="setup-step">
                    <h2>Theme & Background</h2>
                    <p class="setup-desc">Pick a default look. Users can override this with their own preferences.</p>
                    <form method="POST" action="/setup/?step=4">
                        <input type="hidden" name="step" value="4">

                        <div class="form-group">
                            <label class="form-label">Theme</label>
                            <div class="theme-grid-setup">
                                <?php $curTheme = (int)($settings['active_theme'] ?? 1);
                                foreach ($themes as $t):
                                    $c1 = binToHex($t['colour1']); $c2 = binToHex($t['colour2']);
                                    $c3 = binToHex($t['colour3']); $hl = binToHex($t['highlight_colour']);
                                    $pt = binToHex($t['primary_text_colour']);
                                    $st = binToHex($t['secondary_text_colour']);
                                    $hlRgb = hexToRgb($hl); $ptRgb = hexToRgb($pt);
                                ?>
                                    <label class="theme-card-radio <?= $t['id']==$curTheme?'selected':'' ?>"
                                           data-c1="<?=$c1?>" data-c2="<?=$c2?>" data-c3="<?=$c3?>"
                                           data-hl="<?=$hl?>" data-pt="<?=$pt?>" data-st="<?=$st?>"
                                           data-hl-rgb="<?=$hlRgb?>" data-pt-rgb="<?=$ptRgb?>">
                                        <input type="radio" name="theme_id" value="<?= $t['id'] ?>" <?= $t['id']==$curTheme?'checked':'' ?>>
                                        <div class="theme-swatches">
                                            <span style="background:<?=$c1?>"></span>
                                            <span style="background:<?=$c2?>"></span>
                                            <span style="background:<?=$c3?>"></span>
                                            <span style="background:<?=$hl?>"></span>
                                        </div>
                                        <div class="theme-card-name" style="color:<?=$pt?>;background:<?=$c1?>"><?= htmlspecialchars($t['name']) ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Background Animation</label>
                            <div class="bg-grid-setup">
                                <?php $curBg = $settings['active_background'] ?? 'none';
                                foreach ($allBackgrounds as $key => $bg): ?>
                                    <label class="bg-card-radio <?= $key===$curBg?'selected':'' ?>" data-bg-key="<?= htmlspecialchars($key) ?>">
                                        <input type="radio" name="bg_key" value="<?= htmlspecialchars($key) ?>" <?= $key===$curBg?'checked':'' ?>>
                                        <span class="bg-card-icon"><?= $bg['icon'] ?></span>
                                        <span class="bg-card-name"><?= htmlspecialchars($bg['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="setup-actions">
                            <a href="/setup/?step=3" class="btn">← Back</a>
                            <button type="submit" class="btn btn-primary">Next →</button>
                        </div>
                    </form>
                </div>

            <!-- ===== STEP 5: Done ===== -->
            <?php elseif ($step === 5): ?>
                <div class="setup-step setup-done">
                    <div class="done-icon">✓</div>
                    <h2>All Set!</h2>
                    <p class="setup-desc">
                        <strong><?= htmlspecialchars($settings['school_name'] ?? 'Your school') ?></strong>'s landing page is ready to go.
                        <?php if (($settings['auth_mode'] ?? 'none') !== 'none'): ?>
                            <br>Users will be automatically identified when they visit.
                        <?php endif; ?>
                    </p>
                    <p class="setup-desc" style="opacity:0.5;">You can change any of these settings later from the Admin panel.</p>
                    <form method="POST" action="/setup/?step=5">
                        <input type="hidden" name="step" value="5">
                        <div class="setup-actions" style="justify-content:center;">
                            <button type="submit" class="btn btn-primary" style="font-size:1rem;padding:0.7rem 2rem;">Launch Site →</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Background data for live preview -->
    <script>
    const bgData = <?php
        $bgJs = [];
        foreach ($allBackgrounds as $key => $bg) {
            $bgJs[$key] = [
                'html' => $bg['html'],
                'css'  => $bg['css'],
            ];
        }
        echo json_encode($bgJs, JSON_UNESCAPED_SLASHES);
    ?>;
    </script>

    <script>
    function toggleLdapFields() {
        const mode = document.querySelector('input[name="auth_mode"]:checked')?.value;
        const ldap = document.getElementById('ldapFields');
        if (ldap) ldap.style.display = mode === 'remote_user' ? '' : 'none';

        document.querySelectorAll('.auth-option').forEach(el => {
            el.classList.toggle('selected', el.querySelector('input').checked);
        });
    }

    // ---- Live Theme Preview ----
    function applyThemeLive(card) {
        const root = document.documentElement;
        root.style.setProperty('--bground-colour', card.dataset.c1);
        root.style.setProperty('--fground-colour', card.dataset.c2);
        root.style.setProperty('--cground-colour', card.dataset.c3);
        root.style.setProperty('--highlights-colour', card.dataset.hl);
        root.style.setProperty('--primtext-colour', card.dataset.pt);
        root.style.setProperty('--sectext-colour', card.dataset.st);
        root.style.setProperty('--highlight-rgb', card.dataset.hlRgb);
        root.style.setProperty('--glass-bg', 'rgba(' + card.dataset.ptRgb + ', 0.08)');
        root.style.setProperty('--glass-border', 'rgba(' + card.dataset.ptRgb + ', 0.18)');
    }

    // ---- Live Background Preview ----
    function applyBackgroundLive(key) {
        const container = document.querySelector('.background-container');
        if (!container || !bgData[key]) return;

        // Remove old dynamic bg styles
        const oldStyle = document.getElementById('dynamic-bg-style');
        if (oldStyle) oldStyle.remove();

        const data = bgData[key];
        container.innerHTML = data.html;

        // The css field contains <style>...</style> tags, inject them
        if (data.css) {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = data.css;
            const styleEl = wrapper.querySelector('style');
            if (styleEl) {
                styleEl.id = 'dynamic-bg-style';
                document.head.appendChild(styleEl);
            }
        }
    }

    // Theme radio highlighting + live preview
    document.querySelectorAll('.theme-card-radio input').forEach(radio => {
        radio.addEventListener('change', () => {
            const group = radio.closest('.theme-grid-setup');
            group.querySelectorAll('.theme-card-radio').forEach(c => c.classList.remove('selected'));
            const card = radio.closest('.theme-card-radio');
            card.classList.add('selected');
            applyThemeLive(card);
        });
    });

    // BG radio highlighting + live preview
    document.querySelectorAll('.bg-card-radio input').forEach(radio => {
        radio.addEventListener('change', () => {
            const group = radio.closest('.bg-grid-setup');
            group.querySelectorAll('.bg-card-radio').forEach(c => c.classList.remove('selected'));
            radio.closest('.bg-card-radio').classList.add('selected');
            applyBackgroundLive(radio.value);
        });
    });
    </script>

</body>
</html>