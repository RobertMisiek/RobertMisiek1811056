<?php
include("auth.php");
include("config.php");

if ($_SESSION["role"] != "User") {
    header("Location: admin_dashboard.php");
    exit();
}
$user_id = (int) $_SESSION["user_id"];
$max_rentals = 3;
$message = "";
mysqli_query($conn, "UPDATE rentals 
                     SET status = 'Overdue' 
                     WHERE status = 'Rented' 
                     AND due_date < CURDATE()");

if (isset($_GET['rent_id'])) {
    $equipment_id = (int) $_GET['rent_id'];
    $active_rentals_result = mysqli_query($conn, "
        SELECT COUNT(*) AS total_active
        FROM rentals
        WHERE user_id = $user_id
        AND status IN ('Rented', 'Overdue')
    ");
    $active_rentals_row = mysqli_fetch_assoc($active_rentals_result);
    $active_rentals = (int) $active_rentals_row['total_active'];
    $overdue_result = mysqli_query($conn, "
        SELECT COUNT(*) AS total_overdue
        FROM rentals
        WHERE user_id = $user_id
        AND status = 'Overdue'
    ");
    $overdue_row = mysqli_fetch_assoc($overdue_result);
    $overdue_count = (int) $overdue_row['total_overdue'];
    if ($overdue_count > 0) {
        $message = "You cannot rent new equipment because you have overdue rentals.";
    } elseif ($active_rentals >= $max_rentals) {
        $message = "You have reached the maximum rental limit of $max_rentals items.";
    } else {
        $check = mysqli_query($conn, "
            SELECT * FROM equipment 
            WHERE equipment_id = $equipment_id 
            AND available_quantity > 0
        ");
        if (mysqli_num_rows($check) > 0) {
            $today = date("Y-m-d");
            $due_date = date("Y-m-d", strtotime("+7 days"));

            mysqli_query($conn, "
                INSERT INTO rentals (user_id, equipment_id, rental_date, due_date, status)
                VALUES ($user_id, $equipment_id, '$today', '$due_date', 'Rented')
            ");

            mysqli_query($conn, "
                UPDATE equipment
                SET available_quantity = available_quantity - 1
                WHERE equipment_id = $equipment_id
            ");
            $message = "Equipment rented successfully.";
        } else {
            $message = "This equipment is not available.";
        }
    }
}
if (isset($_GET['return_id'])) {
    $rental_id = (int) $_GET['return_id'];
    $today = date("Y-m-d");
    $result = mysqli_query($conn, "
        SELECT equipment_id 
        FROM rentals 
        WHERE rental_id = $rental_id
        AND user_id = $user_id
        AND status IN ('Rented', 'Overdue')
    ");
    if ($row = mysqli_fetch_assoc($result)) {
        $equipment_id = (int) $row['equipment_id'];

        mysqli_query($conn, "
            UPDATE rentals
            SET return_date = '$today', status = 'Returned'
            WHERE rental_id = $rental_id
        ");
        mysqli_query($conn, "
            UPDATE equipment
            SET available_quantity = available_quantity + 1
            WHERE equipment_id = $equipment_id
        ");
        $message = "Equipment returned successfully.";
    }
}
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';
$condition = isset($_GET['condition']) ? mysqli_real_escape_string($conn, trim($_GET['condition'])) : '';
$equipment_sql = "SELECT * FROM equipment WHERE available_quantity > 0";
if ($search != '') {
    $equipment_sql .= " AND (name LIKE '%$search%' OR category LIKE '%$search%' OR serial_number LIKE '%$search%')";
}
if ($category != '') {
    $equipment_sql .= " AND category = '$category'";
}
if ($condition != '') {
    $equipment_sql .= " AND condition_status = '$condition'";
}
$equipment_sql .= " ORDER BY name ASC";
$equipment_result = mysqli_query($conn, $equipment_sql);
$categories_result = mysqli_query($conn, "SELECT DISTINCT category FROM equipment ORDER BY category ASC");
$summary_result = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN status IN ('Rented', 'Overdue') THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) AS overdue_count
    FROM rentals
    WHERE user_id = $user_id
");
$summary = mysqli_fetch_assoc($summary_result);
$active_count = (int) $summary['active_count'];
$overdue_count = (int) $summary['overdue_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    </head>
<body class="users-page">
<div class="table-container">
    <h1>User Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?> | <a href="logout.php">Logout</a></p>
    <p><strong>Maximum rentals allowed:</strong> <?php echo $max_rentals; ?></p>
    <p><strong>Current active rentals:</strong> <?php echo $active_count; ?></p>
    <p><strong>Overdue rentals:</strong> <?php echo $overdue_count; ?></p>
    <?php if ($message != ""): ?>
        <p class="error"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <h2>Search Equipment</h2>
    <form method="GET" action="user_dashboard.php">
        <input type="text" name="search" placeholder="Search by name, category or serial number"
               value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">All Categories</option>
            <?php while ($cat = mysqli_fetch_assoc($categories_result)) { ?>
                <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                    <?php if ($category == $cat['category']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['category']); ?>
                </option>
            <?php } ?>
        </select>
        <select name="condition">
            <option value="">All Conditions</option>
            <option value="New" <?php if ($condition == 'New') echo 'selected'; ?>>New</option>
            <option value="Good" <?php if ($condition == 'Good') echo 'selected'; ?>>Good</option>
            <option value="Damaged" <?php if ($condition == 'Damaged') echo 'selected'; ?>>Damaged</option>
            <option value="Needs Service" <?php if ($condition == 'Needs Service') echo 'selected'; ?>>Needs Service</option>
        </select>
        <button type="submit">Search</button>
    </form>
    <p><a href="user_dashboard.php">Clear Filters</a></p>
    <h2>Available Equipment</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Serial Number</th>
            <th>Condition</th>
            <th>Available</th>
            <th>Action</th>
        </tr>
        <?php
        if (mysqli_num_rows($equipment_result) > 0) {
            while ($row = mysqli_fetch_assoc($equipment_result)) {
                echo "<tr>
                        <td>{$row['equipment_id']}</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['category']) . "</td>
                        <td>" . htmlspecialchars($row['serial_number']) . "</td>
                        <td>" . htmlspecialchars($row['condition_status']) . "</td>
                        <td>{$row['available_quantity']}</td>
                        <td><a href='user_dashboard.php?rent_id={$row['equipment_id']}'>Rent</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No equipment found.</td></tr>";
        }
        ?>
    </table>
    <h2>My Rentals</h2>
    <table>
        <tr>
            <th>Rental ID</th>
            <th>Equipment</th>
            <th>Rental Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        $rentals = mysqli_query($conn, "
            SELECT rentals.*, equipment.name
            FROM rentals
            JOIN equipment ON rentals.equipment_id = equipment.equipment_id
            WHERE rentals.user_id = $user_id
            ORDER BY rentals.rental_id DESC
        ");
        while ($row = mysqli_fetch_assoc($rentals)) {
            echo "<tr>
                    <td>{$row['rental_id']}</td>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>{$row['rental_date']}</td>
                    <td>{$row['due_date']}</td>
                    <td>" . ($row['return_date'] ? $row['return_date'] : '-') . "</td>
                    <td>{$row['status']}</td>
                    <td>";

            if ($row['status'] == 'Rented' || $row['status'] == 'Overdue') {
                echo "<a href='user_dashboard.php?return_id={$row['rental_id']}'>Return</a>";
            } else {
                echo "-";
            }
            echo "</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>