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

$message = isset($_GET['message']) ? trim($_GET['message']) : '';
$error = '';

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
            $messageText = 'Student record updated.';
        } else {
            $student = new Student(0, $studentNumber, $name, $email, $course);
            $repository->create($student);
            $messageText = 'Student record created.';
        }

        header('Location: index.php?message=' . urlencode($messageText));
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $repository->delete($id);
        header('Location: index.php?message=' . urlencode('Student record deleted.'));
        exit;
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
    <title>Dashboard - Student Record</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="app-shell">
        <header class="app-header">
            <div class="brand">
                <div class="brand-logo">SR</div>
                <div>
                    <div class="brand-title">Student Record Dashboard</div>
                    <div class="brand-subtitle">Quick overview of all registered students</div>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-pill">
                    <span class="badge-dot"></span>
                    <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>
                </div>
                <a href="logout.php" class="btn btn-ghost">Logout</a>
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                    Add student
                </button>
            </div>
        </header>

        <main class="layout-main">
            <?php if ($message !== ''): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <section class="card">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total students</div>
                        <div class="stat-value-row">
                            <div class="stat-value"><?php echo $totalStudents; ?></div>
                            <span class="stat-pill">Active records</span>
                        </div>
                        <div class="stat-helper">
                            Students currently stored in the database.
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Name length</div>
                        <div class="stat-value-row">
                            <div class="stat-value">
                                <?php echo $totalStudents > 0 ? $averageNameLength : 0; ?>
                            </div>
                            <span class="stat-pill">Characters avg.</span>
                        </div>
                        <div class="stat-helper">
                            Average number of characters per student name.
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Last updated</div>
                        <div class="stat-value-row">
                            <div class="stat-value"><?php echo date('H:i'); ?></div>
                            <span class="stat-pill">Today</span>
                        </div>
                        <div class="stat-helper">
                            Data refreshed when the page loads.
                        </div>
                    </div>
                </div>

                <div class="table-card-header">
                    <div>
                        <div class="table-title">Student records</div>
                        <div class="table-subtitle">
                            View, edit, or remove students from your database.
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost btn-small" onclick="openCreateModal()">
                        + Add student
                    </button>
                </div>

                <div class="table-wrapper">
                    <?php if ($totalStudents === 0): ?>
                        <div class="empty-state">
                            <strong>No student records yet.</strong>
                            <br>
                            Click
                            <span class="chip">Add student</span>
                            to create your first record.
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="text-muted">
                                        #<?php echo $student->id; ?>
                                    </td>
                                    <td>
                                        <span class="chip">
                                            <?php echo htmlspecialchars($student->studentNumber); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student->getUppercaseName()); ?>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo htmlspecialchars(strtolower($student->email)); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student->getCourseSummary()); ?>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <button
                                                type="button"
                                                class="btn btn-edit btn-small"
                                                onclick="openEditModal(this)"
                                                data-id="<?php echo $student->id; ?>"
                                                data-student-number="<?php echo htmlspecialchars($student->studentNumber, ENT_QUOTES); ?>"
                                                data-name="<?php echo htmlspecialchars($student->name, ENT_QUOTES); ?>"
                                                data-email="<?php echo htmlspecialchars($student->email, ENT_QUOTES); ?>"
                                                data-course="<?php echo htmlspecialchars($student->course, ENT_QUOTES); ?>"
                                            >
                                                Edit
                                            </button>
                                            <a
                                                href="index.php?action=delete&id=<?php echo $student->id; ?>"
                                                class="btn btn-danger btn-small"
                                                onclick="return confirm('Delete this record?');"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <div id="studentModalBackdrop" class="modal-backdrop">
        <div id="studentModal" class="modal" tabindex="-1">
            <div class="modal-header">
                <div class="modal-title" id="studentModalTitle">Add student</div>
                <button type="button" class="icon-button" onclick="closeStudentModal()">&times;</button>
            </div>

            <form id="studentForm" method="post" action="index.php" autocomplete="off">
                <input type="hidden" name="id" value="0">

                <div class="field">
                    <label for="student_number">ID Number</label>
                    <input
                        type="text"
                        id="student_number"
                        name="student_number"
                        required
                    >
                </div>

                <div class="field">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                    >
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                    >
                </div>

                <div class="field">
                    <label for="course">Course</label>
                    <input
                        type="text"
                        id="course"
                        name="course"
                        required
                    >
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn btn-secondary btn-small"
                        onclick="closeStudentModal()"
                    >
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-small">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modalBackdrop = document.getElementById('studentModalBackdrop');
        const modal = document.getElementById('studentModal');
        const form = document.getElementById('studentForm');
        const modalTitle = document.getElementById('studentModalTitle');
        const hiddenIdInput = form.elements['id'];

        function openCreateModal() {
            form.reset();
            if (hiddenIdInput) {
                hiddenIdInput.value = '0';
            }
            modalTitle.textContent = 'Add student';
            modalBackdrop.classList.add('is-visible');
            modal.focus();
        }

        function openEditModal(button) {
            const id = button.getAttribute('data-id');
            const studentNumber = button.getAttribute('data-student-number');
            const name = button.getAttribute('data-name');
            const email = button.getAttribute('data-email');
            const course = button.getAttribute('data-course');

            if (hiddenIdInput) {
                hiddenIdInput.value = id || '0';
            }
            form.student_number.value = studentNumber || '';
            form.name.value = name || '';
            form.email.value = email || '';
            form.course.value = course || '';

            modalTitle.textContent = 'Edit student';
            modalBackdrop.classList.add('is-visible');
            modal.focus();
        }

        function closeStudentModal() {
            modalBackdrop.classList.remove('is-visible');
        }

        document.addEventListener('click', function (event) {
            if (event.target === modalBackdrop) {
                closeStudentModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeStudentModal();
            }
        });
    </script>
</body>
</html>
