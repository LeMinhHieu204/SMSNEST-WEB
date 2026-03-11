<?php
class Country extends Model
{
    public function all()
    {
        $stmt = $this->db->query('SELECT id, country_name, code FROM countries ORDER BY country_name');
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT id, country_name, code FROM countries WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
