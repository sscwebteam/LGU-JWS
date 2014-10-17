<?php
class cls_CollectorsReport { //class reporting for abstracts
    
    public function fnEntry($Month=null,$Year=null,$Day=null,$FilterCashier){ //main function or class entry procedures, alternative for __construct__ method to prevent class bypass
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        //$AllAccounts=cls_user_get::all_concessionaires_account_no(); //create table for today's update'
        $AllAccounts=cls_CollectorsReport::AccntNosEncodedBy($FilterCashier);
        $TableName=cls_CollectorsReport::CreateTable('CollectorsReport','tp_awws',$FilterCashier); //create the table
        cls_CollectorsReport::UpdateRelatedData($AllAccounts,$TableName);
        //create an option for reporting on-screen and for output file as well
        cls_CollectorsReport::RenderResults($Month,$Year,$Day,$FilterCashier);
        //return $AllAccounts;
    }
    
    public function AccntNosEncodedBy($FilterCashier){
        include_once 'db_conn.php';
        //FFU#35
        $sql="select * from or_log where encodedBy='{$FilterCashier}' group by or_number";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            $arrData[]=$row['issued_to_accnt'];
        }
        return $arrData;
    }
    public function RenderResults($Month=null,$Year=null,$Day=null,$FilterCashier){
        include_once 'cls_user.php';
        $e=new Exception();
        $CurrentTableName=cls_CollectorsReport::ShowCurrentTableNameCreated($FilterCashier);
        $CommonPrefixSQL="select * from {$CurrentTableName} where (or_num != 'N/A' and or_num !='0') and(OR_date!='')";
        $year_now=date('Y');$month_now=date('m');$day_now=date('d');
        /*if($Month=='' && $Year=='' && $Day==''){ //selecting all results from the table created
            $PaperSize='A3';
            $SQL_RenderResults="{$CommonPrefixSQL} and OR_date like '{$year_now}%' order by or_date asc,or_num asc";
        }elseif($Year=='' && $Month!='' && $Day==''){ //selecting specific month reports for current fiscal year
            $SQL_RenderResults="{$CommonPrefixSQL} and or_date like '{$year_now}-{$Month}-%' order by or_date asc,or_num asc";
        }elseif($Year=='' && $Month!='' && $Day!='' ){ //selecting specific for the day reports
            $SQL_RenderResults="{$CommonPrefixSQL} and or_date like '{$year_now}-{$Month}-{$Day}' order by or_date asc,or_num asc";
        }
        */
        //set sql statement
        if($Year==''){$year_now;}else{$year_now=$Year;$strYear=$year_now; }
        if($Month==''){$month_now='%';$strMonth='All Months';}else{$month_now=$Month;$strMonth=$Month;}
        if($Day==''){$day_now='%';$strDay='All Days';}else{$day_now=$Day;$strDay=$Day;}
        $SQL_RenderResults="{$CommonPrefixSQL} and or_date like '{$year_now}-{$month_now}-{$day_now}' order by or_date asc,or_num asc";
        
        //set paper size
        if($day_now!='%' && $month_now!='%'){$PaperSize='legal';}else{$PaperSize='A3';}
        
        //start query the sql statement
        $QRY_RenderResults=mysql_query($SQL_RenderResults) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
        while($ROW_RenderResults=mysql_fetch_array($QRY_RenderResults)){
            $data[]=array('id'=>$ROW_RenderResults['id'],
            'OR_date'=>$ROW_RenderResults['OR_date'],
            'OR_num'=>$ROW_RenderResults['OR_num'],
            'Payee'=>$ROW_RenderResults['Payee'],
            'address_brgy'=>$ROW_RenderResults['address_brgy'],
            'app_fee_partial'=>$ROW_RenderResults['app_fee_partial'],
            'app_fee_full'=>$ROW_RenderResults['app_fee_full'],
            'meter_fee'=>$ROW_RenderResults['meter_fee'],
            'MLP'=>$ROW_RenderResults['MLP'],
            'water_bill'=>$ROW_RenderResults['water_bill'],
            'penalty_fee'=>$ROW_RenderResults['penalty_fee'],
            'misc_fee'=>$ROW_RenderResults['misc_fee'],
            'total'=>$ROW_RenderResults['total']
            );
        }
        //start the render options
        
        cls_CollectorsReport::DumpRenderResultsToScreen($data);
        $DateValue=$year_now."-".$month_now."-".$day_now;
        $FullCollectorName=cls_user_get::FullCollectorsName($FilterCashier);
        $ReportsTitle="Abstract Report for {$year_now}-{$strMonth}-{$strDay} CollectorName={$FullCollectorName}";
        cls_CollectorsReport::DumpRenderResultsToExcel($data,$PaperSize,$DateValue,$ReportsTitle);
    }
    
    //[start] stage for commit to remote PS(status: ongoing-edit full function)
    public function DumpRenderResultsToExcel($DataArray,$PaperSize,$DateValue,$ReportsTitle){
        set_time_limit(0);
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        include_once 'cls_user.php';
        include_once 'date_time.php';
        include_once 'classes/PHPExcel.php';
        include_once 'classes/PHPExcel/IOFactory.php';

        //select paper size format
        if($PaperSize=='legal'){$template_file="accnts/awws_legal.xls";}elseif($PaperSize=='A3'){$template_file="accnts/awws_a3.xls";}
        
        //output excel files as filename will be based on reports title presented
        $file_output="accnts/".$ReportsTitle.".xls";
        
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
        //$objWrkSheet=$objFileLoader->getActiveSheet();    
        $ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
        $ActiveSheet->setCellValue("A1",$ReportsTitle);
        $start_row=4;
        if(count($DataArray)==0){die();}
        foreach($DataArray as $key => $value){
            $ActiveSheet->setCellValue("A".$start_row,$value['OR_date']);
            $ActiveSheet->setCellValue("B".$start_row,$value['OR_num']);
            $PayeeName=cls_user_get::ProfileValue('acct_no',$value['Payee'],'applicant');
            $ActiveSheet->setCellValue("C".$start_row,$PayeeName);
            $strBarangay=cls_misc::toString($value['address_brgy'],'Barangay');
            $ActiveSheet->setCellValue("D".$start_row,$strBarangay);
            $ActiveSheet->setCellValue("E".$start_row,$value['app_fee_partial']);
            $ActiveSheet->setCellValue("F".$start_row,$value['app_fee_full']);
            $ActiveSheet->setCellValue("G".$start_row,cls_misc::RemoveZerosForExcel($value['meter_fee']));
            $ActiveSheet->setCellValue("H".$start_row,cls_misc::RemoveZerosForExcel($value['MLP']));
            $ActiveSheet->setCellValue("I".$start_row,cls_misc::RemoveZerosForExcel($value['water_bill']));
            $ActiveSheet->setCellValue("J".$start_row,cls_misc::RemoveZerosForExcel($value['penalty_fee']));
            $ActiveSheet->setCellValue("K".$start_row,cls_misc::RemoveZerosForExcel($value['misc_fee']));
            $ActiveSheet->setCellValue("L".$start_row,$value['total']);
            $SubTotal=$SubTotal + $value['total'];
            $start_row++;
        }
        //$start_row=$start_row +1;
        //$ActiveSheet->setCellValue("A".$start_row,"Total Collection for this Report=".cls_misc::gFormatNumber($SubTotal));
        $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
        //check if file exist
        if(file_exists($file_output)){unlink($file_output);}
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
        //create the download link for export
        cls_CollectorsReport::CreateDownloadLink($file_output);
    } //function ends
    
    
    //todo: [end] stage for commit to remote PS(status: ongoing-edit full function)    
    public function CreateDownloadLink($filename){
        ?><span class="art-button-wrapper"><span class="art-button-l"></span><span class="art-button-r"></span><a href="<?php echo $filename; ?>" class="art-button">Export to Excel File</a></center>
            <?php
    }
    
    public function ScreenReportHeader(){
        echo "<table width='100%'><tr><td><strong><center>Date</center></strong></td>
        <td><strong><center>OR Number</center></strong></td>
        <td><strong><center>Payee</center></strong></td> 
        <td><strong><center>Barangay</center></strong></td>
        <td><strong><center>App Fee-<br>Partial</center></strong></td>
        <td><strong><center>App Fee-<br>Full</center></strong></td>
        <td><strong><center>Meter<br> Fee</center></strong></td>
        <td><strong><center>MLP</center></strong></td>
        <td><strong><center>Water<br> Bill</center></strong></td>
        <td><strong><center>Penalty<br> Fee</center></strong></td>
        <td><strong><center>Misc Fee</center></strong></td>
        <td><strong><center>Total</center></strong></td></tr>";
    }
    
    public function DumpRenderResultsToScreen($DataArray){
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        cls_CollectorsReport::ScreenReportHeader();
        foreach($DataArray as $key => $value){
            $data_row="<tr>";
            $data_row=$data_row."<td>{$value['OR_date']}</td>";
            $data_row=$data_row."<td><center>{$value['OR_num']}<center></td>";
            $PayeeName=cls_user_get::ProfileValue('acct_no',$value['Payee'],'applicant');
            $data_row=$data_row."<td>{$PayeeName}</td>";
            $strBarangay=cls_misc::toString($value['address_brgy'],'Barangay');
            $data_row=$data_row."<td>{$strBarangay}</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['app_fee_partial'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['app_fee_full'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['meter_fee'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['MLP'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['water_bill'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['penalty_fee'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['misc_fee'])."</td>";
            $data_row=$data_row."<td>".cls_misc::RemoveZeros($value['total'])."</td></tr>";
            $SubTotal=$SubTotal + $value['total'];
            echo $data_row;
        }
        echo "<tr><td colspan='12'>SubTotal Collection=".cls_misc::gFormatNumber($SubTotal)."</td></tr>";
        echo "</table>";

        //print_r($DataArray);
        
    }
    
    public function UpdateRelatedData($Ref_Data,$TableName){ //update the related data in the cls_CollectorsReport table specified
    //echo "<br>test: updating reference data to tablename={$TableName}<br>";
    set_time_limit(0); 
    include_once 'db_conn.php';
    include_once 'cls_codes.php';
    if(is_array($Ref_Data)){
        for($i=0;$i < count($Ref_Data);$i++){
            $LedgerTableName=cls_misc::ConvertToTableName($Ref_Data[$i]);
            $SQL_UpdateRelatedData="SELECT sum(pen_fee) as pen_fee,OR_num,OR_date,sum(bill_amnt) as bill_amnt,sum(total) as total,sum(loans_MLP) as loans_MLP,sum(loans_MF) as loans_MF,sum(misc) as misc_fee FROM {$LedgerTableName} where (OR_date is not null and OR_date <> '' and OR_date <> '0000-00-00') or (OR_num <> '' and OR_num is not null and OR_num <> '00000000') group by OR_num order by OR_num asc";
            $e=new Exception();
            $QRY_UpdateRelatedData=mysql_query($SQL_UpdateRelatedData) or die(mysql_error()."-".$e->getFile()."-".$e->getLine());
            while($row_UpdateRelatedData=mysql_fetch_array($QRY_UpdateRelatedData)){
                $payee=$Ref_Data[$i];
                $or_date=$row_UpdateRelatedData['OR_date'];
                $or_num=$row_UpdateRelatedData['OR_num'];
                $pen_fee=$row_UpdateRelatedData['pen_fee'];
                $bill_amnt=$row_UpdateRelatedData['bill_amnt'];
                $loans_MLP=$row_UpdateRelatedData['loans_MLP'];
                $loans_MF=$row_UpdateRelatedData['loans_MF'];
                $misc_fee=$row_UpdateRelatedData['misc'];
                $total=$row_UpdateRelatedData['total'];
                $address_brgy=cls_user_get::ProfileValue('acct_no',$Ref_Data[$i],'address_brgy');
                //insert here to get OR duplication before entry
                $SQL_strCheckEntry="select * from {$TableName} where OR_num='{$or_num}'";
                $e=new Exception();
                $QRY_strCheckEntry=mysql_query($SQL_strCheckEntry) or die(mysql_error());
                if(mysql_numrows($QRY_strCheckEntry)==0){
                    //insert the data if no duplication
                    $SQL_InsUpdateRelatedData="insert into {$TableName}(Payee,address_brgy,OR_date,OR_num,meter_fee,MLP,water_bill,penalty_fee,misc_fee,total)values('{$payee}','{$address_brgy}','{$or_date}','{$or_num}','{$loans_MF}','{$loans_MLP}','{$bill_amnt}','{$pen_fee}','{$misc_fee}','{$total}')";
                    $QRY_InsUpdateRelatedData=mysql_query($SQL_InsUpdateRelatedData) or die("Error:".mysql_error()."__Source File:".$e->getFile()."__Line:".$e->getLine());
                }
            }
            //unset($QRY_UpdateRelatedData);
            //unset($QRY_InsUpdateRelatedData);
        }
    }
}
    public function CreateTable($str_TableName,$RefFile,$FilterCashier){ //create table as specified by the user
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        $date=cls_misc::sanitize_hyp(date('Y-m-d'));
        $TableName="{$str_TableName}_{$date}_{$FilterCashier}"; //create the temporary name
        $e=new Exception();
        $sql="CREATE TABLE if not exists ".$TableName." LIKE {$RefFile}";
        mysql_query($sql) or die(mysql_error().$e->getFile()."-".$e->getLine()); //table create
        mysql_query("truncate {$TableName}") or die("error:".mysql_error()."File:".$e->getFile()."__line:".$e->getLine());
        // -p 10 -o mike -c For Upload: [start]Code updates for cls_CollectorsReport and data preparation for collection updates
        $sql="select * from codes where category='cls_CollectorsReport' and descr='{$TableName}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line".$e->getLine());
        if(mysql_numrows($qry)==0){
            $sql="insert into codes(code,descr,category)values('{$TableName}','{$TableName}','cls_CollectorsReport')";
            $E=NEW ErrorException();
            $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
        }
        //-p 10 -o mike -c For Upload: [end]Code updates for cls_CollectorsReport and data preparation for collection updates
        return $TableName; //spit out the name of the table
    }
    
    public function ShowCurrentTableNameCreated($FilterCashier){
        $date=cls_misc::sanitize_hyp(date('Y-m-d'));
        $str_TableName='CollectorsReport';
        $TableName="{$str_TableName}_{$date}_{$FilterCashier}"; 
        return $TableName;        
    }
    
}

?>