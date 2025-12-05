<?php
// xlsx_raw_download.php
// Excel download script for payment data

// Include database connection
include_once('./_common.php'); // Adjust this to your database connection file

// Check if the date parameters are set
if (!isset($_POST['fr_date']) || !isset($_POST['to_date'])) {
    die('Date parameters are required');
}

// Get and sanitize date parameters
// Get and sanitize date parameters
$fr_date = isset($_POST['fr_date']) ? clean_xss_tags($_POST['fr_date']) : '';
$to_date = isset($_POST['to_date']) ? clean_xss_tags($_POST['to_date']) : '';

// Format dates for the query
$fr_datetime = date('Y-m-d H:i:s', strtotime($fr_date . ' 00:00:00'));
$to_datetime = date('Y-m-d H:i:s', strtotime($to_date . ' 23:59:59'));

// SQL query to get payment data
$sql = "SELECT * FROM g5_payment 
        WHERE pay_datetime BETWEEN '{$fr_datetime}' AND '{$to_datetime}'
        ORDER BY pay_datetime DESC";


$result = sql_query($sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Set headers for Excel download
$filename = "결제내역_".$fr_date."_".$to_date.".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Include the PHPExcel library
require_once './PHPExcel/Classes/PHPExcel.php'; // Adjust path as needed

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Payment System")
                             ->setLastModifiedBy("Payment System")
                             ->setTitle("Payment Data")
                             ->setSubject("Payment Data Export")
                             ->setDescription("Payment data export from database.");

// Add column headers
$headers = array(
    'pay_id', 'pay_type', 'pay', 'pay_num', 'trxid', 'trackId', 'rootTrxId',
    'pay_datetime', 'pay_cdatetime', 'pay_parti', 'pay_card_name', 'pay_card_num',
    'pay_receipt', 'cday', 'mb_1', 'mb_1_name', 'mb_1_fee', 'mb_1_pay',
    'mb_2', 'mb_2_name', 'mb_2_fee', 'mb_2_pay',
    'mb_3', 'mb_3_name', 'mb_3_fee', 'mb_3_pay',
    'mb_4', 'mb_4_name', 'mb_4_fee', 'mb_4_pay',
    'mb_5', 'mb_5_name', 'mb_5_fee', 'mb_5_pay',
    'mb_6', 'mb_6_name', 'mb_6_fee', 'mb_6_pay',
    'dv_type', 'dv_certi', 'dv_tid', 'dv_tid_ori',
    'sftp_mbrno', 'sftp_nurock', 'pg_name', 'memo', 'deposit', 'updatetime', 'datetime'
);

// Set column headers
$col = 0;
foreach ($headers as $header) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $header);
    $col++;
}

// Add data from database
$row_num = 2; // Excel 행 번호 시작
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $col = 0;
    foreach ($row as $key => $value) {
        // 숫자 인덱스는 건너뛰고 문자열 키만 사용
        if(!is_numeric($key)) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row_num, $value);
            $col++;
        }
    }
    $row_num++;
}

// Auto size columns
foreach (range(0, count($headers) - 1) as $col) {
    $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);
}

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Payment Data');

// Set active sheet index to the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Output the Excel file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>