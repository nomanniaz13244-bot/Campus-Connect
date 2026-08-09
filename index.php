<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = "Home";
include 'includes/header.php';
?>
<div class="page-container">
    <div class="card" style="text-align:center; padding:50px 30px;">
        <h1 style="margin-top:0;">Welcome to Campus Connect 🎓</h1>
        <p style="color:var(--text-muted); max-width:600px; margin:0 auto 24px;">
            Your all-in-one university community platform — marketplace, notes sharing, lost & found,
            clubs, events, and complaints, all in one secure place.
        </p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="auth/register.php" class="btn btn-primary">Get Started</a>
            <a href="auth/login.php" class="btn btn-outline" style="margin-left:10px;">Log In</a>
        <?php else: ?>
            <a href="dashboard/<?php echo htmlspecialchars($_SESSION['role']); ?>.php" class="btn btn-primary">Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <div class="card-grid" style="margin-top:30px;">
        <div class="card"><h3>🛒 Marketplace</h3><p style="color:var(--text-muted); font-size:0.9rem;">Buy & sell books, gadgets and everyday items with fellow students.</p></div>
        <div class="card"><h3>📚 Notes Sharing</h3><p style="color:var(--text-muted); font-size:0.9rem;">Find and share notes organized by subject and semester.</p></div>
        <div class="card"><h3>🔍 Lost & Found</h3><p style="color:var(--text-muted); font-size:0.9rem;">Report and recover lost items around campus.</p></div>
        <div class="card"><h3>🎭 Clubs & Societies</h3><p style="color:var(--text-muted); font-size:0.9rem;">Discover and join campus clubs and societies.</p></div>
        <div class="card"><h3>📅 Events & Internships</h3><p style="color:var(--text-muted); font-size:0.9rem;">Stay updated on workshops, events and internship opportunities.</p></div>
        <div class="card"><h3>📝 Complaints Portal</h3><p style="color:var(--text-muted); font-size:0.9rem;">Submit and track campus-related complaints easily.</p></div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
