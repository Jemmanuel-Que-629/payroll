<?php
require __DIR__ . '/../../bootstrap.php';

// Restrict page to HR role only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'hr') {
    header('Location: ../../login.php');
    exit;
}

// Fetch applicants from database
$applicants = [];

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $sql = "SELECT Applicant_ID, First_Name, Middle_Name, Last_Name, Name_Extension, Email, Phone_Number,
                       Position, Preferred_Location, Resume_Path, Additional_Info, Application_Date,
                       Status, Reviewed
                FROM applicants
                ORDER BY Application_Date DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $applicants[] = $row;
        }
        $result->free();
    } catch (mysqli_sql_exception $e) {
        error_log('HR APPLICANTS ERROR - ' . $e->getMessage());
    }
} else {
    error_log('HR APPLICANTS ERROR - Database connection is not available.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Applicants - Green Meadows Security Agency</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <!-- Page specific CSS -->
    <link rel="stylesheet" href="css/applicants.css">
</head>
<body class="applicants-page">

<?php include __DIR__ . '/../../global/header.php'; ?>

<div class="layout-wrapper">
    <?php include __DIR__ . '/../../global/sidebar.php'; ?>

    <main class="layout-content p-3 p-md-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0 text-green">Applicants</h4>
                    <small class="text-muted">Review and manage incoming job applications.</small>
                </div>
            </div>

            <div class="card applicants-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-user-plus me-2"></i>Applicants List</h5>
                    <span class="small">Total: <?php echo count($applicants); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="applicantsTable" class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Applicant Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Preferred Location</th>
                                    <th>Status</th>
                                    <th>Reviewed</th>
                                    <th>Applied On</th>
                                    <th>Resume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($applicants)): ?>
                                    <?php foreach ($applicants as $applicant): ?>
                                        <?php
                                            $fullName = trim($applicant['First_Name'] . ' ' . ($applicant['Middle_Name'] ? $applicant['Middle_Name'] . ' ' : '') . $applicant['Last_Name'] . ' ' . ($applicant['Name_Extension'] ?? ''));
                                            $status = $applicant['Status'];
                                            $statusClass = 'badge-status-new';
                                            switch ($status) {
                                                case 'Contacted':
                                                    $statusClass = 'badge-status-contacted';
                                                    break;
                                                case 'Interview Scheduled':
                                                    $statusClass = 'badge-status-interview';
                                                    break;
                                                case 'Hired':
                                                    $statusClass = 'badge-status-hired';
                                                    break;
                                                case 'Not Qualified':
                                                    $statusClass = 'badge-status-not-qualified';
                                                    break;
                                            }
                                            $reviewedLabel = $applicant['Reviewed'] ? 'Yes' : 'No';
                                            $reviewedClass = $applicant['Reviewed'] ? 'badge bg-success' : 'badge bg-secondary';
                                        ?>
                                        <tr>
                                            <td><?php echo (int)$applicant['Applicant_ID']; ?></td>
                                            <td><?php echo htmlspecialchars($fullName); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['Email']); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['Phone_Number']); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['Position']); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['Preferred_Location'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?php echo $reviewedClass; ?>">
                                                    <?php echo $reviewedLabel; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($applicant['Application_Date']); ?></td>
                                            <td>
                                                <?php if (!empty($applicant['Resume_Path'])): ?>
                                                    <a href="../../<?php echo htmlspecialchars($applicant['Resume_Path']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                        <i class="fa-solid fa-file-arrow-down me-1"></i>View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                        <tr class="no-data-row">
                                            <td class="text-center text-muted py-4">No applicants found.</td>
                                            <td></td>
                                            <td></td>
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
<script src="js/applicants.js"></script>
</body>
</html>
