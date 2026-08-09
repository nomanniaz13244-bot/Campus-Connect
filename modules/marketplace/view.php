<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT m.*, u.full_name AS seller_name, u.email AS seller_email FROM marketplace_items m JOIN users u ON u.user_id = m.seller_id WHERE m.item_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("<p>Item not found. <a href='index.php'>Back to marketplace</a></p>");
}

$is_owner = ($item['seller_id'] == $_SESSION['user_id']);
$page_title = $item['title'];
include '../../includes/header.php';
?>
<div class="page-container" style="max-width:700px;">
    <div class="card">
        <?php if ($item['image_path']): ?>
            <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>" style="width:100%; max-height:350px; object-fit:cover; border-radius:8px; margin-bottom:16px;">
        <?php endif; ?>
        <h2 style="margin:0 0 6px;"><?php echo htmlspecialchars($item['title']); ?></h2>
        <p style="color:var(--primary); font-size:1.4rem; font-weight:700; margin:0 0 10px;">Rs. <?php echo number_format($item['price'], 2); ?></p>
        <p style="color:var(--text-muted);">
            <?php echo htmlspecialchars($item['category'] ?: 'General'); ?> ·
            Condition: <?php echo htmlspecialchars(ucwords(str_replace('_',' ',$item['condition_type']))); ?> ·
            Status: <strong><?php echo htmlspecialchars(ucfirst($item['status'])); ?></strong>
        </p>
        <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
        <hr style="border:none; border-top:1px solid var(--border); margin:16px 0;">
        <p>Seller: <strong><?php echo htmlspecialchars($item['seller_name']); ?></strong> (<?php echo htmlspecialchars($item['seller_email']); ?>)</p>

        <?php if ($is_owner): ?>
            <div style="margin-top:16px; display:flex; gap:10px;">
                <?php if ($item['status'] === 'available'): ?>
                <form method="POST" action="update_status.php">
                    <input type="hidden" name="id" value="<?php echo $item['item_id']; ?>">
                    <input type="hidden" name="status" value="sold">
                    <button type="submit" class="btn btn-outline">Mark as Sold</button>
                </form>
                <?php endif; ?>
                <form method="POST" action="delete.php" onsubmit="return confirm('Delete this listing?');">
                    <input type="hidden" name="id" value="<?php echo $item['item_id']; ?>">
                    <button type="submit" class="btn btn-outline" style="color:var(--danger); border-color:var(--danger);">Delete</button>
                </form>
            </div>
        <?php endif; ?>
        <a href="index.php" style="display:inline-block; margin-top:16px; color:var(--primary);">&larr; Back to Marketplace</a>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
