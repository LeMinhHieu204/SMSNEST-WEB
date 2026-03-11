<?php
class ServiceCountry extends Model
{
    public function getByServiceId($serviceId)
    {
        $stmt = $this->db->prepare(
            'SELECT sc.country_id, sc.stock, '
            . 'COALESCE(sc.custom_min_price, sc.min_price) AS min_price, '
            . 'COALESCE(sc.custom_max_price, sc.max_price) AS max_price, '
            . 'c.country_name, c.code '
            . 'FROM service_countries sc '
            . 'JOIN countries c ON c.id = sc.country_id '
            . 'WHERE sc.service_id = ? '
            . 'ORDER BY c.country_name'
        );
        $stmt->execute([$serviceId]);
        return $stmt->fetchAll();
    }

    public function upsertPricing($serviceId, $countryId, $price, $stock = null)
    {
        $stockValue = $stock === null ? null : (int) $stock;
        $stmt = $this->db->prepare(
            'INSERT INTO service_countries (service_id, country_id, stock, min_price, max_price) '
            . 'VALUES (?, ?, COALESCE(?, 0), ?, ?) '
            . 'ON DUPLICATE KEY UPDATE min_price = VALUES(min_price), max_price = VALUES(max_price), '
            . 'stock = COALESCE(?, stock)'
        );
        $stmt->execute([$serviceId, $countryId, $stockValue, $price, $price, $stockValue]);
    }

    public function updateStock($serviceId, $countryId, $stock)
    {
        $stockValue = (int) $stock;
        $stmt = $this->db->prepare(
            'INSERT INTO service_countries (service_id, country_id, stock, min_price, max_price) '
            . 'VALUES (?, ?, ?, 0, 0) '
            . 'ON DUPLICATE KEY UPDATE stock = VALUES(stock)'
        );
        $stmt->execute([$serviceId, $countryId, $stockValue]);
    }

    public function getByServiceIdRaw($serviceId)
    {
        $stmt = $this->db->prepare(
            'SELECT sc.country_id, sc.stock, sc.min_price, sc.max_price, '
            . 'sc.custom_min_price, sc.custom_max_price, c.country_name, c.code '
            . 'FROM service_countries sc '
            . 'JOIN countries c ON c.id = sc.country_id '
            . 'WHERE sc.service_id = ? '
            . 'ORDER BY c.country_name'
        );
        $stmt->execute([$serviceId]);
        return $stmt->fetchAll();
    }

    public function getByServiceIdRawFiltered($serviceId, $countryQuery)
    {
        $term = '%' . $countryQuery . '%';
        $stmt = $this->db->prepare(
            'SELECT sc.country_id, sc.stock, sc.min_price, sc.max_price, '
            . 'sc.custom_min_price, sc.custom_max_price, c.country_name, c.code '
            . 'FROM service_countries sc '
            . 'JOIN countries c ON c.id = sc.country_id '
            . 'WHERE sc.service_id = ? AND (c.country_name LIKE ? OR c.code LIKE ?) '
            . 'ORDER BY c.country_name'
        );
        $stmt->execute([$serviceId, $term, $term]);
        return $stmt->fetchAll();
    }

    public function updateCustomPrices($serviceId, $countryId, $min, $max)
    {
        $minValue = $min === '' ? null : (float) $min;
        $maxValue = $max === '' ? null : (float) $max;
        $stmt = $this->db->prepare(
            'UPDATE service_countries SET custom_min_price = ?, custom_max_price = ? '
            . 'WHERE service_id = ? AND country_id = ?'
        );
        $stmt->execute([$minValue, $maxValue, $serviceId, $countryId]);
    }

    public function getEffectivePrice($serviceId, $countryId)
    {
        $stmt = $this->db->prepare(
            'SELECT custom_min_price, custom_max_price, min_price, max_price '
            . 'FROM service_countries WHERE service_id = ? AND country_id = ?'
        );
        $stmt->execute([$serviceId, $countryId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if ($row['custom_max_price'] !== null) {
            return (float) $row['custom_max_price'];
        }
        if ($row['custom_min_price'] !== null) {
            return (float) $row['custom_min_price'];
        }
        if ($row['max_price'] !== null) {
            return (float) $row['max_price'];
        }
        if ($row['min_price'] !== null) {
            return (float) $row['min_price'];
        }
        return null;
    }

    public function countLowStock($threshold = 5)
    {
        $thresholdValue = (int) $threshold;
        if ($thresholdValue < 0) {
            $thresholdValue = 0;
        }
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total_low_stock FROM service_countries WHERE stock IS NOT NULL AND stock <= ?'
        );
        $stmt->execute([$thresholdValue]);
        $row = $stmt->fetch();
        return $row ? (int) $row['total_low_stock'] : 0;
    }
}
