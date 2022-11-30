<?php
session_start();
unset($_SESSION['auth']);
$_SESSION['flash']['succes']="Vous êtes déconnecté";
header('location:index.php');