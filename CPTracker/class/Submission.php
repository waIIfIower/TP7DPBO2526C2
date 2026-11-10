<?php
/** Model OOP untuk CRUD riwayat percobaan penyelesaian soal. */
require_once __DIR__ . '/../config/database.php';

class Submission
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function create(int $problem_id, string $language, string $verdict): bool
    {
        // Waktu submission diisi otomatis oleh default timestamp database.
        $stmt = $this->conn->prepare('INSERT INTO submissions (problem_id, language, verdict) VALUES (?, ?, ?)');
        return $stmt->execute([$problem_id, $language, $verdict]);
    }

    public function readAll(): PDOStatement
    {
        // JOIN menyertakan judul soal pada daftar riwayat.
        $stmt = $this->conn->prepare('SELECT s.id, s.problem_id, p.title AS problem_title, s.language, s.verdict, s.submitted_at
            FROM submissions s LEFT JOIN problems p ON s.problem_id = p.id ORDER BY s.submitted_at DESC, s.id DESC');
        $stmt->execute();
        return $stmt;
    }

    public function readById(int $id): array|false
    {
        // Mengambil satu submission untuk mengisi form edit.
        $stmt = $this->conn->prepare('SELECT id, problem_id, language, verdict FROM submissions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function countAll(): int
    {
        // Menghitung seluruh percobaan untuk dashboard.
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM submissions');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, int $problem_id, string $language, string $verdict): bool
    {
        // Memperbarui soal, bahasa, dan hasil percobaan.
        $stmt = $this->conn->prepare('UPDATE submissions SET problem_id = ?, language = ?, verdict = ? WHERE id = ?');
        return $stmt->execute([$problem_id, $language, $verdict, $id]);
    }

    public function delete(int $id): bool
    {
        // Menghapus satu catatan riwayat.
        $stmt = $this->conn->prepare('DELETE FROM submissions WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
