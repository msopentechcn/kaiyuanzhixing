<html>
	<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="validationtool">
        <meta name="keywords" content="validationtool">

		<title>OSS License Openness Evaluation</title>
		<link rel="stylesheet" type="text/css" href="css/style.css">
        <link href="css/bootstrap/3.2.0/bootstrap.custom.css" rel="stylesheet">
		<link href="css/2stage.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/common.css">
		
		<script src="scripts/jquery-1.9.1.js" type="text/javascript"></script>
        <script src="scripts/validate.js" type="text/javascript"></script>
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
                <div id="statusinfo">正在初始化</div>
                <img src="images/processing1.gif" alt="processing">
            </div>
        </div>

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
                    <h1 class="title">开源许可评估</h1>
                </div>
            </div>
            <div class="container">
                <div class="column-container">
                    <!--<h3 class="title">开源许可评估</h3>-->
                    <form action="processresult.php" method="post" id="validform">
                        <div class="article-block input-bottom">
			                <p>项目名称</p>
			                <input class="form-control" type="text" name="projectname" id="proname">
                            <span id="wproName"> *</span>
                        </div>
                        <div class="article-block input-bottom">
                            <p>版本号</p>
                            <input class="form-control" type="text" name="version" id="ver">
                        </div>
                        <div class="article-block input-bottom">
                            <p>项目主站</p>
                            <input class="form-control" type="text" name="projectsite" id="projsite">
                            <span id="wproSite">http(s)  *</span>
                        </div>
                        <div class="article-block input-bottom">
                            <p>源代码仓库URL</p>
                            <input class="form-control input-bottom" type="text" name="reprourl" id="reprou">
                            <select class="form-control" name="rptype" id="reprotype">
			                    <option value ="github">Git/GitHub</option>
			                    <option value ="svn">SVN</option>
			                </select>
                            <span id="wproRepo">   *</span>
                        </div>
                        <div class="article-block input-bottom">
			                <p>验证码</p>
			                <input class="form-control" type="text" name="verificationcode" id="vericode">
			                <?php
				                echo '<img id="capchaimg" src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';
			                ?>
                            <span id="wproVeri"></span>
                        </div>
                        <div class="article-block input-bottom">
			                <input type="checkbox" name="termagree" id="term" value="agree"><span id="declare">我已阅读<a href="http://www.google.com">《个人声明》</a>，并保证遵守开源社网站规范</span>
                        </div>
                        <div class="article-block input-bottom">
			                <input class="btn btn-primary" type="submit" value="提交" id="sub" onclick="return validateFields()">
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

        <div class="footer"></div>	
	</body>
</html>
