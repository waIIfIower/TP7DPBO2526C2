<?php
/** Model OOP untuk CRUD tabel categories. */
require_once __DIR__ . '/../config/database.php';

class Category
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function create(string $name, string $description): bool
    {
        // Placeholder menjaga nilai masukan terpisah dari teks SQL.
        $stmt = $this->conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        return $stmt->execute([$name, $description]);
    }

    public function readAll(): PDOStatement
    {
        // Daftar kategori dipakai pada tabel dan dropdown form soal.
        $stmt = $this->conn->prepare('SELECT id, name, description FROM categories ORDER BY id DESC');
        $stmt->execute();
        return $stmt;
    }

    public function readById(int $id): array|false
    {
        // Mengambil satu kategori untuk mengisi form edit.
        $stmt = $this->conn->prepare('SELECT id, name, description FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function countAll(): int
    {
        // COUNT dipakai dashboard dan tidak bergantung pada rowCount SELECT.
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM categories');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, string $name, string $description): bool
    {
        // Memperbarui data kategori yang dipilih berdasarkan ID.
        $stmt = $this->conn->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
        return $stmt->execute([$name, $description, $id]);
    }

    public function delete(int $id): bool
    {
        // Foreign key cascade menghapus soal dan submission turunannya.
        $stmt = $this->conn->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
