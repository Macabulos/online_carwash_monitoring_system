<?php
session_start();
require_once '../connection/conn.php'; // Include DB connection

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Unauthorized access. Please log in.";
    header("Location: ../auth/login.php");
    exit;
}

// Handle employee form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_employee'])) {
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $position = trim($_POST['position']);
        $contactNumber = trim($_POST['contact_number']);
        $assignedService = $_POST['assigned_service'] ?? null;
        $hireDate = $_POST['hire_date'];
        $availability = $_POST['availability'] ?? 'Available';

        // Basic validation
        if (empty($firstName) || empty($lastName) || empty($position)) {
            $_SESSION['error_message'] = "First name, last name, and position are required.";
        } else {
            // Handle file upload
            $profilePicture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/employees/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExt = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array(strtolower($fileExt), $allowedExtensions)) {
                    $fileName = uniqid('emp_') . '.' . $fileExt;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                        $profilePicture = $targetPath;
                    } else {
                        $_SESSION['error_message'] = "Failed to upload profile picture.";
                    }
                } else {
                    $_SESSION['error_message'] = "Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.";
                }
            }

            if (!isset($_SESSION['error_message'])) {
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO employees 
                    (FirstName, LastName, Position, ContactNumber, AssignedServiceID, Availability, HireDate, ProfilePicture) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("ssssisss", $firstName, $lastName, $position, $contactNumber, 
                    $assignedService, $availability, $hireDate, $profilePicture);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Employee added successfully!";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error adding employee: " . $conn->error;
                }
            }
        }
    }
    
    // Handle employee update
    if (isset($_POST['update_employee'])) {
        $employeeId = $_POST['employee_id'];
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $position = trim($_POST['position']);
        $contactNumber = trim($_POST['contact_number']);
        $assignedService = $_POST['assigned_service'] ?? null;
        $availability = $_POST['availability'] ?? 'Available';
        
        // Handle file upload if new image is provided
        $profilePicture = $_POST['existing_profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/employees/';
            $fileExt = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array(strtolower($fileExt), $allowedExtensions)) {
                $fileName = uniqid('emp_') . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                    // Delete old profile picture if it exists
                    if ($profilePicture && file_exists($profilePicture)) {
                        unlink($profilePicture);
                    }
                    $profilePicture = $targetPath;
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE employees SET 
            FirstName = ?, 
            LastName = ?, 
            Position = ?, 
            ContactNumber = ?, 
            AssignedServiceID = ?, 
            Availability = ?, 
            ProfilePicture = ?
            WHERE EmployeeID = ?");
        
        $stmt->bind_param("ssssissi", $firstName, $lastName, $position, $contactNumber, 
            $assignedService, $availability, $profilePicture, $employeeId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Employee updated successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating employee: " . $conn->error;
        }
    }
    
    // Handle employee deletion
    if (isset($_POST['delete_employee'])) {
        $employeeId = $_POST['employee_id'];
        
        // First get the profile picture path to delete the file
        $stmt = $conn->prepare("SELECT ProfilePicture FROM employees WHERE EmployeeID = ?");
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        
        if ($employee['ProfilePicture'] && file_exists($employee['ProfilePicture'])) {
            unlink($employee['ProfilePicture']);
        }
        
        // Now delete the employee record
        $stmt = $conn->prepare("DELETE FROM employees WHERE EmployeeID = ?");
        $stmt->bind_param("i", $employeeId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Employee deleted successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error deleting employee: " . $conn->error;
        }
    }
}

// Total Customers with Bookings
$totalCustomersQuery = "
    SELECT COUNT(DISTINCT CustomerID) AS total 
    FROM bookings
";
$totalCustomersResult = mysqli_query($conn, $totalCustomersQuery);
$totalCustomers = mysqli_fetch_assoc($totalCustomersResult)['total'] ?? 0;

// Total Feedbacks
$totalFeedbackQuery = "SELECT COUNT(*) AS total FROM feedback";
$totalFeedbackResult = mysqli_query($conn, $totalFeedbackQuery);
$totalFeedback = mysqli_fetch_assoc($totalFeedbackResult)['total'] ?? 0;

// Total Reports
$totalReportsQuery = "SELECT COUNT(*) AS total FROM report";
$totalReportsResult = mysqli_query($conn, $totalReportsQuery);
$totalReports = mysqli_fetch_assoc($totalReportsResult)['total'] ?? 0;

// Total Users
$totalUsersQuery = "SELECT COUNT(*) AS total FROM customer";
$totalUsersResult = mysqli_query($conn, $totalUsersQuery);
$totalUsers = mysqli_fetch_assoc($totalUsersResult)['total'] ?? 0;

// Get all employees with their assigned service
$employeesQuery = "SELECT e.*, s.ServiceName 
                  FROM employees e 
                  LEFT JOIN service s ON e.AssignedServiceID = s.ServiceID
                  ORDER BY e.Availability, e.LastName, e.FirstName";
$employeesResult = mysqli_query($conn, $employeesQuery);

