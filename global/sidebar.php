<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$roleName = $_SESSION['role'] ?? null; // 'hr' or 'accounting'

// Simple guard: redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || !$roleName) {
	header('Location: ../login.php');
	exit;
}

// Helper to mark active links
function is_active($needle)
{
	$uri = $_SERVER['REQUEST_URI'] ?? '';
	return strpos($uri, $needle) !== false ? 'active' : '';
}
?>

<aside class="layout-sidebar bg-dark-green text-white d-flex flex-column flex-shrink-0">
	<div class="p-3 border-bottom border-opacity-25 border-light d-flex align-items-center">
		<span class="sidebar-brand-text fw-bold text-white">Dashboard</span>
	</div>

	<nav class="nav nav-pills flex-column mb-auto mt-2 px-2">
		<?php if ($roleName === 'hr'): ?>
			<a href="dashboard.php" class="nav-link text-white <?php echo is_active('hr/dashboard'); ?>">
				<span class="material-icons-outlined me-2">dashboard</span>
				<span class="link-text">HR Dashboard</span>
			</a>
			<a href="index.php" class="nav-link text-white <?php echo is_active('views/hr/index'); ?>">
				<span class="material-icons-outlined me-2">groups</span>
				<span class="link-text">Employees</span>
			</a>
			<a href="applicants.php" class="nav-link text-white <?php echo is_active('applicants'); ?>">
				<span class="material-icons-outlined me-2">person_add</span>
				<span class="link-text">Applicants</span>
			</a>
			<a href="messages.php" class="nav-link text-white <?php echo is_active('messages'); ?>">
				<span class="material-icons-outlined me-2">mail</span>
				<span class="link-text">Contact Messages</span>
			</a>
		<?php elseif ($roleName === 'accounting'): ?>
			<a href="dashboard.php" class="nav-link text-white <?php echo is_active('accounting/dashboard'); ?>">
				<span class="material-icons-outlined me-2">dashboard</span>
				<span class="link-text">Accounting Dashboard</span>
			</a>
			<a href="index.php" class="nav-link text-white <?php echo is_active('views/accounting/index'); ?>">
				<span class="material-icons-outlined me-2">receipt_long</span>
				<span class="link-text">Payroll</span>
			</a>
			<a href="reports.php" class="nav-link text-white <?php echo is_active('reports'); ?>">
				<span class="material-icons-outlined me-2">insights</span>
				<span class="link-text">Reports</span>
			</a>
		<?php else: ?>
			<div class="p-3 small text-warning">Unknown role. Please contact the administrator.</div>
		<?php endif; ?>
	</nav>

	<div class="mt-auto p-3 small text-white-50 border-top border-opacity-25 border-light">
		<div>Logged in as: <span class="text-white fw-semibold"><?php echo htmlspecialchars(ucfirst($roleName)); ?></span></div>
	</div>
</aside>

<style>
.bg-dark-green {
	background-color: #1e5c3a;
}

.layout-sidebar .nav-link {
	border-radius: 0.5rem;
	padding: 0.55rem 0.75rem;
	margin-bottom: 0.25rem;
	font-size: 0.9rem;
	display: flex;
	align-items: center;
}

.layout-sidebar .nav-link .material-icons-outlined {
	width: 1.25rem;
	text-align: center;
}

.layout-sidebar .nav-link:hover {
	background-color: #2a7d4f;
}

.layout-sidebar .nav-link.active {
	background: linear-gradient(90deg, #2a7d4f 0%, #68a482 100%);
	color: #ffffff;
}
</style>

