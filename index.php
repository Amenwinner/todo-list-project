
    <?php
// 1. DATABASE CONFIGURATION
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todo_app"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. CREATE (Add a New Task) - UNCHANGED
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_name'])) {
    $task_name = trim($_POST['task_name']);

    if (!empty($task_name)) {
        $stmt = $conn->prepare("INSERT INTO tasks (name) VALUES (?)");
        $stmt->bind_param("s", $task_name);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "<p style='color: red;'>Error adding task: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// 3. UPDATE (Mark Task as Done/Undone) - NEW LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_id'], $_POST['action'])) {
    $task_id = (int)$_POST['task_id'];
    $action = $_POST['action'];

    // Determine the new status based on the action button clicked
    $new_status = ($action === 'done') ? 1 : 0; 
    
    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE tasks SET is_done = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $task_id); // 'i' means integer

    if ($stmt->execute()) {
        // Success! Redirect to refresh the list
        header("Location: index.php");
        exit();
    } else {
        echo "<p style='color: red;'>Error updating task: " . $stmt->error . "</p>";
    }
    $stmt->close();
}


// 4. READ (Fetch Existing Tasks) - UNCHANGED
$tasks = $conn->query("SELECT id, name, is_done FROM tasks ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>My Simple To-Do List</h1>

        <form action="index.php" method="POST" class="add-task-form">
            <input type="text" name="task_name" placeholder="Enter a new task..." required>
            <button type="submit">Add Task</button>
        </form>

        <ul class="task-list">
            <?php
            if ($tasks->num_rows > 0) {
                while($row = $tasks->fetch_assoc()) {
                    // Check if the task is done to apply a CSS class
                    $is_done = $row['is_done'];
                    $class = $is_done ? 'done' : '';
                    
                    echo "<li class='task-item $class'>";
                    echo "<span>" . htmlspecialchars($row['name']) . "</span>";
                    
                    // --- NEW: Action Buttons ---
                    echo "<div class='actions'>";
                    
                    // The form sends the task ID and the action (done/undone)
                    echo "<form method='POST' action='index.php' style='display:inline;'>";
                    echo "<input type='hidden' name='task_id' value='{$row['id']}'>";
                    
                    if ($is_done) {
                        // If it's done, show button to mark it 'undone'
                        echo "<button type='submit' name='action' value='undone' class='btn-undone'>Undo</button>";
                    } else {
                        // If it's NOT done, show button to mark it 'done'
                        echo "<button type='submit' name='action' value='done' class='btn-done'>Done</button>";
                    }
                    echo "</form>";
                    
                    // --- Placeholder for Delete button ---
                    // echo "<button class='btn-delete'>X</button>";
                    
                    echo "</div>"; // end actions div
                    echo "</li>";
                }
            } else {
                echo "<p class='no-tasks'>No tasks yet! Add one above.</p>";
            }
            ?>
        </ul>
    </div>

</body>
</html>
<?php 
// Close the database connection
$conn->close(); 
?>
