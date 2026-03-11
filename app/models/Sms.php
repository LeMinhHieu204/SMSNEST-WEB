<?php
class Sms extends Model
{
    public function getMessagesByOrderId($orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM sms_messages WHERE order_id = ? ORDER BY received_at DESC');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function existsForOrderId($orderId, $code)
    {
        $stmt = $this->db->prepare('SELECT id FROM sms_messages WHERE order_id = ? AND code = ? LIMIT 1');
        $stmt->execute([$orderId, $code]);
        return (bool) $stmt->fetch();
    }

    public function create($orderId, $code, $message)
    {
        $stmt = $this->db->prepare('INSERT INTO sms_messages (order_id, code, message) VALUES (?, ?, ?)');
        $stmt->execute([$orderId, $code, $message]);
    }
}
