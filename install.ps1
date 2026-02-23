#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Installs the School Landing Page on Windows Server with IIS.

.DESCRIPTION
    This script automates the full deployment:
      1. Enables IIS with required features (Windows Auth, CGI, URL Rewrite)
      2. Verifies PHP is installed and configured for IIS FastCGI
      3. Verifies MySQL is installed and running
      4. Initialises the database (schema + seed data)
      5. Copies app files to the web root
      6. Creates/updates the IIS site and configures Windows Authentication
      7. Generates config.php with database credentials

    Run from an elevated PowerShell prompt:
        .\install.ps1

    Re-running is safe — existing DB and config are preserved if they already exist.

.NOTES
    Prerequisites (download before running):
      - PHP 8.2+ for Windows NTS x64: https://windows.php.net/download/
        Extract to C:\PHP  (or update $PhpPath below)
      - MySQL 8.0 Community Server: https://dev.mysql.com/downloads/mysql/
        Install with default options; set a root password you remember
      - IIS URL Rewrite Module 2.1 (free):
        https://www.iis.net/downloads/microsoft/url-rewrite
        (The script will warn if it is missing, but the app still works without it)
#>

# ─── CONFIGURATION — change these for your environment ───────────────────────

# Path where PHP is installed (php-cgi.exe must exist here)
$PhpPath       = 'C:\PHP'

# MySQL connection (root, for one-time DB setup only)
$MySqlExe      = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
$MySqlRoot     = 'root'
# Set your MySQL root password here, or leave blank to be prompted
$MySqlRootPass = ''

# Credentials the app will use (created in MySQL by this script)
$AdminDbUser   = 'LPAdmin'
$AdminDbPass   = 'LP_Admin_' + (-join ((65..90) + (97..122) | Get-Random -Count 12 | ForEach-Object {[char]$_}))

$ViewerDbUser  = 'LPViewer'
$ViewerDbPass  = 'LP_Viewer_' + (-join ((65..90) + (97..122) | Get-Random -Count 12 | ForEach-Object {[char]$_}))

# IIS site settings
$SiteName      = 'LandingPage'
$SitePort      = 80
$WebRoot       = 'C:\inetpub\wwwroot\landing'

# App source (relative to this script)
$AppSource     = Join-Path $PSScriptRoot 'app'
$SqlSource     = Join-Path $PSScriptRoot 'install.sql'

# ─── HELPERS ─────────────────────────────────────────────────────────────────

function Write-Step([string]$msg) {
    Write-Host "`n==> $msg" -ForegroundColor Cyan
}
function Write-OK([string]$msg) {
    Write-Host "    [OK] $msg" -ForegroundColor Green
}
function Write-Warn([string]$msg) {
    Write-Host "    [!!] $msg" -ForegroundColor Yellow
}
function Write-Fail([string]$msg) {
    Write-Host "    [FAIL] $msg" -ForegroundColor Red
    exit 1
}

# ─── 1. IIS FEATURES ─────────────────────────────────────────────────────────

Write-Step 'Enabling IIS features...'

$features = @(
    'IIS-WebServerRole',
    'IIS-WebServer',
    'IIS-CommonHttpFeatures',
    'IIS-DefaultDocument',
    'IIS-StaticContent',
    'IIS-CGI',                    # Required for PHP FastCGI
    'IIS-WindowsAuthentication',  # Required for AD passthrough
    'IIS-ManagementConsole'       # IIS Manager GUI (optional but handy)
)

foreach ($f in $features) {
    $state = (Get-WindowsOptionalFeature -Online -FeatureName $f -ErrorAction SilentlyContinue).State
    if ($state -ne 'Enabled') {
        Enable-WindowsOptionalFeature -Online -FeatureName $f -All -NoRestart -ErrorAction Stop | Out-Null
        Write-OK "Enabled $f"
    } else {
        Write-OK "$f already enabled"
    }
}

# Check URL Rewrite (separate MSI, not a Windows feature)
$rewriteKey = 'HKLM:\SOFTWARE\Microsoft\IIS Extensions\URL Rewrite'
if (Test-Path $rewriteKey) {
    Write-OK 'IIS URL Rewrite module found'
} else {
    Write-Warn 'IIS URL Rewrite module NOT found. Clean URLs will not work.'
    Write-Warn 'Download from: https://www.iis.net/downloads/microsoft/url-rewrite'
    Write-Warn 'The app still works — just navigate to /index.php explicitly.'
}

# ─── 2. PHP CHECK ────────────────────────────────────────────────────────────

Write-Step 'Checking PHP...'

$phpCgi = Join-Path $PhpPath 'php-cgi.exe'
if (-not (Test-Path $phpCgi)) {
    Write-Fail "php-cgi.exe not found at $phpCgi — download PHP NTS x64 from https://windows.php.net/download/ and extract to $PhpPath"
}
Write-OK "Found $phpCgi"

