<?php
class ModelAccountWishlist extends Model {
    public function addWishlist(int $extension_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_wishlist` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' AND `extension_id` = '" . (int)$extension_id . "'");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_wishlist` SET `customer_id` = '" . (int)$this->customer->getId() . "', `extension_id` = '" . (int)$extension_id . "', `date_added` = NOW()");
    }

    public function deleteWishlist(int $extension_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_wishlist` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' AND `extension_id` = '" . (int)$extension_id . "'");
    }

    public function getWishlist(): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_wishlist` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

        return $query->rows;
    }

    public function getTotalWishlist(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_wishlist` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

        return (int)$query->row['total'];
    }
}