<?php 
//Déclaration des constantes 
define('TYPE_REPONSE', 'code');
define('ID_CLIENT', '##');
define('ETAT', 'Vérifiée');
define('PORTEE', 'user.metrics');
define('REDIRECT_URI', 'http://localhost/test_api_withings/connect.php');
define('MODE', 'demo');
define('CLIENT_SECRET', "##");
define('AUTORIZATION', 'authorization_code');
define('GRANT_TYPE_AUTORIZECODE', 'authorization_code');
define('GRANT_TYPE_REFRESH_TOKEN', 'refresh_token');


//code d'autorisation
if(isset($_GET['code'])){
    define('CODE', $_SESSION['code']);
}

//Etape 1: Obtenir le code d'autorisation
/**
 * méthode authorize permet d'obtenir le code d'autorisation de l'API 
 *
 * @return String
 */
function authorize(): String{
    return "https://account.withings.com/oauth2_user/authorize2/?response_type=".TYPE_REPONSE."&client_id=".ID_CLIENT."&state=".ETAT."&scope=".PORTEE."&redirect_uri=".urldecode(REDIRECT_URI)."&mode=". MODE ;
}


//Etape 4: Get access token
function getAccessToken(): String{

    //I. Obtenir la signature
    //sort the parameters
    $sortData = "getnonce,".ID_CLIENT.",".time();
    //Generate a hash key value
    $signature = hash_hmac("sha256", $sortData, CLIENT_SECRET);

    //II. Obtenir le code nonce
    //utilisation de curl pour récupérer les données de l'APi Withings

    //prorpriété
    $accessToken = Array();
    $current_refresh_token = '';

    //create curl resource
    $ch = curl_init();

    // configuration des options
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://wbsapi.withings.net/v2/signature",
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        //CURLOPT_CAPATH => "D:\wamp64\bin\php\php7.4.26\extras\ssl\cacert.pem",
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query([ 
            'action' => 'getnonce',
            'client_id' => ID_CLIENT,
            'timestamp' => time(),
            'signature' => $signature
        ])
    ]);

    //exécution de la session
    $resp = curl_exec($ch);

    //Handler error
    if($error = curl_errno($ch)){
        echo $error;
    } else {
        $dataNonce = json_decode($resp, true);
        $nonce = $dataNonce ['body']['nonce'];
         // fermeture des ressources
         curl_close($ch);
         
        //III. AccessToken

        //create curl resource
        $ch = curl_init();

        // configuration des options
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://wbsapi.withings.net/v2/oauth2",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            //CURLOPT_CAPATH => "D:\wamp64\bin\php\php7.4.26\extras\ssl\cacert.pem",
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([ 
                'action' => 'requesttoken',
                'client_id' => ID_CLIENT,
                'nonce' => $nonce,
                'client_secret' => CLIENT_SECRET,
                'grant_type' => GRANT_TYPE_AUTORIZECODE,
                'code' => CODE,
                'redirect_uri' => REDIRECT_URI,
                //'signature' => $signature, //erreur, signature invalide ?, it works withous signature
            ])
        ]);

        //exécution de la session
        $resp = curl_exec($ch);

        //Handler error
        if($error = curl_errno($ch)){
            echo $error;
        } else {
            $dataAccessToken = json_decode($resp, true);
            if(isset($dataAccessToken['body']['refresh_token'])){
                echo $dataAccessToken['body']['refresh_token'];
                $current_refresh_token = $dataAccessToken['body']['refresh_token'];
            }
            
           
        }
        // fermeture des ressources
        curl_close($ch);
        }
        var_dump($current_refresh_token);


        //IV. Refresh access_token
        //create curl resource
        $ch = curl_init();

        // configuration des options
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://wbsapi.withings.net/v2/oauth2",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            //CURLOPT_CAPATH => "D:\wamp64\bin\php\php7.4.26\extras\ssl\cacert.pem",
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([ 
                'action' => 'requesttoken',
                'client_id' => ID_CLIENT,
                'nonce' => $nonce,
                'client_secret' => CLIENT_SECRET,
                'grant_type' => GRANT_TYPE_REFRESH_TOKEN,
                'code' => CODE,
                'refresh_token' => $current_refresh_token
            ])
        ]);

        //exécution de la session
        $resp = curl_exec($ch);

        //Handler error
        if($error = curl_errno($ch)){
            echo $error;
        } else {
            $datarReflesh_token  = json_decode($resp, true);
            
            if(isset($datarReflesh_token['body']['access_token'])){
                $accessToken = $datarReflesh_token['body']['access_token'];
                $current_refresh_token = $datarReflesh_token['body']['refresh_token'];
                echo $accessToken;
            }
            
        }
        // fermeture des ressources
        curl_close($ch);
       
        return $accessToken;
}

function getMeas(){

    echo getAccessToken();
}


?>