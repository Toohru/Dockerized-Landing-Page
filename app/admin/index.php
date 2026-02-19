<?php
require __DIR__ . '/../includes/db.php';

$pdo = getDb('admin');
$message = '';
$messageType = '';
$editLink = null;

// --- Handle form actions ---

// DELETE
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM def_links WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $message = 'Link deleted.';
    $messageType = 'success';
}

// ADD
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $url  = trim($_POST['url'] ?? '');
    if ($name !== '' && $url !== '') {
        $stmt = $pdo->prepare("INSERT INTO def_links (name, url) VALUES (?, ?)");
        $stmt->execute([$name, $url]);
        $message = 'Link added.';
        $messageType = 'success';
    } else {
        $message = 'Name and URL are required.';
        $messageType = 'error';
    }
}

// UPDATE
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $name = trim($_POST['name'] ?? '');
    $url  = trim($_POST['url'] ?? '');
    $id   = $_POST['id'] ?? '';
    if ($name !== '' && $url !== '' && $id !== '') {
        $stmt = $pdo->prepare("UPDATE def_links SET name = ?, url = ? WHERE id = ?");
        $stmt->execute([$name, $url, $id]);
        $message = 'Link updated.';
        $messageType = 'success';
    } else {
        $message = 'All fields are required.';
        $messageType = 'error';
    }
}

// Check if we're editing
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM def_links WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editLink = $stmt->fetch();
}

// Fetch all links
$links = $pdo->query("SELECT * FROM def_links ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Manage Links</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>

    <div class="background-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="admin-container">

        <!-- Header -->
        <div class="admin-header">
            <h1>Manage Links</h1>
            <a href="/" class="back-link">← Back to Home</a>
        </div>

        <!-- Toast -->
        <?php if ($message): ?>
            <div class="toast <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Add / Edit Form -->
        <div class="glass-box" style="height:auto; align-items:stretch;">
            <h3 style="margin-bottom:0.8rem;">
                <?= $editLink ? 'Edit Link' : 'Add New Link' ?>
            </h3>
            <form method="POST" class="admin-form"
                  action="/admin/<?= $editLink ? '' : '' ?>">

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

        <!-- Links List -->
        <div class="glass-box" style="height:auto; align-items:stretch;">
            <h3 style="margin-bottom:0.5rem;">Current Links</h3>

            <?php if (empty($links)): ?>
                <div class="empty-state">
                    <p>No links yet. Add one above.</p>
                </div>
            <?php else: ?>
                <table class="links-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>URL</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links as $link): ?>
                            <tr class="link-row">
                                <td><?= htmlspecialchars($link['name']) ?></td>
                                <td><span class="link-url"><?= htmlspecialchars($link['url']) ?></span></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="/admin/?edit=<?= $link['id'] ?>" class="btn">Edit</a>
                                        <form method="POST" action="/admin/"
                                              onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($link['name'])) ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $link['id'] ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
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

</body>
</html>