<?php
class Affiliate extends Model
{
    public function getByUserId($userId)
    {
        $stmt = $this->db->prepare('SELECT * FROM affiliates WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getByPromoCode($promoCode)
    {
        $stmt = $this->db->prepare('SELECT * FROM affiliates WHERE promo_code = ?');
        $stmt->execute([$promoCode]);
        return $stmt->fetch();
    }

    public function getRegistrations($affiliateId)
    {
        $stmt = $this->db->prepare('SELECT * FROM affiliate_registrations WHERE affiliate_id = ? ORDER BY created_at DESC');
        $stmt->execute([$affiliateId]);
        return $stmt->fetchAll();
    }

    public function createForUser($userId, $promoCode, $referralLink)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO affiliates (user_id, promo_code, referral_link, total_earnings, total_registers, pending_balance) '
            . 'VALUES (?, ?, ?, 0, 0, 0)'
        );
        $stmt->execute([$userId, $promoCode, $referralLink]);
        return (int) $this->db->lastInsertId();
    }

    public function addRegistration($affiliateId, $username, $earnings = 0)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO affiliate_registrations (affiliate_id, username, earnings) VALUES (?, ?, ?)'
        );
        $stmt->execute([$affiliateId, $username, (float) $earnings]);
    }

    public function incrementTotals($affiliateId, $registers = 1, $earnings = 0)
    {
        $registerValue = (int) $registers;
        if ($registerValue < 0) {
            $registerValue = 0;
        }
        $earningsValue = (float) $earnings;
        $stmt = $this->db->prepare(
            'UPDATE affiliates '
            . 'SET total_registers = total_registers + ?, total_earnings = total_earnings + ? '
            . 'WHERE id = ?'
        );
        $stmt->execute([$registerValue, $earningsValue, $affiliateId]);
    }

    public function getRegistrationByUsername($username)
    {
        $stmt = $this->db->prepare(
            'SELECT ar.id, ar.affiliate_id, ar.earnings '
            . 'FROM affiliate_registrations ar '
            . 'WHERE ar.username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function addRegistrationEarnings($registrationId, $amount)
    {
        $stmt = $this->db->prepare(
            'UPDATE affiliate_registrations SET earnings = earnings + ? WHERE id = ?'
        );
        $stmt->execute([(float) $amount, (int) $registrationId]);
    }

    public function addAffiliateEarnings($affiliateId, $amount)
    {
        $stmt = $this->db->prepare(
            'UPDATE affiliates '
            . 'SET total_earnings = total_earnings + ?, pending_balance = pending_balance + ? '
            . 'WHERE id = ?'
        );
        $stmt->execute([(float) $amount, (float) $amount, (int) $affiliateId]);
    }

    public function withdrawToBalance($userId, $amount = null)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT id, pending_balance FROM affiliates WHERE user_id = ? FOR UPDATE');
            $stmt->execute([(int) $userId]);
            $affiliate = $stmt->fetch();
            if (!$affiliate) {
                $this->db->rollBack();
                return 0;
            }
            $pending = (float) $affiliate['pending_balance'];
            if ($pending <= 0) {
                $this->db->rollBack();
                return 0;
            }
            $withdrawAmount = $amount === null ? $pending : (float) $amount;
            if ($withdrawAmount <= 0 || $withdrawAmount > $pending) {
                $this->db->rollBack();
                return 0;
            }
            $update = $this->db->prepare('UPDATE affiliates SET pending_balance = pending_balance - ? WHERE id = ?');
            $update->execute([(float) $withdrawAmount, (int) $affiliate['id']]);
            (new WalletTransaction())->create(
                (int) $userId,
                'deposit',
                $withdrawAmount,
                'completed',
                'Affiliate withdraw'
            );
            $this->db->commit();
            return $withdrawAmount;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
