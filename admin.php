<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

include 'db.php';

// Handle Employee CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_employee'])) {
        // Add Employee
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO employees (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
    } elseif (isset($_POST['edit_employee'])) {
        // Edit Employee
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $email, $password, $role, $id]);
    } elseif (isset($_POST['delete_employee'])) {
        // Delete Employee
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['edit_attendance'])) {
        // Edit Attendance
        $id = $_POST['id'];
        $time_in = $_POST['time_in'];
        $time_out = $_POST['time_out'];

        $stmt = $conn->prepare("UPDATE attendance SET time_in = ?, time_out = ? WHERE id = ?");
        $stmt->execute([$time_in, $time_out, $id]);
    } elseif (isset($_POST['delete_attendance'])) {
        // Delete Attendance
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Fetch Employees
$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll();

// Fetch Attendance Records
$stmt = $conn->query("SELECT a.*, e.name FROM attendance a JOIN employees e ON a.employee_id = e.id");
$attendance = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Admin Panel
                        <a href="logout.php" class="btn btn-danger btn-sm float-right">Logout</a>
                    </div>
                    <div class="card-body">
                        <!-- Employee CRUD Section -->
                        <h5>Employee Management</h5>
                        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addEmployeeModal">Add Employee</button>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?php echo $employee['id']; ?></td>
                                        <td><?php echo $employee['name']; ?></td>
                                        <td><?php echo $employee['email']; ?></td>
                                        <td><?php echo $employee['role']; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editEmployeeModal<?php echo $employee['id']; ?>">Edit</button>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="delete_employee" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Employee Modal -->
                                    <div class="modal fade" id="editEmployeeModal<?php echo $employee['id']; ?>" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input type="text" name="name" class="form-control" value="<?php echo $employee['name']; ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input type="email" name="email" class="form-control" value="<?php echo $employee['email']; ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Password</label>
                                                            <input type="text" name="password" class="form-control" value="<?php echo $employee['password']; ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Role</label>
                                                            <select name="role" class="form-control" required>
                                                                <option value="employee" <?php echo $employee['role'] == 'employee' ? 'selected' : ''; ?>>Employee</option>
                                                                <option value="admin" <?php echo $employee['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" name="edit_employee" class="btn btn-primary">Save Changes</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Add Employee Modal -->
                        <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input type="text" name="password" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select name="role" class="form-control" required>
                                                    <option value="employee">Employee</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance CRUD Section -->
                        <h5 class="mt-5">Attendance Management</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance as $record): ?>
                                    <tr>
                                        <td><?php echo $record['name']; ?></td>
                                        <td><?php echo $record['date']; ?></td>
                                        <td><?php echo $record['time_in']; ?></td>
                                        <td><?php echo $record['time_out']; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAttendanceModal<?php echo $record['id']; ?>">Edit</button>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                                                <button type="submit" name="delete_attendance" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Attendance Modal -->
                                    <div class="modal fade" id="editAttendanceModal<?php echo $record['id']; ?>" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                                                        <div class="form-group">
                                                            <label>Time In</label>
                                                            <input type="datetime-local" name="time_in" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($record['time_in'])); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Time Out</label>
                                                            <input type="datetime-local" name="time_out" class="form-control" value="<?php echo $record['time_out'] ? date('Y-m-d\TH:i', strtotime($record['time_out'])) : ''; ?>">
                                                        </div>
                                                        <button type="submit" name="edit_attendance" class="btn btn-primary">Save Changes</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>