<?php

?>

<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
        <meta name="description" content="validationtool">
        <meta name="keywords" content="validationtool">

        <link rel="stylesheet" type="text/css" href="css/validateresults.css">
        <link href="/templates/hli/css/bootstrap/3.2.0/bootstrap.custom.css" rel="stylesheet">
	    <link href="/templates/hli/css/2stage.css" rel="stylesheet">

        <title>评估结果</title>
        <script src="scripts/jquery-1.9.1.js" type="text/javascript"></script>
        <script src="/templates/hli/js/base.js"></script>
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <div class="container">
                    <div class="navbar-logo"><a href="/index.php"><img src="/templates/hli/css/images/logo.png" height="55"></a></div>
                    <div class="navbar-collapse">
                        <ul class="navbar-nav touch-menu">
                            <li><span class="glyphicon glyphicon-th" id="touch-menu-btn"></span></li>
                        </ul>

                        <ul class="navbar-nav navbar-list">
                            <li>
                                <a href="/index.php">主页</a>
                            </li>
                            <li class="active">
                                <a href="/ossstar/licensing.php">开源之星计划</a>
                            </li>
                            <li ><a href="/index.php?option=com_content&view=category&id=8">开源参考文档</a></li>
                            <li class=""><a href="/ambassador.php">开源大使计划</a></li>
                            <li><a href="/about.html">关于我们</a></li>
                        </ul>
                        <ul class="navbar-nav navbar-search">
                            <li>
                                <form id="search-form" class="search-form" action="">
                                    <input type="text" placeholder="search">
                                    <span class="glyphicon glyphicon-search search-form-submit"></span>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>


        <div class="content">
            <div class="column-header">
                <div class="container">
                    <h1 class="title">开源许可评估结果</h1>
                </div>
            </div>
        <!--</div>-->
        <div class="container">
        <?php 
        if($_GET['certID'] == '') {
            echo '对不起，您所查询的证书编号不存在。';
        }

        $certID = $_GET['certID'];
        $licenseName = $_GET['ln'];

        $dbconf = file('conf/db.json');
        $dbjson = '';
        foreach($dbconf as $line) {
            $dbjson .= $line;
        }
        $decodedDbConf = json_decode($dbjson, TRUE);

        $conn = mysql_connect($decodedDbConf['server'],$decodedDbConf['user'],$decodedDbConf['password']) or die ("数据连接错误!!!");

        $sqlComm = "select RepoURLs, timeLastVisit, chProName from kys.checkpasshistory where cerID = \"".$certID."\"";
        try{
            $result = mysql_query($sqlComm, $conn);
        }catch(Exception $e) {
            print_r($e->getMessage());
        }
        $row = mysql_fetch_array($result, MYSQL_NUM);

        $urlText = $row[0];
        $certDate = $row[1];
        $proName = $row[2];

        echo "<div>
                <div id=\"checkwithfailed\">
                    <span id=\"titleresult\">评估结果:</span>
                    <span id=\"resultsentencepass\">评估通过!</span>
                </div>
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
                    <p id=\"result\">经验证符合";
        echo $licenseName;
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
        <div><a href=\"/ossstar/licensing.php\">完成</a></div>
                </div>
            </div>";
        ?>
        </div>
        </div>
            <div class="footer">
                <div class="container"><a href="/about.html">联系我们</a> | <a href="/index.php" target="_blank">隐私条款</a> | <a href="/index.php" target="_blank">使用条款</a> | 京ICP备<a href="http://www.miibeian.gov.cn/" target="_blank">14047895</a>号</div>
            </div>
        </div>
    </body>
</html>
