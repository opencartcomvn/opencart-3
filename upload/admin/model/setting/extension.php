<?php
class ModelSettingExtension extends Model {
    public function getExtensionsByType(string $type): array {
        $extension_data = [];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' ORDER BY `code` ASC");

        foreach ($query->rows as $result) {
            $extension_data[] = $result['code'];
        }

        return $extension_data;
    }

    public function getExtensionByCode(string $type, string $code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");

        return $query->row;
    }

    public function install(string $type, string $code): void {
        $extensions = $this->getExtensionsByType($type);

        if (!in_array($code, $extensions)) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = '" . $this->db->escape($type) . "', `code` = '" . $this->db->escape($code) . "'");
        }
    }

    public function uninstall(string $type, string $code): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = '" . $this->db->escape($type . '_' . $code) . "'");
    }

    public function addExtensionInstall(string $filename, int $extension_download_id = 0): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension_install` SET `filename` = '" . $this->db->escape($filename) . "', `extension_download_id` = '" . (int)$extension_download_id . "', `date_added` = NOW()");

        return $this->db->getLastId();
    }

    public function deleteExtensionInstall(int $extension_install_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension_install` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
    }

    public function getInstalls(int $start = 0, int $limit = 10): array {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_install` ORDER BY `date_added` ASC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getInstallByExtensionDownloadId(int $extension_download_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_install` WHERE `extension_download_id` = '" . (int)$extension_download_id . "'");

        return $query->row;
    }

    public function getExtensionInstallByCode(string $code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_install` WHERE `code` = '" . $this->db->escape($code) . "'");

        return $query->row;
    }

    public function getTotalInstalls(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "extension_install`");

        return (int)$query->row['total'];
    }

    public function addPath(int $extension_install_id, string $path): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension_path` SET `extension_install_id` = '" . (int)$extension_install_id . "', `path` = '" . $this->db->escape($path) . "', `date_added` = NOW()");
    }

    public function deletePath(int $extension_path_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `extension_path_id` = '" . (int)$extension_path_id . "'");
    }

    public function getPathsByExtensionInstallId(int $extension_install_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE `extension_install_id` = '" . (int)$extension_install_id . "' ORDER BY `date_added` ASC");

        return $query->rows;
    }

    public function getPaths(string $path): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE `path` LIKE '" . $this->db->escape($path) . "' ORDER BY `path` ASC");

        return $query->rows;
    }

    public function getTotalPaths(string $path): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "extension_path` WHERE `path` LIKE '" . $this->db->escape($path) . "'");

        return (int)$query->row['total'];
    }
}