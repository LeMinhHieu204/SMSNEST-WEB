<?php
class Balance extends Model
{
    public function getByUserId($userId)
    {
        $stmt = $this->db->prepare('SELECT * FROM balances WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function createForUser($userId)
    {
        $stmt = $this->db->prepare('INSERT INTO balances (user_id, available_balance, pending_balance) VALUES (?, 0, 0)');
        $stmt->execute([$userId]);
    }
}
