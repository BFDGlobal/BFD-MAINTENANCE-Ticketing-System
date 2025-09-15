<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $status = $_POST["status"];
    $end_at = $_POST["end_at"];
    $department = isset($_POST["department"]) ? $_POST["department"] : '';
    $action_taken = isset($_POST["action_taken"]) ? $_POST["action_taken"] : '';
    $description = isset($_POST["description"]) ? $_POST["description"] : '';
    $date = $_POST["date"];
    $w0_number = $_POST["w0_number"];
    $accomplished_by = $_POST["accomplished_by"];
    $bfd_code = $_POST["bfd_code"];
    $requested_by = $_POST["requested_by"];
    $remarks = $_POST["remarks"];

    // Calculate downtime from created_at and end_at
    $sql = "SELECT created_at FROM tickets WHERE id = $id";
    $result = $conn->query($sql);
    $ticket = $result->fetch_assoc();
    $created_at = new DateTime($ticket['created_at']);
    
    if (!empty($end_at)) {
        $end_at_obj = new DateTime($end_at);
        $end_at_str = $end_at_obj->format('Y-m-d H:i:s');
        $downtime = $created_at->diff($end_at_obj);
        $downtime_minutes = $downtime->h * 60 + $downtime->i;
    } else {
        $end_at_str = NULL;
        $downtime_minutes = NULL;
    }

    $sql = $conn->prepare("UPDATE tickets 
        SET status=?, end_at=?, department=?, action_taken=?, description=?, 
            date=?, w0_number=?, accomplished_by=?, bfd_code=?, requested_by=?, remarks=?, downtime=? 
        WHERE id=?");

    $sql->bind_param("sssssssssssii", $status, $end_at_str, $department, $action_taken, $description, 
                    $date, $w0_number, $accomplished_by, $bfd_code, $requested_by, $remarks, $downtime_minutes, $id);

    if ($sql->execute()) {
        echo "Ticket updated successfully!";
    } else {
        echo "Error: " . $sql->error;
    }
}

if (isset($_POST["delete"])) {
    $id = $_POST["id"];
    $sql = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $sql->bind_param("i", $id);

    if ($sql->execute()) {
        echo "Ticket deleted successfully!";
        header("Location: admin.php");  
        exit;
    } else {
        echo "Error: " . $sql->error;
    }
}

$tickets = $conn->query("SELECT tickets.*, users.username FROM tickets JOIN users ON tickets.user_id=users.id ORDER BY tickets.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFD-Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS for Corporate Style -->
    <link rel="stylesheet" href="style.css">
    <script>
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: 'numeric', second: 'numeric',
                hour12: true 
            };
            const formatted = now.toLocaleString('en-US', options);
            document.getElementById('real-time-clock').textContent = formatted;
        }

        setInterval(updateTime, 1000);
        window.onload = updateTime; 
    </script>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center text-primary">Welcome Back, Admin!</h2>
    <a href="logout.php" class="btn btn-danger mb-3 float-end">Logout</a>
    <div id="real-time-clock" class="text-center mb-3" style="font-weight: bold; font-size: 1.2em;"></div>
    
    <h3 class="mb-4 text-secondary">All Tickets</h3>
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Title</th>
                <th>Status</th>
                <th>Created</th>
                <th>End At</th>
                <th>Department</th>
                <th>Action Taken</th>
                <th>Description</th>
                <th>Downtime</th>
                <th>DATE</th>
                <th>W0#</th>
                <th>Accomplished By</th>
                <th>BFD Code</th>
                <th>Requested By</th>
                <th>Remarks</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $tickets->fetch_assoc()) { 
                $created_at = new DateTime($row['created_at']);
                $end_at = new DateTime($row['end_at']);
                $downtime = $created_at->diff($end_at);
                $downtime_minutes = $downtime->h * 60 + $downtime->i;
            ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['end_at']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['action_taken']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $downtime_minutes ?> minutes</td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['w0_number']) ?></td>
                <td><?= htmlspecialchars($row['accomplished_by']) ?></td>
                <td><?= htmlspecialchars($row['bfd_code']) ?></td>
                <td><?= htmlspecialchars($row['requested_by']) ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                        <select name="status" class="form-select mb-2">
                            <option value="Open" <?= $row['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                            <option value="In Progress" <?= $row['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Closed" <?= $row['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                        <input type="text" name="department" value="<?= htmlspecialchars($row['department']) ?>" class="form-control mb-2" placeholder="Department" required>
                        <textarea name="action_taken" class="form-control mb-2" placeholder="Action Taken"><?= htmlspecialchars($row['action_taken']) ?></textarea>
                        <textarea name="description" class="form-control mb-2" placeholder="Description"><?= htmlspecialchars($row['description']) ?></textarea>
                        <input type="datetime-local" name="end_at" value="<?= htmlspecialchars($row['end_at']) ?>" class="form-control mb-2">
                        <input type="date" name="date" value="<?= htmlspecialchars($row['date']) ?>" class="form-control mb-2">
                        <input type="text" name="w0_number" value="<?= htmlspecialchars($row['w0_number']) ?>" class="form-control mb-2" placeholder="W0#">
                        <input type="text" name="accomplished_by" value="<?= htmlspecialchars($row['accomplished_by']) ?>" class="form-control mb-2" placeholder="Accomplished By">
                        <input type="text" name="bfd_code" value="<?= htmlspecialchars($row['bfd_code']) ?>" class="form-control mb-2" placeholder="BFD Code">
                        <input type="text" name="requested_by" value="<?= htmlspecialchars($row['requested_by']) ?>" class="form-control mb-2" placeholder="Requested By">
                        <textarea name="remarks" class="form-control mb-2" placeholder="Remarks"><?= htmlspecialchars($row['remarks']) ?></textarea>
                        <input type="number" name="downtime" value="<?= $downtime_minutes ?>" class="form-control mb-2" placeholder="Downtime (minutes)" min="0" readonly>
                        <button type="submit" name="update" class="btn btn-primary btn-block">Update</button>
                    </form>
                </td>
                <td>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                        <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
