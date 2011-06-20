<?php
interface Lms_Registry_Adapter_Interface {
    public function get($key, $default = null);
    public function set($key, $value);
}
?>