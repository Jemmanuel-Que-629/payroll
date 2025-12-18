<?php
// process_application.php
// Handles public career applications and stores them in the applicants table.

require __DIR__ . '/../bootstrap.php'; // loads .env, starts session, and provides $conn (mysqli)

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // If not POST request, redirect to homepage
    header("Location: ../index.php");
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log("APPLY ERROR - Database connection is not available.");
    header("Location: ../index.php?section=careers&status=error&msg=db_connection#apply");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = htmlspecialchars($_POST['firstName']);
    $middleName = htmlspecialchars($_POST['middleName'] ?? '');
    $lastName = htmlspecialchars($_POST['lastName']);
    $nameExtension = htmlspecialchars($_POST['nameExtension'] ?? '');
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $position = htmlspecialchars($_POST['position']);
    $preferredLocation = htmlspecialchars($_POST['preferredLocation'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    
    // Check for required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($position) || !isset($_FILES['resume'])) {
        error_log("APPLY ERROR - Missing required fields. firstName: " . (!empty($firstName) ? 'OK' : 'MISSING') . 
                  ", lastName: " . (!empty($lastName) ? 'OK' : 'MISSING') . 
                  ", email: " . (!empty($email) ? 'OK' : 'MISSING') . 
                  ", phone: " . (!empty($phone) ? 'OK' : 'MISSING') . 
                  ", position: " . (!empty($position) ? 'OK' : 'MISSING') . 
                  ", resume: " . (isset($_FILES['resume']) ? 'OK' : 'MISSING'));
        header("Location: ../index.php?section=careers&status=incomplete_fields#apply");
        exit;
    }
    
    // Resume upload handling
    $resumePath = '';
    if(isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        error_log("APPLY LOG - Resume file received: " . $_FILES['resume']['name'] . 
                  ", size: " . $_FILES['resume']['size'] . 
                  ", type: " . $_FILES['resume']['type'] . 
                  ", tmp_name: " . $_FILES['resume']['tmp_name']);
        
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = $_FILES['resume']['type'];
        
        // Validate file type
        if(in_array($fileType, $allowedTypes)) {
            // Validate file size (5MB max)
            if($_FILES['resume']['size'] <= 5 * 1024 * 1024) {
                // Use filesystem path for upload, public path for storing in DB
                $uploadDirFs = __DIR__ . '/../uploads/resumes/';
                $uploadDirPublic = 'uploads/resumes/';
                
                // Create directory if it doesn't exist
                if(!file_exists($uploadDirFs)) {
                    error_log("APPLY LOG - Creating upload directory: " . $uploadDirFs);
                    if(!mkdir($uploadDirFs, 0777, true)) {
                        error_log("APPLY ERROR - Failed to create directory: " . $uploadDirFs);
                        header("Location: ../index.php?section=careers&status=upload_error&msg=dir_create_failed#apply");
                        exit;
                    }
                }
                
                // Check if directory is writable
                if(!is_writable($uploadDirFs)) {
                    error_log("APPLY ERROR - Directory not writable: " . $uploadDirFs);
                    header("Location: ../index.php?section=careers&status=upload_error&msg=dir_not_writable#apply");
                    exit;
                }
                
                // Generate unique filename
                $fileName = time() . '_' . $lastName . '_' . $firstName . '_resume';
                $fileExt = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
                $filePathFs = $uploadDirFs . $fileName . '.' . $fileExt;
                $filePathPublic = $uploadDirPublic . $fileName . '.' . $fileExt;

                error_log("APPLY LOG - Attempting to move file to: " . $filePathFs);
                
                // Move uploaded file
                if(move_uploaded_file($_FILES['resume']['tmp_name'], $filePathFs)) {
                    $resumePath = $filePathPublic;
                    error_log("APPLY LOG - File uploaded successfully: " . $filePathFs);
                } else {
                    $uploadError = error_get_last();
                    error_log("APPLY ERROR - move_uploaded_file failed. Error: " . print_r($uploadError, true));
                    error_log("APPLY ERROR - Source: " . $_FILES['resume']['tmp_name'] . ", Destination: " . $filePathFs);
                    error_log("APPLY ERROR - Source exists: " . (file_exists($_FILES['resume']['tmp_name']) ? 'YES' : 'NO'));
                    header("Location: ../index.php?section=careers&status=upload_error&msg=move_failed#apply");
                    exit;
                }
            } else {
                error_log("APPLY ERROR - File too large: " . $_FILES['resume']['size'] . " bytes (max 5MB)");
                header("Location: ../index.php?section=careers&status=file_too_large#apply");
                exit;
            }
        } else {
            error_log("APPLY ERROR - Invalid file type: " . $fileType . ", allowed: " . implode(', ', $allowedTypes));
            header("Location: ../index.php?section=careers&status=invalid_file_type#apply");
            exit;
        }
    } else {
        $fileError = isset($_FILES['resume']['error']) ? $_FILES['resume']['error'] : 'not set';
        error_log("APPLY ERROR - Resume file not received or has error. Error code: " . $fileError);
        if($fileError !== 0 && $fileError !== 'not set') {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload'
            ];
            $errorMsg = isset($errorMessages[$fileError]) ? $errorMessages[$fileError] : 'Unknown error';
            error_log("APPLY ERROR - Upload error details: " . $errorMsg);
        }
        header("Location: ../index.php?section=careers&status=resume_required#apply");
        exit;
    }
    
    try {
        error_log("APPLY LOG - Starting database insertion for: " . $email);
        
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Check for duplicate email
        $checkEmail = $conn->prepare("SELECT COUNT(*) AS cnt FROM applicants WHERE Email = ?");
        $checkEmail->bind_param('s', $email);
        $checkEmail->execute();
        $checkEmail->bind_result($emailCount);
        $checkEmail->fetch();
        $checkEmail->close();

        if ($emailCount > 0) {
            error_log("APPLY LOG - Duplicate email found (" . $emailCount . "), deleting old application: " . $email);
            // Email already exists - could be from old application, delete it and allow reapplication
            $deleteOld = $conn->prepare("DELETE FROM applicants WHERE Email = ?");
            $deleteOld->bind_param('s', $email);
            $deleteOld->execute();
            $deleteOld->close();
            error_log("APPLY LOG - Old application deleted successfully");
        }
        
        // Check for duplicate phone number
        $checkPhone = $conn->prepare("SELECT COUNT(*) AS cnt FROM applicants WHERE Phone_Number = ?");
        $checkPhone->bind_param('s', $phone);
        $checkPhone->execute();
        $checkPhone->bind_result($phoneCount);
        $checkPhone->fetch();
        $checkPhone->close();
        
        if ($phoneCount > 0) {
            error_log("APPLY LOG - Duplicate phone found (" . $phoneCount . "), deleting old application: " . $phone);
            // Phone already exists - could be from old application, delete it and allow reapplication
            $deleteOld = $conn->prepare("DELETE FROM applicants WHERE Phone_Number = ?");
            $deleteOld->bind_param('s', $phone);
            $deleteOld->execute();
            $deleteOld->close();
            error_log("APPLY LOG - Old application with phone deleted successfully");
        }
        
        // Insert new application with Status column
        error_log("APPLY LOG - Preparing INSERT query");
        $stmt = $conn->prepare("INSERT INTO applicants (First_Name, Middle_Name, Last_Name, Name_Extension, Email, Phone_Number, 
                        Position, Preferred_Location, Resume_Path, Additional_Info, Status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')");

        if (!$stmt) {
            throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
        }

        error_log("APPLY LOG - Executing INSERT with data: Name=" . $firstName . " " . $lastName . ", Email=" . $email . ", Position=" . $position);
        $stmt->bind_param(
            'ssssssssss',
            $firstName,
            $middleName,
            $lastName,
            $nameExtension,
            $email,
            $phone,
            $position,
            $preferredLocation,
            $resumePath,
            $message
        );
        $stmt->execute();

        $lastId = $conn->insert_id;
        error_log("APPLY LOG - Application inserted successfully with ID: " . $lastId);
        
        // Redirect after successful submission
        header("Location: ../index.php?section=careers&status=success#apply");
        exit;
        
    } catch (mysqli_sql_exception $e) {
        // Log detailed error
        error_log("APPLY ERROR - Database error occurred");
        error_log("APPLY ERROR - Message: " . $e->getMessage());
        
        // Redirect with error message
        header("Location: ../index.php?section=careers&status=error&msg=" . urlencode($e->getMessage()) . "#apply");
        exit;
    }
}
?>