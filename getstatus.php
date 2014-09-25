<?php
    $postJson = $_POST["sessionid"];

    $conn = mysql_connect('localhost','root','') or die ("数据连接错误!!!");
    mysql_select_db('kys', $conn);

    $sqlComm = "select chStatus from kys.processstatus where chSessionId = \"".$postJson."\" order by dtUpdateTime desc LIMIT 1";
    $result = mysql_query($sqlComm, $conn);
    $rowCount = mysql_num_rows($result);
    $row = mysql_fetch_array($result, MYSQL_NUM);

    echo $row[0];
?>