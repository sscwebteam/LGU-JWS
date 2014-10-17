<?php
ob_start();
class Recievables { //class reporting for abstracts
	
	public function fnEntry($Month=null,$Year=null,$Day=null){ //main function or class entry procedures, alternative for __construct__ method to prevent class bypass
		include_once 'db_conn.php';
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
        include_once 'juban_functions.php';
		$AllAccounts=cls_user_get::all_concessionaires_account_no(); //create table for today's update'
		$TableName=Recievables::CreateTable('Recievables','tp_receivables'); //create the table
        //echo "table name={$TableName}";
        $DateFilter=self::CreateDateFilter($Year,$Month,$Day);

		Recievables::UpdateRelatedData($AllAccounts,$TableName,$DateFilter);
		//create an option for reporting on-screen and for output file as well
		Recievables::RenderResults($Month,$Year,$Day);
		//return $AllAccounts;
	}
	
    public function CreateDateFilter($Year,$Month,$Day){
        if($Year==null){$Year=date('Y');}else{$Year=$Year;}
        if($Month==null){$Month='%';}else{$Month=$Month;}
        if($Day==null){$Day='%';}else{$Day=$Day;}
        return $Year.'-'.$Month.'-'.$Day;
    }

	public function RenderResults($Month=null,$Year=null,$Day=null){
		$e=new Exception();
		$CurrentTableName=Recievables::ShowCurrentTableNameCreated();
		$year_now=date('Y');$month_now=date('m');$day_now=date('d');

		//set sql statement
		if($Year==''){$year_now;}else{$year_now=$Year;$strYear=$year_now; }
		if($Month==''){$month_now='%';$strMonth='All Months';}else{$month_now=$Month;$strMonth=$Month;}
		if($Day==''){$day_now='%';$strDay='All Days';}else{$day_now=$Day;$strDay=$Day;}

		$CommonPrefixSQL="select * from {$CurrentTableName} where";
        $SQL_RenderResults="{$CommonPrefixSQL} reading_date like '{$year_now}-{$month_now}-{$day_now}' order by reading_date asc";
		//set paper size
		if($day_now!='%' && $month_now!='%'){$PaperSize='legal';}else{$PaperSize='A3';}
		
		//start query the sql statement
        //echo "<br>SQL={$SQL_RenderResults}";
		$QRY_RenderResults=mysql_query($SQL_RenderResults) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
		while($ROW_RenderResults=mysql_fetch_array($QRY_RenderResults)){ //todo: FFU#62
			$data[]=array('id'=>$ROW_RenderResults['row_id'],
			'AccountNo'=>$ROW_RenderResults['accntno'],
			'Payee'=>$ROW_RenderResults['name'],
			'ReadingDate'=>$ROW_RenderResults['reading_date'],
			'OtherPayments'=>$ROW_RenderResults['other_payments'],
            'BillAmnt'=>$ROW_RenderResults['bill_amnt'],
            'DueDate'=>$ROW_RenderResults['due_date'],
			'PenaltyAmnt'=>$ROW_RenderResults['penalty_amnt'],
			'TotalReceivables'=>$ROW_RenderResults['total_receivables']
			);
		}
		//start the render options
		Recievables::DumpRenderResultsToScreen($data);
		$DateValue=$year_now."-".$month_now."-".$day_now;
		$ReportsTitle="Recievables Report for {$year_now}-{$strMonth}-{$strDay}";
		Recievables::DumpRenderResultsToExcel($data,$PaperSize,$DateValue,$ReportsTitle);
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
		if($PaperSize=='legal'){$template_file="accnts/recv_legal.xls";}elseif($PaperSize=='A3'){$template_file="accnts/recv_a3.xls";}
		
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
/*        
*            $data[]=array('id'=>$ROW_RenderResults['row_id'],
            'AccountNo'=>$ROW_RenderResults['accntno'],
            'Payee'=>$ROW_RenderResults['name'],
            'ReadingDate'=>$ROW_RenderResults['reading_date'],
            'OtherPayments'=>$ROW_RenderResults['other_payments'],
            'BillAmnt'=>$ROW_RenderResults['bill_amnt'],
            'DueDate'=>$ROW_RenderResults['due_date'],
            'PenaltyAmnt'=>$ROW_RenderResults['penalty_amnt'],
            'TotalReceivables'=>$ROW_RenderResults['total_receivables']
 
* 
*/
            $ActiveSheet->setCellValue("A".$start_row,$value['ReadingDate']);
            $ActiveSheet->setCellValue("B".$start_row,$value['AccountNo']);
            $ActiveSheet->setCellValue("C".$start_row,$value['Payee']);
            $Barangay=cls_misc::toString(cls_user_get::ProfileValue('acct_no',$value['AccountNo'],'address_brgy'),'Barangay');
            $ActiveSheet->setCellValue("D".$start_row,$Barangay);
            $ActiveSheet->setCellValue("E".$start_row,$value['OtherPayments']);
            $ActiveSheet->setCellValue("F".$start_row,$value['BillAmnt']);
            $ActiveSheet->setCellValue("G".$start_row,$value['DueDate']);
            $ActiveSheet->setCellValue("H".$start_row,$value['PenaltyAmnt']);
            $ActiveSheet->setCellValue("I".$start_row,$value['TotalReceivables']);
            
			$start_row++;
		}
		//$start_row=$start_row +1;
		//$ActiveSheet->setCellValue("A".$start_row,"Total Collection for this Report=".cls_misc::gFormatNumber($SubTotal));
		$ActiveSheet->getColumnDimension('A')->setAutoSize(true);
		$ActiveSheet->getColumnDimension('B')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('C')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('E')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('F')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('G')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('H')->setAutoSize(true);
        $ActiveSheet->getColumnDimension('I')->setAutoSize(true);
		//check if file exist
		
