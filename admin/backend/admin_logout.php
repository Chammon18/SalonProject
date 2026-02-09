<?php
require_once(__DIR__ . '/../auth_check.php');

session_unset();
session_destroy();

header("Location: index.php");
exit;
