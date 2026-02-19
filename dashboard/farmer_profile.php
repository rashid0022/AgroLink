<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);

$error = '';
$success = '';
$editMode = isset($_GET['edit']) && $_GET['edit'] === '1';

// Create uploads directory if it doesn't exist
$uploadsDir = '../assets/images/farmers/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmName = isset($_POST['farm_name']) ? trim($_POST['farm_name']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $phoneNumber = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $passportPhoto = null;

    if (empty($farmName) || empty($location) || empty($phoneNumber)) {
        $error = 'Please fill all required fields';
    } else {
        // Handle file upload if provided
        if (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] != UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['passport_photo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Error uploading file';
            } elseif (!in_array($file['type'], $allowedTypes)) {
                $error = 'Only image files are allowed (JPG, PNG, GIF, WebP)';
            } elseif ($file['size'] > $maxFileSize) {
                $error = 'Image size must be less than 5MB';
            } else {
                // Generate unique filename
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $passportPhoto = 'farmer_' . $farmer['farmer_id'] . '_' . time() . '.' . $fileExtension;
                $uploadPath = $uploadsDir . $passportPhoto;

                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Delete old photo if exists
                    if (!empty($farmer['passport_photo']) && file_exists($uploadsDir . $farmer['passport_photo'])) {
                        unlink($uploadsDir . $farmer['passport_photo']);
                    }
                    $result = updateFarmerProfile($farmer['farmer_id'], $farmName, $location, $phoneNumber, $passportPhoto);
                    if ($result['success']) {
                        $success = 'Profile updated successfully!';
                        $farmer = getFarmerByUserId($user['user_id']); // Refresh data
                        $editMode = false;
                    } else {
                        $error = $result['message'];
                        // Delete uploaded image if update failed
                        if (file_exists($uploadPath)) {
                            unlink($uploadPath);
                        }
                    }
                } else {
                    $error = 'Failed to upload photo. Please try again.';
                }
            }
        } else {
            // Update without photo
            $result = updateFarmerProfile($farmer['farmer_id'], $farmName, $location, $phoneNumber);
            if ($result['success']) {
                $success = 'Profile updated successfully!';
                $farmer = getFarmerByUserId($user['user_id']); // Refresh data
                $editMode = false;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Farm Profile - AgroLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 4px solid #11998e;
        }
        
        header h1 {
            display: inline-block;
            font-size: 24px;
            margin-right: 20px;
        }
        
        .header-right {
            float: right;
            text-align: right;
        }
        
        .header-right a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #667eea;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .info-row {
            margin-bottom: 20px;
        }
        
        .info-label {
            color: #999;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            padding: 12px;
            background: #f5f6fa;
            border-radius: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #1565c0;
            font-size: 14px;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #667eea;
        }

        .photo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .edit-mode-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .edit-mode-form .form-group {
            margin-bottom: 15px;
        }

        .edit-mode-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .edit-mode-form input,
        .edit-mode-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .edit-mode-form textarea {
            resize: vertical;
            min-height: 80px;
        }

        .edit-mode-form input:focus,
        .edit-mode-form textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-save {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-save:hover {
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #e0e0e0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #d0d0d0;
        }

        .edit-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <header>
        <h1>🚜 My Farm Profile</h1>
        <div class="header-right">
            <a href="../dashboard/farmer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="profile-container">
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($editMode): ?>
                <!-- EDIT MODE -->
                <form method="POST" enctype="multipart/form-data" class="edit-mode-form">
                    <h3 style="margin-bottom: 20px; color: #667eea;">Edit Profile</h3>
                    
                    <div class="form-group">
                        <label>Passport/Profile Photo</label>
                        <?php if (!empty($farmer['passport_photo']) && file_exists('../assets/images/farmers/' . $farmer['passport_photo'])): ?>
                            <img src="../assets/images/farmers/<?php echo htmlspecialchars($farmer['passport_photo']); ?>" alt="Profile Photo" class="profile-photo" style="display: block; margin: 0 auto 10px;">
                        <?php endif; ?>
                        <input type="file" name="passport_photo" accept="image/*">
                        <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: JPG, PNG, GIF, WebP. Maximum size: 5MB</small>
                    </div>

                    <div class="form-group">
                        <label>Farm Name</label>
                        <input type="text" name="farm_name" value="<?php echo htmlspecialchars($farmer['farm_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($farmer['location']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($farmer['phone_number']); ?>" required>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-save">Save Changes</button>
                        <a href="farmer_profile.php" class="btn btn-cancel" style="text-decoration: none; text-align: center;">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <!-- VIEW MODE -->
                <div style="text-align: right; margin-bottom: 20px;">
                    <a href="farmer_profile.php?edit=1" class="edit-btn">✏️ Edit Profile</a>
                </div>
                
                <?php if ($farmer['verification_status'] === 'Pending'): ?>
                    <div class="info-box">
                        ⏱️ Your farm is pending verification. Admin will review your documents shortly.
                    </div>
                <?php elseif ($farmer['verification_status'] === 'Suspended'): ?>
                    <div class="info-box" style="background: #ffebee; border-color: #f44336; color: #c62828;">
                        ⚠️ Your farm account has been suspended. Please contact admin support.
                    </div>
                <?php endif; ?>
                
                <!-- PROFILE PHOTO SECTION -->
                <div class="photo-section">
                    <h2 class="section-title">Profile Photo</h2>
                    <?php if (!empty($farmer['passport_photo']) && file_exists('../assets/images/farmers/' . $farmer['passport_photo'])): ?>
                        <img src="../assets/images/farmers/<?php echo htmlspecialchars($farmer['passport_photo']); ?>" alt="Profile Photo" class="profile-photo">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; background: #f0f0f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #999; font-size: 40px;">
                            📷
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="section">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Contact Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($farmer['phone_number']); ?></div>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">Farm Information</h2>
                    <div class="info-row">
                        <div class="info-label">Farm Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($farmer['farm_name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Location</div>
                        <div class="info-value"><?php echo htmlspecialchars($farmer['location']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Verification Status</div>
                        <div>
                            <span class="status-badge status-<?php echo strtolower($farmer['verification_status']); ?>">
                                <?php echo htmlspecialchars($farmer['verification_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Farm Registered</div>
                        <div class="info-value"><?php echo formatDate($farmer['registered_date']); ?></div>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">Verification Documents</h2>
                    <div class="info-row">
                        <div class="info-label">National ID Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($farmer['national_id_number']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">House/Plot Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($farmer['house_number']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Business License Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($farmer['business_license_number']); ?></div>
                    </div>
                </div>
                
                <a href="../dashboard/farmer.php" class="back-link">Back to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
