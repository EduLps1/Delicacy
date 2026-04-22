<?php
// src/models/BaseModel.php

namespace Delicacy\Models;

use Delicacy\Database\Connection;

abstract class BaseModel
{
    protected $db;
    protected $table = '';

    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $stmt = $this->db->prepare($sql);

        $types = str_repeat('s', count($data));
        $stmt->bind_param($types, ...array_values($data));

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$set} WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        $types = str_repeat('s', count($data)) . 'i';
        $values = array_values($data);
        $values[] = $id;

        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

?>