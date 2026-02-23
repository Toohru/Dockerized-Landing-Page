<?php
/**
 * auth.php — Identifies the current user.
 *
 * Reads auth_mode from settings:
 *   'remote_user' — trusts IIS Windows Authentication (AUTH_USER / LOGON_USER)
 *                   or Apache Kerberos/NTLM (REMOTE_USER) as fallback
 *   'header'      — trusts a reverse-proxy header (X-Forwarded-User)
 *   'none'        — no auth, all users share a single "default" profile
 *
 * Returns a normalised lowercase username string, or null if unidentified.
 */

function getCurrentUsername(PDO $pdo): ?string
{
    static $cache = false;
    if ($cache !== false) return $cache;

    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");

    $stmt->execute(['auth_mode']);
    $mode = ($stmt->fetch())['setting_value'] ?? 'none';

    $username = null;

    switch ($mode) {
        case 'remote_user':
            // IIS Windows Authentication sets AUTH_USER and LOGON_USER.
            // Apache mod_auth_kerb / mod_auth_ntlm sets REMOTE_USER.
            // Check all variants so both web servers work without config changes.
            $raw = $_SERVER['AUTH_USER']
                ?? $_SERVER['LOGON_USER']
                ?? $_SERVER['REMOTE_USER']
                ?? $_SERVER['REDIRECT_REMOTE_USER']
                ?? '';
            if ($raw !== '') {
                // Strip DOMAIN\ prefix (IIS: "SCHOOL\jsmith" → "jsmith")
                if (str_contains($raw, '\\')) {
                    $raw = substr($raw, strpos($raw, '\\') + 1);
                }
                // Strip @domain suffix (UPN: "jsmith@school.wa.edu.au" → "jsmith")
                if (str_contains($raw, '@')) {
                    $raw = substr($raw, 0, strpos($raw, '@'));
                }
                $username = strtolower(trim($raw));
            }
            break;

        case 'header':
            // Trust a reverse-proxy injected header
            $raw = $_SERVER['HTTP_X_FORWARDED_USER'] ?? $_SERVER['HTTP_X_REMOTE_USER'] ?? '';
            if ($raw !== '') {
                $username = strtolower(trim($raw));
            }
            break;

        case 'none':
        default:
            // No auth — everyone is "default"
            $username = null;
            break;
    }

    $cache = ($username !== '' && $username !== null) ? $username : null;
    return $cache;
}

/**
 * Get or create a user profile. Returns the user_profiles row as an associative array.
 * Returns null if auth is disabled (mode = 'none').
 */
function getOrCreateUserProfile(PDO $pdo, ?string $username): ?array
{
    if ($username === null) return null;

    // Check if exists
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE username = ?");
    $stmt->execute([$username]);
    $profile = $stmt->fetch();

    if ($profile) {
        // Update last_seen
        $stmt = $pdo->prepare("UPDATE user_profiles SET last_seen = NOW() WHERE id = ?");
        $stmt->execute([$profile['id']]);
        return $profile;
    }

    // Create new profile with defaults
    $stmt = $pdo->prepare("INSERT INTO user_profiles (username) VALUES (?)");
    $stmt->execute([$username]);

    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() ?: null;
}

/**
 * Get links for a user. Falls back to def_links if user has no custom links.
 */
function getLinksForUser(PDO $pdo, ?array $userProfile): array
{
    if ($userProfile) {
        // Check if user has custom links
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_links WHERE user_id = ?");
        $stmt->execute([$userProfile['id']]);
        $count = (int)$stmt->fetch()['cnt'];

        if ($count > 0) {
            $stmt = $pdo->prepare("SELECT name, url, icon_path FROM user_links WHERE user_id = ? ORDER BY sort_order, name");
            $stmt->execute([$userProfile['id']]);
            return $stmt->fetchAll();
        }
    }

    // Fall back to default links
    return $pdo->query("SELECT name, url, icon_path FROM def_links ORDER BY sort_order, name")->fetchAll();
}

/**
 * Get the theme for a user. Falls back to site default.
 */
function getThemeForUser(PDO $pdo, ?array $userProfile): ?array
{
    if ($userProfile && $userProfile['theme_id']) {
        $stmt = $pdo->prepare("SELECT * FROM themes WHERE id = ?");
        $stmt->execute([$userProfile['theme_id']]);
        $theme = $stmt->fetch();
        if ($theme) return $theme;
    }

    // Fall back to site default
    return loadActiveTheme($pdo);
}

/**
 * Get the background key for a user. Falls back to site default.
 */
function getBackgroundForUser(PDO $pdo, ?array $userProfile): string
{
    if ($userProfile && $userProfile['background_key']) {
        $bgs = getBackgrounds();
        if (isset($bgs[$userProfile['background_key']])) {
            return $userProfile['background_key'];
        }
    }

    return loadActiveBackground($pdo);
}

/**
 * Check if first-run setup is complete.
 */
function isSetupComplete(PDO $pdo): bool
{
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'setup_complete'");
    $row = $stmt->fetch();
    return $row && $row['setting_value'] === '1';
}
