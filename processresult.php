<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
        <meta name="description" content="validationtool">
        <meta name="keywords" content="validationtool">

        <link rel="stylesheet" type="text/css" href="/css/validateresults.css">
        <link href="/css/bootstrap/3.2.0/bootstrap.custom.css" rel="stylesheet">
		<link href="/css/2stage.css" rel="stylesheet">

        <title>评估结果</title>
        
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <div class="container">
                    <div class="navbar-logo"><a href="http://www.kaiyuanshe.cn/index.php"><img src="http://www.kaiyuanshe.cn/templates/hli/css/images/logo.png" height="55"></a></div>
                    <div class="navbar-collapse">
                        <ul class="navbar-nav touch-menu">
                            <li><span class="glyphicon glyphicon-th" id="touch-menu-btn"></span></li>
                        </ul>

                        <ul class="navbar-nav navbar-list">
                            <li>
                                <a href="http://www.kaiyuanshe.cn/index.php">主页</a>
                            </li>
                            <li class="active">
                                <a href="http://www.kaiyuanshe.cn/star-home.html">开源之星计划</a>
                            </li>
                            <li ><a href="http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=8">开源参考文档</a></li>
                            <li class=""><a href="http://www.kaiyuanshe.cn/ambassador-home.html">开源大使计划</a></li>
                            <li><a href="http://www.kaiyuanshe.cn/about.html">关于我们</a></li>
                        </ul>
                        <!--<ul class="navbar-nav navbar-search">
                            <li>
                                <form id="search-form" class="search-form" action="">
                                    <input type="text" placeholder="search">
                                    <span class="glyphicon glyphicon-search search-form-submit"></span>
                                </form>
                            </li>
                        </ul>-->
                    </div>
                </div>
            </div>

        <div class="content">
            <div class="column-header">
                <div class="container">
                    <h1 class="title">开源许可评估结果</h1>
                </div>
            </div>

        <div class="container comparison">
        <?php
            require_once("util/Helper.php");
            require_once('licensediff.php');
            require_once('util/Git.php');
            require_once('util/SvnPeer.php');
            require_once('util/Logger.php');

            $logger = new Logger("log");
            $loghelperArr = array();
            $logger->log('info', '<===================================================>', $loghelperArr);

            chdir("tmp");
                        
            function deldir($dir) {
              // remove the files under the current folder
              $dh=opendir($dir);
              while ($file=readdir($dh)) {
                if($file!="." && $file!="..") {
                  $fullpath=$dir."/".$file;
                  if(!is_dir($fullpath)) {
                      chmod($fullpath, 0777);
                      unlink($fullpath);
                  } else {
                      chmod($fullpath, 0777);
                      deldir($fullpath);
                  }
                }
              }
 
              closedir($dh);
              // remove current directory
              if(rmdir($dir)) {
                return true;
              } else {
                return false;
              }
            }

            function RemoveStatusRecords($sid, $conn, $logger, $loghelperArr) {
                $sqlComm = "delete from kys.processstatus where chSessionId = \"".$sid."\"";
                try{
                    $result = mysql_query($sqlComm, $conn);
                }catch(Exception $e) {
                    $logger->log('error', 'Remove Status Records got exception: '.$e->getMessage(), $loghelperArr);
                }
            }

            function InsertStatusRecords($sid, $stat, $conn, $logger, $loghelperArr) {
                $latestTime = date('y-m-d h:i:s',time());
                $sqlComm = "insert into kys.processstatus(chSessionId, chStatus, dtUpdateTime) values(\"".$sid."\", \"".$stat."\", \"".$latestTime."\")";
                try{
                    $result = mysql_query($sqlComm, $conn);
                }catch(Exception $e) {
                    $logger->log('error', 'Insert Status Records got exception: '.$e->getMessage(), $loghelperArr);
                }
            }

            function InsertRecords($urlText, $validationresult, $projectname, $projectsite, $projectversion, $clientip, $repotype, $conn, $logger, $loghelperArr) {

                $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
                try{
                    $result = mysql_query($sqlComm, $conn);
                }catch(Exception $e) {
                    $logger->log('error', 'Select records from table repourls got exception: '.$e->getMessage(), $loghelperArr);
                }
                $rowCount = mysql_num_rows($result);
                $row = mysql_fetch_array($result, MYSQL_NUM);

                if($rowCount == 0) {
                    $sqlComm = "insert into kys.repourls(chRepoURL) values(\"".$urlText."\")";
                    try{
                        $result = mysql_query($sqlComm, $conn);
                    }catch(Exception $e) {
                        $logger->log('error', 'Insert records into table repourls got exception: '.$e->getMessage(), $loghelperArr);
                    }
                    
                    $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
                    try{
                        $result = mysql_query($sqlComm, $conn);
                    }catch(Exception $e) {
                        $logger->log('error', 'Select records from table repourls got exception: '.$e->getMessage(), $loghelperArr);
                    }
                    $row = mysql_fetch_array($result, MYSQL_NUM);

                    $currentidURL = $row[0];

                    $latestTime = date('y-m-d h:i:s',time());
                    $sqlComm = "insert into kys.reprovisithistory(idRepoURLs, chResult, timeLastVisit, chProName, chProSite, chVersion, chIPAddr, chRepoType) values(".$currentidURL.", \"".$validationresult."\", \"".$latestTime."\", \"".$projectname."\", \"".$projectsite."\", \"".$projectversion."\", \"".$clientip."\", \"".$repotype."\")";
                    try{
                        $result = mysql_query($sqlComm, $conn);
                    }catch(Exception $e) {
                        $logger->log('error', 'Insert records into table reprovisithistory got exception: '.$e->getMessage(), $loghelperArr);
                    }
                }
                else {
                    $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
                    try{
                        $result = mysql_query($sqlComm, $conn);
                    }catch(Exception $e) {
                        $logger->log('error', 'Select records from table repourls got exception: '.$e->getMessage(), $loghelperArr);
                    }
                    $row = mysql_fetch_array($result, MYSQL_NUM);

                    $latestTime = date('y-m-d h:i:s',time());

                    $currentidURL = $row[0];
                    $sqlComm = "insert into kys.reprovisithistory(idRepoURLs, chResult, timeLastVisit, chProName, chProSite, chVersion, chIPAddr, chRepoType) values(".$currentidURL.", \"".$validationresult."\", \"".$latestTime."\", \"".$projectname."\", \"".$projectsite."\", \"".$projectversion."\", \"".$clientip."\", \"".$repotype."\")";
                    try{
                        $result = mysql_query($sqlComm, $conn);
                    }catch(Exception $e) {
                        $logger->log('error', 'Insert records into table reprovisithistory got exception: '.$e->getMessage(), $loghelperArr);
                    }
                }
            }

            function GetCertDate($urlText, $projectname, $proVer, $conn, $logger, $loghelperArr) {
                $urlText = trim($urlText);
                $sqlComm = "select timeLastVisit from kys.checkpasshistory where RepoURLs = \"".$urlText."\" and chVersion = \"".$proVer."\" and chProName = \"".$projectname."\"";
                try{
                   $result = mysql_query($sqlComm, $conn); 
                }catch(Exception $e) {
                   $logger->log('error', 'Get cerID from kys.checkpasshistory got exception: '.$e->getMessage(), $loghelperArr); 
                }
                $row = mysql_fetch_array($result, MYSQL_NUM);

                $dtLastTime = $row[0];
                return $dtLastTime;
            }

            function GetCertID($urlText, $projectname, $proVer, $conn, $logger, $loghelperArr) {
                $urlText = trim($urlText);
                $sqlComm = "select cerID from kys.checkpasshistory where RepoURLs = \"".$urlText."\" and chVersion = \"".$proVer."\" and chProName = \"".$projectname."\"";
                try{
                   $result = mysql_query($sqlComm, $conn); 
                }catch(Exception $e) {
                   $logger->log('error', 'Get cerID from kys.checkpasshistory got exception: '.$e->getMessage(), $loghelperArr); 
                }
                $row = mysql_fetch_array($result, MYSQL_NUM);

                $certID = $row[0];
                return $certID;
            }

            function InsertPassRecords($urlText, $validationresult, $projectname, $projectsite, $projectversion, $clientip, $repotype, $conn, $logger, $loghelperArr) {
                $sqlComm = "select cerID from kys.checkpasshistory where RepoURLs = \"".$urlText."\" and chVersion = \"".$projectversion."\" and chProName = \"".$projectname."\"";
                try{
                   $result = mysql_query($sqlComm, $conn); 
                }catch(Exception $e) {
                   $logger->log('error', 'Get cerID from kys.checkpasshistory got exception: '.$e->getMessage(), $loghelperArr); 
                }
                $row = mysql_fetch_array($result, MYSQL_NUM);

                $certID = $row[0];
                if($certID == '') {
                    // Get latest certID
                    $sqlComm = "select cerID from kys.checkpasshistory order by cerID desc LIMIT 1";
                    try{
                        $result = mysql_query($sqlComm, $conn);
                    }catch(Exception $e) {
                        $logger->log('error', 'Get latest cerID from kys.checkpasshistory got exception: '.$e->getMessage(), $loghelperArr);
                    }
                    $latestTime = date('y-m-d h:i:s',time());
                    $row = mysql_fetch_array($result, MYSQL_NUM);
                    $latestCertID = $row[0];
                    if($latestCertID == '') {
                        $latestCertID = '000001';
                        
                        $sqlComm = "insert into kys.checkpasshistory(RepoURLs, chResult, timeLastVisit, chProName, chProSite, chVersion, chIPAddr, chRepoType, cerID) values(\"".$urlText."\", \"".$validationresult."\", \"".$latestTime."\", \"".$projectname."\", \"".$projectsite."\", \"".$projectversion."\", \"".$clientip."\", \"".$repotype."\", \"".$latestCertID."\")";

                        try{
                            $result = mysql_query($sqlComm, $conn);
                        }catch(Exception $e) {
                            $logger->log('error', 'Insert latest cerID into kys.checkpasshistory got exception: '.$e->getMessage(), $loghelperArr);
                        }
                    }else {
                        $latestCertID = $latestCertID + 1;
                        $len = strlen($latestCertID);
                        $tmp = '';
                        if($len < 6) {
                            for($i = 0; $i < 6 - $len; $i++) {
                                $tmp .= '0';
                            }
                            $tmp = $tmp.$latestCertID;
                            $latestCertID = $tmp;
                        }

                        $sqlComm = "insert into kys.checkpasshistory(RepoURLs, chResult, timeLastVisit, chProName, chProSite, chVersion, chIPAddr, chRepoType, cerID) values(\"".$urlText."\", \"".$validationresult."\", \"".$latestTime."\", \"".$projectname."\", \"".$projectsite."\", \"".$projectversion."\", \"".$clientip."\", \"".$repotype."\", \"".$latestCertID."\")";

                        try{
                            $result = mysql_query($sqlComm, $conn);
                        }catch(Exception $e) {
                            $logger->log('error', 'Insert latest cerID into kys.checkpasshistory got exception: '.$e->getMessage(), $loghelperArr);
                        }
                    }
                }
            }

            // Validate the variables from POST request
            if($_POST["projectname"] == "") {
                echo "缺少项目名称";
                die();
            }

            if($_POST["reprourl"] == "") {
                echo "缺少项目代码仓库URL";
                die();
            } 
            if($_POST["projectsite"] == "") {
                echo "缺少项目网站";
                die();
            }
            if($_POST["rptype"] == "") {
                echo "缺少项目类型";
                die();
            }
            if($_POST["ipaddr"] == "") {
                echo "非有效IP";
                die();
            }

            $proName = $_POST["projectname"];
            $proSite = $_POST["projectsite"];
            $proVer = $_POST["version"];
            $ipAddr = $_POST["ipaddr"];
            $sessionId = $_POST["sessionid"];

            // Initialize MySQL connection and query if it is allowed to verify
            $dbconf = file('../conf/db.json');
            $dbjson = '';
            foreach($dbconf as $line) {
                $dbjson .= $line;
            }
            $decodedDbConf = json_decode($dbjson, TRUE);

            $conn = mysql_connect($decodedDbConf['server'],$decodedDbConf['user'],$decodedDbConf['password']) or die ("数据连接错误!!!");

            InsertStatusRecords($sessionId, "正在探测许可证文件", $conn, $logger, $loghelperArr);

            $urlText = $_POST["reprourl"];

            mysql_select_db('kys',$conn);

            $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
            try{
                $result = mysql_query($sqlComm, $conn);
            }catch(Exception $e) {
                $logger->log('error', 'Select records from table repourls got exception: '.$e->getMessage(), $loghelperArr);
            }
            $rowCount = mysql_num_rows($result);
            $row = mysql_fetch_array($result, MYSQL_NUM);

            if($rowCount != 0) {
                $idResult = $row[0];
                $sqlComm = "select timeLastVisit from reprovisithistory where idRepoURLs = ".$idResult."  and chIPAddr = \"".$ipAddr."\" order by timeLastVisit desc LIMIT 1";
                try{
                    $result = mysql_query($sqlComm, $conn);
                }catch(Exception $e) {
                    $logger->log('error', 'Select last visit time from table reprovisithistory got exception: '.$e->getMessage(), $loghelperArr);
                }
                $rowCount = mysql_num_rows($result);
                $row = mysql_fetch_array($result, MYSQL_NUM);
                if($rowCount != 0) {
                    $lastVisit = $row[0];
                    $currentTime = date('y-m-d h:i:s',time());
                    $diff = strtotime($currentTime) - strtotime($lastVisit);
                    if(abs($diff) < 60) {
                        echo "访问很频繁，此开源项目正在被验证过程中。。。";
                        die();
                    }
                }
            }

            // Put license file patterns into array
            $licensepatterns = array('License', 'License.txt', 'License.md', 'Copying', 'Copying.txt', 'Copyright', 'Copyright.txt');

            // Load keywords from json file based on current license folder
            $licensedetectkeywords = getKeywordsArray();

            $logger->log('debug', "Start loading license files at: ".date('Y-m-d H:i:s',time()), $loghelperArr);

            // Initialize Standard License File Hashtable mem singleton object
            $licensecollection = array();
            if ($handle = opendir('../StandardLicenses')) {
               while (false !== ($entry = readdir($handle))) {
                   if ($entry != "." && $entry != "..") {
                       $tempfilecontentarr = file("../StandardLicenses/".$entry);
                       $tempfilecontent = "";
                       foreach($tempfilecontentarr as $line) {
                           $tempfilecontent.=$line;
	                   }
                       $licensecollection[$entry] = $tempfilecontent;
                   }
               }
               closedir($handle);
            }

            $licensefilenames = array_keys($licensecollection);
            $licesnefilecomtents = array_values($licensecollection);

            $logger->log('debug', "End loading license files at: ".date('Y-m-d H:i:s',time()), $loghelperArr);

            if(count($licensedetectkeywords) != count($licensecollection)) {
                $logger->log('error', "The number of keywords and license files are not consistent", $loghelperArr);
            }

            // Determine the protocol type
            $protocoltype = $_POST["rptype"];
            $curl_result = "";

            // Diff the license file
            switch($protocoltype) {
                case "github":
                    $logger->log('debug', "The prototype is GitHub", $loghelperArr);

                    $githubProName = '';
                    $existLincese = FALSE;

                    $sitescontent = file('../conf/approved-sites.json');
                    $approvedsites = '';
                    foreach($sitescontent as $line) {
                        $approvedsites.=$line;
	                }

                    $decodedArr = json_decode($approvedsites, TRUE);
                    

                    foreach($decodedArr as $siteInstance) {
                        $comresult = stripos($urlText, $siteInstance['website']);
                        if($comresult !== FALSE) {
                            $pos1 = stripos($urlText, $siteInstance['website']);
                            $sub1 = substr($urlText, $pos1 + strlen($siteInstance['website']));
                            $comresult = stripos($urlText, '.git');
                            if($comresult !== FALSE) {
                                $pos2 = strrpos($sub1, "git");
                            }else{
                                $pos2 = strlen($sub1) + 1;
                            }
                            $sub2 = substr($sub1, 0, $pos2 - 1);
                            if($siteInstance['website'] == 'github.com') {
                                $githubProName = $sub2;
                            }
                            $urlTextRaw = $siteInstance['prefix'].$sub2.$siteInstance['suffix'];
                        }
                    }
                    
                    $logger->log('debug', $urlTextRaw, $loghelperArr);

                    $logger->log('debug', 'Start HttpRequests at: '.date('Y-m-d H:i:s', time()), $loghelperArr);
                    $start_time = microtime();

                    InsertStatusRecords($sessionId, "正在探测许可文件", $conn, $logger, $loghelperArr);

                    // Send GET requests and try different kinds of license files
                    foreach($licensepatterns as $singlefile) {
                        $comresult = stripos($urlTextRaw, 'coding.net');
                        if($comresult !== FALSE) {
                            break;   
                        }

                        $tempURL = $urlTextRaw."/".$singlefile;
                        $comresult = stripos($singlefile, ".");
                        if($comresult !== FALSE) {
                            $pos1 = stripos($singlefile, '.');
                            $sub1 = substr($singlefile, 0, $pos1);
                            $sub2 = substr($singlefile, $pos1);
                            $result = strtoupper($sub1).$sub2;
                        }
                        else {
                            $result = strtoupper($singlefile);
                        }
                        $lowerURL = $urlTextRaw."/".$result;
                        $upperURL = $urlTextRaw."/".strtolower($singlefile);
                        $requests = array($tempURL, $lowerURL, $upperURL);

                        // Use multi-curl requests
                        $main = curl_multi_init();
                        $results = array();
                        $count = count($requests);
                        for($i = 0; $i < $count; $i++) 
                        {  
                            $handles[$i] = curl_init();
                            curl_setopt($handles[$i], CURLOPT_URL, $requests[$i]);
                            curl_setopt($handles[$i], CURLOPT_TIMEOUT, 10);
	                        curl_setopt($handles[$i], CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($handles[$i], CURLOPT_SSL_VERIFYHOST, false);  
                            curl_setopt($handles[$i], CURLOPT_RETURNTRANSFER, 1);  
                            curl_multi_add_handle($main, $handles[$i]);
                        }
                        
                        $running = 0; 
                        do {  
                            curl_multi_exec($main, $running);
                        }while($running > 0);

                        $detectPt = 0;
                        for($i = 0; $i < $count; $i++){                        
                            $statuscode = curl_getinfo($handles[$i], CURLINFO_HTTP_CODE);
                            if($statuscode == 200) {
                                $existLincese = TRUE;
                                $curl_result = curl_multi_getcontent($handles[$i]);
                                $detectPt = $i;
                                break;
                            }
                            curl_close($handles[$i]);
                            curl_multi_remove_handle($main, $handles[$i]);
                        }
                        
                        curl_multi_close($main);

                        if($existLincese == TRUE) {
                            $logger->log('debug', 'found licesefile: '.$singlefile, $requests);
                            break;
                        }
                    }          

                    $logger->log('debug', 'End HttpRequests at: '.date('Y-m-d H:i:s', time()), $loghelperArr);
                    $end_time = microtime();
                    $difftime = abs($end_time - $start_time);
                    $logger->log('debug', 'time cost: '.$difftime, $loghelperArr);

                    if($existLincese == TRUE) {
                        $logger->log('debug', 'License file found', $loghelperArr);
                        InsertStatusRecords($sessionId, "正在比较许可文件", $conn, $logger, $loghelperArr);

                        // Pick up the right license files
                        $keyFiles = array();
                        foreach($licensedetectkeywords as $key => $value) {
                            $comresult = stripos($curl_result, $value);
                            if($comresult !== FALSE) {
                                array_push($keyFiles, $key);
                            }
                        }

                        $logger->log('debug', 'Picked up license files: ', $keyFiles);
                        foreach($keyFiles as $key => $value) {
                            $logger->log('debug', 'Key file['.$key."]: ".$value);
                        }
                        $logger->log('debug', 'The length of keyFiles: '.count($keyFiles), $loghelperArr);

                        if(count($keyFiles) == 0) {
                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentence\">许可证文件库未发现匹配文件</span>
                                    </div>
                                    <div id=\"declare\">
                                        <span>原因: 在我们的许可证文件库未发现您的许可证类型: </span>
                                    </div>
                                    <div id=\"sourlink\">
                                        <a href=\""; 
                                echo $urlText; 
                                echo "\">";
                                echo $urlText; 
                                echo "</a>
                                    <br>我们会稍后评估并补充许可证文件库</div>
                                    <div id=\"moreinfo\">
                                        <span>请参考下面的链接:</span><br>
                                        <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                        <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                        如果对检测结果有任何意见和反馈, 您可以<a href=\"/feedback.php\">联系我们</a>
                                        <div><a href=\"/licensing.php\">完成</a></div>
                                    </div>
                                </div>";
                            InsertRecords($urlText, "none", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                            break;
                        }

                        // Compare original license file with standard license files
                        $diffOpcodeArr = array();
                        $minDiff = 0;
                        $minKey = 0;
                        $maxRatio = 0;
                        $maxKey = 0;

                        $curl_resultProcessed = trim($curl_result);
                        $curl_resultProcessed = trim(preg_replace('/\s+/', ' ', $curl_resultProcessed));
                        $curl_resultProcessed = preg_replace('/s(?=s)/', '', $curl_resultProcessed);
                        $curl_resultProcessed = htmlspecialchars_decode($curl_resultProcessed);

                        foreach($keyFiles as $key => $value) {
                            $licenseProcessedFile = trim($licensecollection[$value]);
                            $licenseProcessedFile = preg_replace('/s(?=s)/', '', $licenseProcessedFile);
                            $licenseProcessedFile = trim(preg_replace('/\s+/', ' ', $licenseProcessedFile));

                            array_push($diffOpcodeArr, LicenseDiff::compareLicense($licenseProcessedFile, $curl_resultProcessed));
                            if(LicenseDiff::$diffLength < $minDiff || $minDiff == 0) {
                                $minDiff = LicenseDiff::$diffLength;
                                $minKey = $key;
                            }
                        }

                        $comparedStandardLicenseFileContent = $diffOpcodeArr[$minKey];

                        if(verifyPass($comparedStandardLicenseFileContent, $licensecollection[$keyFiles[$minKey]])) {

                            InsertRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            InsertPassRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            $certID = GetCertID($urlText, $proName, $proVer, $conn, $logger, $loghelperArr);
                            $certDate = GetCertDate($urlText, $proName, $proVer, $conn, $logger, $loghelperArr);
                            $ln = substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentencepass\">评估通过!</span>
                                    </div>
                                    <div class=\"permanent-link\">证书永久Link: <a href='/gethistory.php?certID=$certID&ln=$ln'>点这里</a></div>
                                    <div class=\"star-content\">
                                        <div class=\"left-arrow\"></div>
                                        <div class=\"right-arrow\"></div>
                                        <div class=\"star-inner\">
                                            <div class=\"star-badge\"></div>
                                            <div class=\"star-text\">
                                                <h1>开源之星认证</h1>
                                                <h2>";
                            echo $proName;
                            echo "</h2>
                                        <p class=\"address\">源代码库地址：";
                            echo $urlText;
                            echo "</p>
                                        <p class=\"result\" id=\"result\">经验证符合";
                            echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                            echo "的标准</p><p class=\"thanks\">感谢您对中国开源社区的贡献！</p>
                                        </div>
                                        <div class=\"star-footer\">
                                            <div class=\"logo\"></div>
                                            <p class=\"number\">NO.";
                            echo $certID;
                            echo "</p>
                                <p class=\"date\">";
                            echo $certDate; 
                            echo "</p>
                            </div></div></div>
                            <div class=\"page-jump\">
                                <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                            </div>
                            </div>";
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                        }
                        else {
                            InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);

                            echo "<div id=\"checkwithfailed\">
                                            <span id=\"titleresult\">评估结果:</span>
                                            <span id=\"resultsentence\">没有通过评估</span>
                                        </div>
                                        <div id=\"declare\">
                                            <p>原因: 您的许可证文件已经被检测到, 但是内容并没有完全匹配到由OSI批准的";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "许可证文件内容, 详细文本之间的差别请看下面:</p>
                                <p><span style=\"background-color:#005500\">&nbsp;&nbsp;&nbsp;&nbsp;</span>代表比标准文本增加的部分</p><p><span style=\"background-color:#990000\">&nbsp;&nbsp;&nbsp;&nbsp;</span>代表比标准文本减少的部分</span></p>
                                        </div>
                                        <div id=\"originaldiv\">
                                            <div>您的许可证比较结果</div>
                                            <div id=\"originalcontent\"><pre>";
                                echo $comparedStandardLicenseFileContent; 
                                echo "</pre></div>
                                        </div>
                                        <div id=\"standarddiv\">
                                        <div>";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "</div>
                                            <div id=\"standardcontent\"><pre>";
                                echo htmlspecialchars($licensecollection[$keyFiles[$minKey]]);
                                echo "</pre></div>
                                <div class=\"contact-us\">如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a></div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                                        </div>
                                        </div>";
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                        }
                    }
                    else {
                        $logger->log('debug', 'License file not found', $loghelperArr);
                        InsertStatusRecords($sessionId, "快速检测未找到许可文件，正在克隆代码仓库副本以便进一步检测", $conn, $logger, $loghelperArr);

                        // Git clone the repository based on git URL

                        Git::windows_mode();
                        $repo = new GitRepo();

                        // Get git client folder name
                        $comresult = stripos($urlText, '.git');
                        if($comresult !== FALSE) {
                            $indexOfLastSplash = strrpos($urlText, "/");
                            $indexOfLastDot = strrpos($urlText, '.');
                            $GitName = substr($urlText, $indexOfLastSplash + 1, $indexOfLastDot - $indexOfLastSplash - 1);
                        }else{
                            $indexOfLastSplash = strrpos($urlText, '/');
                            $GitName = substr($urlText, $indexOfLastSplash + 1);
                        }

                        $logger->log('debug', 'GitName: '.$GitName, $loghelperArr);

                        $ran = rand();
                        $timeparts = explode(' ',microtime());
                        $etime = $timeparts[1].substr($timeparts[0],1);
                        $tempName = $ran.$timeparts[1];
                        $foldername = $GitName.$tempName;

                        $comresult = stripos($urlText, "github.com");
                        if($comresult !== FALSE) {
                            $githubRepoSize = getGitHubSize($githubProName);
                            $logger->log('debug', 'Repository size is: '.$githubRepoSize, $loghelperArr);
                            if($githubRepoSize > 500000) {
                                $logger->log('debug', 'Repository is over 500M', $loghelperArr);
                                echo "您的Git Hub仓库代码超过500M！";
                                break;
                            }
                        }

                        mkdir($foldername, 0777, true);
                        chdir($foldername);

                        try {
                            $found = $repo->clone_remote($urlText);
                        }catch (Exception $e) {
                            $logger->log('error', 'Git clone got exception: '.$e->getMessage(), $loghelperArr);   
                            chdir("..");
                            deldir($foldername);
                            echo "
                                <div class=\"article-block\" id=\"checkwithfailed\">
                                    <span id=\"titleresult\">评估结果:</span>
                                    <span id=\"resultsentence\">没有发现许可证文件</span>
                                </div>
                                <div class=\"article-block\" id=\"declare\">
                                    <p>原因: 在您提供的代码仓库地址中没有发现许可证文件: </p>
                                    <p id=\"sourlink\">
                                            <a href=\""; 
                                    echo $urlText; 
                                    echo "\">";
                                    echo $urlText; 
                                    echo "</a>
                                        </p>
                                </div>
                                
                                <div class=\"article-block\" id=\"moreinfo\">
                                    <span>请参考下面的链接:</span><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                </div>
                                <div class=\"article-block\">
                                    如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a>
                                </div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                            </div>";   
                            break;   
                        }

                        $originalfilecontent = "";
                        $choosenFile = "";
                    
                        // Iterate filenames under root folder to see if there is one license file
                        $existLincese = FALSE;
                        $originalfilelist = array();

                        if ($handle = opendir($GitName)) {
                            while (false !== ($entry = readdir($handle))) {
                                if ($entry != "." && $entry != ".." && $entry != ".git") {
                                        if(!is_dir($entry)) {
                                            array_push($originalfilelist, $entry);
                                        }
                                }
                            }
                            closedir($handle);
                        }

                        foreach($licensepatterns as $lipattern) {
                            foreach($originalfilelist as $singlefile) {
                                if(strnatcasecmp($lipattern, $singlefile) == 0) {
                                    $existLincese = TRUE;
                                    $choosenFile = $singlefile;
                                    $originalfilecontentarr = file($GitName.'/'.$singlefile);

                                    foreach($originalfilecontentarr as $line) {
                                        $originalfilecontent.=$line;
	                                }
                                    break;
                                }
                            }
                            if($existLincese == TRUE) {
                                break;
                            }
                        }

                        chdir("..");

                        if($existLincese == TRUE) {
                            InsertStatusRecords($sessionId, "正在比较许可文件", $conn, $logger, $loghelperArr);

                            $originalfilecontent = trim(preg_replace('/\s\s+/', ' ', $originalfilecontent));

                            // Pick up the right license files
                            $keyFiles = array();
                            foreach($licensedetectkeywords as $key => $value) {
                                $comresult = stripos($originalfilecontent, $value);
                                if($comresult !== FALSE) {
                                    array_push($keyFiles, $key);
                                }
                            }

                            $logger->log('debug', 'Picked up license files after git clone: ', $keyFiles);
                            foreach($keyFiles as $key => $value) {
                                $logger->log('debug', 'Key file['.$key."]: ".$value);
                            }
                            $logger->log('debug', 'The length of keyFiles after git clone: '.count($keyFiles), $loghelperArr);

                            if(count($keyFiles) == 0) {
                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentence\">许可证文件库未发现匹配文件</span>
                                    </div>
                                    <div id=\"declare\">
                                        <span>原因: 在我们的许可证文件库未发现您的许可证类型: </span>
                                    </div>
                                    <div id=\"sourlink\">
                                        <a href=\""; 
                                echo $urlText; 
                                echo "\">";
                                echo $urlText; 
                                echo "</a>
                                    <br>我们会稍后评估并补充许可证文件库</div>
                                    <div id=\"moreinfo\">
                                        <span>请参考下面的链接:</span><br>
                                        <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                        <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                        如果对检测结果有任何意见和反馈, 您可以<a href=\"/feedback.php\">联系我们</a>
                                        <div><a href=\"/licensing.php\">完成</a></div>
                                    </div>
                                </div>";
                            InsertRecords($urlText, "none", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                            break;
                        }

                            // Compare original license file with standard license files
                            $diffOpcodeArr = array();
                            $minDiff = 0;
                            $minKey = 0;
                            $maxRatio = 0;
                            $maxKey = 0;

                            $curl_resultProcessed = trim($originalfilecontent);
                            $curl_resultProcessed = trim(preg_replace('/\s+/', ' ', $curl_resultProcessed));
                            $curl_resultProcessed = preg_replace('/s(?=s)/', '', $curl_resultProcessed);
                            $curl_resultProcessed = htmlspecialchars_decode($curl_resultProcessed);

                            foreach($keyFiles as $key => $value) {
                                $licenseProcessedFile = trim($licensecollection[$value]);
                                $licenseProcessedFile = preg_replace('/s(?=s)/', '', $licenseProcessedFile);
                                $licenseProcessedFile = trim(preg_replace('/\s+/', ' ', $licenseProcessedFile));

                                array_push($diffOpcodeArr, LicenseDiff::compareLicense($licenseProcessedFile, $curl_resultProcessed));
                                if(LicenseDiff::$diffLength < $minDiff || $minDiff == 0) {
                                    $minDiff = LicenseDiff::$diffLength;
                                    $minKey = $key;
                                }
                            }

                            $logger->log('debug', 'The minimal key index in keyFiles after git clone: '.$minKey, $loghelperArr);
                            $comparedStandardLicenseFileContent = $diffOpcodeArr[$minKey];
                            
                            if(verifyPass($comparedStandardLicenseFileContent, $licensecollection[$keyFiles[$minKey]])) {

                                InsertRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                                InsertPassRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                                $certID = GetCertID($urlText, $proName, $proVer, $conn, $logger, $loghelperArr);
                                $certDate = GetCertDate($urlText, $proName, $proVer, $conn, $logger, $loghelperArr);
                                $ln = substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));

                                echo "<div>
                                        <div id=\"checkwithfailed\">
                                            <span id=\"titleresult\">评估结果:</span>
                                            <span id=\"resultsentencepass\">评估通过!</span>
                                        </div>
                                        <div class=\"permanent-link\">证书永久Link: <a href='/gethistory.php?certID=$certID&ln=$ln'>点这里</a></div>
                                        <div class=\"star-content\">
                                            <div class=\"left-arrow\"></div>
                                            <div class=\"right-arrow\"></div>
                                            <div class=\"star-inner\">
                                                <div class=\"star-badge\"></div>
                                                <div class=\"star-text\">
                                                    <h1>开源之星认证</h1>
                                                    <h2>";
                                echo $proName;
                                echo "</h2>
                                            <p class=\"address\">源代码库地址：";
                                echo $urlText;
                                echo "</p>
                                            <p class=\"result\" id=\"result\">经验证符合";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "的标准</p><p class=\"thanks\">感谢您对中国开源社区的贡献！</p>
                                            </div>
                                            <div class=\"star-footer\">
                                                <div class=\"logo\"></div>
                                                <p class=\"number\">NO.";
                                echo $certID;
                                echo "</p>
                                    <p class=\"date\">";
                                echo $certDate; 
                                echo "</p>
                                </div></div></div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                                </div>";
                                RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                            }
                            else {
                                InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);                          

                                echo "<div id=\"checkwithfailed\">
                                            <span id=\"titleresult\">评估结果:</span>
                                            <span id=\"resultsentence\">没有通过评估</span>
                                        </div>
                                        <div id=\"declare\">
                                            <p>原因: 您的许可证文件已经被检测到, 但是内容并没有完全匹配到由OSI批准的";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "许可证文件内容, 详细文本之间的差别请看下面:</p>
                                <p><span style=\"background-color:#005500\">&nbsp;&nbsp;&nbsp;&nbsp;</span>代表比标准文本增加的部分</p><p><span style=\"background-color:#990000\">&nbsp;&nbsp;&nbsp;&nbsp;</span>代表比标准文本减少的部分</span></p>
                                        </div>
                                        <div id=\"originaldiv\">
                                            <div>您的许可证比较结果</div>
                                            <div id=\"originalcontent\"><pre>";
                                echo $comparedStandardLicenseFileContent; 
                                echo "</pre></div>
                                        </div>
                                        <div id=\"standarddiv\">
                                        <div>";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "</div>
                                            <div id=\"standardcontent\"><pre>";
                                echo htmlspecialchars($licensecollection[$keyFiles[$minKey]]);
                                echo "</pre></div>
                                <div class=\"contact-us\">如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a></div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                                        </div>
                                        </div>";
                                RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                            }

                            // remove the git folder downloaded
                            deldir($foldername);
                        }
                        else {
                            InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);                            

                            echo "
                                <div class=\"article-block\" id=\"checkwithfailed\">
                                    <span id=\"titleresult\">评估结果:</span>
                                    <span id=\"resultsentence\">没有发现许可证文件</span>
                                </div>
                                <div class=\"article-block\" id=\"declare\">
                                    <p>原因: 在您提供的代码仓库地址中没有发现许可证文件: </p>
                                    <p id=\"sourlink\">
                                            <a href=\""; 
                                    echo $urlText; 
                                    echo "\">";
                                    echo $urlText; 
                                    echo "</a>
                                        </p>
                                </div>
                                
                                <div class=\"article-block\" id=\"moreinfo\">
                                    <span>请参考下面的链接:</span><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                </div>
                                <div class=\"article-block\">
                                    如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a>
                                </div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                            </div>";
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);

                            // remove the git folder downloaded
                            deldir($foldername);
                        }
                    }
                    break;
                case "svn":
                    $logger->log('debug', "The prototype is SVN", $loghelperArr);

                    InsertStatusRecords($sessionId, "正在探测许可文件", $conn, $logger, $loghelperArr);

                    $command = "svn ls ".$urlText;
                    $fileList = SvnPeer::runCmd($command);

                    $existLincese = FALSE;

                    foreach($fileList as $svnSingleFile) {
                        foreach($licensepatterns as $licenseSingleFile) {
                            // Compare raw file names
                            if($svnSingleFile == $licenseSingleFile) {
                                $existLincese = TRUE;
                                $foundLinceseFile = $svnSingleFile;
                                break;
                            }

                            // Compare lower version of file names
                            $comresult = stripos($licenseSingleFile, ".");
                            if($comresult !== FALSE) {
                                $pos1 = stripos($licenseSingleFile, '.');
                                $sub1 = substr($licenseSingleFile, 0, $pos1);
                                $sub2 = substr($licenseSingleFile, $pos1);
                                $result = strtoupper($sub1).$sub2;
                            }
                            else {
                                $result = strtoupper($licenseSingleFile);
                            }

                            if($svnSingleFile == $result) {
                                $existLincese = TRUE;
                                $foundLinceseFile = $svnSingleFile;
                                break;
                            }

                            // Compare lower version of file names
                            $result = strtolower($licenseSingleFile);
                            if($svnSingleFile == $result) {
                                $existLincese = TRUE;
                                $foundLinceseFile = $svnSingleFile;
                                break;
                            }
                        }
                        if($existLincese == TRUE) {
                            break;
                        }
                    }



                    if($existLincese == TRUE) {
                        $logger->log('debug', 'License file found', $loghelperArr);
                        // Get file content from svn
                        InsertStatusRecords($sessionId, "正在check out许可文件", $conn, $logger, $loghelperArr);

                        $ran = rand();
                        $timeparts = explode(' ',microtime());
                        $etime = $timeparts[1].substr($timeparts[0],1);
                        $tempName = $ran.$timeparts[1];
                        $foldername = $tempName;

                        mkdir($foldername, 0777, true);
                        chdir($foldername);

                        try {
                            $command = "svn co ".$urlText." target --depth empty";
                            $checkoutresult = SvnPeer::runCmd($command);
                        }catch(Exception $e) {
                            $logger->log('error', 'SVN command "svn co ... target --depth empty" got exception: '.$e->getMessage(), $loghelperArr);
                            chdir("..");
                            deldir($foldername);
                            echo "
                                <div class=\"article-block\" id=\"checkwithfailed\">
                                    <span id=\"titleresult\">评估结果:</span>
                                    <span id=\"resultsentence\">没有发现许可证文件</span>
                                </div>
                                <div class=\"article-block\" id=\"declare\">
                                    <p>原因: 在您提供的代码仓库地址中没有发现许可证文件: </p>
                                    <p id=\"sourlink\">
                                            <a href=\""; 
                                    echo $urlText; 
                                    echo "\">";
                                    echo $urlText; 
                                    echo "</a>
                                        </p>
                                </div>
                                
                                <div class=\"article-block\" id=\"moreinfo\">
                                    <span>请参考下面的链接:</span><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                </div>
                                <div class=\"article-block\">
                                    如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a>
                                </div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                            </div>";
                            break;
                        }                      
                        chdir("target");
                        try {
                            $command = "svn up ".$foundLinceseFile;
                            $checkoutresult = SvnPeer::runCmd($command);
                        }catch(Exception $e) {
                            $logger->log('error', 'SVN command "svn up" got exception: '.$e->getMessage(), $loghelperArr);
                            chdir("..");
                            chdir("..");
                            deldir($foldername);
                            echo "
                                <div class=\"article-block\" id=\"checkwithfailed\">
                                    <span id=\"titleresult\">评估结果:</span>
                                    <span id=\"resultsentence\">没有发现许可证文件</span>
                                </div>
                                <div class=\"article-block\" id=\"declare\">
                                    <p>原因: 在您提供的代码仓库地址中没有发现许可证文件: </p>
                                    <p id=\"sourlink\">
                                            <a href=\""; 
                                    echo $urlText; 
                                    echo "\">";
                                    echo $urlText; 
                                    echo "</a>
                                        </p>
                                </div>
                                
                                <div class=\"article-block\" id=\"moreinfo\">
                                    <span>请参考下面的链接:</span><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                </div>
                                <div class=\"article-block\">
                                    如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a>
                                </div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                            </div>";
                            break;
                        }

                        // Load file content
                        $originalfilecontentarr = file($foundLinceseFile);
                        $logger->log('debug', 'the found license file name in svn check out: '.$foundLinceseFile, $loghelperArr);

                        foreach($originalfilecontentarr as $line) {
                            $originalfilecontent.=$line;
	                    }

                        chdir("..");
                        chdir("..");

                        InsertStatusRecords($sessionId, "正在比较许可文件", $conn, $logger, $loghelperArr);
                        // Start compare license files
                        $originalfilecontent = trim(preg_replace('/\s\s+/', ' ', $originalfilecontent));

                        // Pick up the right license files
                        $keyFiles = array();
                        foreach($licensedetectkeywords as $key => $value) {
                            $comresult = stripos($originalfilecontent, $value);
                            if($comresult !== FALSE) {
                                array_push($keyFiles, $key);
                            }
                        }

                        $logger->log('debug', 'Picked up license files after svn check out: ', $keyFiles);
                        foreach($keyFiles as $key => $value) {
                            $logger->log('debug', 'Key file['.$key."]: ".$value);
                        }
                        $logger->log('debug', 'The length of keyFiles after svn check out: '.count($keyFiles), $loghelperArr);

                        if(count($keyFiles) == 0) {
                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentence\">许可证文件库未发现匹配文件</span>
                                    </div>
                                    <div id=\"declare\">
                                        <span>原因: 在我们的许可证文件库未发现您的许可证类型: </span>
                                    </div>
                                    <div id=\"sourlink\">
                                        <a href=\""; 
                                echo $urlText; 
                                echo "\">";
                                echo $urlText; 
                                echo "</a>
                                    <br>我们会稍后评估并补充许可证文件库</div>
                                    <div id=\"moreinfo\">
                                        <span>请参考下面的链接:</span><br>
                                        <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                        <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                        <div><a href=\"/licensing.php\">完成</a></div>
                                    </div>
                                </div>";
                            InsertRecords($urlText, "none", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                            break;
                        }

                        // Compare original license file with standard license files
                        $diffOpcodeArr = array();
                        $minDiff = 0;
                        $minKey = 0;
                        $maxRatio = 0;
                        $maxKey = 0;

                        $curl_resultProcessed = trim($originalfilecontent);
                        $curl_resultProcessed = trim(preg_replace('/\s+/', ' ', $curl_resultProcessed));
                        $curl_resultProcessed = preg_replace('/s(?=s)/', '', $curl_resultProcessed);
                        $curl_resultProcessed = htmlspecialchars_decode($curl_resultProcessed);

                        foreach($keyFiles as $key => $value) {
                            $licenseProcessedFile = trim($licensecollection[$value]);
                            $licenseProcessedFile = preg_replace('/s(?=s)/', '', $licenseProcessedFile);
                            $licenseProcessedFile = trim(preg_replace('/\s+/', ' ', $licenseProcessedFile));

                            array_push($diffOpcodeArr, LicenseDiff::compareLicense($licenseProcessedFile, $curl_resultProcessed));
                            if(LicenseDiff::$diffLength < $minDiff || $minDiff == 0) {
                                $minDiff = LicenseDiff::$diffLength;
                                $minKey = $key;
                            }
                        }

                        $comparedStandardLicenseFileContent = $diffOpcodeArr[$minKey];
                            

                        if(verifyPass($comparedStandardLicenseFileContent, $licensecollection[$keyFiles[$minKey]])) {

                            InsertRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            InsertPassRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                            $certID = GetCertID($urlText, $proName, $proVer, $conn, $logger, $loghelperArr);
                            $certDate = GetCertDate($urlText, $proName, $proVer, $conn, $logger, $loghelperArr);
                            $ln = substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentencepass\">评估通过!</span>
                                    </div>
                                    <div class=\"permanent-link\">证书永久Link: <a href='/gethistory.php?certID=$certID&ln=$ln'>点这里</a></div>
                                    <div class=\"star-content\">
                                        <div class=\"left-arrow\"></div>
                                        <div class=\"right-arrow\"></div>
                                        <div class=\"star-inner\">
                                            <div class=\"star-badge\"></div>
                                            <div class=\"star-text\">
                                                <h1>开源之星认证</h1>
                                                <h2>";
                            echo $proName;
                            echo "</h2>
                                        <p class=\"address\">源代码库地址：";
                            echo $urlText;
                            echo "</p>
                                        <p class=\"result\" id=\"result\">经验证符合";
                            echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                            echo "的标准</p><p class=\"thanks\">感谢您对中国开源社区的贡献！</p>
                                        </div>
                                        <div class=\"star-footer\">
                                            <div class=\"logo\"></div>
                                            <p class=\"number\">NO.";
                            echo $certID;
                            echo "</p>
                                <p class=\"date\">";
                            echo $certDate; 
                            echo "</p>
                            </div></div></div>
                            <div class=\"page-jump\">
                                <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                            </div>
                            </div>";
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                        }
                        else {
                            InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);                      

                            echo "<div id=\"checkwithfailed\">
                                            <span id=\"titleresult\">评估结果:</span>
                                            <span id=\"resultsentence\">没有通过评估</span>
                                        </div>
                                        <div id=\"declare\">
                                            <p>原因: 您的许可证文件已经被检测到, 但是内容并没有完全匹配到由OSI批准的";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "许可证文件内容, 详细文本之间的差别请看下面:</p>
                                <p><span style=\"background-color:#005500\">&nbsp;&nbsp;&nbsp;&nbsp;</span>代表比标准文本增加的部分</p><p><span style=\"background-color:#990000\">&nbsp;&nbsp;&nbsp;&nbsp;</span>代表比标准文本减少的部分</span></p>
                                        </div>
                                        <div id=\"originaldiv\">
                                            <div>您的许可证比较结果</div>
                                            <div id=\"originalcontent\"><pre>";
                                echo $comparedStandardLicenseFileContent; 
                                echo "</pre></div>
                                        </div>
                                        <div id=\"standarddiv\">
                                        <div>";
                                echo substr($keyFiles[$minKey], 0, strrpos($keyFiles[$minKey], "."));
                                echo "</div>
                                            <div id=\"standardcontent\"><pre>";
                                echo htmlspecialchars($licensecollection[$keyFiles[$minKey]]);
                                echo "</pre></div>
                                <div class=\"contact-us\">如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a></div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                                        </div>
                                        </div>";
                            RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);
                        }

                        // remove the git folder downloaded
                        deldir($foldername);
                        
                    }
                    else {
                        $logger->log('debug', 'License file not found', $loghelperArr);
                        // Show No License Result
                        InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype, $conn, $logger, $loghelperArr);
                        echo "
                                <div class=\"article-block\" id=\"checkwithfailed\">
                                    <span id=\"titleresult\">评估结果:</span>
                                    <span id=\"resultsentence\">没有发现许可证文件</span>
                                </div>
                                <div class=\"article-block\" id=\"declare\">
                                    <p>原因: 在您提供的代码仓库地址中没有发现许可证文件: </p>
                                    <p id=\"sourlink\">
                                            <a href=\""; 
                                    echo $urlText; 
                                    echo "\">";
                                    echo $urlText; 
                                    echo "</a>
                                        </p>
                                </div>
                                
                                <div class=\"article-block\" id=\"moreinfo\">
                                    <span>请参考下面的链接:</span><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的许可证文件?</a><br>
                                    <a href=\"http://www.kaiyuanshe.cn/index.php?option=com_content&view=category&id=9\">如何建立您的开源许可证?</a>
                                </div>
                                <div class=\"article-block\">
                                    如果对检测结果有任何意见和反馈, 您可以<a href=\"http://www.kaiyuanshe.cn/feedback.php\">联系我们</a>
                                </div>
                                <div class=\"page-jump\">
                                    <input type=\"button\" value=\"完成\" class=\"resolver-next\" data-redirect=\"/licensing.php\">
                                </div>
                            </div>";
                        RemoveStatusRecords($sessionId, $conn, $logger, $loghelperArr);                        
                    }                     
                    break;
                }           
        ?>

            <div class="footer">
                <div class="container"><a href="http://www.kaiyuanshe.cn/about.html">联系我们</a> | <a href="http://www.kaiyuanshe.cn/privacy-policy.html" target="_blank">隐私条款</a> | <a href="http://www.kaiyuanshe.cn/terms-of-use.html" target="_blank">使用条款</a> | 京ICP备<a href="http://www.miibeian.gov.cn/" target="_blank">14047895</a>号</div>
                </div>
            </div>
            </div>
        </div>

        <script src="/library/jquery/1.11.1/jquery.min.js"></script>
        <script src="/js/base.js"></script>
    </body>
</html>
