<?php
/*
include_once 'classes/PHPExcel.php';
//require_once 'classes/PHPExcel.php';
include_once 'classes/PHPExcel/IOFactory.php';
//declare filetype
$FileType='Excel5';

//declare file template
$file_template='accnts/template.xls';
$file_output='accnts/sample.xls';

$objReader=PHPExcel_IOFactory::createReader($FileType);
$objFileLoader=$objReader->load($file_template);

$objWrkSheet=$objFileLoader->getActiveSheet();

$objWrkSheet->getCell('D30')->setValue('mike');
$objWrkSheet->getCell('D33')->SetValue('mike');
$objWrkSheet->getCell('E10')->SetValue('20000');

if (var_dump(unlink($file_output))==false) {
	$objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
	$objWriter->save($file_output);
}
 */
 include_once 'db_conn.php';
include 'juban_functions.php';
        
$siya = array(array('1','2','3'),array('4','5','6'),array('7','8','9'));
/* foreach($siya as $ako){
if(end($siya)){
    echo "tapos na ".$ako."<br>";
}else{
    echo "dire pa".$ako."<br>";
} 
} */ 
foreach($siya as $mga){
$ako =implode(",",$mga);
echo $ako."<br>";
}                                     
?>