<?php
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set("memory_limit","-1");
    include_once 'juban_functions.php';
	include_once 'cls_codes.php';
	include_once 'cls_bill.php';
			
            $accnt_no=base64_decode($_REQUEST['accnt_no']);
            echo "test:account number decoded={$accnt_no}<br>";
			$last_bill=base64_decode($_REQUEST['last_bill']);
            echo "test:bill requested for download={$last_bill}<br>";
			#gets the billed row
			$values_row = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$accnt_no)," reading_date='".$last_bill."'"); 
			$values=mysql_fetch_assoc($values_row); 
			#gets profile
			$profile_row = sql_retrieve::request_rows("*","profile"," acct_no='".$accnt_no."'");
			$profile =mysql_fetch_assoc($profile_row);  
			#gets the due date
			$date_row = mysql_fetch_assoc(sql_retrieve::request_rows("date_payment","dates_sched"," bryg_codes='".$profile['address_brgy']."'"));
			$month_due=substr($last_bill,5,2)+1;
			#checks whether month equals 13
			if($month_due>12){                                                    //month decision
				$month_due=cls_misc::toString(sprintf("%02d",1),"month");
			}else{
				 $month_due=cls_misc::toString(sprintf("%02d",$month_due),"month");
			}
			$day_year_due=$date_row['date_payment']."-".substr($last_bill,0,4);//day and year
			// todo: [start] stage for commit to remote PS (status: ongoinh) -create new due date
			$AccountNo_DueDate=cls_bill_get::payment_grace(cls_bill_get::payment_date($last_bill,$accnt_no));
			$var_array=explode('-',$AccountNo_DueDate);
			$strMonth=cls_misc::CodestoString("code='{$var_array[1]}' and category='month'");
			$newDueDate=$strMonth." ".$var_array[2].",".$var_array[0];
			// todo: [end] stage for commit to remote PS 
			#insert a file to dl_bill table to update remarks of downloads(YES nO download)
				$check_value = mysql_num_rows(sql_retrieve::request_rows("*","dl_bill","accnt_no='".$accnt_no."' and last_bill='".$last_bill."'"));
				if($check_value==0){
					mysql_query("insert into dl_bill(accnt_no, last_bill)values('".$accnt_no."','".$last_bill."')") or die('Was not able to update remarks in download');
				}else{
					//return none
				}
		 #excel declaration
				
				date_default_timezone_set('Asia/Manila'); 
				require_once '../Classes/PHPExcel.php';
				$objReader = new PHPExcel_Reader_Excel5();
				$objPHPExcel = $objReader->load("accnts/gagamit.xls");    
				
		  ##end of excel declarationi      
		 #start of writing
             $objPHPExcel->getActiveSheet()->setCellValue('B10',$values['meter_reading']);
			 $objPHPExcel->getActiveSheet()->setCellValue('C10',$values['meter_reading']-$values['cu_used']);
			 $objPHPExcel->getActiveSheet()->setCellValue('D10',$values['cu_used']);
             $objPHPExcel->getActiveSheet()->setCellValue('E10',$values['bill_amnt']);
             $objPHPExcel->getActiveSheet()->setCellValue('E12',$values['loans_MF']);//meter fee loan from addon FFU#53
             $objPHPExcel->getActiveSheet()->setCellValue('E14',$values['loans_MLP']); //material loan
			 $objPHPExcel->getActiveSheet()->setCellValue('D18',$values['total']); //amouunt
			 $objPHPExcel->getActiveSheet()->setCellValue('D20',cls_misc::toString(substr($values['reading_date'],5,2),"month")."(".substr($values['reading_date'],0,4).")"); //month of
			 $objPHPExcel->getActiveSheet()->setCellValue('D22',$accnt_no); //accout number
			 $objPHPExcel->getActiveSheet()->setCellValue('D23',$profile['meterno']); //serial
			 $objPHPExcel->getActiveSheet()->setCellValue('C25',$profile['applicant']); //name
			 $objPHPExcel->getActiveSheet()->setCellValue('C26',cls_misc::toString($profile['address_brgy'],"barangay")); //address
			 //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,28,$month_due."-".$day_year_due); //due_date
			 $objPHPExcel->getActiveSheet()->setCellValue('D28',$newDueDate); //due_date
             
			 $objPHPExcel->getActiveSheet()->setCellValue('D30',$_SESSION['username']); //prepared by
			 $objPHPExcel->getActiveSheet()->setCellValue('D33', date('D').",".date('F')." ". date('d').",".date('Y')); //date
			  
			  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
              ob_end_clean();
			  header('Content-Type: application/vnd.ms-excel');
			  header('Content-Disposition: attachment;filename='.$accnt_no."_Date_".str_replace("-","_",$last_bill).'.xls');
			  header('Cache-Control: max-age=0');

			  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			  $objWriter->save('php://output');
					
			  $objPHPExcel->disconnectWorksheets();                
			  unset($objPHPExcel);       
?>