<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="validationtool">
        <meta name="keywords" content="validationtool">

        <link rel="stylesheet" type="text/css" href="css/validateresults.css">
        <link href="css/bootstrap/3.2.0/bootstrap.custom.css" rel="stylesheet">
		<link href="css/2stage.css" rel="stylesheet">

        <title>评估结果</title>
        
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <div class="container">
                    <div class="navbar-logo">开源社</div>
                    <div class="navbar-collapse">
                        <ul class="navbar-nav navbar-list">
                            <li>
                                <a href="index.html">主页</a>
                            </li>
                            <li class="active">
                                <a href="validationtool.php">开源许可评估</a>
                            </li>
                            <li>
                                <a href="license.html">开源许可证</a>
                            </li>
                            <li>
                                <a href="/#">关于我们</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="column-header">
                <div class="container">
                    <h1 class="title">开源许可评估结果</h1>
                </div>
            </div>
        </div>

        <?php
            require_once("util/Helper.php");
                        
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

            function RemoveStatusRecords($sid) {
                $conn = mysql_connect('localhost','root','') or die ("数据连接错误!!!");
                mysql_select_db('kys',$conn);

                $sqlComm = "delete from kys.processstatus where chSessionId = \"".$sid."\"";
                $result = mysql_query($sqlComm, $conn);
            }

            function InsertStatusRecords($sid, $stat) {
                $conn = mysql_connect('localhost','root','') or die ("数据连接错误!!!");
                mysql_select_db('kys',$conn);

                $latestTime = date('y-m-d h:i:s',time());
                $sqlComm = "insert into kys.processstatus(chSessionId, chStatus, dtUpdateTime) values(\"".$sid."\", \"".$stat."\", \"".$latestTime."\")";

                $result = mysql_query($sqlComm, $conn);
            }

            function InsertRecords($urlText, $validationresult, $projectname, $projectsite, $projectversion, $clientip, $repotype) {
                $conn = mysql_connect('localhost','root','') or die ("数据连接错误!!!");
                mysql_select_db('kys',$conn);

                $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
                $result = mysql_query($sqlComm, $conn);
                $rowCount = mysql_num_rows($result);
                $row = mysql_fetch_array($result, MYSQL_NUM);

                if($rowCount == 0) {
                    $sqlComm = "insert into kys.repourls(chRepoURL) values(\"".$urlText."\")";
                    $result = mysql_query($sqlComm, $conn);
                    
                    $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
                    $result = mysql_query($sqlComm, $conn);
                    $row = mysql_fetch_array($result, MYSQL_NUM);

                    $currentidURL = $row[0];
                    echo "currentidURL: ".$currentidURL;
                    echo "<br>";

                    $latestTime = date('y-m-d h:i:s',time());
                    $sqlComm = "insert into kys.reprovisithistory(idRepoURLs, chResult, timeLastVisit, chProName, chProSite, chVersion, chIPAddr, chRepoType) values(".$currentidURL.", \"".$validationresult."\", \"".$latestTime."\", \"".$projectname."\", \"".$projectsite."\", \"".$projectversion."\", \"".$clientip."\", \"".$repotype."\")";

                    $result = mysql_query($sqlComm, $conn);
                }
                else {
                    $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
                    $result = mysql_query($sqlComm, $conn);
                    $row = mysql_fetch_array($result, MYSQL_NUM);

                    $latestTime = date('y-m-d h:i:s',time());

                    $currentidURL = $row[0];
                    $sqlComm = "insert into kys.reprovisithistory(idRepoURLs, chResult, timeLastVisit, chProName, chProSite, chVersion, chIPAddr, chRepoType) values(".$currentidURL.", \"".$validationresult."\", \"".$latestTime."\", \"".$projectname."\", \"".$projectsite."\", \"".$projectversion."\", \"".$clientip."\", \"".$repotype."\")";
                    $result = mysql_query($sqlComm, $conn);
                }
            }

            function sendRequest($url, & $curl_result) {
                $ch = curl_init();
	            curl_setopt($ch, CURLOPT_URL, $url);
	            curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	            $curl_result = curl_exec($ch);
                $statuscode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	            curl_close($ch);
                return $statuscode; 
            }

            require_once('licensediff.php');

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
            InsertStatusRecords($sessionId, "正在初始化");

            $urlText = $_POST["reprourl"];
            $conn = mysql_connect('localhost','root','') or die ("数据连接错误!!!");
            mysql_select_db('kys',$conn);

            $sqlComm = "select idRepoURLs from kys.repourls where chRepoURL = \"".$urlText."\"";
            $result = mysql_query($sqlComm, $conn);
            $rowCount = mysql_num_rows($result);
            $row = mysql_fetch_array($result, MYSQL_NUM);

            if($rowCount != 0) {
                $idResult = $row[0];
                $sqlComm = "select timeLastVisit from reprovisithistory where idRepoURLs = ".$idResult."  and chIPAddr = \"".$ipAddr."\" order by timeLastVisit desc LIMIT 1";
                $result = mysql_query($sqlComm, $conn);
                $rowCount = mysql_num_rows($result);
                $row = mysql_fetch_array($result, MYSQL_NUM);
                if($rowCount != 0) {
                    $lastVisit = $row[0];
                    $currentTime = date('y-m-d h:i:s',time());
                    $diff = strtotime($currentTime) - strtotime($lastVisit);
                    if(abs($diff) < 60) {
                        echo "此开源项目正在被验证过程中。。。";
                        die();
                    }
                }
            }

            // Put license file patterns into array
            $licensepatterns = array('License', 'License.txt', 'License.md', 'Copying', 'Copying.txt', 'Copyright', 'Copyright.txt');
            $licensedetectkeywords = array('Academic Free License', 'ADAPTIVE PUBLIC LICENSE', 'Apache License', 'APPLE PUBLIC SOURCE LICENSE', 'Artistic License 2.0', 'Attribution Assurance License', 'Permission is hereby granted, free of charge, to any person or organization obtaining a copy of the software and accompanying documentation covered by this license (the "Software") to use, reproduce, display, distribute, execute, and transmit the Software, and to prepare derivative works of the Software, and to permit third-parties to whom the Software is furnished to do so, all subject to the following', 'The authors of the CeCILL (for Ce[a] C[nrs] I[nria] L[ogiciel] L[ibre]) license are', '(The CNRI portion of the multi-part Python License.) (CNRI-Python)', 'COMMON DEVELOPMENT AND DISTRIBUTION LICENSE (CDDL)', 'Common Public Attribution License Version 1.0', 'Computer Associates Trusted Open Source License', 'CUA Office Public License Version 1.0', 'Eclipse Public License, Version 1.0', 'Educational Community License, Version 2.0', 'Eiffel Forum License, version 2', 'Entessa Public License Version. 1.0', 'EU DataGrid Software License', 'The European Union Public License', 'Fair License', 'THE FRAMEWORX OPEN LICENSE 1.0', 'Copyright (C) 2007 Free Software Foundation, Inc.', 'The GNU General Public License', 'GNU GENERAL PUBLIC LICENSE', 'Historical Permission Notice and Disclaimer', 'IPA Font License Agreement v1.0', 'IBM Public License Version 1.0', 'Copyright (c) 4-digit year, Company or Person\'s Name', 'GNU Lesser General Public License', 'GNU LESSER GENERAL PUBLIC LICENSE', 'Lucent Public License Version 1.02', 'LPPL Version 1.3c 2008-05-04', 'To apply the template(1) specify the years of copyright (separated by comma, not as a range), the legal names of the copyright holders, and the real names of the authors if different. Avoid adding text.', 'The MIT License', 'MOTOSOTO OPEN SOURCE LICENSE', 'Mozilla Public License', 'This license governs use of the accompanying software', 'Historical Background', 'NASA OPEN SOURCE AGREEMENT VERSION 1.3', 'NAUMEN Public License', 'University of Illinois/NCSA Open Source License', 'Nethack General Public License', 'Nokia Open Source License', 'Non-Profit Open Software License 3.0', 'Copyright (c) (CopyrightHoldersName) (From 4-digit-year)-(To 4-digit-year)', 'OCLC Research Public License 2.0 License', 'Copyright (c) <dates>, <Copyright Holder>', 'The Open Group Test Suite License', 'Open Software License v. 3.0', 'The PHP License', 'This is a template license', 'Python License, Version 2', 'The Q Public License Version 1.0', 'Reciprocal Public License', 'RealNetworks Public Source License Version 1.0', 'Ricoh Source Code Public License', 'This Simple Public License 2.0', 'The Sleepycat License', 'SUN PUBLIC LICENSE Version 1.0', 'The BSD 2-Clause License', 'The BSD 3-Clause License', 'Vovida Software License v. 1.0', 'W3C? SOFTWARE NOTICE AND LICENSE', 'USE OF THE SYBASE OPEN WATCOM SOFTWARE DESCRIBED BELOW ("SOFTWARE")', 'The wxWindows Library Licence', 'The X.Net, Inc. License', 'The zlib/libpng License', 'Zope Public License (ZPL) Version 2.0');

            //echo "Start loading license files at: ".date('Y-m-d H:i:s',time());;
            //echo "<br>";  
            // Initialize Standard License File Hashtable mem singleton object
            $licensecollection = array();
            if ($handle = opendir('StandardLicenses')) {
               while (false !== ($entry = readdir($handle))) {
                   if ($entry != "." && $entry != "..") {
                       $tempfilecontentarr = file("StandardLicenses/".$entry);
                       $tempfilecontent = "";
                       foreach($tempfilecontentarr as $line) {
                           $tempfilecontent.=$line;
	                   }
                       $licensecollection[$entry] = $tempfilecontent;
                   }
               }
               closedir($handle);
            }

            //echo "End loading license files at: ".date('Y-m-d H:i:s',time());;
            //echo "<br>";

            // Determine the protocol type
            $protocoltype = $_POST["rptype"];

            // Diff the license file
            switch($protocoltype) {
                case "github":
                    $licensefilenames = array_keys($licensecollection);
                    $licesnefilecomtents = array_values($licensecollection);

                    $existLincese = FALSE;

                    if(stripos($urlText, "github.com")) {
                        $pos1 = stripos($urlText, "github.com");
                        $sub1 = substr($urlText, $pos1 + 10);
                        $pos2 = strrpos($sub1, "git");
                        $sub2 = substr($sub1, 0, $pos2 - 1);
                        $urlTextRaw = "https://raw.githubusercontent.com".$sub2."/master";
                        $githubProName = $sub2;
                    }
                    else if(stripos($urlText, "git.oschina.net")) {
                        $pos1 = stripos($urlText, "git.oschina.net");
                        $sub1 = substr($urlText, $pos1 + 15);
                        $pos2 = strrpos($sub1, "git");
                        $sub2 = substr($sub1, 0, $pos2 - 1);
                        $urlTextRaw = "https://git.oschina.net".$sub2."/raw/master";
                    }

                    InsertStatusRecords($sessionId, "正在探测许可文件");
 
                    //echo "Start Http Requests at: ".date('Y-m-d H:i:s',time());;
                    //echo "<br>";

                    // Send GET requests and try different kinds of license files
                    foreach($licensepatterns as $singlefile) {
                        $tempURL = $urlTextRaw."/".$singlefile;
                        $lowerURL = $urlTextRaw."/".strtolower($singlefile);
                        $upperURL = $urlTextRaw."/".strtoupper($singlefile);
                        $requests = array($tempURL, $lowerURL, $upperURL);
                        //print_r($requests);
                        //echo "<br>";
                        $main    = curl_multi_init();
                        $results = array();
                        //$errors  = array();
                        //$info = array();
                        $count = count($requests);
                        for($i = 0; $i < $count; $i++) 
                        {  
                            $handles[$i] = curl_init($requests[$i]);  
                            curl_setopt($handles[$i], CURLOPT_URL, $requests[$i]);
                            curl_setopt($handles[$i], CURLOPT_TIMEOUT, 2);
	                        curl_setopt($handles[$i], CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($handles[$i], CURLOPT_SSL_VERIFYHOST, false);  
                            curl_setopt($handles[$i], CURLOPT_RETURNTRANSFER, 1);  
                            curl_multi_add_handle($main, $handles[$i]);
                        }
                        $running = 0; 
                        do {  
                            curl_multi_exec($main, $running);
                        } 
                        while($running > 0); 
                        for($i = 0; $i < $count; $i++){                        
                            $statuscode = curl_getinfo($handles[$i], CURLINFO_HTTP_CODE);
                            if($statuscode == 200) {
                                $existLincese = TRUE;
                                $curl_result = curl_multi_getcontent($handles[$i]);
                                break;
                            }
                            curl_multi_remove_handle($main, $handles[$i]);
                        }
                        curl_multi_close($main);

                        if($existLincese == TRUE) {
                            break;
                        }
                    }           
  
                    //echo "End Http Requests at: ".date('Y-m-d H:i:s',time());;
                    //echo "<br>";

                    if($existLincese == TRUE) {
                        InsertStatusRecords($sessionId, "正在比较许可文件");

                        //echo "Start compare license files at: ".date('Y-m-d H:i:s',time());;
                        //echo "<br>"; 

                        // Pick up the right license files
                        $keyFiles = array();
                        foreach($licensedetectkeywords as $key => $value) {
                            if(stripos($curl_result, $value) == 0) {
                                array_push($keyFiles, $key);
                            }
                        }

                        // Compare original license file with standard license files
                        $diffOpcodeArr = array();
                        $minDiff = 0;
                        $minKey = 0;
                        $maxRatio = 0;
                        $maxKey = 0;
                        foreach($keyFiles as $key => $value) {
                            $licenseProcessedFile = trim(preg_replace('/\s\s+/', ' ', $licesnefilecomtents[$value]));
                            $curl_resultProcessed = trim(preg_replace('/\s\s+/', ' ', $curl_result));
                            array_push($diffOpcodeArr, LicenseDiff::compareLicense($licenseProcessedFile, $curl_resultProcessed));
                            if(LicenseDiff::$diffLength < $minDiff || $minDiff == 0) {
                                $minDiff = LicenseDiff::$diffLength;
                                $minKey = $key;
                            }
                        }

                        //echo "End compare license files at: ".date('Y-m-d H:i:s',time());;
                        //echo "<br>";

                        $comparedStandardLicenseFileContent = $diffOpcodeArr[$minKey];

                        if(substr_count($comparedStandardLicenseFileContent, "<ins>") == 0 && substr_count($comparedStandardLicenseFileContent, "<del>") == 0) {
                            InsertRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype);

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentencepass\">评估通过!</span>
                                    </div>
                                    <div id=\"suball\">
                                        <img src=\"images/osi-certified.png\" alt=\"\"/>
                                        <div id=\"decl\">许可评估成功</div>
                                        <div id=\"project-name\">";
                            echo $proName;
                            echo "</div>
                                        <div id=\"repo-path\">";
                            echo $urlText;
                            echo "</div>
                                        <div id=\"end-decl\">已经被评估符合";
                            echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                            echo "感谢你对中国开源社的贡献！</div>
                                        <img src=\"images/opensource.png\" alt=\"\" />
                                        <div id=\"sigtime\">OSS alliance signature ";
                            echo date('Y-m-d H:i:s',time()); 
                            echo "</div>
                                    </div>
                                </div>";
                            RemoveStatusRecords($sessionId);
                        }
                        else {
                            InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype);

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentence\">评估失败</span>
                                    </div>
                                    <div id=\"declare\">
                                        <span>原因: 您的许可证文件: LICENSE.txt, 已经被检测到, 但是内容并没有完全匹配到由OSI批准的";
                            echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                            echo "许可证文件内容, 详细文本之间的差别请看下面:</span>
                                    </div>
                                    <div id=\"originaldiv\">
                                        <div>您的许可证比较结果</div>
                                        <div id=\"originalcontent\">";
                            echo $comparedStandardLicenseFileContent; 
                            echo "</div>
                                    </div>
                                    <div id=\"standarddiv\">
                                    <div>";
                            echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                            echo "</div>
                                        <div id=\"standardcontent\">";
                            echo trim(preg_replace('/\s\s+/', ' ', $licesnefilecomtents[$keyFiles[$minKey]]));
                            echo "</div>
                                    </div>
                                    </div>";
                            RemoveStatusRecords($sessionId);
                        }
                    }
                    else {
                        InsertStatusRecords($sessionId, "未发现许可文件，正在Git Clone代码仓库副本");

                        // Git clone the repository based on git URL
                        require_once('util/git.php');

                        Git::windows_mode();
                        $repo = new GitRepo();

                        // Get git client folder name
                        $indexOfLastSplash = strrpos($urlText, "/");
                        $indexOfLastDot = strrpos($urlText, '.');
                        $GitName = substr($urlText, $indexOfLastSplash + 1, $indexOfLastDot - $indexOfLastSplash - 1);

                        $ran = rand();
                        $timeparts = explode(' ',microtime());
                        $etime = $timeparts[1].substr($timeparts[0],1);
                        $tempName = $ran.$timeparts[1];
                        $foldername = $GitName.$tempName;

                        if(stripos($urlText, "github.com")) {
                            $githubRepoSize = getGitHubSize($githubProName);
                            if($githubRepoSize > 500000) {
                                echo "您的Git Hub仓库代码超过500M！";
                                die();
                            }
                        }

                        mkdir($foldername, 0777, true);
                        chdir($foldername);

                        try {
                            $found = $repo->clone_remote($urlText);
                        }catch (Exception $e) {   
                            print $e->getMessage();
                            chdir("..");
                            deldir($foldername);   
                            die();   
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
                            InsertStatusRecords($sessionId, "正在比较许可文件");

                            //echo "Start compare license files at (inner): ".date('Y-m-d H:i:s',time());;
                            //echo "<br>"; 

                            $originalfilecontent = trim(preg_replace('/\s\s+/', ' ', $originalfilecontent));

                            // Pick up the right license files
                            $keyFiles = array();
                            foreach($licensedetectkeywords as $key => $value) {
                                if(stripos($originalfilecontent, $value) == 0) {
                                    array_push($keyFiles, $key);
                                }
                            }

                            // Compare original license file with standard license files
                            $diffOpcodeArr = array();
                            $minDiff = 0;
                            $minKey = 0;
                            $maxRatio = 0;
                            $maxKey = 0;
                            foreach($keyFiles as $key => $value) {
                                $licenseProcessedFile = trim(preg_replace('/\s\s+/', ' ', $licesnefilecomtents[$value]));
                                $curl_resultProcessed = trim(preg_replace('/\s\s+/', ' ', $originalfilecontent));
                                array_push($diffOpcodeArr, LicenseDiff::compareLicense($licenseProcessedFile, $curl_resultProcessed));
                                if(LicenseDiff::$diffLength < $minDiff || $minDiff == 0) {
                                    $minDiff = LicenseDiff::$diffLength;
                                    $minKey = $key;
                                }
                            }

                            //echo "End compare license files at (inner): ".date('Y-m-d H:i:s',time());;
                            //echo "<br>";

                            $comparedStandardLicenseFileContent = $diffOpcodeArr[$minKey];
                            

                            if(substr_count($comparedStandardLicenseFileContent, "<ins>") == 0 && substr_count($comparedStandardLicenseFileContent, "<del>") == 0) {

                                InsertRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype);

                                echo "<div>
                                        <div id=\"checkwithfailed\">
                                            <span id=\"titleresult\">评估结果:</span>
                                            <span id=\"resultsentencepass\">评估通过!</span>
                                        </div>
                                        <div id=\"suball\">
                                            <img src=\"images/osi-certified.png\" alt=\"\"/>
                                            <div id=\"decl\">许可评估成功</div>
                                            <div id=\"project-name\">";
                                echo $proName;
                                echo "</div>
                                            <div id=\"repo-path\">";
                                echo $urlText;
                                echo "</div>
                                            <div id=\"end-decl\">已经被评估符合";
                                echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                                echo "感谢你对中国开源社的贡献！</div>
                                            <img src=\"images/opensource.png\" alt=\"\" />
                                            <div id=\"sigtime\">OSS alliance signature ";
                                echo date('Y-m-d H:i:s',time()); 
                                echo "</div>
                                        </div>
                                    </div>";
                                RemoveStatusRecords($sessionId);
                            }
                            else {
                                InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype);                          

                                echo "<div>
                                        <div id=\"checkwithfailed\">
                                            <span id=\"titleresult\">评估结果:</span>
                                            <span id=\"resultsentence\">评估失败</span>
                                        </div>
                                        <div id=\"declare\">
                                            <span>原因: 您的许可证文件: LICENSE.txt, 已经被检测到, 但是内容并没有完全匹配到由OSI批准的";
                                echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                                echo "许可证文件内容, 详细文本之间的差别请看下面:</span>
                                        </div>
                                        <div id=\"originaldiv\">
                                            <div>您的许可证比较结果</div>
                                            <div id=\"originalcontent\">";
                                echo $comparedStandardLicenseFileContent; 
                                echo "</div>
                                        </div>
                                        <div id=\"standarddiv\">
                                        <div>";
                                echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                                echo "</div>
                                            <div id=\"standardcontent\">";
                                echo trim(preg_replace('/\s\s+/', ' ', $licesnefilecomtents[$keyFiles[$minKey]]));
                                echo "</div>
                                        </div>
                                        </div>";
                                RemoveStatusRecords($sessionId);
                            }

                            // remove the git folder downloaded
                            deldir($foldername);
                        }
                        else {
                            InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype);                            

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentence\">没有许可证文件被发现</span>
                                    </div>
                                    <div id=\"declare\">
                                        <span>原因: 在您提供的代码仓库地址中没有发现许可证文件: </span>
                                    </div>
                                    <div id=\"sourlink\">
                                        <a href=\""; 
                                echo $urlText; 
                                echo "\">";
                                echo $urlText; 
                                echo "</a>
                                    </div>
                                    <div id=\"moreinfo\">
                                        <span>请参考下面的链接:</span><br>
                                        <a href=\"www.google.com\">如何建立您的许可证文件?</a><br>
                                        <a href=\"www.google.com\">如何建立您的开源许可证?</a>
                                    </div>
                                </div>";
                            RemoveStatusRecords($sessionId);

                            // remove the git folder downloaded
                            deldir($foldername);
                        }
                    }
                    break;
                case "svn":
                    require_once('util/SvnPeer.php');
                    InsertStatusRecords($sessionId, "正在探测许可文件");

                    $fileList = SvnPeer::ls($urlText);
                    $fileList = explode("<br>", $fileList);

                    $existLincese = FALSE;

                    foreach($fileList as $svnSingleFile) {
                        foreach($licensepatterns as $licenseSingleFile) {
                            if($svnSingleFile == $licenseSingleFile) {
                                $existLincese = TRUE;
                                $foundLinceseFile = $svnSingleFile;
                                breank;
                            }
                        }
                        if($existLincese == TRUE) {
                            break;
                        }
                    }

                    if($existLincese == TRUE) {
                        // Get file content from svn
                        InsertStatusRecords($sessionId, "正在check out许可文件");

                        $ran = rand();
                        $timeparts = explode(' ',microtime());
                        $etime = $timeparts[1].substr($timeparts[0],1);
                        $tempName = $ran.$timeparts[1];
                        $foldername = $GitName.$tempName;

                        mkdir($foldername, 0777, true);
                        chdir($foldername);

                        try {
                            $command = "svn co ".$urlText." target --depth empty";
                            $checkoutresult = SvnPeer::runCmd($command);
                        }catch(Exception $e) {
                            print $e->getMessage();
                            chdir("..");
                            deldir($foldername);
                            die();
                        }                      
                        chdir("target");
                        try {
                            $command = "svn up ".$foundLinceseFile;
                            $checkoutresult = SvnPeer::runCmd($command);
                        }catch(Exception $e) {
                            print $e->getMessage();
                            chdir("..");
                            chdir("..");
                            deldir($foldername);
                            die();
                        }

                        // Load file content
                        $originalfilecontentarr = file($foundLinceseFile);

                        foreach($originalfilecontentarr as $line) {
                            $originalfilecontent.=$line;
	                    }

                        chdir("..");
                        chdir("..");

                        InsertStatusRecords($sessionId, "正在比较许可文件");
                        // Start compare license files
                        $originalfilecontent = trim(preg_replace('/\s\s+/', ' ', $originalfilecontent));

                        // Pick up the right license files
                        $keyFiles = array();
                        foreach($licensedetectkeywords as $key => $value) {
                            if(stripos($originalfilecontent, $value) == 0) {
                                array_push($keyFiles, $key);
                            }
                        }

                        // Compare original license file with standard license files
                        $diffOpcodeArr = array();
                        $minDiff = 0;
                        $minKey = 0;
                        $maxRatio = 0;
                        $maxKey = 0;
                        foreach($keyFiles as $key => $value) {
                            $licenseProcessedFile = trim(preg_replace('/\s\s+/', ' ', $licesnefilecomtents[$value]));
                            $curl_resultProcessed = trim(preg_replace('/\s\s+/', ' ', $originalfilecontent));
                            array_push($diffOpcodeArr, LicenseDiff::compareLicense($licenseProcessedFile, $curl_resultProcessed));
                            if(LicenseDiff::$diffLength < $minDiff || $minDiff == 0) {
                                $minDiff = LicenseDiff::$diffLength;
                                $minKey = $key;
                            }
                        }

                        $comparedStandardLicenseFileContent = $diffOpcodeArr[$minKey];
                            

                        if(substr_count($comparedStandardLicenseFileContent, "<ins>") == 0 && substr_count($comparedStandardLicenseFileContent, "<del>") == 0) {

                            InsertRecords($urlText, "pass", $proName, $proSite, $proVer, $ipAddr, $protocoltype);

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentencepass\">评估通过!</span>
                                    </div>
                                    <div id=\"suball\">
                                        <img src=\"images/osi-certified.png\" alt=\"\"/>
                                        <div id=\"decl\">许可评估成功</div>
                                        <div id=\"project-name\">";
                            echo $proName;
                            echo "</div>
                                        <div id=\"repo-path\">";
                            echo $urlText;
                            echo "</div>
                                        <div id=\"end-decl\">已经被评估符合";
                            echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                            echo "感谢你对中国开源社的贡献！</div>
                                        <img src=\"images/opensource.png\" alt=\"\" />
                                        <div id=\"sigtime\">OSS alliance signature ";
                            echo date('Y-m-d H:i:s',time()); 
                            echo "</div>
                                    </div>
                                </div>";
                            RemoveStatusRecords($sessionId);
                        }
                        else {
                            InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype);                          

                            echo "<div>
                                    <div id=\"checkwithfailed\">
                                        <span id=\"titleresult\">评估结果:</span>
                                        <span id=\"resultsentence\">评估失败</span>
                                    </div>
                                    <div id=\"declare\">
                                        <span>原因: 您的许可证文件: LICENSE.txt, 已经被检测到, 但是内容并没有完全匹配到由OSI批准的";
                            echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                            echo "许可证文件内容, 详细文本之间的差别请看下面:</span>
                                    </div>
                                    <div id=\"originaldiv\">
                                        <div>您的许可证比较结果</div>
                                        <div id=\"originalcontent\">";
                            echo $comparedStandardLicenseFileContent; 
                            echo "</div>
                                    </div>
                                    <div id=\"standarddiv\">
                                    <div>";
                            echo substr($licensefilenames[$keyFiles[$minKey]], 0, strrpos($licensefilenames[$keyFiles[$minKey]], "."));
                            echo "</div>
                                        <div id=\"standardcontent\">";
                            echo trim(preg_replace('/\s\s+/', ' ', $licesnefilecomtents[$keyFiles[$minKey]]));
                            echo "</div>
                                    </div>
                                    </div>";
                            RemoveStatusRecords($sessionId);
                        }

                        // remove the git folder downloaded
                        deldir($foldername);
                        
                    }
                    else {
                        // Show No License Result
                        InsertRecords($urlText, "fail", $proName, $proSite, $proVer, $ipAddr, $protocoltype);
                        echo "<div>
                            <div id=\"checkwithfailed\">
                                <span id=\"titleresult\">评估结果:</span>
                                <span id=\"resultsentence\">没有许可证文件被发现</span>
                            </div>
                            <div id=\"declare\">
                                <span>原因: 在您提供的代码仓库地址中没有发现许可证文件: </span>
                            </div>
                            <div id=\"sourlink\">
                                <a href=\""; 
                        echo $urlText; 
                        echo "\">";
                        echo $urlText; 
                        echo "</a>
                            </div>
                            <div id=\"moreinfo\">
                                <span>请参考下面的链接:</span><br>
                                <a href=\"www.google.com\">如何建立您的许可证文件?</a><br>
                                <a href=\"www.google.com\">如何建立您的开源许可证?</a>
                            </div>
                        </div>";
                        RemoveStatusRecords($sessionId);                        
                    }                     
                    break;
                }           
        ?>
    </body>
</html>
