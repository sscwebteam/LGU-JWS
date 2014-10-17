<?php
  class cls_Excel_Export{

    public function InactiveUsers($TableName){
          //start includes
          include_once 'db_conn.php';
          include_once 'date_time.php';
          include_once 'classes/PHPExcel.php';
          include_once 'classes/PHPExcel/IOFactory.php';
          
          //variables
          $template_file="accnts/tp_inactive.xls";

        
        //output excel files as filename will be based on reports title presented
        $file_output="accnts/InactiveUsers_{$TableName}.xls";
        
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
        //$objWrkSheet=$objFileLoader->getActiveSheet();    
        $ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
        $ActiveSheet->setCellValue("A1","Inactive Users as of ".date('Y-m-d'));
        $ActiveSheet->mergecells("A1:D1");
        $start_row=3;
        
        //iterate to table data
        $sqlTableData="select * from {$TableName} order by barangay asc";
        $e=new Exception();
        $qryTableData=mysql_query($sqlTableData) or die(mysql_error()."___File: ".$e->getFile()."___Line: ".$e->getLine());
        while($rowTableData=mysql_fetch_array($qryTableData)){
            $ActiveSheet->setCellValue("A".$start_row,$rowTableData['accnt_no']);
            $ActiveSheet->setCellValue("B".$start_row,$rowTableData['accnt_name']);
            $ActiveSheet->setCellValue("C".$start_row,$rowTableData['inactive_since']);
            $ActiveSheet->setCellValue("D".$start_row,$rowTableData['barangay']);
            $start_row++;
        }    
      
      $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
      $ActiveSheet->getColumnDimension('B')->setAutoSize(true);
      $ActiveSheet->getColumnDimension('C')->setAutoSize(true);
      $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
        
        //check if file exist
        ob_end_flush();
        //ob_end_clean();
        if(file_exists($file_output)){unlink($file_output);}
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
        //create the download link for export
        self::CreateDownloadLink($file_output);
    } //function ends
    
    public function CreateDownloadLink($filename){
        ?><span class="art-button-wrapper"><span class="art-button-l"></span><span class="art-button-r"></span><a href="<?php echo $filename; ?>" class="art-button">Export to Excel File</a></center>
            <?php
    } //function ends
    
    public function LedgerDownloadIND($account_no){
          include_once 'db_conn.php';
          include_once 'cls_codes.php';
          include_once 'classes/PHPExcel.php';
          include_once 'classes/PHPExcel/IOFactory.php';
          include_once 'cls_user.php';
          //variables
          $template_file="accnts/tp_ledger.xls";
        
        //output excel files as filename will be based on reports title presented
        $file_output="accnts/ledger_{$account_no}.xls";
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
        //$objWrkSheet=$objFileLoader->getActiveSheet();    
        //generate data profile of the concessionaire
        //$Name=cls_user_get::ProfileValue('acct_no',$account_no,'applicant');
        $ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
        $ActiveSheet->setCellValue("C7",$account_no);
        $ActiveSheet->setCellValue("C8",cls_user_get::ProfileValue('acct_no',$account_no,'applicant'));
        $ActiveSheet->setCellValue("C9",cls_misc::toString(cls_user_get::ProfileValue('acct_no',$account_no,'address_brgy'),'Barangay'));
        $ActiveSheet->setCellValue("C10",cls_user_get::ProfileValue('acct_no',$account_no,'date_installed'));
        $ActiveSheet->setCellValue("J7",cls_user_get::ProfileValue('acct_no',$account_no,'serial_no'));
        $ActiveSheet->setCellValue("J8",cls_user_get::ProfileValue('acct_no',$account_no,'brand'));
        
        //write date generated updates
        $ActiveSheet->mergecells("A12:L12");
        $ActiveSheet->setCellValue("A12",'Ledger Data Generated as of '.date('Y-m-s'));
        $start_row=14;
        
        //iterate to table data
        $TableName=cls_misc::ConvertToTableName($account_no);
        $sqlTableData="select * from {$TableName} order by reading_date asc";
        $e=new Exception();
        $qryTableData=mysql_query($sqlTableData) or die(mysql_error()."___File: ".$e->getFile()."___Line: ".$e->getLine());
        while($rowTableData=mysql_fetch_array($qryTableData)){
            $ActiveSheet->setCellValue("A".$start_row,$rowTableData['id']);
            $ActiveSheet->setCellValue("B".$start_row,$rowTableData['reading_date']);
            $ActiveSheet->setCellValue("C".$start_row,$rowTableData['meter_reading']);
            $ActiveSheet->setCellValue("D".$start_row,$rowTableData['cu_used']);
            $ActiveSheet->setCellValue("E".$start_row,$rowTableData['pen_fee']);
            $ActiveSheet->setCellValue("F".$start_row,$rowTableData['bill_amnt']);
            $ActiveSheet->setCellValue("G".$start_row,$rowTableData['loan_MLP']);
            $ActiveSheet->setCellValue("H".$start_row,$rowTableData['loan_MF']);
            $ActiveSheet->setCellValue("I".$start_row,$rowTableData['total']);
            $ActiveSheet->setCellValue("J".$start_row,$rowTableData['OR_num']);
            $ActiveSheet->setCellValue("K".$start_row,$rowTableData['OR_date']);
            $ActiveSheet->setCellValue("L".$start_row,$rowTableData['remarks']);
            $start_row++;
        }    
      
      
/*      $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
      $ActiveSheet->getColumnDimension('B')->setAutoSize(true);
      $ActiveSheet->getColumnDimension('C')->setAutoSize(true);
      $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
*/        
        //check if file exist
        ob_end_flush();
        //ob_end_clean();
        if(file_exists($file_output)){unlink($file_output);}
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
        //create the download link for export
        //self::CreateDownloadLink($file_output);
    } //function ends
          
    }//class ends

?>
