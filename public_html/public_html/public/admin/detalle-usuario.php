<?php
    if(!isset($_GET['id'])) {
        // header("location: usuarios.php");
        exit();
    }
    else{
        var_dump($_GET['id']);
    }
?>