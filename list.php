<?php
include 'db.php';
$result = mysqli_query($conn, "
    SELECT e.*, d.deptname 
    FROM employee e 
    LEFT JOIN department d ON e.deptid = d.deptid
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee List</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #333; color: white; }
        a.btn { background: #007bff; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; }
        a.btn:hover { background: #0056b3; }
        img { border-radius: 4px; }
        .btn-add {
        display: inline-block;
        background: #28a745;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 15px;
        }
    .btn-add:hover {
        background: #218838;
    }
    .status-toggle {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: bold;
        color: #fff;
        display: inline-block;
        text-decoration: none;
    }
    .status-active {
        background-color: green;
    }
    .status-inactive {
        background-color: red;
    }
    </style>
</head>
<body>
    <h2>Employee List</h2>
    <a href="add.php" class="btn-add">+ Add New Employee</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Dept</th>
            <th>Mobile</th>
            <th>Salary</th>
            <th>Status</th>
            <th>Start</th>
            <th>End</th>
            <th>Picture</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['empid'] ?></td>
            <td><?= $row['empname'] ?></td>
            <td><?= $row['deptname'] ?></td>
            <td><?= $row['emp_mobile'] ?></td>
            <td><?= $row['emp_salary'] ?></td>
            <td>
                <a href="javascript:void(0);" 
                    class="status-toggle <?= $row['emp_status'] == 1 ? 'status-active' : 'status-inactive' ?>" 
                    data-id="<?= $row['empid'] ?>" 
                    data-status="<?= $row['emp_status'] ?>">
                    <?= $row['emp_status'] == 1 ? 'Active' : ($row['emp_status'] === 0 ? 'Inactive' : 'Inactive') ?>
                 </a>
            </td>

            <td><?= $row['emp_start'] ?></td>
            <td><?= $row['emp_endson'] ?></td>
            <td>
                <?php if (!empty($row['emp_picture']) && file_exists("uploads/" . $row['emp_picture'])): ?>
                    <img src="uploads/<?= $row['emp_picture'] ?>?v=<?= time() ?>" width="50">
                <?php else: ?>
                   Inactive
                <?php endif; ?>
            </td>
            <td>
                <a class="btn" href="edit.php?id=<?= $row['empid'] ?>">Edit</a>
                <a class="btn btn-delete" href="delete.php?id=<?= $row['empid'] ?>" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
console.log("JavaScript is working!");  
$(document).on('click', '.status-toggle', function() {
    var el = $(this);
    var empId = el.data('id');
    var currentStatus = el.data('status');
    var newStatus = currentStatus == 1 ? 0 : 1;  

    console.log("Emp ID: " + empId + ", Current Status: " + currentStatus + ", New Status: " + newStatus);

    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: { empid: empId, status: newStatus },
        success: function(response) {
            console.log("Response from server:", response); 
            if (response.trim() === "success") {
                
                el.text(newStatus == 1 ? 'Active' : 'Inactive');
                el.data('status', newStatus);
                el.removeClass('status-active status-inactive');
                el.addClass(newStatus == 1 ? 'status-active' : 'status-inactive');
            } else {
                alert("Failed to update status.");
            }
        },
        error: function(xhr, status, error) {
            console.log("AJAX Error:", error);  
        }
    });
});

</script>

</body>
</html>
