<?php
class Deposit extends Model
{
    public function getByUserId($userId)
    {
        $stmt = $this->db->prepare('SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($userId, $amount, $method)
    {
        $stmt = $this->db->prepare('INSERT INTO deposits (user_id, amount, method) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $amount, $method]);
        return (int) $this->db->lastInsertId();
    }
}