# Ensure php.ini exists (copy from development template if not)
$phpIni = Join-Path $PhpPath 'php.ini'
if (-not (Test-Path $phpIni)) {
    $iniDev = Join-Path $PhpPath 'php.ini-development'
    if (Test-Path $iniDev) {
        Copy-Item $iniDev $phpIni
        Write-OK 'Created php.ini from php.ini-development'
    } else {
        Write-Warn 'No php.ini found — PHP may use defaults. Extensions like PDO_MySQL might not load.'
    }
}

# Ensure required extensions are enabled in php.ini
$requiredExts = @('extension=pdo_mysql', 'extension=mysqli', 'extension=ldap', 'extension=fileinfo')
$iniContent = Get-Content $phpIni -Raw
foreach ($ext in $requiredExts) {
    $extName = $ext -replace 'extension=', ''
    if ($iniContent -match "^;?\s*$([regex]::Escape($ext))") {
        $iniContent = $iniContent -replace "^;?\s*$([regex]::Escape($ext))", $ext, 'Multiline'
        Write-OK "Enabled $extName in php.ini"
    } elseif ($iniContent -notmatch [regex]::Escape($ext)) {
        $iniContent += "`n$ext"
        Write-OK "Added $extName to php.ini"
    } else {
        Write-OK "$extName already enabled"
    }
}
Set-Content $phpIni $iniContent -Encoding UTF8

