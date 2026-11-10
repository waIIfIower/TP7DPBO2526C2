<?php
/** Model OOP untuk CRUD tabel problems beserta relasi kategori. */
require_once __DIR__ . '/../config/database.php';

class Problem
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function create(int $category_id, string $title, string $platform, string $difficulty): bool
    {
        // category_id menghubungkan soal dengan kategori induk.
        $stmt = $this->conn->prepare('INSERT INTO problems (category_id, title, platform, difficulty) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$category_id, $title, $platform, $difficulty]);
    }

    public function readAll(): PDOStatement
    {
        // LEFT JOIN mengambil nama kategori untuk ditampilkan di tabel soal.
        $stmt = $this->conn->prepare('SELECT p.id, p.category_id, p.title, p.platform, p.difficulty, c.name AS category_name
            FROM problems p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC');
        $stmt->execute();
        return $stmt;
    }

    public function readById(int $id): array|false
    {
        // Mengambil atribut soal untuk form edit.
        $stmt = $this->conn->prepare('SELECT id, category_id, title, platform, difficulty FROM problems WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Periksa apakah judul sudah tercatat pada platform yang sama.
     * Saat edit, ID record yang sedang diedit dikecualikan agar nilai lama
     * tetap dapat disimpan tanpa dianggap sebagai duplikat dirinya sendiri.
     */
    public function duplicateExists(string $title, string $platform, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $stmt = $this->conn->prepare(
                'SELECT 1 FROM problems WHERE title = ? AND platform = ? LIMIT 1'
            );
            $stmt->execute([$title, $platform]);
        } else {
            $stmt = $this->conn->prepare(
                'SELECT 1 FROM problems WHERE title = ? AND platform = ? AND id <> ? LIMIT 1'
            );
            $stmt->execute([$title, $platform, $excludeId]);
        }

        return $stmt->fetchColumn() !== false;
    }

    public function countAll(): int
    {
        // Total soal untuk kartu statistik dashboard.
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM problems');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, int $category_id, string $title, string $platform, string $difficulty): bool
    {
        // Mengganti detail soal dan kategori relasinya.
        $stmt = $this->conn->prepare('UPDATE problems SET category_id = ?, title = ?, platform = ?, difficulty = ? WHERE id = ?');
        return $stmt->execute([$category_id, $title, $platform, $difficulty, $id]);
    }

    public function delete(int $id): bool
    {
        // Penghapusan soal turut menghapus submission melalui cascade.
        $stmt = $this->conn->prepare('DELETE FROM problems WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
