<?php
include 'db.php';

class Formatter {
    public static function formatCurrency($number) {
        return '₹' . number_format($number, 2);
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$dept_filter = isset($_GET['dept_filter']) ? mysqli_real_escape_string($conn, $_GET['dept_filter']) : '';


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;


$where = "WHERE 1";
if (!empty($search)) {
    $where .= " AND (e.empname LIKE '%$search%' OR e.emp_mobile LIKE '%$search%')";
}
if (!empty($dept_filter)) {
    $where .= " AND e.deptid = '$dept_filter'";
}


$countSql = "SELECT COUNT(*) as total FROM employee e $where";
$countResult = mysqli_query($conn, $countSql);
$rowCount = mysqli_fetch_assoc($countResult);
$totalRecords = $rowCount['total'];
$totalPages = ceil($totalRecords / $limit);


$sql = "SELECT e.*, d.deptname 
        FROM employee e 
        LEFT JOIN department d ON e.deptid = d.deptid 
        $where 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
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
        a.btn, .btn-add { background: #007bff; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; }
        a.btn:hover, .btn-add:hover { background: #0056b3; }
        .btn-add { background: #28a745; margin-bottom: 15px; display: inline-block; }
        .status-toggle { padding: 6px 12px; border-radius: 4px; font-weight: bold; color: #fff; text-decoration: none; }
        .status-active { background-color: green; }
        .status-inactive { background-color: red; }
        .pagination a { margin: 0 5px; padding: 5px 10px; text-decoration: none; background: #ddd; border-radius: 4px; }
        .pagination a.active { background: #007bff; color: white; }
    </style>
</head>
<body>

<h2>Employee List</h2>


<form method="GET" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search by name or mobile" value="<?= htmlspecialchars($search) ?>">
    
    <select name="dept_filter">
        <option value="">All Departments</option>
        <?php
        $deptResult = mysqli_query($conn, "SELECT * FROM department");
        while ($dept = mysqli_fetch_assoc($deptResult)) {
            $selected = ($dept_filter == $dept['deptid']) ? 'selected' : '';
            echo "<option value='{$dept['deptid']}' $selected>{$dept['deptname']}</option>";
        }
        ?>
    </select>

    <button type="submit">Search</button>
</form>

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

    <?php if (mysqli_num_rows($result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= $row['empid'] ?></td>
        <td><?= $row['empname'] ?></td>
        <td><?= $row['deptname'] ?></td>
        <td><?= $row['emp_mobile'] ?></td>
        <td><?= Formatter::formatCurrency($row['emp_salary']) ?></td>

        <td>
            <a href="javascript:void(0);" 
               class="status-toggle <?= $row['emp_status'] == 1 ? 'status-active' : 'status-inactive' ?>" 
               data-id="<?= $row['empid'] ?>" 
               data-status="<?= $row['emp_status'] ?>">
               <?= $row['emp_status'] == 1 ? 'Active' : 'Inactive' ?>
            </a>
        </td>
        <td><?= $row['emp_start'] ?></td>
        <td><?= $row['emp_endson'] ?></td>
        <td>
            <?php if (!empty($row['emp_picture']) && file_exists("uploads/" . $row['emp_picture'])): ?>
                <img src="uploads/<?= $row['emp_picture'] ?>?v=<?= time() ?>" width="50">
            <?php else: ?>
                N/A
            <?php endif; ?>
        </td>
        <td>
            <a class="btn" href="edit.php?id=<?= $row['empid'] ?>">Edit</a>
            <a class="btn" href="delete.php?id=<?= $row['empid'] ?>" onclick="return confirm('Are you sure?');">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="10">No results found.</td>
    </tr>
<?php endif; 
?>

</table>


<div class="pagination" style="margin-top: 20px;">
<?php
for ($i = 1; $i <= $totalPages; $i++) {
    $link = "?page=$i";
    if (!empty($search)) $link .= "&search=" . urlencode($search);
    if (!empty($dept_filter)) $link .= "&dept_filter=" . urlencode($dept_filter);

    echo "<a class='" . ($i == $page ? "active" : "") . "' href='$link'>$i</a>";
}
?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on('click', '.status-toggle', function() {
    var el = $(this);
    var empId = el.data('id');
    var currentStatus = el.data('status');
    var newStatus = currentStatus == 1 ? 0 : 1;

    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: { empid: empId, status: newStatus },
        success: function(response) {
            if (response.trim() === "success") {
                el.text(newStatus == 1 ? 'Active' : 'Inactive');
                el.data('status', newStatus);
                el.removeClass('status-active status-inactive');
                el.addClass(newStatus == 1 ? 'status-active' : 'status-inactive');
            } else {
                alert("Failed to update status.");
            }
        }
    });
});
</script>

</body>
</html>
