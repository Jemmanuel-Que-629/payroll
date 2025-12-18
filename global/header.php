<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$roleName = $_SESSION['role'] ?? null; // 'hr' or 'accounting' from login
$userEmail = $_SESSION['user_email'] ?? null;
?>


<header class="top-header d-flex align-items-center justify-content-between px-3 px-md-4 py-2 shadow-sm">
	<div class="d-flex align-items-center">
		<!-- Sidebar toggle / hamburger -->
		<button id="sidebarToggle" class="btn btn-link header-icon-btn p-0 me-3 d-flex align-items-center justify-content-center">
			<span class="material-icons-outlined">menu</span>
		</button>
		<span class="fw-semibold header-title d-none d-sm-inline">Green Meadows Security Agency</span>
	</div>

	<!-- Center: date & time -->
	<div class="header-datetime text-center small">
		<span id="currentDate" class="d-block fw-semibold"></span>
		<span id="currentTime" class="d-block"></span>
	</div>

	<!-- Right: notifications + profile -->
	<div class="d-flex align-items-center">
		<button class="btn btn-link header-icon-btn position-relative me-3" id="notificationButton" type="button">
			<span class="material-icons-outlined">notifications</span>
			<span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.65rem; display:none;" id="notificationBadge">0</span>
		</button>

		<div class="dropdown">
			<button class="btn btn-light btn-sm d-flex align-items-center rounded-pill" type="button" id="profileMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
				<span class="avatar-circle bg-success text-white me-2 text-uppercase">
					<?php echo htmlspecialchars(substr($userEmail ?? ($roleName ?? 'U'), 0, 1)); ?>
				</span>
				<span class="d-none d-sm-inline small text-dark">
					<?php echo htmlspecialchars($roleName ? ucfirst($roleName) : 'User'); ?>
				</span>
				<span class="material-icons-outlined ms-2 small text-muted d-none d-sm-inline" style="font-size: 18px;">expand_more</span>
			</button>
			<ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileMenuButton">
				<li class="dropdown-header small text-muted px-3 py-2">
					Signed in as<br>
					<span class="fw-semibold text-dark"><?php echo htmlspecialchars($userEmail ?? 'Unknown'); ?></span>
				</li>
				<li><hr class="dropdown-divider"></li>
				<li><a class="dropdown-item small" href="../../logout.php"><span class="material-icons-outlined me-2" style="font-size: 18px;">logout</span>Logout</a></li>
			</ul>
		</div>
	</div>
</header>

<style>
.top-header {
	background-color: #ffffff;
	color: #222222;
	border-bottom: 1px solid #e0e0e0;
	z-index: 1030;
}

.header-title {
	color: #2a7d4f;
}

.header-datetime span {
	color: #555555;
}

.header-icon-btn {
	color: #2a7d4f;
}

.header-icon-btn .material-icons-outlined {
	font-size: 22px;
}

.avatar-circle {
	width: 30px;
	height: 30px;
	border-radius: 50%;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	font-size: 0.85rem;
}

.layout-wrapper {
	display: flex;
	min-height: 100vh;
}

.layout-sidebar {
	width: 250px;
	transition: width 0.25s ease, transform 0.25s ease;
}

.layout-content {
	flex: 1;
	min-width: 0;
}

/* Collapsed sidebar state */
.sidebar-collapsed .layout-sidebar {
	width: 70px;
}

.sidebar-collapsed .layout-sidebar .nav-link span.link-text {
	display: none;
}

.sidebar-collapsed .layout-sidebar .sidebar-brand-text {
	display: none;
}

@media (max-width: 767.98px) {
	.layout-sidebar {
		position: fixed;
		top: 56px; /* approx header height */
		bottom: 0;
		left: 0;
		transform: translateX(-100%);
		width: 230px;
	}

	.sidebar-open .layout-sidebar {
		transform: translateX(0);
	}
}
</style>

<script>
// Header JS: datetime and sidebar toggle
document.addEventListener('DOMContentLoaded', function () {
	const updateDateTime = () => {
		const now = new Date();
		const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
		const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
		const dateEl = document.getElementById('currentDate');
		const timeEl = document.getElementById('currentTime');
		if (dateEl) dateEl.textContent = now.toLocaleDateString(undefined, dateOptions);
		if (timeEl) timeEl.textContent = now.toLocaleTimeString(undefined, timeOptions);
	};

	updateDateTime();
	setInterval(updateDateTime, 1000);

	const toggleBtn = document.getElementById('sidebarToggle');
	if (toggleBtn) {
		toggleBtn.addEventListener('click', function () {
			const body = document.body;
			if (window.innerWidth < 768) {
				body.classList.toggle('sidebar-open');
			} else {
				body.classList.toggle('sidebar-collapsed');
			}
		});
	}
});
</script>

