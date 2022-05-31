<?php 
//paramètrage de l'API GOOGLE OAuth
//définision des constants pour récuprér le id Client et le code secret de l'api google oAuth
define('GOOGLE_ID', '286540414152-1lp5ul3bdgi045f7v6v499pa7aes7pmi.apps.googleusercontent.com');
define('GOOGLE_SECRET', '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');

//Déclaration des constantes 
define('TYPE_REPONSE', 'code');
define('ID_CLIENT', 'd3e5e6275acaa8bf3d8989e494ddba74864e21fb5548d0efa0fbe032e3757968');
define('ETAT', 'Vérifiée');
define('PORTEE', 'user.metrics');
define('REDIRECT_URI', 'http://localhost/test_api_withings/connect.php');
define('MODE', 'demo');
define('CLIENT_SECRET', "3db32d2fd128403ac173d1b7b397f37d2c235315af8fb63f4806c5819079898c");
define('AUTORIZATION', 'authorization_code');

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
function getAccessToken(): Array{

    //I. Obtenir la signature
    //sort the parameters
    $sortData = "getnonce,".ID_CLIENT.",".time();
    //Generate a hash key value
    define('SIGNATURE', hash_hmac("sha256", $sortData, CLIENT_SECRET));

    //Etape 3: Obtenir le code nonce
    //utilisation de curl pour récupérer les données de l'APi Withings

    /**
     * getNonce
     *
     * @return String
     */
    function getNonce(): String{
        //prorpriétés 
        $nonce = null;

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
                'signature' => SIGNATURE
            ])
        ]);

        //exécution de la session
        $resp = curl_exec($ch);

        //Handler error
        if($error = curl_errno($ch)){
            echo $error;
        } else {
            $datajson = json_decode($resp, true);
            $nonce = $datajson['body']['nonce'];
        }
        // fermeture des ressources
        curl_close($ch);

        return $nonce;
    }
    //prorpriétés 
    $accessToken = null;

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
            'nonce' => getNonce(),
            'signature' => SIGNATURE,
            'client_secret' => CLIENT_SECRET,
            'grant_type' => AUTORIZATION,
            'code' => CODE,
            'redirect_uri' => REDIRECT_URI
        ])
    ]);

    //exécution de la session
    $resp = curl_exec($ch);

    //Handler error
    if($error = curl_errno($ch)){
        echo $error;
    } else {
        $datajson = json_decode($resp, true);
        $accessToken = $datajson ;
    }
     // fermeture des ressources
     curl_close($ch);

     return $accessToken;
}

//Etape 3: Obtenir la ou les dernières mesures de poids d'un utilisateur

//create curl resource
$ch = curl_init();

// configuration des options
curl_setopt_array($ch, [
    CURLOPT_URL => "https://wbsapi.withings.net/measure",
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer '.$acessToken
    ],
    CURLOPT_SSL_VERIFYPEER => FALSE,
    //CURLOPT_CAPATH => "D:\wamp64\bin\php\php7.4.26\extras\ssl\cacert.pem",
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query([ 
        'action' => 'getmeas',
        'meastype' => 1,
        'category' => 1
    ])
]);

//exécution de la session
$resp = curl_exec($ch);

//Handler error
if($error = curl_errno($ch)){
    echo $error;
} else {
    $datajson = json_decode($resp, true);
    $measure = $datajson ;
}
// fermeture des ressources
curl_close($ch);


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







?>