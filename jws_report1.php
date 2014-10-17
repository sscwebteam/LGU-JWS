<?php
  class jws_reporting1{
    
    
    public function fnEntry($YearMonth=null){ //entry functions for this class
        include_once 'cls_codes.php';
        
        $MainTable="jws_reports1";
        //$YearMonthNow=date('Y-m'); //initiate year now
        //$YearMonthNow='2013-04';
        $YearMonthNow=$YearMonth;
        $MainTable="jws_reports1";    
        $BarangayCodes=cls_misc::OptArrayValues('Barangay');
        for($i=0;$i < count($BarangayCodes);$i++){
            jws_reporting1::CheckEntry($YearMonthNow,$BarangayCodes[$i],$MainTable);
            jws_reporting1::Total_Concessionaires($YearMonthNow,$BarangayCodes[$i],$MainTable);
            jws_reporting1::Total_Active_Concessionaires($YearMonthNow,$BarangayCodes[$i],$MainTable); 
            jws_reporting1::SumTotalCuUsed($YearMonthNow,$BarangayCodes[$i],$MainTable);
        }
            $arrData=jws_reporting1::arrGetDataForRenderingResults($YearMonthNow);
//          print_r($arrData);
//            break;
            jws_reporting1::ResultScreenDump($arrData,$YearMonthNow);
            $ReportsTitle="Summary Reports on Barangay Water Usage, Collectibles and Collections Billing Month={$YearMonthNow}"; 
            jws_reporting1::DumpRenderResultsToExcel($arrData,$YearMonthNow,$ReportsTitle);
             
            
            
    }
    
    public function ResultScreenDump($arrData, $YearMonthValue){
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        echo "<table><tr><td colspan='6'><cente>Summary Reports on Barangay Water Usage, Collectibles and Collections Billing Month={$YearMonthValue}</center></td></tr>";
        echo "<tr><td>Barangay</td><td>Cu.m. Used</td><td>Total Collectibles</td><td>Total Collections</td><td>Collection<br> Effieciency</td><td>Backlog<br> Amounts</td></tr>";
        foreach($arrData as $key=>$value){
            $strBrgy=cls_misc::toString($value['brgy'],'Barangay');
            $total_collections=($value['collections_onDue'] + $value['collections_afterDue']);
            $total_collectibles=($value['collectibles']);
            $total_cu_used=($value['cu_m_used']);
            $EffCollect=@($total_collections/$total_collectibles) *100;
            $BackLogs=($total_collectibles - $total_collections);
            
            echo "<tr><td>".$strBrgy."</td><td><p align='right'>".cls_misc::gFormatNumber($total_cu_used)."</td><td><p align='right'>".cls_misc::gFormatNumber($total_collectibles)."</td><td><p align='right'>".cls_misc::gFormatNumber($total_collections)."</td><td><p align='right'>".cls_misc::gFormatNumber($EffCollect)."%</td><td><p align='right'>".cls_misc::gFormatNumber($BackLogs)."</td></tr></center."; 
        }
         echo "</table>";
    }
    
    public function arrGetDataForRenderingResults($YearMonthValue){
        include_once 'db_conn.php';
        $sql="select * from jws_reports1 where month='{$YearMonthValue}' order by brgy asc";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            $arrData[]=array('brgy'=>$row['brgy'],'totalC'=>$row['totalC'],'activeC'=>$row['activeC'],'cu_m_used'=>$row['cu_m_used'],'collectibles'=>$row['collectibles'],'collections_onDue'=>$row['collections_onDue'],'collections_afterDue'=>$row['collections_afterDue']);
        }
        return $arrData;    
    }
    
    
    public function SumTotalCuUsed($YearMonth,$Barangay,$MainTable){ //the Year-month specifies the consumes for the month
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        include_once 'cls_bill.php';
        
        $AccountNos=cls_user_get::all_concessionaires_account_no($Barangay);
        //variables
        $cu_used=0;$collectibles=0;$collections_afterDue=0;$collections_onDue=0;
        for($i=0;$i < count($AccountNos);$i++){
            $LedgerTableName=cls_misc::ConvertToTableName($AccountNos[$i]);
            //if(cls_user_get::isPadLock($LedgerTableName)==false){
                $sql="select * from {$LedgerTableName} where reading_date like '{$YearMonth}-%'";
                $e=new Exception();
                $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                $row=mysql_fetch_array($qry);
                $ActualReadingDate=$row['reading_date'];
                $cu_used=$row['cu_used'] + $cu_used;
                $collectibles=$row['total'] + $collectibles;
                if($row['OR_num']!=null || $row['OR_num']!=''){
                    //FFU # 30
                    $ReadingPaymentExt=cls_misc::DateAdd($ActualReadingDate,'22','day');
                    $ActualPaymentDate=$row['OR_date'] ;
                    if($ActualPaymentDate > $ReadingPaymentExt){
                        $collections_afterDue=$row['total'] + $collections_afterDue;
                    }else{
                        $collections_onDue=$row['total'] + $collections_onDue;
                    }
                }
            //}
        }
        $sql="update {$MainTable} set cu_m_used='{$cu_used}',collectibles='{$collectibles}',collections_onDue='{$collections_onDue}',collections_afterDue='{$collections_afterDue}' where month='{$YearMonth}' and brgy='{$Barangay}'";
        $e=new Exception();
        mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
    }
    
    public function Total_Concessionaires($YearMonth,$Barangay,$MainTable){
        include_once 'cls_user.php';
        include_once 'db_conn.php';
        $TotalC=count(cls_user_get::all_concessionaires_account_no($Barangay));
        $sql="update {$MainTable} set totalC='{$TotalC}' where month='{$YearMonth}' and brgy='{$Barangay}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
    }
    
    public function getTotalC($Barangay){
        include_once 'cls_user.php';
        $TotalC=count(cls_user_get::all_concessionaires_account_no($Barangay));
        return $TotalC;
    }
    
    public function Total_Active_Concessionaires($YearMonth,$Barangay,$MainTable){
        $ActiveC=jws_reporting1::getTotalC($Barangay) - jws_reporting1::Inactive_Concessionaires($Barangay);
        $sql="update {$MainTable} set activeC='{$ActiveC}' where month='{$YearMonth}' and brgy='{$Barangay}'";
        $e=new Exception();
        mysql_query($sql) or die(mysql_query()."__File: ".$e->getFile()."__Line: ".$e->getLine());
    }
    
    public function Inactive_Concessionaires($Barangay=null){
        include_once 'cls_user.php';
        $inactiveC=cls_user_get::CountInactiveC($Barangay);
        return $inactiveC;
    }
    
    public function CheckEntry($YearMonth,$BarangayCode,$MainTable){
        include_once 'db_conn.php';
        $sql="select * from {$MainTable} where month='{$YearMonth}' and brgy='{$BarangayCode}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        if(mysql_numrows($qry)==0){
            $sql="insert into {$MainTable}(month,brgy)values('{$YearMonth}','{$BarangayCode}')";
            $e=new Exception();
            mysql_query($sql) or die(mysql_query()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        }
    }
    
    public function DumpRenderResultsToExcel($DataArray,$DateValue,$ReportsTitle){
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        include_once 'cls_user.php';
        include_once 'date_time.php';
        include_once 'classes/PHPExcel.php';
        include_once 'classes/PHPExcel/IOFactory.php';

        //select paper size format
        $template_file="accnts/tpl_jws1.xls";
        
        //output excel files as filename will be based on reports title presented
        $file_output="accnts/jws1_".$DateValue.".xls";
        
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
        //$objWrkSheet=$objFileLoader->getActiveSheet();    
        $ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
        $ActiveSheet->setCellValue("A1",$ReportsTitle);
        $start_row=5;
        if(count($DataArray)==0){die();}
        foreach($DataArray as $key => $value){
            $strBrgy=cls_misc::toString($value['brgy'],'Barangay');
            $ActiveSheet->setCellValue("A".$start_row,$strBrgy);
            $ActiveSheet->setCellValue("B".$start_row,$value['totalC']);
            $ActiveSheet->setCellValue("C".$start_row,$value['activeC']);
            $ActiveSheet->setCellValue("D".$start_row,$value['cu_m_used']);
            $ActiveSheet->setCellValue("F".$start_row,$value['collectibles']);
            $ActiveSheet->setCellValue("H".$start_row,$value['collections_onDue']);
            $ActiveSheet->setCellValue("I".$start_row,$value['collections_afterDue']);
            $start_row++;
        }
        //start adjusting the columns
        $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('F')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('H')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('I')->setAutoSize(true);
        
        //check if file exist
        if(file_exists($file_output)){unlink($file_output);}
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
        //create the download link for export
        jws_reporting1::CreateDownloadLink($file_output);
    } //function ends
    
    

    public function CreateDownloadLink($filename){
        ?><span class="art-button-wrapper"><span class="art-button-l"></span><span class="art-button-r"></span><a href="<?php echo $filename; ?>" class="art-button">Export to Excel File</a><?php
    }
    
    
  } //class ends
    ?>
