<?php
class Order extends Model
{
    private static $hasProviderOrderId = null;
    private static $hasQuantityColumn = null;

    private function hasProviderOrderIdColumn()
    {
        if (self::$hasProviderOrderId !== null) {
            return self::$hasProviderOrderId;
        }
        $stmt = $this->db->prepare("SHOW COLUMNS FROM orders LIKE 'provider_order_id'");
        $stmt->execute();
        self::$hasProviderOrderId = (bool) $stmt->fetch();
        if (!self::$hasProviderOrderId) {
            // Ensure column exists so provider order ids can be stored.
            $this->db->exec("ALTER TABLE orders ADD COLUMN provider_order_id VARCHAR(60) DEFAULT NULL");
            $stmt = $this->db->prepare("SHOW COLUMNS FROM orders LIKE 'provider_order_id'");
            $stmt->execute();
            self::$hasProviderOrderId = (bool) $stmt->fetch();
        }
        return self::$hasProviderOrderId;
    }

    private function hasQuantityColumn()
    {
        if (self::$hasQuantityColumn !== null) {
            return self::$hasQuantityColumn;
        }
        $stmt = $this->db->prepare("SHOW COLUMNS FROM orders LIKE 'quantity'");
        $stmt->execute();
        self::$hasQuantityColumn = (bool) $stmt->fetch();
        if (!self::$hasQuantityColumn) {
            $this->db->exec("ALTER TABLE orders ADD COLUMN quantity INT NOT NULL DEFAULT 1");
            $stmt = $this->db->prepare("SHOW COLUMNS FROM orders LIKE 'quantity'");
            $stmt->execute();
            self::$hasQuantityColumn = (bool) $stmt->fetch();
        }
        return self::$hasQuantityColumn;
    }

    public function create($userId, $serviceId, $country, $phoneNumber, $cost, $providerOrderId = null, $quantity = 1)
    {
        $quantityValue = (int) $quantity;
        if ($quantityValue < 1) {
            $quantityValue = 1;
        }
        if ($this->hasProviderOrderIdColumn() && $this->hasQuantityColumn()) {
            $stmt = $this->db->prepare(
                'INSERT INTO orders (user_id, service_id, country, phone_number, cost, status, provider_order_id, quantity) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $serviceId,
                $country,
                $phoneNumber,
                $cost,
                'pending',
                $providerOrderId,
                $quantityValue,
            ]);
        } elseif ($this->hasProviderOrderIdColumn()) {
            $stmt = $this->db->prepare(
                'INSERT INTO orders (user_id, service_id, country, phone_number, cost, status, provider_order_id) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $serviceId,
                $country,
                $phoneNumber,
                $cost,
                'pending',
                $providerOrderId,
            ]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO orders (user_id, service_id, country, phone_number, cost, status) '
                . 'VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $serviceId,
                $country,
                $phoneNumber,
                $cost,
                'pending',
            ]);
        }
        return (int) $this->db->lastInsertId();
    }

    public function getByProviderOrderId($providerOrderId)
    {
        if (!$this->hasProviderOrderIdColumn()) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE provider_order_id = ? LIMIT 1');
        $stmt->execute([$providerOrderId]);
        return $stmt->fetch();
    }

    public function getById($orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function updateStatusByProviderOrderId($providerOrderId, $status)
    {
        if (!$this->hasProviderOrderIdColumn()) {
            return;
        }
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE provider_order_id = ?');
        $stmt->execute([$status, $providerOrderId]);
    }

    public function updateStatusById($orderId, $status)
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
    }

    public function getPendingByUserId($userId)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, UNIX_TIMESTAMP(o.created_at) AS created_at_epoch, s.service_name, "
            . "sm.code AS sms_code, sm.message AS sms_message "
            . "FROM orders o "
            . "JOIN services s ON s.id = o.service_id "
            . "LEFT JOIN sms_messages sm ON sm.id = ("
                . "SELECT id FROM sms_messages "
                . "WHERE order_id = o.id "
                . "ORDER BY received_at DESC, id DESC "
                . "LIMIT 1"
            . ") "
            . "WHERE o.user_id = ? AND o.status IN ('pending','completed') "
            . "ORDER BY o.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getHistoryByUserId($userId)
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, s.service_name '
            . 'FROM orders o '
            . 'JOIN services s ON s.id = o.service_id '
            . 'WHERE o.user_id = ? '
            . 'ORDER BY o.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getStatsByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total_orders, SUM(cost) AS total_spent FROM orders WHERE user_id = ? AND status IN ('completed','pending')");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getAdminStats()
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total_orders, "
            . "SUM(cost) AS total_spent, "
            . "SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders "
            . "FROM orders WHERE status IN ('completed','pending')"
        );
        return $stmt->fetch();
    }

    public function getTotalCompletedSpent()
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(cost), 0) AS total_spent FROM orders WHERE status = 'completed'"
        );
        $row = $stmt->fetch();
        return $row ? (float) $row['total_spent'] : 0;
    }

    public function getRecentForAdmin($limit = 5)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 5;
        }
        $stmt = $this->db->prepare(
            'SELECT o.id, o.country, o.status, o.cost, o.created_at, '
            . 'u.username, s.service_name '
            . 'FROM orders o '
            . 'JOIN users u ON u.id = o.user_id '
            . 'JOIN services s ON s.id = o.service_id '
            . 'ORDER BY o.created_at DESC '
            . "LIMIT {$limitValue}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllForAdmin($limit = 200)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 200;
        }
        $stmt = $this->db->query(
            'SELECT o.id, o.user_id, o.country, o.status, o.cost, o.created_at, '
            . 'o.phone_number, o.quantity, '
            . 'u.username, s.service_name '
            . 'FROM orders o '
            . 'JOIN users u ON u.id = o.user_id '
            . 'JOIN services s ON s.id = o.service_id '
            . 'ORDER BY o.created_at DESC '
            . 'LIMIT ' . $limitValue
        );
        return $stmt->fetchAll();
    }

    public function getAllForAdminFiltered($filters, $limit = 200)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 200;
        }
        $where = [];
        $params = [];

        $username = trim($filters['username'] ?? '');
        if ($username !== '') {
            $where[] = 'u.username LIKE ?';
            $params[] = '%' . $username . '%';
        }

        $service = trim($filters['service'] ?? '');
        if ($service !== '') {
            $where[] = 's.service_name LIKE ?';
            $params[] = '%' . $service . '%';
        }

        $country = trim($filters['country'] ?? '');
        if ($country !== '') {
            $where[] = 'o.country LIKE ?';
            $params[] = '%' . $country . '%';
        }

        $status = trim($filters['status'] ?? '');
        if ($status !== '') {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }

        $orderId = trim($filters['order_id'] ?? '');
        if ($orderId !== '') {
            $where[] = 'o.id = ?';
            $params[] = (int) $orderId;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql =
            'SELECT o.id, o.user_id, o.country, o.status, o.cost, o.created_at, '
            . 'o.phone_number, o.quantity, '
            . 'u.username, s.service_name '
            . 'FROM orders o '
            . 'JOIN users u ON u.id = o.user_id '
            . 'JOIN services s ON s.id = o.service_id '
            . $whereSql . ' '
            . 'ORDER BY o.created_at DESC '
            . 'LIMIT ' . $limitValue;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
