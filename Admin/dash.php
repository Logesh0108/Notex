<?php
session_start();
include('../config.php'); // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id']; // Fetch admin ID from session

// Fetch admin username
$query = "SELECT username FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// ✅ Fetch User Creation Data
$user_query = "SELECT MONTH(created_at) AS month, COUNT(*) AS count FROM registered GROUP BY MONTH(created_at)";
$user_result = $conn->query($user_query);
$user_data = [];
while ($row = $user_result->fetch_assoc()) {
    $user_data[$row['month']] = $row['count'];
}
$user_result->free(); 

// ✅ Fetch Note Creation Data
$note_query = "SELECT MONTH(created_at) AS month, COUNT(*) AS count FROM notes GROUP BY MONTH(created_at)";
$note_result = $conn->query($note_query);
$note_data = [];
while ($row = $note_result->fetch_assoc()) {
    $note_data[$row['month']] = $row['count'];
}
$note_result->free(); 

// ✅ Convert data to JSON format for JavaScript
$user_chart_data = json_encode($user_data);
$note_chart_data = json_encode($note_data);

// ✅ Fetch User Details for Table
$users_table_query = "SELECT id, email AS username, password, 
                     (SELECT COUNT(*) FROM notes WHERE notes.user_id = registered.id) AS note_count 
                     FROM registered";

$users_table_result = $conn->query($users_table_query);
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        :root {
            --bg-dark: #0a0a0a;
            --bg-light: #f4f4f4;
            --text-dark: #fff;
            --text-light: #222;
            --accent: #00ff00;
        }

            body {
                font-family: 'Courier New', monospace;
                background-color: var(--bg-dark);
                color: var(--text-dark);
                transition: 0.3s;
            }

            /* Sidebar */
            .sidebar {
                width: 260px;
                height: 100vh;
                position: fixed;
                top: 0;
                left: 0;
                background-color: #111;
                padding: 20px;
                transition: 0.3s;
                z-index: 1000;
            }

            .sidebar a {
                display: block;
                color: var(--accent);
                padding: 10px;
                margin: 10px 0;
                text-decoration: none;
                transition: 0.3s;
            }

            .sidebar a:hover {
                background-color: #00ff00;
                color: #111;
            }
            .brand:hover {
                background-color:rgb(255, 0, 0) !important;
                color: #000 !important;
            }

            .sidebar .brand {
                color:#fff;
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 30px;
                display: block;
                text-align: center;
            }

            .content {
                margin-left: 270px;
                padding: 20px;
                transition: margin-left 0.3s;
            }

            /* Mobile: Hide sidebar by default */
            .menu-container {
                position: relative;
                padding: 20px;
            }

            .menu-toggle {
                display: none;
                font-size: 24px;
                background: none;
                border: none;
                color: var(--accent);
                cursor: pointer;
                z-index: 1100;
            }

            .menu-text {
                display: block;
                font-size: 18px;
                color: var(--accent);
                margin-left: 40px;
            }

            /* Overlay for Mobile */
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                opacity: 0;
                visibility: hidden;
                transition: 0.3s;
                z-index: 999;
            }

            .overlay.active {
                opacity: 1;
                visibility: visible;
            }

            /* Light Mode */
            body.light-mode {
                background-color: var(--bg-light);
                color: var(--text-light);
            }

            body.light-mode .sidebar {
                background-color: #ddd;
            }

            body.light-mode .sidebar a {
                color: #222;
            }

            body.light-mode .sidebar a:hover {
                background-color: #222;
                color: #fff;
            }

            /* Mobile View */
            @media screen and (max-width: 768px) {
                .content {
                    margin-left: 0;
                }

                .sidebar {
                    left: -250px;
                    position: fixed;
                }

                .sidebar.active {
                    left: 0;
                }

                .menu-toggle {
                    display: block;
                    position: absolute;
                    left: 20px;
                    top: 15px;
                }

                .menu-text {
                    display: inline-block;
                    margin-left: 50px;
                    margin-top: 5px;
                }
            }


            .content {
            margin-left: 270px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

       /* Dashboard Container */
.dashboard-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    padding: 20px;
    margin-left: 270px;
}

/* Chart Container */
.chart-container {
    width: 48%;
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    text-align: center;
}

/* Users Table (Styled like a chart) */
.users-table {
    width: 100%;
    background: white;
    color: black;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    display: inline-block;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    white-space: nowrap;
}

th {
    background: #222;
    color: white;
}

