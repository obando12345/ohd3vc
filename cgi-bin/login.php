<?php


// FB SDKのパス
define('LIB_DIR', __DIR__.'/../lib/facebook-php-sdk-v4-4.0-dev/');

define('FACEBOOK_SDK_V4_SRC_DIR', LIB_DIR.'/src/Facebook/');
require LIB_DIR.'autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\GraphLocation;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequestException;

define('APP_ID', '546482198788169');
define('APP_SECRET', 'f53623c36f24a452009d58a9b9a93708');

FacebookSession::setDefaultApplication(APP_ID, APP_SECRET);

session_start();

define('HOST', 'http://localhost:7000/');
$helper  = new FacebookRedirectLoginHelper(HOST.'login.php');
try {
    $session = $helper->getSessionFromRedirect();
} catch(FacebookRequestException $ex) {
    // When Facebook returns an error
} catch(\Exception $ex) {
    // When validation fails or other local issues
}

$scope=null;
if($session) {
    $accessToken = $session->getToken();
    $request = new FacebookRequest(
                            $session,
                            'GET',
                            '/me?fields=id,friends,relationship_status,feed.limit(50)');
    $response = $request->execute();
}else{
        //アクセスできる権限を付与
        $scope = array(
                       'user_friends', //ともだち
                       'user_activities', // アクティビティ
                       'user_relationships', // 交際関係
                       'read_stream', //ニュースフィード
                        );
}
if(!$session) {
    $permit = sprintf('<div><a href="%s"><img src="../img/login_button_facebook_i.jpg" alt="facebook認証"></a></div>', 
                        $helper->getLoginUrl($scope));
}

if(isset($response)) {
    $graphObject = $response->getGraphObject();
    print_r($graphObject->getProperty('relationship_status'));
    print_r($graphObject->getProperty('friends')->getProperty('summary')->getProperty('total_count'));
}

$page = <<<EOD
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body>
    $permit
</body>
</html>
EOD;

print $page;

?>
