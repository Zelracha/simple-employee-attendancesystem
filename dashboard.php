<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include 'db.php';

$employee_id = $_SESSION['user_id'];
$date = date('Y-m-d');

// Check if the user has already timed in today
$stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
$stmt->execute([$employee_id, $date]);
$attendance_today = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['time_in'])) {
        if (!$attendance_today) {
            // Insert time in only if the user hasn't timed in today
            $stmt = $conn->prepare("INSERT INTO attendance (employee_id, time_in, date) VALUES (?, NOW(), ?)");
            $stmt->execute([$employee_id, $date]);
        }
    } elseif (isset($_POST['time_out'])) {
        if ($attendance_today && !$attendance_today['time_out']) {
            // Update time out only if the user has timed in and hasn't timed out yet
            $stmt = $conn->prepare("UPDATE attendance SET time_out = NOW() WHERE id = ?");
            $stmt->execute([$attendance_today['id']]);

            // Redirect to logout after time out
            header('Location: logout.php');
            exit();
        }
    }
}

// Fetch all attendance records for the employee
$stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? ORDER BY date DESC");
$stmt->execute([$employee_id]);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Employee Dashboard
                        <a href="logout.php" class="btn btn-danger btn-sm float-right">Logout</a>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if (!$attendance_today || $attendance_today['time_out']): ?>
                                <button type="submit" name="time_in" class="btn btn-success">Time In</button>
                            <?php endif; ?>
                            <?php if ($attendance_today && !$attendance_today['time_out']): ?>
                                <button type="submit" name="time_out" class="btn btn-danger">Time Out</button>
                            <?php endif; ?>
                        </form>
                        <hr>
                        <h5>Attendance Records</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo $record['date']; ?></td>
                                        <td><?php echo $record['time_in']; ?></td>
                                        <td><?php echo $record['time_out']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>