<?php
session_start();      // Start session
session_destroy();    // Remove all session data
header("Location: login.php");
exit;
