<?php
/**
 * MyGlassBlog PHP - 退出登录
 */
session_start();
session_destroy();
session_write_close();

require_once __DIR__ . '/../includes/functions.php';
redirect(site_url('admin/login.php'));
