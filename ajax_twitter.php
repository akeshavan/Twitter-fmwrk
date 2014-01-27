<?php

header("Content-Type: application/json");

require_once("twitteroauth/twitteroauth/twitteroauth.php"); //Path to twitteroauth library

$result = array();


if (isset($_POST['searchText']) && !is_null($_POST['searchText']))
{
    $search = $_POST['searchText'];

    error_log($search);

    if(isset($_POST['functionName']) && !is_null($_POST['functionName']) && $_POST['functionName'] == 'getTwitterJSON')
    {
        error_log("Function : " . $_POST['functionName']);
        $notweets = 15;
        $consumerkey = "4uGsdz8iqqNSyVCBpehQLg";
        $consumersecret = "f482xiwQ1URCIwdG8h7iEGEuCNYolkEY0BNO2VzKYzk";
        $accesstoken = "140177155-j9M3NziZyPZvg6pAcerNjKxSGXoqPH0SGcCmOhF0";
        $accesstokensecret = "4rxd5fOby9gsmWKAC3ZKkgwrsyuTujPfeH6EDKCs1TV2h";

        function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
            $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
            return $connection;
        }

        $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

        $tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?q=".str_replace("#", "%23", $search)."&count=".$notweets);

        $outputFileName = $search . "|" . time().  '.json';
        $outputFile = fopen($outputFileName, 'w');
        fwrite($outputFile, json_encode($tweets));
        fclose($outputFile);

        $result['success'] = true;
        $result['data'] = $tweets;
    }

    if(isset($_POST['functionName']) && !is_null($_POST['functionName']) && $_POST['functionName'] == 'consolidateJSON')
    {
        $allJsonData = array();
        $existingIds = array();

        error_log("Function : " . $_POST['functionName']);
        $dir = new DirectoryIterator(dirname(__FILE__));
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $fileName = $fileinfo->getFilename();
                if(strpos($fileName, ".json") !== FALSE && strpos($fileName, "-CONSOLIDATED") === FALSE) {
                    $searchInfo = explode("|", $fileName);
                    if(strpos($searchInfo[0], $search) !== FALSE) {
                        $jsonContents = json_decode(file_get_contents($fileName), true);
                        $tweets = $jsonContents['statuses'];

                        foreach($tweets as $tweet) {
                            if(!isset($existingIds[$tweet['id']])){
                                $allJsonData[] = $tweet;
                                $existingIds[$tweet['id']] = true;
                            }
                        }
                    }
                }
            }
        }

        $outputFileName = $search . "-CONSOLIDATED~" . time().  '.json';
        $outputFile = fopen($outputFileName, 'w');
        fwrite($outputFile, json_encode($allJsonData));
        fclose($outputFile);

        $result['success'] = true;

    }


} else {
    $result['success'] = false;
    $result['error'] = 'Request type cannot be null';
}

print json_encode($result);