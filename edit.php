<?php
include 'db.php';

if (isset($_GET['id'])) {
    $empid = (int)$_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM employee WHERE empid = $empid");
    $row = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empid = $_POST['empid'];
    $empname = $_POST['empname'];
    $deptid = $_POST['deptid'];
    $emp_mobile = $_POST['emp_mobile'];
    $emp_salary = $_POST['emp_salary'];
    $emp_status = $_POST['emp_status'];
    $emp_start = $_POST['emp_start'];
    $emp_endson = $_POST['emp_endson'];
    $filename = $_POST['old_picture']; // default to old picture

    // If a new image is uploaded
    if ($_FILES['emp_picture']['name']) {
        $tmp_name = $_FILES['emp_picture']['tmp_name'];
        $imageData = file_get_contents($tmp_name);

        // Create image from string
        $img = imagecreatefromstring($imageData);

        if ($img !== false) {
            $filename = $empid . ".jpg"; // rename to empid.jpg
            $destination = "uploads/" . $filename;
            imagejpeg($img, $destination, 90); // Save as .jpg
            imagedestroy($img);
        } else {
            echo "Error: Invalid image file.";
            exit;
        }
    }

    $sql = "UPDATE employee SET 
                empname = '$empname',
                deptid = '$deptid',
                emp_mobile = '$emp_mobile',
                emp_salary = '$emp_salary',
                emp_status = '$emp_status',
                emp_picture = '$filename',
                emp_start = '$emp_start',
                emp_endson = '$emp_endson'
            WHERE empid = $empid";

    if (mysqli_query($conn, $sql)) {
        header("Location: list.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px #ccc;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input[type="text"], input[type="date"], select, input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }
        button:hover {
            background: #218838;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        img {
            margin-top: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="list.php">← Back to Employee List</a>

    <h2>Edit Employee</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="empid" value="<?= $row['empid'] ?>">
        <input type="hidden" name="old_picture" value="<?= $row['emp_picture'] ?>">

        <label>Name:</label>
        <input type="text" name="empname" value="<?= $row['empname'] ?>" required>

        <label>Department:</label>
        <select name="deptid" required>
            <?php
            $depts = mysqli_query($conn, "SELECT * FROM department");
            while ($dept = mysqli_fetch_assoc($depts)) {
                $selected = $dept['deptid'] == $row['deptid'] ? 'selected' : '';
                echo "<option value='{$dept['deptid']}' $selected>{$dept['deptname']}</option>";
            }
            ?>
        </select>

        <label>Mobile:</label>
        <input type="text" name="emp_mobile" value="<?= $row['emp_mobile'] ?>" required>

        <label>Salary:</label>
        <input type="text" name="emp_salary" value="<?= $row['emp_salary'] ?>" required>

        <label>Status:</label>
        <select name="emp_status" required>
            <option value="1" <?= $row['emp_status'] == '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $row['emp_status'] == '0' ? 'selected' : '' ?>>Inactive</option>
        </select>

        <label>Start Date:</label>
        <input type="date" name="emp_start" value="<?= $row['emp_start'] ?>">

        <label>End Date:</label>
        <input type="date" name="emp_endson" value="<?= $row['emp_endson'] ?>">

        <label>Picture:</label>
        <input type="file" name="emp_picture">
        <?php if (!empty($row['emp_picture'])): ?>
            <img src="uploads/<?= $row['emp_picture'] ?>" width="80">
        <?php endif; ?>

        <button type="submit">Update Employee</button>
    </form>
</div>
</body>
</html>
