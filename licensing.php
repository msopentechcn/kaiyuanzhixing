<!DOCTYPE html>
<html lang="zh-CN">
	<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
        <meta name="description" content="validationtool">
        <meta name="keywords" content="validationtool">

		<title>开源之星计划</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css">
        <link href="/css/bootstrap/3.2.0/bootstrap.custom.css" rel="stylesheet">
        <link rel="stylesheet" href="/css/rixth-customSelect/jquery.customSelect.css" />
		<link href="/css/2stage.css" rel="stylesheet">
		
		<script src="http://kaiyuanshe.chinacloudapp.cn/ossstar/scripts/jquery-1.9.1.js" type="text/javascript"></script>
        <script src="http://kaiyuanshe.chinacloudapp.cn/ossstar/scripts/validate.js" type="text/javascript"></script>
	</head>
	<body>
        <?php
			session_start();
			$_SESSION = array();

			require_once("simple-php-captcha.php");
			$_SESSION['captcha'] = simple_php_captcha();

            require_once("util/Helper.php");
		?>

        <div id="overlay"></div>
        <div id="statusdialog">
            <div id="dialog">
                <div id="statusinfo">正在探测文件</div>
                <img src="/images/processing1.gif" alt="processing">
            </div>
        </div>

        <div class="wrapper">
            <div class="header">
                <div class="container">
                    <div class="navbar-logo"><a href="http://kaiyuanshe.chinacloudapp.cn/index.php"><img src="http://kaiyuanshe.chinacloudapp.cn/templates/hli/css/images/logo.png" height="55"></a></div>
                    <div class="navbar-collapse">
                    <ul class="navbar-nav touch-menu">
                        <li><span class="glyphicon glyphicon-th" id="touch-menu-btn"></span></li>
                    </ul>
                    <ul class="navbar-nav navbar-list">
                        <li><a href="/#">主页</a></li>
                        <li class="active"><a href="http://kaiyuanshe.chinacloudapp.cn/star-home.html">开源之星计划</a></li>
                        <li><a href="http://kaiyuanshe.chinacloudapp.cn/index.php?option=com_content&view=category&id=8">开源参考文档</a></li>
                        <li class=""><a href="http://kaiyuanshe.chinacloudapp.cn/ambassador-home.html">开源大使计划</a></li>
                        <li><a href="http://kaiyuanshe.chinacloudapp.cn/about.html">关于我们</a></li>
                    </ul>
                    <!--<ul class="navbar-nav navbar-search">
                        <li>
                            <form id="search-form" class="search-form">
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
                    <h1 class="title">开源之星计划</h1>
                </div>
            </div>
            <div class="container">
                <div class="column-container">
                    <!--<h3 class="title">开源许可评估</h3>-->
                    <form action="processresult.php" method="post" id="validform">
                        <div class="article-block input-bottom min-type">
			                <p>项目名称</p>
			                <input class="form-control" type="text" name="projectname" maxlength="16" id="proname">
                            <span id="wproName"> *</span>
                        </div>
                        <div class="article-block input-bottom min-type">
                            <p>版本号</p>
                            <input class="form-control" type="text" name="version" maxlength="10" id="ver">
                        </div>
                        <div class="article-block input-bottom">
                            <p>项目主站</p>
                            <input class="form-control" type="text" name="projectsite" maxlength="100" id="projsite">
                            <span id="wproSite">*</span>
                        </div>
                        <div class="article-block input-bottom small">
                            <p>源代码仓库URL</p>
                            <input class="form-control input-bottom" type="text" name="reprourl" maxlength="100" id="reprou">
                            <select class="form-control" name="rptype" id="reprotype">
			                    <option value ="github">Git/GitHub</option>
			                    <option value ="svn">SVN</option>
			                </select>
                            <span id="wproRepo">   *</span>
                        </div>
                        <div class="article-block input-bottom">
			                <p>验证码</p>
			                <input class="form-control" type="text" name="verificationcode" minlength="5" maxlength="5" id="vericode">
			                <?php
				                echo '<img id="capchaimg" src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';
			                ?>
                            <span>看不清？点击图片</span><span id="wproVeri"></span>
                        </div>
                        <div class="article-block input-bottom">
			                <input type="checkbox" name="termagree" id="term" value="agree"><span id="declare">我已阅读<a href="#">《个人声明》</a>，并保证遵守开源社网站规范</span>
                        </div>
                        <div class="article-block input-bottom">
			                <input class="btn btn-primary resolver-next" type="submit" value="提交" id="sub" onclick="return validateFields()">
                        </div>
                        <div>
                            <span id="wAll">* 必填字段</span>
                        </div>
                        <div>
                            <input type="hidden" id="captchavalue" value="<?php echo $_SESSION['captcha']['code']; ?>">
                            <input type="hidden" name="ipaddr" value="<?php echo getIP(); ?>">
                            <input type="hidden" name="sessionid" id="sessid" value="<?php echo session_id(); ?>">
                        </div>
		            </form>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="container"><a href="http://kaiyuanshe.chinacloudapp.cn/about.html">联系我们</a> | <a href="http://kaiyuanshe.chinacloudapp.cn/index.php" target="_blank">隐私条款</a> | <a href="http://kaiyuanshe.chinacloudapp.cn/index.php" target="_blank">使用条款</a> | 京ICP备<a href="http://www.miibeian.gov.cn/" target="_blank">14047895</a>号</div>
        </div>
    </div>
    <script src="/library/jqueryui/1.8.18/jquery-ui.min.js"></script>
    <script src="/css/rixth-customSelect/jquery.customSelect.min.js"></script>

    <script src="/js/base.js"></script>

    <script>
        $('.singleSelect').customSelect();
    </script>	
	</body>
</html>