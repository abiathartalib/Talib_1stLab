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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student = null;
$isEdit = false;
$error = '';

if ($id > 0) {
    $student = $repository->findById($id);
    if ($student) {
        $isEdit = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idFromPost = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $studentNumber = isset($_POST['student_number']) ? trim($_POST['student_number']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $course = isset($_POST['course']) ? trim($_POST['course']) : '';

    if ($studentNumber === '' || $name === '' || $email === '' || $course === '') {
        $error = 'All fields are required.';
    } else {
        if ($idFromPost > 0) {
            $student = new Student($idFromPost, $studentNumber, $name, $email, $course);
            $repository->update($student);
        } else {
            $student = new Student(0, $studentNumber, $name, $email, $course);
            $repository->create($student);
        }
        header('Location: index.php');
        exit;
    }

    $isEdit = $idFromPost > 0;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $isEdit ? 'Edit Student' : 'Add Student'; ?></title>
    <link rel="stylesheet" href="assets/css/student_form.css">
</head>
<body>
    <h1><?php echo $isEdit ? 'Edit Student Record' : 'Add Student Record'; ?></h1>
    <p><a href="index.php">Back to Student Record</a></p>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="student_form.php">
        <input type="hidden" name="id" value="<?php echo $isEdit && $student ? $student->id : 0; ?>">

        <label for="student_number">ID Number</label>
        <input
            type="text"
            id="student_number"
            name="student_number"
            value="<?php echo $student ? htmlspecialchars($student->studentNumber) : ''; ?>"
        >

        <label for="name">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?php echo $student ? htmlspecialchars($student->name) : ''; ?>"
        >

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?php echo $student ? htmlspecialchars($student->email) : ''; ?>"
        >

        <label for="course">Course</label>
        <input
            type="text"
            id="course"
            name="course"
            value="<?php echo $student ? htmlspecialchars($student->course) : ''; ?>"
        >

        <div class="buttons">
            <input type="submit" value="<?php echo $isEdit ? 'Update Record' : 'Add Record'; ?>">
            <a href="index.php">Cancel</a>
        </div>
    </form>
</body>
</html>
