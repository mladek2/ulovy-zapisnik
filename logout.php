<?php
session_start();
session_unset();     // Vymaže všechny proměnné session
session_destroy();   // Ukončí session
header('Location: index.php');
exit;
