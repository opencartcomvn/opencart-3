<?php
/**
 * @package        OpenCart
 * @author         Daniel Kerr
 * @copyright      Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license        https://opensource.org/licenses/GPL-3.0
 * @link           https://www.opencart.com
 */

/**
 * Registry class
 */
final class Registry {
    private array $data = [];

    /**
     * @param    string    $key
     *
     * @return    mixed
     */
    public function get(string $key): object|null {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @param    string    $key
     * @param    string    $value
     */
    public function set(string $key, object $value): void {
        $this->data[$key] = $value;
    }

    /**
     * @param    string    $key
     *
     * @return    bool
     */
    public function has($key) {
        return isset($this->data[$key]);
    }
}