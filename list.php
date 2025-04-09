<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee List</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
        }
        img {
            border-radius: 4px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #ccc;
            text-decoration: none;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Employee List</h2>

<form method="GET" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search by name or mobile" 
           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

    <select name="dept_filter">
        <option value="">All Departments</option>
        <?php
        $deptResult = mysqli_query($conn, "SELECT * FROM department");
        while ($dept = mysqli_fetch_assoc($deptResult)) {
            $selected = (isset($_GET['dept_filter']) && $_GET['dept_filter'] == $dept['deptid']) ? 'selected' : '';
            echo "<option value='" . $dept['deptid'] . "' $selected>" . $dept['deptname'] . "</option>";
        }
        ?>
    </select>

    <button type="submit">Search</button>
</form>

<a href="add.php" style="
    display: inline-block;
    margin-bottom: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 4px;
    font-family: Arial, sans-serif;
">+ Add New Employee</a>


<?php
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

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Mobile</th>
            <th>Salary</th>
            <th>Picture</th>
            <th>Status</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['empid'] . "</td>";
                echo "<td>" . $row['empname'] . "</td>";
                echo "<td>" . $row['deptname'] . "</td>";
                echo "<td>" . $row['emp_mobile'] . "</td>";
                echo "<td>" . $row['emp_salary'] . "</td>";
                echo "<td><img src='uploads/" . $row['emp_picture'] . "' width='50'></td>";
                echo "<td>" . ($row['emp_status'] == '1' ? 'Active' : 'Inactive') . "</td>";
                echo "<td><a href='edit.php?id=" . $row['empid'] . "'>Edit</a></td>";
                echo "<td><a href='delete.php?id=" . $row['empid'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No employees found.</td></tr>";
        }
        ?>
    </tbody>
</table>


<div class="pagination" style="margin-top: 20px;">
    <?php
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $page) ? 'active' : '';
        $link = "?page=$i";

        if (!empty($search)) {
            $link .= "&search=" . urlencode($search);
        }
        if (!empty($dept_filter)) {
            $link .= "&dept_filter=" . urlencode($dept_filter);
        }

        echo "<a class='$active' href='$link'>$i</a>";
    }
    ?>
</div>

</body>
</html>