// Get all services for dropdown
$servicesQuery = "SELECT ServiceID, ServiceName FROM service";
$servicesResult = mysqli_query($conn, $servicesQuery);
$services = [];
while ($service = mysqli_fetch_assoc($servicesResult)) {
    $services[] = $service;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Admin Dashboard</title>
    <style>
        .employee-card {
            transition: transform 0.3s;
        }
        .employee-card:hover {
            transform: translateY(-5px);
        }
        .employee-img {
            height: 200px;
            object-fit: cover;
        }
        .badge-available {
            background-color: #28a745;
        }
        .badge-assigned {
            background-color: #007bff;
        }
        .badge-onleave {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main">
            <?php include 'includes/navbar.php'; ?>
            <main class="content">
                <div class="container-fluid">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <h1 class="h3 mb-3">Admin Dashboard</h1>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Users</h4>
                                    <p class="display-4"><?= $totalUsers ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Bookings</h4>
                                    <p class="display-4"><?= $totalCustomers ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Feedbacks</h4>
                                    <p class="display-4"><?= $totalFeedback ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4>Total Reports</h4>
                                    <p class="display-4"><?= $totalReports ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employee Management Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Employee Management</h5>
                                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                        <i class="fas fa-plus me-2"></i>Add New Employee
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Photo</th>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Assigned Service</th>
                                                    <th>Contact</th>
                                                    <th>Availability</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($employeesResult->num_rows > 0): ?>
                                                    <?php while ($employee = mysqli_fetch_assoc($employeesResult)): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if ($employee['ProfilePicture']): ?>
                                                                    <img src="<?= $employee['ProfilePicture'] ?>" alt="Employee Photo" width="50" height="50" class="rounded-circle">
                                                                <?php else: ?>
                                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                        <i class="fas fa-user text-white"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']) ?></td>
                                                            <td><?= htmlspecialchars($employee['Position']) ?></td>
                                                            <td><?= $employee['ServiceName'] ?? 'Not assigned' ?></td>
                                                            <td><?= $employee['ContactNumber'] ?? 'N/A' ?></td>
                                                            <td>
                                                                <span class="badge 
                                                                    <?= $employee['Availability'] === 'Available' ? 'badge-available' : 
                                                                       ($employee['Availability'] === 'Assigned' ? 'badge-assigned' : 'badge-onleave') ?>">
                                                                    <?= $employee['Availability'] ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editEmployeeModal<?= $employee['EmployeeID'] ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal<?= $employee['EmployeeID'] ?>">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No employees found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position *</label>
                                    <input type="text" class="form-control" id="position" name="position" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="tel" class="form-control" id="contact_number" name="contact_number">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_service" class="form-label">Assigned Service</label>
                                    <select class="form-select" id="assigned_service" name="assigned_service">
                                        <option value="">-- Select Service --</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= $service['ServiceID'] ?>"><?= htmlspecialchars($service['ServiceName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="availability" class="form-label">Availability</label>
                                    <select class="form-select" id="availability" name="availability">
                                        <option value="Available">Available</option>
                                        <option value="Assigned">Assigned</option>
                                        <option value="On Leave">On Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hire_date" class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit/Delete Employee Modals -->
    <?php 
    // Reset the pointer to loop through employees again for modals
    mysqli_data_seek($employeesResult, 0);
    if ($employeesResult->num_rows > 0): 
        while ($employee = mysqli_fetch_assoc($employeesResult)): 
    ?>
        <!-- Edit Employee Modal -->
        <div class="modal fade" id="editEmployeeModal<?= $employee['EmployeeID'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Employee</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="employee_id" value="<?= $employee['EmployeeID'] ?>">
                            <input type="hidden" name="existing_profile_picture" value="<?= $employee['ProfilePicture'] ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($employee['FirstName']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($employee['LastName']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="position" class="form-label">Position</label>
                                        <input type="text" class="form-control" name="position" value="<?= htmlspecialchars($employee['Position']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" name="contact_number" value="<?= htmlspecialchars($employee['ContactNumber']) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="assigned_service" class="form-label">Assigned Service</label>
                                        <select class="form-select" name="assigned_service">
                                            <option value="">-- Select Service --</option>
                                            <?php foreach ($services as $service): ?>
                                                <option value="<?= $service['ServiceID'] ?>" <?= ($employee['AssignedServiceID'] == $service['ServiceID']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($service['ServiceName']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="availability" class="form-label">Availability</label>
                                        <select class="form-select" name="availability">
                                            <option value="Available" <?= ($employee['Availability'] == 'Available') ? 'selected' : '' ?>>Available</option>
                                            <option value="Assigned" <?= ($employee['Availability'] == 'Assigned') ? 'selected' : '' ?>>Assigned</option>
                                            <option value="On Leave" <?= ($employee['Availability'] == 'On Leave') ? 'selected' : '' ?>>On Leave</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                <?php if ($employee['ProfilePicture']): ?>
                                    <div class="mt-2">
                                        <img src="<?= $employee['ProfilePicture'] ?>" width="100" class="img-thumbnail">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_profile_picture" id="remove_profile_picture<?= $employee['EmployeeID'] ?>">
                                            <label class="form-check-label" for="remove_profile_picture<?= $employee['EmployeeID'] ?>">
                                                Remove current photo
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_employee" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Delete Employee Modal -->
        <div class="modal fade" id="deleteEmployeeModal<?= $employee['EmployeeID'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="employee_id" value="<?= $employee['EmployeeID'] ?>">
                            <p>Are you sure you want to delete <?= htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']) ?>?</p>
                            <p class="text-danger">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="delete_employee" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php 
        endwhile;
    endif; 
    ?>

    <?php include 'includes/scripts.php'; ?>
</body>
</html>
