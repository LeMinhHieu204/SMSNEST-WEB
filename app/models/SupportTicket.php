<?php
class SupportTicket extends Model
{
    public function create($userId, $username, $email, $title, $content, $imagePath = null)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO support_tickets (user_id, username, email, title, content, image_path) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $username, $email, $title, $content, $imagePath]);
        return (int) $this->db->lastInsertId();
    }

    public function getAll($limit = 200, $emailQuery = '')
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 200;
        }
        $sql = 'SELECT st.*, u.username AS account_username '
            . 'FROM support_tickets st '
            . 'LEFT JOIN users u ON u.id = st.user_id ';
        $params = [];
        if ($emailQuery !== '') {
            $sql .= 'WHERE st.email LIKE ? ';
            $params[] = '%' . $emailQuery . '%';
        }
        $sql .= 'ORDER BY st.created_at DESC LIMIT ' . $limitValue;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByUserId($userId, $limit = 50)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 50;
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . $limitValue
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
