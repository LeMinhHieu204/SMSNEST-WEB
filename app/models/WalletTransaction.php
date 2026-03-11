<?php
class WalletTransaction extends Model
{
    public function getByUserId($userId)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getByUserIdAndType($userId, $type)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM wallet_transactions WHERE user_id = ? AND type = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId, $type]);
        return $stmt->fetchAll();
    }

    public function create($userId, $type, $amount, $status = 'completed', $note = null)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO wallet_transactions (user_id, type, amount, status, note) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $amount, $status, $note]);
        return (int) $this->db->lastInsertId();
    }

    public function getByNote($note)
    {
        $stmt = $this->db->prepare('SELECT * FROM wallet_transactions WHERE note = ? LIMIT 1');
        $stmt->execute([$note]);
        return $stmt->fetch();
    }

    public function updateStatusByNote($note, $status)
    {
        $stmt = $this->db->prepare('UPDATE wallet_transactions SET status = ? WHERE note = ?');
        $stmt->execute([$status, $note]);
        return $stmt->rowCount();
    }

    public function updateAmountStatusByNote($note, $amount, $status)
    {
        $stmt = $this->db->prepare(
            'UPDATE wallet_transactions SET amount = ?, status = ? WHERE note = ?'
        );
        $stmt->execute([$amount, $status, $note]);
        return $stmt->rowCount();
    }

    public function getTotalByUserId($userId, $type = 'deposit')
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(amount), 0) AS total_amount '
            . 'FROM wallet_transactions WHERE user_id = ? AND type = ? AND status = ?'
        );
        $stmt->execute([$userId, $type, 'completed']);
        $row = $stmt->fetch();
        return $row ? (float) $row['total_amount'] : 0;
    }

    public function getNetByUserId($userId)
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(CASE WHEN type = \'deposit\' THEN amount ELSE -amount END), 0) AS net_amount '
            . 'FROM wallet_transactions WHERE user_id = ? AND status = ?'
        );
        $stmt->execute([$userId, 'completed']);
        $row = $stmt->fetch();
        return $row ? (float) $row['net_amount'] : 0;
    }

    public function getDepositLogs($limit = 200)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 200;
        }
        $stmt = $this->db->query(
            'SELECT wt.id, wt.user_id, wt.amount, wt.status, wt.note, wt.created_at, u.username, u.email '
            . 'FROM wallet_transactions wt '
            . 'JOIN users u ON u.id = wt.user_id '
            . "WHERE wt.type = 'deposit' AND (wt.note IS NULL OR wt.note NOT LIKE 'Refund SMS order%') "
            . 'ORDER BY wt.created_at DESC '
            . 'LIMIT ' . $limitValue
        );
        return $stmt->fetchAll();
    }

    public function getTotalDepositsAll()
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) AS total_amount "
            . "FROM wallet_transactions WHERE type = 'deposit' AND status = 'completed'"
        );
        $row = $stmt->fetch();
        return $row ? (float) $row['total_amount'] : 0;
    }
}
