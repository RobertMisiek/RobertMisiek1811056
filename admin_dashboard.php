<?php
include("admin_only.php");
include("config.php");

function clean($conn, $value) {
    return mysqli_real_escape_string($conn, trim($value));
}

function escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (isset($_POST['add_equipment'])) {
    $name = clean($conn, $_POST['name']);
    $category = clean($conn, $_POST['category']);
    $serial_number = clean($conn, $_POST['serial_number']);
    $condition_status = clean($conn, $_POST['condition_status']);
    $total_quantity = (int) $_POST['total_quantity'];
    $available_quantity = $total_quantity;

    $sql = "INSERT INTO equipment (name, category, serial_number, condition_status, total_quantity, available_quantity)
            VALUES ('$name', '$category', '$serial_number', '$condition_status', $total_quantity, $available_quantity)";
    mysqli_query($conn, $sql);

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['update_equipment'])) {
    $equipment_id = (int) $_POST['equipment_id'];
    $name = clean($conn, $_POST['name']);
    $category = clean($conn, $_POST['category']);
    $serial_number = clean($conn, $_POST['serial_number']);
    $condition_status = clean($conn, $_POST['condition_status']);
    $total_quantity = (int) $_POST['total_quantity'];
    $available_quantity = (int) $_POST['available_quantity'];

    if ($available_quantity < 0) {
        $available_quantity = 0;
    }

    if ($available_quantity > $total_quantity) {
        $available_quantity = $total_quantity;
    }

    $sql = "UPDATE equipment
            SET name='$name',
                category='$category',
                serial_number='$serial_number',
                condition_status='$condition_status',
                total_quantity=$total_quantity,
                available_quantity=$available_quantity
            WHERE equipment_id=$equipment_id";
    mysqli_query($conn, $sql);

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['delete_equipment'])) {
    $equipment_id = (int) $_GET['delete_equipment'];
    mysqli_query($conn, "DELETE FROM equipment WHERE equipment_id = $equipment_id");

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['add_user'])) {
    $full_name = clean($conn, $_POST['full_name']);
    $email = clean($conn, $_POST['email']);
    $username = clean($conn, $_POST['username']);
    $password = $_POST['password'];
    $role = clean($conn, $_POST['role']);
    $status = clean($conn, $_POST['status']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (full_name, email, username, password, role, status)
            VALUES ('$full_name', '$email', '$username', '$hashed_password', '$role', '$status')";
    mysqli_query($conn, $sql);

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['update_user'])) {
    $user_id = (int) $_POST['user_id'];
    $full_name = clean($conn, $_POST['full_name']);
    $email = clean($conn, $_POST['email']);
    $username = clean($conn, $_POST['username']);
    $password = $_POST['password'];
    $role = clean($conn, $_POST['role']);
    $status = clean($conn, $_POST['status']);

    if ($password !== "") {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE users
                SET full_name='$full_name',
                    email='$email',
                    username='$username',
                    password='$hashed_password',
                    role='$role',
                    status='$status'
                WHERE user_id=$user_id";
    } else {
        $sql = "UPDATE users
                SET full_name='$full_name',
                    email='$email',
                    username='$username',
                    role='$role',
                    status='$status'
                WHERE user_id=$user_id";
    }

    mysqli_query($conn, $sql);

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['delete_user'])) {
    $user_id = (int) $_GET['delete_user'];

    if ($user_id !== (int) $_SESSION["user_id"]) {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
    }

    header("Location: admin_dashboard.php");
    exit();
}

$edit_equipment = null;
if (isset($_GET['edit_equipment'])) {
    $equipment_id = (int) $_GET['edit_equipment'];
    $result = mysqli_query($conn, "SELECT * FROM equipment WHERE equipment_id = $equipment_id");
    $edit_equipment = mysqli_fetch_assoc($result);
}