# Set extension_dir if not already absolute
if ($iniContent -notmatch 'extension_dir\s*=\s*"') {
    $extDir = Join-Path $PhpPath 'ext'
    if (Test-Path $extDir) {
        (Get-Content $phpIni) -replace '^;?\s*extension_dir\s*=.*', "extension_dir = `"$extDir`"" |
            Set-Content $phpIni -Encoding UTF8
        Write-OK "Set extension_dir to $extDir"
    }
}

# Register PHP FastCGI with IIS (idempotent)
Import-Module WebAdministration -ErrorAction Stop
$fcgiSection = Get-WebConfiguration 'system.webServer/fastCgi/application' |
    Where-Object { $_.fullPath -eq $phpCgi }
if (-not $fcgiSection) {
    Add-WebConfiguration -Filter 'system.webServer/fastCgi' -Value @{
        fullPath           = $phpCgi
        maxInstances       = 4
        instanceMaxRequests = 10000
    }
    Write-OK 'Registered PHP FastCGI with IIS'
} else {
    Write-OK 'PHP FastCGI already registered'
}

# ─── 3. MYSQL CHECK ──────────────────────────────────────────────────────────

Write-Step 'Checking MySQL...'

if (-not (Test-Path $MySqlExe)) {
    # Try PATH
    $found = Get-Command mysql.exe -ErrorAction SilentlyContinue
    if ($found) {
        $MySqlExe = $found.Source
        Write-OK "Found mysql.exe in PATH: $MySqlExe"
    } else {
        Write-Fail "mysql.exe not found at $MySqlExe and not in PATH. Install MySQL 8.0 Community from https://dev.mysql.com/downloads/mysql/"
    }
} else {
    Write-OK "Found $MySqlExe"
}

# Prompt for root password if not set
if ([string]::IsNullOrEmpty($MySqlRootPass)) {
    $secPass = Read-Host 'Enter MySQL root password' -AsSecureString
    $MySqlRootPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secPass)
    )
}

# Test connection
$testResult = & $MySqlExe -u $MySqlRoot "-p$MySqlRootPass" -e 'SELECT 1' 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Fail "Cannot connect to MySQL as root: $testResult"
}
Write-OK 'MySQL connection successful'

# ─── 4. DATABASE SETUP ───────────────────────────────────────────────────────

Write-Step 'Setting up database...'

# Check if DB already exists
$dbExists = & $MySqlExe -u $MySqlRoot "-p$MySqlRootPass" -e "SHOW DATABASES LIKE 'LandingPageDB'" 2>&1
if ($dbExists -match 'LandingPageDB') {
    Write-Warn 'Database LandingPageDB already exists — skipping schema creation (existing data preserved)'
} else {
    # Run install.sql
    if (-not (Test-Path $SqlSource)) {
        Write-Fail "install.sql not found at $SqlSource"
    }
    & $MySqlExe -u $MySqlRoot "-p$MySqlRootPass" < $SqlSource 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Fail 'Failed to initialise database'
    }
    Write-OK 'Database schema and seed data created'
}

# Create app DB users (idempotent DROP IF EXISTS + CREATE)
$userSql = @"
DROP USER IF EXISTS '$AdminDbUser'@'localhost';
DROP USER IF EXISTS '$ViewerDbUser'@'localhost';

CREATE USER '$AdminDbUser'@'localhost' IDENTIFIED BY '$AdminDbPass';
GRANT ALL PRIVILEGES ON LandingPageDB.* TO '$AdminDbUser'@'localhost';

CREATE USER '$ViewerDbUser'@'localhost' IDENTIFIED BY '$ViewerDbPass';
GRANT SELECT ON LandingPageDB.* TO '$ViewerDbUser'@'localhost';
GRANT INSERT, UPDATE ON LandingPageDB.user_profiles TO '$ViewerDbUser'@'localhost';
GRANT INSERT, UPDATE, DELETE ON LandingPageDB.user_links TO '$ViewerDbUser'@'localhost';

FLUSH PRIVILEGES;
"@

$userSql | & $MySqlExe -u $MySqlRoot "-p$MySqlRootPass" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Fail 'Failed to create database users'
}
Write-OK "Created DB users: $AdminDbUser / $ViewerDbUser"

# ─── 5. COPY APP FILES ───────────────────────────────────────────────────────

Write-Step "Copying app files to $WebRoot..."

if (-not (Test-Path $WebRoot)) {
    New-Item -ItemType Directory -Path $WebRoot -Force | Out-Null
}

# Robocopy: /MIR mirrors source to dest (adds new, removes deleted, preserves existing)
# /XD uploads — preserve user uploads between deployments
robocopy $AppSource $WebRoot /MIR /XD uploads /NP /NFL /NDL | Out-Null
Write-OK 'App files copied'

# Ensure uploads directory exists with correct permissions
$uploadsDir = Join-Path $WebRoot 'uploads\icons'
if (-not (Test-Path $uploadsDir)) {
    New-Item -ItemType Directory -Path $uploadsDir -Force | Out-Null
}
# Grant IIS AppPool write access to uploads
$acl = Get-Acl $uploadsDir
$rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
    "IIS AppPool\$SiteName", 'Modify', 'ContainerInherit,ObjectInherit', 'None', 'Allow'
)
$acl.SetAccessRule($rule)
Set-Acl $uploadsDir $acl
Write-OK 'Uploads directory ready'

# ─── 6. GENERATE config.php ──────────────────────────────────────────────────

Write-Step 'Writing config.php...'

# config.php lives one level above the web root (not publicly accessible)
$configDir = Split-Path $WebRoot -Parent
$configPath = Join-Path $configDir 'config.php'

if (Test-Path $configPath) {
    Write-Warn "config.php already exists at $configPath — not overwriting (credentials preserved)"
} else {
    $configContent = @"
<?php
// Generated by install.ps1 on $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
// Do not commit this file to source control.
define('DB_HOST',        'localhost');
define('DB_NAME',        'LandingPageDB');
define('DB_ADMIN_USER',  '$AdminDbUser');
define('DB_ADMIN_PASS',  '$AdminDbPass');
define('DB_VIEWER_USER', '$ViewerDbUser');
define('DB_VIEWER_PASS', '$ViewerDbPass');
"@
    Set-Content $configPath $configContent -Encoding UTF8
    Write-OK "config.php written to $configPath"
    Write-Warn 'IMPORTANT: Keep config.php safe — it contains DB credentials'
}

# ─── 7. IIS SITE ─────────────────────────────────────────────────────────────

Write-Step 'Configuring IIS site...'

# Create App Pool if needed
if (-not (Test-Path "IIS:\AppPools\$SiteName")) {
    New-WebAppPool -Name $SiteName
    Set-ItemProperty "IIS:\AppPools\$SiteName" managedRuntimeVersion ''  # No .NET (PHP only)
    Write-OK "Created App Pool: $SiteName"
} else {
    Write-OK "App Pool $SiteName already exists"
}

# Create or update site
if (-not (Test-Path "IIS:\Sites\$SiteName")) {
    New-WebSite -Name $SiteName -Port $SitePort -PhysicalPath $WebRoot -ApplicationPool $SiteName
    Write-OK "Created IIS site: $SiteName on port $SitePort"
} else {
    Set-ItemProperty "IIS:\Sites\$SiteName" physicalPath $WebRoot
    Write-OK "Updated IIS site: $SiteName"
}

# Enable Windows Authentication, disable Anonymous
Set-WebConfigurationProperty `
    -Filter 'system.webServer/security/authentication/windowsAuthentication' `
    -Name enabled -Value $true `
    -PSPath "IIS:\Sites\$SiteName"

Set-WebConfigurationProperty `
    -Filter 'system.webServer/security/authentication/anonymousAuthentication' `
    -Name enabled -Value $false `
    -PSPath "IIS:\Sites\$SiteName"

Write-OK 'Windows Authentication enabled, Anonymous Authentication disabled'

# ─── 8. DONE ─────────────────────────────────────────────────────────────────

Write-Host ''
Write-Host '============================================================' -ForegroundColor Green
Write-Host '  Installation complete!' -ForegroundColor Green
Write-Host '============================================================' -ForegroundColor Green
Write-Host ''
Write-Host "  Site URL : http://$(hostname)$(if ($SitePort -ne 80) { ":$SitePort" })"
Write-Host "  Web Root : $WebRoot"
Write-Host "  Config   : $configPath"
Write-Host ''
Write-Host '  Next steps:' -ForegroundColor Yellow
Write-Host '    1. Open the site URL in a browser on a domain-joined PC'
Write-Host '    2. The setup wizard will guide you through configuration'
Write-Host '    3. Windows login is used automatically — no extra auth setup needed'
Write-Host ''
Write-Host '  To re-run a fresh install (wipes DB):' -ForegroundColor DarkGray
Write-Host '    Drop the LandingPageDB database in MySQL, then re-run this script'
Write-Host ''
