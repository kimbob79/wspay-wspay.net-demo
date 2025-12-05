<?php
include_once('./_common.php');

//$sql = " desc  g5_payment ";
$sql = " select * from  g5_payment order by pay_id desc limit 2";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    //print_r2($row);
  }


  echo "<br>\n===========================================<br>\n";
  

$sql = " select * from  g5_payment_stn order by pg_id desc limit 10";
$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
    //print_r2($row);
  }


  $sql = " select * from  pay_config";
  $result = sql_query($sql);
  
  while ($row = sql_fetch_array($result)) {
      //print_r2($row);
    }



    $sql = " SHOW tables; ";
    $result = sql_query($sql);
    
    echo "result : " . count($result);

    $i=0;
    while ($row = sql_fetch_array($result)) {
      $i++;
        print_r2($row);
      }
  
    echo "i : ".$i;
?>


