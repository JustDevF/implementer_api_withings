<?php 
    //démarrer une session
    session_start();

    require('config.php');
    
    //récupérer le code d'autorisation renvoyé par google oAuth
    if(isset($_GET['code'])){
        $_SESSION['code'] = $_GET['code'];
         //reflesh page
         header('Refresh');
    
        getMeas();
    }
?>