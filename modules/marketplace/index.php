<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Marketplace";

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT m.*, u.full_name AS seller_name FROM marketplace_items m
        JOIN users u ON u.user_id = m.seller_id
        WHERE m.status = 'available'";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (m.title LIKE ? OR m.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like;
    $types .= "ss";
}
if ($category !== '') {
    $sql .= " AND m.category = ?";
    $params[] = $category;
    $types .= "s";
}
$sql .= " ORDER BY m.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result();

$categories = $conn->query("SELECT DISTINCT category FROM marketplace_items WHERE category IS NOT NULL AND category != ''");

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0;">🛒 Buy & Sell Marketplace</h2>
            <p style="color:var(--text-muted); margin:4px 0 0;">Browse listings from fellow students</p>
        </div>
        <a href="create.php" class="btn btn-primary">+ Post an Item</a>
    </div>

    <div class="card">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap;">
            <input type="text" name="q" class="form-control" style="flex:2; min-width:200px;" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category" class="form-control" style="flex:1; min-width:150px;">
                <option value="">All Categories</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($c['category']); ?>" <?php echo $category === $c['category'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['category']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
        </form>
    </div>

    <div class="card-grid">
        <?php if ($items->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No items found.</p>
        <?php endif; ?>
        <?php while ($item = $items->fetch_assoc()): ?>
            <a href="view.php?id=<?php echo $item['item_id']; ?>" class="card" style="display:block;">
                <?php if ($item['image_path']): ?>
                    <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>" style="width:100%; height:150px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
                <?php else: ?>
                    <div style="width:100%; height:150px; background:var(--bg); border-radius:8px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">No Image</div>
                <?php endif; ?>
                <h4 style="margin:0 0 6px;"><?php echo htmlspecialchars($item['title']); ?></h4>
                <p style="color:var(--primary); font-weight:700; margin:0 0 6px;">Rs. <?php echo number_format($item['price'], 2); ?></p>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0;"><?php echo htmlspecialchars($item['category'] ?: 'General'); ?> · by <?php echo htmlspecialchars($item['seller_name']); ?></p>
            </a>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
