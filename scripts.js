document.addEventListener('DOMContentLoaded', function() {
    // Clock In/Out functionality
    if (document.getElementById('clockIn')) {
        document.getElementById('clockIn').addEventListener('click', function() {
            fetch('process.php?action=clock_in')
                .then(response => response.text())
                .then(data => alert(data));
        });

        document.getElementById('clockOut').addEventListener('click', function() {
            fetch('process.php?action=clock_out')
                .then(response => response.text())
                .then(data => alert(data));
        });
    }

    // Admin functionality
    if (document.getElementById('employeeList')) {
        loadEmployees();
        document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_employee');
            
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                loadEmployees();
                $('#addEmployeeModal').modal('hide');
            });
        });
    }

    function loadEmployees() {
        fetch('process.php?action=get_employees')
            .then(response => response.text())
            .then(data => {
                document.getElementById('employeeList').innerHTML = data;
            });
    }
});