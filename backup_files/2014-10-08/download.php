<?php ob_start();
//session_start();
//start output buffering
if (ob_get_contents()!='') {
	ob_get_clean();
}?>
<?php include_once 'juban_functions.php';?>
<?php include_once 'listings.php';
        include_once 'cls_codes.php'; 
     
switch ($_REQUEST['action']){
			case 'download_recv':
				listings_type::receivables_excel($_REQUEST['brgy'],$_REQUEST['year'],$_REQUEST['month']);
				break;	
            case 'download_receipt':
                listings_type::cash_received_excel($_REQUEST['brgy'],$_REQUEST['year'],$_REQUEST['month']);
                break;  
            case 'download_ledger':
                $acct =  base64_decode($_REQUEST['data']);
                $sql = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$acct),"none");
                #declare an array and  push into it the rows retrieve from the above statements
                $data = array();
                    while($row = mysql_fetch_assoc($sql)){
                        array_push($data,array('reading_date'=>$row['reading_date'],'meter_reading'=>$row['meter_reading'],'cu_used'=>$row['cu_used'],'pen_fee'=>$row['pen_fee'],'bill_amnt'=>$row['bill_amnt'],'loan_mlp'=>$row['loans_MLP'],'loan_mf'=>$row['loans_MF'],'total'=>$row['total'],'or_no'=>$row['OR_num'],'or_date'=>$row['OR_date'],'remarks'=>$row['remarks']));
                    }
                 #get the profile from another table before appending it to the results above
                    $profile = mysql_fetch_assoc(sql_retrieve::request_rows("*","profile"," acct_no='".$acct."'"));
                 #send it to the function for excel download
                    listings_type::download_ledger($data,$profile);
                break;    
}
switch ($_REQUEST['request']) {   
            case 'init_dl': //used to create billing form on excel file,refer to billing for more details
            $accnt_no=base64_decode($_REQUEST['accnt_no']);
            $last_bill=base64_decode($_REQUEST['last_bill']);
            $ledger_row = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$accnt_no)," reading_date='".$last_bill."'"); //gets the billed row
            $last_bill_result=mysql_fetch_assoc($ledger_row); 
            billing_download($last_bill_result,$accnt_no,$last_bill_result);
            break;   
              
} 
 function billing_download($values,$acctno,$profile){
     #this will write values to excel for the bills of clients
     #excel declaration
            error_reporting(E_ALL);
            date_default_timezone_set('Europe/London');    
            require_once '../Classes/PHPExcel.php';
            $objReader = new PHPExcel_Reader_Excel5();
            $objPHPExcel = $objReader->load("accnts/gagamit.xls");    
            ini_set("memory_limit","-1");
      ##end of excel declarationi      
     #start of writing
         $objPHPExcel->getActiveSheet()->setCellValue('b10',$values['meter_reading']); //present reading
         $objPHPExcel->getActiveSheet()->setCellValue('c10',$values['meter_reading']-$values['cu_used']); //previous
         $objPHPExcel->getActiveSheet()->setCellValue('d10',$values['cu_used']); //cu_used
         $objPHPExcel->getActiveSheet()->setCellValue('e10',$values['bill_amnt']); //bill_amnt
         //$objPHPExcel->getActiveSheet()->setCellValue('c10',$values['meter_reading']-$values['cu_used']); //previous
         //$objPHPExcel->getActiveSheet()->setCellValue('c10',$values['meter_reading']-$values['cu_used']); //previous
         $objPHPExcel->getActiveSheet()->setCellValue('e14',$values['loans_MLP']); //material loan        
         $objPHPExcel->getActiveSheet()->setCellValue('d18',$values['total']); //amouunt
         $objPHPExcel->getActiveSheet()->setCellValue('d20',cls_misc::toString(substr($values['reading_date'],5,2),"month")."(".substr($values['reading_date'],0,4).")"); //month of
         $objPHPExcel->getActiveSheet()->setCellValue('d22',$acctno); //accout number
         $objPHPExcel->getActiveSheet()->setCellValue('d23',$profile['meter_no']); //serial
         $objPHPExcel->getActiveSheet()->setCellValue('c25',$profile['name']); //name
         $objPHPExcel->getActiveSheet()->setCellValue('c26',$profile['address_brgy']); //address
        // $objPHPExcel->getActiveSheet()->setCellValue('d28',); //due_date
         $objPHPExcel->getActiveSheet()->setCellValue('d30',$_SESSION['username']); //prepared by
         $objPHPExcel->getActiveSheet()->setCellValue('d33',$values['meter_reading']-$values['cu_used']); //date
          
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
               header('Content-Type: application/vnd.ms-excel');
               header('Content-Disposition: attachment;filename="relan.xls');
               // header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
     ##end of force browser download           
     #close excel        
                $objPHPExcel->disconnectWorksheets();                
                unset($objPHPExcel);       
     ##end of close excel
}
?>