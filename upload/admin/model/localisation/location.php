<?php
class ModelLocalisationLocation extends Model {
    public function addLocation(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "location` SET `name` = '" . $this->db->escape($data['name']) . "', `address` = '" . $this->db->escape($data['address']) . "', `geocode` = '" . $this->db->escape($data['geocode']) . "', `telephone` = '" . $this->db->escape($data['telephone']) . "', `fax` = '" . $this->db->escape($data['fax']) . "', `image` = '" . $this->db->escape($data['image']) . "', `open` = '" . $this->db->escape($data['open']) . "', `comment` = '" . $this->db->escape($data['comment']) . "'");

        return $this->db->getLastId();
    }

    public function editLocation(int $location_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "location` SET `name` = '" . $this->db->escape($data['name']) . "', `address` = '" . $this->db->escape($data['address']) . "', `geocode` = '" . $this->db->escape($data['geocode']) . "', `telephone` = '" . $this->db->escape($data['telephone']) . "', `fax` = '" . $this->db->escape($data['fax']) . "', `image` = '" . $this->db->escape($data['image']) . "', `open` = '" . $this->db->escape($data['open']) . "', `comment` = '" . $this->db->escape($data['comment']) . "' WHERE `location_id` = '" . (int)$location_id . "'");
    }

    public function deleteLocation(int $location_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "location` WHERE `location_id` = '" . (int)$location_id . "'");
    }

    public function getLocation(int $location_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "location` WHERE `location_id` = '" . (int)$location_id . "'");

        return $query->row;
    }

    public function getLocations(array $data = []): array {
        $sql = "SELECT `location_id`, `name`, `address` FROM `" . DB_PREFIX . "location`";

        $sort_data = [
            'name',
            'address'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY `name`";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalLocations(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "location`");

        return (int)$query->row['total'];
    }
}