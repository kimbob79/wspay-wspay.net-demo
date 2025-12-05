<?
// https://api.withpay.co.kr/field.php?table=as5.as6_cardata&var=ori
$db_host = "192.168.0.203";
$db_user = "root";
$db_pass = "jskim(*!@@)";
$db_name = "as5";
$db_conn = @mysql_connect($db_host, $db_user, $db_pass);
mysql_query('set names utf8',$db_conn);
if(!$db_conn) {
	echo "데이타베이스 업데이트 중입니다!!<br>잠시 후 접속해 주세요!!<br>";
	exit;
}
$table = $_GET['table'];
$var = $_GET['var'];
$sql = "SHOW COLUMNS FROM $table";
$rst = mysql_query($sql, $db_conn);
$arrString = '';
$Fields = [];
$Keys = [];
$updateFieldString = '';
while($column = mysql_fetch_array($rst)) {
	$Fields[] = $column['Field'];
	if($column['Key'] == 'PRI') $keys[] = $column['Field'];
	if($updateFieldString) $updateFieldString .= ', ';
	if($var)
		$updateFieldString .= "{$column['Field']} = '{\${$var}['{$column['Field']}']}";
	else
		$updateFieldString .= "{$column['Field']} = '\${$column['Field']}'";
}
if($keys) {
	foreach($keys as $key) {
		if($keyString) $keyString .= " and ";
		$keyString .= "'$key' = '".($var?'{':'')."\$".($var?$var."['":'')."{$key}".($var?"']}":'')."'";
	}
	$keyString = " where $keyString";
}
$select = "select ".implode(', ',$Fields)." from $table";
$delete = "delete from $table$keyString";
if($var) $insert = "insert into $table (".implode(", ",$Fields).") values ('{\${$var}[$".implode("']}', '{\${$var}['",$Fields)."']}')";
 else $insert = "insert into $table ('".implode("', '",$Fields)."') values ('$".implode("', '$",$Fields)."')";
$update = "update $table set $updateFieldString";
$arrString = "\$fields = ['".implode("', '",$Fields)."']";
echo $arrString.'<br><br>';
echo $select.'<br><br>';
echo $delete.'<br><br>';
echo $insert.'<br><br>';
echo $update.'<br><br>';
?>