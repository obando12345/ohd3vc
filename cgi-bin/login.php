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
    
    $rea10_count = 0;

    // $married[] = '結婚しているか？';
    $married[] = $graphObject->getProperty('relationship_status');
    if($married[0] == 'Married') {
        $married[] = '既婚';
        $married[] = 'NG';
        $rea10_count++;
    } else {
        $married[] = '未婚';
        $married[] = 'OK';
    }

    // $friends[] = '友達の数';
    $friends[] = $graphObject->getProperty('friends')->getProperty('summary')->getProperty('total_count');
    if($friends[0] >= 100) {
        $friends[] = '多い';
        $friends[] = 'NG';
        $rea10_count++;
    } else {
        $friends[] = '少ない';
        $friends[] = 'OK';
    }

    $result = true;
    if($rea10_count > 0) {
        $result = false;
    }

$page = <<<EOD
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/my.css">
    <script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $(function(){
            var data = [
                {question: "友達の数", reason: "$friends[0]人：$friends[1]", status: "$friends[2]"},
                {question: "結婚しているか？", reason: "$married[1]", status: "$married[2]"},
                {result: $result}
            ];

            $.each(data, function(i, val){
                var ele_id = "ele_"+i;

                var canvas = document.getElementById("clear_box");

                if ( ! canvas || ! canvas.getContext ) {
                    return false;
                }
                canvas.getContext('2d');
                var ctx = canvas.getContext("2d");
                ctx.beginPath();
                ctx.clearRect(0, 0, 64, 64);

                var result_img = canvas.toDataURL();

                var $ele = $("<li style='font-size:x-large' class='media slideLeft'><div class='media-left'><img id='"+ele_id+"' class='media-object' src='"+result_img+"'></div><div class='media-body'><h3 class='media-heading'>"+val.question+"</h3>"+val.reason+"</div></li><hr>");

                var delay = 3000*(i+1);             
                setTimeout(function(){
                    if('result' in val && val.result == false) {
                        $.ajax({
                            type: "GET",
                            url: "sample/cloudbits.php?percent=100",
                            success: function(){
                                console.log("ajax success");
                            },
                            error: function(req, st, e) {
                                consol.log(st);
                            }
                        });
                        return;
                    }
                    $("#list").append($ele);
                    $('html,body').animate({ scrollTop: $("#bottom").offset().top }, 'fast');
                    setTimeout(function(){
                        if(val.status == "OK") {
                            var result_img = "check-mark-10-64.png";
                        } else {
                            var result_img = "x-mark-64.png";
                        }
                        $("#"+ele_id).attr("src", "img/"+result_img);
                    }, 2000);
                }, delay);
            });
        });
    </script>
</head>
<body>
    <div id="contents" class="container">
        <h2 class="jumbotron">判定結果</h2>
        <ul id="list" class="media-list">
        </ul>
    </div>
    <div id="bottom"></div>
    <canvas id="clear_box" style="display:none;" width="64" height="64"></canvas>
</body>
</html>


EOD;
} else {
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
}

print $page;

?>