/* Responsive Adjustments */
@media screen and (max-width: 992px) {
    .dashboard-container {
        margin-left: 0;
        flex-direction: column;
        align-items: center;
    }

    .chart-container,
    .users-table {
        width: 100%;
    }

    table {
        display: block;
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
    }
}

        span{
            color:red;
            font-weight:800;
            font-family:Rubik;
            text-transform: uppercase;
        }

        

    </style>
</head>
<body>

    <!-- Menu Button and Title -->
    <div class="menu-container">
        <button class="menu-toggle"><i class="fas fa-bars"></i></button>
        
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a class="brand">☠ Admin Panel ☠</a>
        <a href="#" id="analyticsBtn"><i class="fas fa-tachometer-alt"></i> Analytics</a>
        <a href="#" id="usersBtn"><i class="fas fa-users"></i> Users</a>
        <a href="#"><i class="fas fa-file-alt"></i> Reports</a>
        <a href="#"><i class="fas fa-cogs"></i> Settings</a>
        <a href="../logout_confirm.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Overlay -->
    <div class="overlay"></div>

    <!-- Main Content -->
    <div class="content">
        <h1>Welcome,Mr.<span><?php echo htmlspecialchars($username); ?></span></h1>
        <p>Welcome to your control center for the personalized NoteSnap web application. Manage users, track analytics, and optimize your workflow effortlessly.</p>
    </div>
   <!-- Dashboard Analytics (Initially Hidden) -->
<!-- Dashboard Analytics (Initially Hidden) -->
<div class="container mt-5" id="dashboardAnalytics" style="display: none;">
    <h2 class="text-center text-light">Dashboard Analytics</h2>
    
    <div class="dashboard-container">
        <!-- User Creation Chart -->
        <div class="chart-container">
            <h4 class="text-center">User Creation Per Month</h4>
            <canvas id="userChart"></canvas>
        </div>
        
        <!-- Note Creation Chart -->
        <div class="chart-container">
            <h4 class="text-center">Note Creation Per Month</h4>
            <canvas id="noteChart"></canvas>
        </div>

        <!-- Users Table (Aligned like a chart) -->
        <div class="users-table">
            <h4 class="text-center">Registered Users</h4>
            <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username (Email)</th>
                    <th>Password</th>
                    <th>Note Count</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users_table_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['password']); ?></td> <!-- ⚠️ WARNING: Only if passwords are not hashed -->
                        <td><?php echo $row['note_count']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
       
    </div>
</div>
<div class="container mt-4" id="users" style="display: none;">
    <div class="users-table">
            <h4 class="text-center">Registered Users</h4>
            <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username (Email)</th>
                    <th>Password</th>
                    <th>Note Count</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users_table_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['password']); ?></td> <!-- ⚠️ WARNING: Only if passwords are not hashed -->
                        <td><?php echo $row['note_count']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>


    <script>
    document.getElementById("analyticsBtn").addEventListener("click", function () {
        const analyticsSection = document.getElementById("dashboardAnalytics");
        analyticsSection.style.display = (analyticsSection.style.display === "none") ? "block" : "none";
    });
    document.getElementById("usersBtn").addEventListener("click", function () {
        const analyticsSection = document.getElementById("users");
        analyticsSection.style.display = (analyticsSection.style.display === "none") ? "block" : "none";
    });
</script>

    <script>
        const menuToggle = document.querySelector(".menu-toggle");
        const sidebar = document.querySelector(".sidebar");
        const overlay = document.querySelector(".overlay");

        menuToggle.addEventListener("click", () => {
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
        const userChartData = <?php echo $user_chart_data; ?>;
        const noteChartData = <?php echo $note_chart_data; ?>;
        const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        function processChartData(data) {
            let chartData = Array(12).fill(0); // Initialize array with 12 months
            Object.keys(data).forEach(month => {
                chartData[month - 1] = data[month];
            });
            return chartData;
        }

        // User Chart
        new Chart(document.getElementById("userChart"), {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Users Created",
                    data: processChartData(userChartData),
                    backgroundColor: "rgba(75, 192, 192, 0.6)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 1
                }]
            },
            options: { responsive: true }
        });

        // Note Chart
        new Chart(document.getElementById("noteChart"), {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Notes Created",
                    data: processChartData(noteChartData),
                    backgroundColor: "rgba(255, 99, 132, 0.6)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 1
                }]
            },
            options: { responsive: true }
        });
        showDashboard();
    </script>

</body>
</html>
