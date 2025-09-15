<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "client") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add the new fields to the POST data
    $title = $_POST["title"];
    $desc = $_POST["description"];
    $department = $_POST["department"];
    $action_taken = $_POST["action_taken"];
    $date = $_POST["date"];  // New field
    $w0_number = $_POST["w0_number"];  // New field
    $accomplished_by = $_POST["accomplished_by"];  // New field
    $bfd_code = $_POST["bfd_code"];  // New field
    $requested_by = $_POST["requested_by"];  // New field
    $remarks = $_POST["remarks"];  // New field

    $sql = "INSERT INTO tickets (user_id, title, description, department, action_taken, date, w0_number, accomplished_by, bfd_code, requested_by, remarks) 
        VALUES ('$user_id', '$title', '$desc', '$department', '$action_taken', '$date', '$w0_number', '$accomplished_by', '$bfd_code', '$requested_by', '$remarks')";

    if ($conn->query($sql) === TRUE) {
        echo "Ticket created successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$tickets = $conn->query("SELECT * FROM tickets WHERE user_id='$user_id'");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="style.css">

    <script>
    function updateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            hour: 'numeric', minute: 'numeric', second: 'numeric',
            hour12: true 
        };
        document.getElementById('real-time-clock').textContent = now.toLocaleString('en-US', options);
    }

    setInterval(updateTime, 1000);
    window.onload = updateTime;
    </script>
</head>
<body>
<div class="container">
    <h2>Welcome Client</h2>
    <a href="logout.php" class="logout-button">Logout</a>

    <div id="real-time-clock" style="font-weight: bold; margin-bottom: 15px; font-size: 1.2em;"></div>

    <h3>Create Ticket</h3>
    <form method="POST">
        <input type="text" name="title" placeholder="Fullname" required><br>
        <textarea name="description" placeholder="Status Problem" required></textarea><br>
        <input type="text" name="department" placeholder="Department (e.g. IT, Maintenance)" required><br>
        <textarea name="action_taken" placeholder="Action Taken (Optional)" required></textarea><br>
        
        <!-- New Fields -->
        <input type="date" name="date" placeholder="Date" required><br>
        <input type="text" name="w0_number" placeholder="W0#" /><br>
        <input type="text" name="accomplished_by" placeholder="Accomplished By" /><br>
        <input type="text" name="bfd_code" placeholder="BFD Code" /><br>
        <input type="text" name="requested_by" placeholder="Requested By" /><br>
        <textarea name="remarks" placeholder="Remarks"></textarea><br>

        <button type="submit">Submit</button>
    </form>

    <h3>My Tickets</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Fullname</th>
            <th>Status</th>
            <th>Created</th>
            <th>End At</th>
            <th>Department</th>
            <th>Action Taken</th>
            <th>Status Problem</th>
            <th>Downtime (minutes)</th>
            <th>Date</th>
            <th>W0#</th>
            <th>Accomplished By</th>
            <th>BFD Code</th>
            <th>Requested By</th>
            <th>Remarks</th>
        </tr>
        <?php while($row = $tickets->fetch_assoc()) { 
          
            $created_at = new DateTime($row['created_at']);
            $end_at = new DateTime($row['end_at']);
            $downtime = $created_at->diff($end_at);
            $downtime_minutes = $downtime->h * 60 + $downtime->i; 
        ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
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
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
