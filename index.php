<?php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Student.php';

$connection = getDbConnection();
$repository = new StudentRepository($connection);

$message = '';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $repository->delete($id);
        $message = 'Student record deleted.';
    }
}

$students = $repository->getAll();
$totalStudents = count($students);
$averageNameLength = 0;

if ($totalStudents > 0) {
    $sum = 0;
    foreach ($students as $student) {
        $sum += strlen($student->name);
    }
    $averageNameLength = round($sum / $totalStudents, 2);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - Student Record</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { margin-bottom: 10px; }
        .buttons { margin-bottom: 20px; }
        .buttons a { display: inline-block; padding: 8px 16px; margin-right: 10px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 4px; }
        .buttons a:hover { background-color: #0056b3; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cccccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { margin-bottom: 10px; color: green; }
        .stats { margin-bottom: 20px; }
        .actions .btn { display: inline-block; padding: 6px 10px; margin-right: 4px; border-radius: 4px; text-decoration: none; color: #ffffff; }
        .actions .btn-edit { background-color: #28a745; }
        .actions .btn-delete { background-color: #dc3545; }
        .actions .btn-edit:hover { background-color: #218838; }
        .actions .btn-delete:hover { background-color: #c82333; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .top-bar .title { font-size: 24px; font-weight: bold; }
        .top-bar .right a { margin-left: 10px; }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="title">Student Record</div>
        <div class="right">
            <a href="student_form.php" class="btn btn-edit">Add Student</a>
            <a href="logout.php" class="btn btn-delete">Logout</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="stats">
        <strong>Total students:</strong> <?php echo $totalStudents; ?><br>
    </div>

    <?php if ($totalStudents === 0): ?>
        <p>No student records found. Click "Create Student" to add one.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Number</th>
                    <th>Name (Uppercase)</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo $student->id; ?></td>
                    <td><?php echo htmlspecialchars($student->studentNumber); ?></td>
                    <td><?php echo htmlspecialchars($student->getUppercaseName()); ?></td>
                    <td><?php echo htmlspecialchars(strtolower($student->email)); ?></td>
                    <td><?php echo htmlspecialchars($student->getCourseSummary()); ?></td>
                    <td class="actions">
                        <a href="student_form.php?id=<?php echo $student->id; ?>" class="btn btn-edit">Edit</a>
                        <a href="index.php?action=delete&id=<?php echo $student->id; ?>" class="btn btn-delete" onclick="return confirm('Delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