$edit_user = null;
if (isset($_GET['edit_user'])) {
    $user_id = (int) $_GET['edit_user'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
    $edit_user = mysqli_fetch_assoc($result);
}

$equipment_result = mysqli_query($conn, "SELECT * FROM equipment ORDER BY equipment_id DESC");
$users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body class="admin-page">
<div class="table-container">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?php echo escape($_SESSION["full_name"]); ?> | <a href="logout.php">Logout</a></p>

    <h2><?php echo $edit_equipment ? "Update Equipment" : "Add Equipment"; ?></h2>
    <form method="POST" action="admin_dashboard.php">
        <?php if ($edit_equipment): ?>
            <input type="hidden" name="equipment_id" value="<?php echo (int)$edit_equipment['equipment_id']; ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Equipment Name" required
               value="<?php echo $edit_equipment ? escape($edit_equipment['name']) : ''; ?>">

        <input type="text" name="category" placeholder="Category" required
               value="<?php echo $edit_equipment ? escape($edit_equipment['category']) : ''; ?>">

        <input type="text" name="serial_number" placeholder="Serial Number" required
               value="<?php echo $edit_equipment ? escape($edit_equipment['serial_number']) : ''; ?>">

        <select name="condition_status" required>
            <option value="New" <?php if ($edit_equipment && $edit_equipment['condition_status'] == 'New') echo 'selected'; ?>>New</option>
            <option value="Good" <?php if ($edit_equipment && $edit_equipment['condition_status'] == 'Good') echo 'selected'; ?>>Good</option>
            <option value="Damaged" <?php if ($edit_equipment && $edit_equipment['condition_status'] == 'Damaged') echo 'selected'; ?>>Damaged</option>
            <option value="Needs Service" <?php if ($edit_equipment && $edit_equipment['condition_status'] == 'Needs Service') echo 'selected'; ?>>Needs Service</option>
        </select>

        <input type="number" name="total_quantity" min="1" placeholder="Total Quantity" required
               value="<?php echo $edit_equipment ? (int)$edit_equipment['total_quantity'] : ''; ?>">

        <input type="number" name="available_quantity" min="0" placeholder="Available Quantity"
               value="<?php echo $edit_equipment ? (int)$edit_equipment['available_quantity'] : ''; ?>">

        <?php if ($edit_equipment): ?>
            <button type="submit" name="update_equipment">Update Equipment</button>
            <p><a href="admin_dashboard.php">Cancel Equipment Edit</a></p>
        <?php else: ?>
            <button type="submit" name="add_equipment">Add Equipment</button>
        <?php endif; ?>
    </form>

    <h2>Equipment List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Serial Number</th>
            <th>Condition</th>
            <th>Total</th>
            <th>Available</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($equipment_result)): ?>
            <tr>
                <td><?php echo (int)$row['equipment_id']; ?></td>
                <td><?php echo escape($row['name']); ?></td>
                <td><?php echo escape($row['category']); ?></td>
                <td><?php echo escape($row['serial_number']); ?></td>
                <td><?php echo escape($row['condition_status']); ?></td>
                <td><?php echo (int)$row['total_quantity']; ?></td>
                <td><?php echo (int)$row['available_quantity']; ?></td>
                <td>
                    <a href="admin_dashboard.php?edit_equipment=<?php echo (int)$row['equipment_id']; ?>">Edit</a> |
                    <a href="admin_dashboard.php?delete_equipment=<?php echo (int)$row['equipment_id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this equipment?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2><?php echo $edit_user ? "Update User" : "Add User"; ?></h2>
    <form method="POST" action="admin_dashboard.php">
        <?php if ($edit_user): ?>
            <input type="hidden" name="user_id" value="<?php echo (int)$edit_user['user_id']; ?>">
        <?php endif; ?>

        <input type="text" name="full_name" placeholder="Full Name" required
               value="<?php echo $edit_user ? escape($edit_user['full_name']) : ''; ?>">

        <input type="email" name="email" placeholder="Email" required
               value="<?php echo $edit_user ? escape($edit_user['email']) : ''; ?>">

        <input type="text" name="username" placeholder="Username" required
               value="<?php echo $edit_user ? escape($edit_user['username']) : ''; ?>">

        <input type="password" name="password"
               placeholder="<?php echo $edit_user ? 'Leave blank to keep current password' : 'Password'; ?>"
               <?php echo $edit_user ? '' : 'required'; ?>>

        <select name="role" required>
            <option value="Admin" <?php if ($edit_user && $edit_user['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
            <option value="User" <?php if ($edit_user && $edit_user['role'] == 'User') echo 'selected'; ?>>User</option>
        </select>

        <select name="status" required>
            <option value="Active" <?php if ($edit_user && $edit_user['status'] == 'Active') echo 'selected'; ?>>Active</option>
            <option value="Inactive" <?php if ($edit_user && $edit_user['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
        </select>

        <?php if ($edit_user): ?>
            <button type="submit" name="update_user">Update User</button>
            <p><a href="admin_dashboard.php">Cancel User Edit</a></p>
        <?php else: ?>
            <button type="submit" name="add_user">Add User</button>
        <?php endif; ?>
    </form>

    <h2>User List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($users_result)): ?>
            <tr>
                <td><?php echo (int)$row['user_id']; ?></td>
                <td><?php echo escape($row['full_name']); ?></td>
                <td><?php echo escape($row['email']); ?></td>
                <td><?php echo escape($row['username']); ?></td>
                <td><?php echo escape($row['role']); ?></td>
                <td><?php echo escape($row['status']); ?></td>
                <td>
                    <a href="admin_dashboard.php?edit_user=<?php echo (int)$row['user_id']; ?>">Edit</a> |
                    <a href="admin_dashboard.php?delete_user=<?php echo (int)$row['user_id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    
</div>
</body>
</html>