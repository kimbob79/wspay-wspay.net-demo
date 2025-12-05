<?php
$sub_menu = "100101";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$sql = " update {$g5['config_table']}
            set cf_1_subj = '{$_POST['cf_1_subj']}',
                cf_2_subj = '{$_POST['cf_2_subj']}',
                cf_3_subj = '{$_POST['cf_3_subj']}',
                cf_4_subj = '{$_POST['cf_4_subj']}',
                cf_5_subj = '{$_POST['cf_5_subj']}',
                cf_6_subj = '{$_POST['cf_6_subj']}',
                cf_7_subj = '{$_POST['cf_7_subj']}',
                cf_8_subj = '{$_POST['cf_8_subj']}',
                cf_9_subj = '{$_POST['cf_9_subj']}',
                cf_10_subj = '{$_POST['cf_10_subj']}',
                cf_11_subj = '{$_POST['cf_11_subj']}',
                cf_12_subj = '{$_POST['cf_12_subj']}',
                cf_13_subj = '{$_POST['cf_13_subj']}',
                cf_14_subj = '{$_POST['cf_14_subj']}',
                cf_15_subj = '{$_POST['cf_15_subj']}',
                cf_16_subj = '{$_POST['cf_16_subj']}',
                cf_17_subj = '{$_POST['cf_17_subj']}',
                cf_18_subj = '{$_POST['cf_18_subj']}',
                cf_19_subj = '{$_POST['cf_19_subj']}',
                cf_20_subj = '{$_POST['cf_20_subj']}',

                cf_1 = '{$_POST['cf_1']}',
                cf_2 = '{$_POST['cf_2']}',
                cf_3 = '{$_POST['cf_3']}',
                cf_4 = '{$_POST['cf_4']}',
                cf_5 = '{$_POST['cf_5']}',
                cf_6 = '{$_POST['cf_6']}',
                cf_7 = '{$_POST['cf_7']}',
                cf_8 = '{$_POST['cf_8']}',
                cf_9 = '{$_POST['cf_9']}',
                cf_10 = '{$_POST['cf_10']}',
                cf_11 = '{$_POST['cf_11']}',
                cf_12 = '{$_POST['cf_12']}',
                cf_13 = '{$_POST['cf_13']}',
                cf_14 = '{$_POST['cf_14']}',
                cf_15 = '{$_POST['cf_15']}',
                cf_16 = '{$_POST['cf_16']}',
                cf_17 = '{$_POST['cf_17']}',
                cf_18 = '{$_POST['cf_18']}',
                cf_19 = '{$_POST['cf_19']}',
                cf_20 = '{$_POST['cf_20']}',

                cfv_1 = '{$_POST['cfv_1']}',
                cfv_2 = '{$_POST['cfv_2']}',
                cfv_3 = '{$_POST['cfv_3']}',
                cfv_4 = '{$_POST['cfv_4']}',
                cfv_5 = '{$_POST['cfv_5']}',
                cfv_6 = '{$_POST['cfv_6']}',
                cfv_7 = '{$_POST['cfv_7']}',
                cfv_8 = '{$_POST['cfv_8']}',
                cfv_9 = '{$_POST['cfv_9']}',
                cfv_10 = '{$_POST['cfv_10']}',
                cfv_11 = '{$_POST['cfv_11']}',
                cfv_12 = '{$_POST['cfv_12']}',
                cfv_13 = '{$_POST['cfv_13']}',
                cfv_14 = '{$_POST['cfv_14']}',
                cfv_15 = '{$_POST['cfv_15']}',
                cfv_16 = '{$_POST['cfv_16']}',
                cfv_17 = '{$_POST['cfv_17']}',
                cfv_18 = '{$_POST['cfv_18']}',
                cfv_19 = '{$_POST['cfv_19']}',
                cfv_20 = '{$_POST['cfv_20']}' ";
sql_query($sql);

//sql_query(" OPTIMIZE TABLE `$g5[config_table]` ");

goto_url('./pay.php', true);
