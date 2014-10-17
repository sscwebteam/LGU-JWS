<?php
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set("memory_limit","-1");
    include_once 'juban_functions.php';
	include_once 'cls_codes.php';
	include_once 'cls_bill.php';
    include_once 'cls_user.php';

    $accnt_no=base64_decode($_REQUEST['accnt_no']);
    
    $accnts=explode('|',$accnt_no);
    print_r($accnts);
     #excel declaration
            
            date_default_timezone_set('Asia/Manila'); 
            require_once '../Classes/PHPExcel.php';
            $objReader = new PHPExcel_Reader_Excel5();
            $objPHPExcel = $objReader->load("accnts/gagamit.xls");    
            
      ##end of excel declarationi      

    for($i=0; $i < count($accnts); $i++){
        //expected maximum array counts=3 with upper bound index=2
                $names.=cls_user_get::ProfileValue('acct_no',$accnts[$i],'applicant');
			    $last_bill=base64_decode($_REQUEST['last_bill']);
			    #gets the billed row
			    $values_row = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$accnts[$i])," reading_date='".$last_bill."'"); 
			    $values=mysql_fetch_assoc($values_row); 
			    #gets profile
			    $profile_row = sql_retrieve::request_rows("*","profile"," acct_no='".$accnts[$i]."'");
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
			    $AccountNo_DueDate=cls_bill_get::payment_grace(cls_bill_get::payment_date($last_bill,$accnts[$i]));
			    $var_array=explode('-',$AccountNo_DueDate);
			    $strMonth=cls_misc::CodestoString("code='{$var_array[1]}' and category='month'");
			    $newDueDate=$strMonth." ".$var_array[2].",".$var_array[0];
			    // todo: [end] stage for commit to remote PS 
			    #insert a file to dl_bill table to update remarks of downloads(YES nO download)
				    $check_value = mysql_num_rows(sql_retrieve::request_rows("*","dl_bill","accnt_no='".$accnts[$i]."' and last_bill='".$last_bill."'"));
				    if($check_value==0){
					    mysql_query("insert into dl_bill(accnt_no, last_bill)values('".$accnts[$i]."','".$last_bill."')") or die('Was not able to update remarks in download');
				    }else{
					    //return none
				    }
		     #start of writing
                 $arr_CellsPresentMeterReading=array("B10","G10","L10");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsPresentMeterReading[$i]}",$values['meter_reading']);
                 
			     $arr_CellsPreviousMeterReading=array("C10","H10","M10");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsPreviousMeterReading[$i]}",$values['meter_reading']-$values['cu_used']);
                 
			     $arr_CellsCuUsed=array("D10","I10","N10");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsCuUsed[$i]}",$values['cu_used']);
                 
                 $arr_CellsBillAmount=array("E10","J10","O10");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsBillAmount[$i]}",$values['bill_amnt']);
                 
                 $arr_CellsMeterFee=array("E12","J12","O12");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsMeterFee[$i]}",$values['loans_MF']);//meter fee loan from addon FFU#53
                 if(date('Y-m-d') > $AccountNo_DueDate){
                     $Penalty=$values['bill_amnt'] * 0.05;
                 }else{
                     $Penalty='-';
                 }
                 $arr_CellsPenalty=array("E13","J13","O13");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsPenalty[$i]}",$Penalty);//penalty
                 
                 $arr_CellsMLP=array("E14","J14","O14");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsMLP[$i]}",$values['loans_MLP']); //material loan
			     
                 $arr_CellsAmountDue=array("D18","I18","N18");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsAmountDue[$i]}",$values['bill_amnt'] + $values['loans_MF'] + $Penalty + $values['loans_MLP']); //amouunt
                 
			     $arr_CellsForTheMonthOf=array("D20","I20","N20");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsForTheMonthOf[$i]}",cls_misc::toString(substr($values['reading_date'],5,2),"month")."(".substr($values['reading_date'],0,4).")"); //month of
			     
                 $arr_CellsAccountNo=array("D22","I22","N22");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsAccountNo[$i]}",$accnts[$i]); //accout number
                 
                 $arr_CellsMeterNo=array("D23","I23","N23");
			     $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsMeterNo[$i]}",$profile['meterno']); //serial
			     
                 $arr_CellsApplicantName=array("C25","H25","M25");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsApplicantName[$i]}",$profile['applicant']); //name
			     
                 $arr_CellsConsBrgy=array("C26","H26","M26");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsConsBrgy[$i]}",cls_misc::toString($profile['address_brgy'],"barangay")); //address
			     //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,28,$month_due."-".$day_year_due); //due_date
			     
                 $arr_CellsDueDate=array("D28","I28","N28");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsDueDate[$i]}",$newDueDate); //due_date
                 
			     $arr_CellsUserName=array("D30","I30","N30");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsUserName[$i]}",$_SESSION['username']); //prepared by
			     
                 $arr_CellsCurrDate=array("D33","I33","N33");
                 $objPHPExcel->getActiveSheet()->setCellValue("{$arr_CellsCurrDate[$i]}", date('D').",".date('F')." ". date('d').",".date('Y')); //date
    }			      
	//start buffer output to file
    		      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                  ob_end_clean();
			      header('Content-Type: application/vnd.ms-excel');
			      header('Content-Disposition: attachment;filename='.$names."_Date_".str_replace("-","_",$last_bill).'.xls');
			      header('Cache-Control: max-age=0');

			      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			      $objWriter->save('php://output');
					    
			      $objPHPExcel->disconnectWorksheets();                
			      unset($objPHPExcel);       
?>