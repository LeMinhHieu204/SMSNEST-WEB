<?php
class Service extends Model
{
    public function all()
    {
        $stmt = $this->db->query('SELECT id, service_name FROM services ORDER BY service_name');
        return $stmt->fetchAll();
    }

    public function searchByName($query)
    {
        $term = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT id, service_name FROM services WHERE service_name LIKE ? ORDER BY service_name'
        );
        $stmt->execute([$term]);
        return $stmt->fetchAll();
    }
}
