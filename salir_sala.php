<?php
session_start();
unset($_SESSION['sala_id']);
header("Location: inicio.php");
exit;
?>