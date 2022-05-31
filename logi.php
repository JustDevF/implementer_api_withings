
<?php
    //démarrage d'une session permettant de connecter l'utilisateur 
    session_start();
    if(!isset($_SESSION['email'])){
        header('Location: connexion.php');
    }

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name=viewport content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Log</title>
    </head>
    <body>
    </body>
</html>