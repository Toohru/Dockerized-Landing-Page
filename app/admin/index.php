<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/theme.php';
require __DIR__ . '/../includes/backgrounds.php';

$pdo = getDb('admin');
$message = '';
$messageType = '';
$editLink = null;

// Icon upload directory
$iconDir = __DIR__ . '/../uploads/icons/';
if (!is_dir($iconDir)) {
    mkdir($iconDir, 0755, true);
}

/**
 * Handle icon upload. Returns the relative path or null.
 */
function handleIconUpload(array $file, string $iconDir): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) {
        return null;
    }

    // Validate file type
    $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed, true)) {
        return null;
    }

    // Limit size to 2MB
    if ($file['size'] > 2 * 1024 * 1024) {
        return null;
    }

    $ext = match ($mime) {
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
        'image/webp' => 'webp',
        'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
        default => 'png',
    };

    $filename = uniqid('icon_', true) . '.' . $ext;
    $dest = $iconDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/uploads/icons/' . $filename;
    }

    return null;
}

// --- Handle form actions ---

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'delete':
            // Delete the icon file if it exists
            $stmt = $pdo->prepare("SELECT icon_path FROM def_links WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $row = $stmt->fetch();
            if ($row && $row['icon_path']) {
                $fullPath = __DIR__ . '/..' . $row['icon_path'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $stmt = $pdo->prepare("DELETE FROM def_links WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Link deleted.';
            $messageType = 'success';
            break;

        case 'add':
            $name = trim($_POST['name'] ?? '');
            $url  = trim($_POST['url'] ?? '');
            if ($name !== '' && $url !== '') {
                $iconPath = null;
                if (isset($_FILES['icon'])) {
                    $iconPath = handleIconUpload($_FILES['icon'], $iconDir);
                }
                $stmt = $pdo->prepare("INSERT INTO def_links (name, url, icon_path) VALUES (?, ?, ?)");
                $stmt->execute([$name, $url, $iconPath]);
                $message = 'Link added.';
                $messageType = 'success';
            } else {
                $message = 'Name and URL are required.';
                $messageType = 'error';
            }
            break;

        case 'update':
            $name = trim($_POST['name'] ?? '');
            $url  = trim($_POST['url'] ?? '');
            $id   = $_POST['id'] ?? '';
            if ($name !== '' && $url !== '' && $id !== '') {
                $iconPath = null;
                $removeIcon = isset($_POST['remove_icon']) && $_POST['remove_icon'] === '1';

                if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
                    $iconPath = handleIconUpload($_FILES['icon'], $iconDir);
                }

                if ($iconPath || $removeIcon) {
                    // Delete old icon file
                    $stmt = $pdo->prepare("SELECT icon_path FROM def_links WHERE id = ?");
                    $stmt->execute([$id]);
                    $old = $stmt->fetch();
                    if ($old && $old['icon_path']) {
                        $fullPath = __DIR__ . '/..' . $old['icon_path'];
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                    $stmt = $pdo->prepare("UPDATE def_links SET name = ?, url = ?, icon_path = ? WHERE id = ?");
                    $stmt->execute([$name, $url, $removeIcon ? null : $iconPath, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE def_links SET name = ?, url = ? WHERE id = ?");
                    $stmt->execute([$name, $url, $id]);
                }
                $message = 'Link updated.';
                $messageType = 'success';
            } else {
                $message = 'All fields are required.';
                $messageType = 'error';
            }
            break;

        case 'set_theme':
            $themeId = (int)($_POST['theme_id'] ?? 0);
            if ($themeId > 0) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_theme'");
                $stmt->execute([$themeId]);
                $message = 'Theme updated.';
                $messageType = 'success';
            }
            break;

        case 'set_background':
            $bgKey = $_POST['bg_key'] ?? '';
            $allBgs = getBackgrounds();
            if (isset($allBgs[$bgKey])) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_background'");
                $stmt->execute([$bgKey]);
                $message = 'Background updated.';
                $messageType = 'success';
            }
            break;

        case 'set_custom_theme':
            $c1 = $_POST['colour1'] ?? '';
            $c2 = $_POST['colour2'] ?? '';
            $c3 = $_POST['colour3'] ?? '';
            $pt = $_POST['primary_text'] ?? '';
            $st = $_POST['secondary_text'] ?? '';
            $hl = $_POST['highlight'] ?? '';
            if ($c1 && $c2 && $c3 && $pt && $st && $hl) {
                $stmt = $pdo->query("SELECT id FROM themes WHERE name = 'Custom'");
                $existing = $stmt->fetch();
                $binC1 = hex2bin(ltrim($c1, '#'));
                $binC2 = hex2bin(ltrim($c2, '#'));
                $binC3 = hex2bin(ltrim($c3, '#'));
                $binPt = hex2bin(ltrim($pt, '#'));
                $binSt = hex2bin(ltrim($st, '#'));
                $binHl = hex2bin(ltrim($hl, '#'));
                if ($existing) {
                    $stmt = $pdo->prepare("UPDATE themes SET colour1=?, colour2=?, colour3=?, primary_text_colour=?, secondary_text_colour=?, highlight_colour=? WHERE id=?");
                    $stmt->execute([$binC1, $binC2, $binC3, $binPt, $binSt, $binHl, $existing['id']]);
                    $customId = $existing['id'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO themes (name, colour1, colour2, colour3, primary_text_colour, secondary_text_colour, highlight_colour) VALUES ('Custom',?,?,?,?,?,?)");
                    $stmt->execute([$binC1, $binC2, $binC3, $binPt, $binSt, $binHl]);
                    $customId = $pdo->lastInsertId();
                }
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_theme'");
                $stmt->execute([$customId]);
                $message = 'Custom theme saved & applied.';
                $messageType = 'success';
            }
            break;
    }
}

// Check if editing a link
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM def_links WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editLink = $stmt->fetch();
}

// Fetch data
$links = $pdo->query("SELECT * FROM def_links ORDER BY name")->fetchAll();
$themes = $pdo->query("SELECT * FROM themes ORDER BY name")->fetchAll();
$activeTheme = loadActiveTheme($pdo);
$activeThemeId = $activeTheme ? $activeTheme['id'] : 1;
$activeBgKey = loadActiveBackground($pdo);
$allBackgrounds = getBackgrounds();

$curC1 = $activeTheme ? binToHex($activeTheme['colour1']) : '#0F172A';
$curC2 = $activeTheme ? binToHex($activeTheme['colour2']) : '#111827';
$curC3 = $activeTheme ? binToHex($activeTheme['colour3']) : '#1E293B';
$curPt = $activeTheme ? binToHex($activeTheme['primary_text_colour']) : '#E5E7EB';
$curSt = $activeTheme ? binToHex($activeTheme['secondary_text_colour']) : '#9CA3AF';
$curHl = $activeTheme ? binToHex($activeTheme['highlight_colour']) : '#22D3EE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Settings</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="admin_styles.css">
    <?php if ($activeTheme): ?>
        <?= themeCSS($activeTheme) ?>
    <?php endif; ?>
</head>
<body>

    <div class="background-container">
        <?= renderBackground($activeBgKey) ?>
    </div>

    <!-- ===== BENTO GRID ===== -->
    <div class="admin-bento">

        <!-- TOP BAR -->
        <div class="admin-topbar">
            <h1>Settings</h1>
            <?php if ($message): ?>
                <div class="toast <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <a href="/" class="home-icon" title="Back to Home">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </a>
        </div>

        <!-- LEFT COLUMN -->
        <div class="admin-col-left">

            <!-- Add / Edit Link -->
            <div class="glass-box admin-box admin-box-form">
                <h3><?= $editLink ? 'Edit Link' : 'Add New Link' ?></h3>
                <form method="POST" class="admin-form" action="/admin/" enctype="multipart/form-data">
                    <?php if ($editLink): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editLink['id'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="add">
                    <?php endif; ?>
                    <div class="form-row">
                        <input type="text" name="name" placeholder="Name (e.g. Google)"
                               value="<?= htmlspecialchars($editLink['name'] ?? '') ?>" required>
                        <input type="url" name="url" placeholder="URL (e.g. https://google.com)"
                               value="<?= htmlspecialchars($editLink['url'] ?? '') ?>" required>
                    </div>
                    <div class="form-row icon-upload-row">
                        <label class="icon-upload-label">
                            <span class="icon-upload-text">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <span id="iconFileName">Custom icon (optional)</span>
                            </span>
                            <input type="file" name="icon" accept="image/*" style="display:none"
                                   onchange="document.getElementById('iconFileName').textContent = this.files[0] ? this.files[0].name : 'Custom icon (optional)';">
                        </label>
                        <?php if ($editLink && $editLink['icon_path']): ?>
                            <div class="current-icon-preview">
                                <img src="<?= htmlspecialchars($editLink['icon_path']) ?>" alt="Current icon" width="28" height="28">
                                <label class="remove-icon-toggle">
                                    <input type="checkbox" name="remove_icon" value="1">
                                    <span>Remove</span>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?= $editLink ? 'Save Changes' : 'Add Link' ?>
                        </button>
                        <?php if ($editLink): ?>
                            <a href="/admin/" class="btn btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Current Links — internal scroll -->
            <div class="glass-box admin-box admin-box-links">
                <h3>Current Links</h3>
                <div class="links-scroll">
                    <?php if (empty($links)): ?>
                        <div class="empty-state"><p>No links yet. Add one above.</p></div>
                    <?php else: ?>
                        <table class="links-table">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($links as $link):
                                    $parsedUrl = parse_url($link['url']);
                                    $domain = $parsedUrl['host'] ?? '';
                                ?>
                                    <tr class="link-row">
                                        <td class="icon-cell">
                                            <?php if ($link['icon_path']): ?>
                                                <img src="<?= htmlspecialchars($link['icon_path']) ?>" alt="" width="24" height="24" class="table-icon">
                                                <span class="icon-badge custom" title="Custom icon">✦</span>
                                            <?php else: ?>
                                                <img src="https://www.google.com/s2/favicons?domain=<?= urlencode($domain) ?>&sz=64" alt="" width="24" height="24" class="table-icon">
                                                <span class="icon-badge auto" title="Auto-fetched">⟳</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($link['name']) ?></td>
                                        <td><span class="link-url"><?= htmlspecialchars($link['url']) ?></span></td>
                                        <td>
                                            <div class="row-actions">
                                                <a href="/admin/?edit=<?= $link['id'] ?>" class="btn btn-sm">Edit</a>
                                                <form method="POST" action="/admin/"
                                                      onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($link['name'])) ?>?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $link['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="admin-col-right">

            <!-- Theme -->
            <div class="glass-box admin-box admin-box-theme">
                <div class="box-header">
                    <h3>Theme</h3>
                    <button type="button" class="btn btn-sm" onclick="document.getElementById('customModal').classList.add('open')">
                        Custom
                    </button>
                </div>
                <div class="picker-scroll">
                    <div class="theme-grid">
                        <?php foreach ($themes as $theme):
                            $c1 = binToHex($theme['colour1']);
                            $c2 = binToHex($theme['colour2']);
                            $c3 = binToHex($theme['colour3']);
                            $hl = binToHex($theme['highlight_colour']);
                            $pt = binToHex($theme['primary_text_colour']);
                            $isActive = ($theme['id'] == $activeThemeId);
                        ?>
                        <form method="POST" action="/admin/" class="theme-card-form">
                            <input type="hidden" name="action" value="set_theme">
                            <input type="hidden" name="theme_id" value="<?= $theme['id'] ?>">
                            <button type="submit"
                                    class="theme-card <?= $isActive ? 'theme-active' : '' ?>"
                                    title="<?= htmlspecialchars($theme['name']) ?>">
                                <div class="theme-swatches">
                                    <span class="swatch" style="background:<?= $c1 ?>;"></span>
                                    <span class="swatch" style="background:<?= $c2 ?>;"></span>
                                    <span class="swatch" style="background:<?= $c3 ?>;"></span>
                                    <span class="swatch swatch-accent" style="background:<?= $hl ?>;"></span>
                                </div>
                                <span class="theme-label" style="color:<?= $pt ?>; background:<?= $c1 ?>;">
                                    <?= htmlspecialchars($theme['name']) ?>
                                </span>
                                <?php if ($isActive): ?>
                                    <span class="theme-badge">Active</span>
                                <?php endif; ?>
                            </button>
                        </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Background Animation -->
            <div class="glass-box admin-box admin-box-bg">
                <h3>Background</h3>
                <div class="picker-scroll">
                    <div class="bg-grid">
                        <?php foreach ($allBackgrounds as $key => $bg):
                            $isActive = ($key === $activeBgKey);
                        ?>
                        <form method="POST" action="/admin/" class="bg-card-form">
                            <input type="hidden" name="action" value="set_background">
                            <input type="hidden" name="bg_key" value="<?= htmlspecialchars($key) ?>">
                            <button type="submit"
                                    class="bg-card <?= $isActive ? 'bg-active' : '' ?>"
                                    title="<?= htmlspecialchars($bg['name']) ?>">
                                <div class="bg-preview">
                                    <span class="bg-icon"><?= $bg['icon'] ?></span>
                                </div>
                                <span class="bg-label"><?= htmlspecialchars($bg['name']) ?></span>
                                <?php if ($isActive): ?>
                                    <span class="bg-badge">Active</span>
                                <?php endif; ?>
                            </button>
                        </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ===== CUSTOM COLOUR MODAL ===== -->
    <div class="modal-overlay" id="customModal">
        <div class="modal glass-box">
            <div class="modal-header">
                <h3>Custom Colours</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('customModal').classList.remove('open')">&times;</button>
            </div>
            <form method="POST" action="/admin/" class="modal-form">
                <input type="hidden" name="action" value="set_custom_theme">

                <div class="modal-preview" id="modalPreview">
                    <div class="preview-blob preview-blob-1"></div>
                    <div class="preview-blob preview-blob-2"></div>
                    <div class="preview-glass">
                        <span class="preview-primary">Primary Text</span>
                        <span class="preview-secondary">Secondary Text</span>
                        <span class="preview-highlight">Highlight</span>
                    </div>
                </div>

                <div class="colour-fields">
                    <label class="colour-field">
                        <span>Background</span>
                        <input type="color" name="colour1" value="<?= $curC1 ?>" oninput="updatePreview()">
                    </label>
                    <label class="colour-field">
                        <span>Foreground</span>
                        <input type="color" name="colour2" value="<?= $curC2 ?>" oninput="updatePreview()">
                    </label>
                    <label class="colour-field">
                        <span>Accent</span>
                        <input type="color" name="colour3" value="<?= $curC3 ?>" oninput="updatePreview()">
                    </label>
                    <label class="colour-field">
                        <span>Primary Text</span>
                        <input type="color" name="primary_text" value="<?= $curPt ?>" oninput="updatePreview()">
                    </label>
                    <label class="colour-field">
                        <span>Secondary Text</span>
                        <input type="color" name="secondary_text" value="<?= $curSt ?>" oninput="updatePreview()">
                    </label>
                    <label class="colour-field">
                        <span>Highlight</span>
                        <input type="color" name="highlight" value="<?= $curHl ?>" oninput="updatePreview()">
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn" onclick="document.getElementById('customModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save & Apply</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function updatePreview() {
        const f = document.querySelector('.modal-form');
        const p = document.getElementById('modalPreview');
        const c1 = f.colour1.value;
        const c2 = f.colour2.value;
        const c3 = f.colour3.value;
        const pt = f.primary_text.value;
        const st = f.secondary_text.value;
        const hl = f.highlight.value;

        p.style.background = c1;
        p.querySelector('.preview-blob-1').style.background = c2;
        p.querySelector('.preview-blob-2').style.background = c3;
        p.querySelector('.preview-glass').style.borderColor = pt + '30';
        p.querySelector('.preview-glass').style.background = pt + '15';
        p.querySelector('.preview-primary').style.color = pt;
        p.querySelector('.preview-secondary').style.color = st;
        p.querySelector('.preview-highlight').style.color = hl;
    }
    document.addEventListener('DOMContentLoaded', updatePreview);
    </script>

</body>
</html>