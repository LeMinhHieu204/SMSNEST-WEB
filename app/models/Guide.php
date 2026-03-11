<?php
class Guide extends Model
{
    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM guides ORDER BY created_at DESC, id DESC');
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM guides WHERE id = ?');
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function getBySection($section)
    {
        $stmt = $this->db->prepare('SELECT * FROM guides WHERE section = ? ORDER BY created_at DESC, id DESC');
        $stmt->execute([$section]);
        return $stmt->fetchAll();
    }

    public function create($section, $title, $content, $imagePath)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO guides (section, title, content, image_path) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$section, $title, $content, $imagePath]);
        return (int) $this->db->lastInsertId();
    }

    public function update($id, $section, $title, $content, $imagePath)
    {
        $stmt = $this->db->prepare(
            'UPDATE guides SET section = ?, title = ?, content = ?, image_path = ? WHERE id = ?'
        );
        $stmt->execute([$section, $title, $content, $imagePath, (int) $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM guides WHERE id = ?');
        $stmt->execute([(int) $id]);
    }
}
