<?php
require __DIR__ . '/../../bootstrap.php';

// Restrict page to HR role only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'hr') {
    header('Location: ../../login.php');
    exit;
}

// Fetch contact messages from database
$messages = [];

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $sql = "SELECT id, name, email, phone, subject, message, status, created_at
                FROM contact_messages
                ORDER BY created_at DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $result->free();
    } catch (mysqli_sql_exception $e) {
        error_log('HR MESSAGES ERROR - ' . $e->getMessage());
    }
} else {
    error_log('HR MESSAGES ERROR - Database connection is not available.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Messages - Green Meadows Security Agency</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <!-- Page specific CSS -->
    <link rel="stylesheet" href="css/messages.css">
</head>
<body class="messages-page">

<?php include __DIR__ . '/../../global/header.php'; ?>

<div class="layout-wrapper">
    <?php include __DIR__ . '/../../global/sidebar.php'; ?>

    <main class="layout-content p-3 p-md-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0 text-green">Contact Messages</h4>
                    <small class="text-muted">View inquiries sent from the public contact form.</small>
                </div>
            </div>

            <div class="card messages-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><span class="material-icons-outlined me-2">mail</span>Messages List</h5>
                    <span class="small">Total: <?php echo count($messages); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="messagesTable" class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Received On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($messages)): ?>
                                    <?php foreach ($messages as $msg): ?>
                                        <?php
                                            $isRead = (int)$msg['status'] === 1;
                                            $statusClass = $isRead ? 'badge-status-read' : 'badge-status-unread';
                                            $statusLabel = $isRead ? 'Read' : 'Unread';
                                            $rowClass = $isRead ? '' : 'unread-row';
                                            $shortMessage = mb_strimwidth($msg['message'], 0, 80, '...');
                                        ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td><?php echo (int)$msg['id']; ?></td>
                                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                            <td><?php echo htmlspecialchars($msg['phone'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($shortMessage)); ?></td>
                                            <td>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo $statusLabel; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($msg['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                        <tr class="no-data-row">
                                            <td class="text-center text-muted py-4">No messages found.</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<!-- Page specific JS -->
<script src="js/messages.js"></script>
</body>
</html>
