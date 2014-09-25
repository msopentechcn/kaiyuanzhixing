<?php
    function getIP(){
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }

    function getGitHubSize($projectname) {
        $url = 'https://api.github.com/repos'.$projectname;

        $cURL = curl_init();

        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($cURL, CURLOPT_USERAGENT,'Git.php');
        curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($cURL);

        curl_close($cURL);
    
        $json = json_decode($result, true);
        
        return $json['size'];
    }
?>