        if(file_exists($file_output)){unlink($file_output);}
		//proceed to output creation
		$objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
		$objWriter->save($file_output);
		unset($objReader);
		//create the download link for export
		Recievables::CreateDownloadLink($file_output);
        ob_end_flush();
	} //function ends
	
	
	public function CreateDownloadLink($filename){
		?><br><center><span class="art-button-wrapper"><span class="art-button-l"></span><span class="art-button-r"></span><a href="<?php echo $filename; ?>" class="art-button">Export to Excel File</a></center>
			<?php
	}
	
	public function ScreenReportHeader(){
		echo "<center><table width=\"100%\" cellspacing=\"3\" cellpading=\"3\"><tr><td><strong>Reading Date</strong></td>
		<td><strong>Account No</strong></td>
		<td><strong>Payee</strong></td> 
		<td><strong>Barangay</strong></td>
		<td><strong>Other Payments</strong></td>
		<td><strong>Bill Amount</strong></td>
		<td><strong>Due Date</strong></td>
		<td><strong>Penalty Amount</strong></td>
		<td><strong>Total<br>Receivables</strong></td>";
	}
	
	public function DumpRenderResultsToScreen($DataArray){
        set_time_limit(0);
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
		Recievables::ScreenReportHeader();
        if(count($DataArray)==0){echo '<br>no data found!';die();}
       
		foreach($DataArray as $key => $value){
            $data_row="<tr>";
            $data_row=$data_row."<td>{$value['ReadingDate']}</td>";
            $data_row=$data_row."<td>{$value['AccountNo']}&nbsp;&nbsp;&nbsp;</td>";
            $data_row=$data_row."<td>{$value['Payee']}</td>";
            $Barangay=cls_misc::toString(cls_user_get::ProfileValue('acct_no',$value['AccountNo'],'address_brgy'),'Barangay');
            $data_row=$data_row."<td>{$Barangay}&nbsp;&nbsp;&nbsp;</td>";
            $OtherPayments=cls_misc::RemoveZeros($value['OtherPayments']);
            $data_row=$data_row."<td>{$OtherPayments}</td>";
            $data_row=$data_row."<td>".cls_misc::gFormatNumber($value['BillAmnt'])."</td>";
            $data_row=$data_row."<td>{$value['DueDate']}&nbsp;&nbsp;&nbsp;";
            $data_row=$data_row."<td>".cls_misc::gFormatNumber($value['PenaltyAmnt'])."</td>";
            $data_row=$data_row."<td>".cls_misc::gFormatNumber($value['TotalReceivables'])."</td></tr>";                
			$SubTotal=$SubTotal + $value['TotalReceivables'];
			echo $data_row;
		}
		echo "<tr><td colspan='12'>SubTotal Collectibles=".cls_misc::gFormatNumber($SubTotal)."</td></tr></table></center>";
	}
	
	
    public function UpdateRelatedData($Ref_Data,$TableName,$DateFilter){ //update the related data in the Recievables table specified
    set_time_limit(0);
    include_once 'db_conn.php';
    include_once 'cls_codes.php';
    include_once 'cls_user.php';
    
    if(is_array($Ref_Data)){
        for($i=0;$i < count($Ref_Data);$i++){
            $LedgerTableName=cls_misc::ConvertToTableName($Ref_Data[$i]);
            //get all data from each ledger that satisfies the sql, and filter later during the render of the results
            //$SQL_UpdateRelatedData="SELECT reading_date,sum(pen_fee) as pen_fee,sum(bill_amnt) as bill_amnt,sum(total) as total,sum(loans_MLP) as loans_MLP,sum(loans_MF) as loans_MF,sum(misc) as misc_fee FROM {$LedgerTableName} where (OR_date is null or OR_date = '' and OR_date <> '0000-00-00') or (OR_num = '' or OR_num is null and OR_num <> '00000000') group by OR_num order by OR_num asc";
            $SQL_UpdateRelatedData="SELECT reading_date,pen_fee,bill_amnt,total,loans_MLP,loans_MF,misc as misc_fee FROM {$LedgerTableName} where reading_date like '{$DateFilter}' and (OR_num is NULL or OR_num='')";  
            //group by OR_num order by OR_num asc
            $e=new Exception();
            $QRY_UpdateRelatedData=mysql_query($SQL_UpdateRelatedData) or die(mysql_error()."-".$e->getFile()."-".$e->getLine());
            
            //process other miscellaneous values
            $address_brgy=cls_user_get::ProfileValue('acct_no',$Ref_Data[$i],'address_brgy');
            $fullname=cls_user_get::ProfileValue('acct_no',$Ref_Data[$i],'applicant');

            while($row_UpdateRelatedData=mysql_fetch_array($QRY_UpdateRelatedData)){
                $payee=$Ref_Data[$i];
                $reading_date= $row_UpdateRelatedData['reading_date']; //get the reading date
                $DueDate=auxilliary::dagdag_date((string)$reading_date,'P22D'); //compute for due dates
                $bill_amnt=$row_UpdateRelatedData['bill_amnt']; //get the bill amount
                //compute for the penalty right away
                if(date('Y-m-d') > $DueDate){$penalty=$bill_amnt * 0.05;}else{$penalty=0.00;}
                
                //process other bill payment addons
                $loans_MLP=$row_UpdateRelatedData['loans_MLP'];
                $loans_MF=$row_UpdateRelatedData['loans_MF'];
                $misc_fee=$row_UpdateRelatedData['misc_fee']; 
                $OtherPayments=$pen_fee + $loans_MLP + $loans_MF;
                
                //$total=$row_UpdateRelatedData['total'];
                $total=$bill_amnt + $penalty + $OtherPayments;
                $SQL_InsUpdateRelatedData="insert into {$TableName}(accntno,name,reading_date,other_payments,bill_amnt,due_date,penalty_amnt,total_receivables)
                values('{$payee}','{$fullname}','{$reading_date}','{$OtherPayments}','{$bill_amnt}','{$DueDate}','{$penalty}','{$total}')";
                $QRY_InsUpdateRelatedData=mysql_query($SQL_InsUpdateRelatedData) or die("Error:".mysql_error()."__Source File:".$e->getFile()."__Line:".$e->getLine());   

//            }
/*
                //check reading date and accnt no on consolidated table
                //todo: FFU#79
                $SQL_Check="select * from {$TableName} where accntno='{$payee}' and reading_date='{$reading_date}'";
                $e=new Exception();
                $QRY_Check=mysql_query($SQL_Check) or die(mysql_error()."___File: ".$e->getFile()."__Line: ".$e->getLine());
                if(mysql_numrows($QRY_Check)==0){ //same data entry does not exist
                //prepare the sql statement for data insertion
            }
*/          
        }
    }
    }
    }
    /* public function UpdateRelatedData($Ref_Data,$TableName,$DateFilter){ //update the related data in the Recievables table specified
	//echo "<br>test: updating reference data to tablename={$TableName}<br>";
    set_time_limit(0);
	include_once 'db_conn.php';
	include_once 'cls_codes.php';
    include_once 'cls_user.php';
	if(is_array($Ref_Data)){
		for($i=0;$i < count($Ref_Data);$i++){
			$LedgerTableName=cls_misc::ConvertToTableName($Ref_Data[$i]);
            //get all data from each ledger that satisfies the sql, and filter later during the render of the results
			//$SQL_UpdateRelatedData="SELECT reading_date,sum(pen_fee) as pen_fee,sum(bill_amnt) as bill_amnt,sum(total) as total,sum(loans_MLP) as loans_MLP,sum(loans_MF) as loans_MF,sum(misc) as misc_fee FROM {$LedgerTableName} where (OR_date is null or OR_date = '' and OR_date <> '0000-00-00') or (OR_num = '' or OR_num is null and OR_num <> '00000000') group by OR_num order by OR_num asc";

            //$SQL_UpdateRelatedData="SELECT reading_date,pen_fee,bill_amnt,total,loans_MLP,loans_MF,misc as misc_fee FROM {$LedgerTableName} where (OR_date is null or OR_date = '' and OR_date <> '0000-00-00') or (OR_num = '' or OR_num is null and OR_num <> '00000000') group by OR_num order by OR_num asc";  

            //$SQL_UpdateRelatedData="SELECT reading_date,pen_fee,bill_amnt,total,loans_MLP,loans_MF,misc as misc_fee FROM {$LedgerTableName} where reading_date like '{$DateFilter}' and ((OR_date is NULL or OR_date = '' and OR_date <> '0000-00-00') or (OR_num = '' or OR_num is NULL and OR_num <> '00000000')) group by OR_num order by OR_num asc";

            $SQL_UpdateRelatedData="SELECT reading_date,pen_fee,bill_amnt,total,loans_MLP,loans_MF,misc as misc_fee FROM {$LedgerTableName} where reading_date like '{$DateFilter}' and ((OR_date is NULL or OR_date = '' and OR_date <> '0000-00-00') or (OR_num = '' or OR_num is NULL and OR_num <> '00000000'))";

			$e=new Exception();
			$QRY_UpdateRelatedData=mysql_query($SQL_UpdateRelatedData) or die(mysql_error()."-".$e->getFile()."-".$e->getLine());
            
            //process other miscellaneous values
            $address_brgy=cls_user_get::ProfileValue('acct_no',$Ref_Data[$i],'address_brgy');
            $fullname=cls_user_get::ProfileValue('acct_no',$Ref_Data[$i],'applicant');

			while($row_UpdateRelatedData=mysql_fetch_array($QRY_UpdateRelatedData)){
				$payee=$Ref_Data[$i];
                $reading_date= $row_UpdateRelatedData['reading_date']; //get the reading date
                $DueDate=auxilliary::dagdag_date((string)$reading_date,'P22D'); //compute for due dates
				$bill_amnt=$row_UpdateRelatedData['bill_amnt']; //get the bill amount
                //compute for the penalty right away
                if(date('Y-m-d') > $DueDate){$penalty=$bill_amnt * 0.05;}else{$penalty=0.00;}
                
                //process other bill payment addons
                $loans_MLP=$row_UpdateRelatedData['loans_MLP'];
				$loans_MF=$row_UpdateRelatedData['loans_MF'];
                $misc_fee=$row_UpdateRelatedData['misc_fee']; 
                $OtherPayments=$pen_fee + $loans_MLP + $loans_MF;
				
                //$total=$row_UpdateRelatedData['total'];
                $total=$bill_amnt + $penalty + $OtherPayments;
            }
                //check reading date and accnt no on consolidated table
                //todo: FFU#79
                $SQL_Check="select * from {$TableName} where accntno='{$payee}' and reading_date='{$reading_date}'";
                $e=new Exception();
                $QRY_Check=mysql_query($SQL_Check) or die(mysql_error()."___File: ".$e->getFile()."__Line: ".$e->getLine());
                if(mysql_numrows($QRY_Check)==0){ //same data entry does not exist
                //prepare the sql statement for data insertion
            	$SQL_InsUpdateRelatedData="insert into {$TableName}(accntno,name,reading_date,other_payments,bill_amnt,due_date,penalty_amnt,total_receivables)
                values('{$payee}','{$fullname}','{$reading_date}','{$OtherPayments}','{$bill_amnt}','{$DueDate}','{$penalty}','{$total}')";
				$QRY_InsUpdateRelatedData=mysql_query($SQL_InsUpdateRelatedData) or die("Error:".mysql_error()."__Source File:".$e->getFile()."__Line:".$e->getLine());
			    }
                        
		}
	}
} */
	public function CreateTable($str_TableName,$RefFile){ //create table as specified by the user
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$date=cls_misc::sanitize_hyp(date('Y-m-d'));
		$TableName="{$str_TableName}_{$date}"; //create the temporary name
		$e=new Exception();
		$sql="CREATE TABLE if not exists ".$TableName." LIKE {$RefFile}";
		mysql_query($sql) or die(mysql_error().$e->getFile()."-".$e->getLine()); //table create
		mysql_query("truncate {$TableName}") or die("error:".mysql_error()."File:".$e->getFile()."__line:".$e->getLine());
		// -p 10 -o mike -c For Upload: [start]Code updates for Recievables and data preparation for collection updates
		$sql="select * from codes where category='Recievables' and descr='{$TableName}'";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line".$e->getLine());
		if(mysql_numrows($qry)==0){
			$sql="insert into codes(code,descr,category)values('{$TableName}','{$TableName}','Recievables')";
			$E=NEW ErrorException();
			$qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
		}
		//-p 10 -o mike -c For Upload: [end]Code updates for Recievables and data preparation for collection updates
		return $TableName; //spit out the name of the table
	}
	
	public function ShowCurrentTableNameCreated(){
		$date=cls_misc::sanitize_hyp(date('Y-m-d'));
		$str_TableName='Recievables';
		$TableName="{$str_TableName}_{$date}"; 
		return $TableName;		
	}
	
}

?>