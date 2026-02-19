<?php
/**
 * theme.php — Loads the active theme from the database.
 * 
 * Requires db.php to be loaded first.
 * Provides:
 *   $activeTheme — associative array of the theme row
 *   themeCSS()   — returns a <style> block with CSS custom properties
 */

function loadActiveTheme(PDO $pdo): ?array
{
    // Get active theme id from settings
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'active_theme'");
    $row = $stmt->fetch();
    $themeId = $row ? (int)$row['setting_value'] : 1;

    // Fetch the theme
    $stmt = $pdo->prepare("SELECT * FROM themes WHERE id = ?");
    $stmt->execute([$themeId]);
    return $stmt->fetch() ?: null;
}

/**
 * Convert a BINARY(3) column to a hex color string like #0F172A
 */
function binToHex(string $bin): string
{
    return '#' . strtoupper(bin2hex($bin));
}

/**
 * Convert hex to an rgba()-friendly "r, g, b" string
 */
function hexToRgb(string $hex): string
{
    $hex = ltrim($hex, '#');
    return implode(', ', [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ]);
}

/**
 * Output a <style> block that overrides the CSS custom properties with the active theme.
 */
function themeCSS(array $theme): string
{
    $c1   = binToHex($theme['colour1']);
    $c2   = binToHex($theme['colour2']);
    $c3   = binToHex($theme['colour3']);
    $pt   = binToHex($theme['primary_text_colour']);
    $st   = binToHex($theme['secondary_text_colour']);
    $hl   = binToHex($theme['highlight_colour']);

    // Compute glass colors from highlight and text
    $hlRgb = hexToRgb($hl);
    $ptRgb = hexToRgb($pt);

    return <<<CSS
<style>
    :root {
        --bground-colour: {$c1};
        --fground-colour: {$c2};
        --cground-colour: {$c3};
        --highlights-colour: {$hl};
        --primtext-colour: {$pt};
        --sectext-colour: {$st};

        --glass-bg: rgba({$ptRgb}, 0.08);
        --glass-border: rgba({$ptRgb}, 0.18);
        --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        --highlight-rgb: {$hlRgb};
    }
</style>
CSS;
}
