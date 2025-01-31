<?php
session_start();
require 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'clock_in':
        $employeeId = $_SESSION['user']['id'];
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO attendance (employee_id, time_in, date) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $employeeId, $time, $date);
        $stmt->execute();
        echo "Clocked in successfully!";
        break;

    case 'clock_out':
        $employeeId = $_SESSION['user']['id'];
        $time = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE employee_id = ? AND date = ?");
        $stmt->bind_param('sis', $time, $employeeId, date('Y-m-d'));
        $stmt->execute();
        echo "Clocked out successfully!";
        break;

    case 'add_employee':
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $password);
        $stmt->execute();
        echo "Employee added successfully!";
        break;

    case 'get_employees':
        $result = $conn->query("SELECT * FROM employees");
        echo '<ul class="list-group">';
        while ($row = $result->fetch_assoc()) {
            echo '<li class="list-group-item">'.$row['name'].' ('.$row['email'].')</li>';
        }
        echo '</ul>';
        break;
}

function db() {
    static $conn;
    if (!$conn) {
        $conn = new mysqli('localhost', 'root', '', 'timekeeping');
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}