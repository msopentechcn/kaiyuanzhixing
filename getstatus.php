<?php
    $postJson = $_POST["sessionid"];

    $dbconf = file('conf/db.json');
    $dbjson = '';
    foreach($dbconf as $line) {
        $dbjson .= $line;
    }
    $decodedDbConf = json_decode($dbjson, TRUE);

    $conn = mysql_connect($decodedDbConf['server'],$decodedDbConf['user'],$decodedDbConf['password']) or die ("数据连接错误!!!");
    mysql_select_db('kys', $conn);

    $sqlComm = "select chStatus from kys.processstatus where chSessionId = \"".$postJson."\" order by dtUpdateTime desc LIMIT 1";
    $result = mysql_query($sqlComm, $conn);
    $rowCount = mysql_num_rows($result);
    $row = mysql_fetch_array($result, MYSQL_NUM);

    echo $row[0];
?>