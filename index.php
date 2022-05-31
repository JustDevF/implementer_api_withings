<?php 
    //récupération des paramètres pour les chaînes de requête endpoind
    require('config.php');
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name=viewport content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Test Api Withings</title>
    </head>
    <body>
        <h1>Se connecter avec Withings</h1>
        <!--Etape 1 : Obtenir le code d’autorisation -->
        <p>
            <button><a href="<?= authorize(); ?>">Connexion</a></button>
        </p>

         <!--access token-->
        <h3>Récupérer la ou les dernières mesures de poids </h3>
    
    </body>
</html>