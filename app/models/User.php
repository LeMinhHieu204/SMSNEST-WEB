<?php
class User extends Model
{
    private static $hasAvatarColumn = null;
    private static $hasPasswordResetColumns = null;

    private function hasAvatarColumn()
    {
        if (self::$hasAvatarColumn !== null) {
            return self::$hasAvatarColumn;
        }
        $stmt = $this->db->prepare("SHOW COLUMNS FROM users LIKE 'avatar'");
        $stmt->execute();
        self::$hasAvatarColumn = (bool) $stmt->fetch();
        if (!self::$hasAvatarColumn) {
            $this->db->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
            $stmt = $this->db->prepare("SHOW COLUMNS FROM users LIKE 'avatar'");
            $stmt->execute();
            self::$hasAvatarColumn = (bool) $stmt->fetch();
        }
        return self::$hasAvatarColumn;
    }

    private function hasPasswordResetColumns()
    {
        if (self::$hasPasswordResetColumns !== null) {
            return self::$hasPasswordResetColumns;
        }
        $hasToken = $this->columnExists('password_reset_token');
        $hasExpires = $this->columnExists('password_reset_expires');
        if (!$hasToken) {
            $this->db->exec("ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(128) DEFAULT NULL");
        }
        if (!$hasExpires) {
            $this->db->exec("ALTER TABLE users ADD COLUMN password_reset_expires DATETIME DEFAULT NULL");
        }
        self::$hasPasswordResetColumns = $this->columnExists('password_reset_token')
            && $this->columnExists('password_reset_expires');
        return self::$hasPasswordResetColumns;
    }

    private function columnExists($column)
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM users LIKE ?");
        $stmt->execute([$column]);
        return (bool) $stmt->fetch();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function existsByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    public function create($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, role, password_hash, email_verified_at, verification_token) '
            . 'VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, 'user', $hash, null, null]);
        return (int) $this->db->lastInsertId();
    }

    public function getAll($limit = 100)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 100;
        }
        $stmt = $this->db->query(
            'SELECT id, username, email, role FROM users ORDER BY id DESC LIMIT ' . $limitValue
        );
        return $stmt->fetchAll();
    }

    public function getAllWithBalances($limit = 100)
    {
        $limitValue = (int) $limit;
        if ($limitValue < 1) {
            $limitValue = 100;
        }
        $stmt = $this->db->query(
            'SELECT u.id, u.username, u.email, u.role, '
            . 'COALESCE(SUM(CASE WHEN wt.status = \'completed\' '
            . 'THEN CASE WHEN wt.type = \'deposit\' THEN wt.amount ELSE -wt.amount END '
            . 'ELSE 0 END), 0) AS balance_total '
            . 'FROM users u '
            . 'LEFT JOIN wallet_transactions wt ON wt.user_id = u.id '
            . 'GROUP BY u.id '
            . 'ORDER BY u.id DESC '
            . 'LIMIT ' . $limitValue
        );
        return $stmt->fetchAll();
    }

    public function updateProfile($userId, $username, $email)
    {
        $stmt = $this->db->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
        $stmt->execute([$username, $email, (int) $userId]);
    }

    public function updatePassword($userId, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, (int) $userId]);
    }

    public function updateAvatar($userId, $avatarPath)
    {
        if (!$this->hasAvatarColumn()) {
            return;
        }
        $stmt = $this->db->prepare('UPDATE users SET avatar = ? WHERE id = ?');
        $stmt->execute([$avatarPath, (int) $userId]);
    }

    public function setVerificationToken($userId, $token)
    {
        $stmt = $this->db->prepare('UPDATE users SET verification_token = ? WHERE id = ?');
        $stmt->execute([$token, (int) $userId]);
    }

    public function getByVerificationToken($token)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE verification_token = ? LIMIT 1');
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function markEmailVerified($userId)
    {
        $stmt = $this->db->prepare('UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?');
        $stmt->execute([(int) $userId]);
    }

    public function setPasswordResetToken($userId, $token, $expiresAt)
    {
        if (!$this->hasPasswordResetColumns()) {
            return;
        }
        $stmt = $this->db->prepare(
            'UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?'
        );
        $stmt->execute([$token, $expiresAt, (int) $userId]);
    }

    public function getByPasswordResetToken($token)
    {
        if (!$this->hasPasswordResetColumns()) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE password_reset_token = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function clearPasswordResetToken($userId)
    {
        if (!$this->hasPasswordResetColumns()) {
            return;
        }
        $stmt = $this->db->prepare(
            'UPDATE users SET password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?'
        );
        $stmt->execute([(int) $userId]);
    }
}
