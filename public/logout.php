<?php
session_start();
require_once '../php/includes/auth.php';
distruggiSessione();
header("Location: ../index.php");
exit;
?>