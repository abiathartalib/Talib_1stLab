<?php

class Student
{
    public $id;
    public $studentNumber;
    public $name;
    public $email;
    public $course;

    public function __construct($id, $studentNumber, $name, $email, $course)
    {
        $this->id = $id;
        $this->studentNumber = $studentNumber;
        $this->name = $name;
        $this->email = $email;
        $this->course = $course;
    }

    public function getUppercaseName()
    {
        return strtoupper($this->name);
    }

    public function getCourseSummary()
    {
        return strtoupper($this->course);
    }
}

class StudentRepository
{
    private $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;

        $this->connection->query(
            'CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_number VARCHAR(50) NOT NULL,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                course VARCHAR(100) NOT NULL
            )'
        );
    }

    public function getAll()
    {
        $students = [];
        $result = $this->connection->query('SELECT id, student_number, name, email, course FROM students ORDER BY id ASC');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $students[] = new Student(
                    (int)$row['id'],
                    $row['student_number'],
                    $row['name'],
                    $row['email'],
                    $row['course']
                );
            }
            $result->free();
        }
        return $students;
    }

    public function findById($id)
    {
        $statement = $this->connection->prepare('SELECT id, student_number, name, email, course FROM students WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        $statement->close();
        if ($row) {
            return new Student(
                (int)$row['id'],
                $row['student_number'],
                $row['name'],
                $row['email'],
                $row['course']
            );
        }
        return null;
    }

    public function create(Student $student)
    {
        $statement = $this->connection->prepare('INSERT INTO students (student_number, name, email, course) VALUES (?, ?, ?, ?)');
        $statement->bind_param(
            'ssss',
            $student->studentNumber,
            $student->name,
            $student->email,
            $student->course
        );
        $statement->execute();
        $statement->close();
    }

    public function update(Student $student)
    {
        $statement = $this->connection->prepare('UPDATE students SET student_number = ?, name = ?, email = ?, course = ? WHERE id = ?');
        $statement->bind_param(
            'ssssi',
            $student->studentNumber,
            $student->name,
            $student->email,
            $student->course,
            $student->id
        );
        $statement->execute();
        $statement->close();
    }

    public function delete($id)
    {
        $statement = $this->connection->prepare('DELETE FROM students WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}
