<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Database Constants
defined('DB_SERVER') ? null : define("DB_SERVER", "localhost"); // Usually localhost
defined('DB_USER')   ? null : define("DB_USER", "db_username"); // Username now stored outside version control
defined('DB_PASS')   ? null : define("DB_PASS", "password"); // Password now stored outside version control
defined('DB_NAME')   ? null : define("DB_NAME", "corona");

?>