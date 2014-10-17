<?php 
include_once 'juban_functions.php';
//[start] stage for commit to remote PS (code status: uploaded) 2010-10-09
include_once 'cls_codes.php';
//[end] stage for commit to remote PS
?>
<?php class listings_type {
	public function general(){?>
						<h2 class="art-postheader">
							<center>Please select your filter</center>   
						
							<form method="POST" action="ledger_content.php">
								<?php 
									forms_handlers::form_types("text","accountno","wara");                			
								?>
								<input type="submit" name="submit" value="SHOW LEDGER"></input>
							</form>
						 </h2><?php 
		
}

	//[start] stage for commit to remote PS(code status:done 2010-10-04)(update the whole function to remote PS instead)
	public function receivables2($barangay_code,$month,$year=null,$cash_flow=null){ //$cash_flow(0;null=receivables,1=receipts) //FFu#51
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
		include_once 'cls_bill.php';
        //initialize array variable
		//initialize year
		if($year==null){$year=date('Y');}
		//initialize month
		if($month==null){$month=date('m');}
			$criteria=$year."-".$month."-".cls_user_get::tblDate_Sched_Value('bryg_codes',$barangay_code,'date_meter_reading');    
		//initialize sql statement to retrieve all concessionairs accounts
		//results:array of accounts,keymap=none, iterate on values only
		if($barangay_code=='general'){
			$accounts=cls_user_get::all_concessionaires_account_no();
		}elseif($barangay_code!='general'){
			$accounts=cls_user_get::all_concessionaires_account_no($barangay_code);
		}

		$str_month=cls_misc::toString($month,'month');
		$str_barangay=cls_misc::toString($barangay_code,'Barangay');
		//establish output title 
		if($cash_flow=='' || $cash_flow==null){ //receivables
			echo "<center><strong><font size=3>Account Receivables for the Month of {$str_month}-{$year} for Barangay {$str_barangay}</font></strong></center><br>";
            $ReportsTitle="Account Receivables for the Month of {$str_month}-{$year} for Barangay {$str_barangay}";
		}elseif($cash_flow=='1'){ //cash receipts
			echo "<center><strong><font size=3>Cash Receipts for the Month of {$str_month}-{$year} for Barangay {$str_barangay}</font></strong></center><br>";
            $ReportsTitle="Cash Receipts for the Month of {$str_month}-{$year} for Barangay {$str_barangay}";
		}
		?><center> <table width='auto' bgcolor="white"><?php
		//establish table headers
		if($cash_flow=='' || $cash_flow==null){
		echo "<tr><td><strong><center>Count</center></strong></td> <td><strong><center>Account Number</center></strong></td> <td><strong><center>Name</center></strong></td> <td><strong><center>Receivables</center></strong></td> <td><strong><center>Due Date</center></strong></td> <td><strong><center>With Penalty</center></strong></td> <td><strong><center>Receivables</center></strong></td></tr>";
		}elseif($cash_flow=='1'){
		echo "<tr>
				<td><strong><center>Count</center></strong></td> 
				<td><strong><center>Account Number</center></strong></td> 
				<td><strong><center>Name</center></strong></td> 
				<td><strong><center>Receipts</center></strong></td>
				<td><strong><center>OR Date</center></strong></td> 
				<td><strong><center>OR Number</center></strong></td> 
				</tr>";
		}
		//start iterating to all values on accounts retrieve
        $criteria=$year."-".$month."-%";
		for($i=0;$i < count($accounts);$i++){
			//re-create ledger table
			$LedgerTable=cls_misc::ConvertToTableName($accounts[$i]);
			$AccountName=cls_user_get::ProfileValue('acct_no',trim($accounts[$i]),'applicant');
            if($cash_flow==null || $cash_flow==''){ //user request for receivables
                $SQL_receivables="SELECT * FROM {$LedgerTable} WHERE (reading_date like '{$criteria}' and OR_num is null and OR_date is null) or (reading_date like '{$criteria}' and OR_num='' and OR_date='')";
            }elseif($cash_flow=='1'){ //user request for cash receipts
                $SQL_receivables="SELECT OR_num,OR_date,sum(total)as total FROM {$LedgerTable} WHERE OR_date like '{$criteria}' and (OR_num is not null or OR_num <> '' or OR_num <> '00000000' ) group by OR_num";
                //$SQL_receivables="SELECT sum(pen_fee) as pen_fee,OR_num,OR_date,sum(bill_amnt) as bill_amnt,sum(total) as total,sum(loans_MLP) as loans_MLP,sum(loans_MF) as loans_MF,sum(misc) as misc_fee FROM {$LedgerTable} where (OR_date like and OR_date <> '' and OR_date <> '0000-00-00') or (OR_num <> '' and OR_num is not null and OR_num <> '00000000') group by OR_num order by OR_num asc";
            }
			
            /*if($barangay_code!='general'){ //selection for barangay passed
				if($cash_flow=='1'){ //user request for cash receipts only
					$criteria=$year."-".$month."-%";
					$SQL_receivables="SELECT * FROM {$LedgerTable} WHERE OR_date like '{$criteria}' group by OR_num";
				}
				elseif($cash_flow==null||$cash_flow==''){ //receivables selected as per user request for reporting
					$SQL_receivables="SELECT * FROM {$LedgerTable} WHERE (reading_date='{$criteria}' and OR_num is null and OR_date is null) or (reading_date='{$criteria}' and OR_num='' and OR_date='')"; }
			}

			else{ //barangay_code is not present
				if($cash_flow=='1'){ //action request is receipts only
					$criteria=$year."-".$month."-%";
					$SQL_receivables="SELECT * FROM {$LedgerTable} WHERE OR_date like '{$criteria}' group by OR_num";
				}elseif($cash_flow==null||$cash_flow==''){ //receivables selected as per user request for reporting
					$SQL_receivables="SELECT * FROM {$LedgerTable} WHERE (OR_num is null or OR_date is null) and (OR_num='' or OR_date='')";
				}
			}*/
            
			$QRY_receivables=mysql_query($SQL_receivables) or die(mysql_error());
			if(mysql_numrows($QRY_receivables)>0){ //records found, start iterating and printing results
				while($ROW_receivables=mysql_fetch_array($QRY_receivables)){
                    //initiate array variable
					$count++;
					if($count % 2==0){
						?><tr bgcolor="#AAAAAA"><?php
					}else{
						echo "<tr>";
					}
					//due to manual update of the ledger, the total amount is being neglected by the end users
					//to avoid such zero values on reports, conditions had been sets off

                    //for receivables only
                    $receivables=$ROW_receivables['bill_amnt'];
                    
					$penalty_date=cls_bill_get::payment_ext(cls_bill_get::payment_date($ROW_receivables['reading_date'],$accounts[$i]));
					if(date('Y-m-d') > $penalty_date){$penalty_amount= $receivables * 0.05;}
					//$OtherPayments=$ROW_receivables['loans_MLP'] + $ROW_receivables['loans_MF'] + $ROW_receivables['misc'] + $ROW_receivables['AF'];
                    $Total_Receivables=$penalty_amount + $receivables;
					$print_Total_Receivables=cls_misc::gFormatNumber($Total_Receivables);
					$Gross_Receivables=$Gross_Receivables + $Total_Receivables;
                    
                    //for receipts only
                    $items=$ROW_receivables['total'];// "'pen_fee','bill_amnt',sum(total) as total,sum(loans_MLP) as loans_MLP,sum(loans_MF) as loans_MF,sum(misc) as misc_fee";
					$Gross_Receipts=$Gross_Receipts + $items;
                    
					if($cash_flow==''||$cash_flow==null){
						echo "<td><center>{$count}</center></td><td><center>{$accounts[$i]}</center></td><td>{$AccountName}</td><td>".cls_misc::gFormatNumber($receivables)."</td> <td><center>{$penalty_date}</center></td> <td><center>".cls_misc::gFormatNumber($penalty_amount)."</center></td> <td><center>{$print_Total_Receivables}</center></td></tr>";
                        //initiate array for data export to excel for cash receivables
                        $arrData[]=array('count'=>$count,'AccountNo'=>$accounts[$i],'AccountName'=>$AccountName,'Receivables'=>$receivables,'PenaltyDate'=>$penalty_date,'PenaltyAmount'=>$penalty_amount,'PrintTotalReceivables'=>str_replace(',','',$print_Total_Receivables));
                        $ReportType=7;                   
					}elseif($cash_flow=='1'){ //cash receipts
						$cash_receipt=$ROW_receivables['total'];
						$or_date=$ROW_receivables['OR_date'];
						$or_num=$ROW_receivables['OR_num'];
						echo "<td><center>{$count}</center></td><td><center>{$accounts[$i]}</center></td><td>{$AccountName}</td><td>".cls_misc::gFormatNumber($cash_receipt)."</td>  <td><center>{$or_date}</center></td> <td><center>{$or_num}</center></td></tr>";
                        //initiate array for data to excel for cash receipts
                        $arrData[]=array('Count'=>$count,'AccountNo'=>$accounts[$i],'AccountName'=>$AccountName,'CashReceipts'=>$cash_receipt,'OrDate'=>$or_date,'OrNum'=>$or_num);
                        $ReportType=6;
					}
				}
			}
    
		}
		if($cash_flow=='' || $cash_flow==null){
			echo "<tr><td colspan='7'>Gross Receivables=".cls_misc::gFormatNumber($Gross_Receivables)."</td></tr></table></center>";
            $GrossAmount="Gross Receivables=".cls_misc::gFormatNumber($Gross_Receivables);
		}elseif($cash_flow=='1'){
			echo "<tr><td colspan='7'>Gross Cash Receipt=".cls_misc::gFormatNumber($Gross_Receipts)."</td></tr></table></center>";
            $GrossAmount="Gross Cash Receipt=".cls_misc::gFormatNumber($Gross_Receipts);
		}
	        //insert here process for data export to excel
            listings_type::DumpRenderResultsToExcel($arrData,count($arrData),$ReportsTitle,$GrossAmount,$ReportType);
    }
    
	//[end] stage for commit to remote PS
	public function receivables($value_criteria,$taon,$bulan){  //brangay code, year, month   will return page of lists names
		listings_type::receivables2($value_criteria,$bulan,$taon);
		}
//[end] stage for commit to remote PS
	public function cash_received($value_criteria,$taon,$bulan){  //barangay code/year,month will return a page of lists
			//GET NAME
				$cap_address = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='".$value_criteria."' "));
				$cap_bulan = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='".$bulan."' && category='month'"));
					switch ($value_criteria){
						case 'general':
							$quer = sql_retrieve::request_rows("*","or_log"," extract(month from or_log.or_date) = '$bulan' && extract(year from or_log.or_date)='$taon'");		
							$count  = 0;?> <table align="center" class="listings">
							<caption style="font-size:15pt;">CASH RECEIVED AS OF <?php echo strtoupper($cap_bulan['descr'])." ".$taon;?> </caption>
							<tr><th>COUNT</th><th>ACCOUNT NUMBER</th><th>NAME</th><th>CASH RECEIVED</th><TH>OR NUMBER</TH></tr><?php 
							while ($row=mysql_fetch_assoc($quer)){
									$quer2 = sql_retrieve::request_rows("applicant","profile","acct_no='".$row['issued_to_accnt']."'");
									$row2 = mysql_fetch_assoc($quer2);
									$count++;		
							 if (($count % 2<>0)){?>
								<tr class="odd"><?php }
							else{?><tr><?php }?>
							<td><?php echo $count?></td><td><?php echo $row['issued_to_accnt'];?></td><td><?php echo $row2['applicant'];?></td><td><?php echo $row['issued_amnt'];?></td><td><?php echo $row['or_number'];?></td></tr>					
							<?php 		  
							}?></table>
							  <center><a href="download.php?action=download_receipt&brgy=<?php echo $value_criteria;?>&year=<?php echo $taon;?>&month=<?php echo $bulan;?>"><input type="button" value="Download" class="art-button" ></input></a></center>
							 <?php 
							break;	
						default:
							$query = sql_retrieve::request_rows("*","profile","address_brgy='$value_criteria' order by acct_no asc");
							$count = 0;
							?><table align="center" class="listings">
								<caption style="font-size:15pt;">CASH RECEIVED FROM <?php echo strtoupper($cap_address['descr']);?> AS OF <?php echo strtoupper($cap_bulan['descr'])." ".$taon;?> </caption>
								<tr><th>COUNT</th><th>ACCOUNT NUMBER</th><th>NAME</th><th>CASH RECEIVED</th><th>OR NUMBER</th></tr><?php 
							while($row = mysql_fetch_assoc($query)){
								$quer = sql_retrieve::request_rows("*","or_log"," extract(month from or_date) = '$bulan' && extract(year from or_date)='$taon' && issued_to_accnt='".$row['acct_no']."'");
								//SELECT * FROM table1.ldg_2005_05_0299 l where extract(month from reading_date) = '12' && extract(year from reading_date)='2011'							
								$row_2 = mysql_fetch_assoc($quer);
								$count++;
								if (($count % 2<>0)){?>
								<tr class="odd"><?php }
							else{?><tr><?php }?>
								<td><?php echo $count?></td><td><?php echo $row['acct_no'];?></td><td><?php echo $row['applicant'];?></td><td><?php echo $row_2['issued_amnt'];?></td><td><?php echo $row_2['or_number'];?></td></tr>										
							<?php }
								?></table>
								 <center><a href="download.php?action=download_receipt&brgy=<?php echo $value_criteria;?>&year=<?php echo $taon;?>&month=<?php echo $bulan;?>"><input type="button" value="Download" class="art-button" ></input></a></center>
								 <?php 
							break;					
					}	
}

public function billings($acct,$bulan,$taon){
					?><script type="text/javascript">alert('<?php echo $acct;?>')</script>
						<script type="text/javascript">alert('<?php echo "present".$bulan;?>')</script>
						<script type="text/javascript">alert('<?php ;?>')</script>
						<?php 
					$quer_1 = sql_retrieve::request_rows("*","profile","acct_no='$acct'");
					$row_1 = mysql_fetch_assoc($quer_1); 
					$acct_replace = str_replace("-","_",$acct);
					$quer_present = sql_retrieve::request_rows("*","ldg_".$acct_replace," extract(month from reading_date) = '$bulan' && extract(year from reading_date)='$taon'");	
					$row_present = mysql_fetch_assoc($quer_present);
						$bulan_prev = sprintf("%02s",($bulan-1));
					$quer_previous = sql_retrieve::request_rows("*","ldg_".$acct_replace," extract(month from reading_date) = '$bulan_prev' && extract(year from reading_date)='$taon'");	
					$row_previous = mysql_fetch_assoc($quer_previous);
					?>
					<table class="header" border="0" align="center">
						<tr><td>Republic of the Philippines</td></tr>
						<tr><td>MUNICIPALITY OF JUBAN</td></tr>
						<tr><td>SORSOGON</td></tr>
						<tr><td></td></tr>
						<tr><td>JUBAN WATER SYSTEM</td></tr>
						<tr><td>WATER BILL</td></tr>
					</table>
						<p>	</p>
						<p></p>
					<table>
						<tr><td>PRESENT</td><TD>PREVIOUS</TD><td>CU.M. USED</td><TD>AMOUNT</TD></tr>
						<tr><td><?php echo $row_present['meter_reading'];?></td><td><?php echo $row_previous['meter_reading'];?></td>
						<td><?php echo $row_present['cu_used'];?></td><td><?php echo $row_present['total'];?></td></tr>
					</table>
						<p>	</p>
						<p></p>
					<table>
						<tr><td>METER FEE</td><td><? echo "0"; ?></td></tr>
							<tr><td>PENALTY</td><td><?php echo "0";?></td></tr>
								<tr><td>MATERIAL LOAN PROGRAM</td><td><?php echo "0";?></td></tr>
									<tr><td>INTEREST</td><td><?php echo "0";?></td></tr>
										<tr><td>RECONNECTION FEE</td><td><?php echo "0";?></td></tr>
					</table>					
					<table>
											<tr><td>AMOUNT DUE</td><td><?php echo $row_present['total'];?></td></tr>
											<tr><td>For the Month of</td><td><?php echo date_format(date_create($taon."-".$bulan),"F");?></td></tr>
											
											<tr><td>Account Number</td><td><?php echo $row_1['acct_no'];?></td></tr>
											<tr><td>Serial Number</td><td><?php echo $row_1['meter_no'];?></td></tr>
											
											<tr><td>Name</td><td><?php echo $row_1['applicant'];?></td></tr>
											<?php $barangay = sql_retrieve::request_rows("descr","codes","code='".$row_1['address_brgy']."'");	
												$barangay = mysql_fetch_assoc($barangay);?>
											<tr><td>Address</td><td><?php echo $barangay['descr'];?></td></tr>
											
											<tr><td>Due Date</td><td><?php "ambot";?></td></tr>
											
											<tr><td>Prepared by</td><td><?php "ambot";?></td></tr>
											
											<tr><td>Date</td><td></td></tr>
					</table>					
					
					<?php 	
}
public function billings_excel(){

		error_reporting(E_ALL);
		date_default_timezone_set('Europe/London');
		/** PHPExcel */
		require_once '../Classes/PHPExcel.php';
		$objReader = new PHPExcel_Reader_Excel5();
		$objPHPExcel = $objReader->load("attachments/waterbill.xls");
					$objPHPExcel->getActiveSheet()->setCellValue('B10',"'".$present."'");
					$objPHPExcel->getActiveSheet()->setCellValue('C10',"'".$previous."'");
					$objPHPExcel->getActiveSheet()->setCellValue('D10',"'".$cu_used."'");
					$objPHPExcel->getActiveSheet()->setCellValue('E10',"'".$amount."'");
					
					$objPHPExcel->getActiveSheet()->setCellValue('D20',"'".$adlaw."'");
					$objPHPExcel->getActiveSheet()->setCellValue('D22',"'".$acct_no."'");
					$objPHPExcel->getActiveSheet()->setCellValue('D23',"'".$serial."'");
					$objPHPExcel->getActiveSheet()->setCellValue('C25',"'".$applicant."'");
					$objPHPExcel->getActiveSheet()->setCellValue('C26',"'".$address."'");
					$objPHPExcel->getActiveSheet()->setCellValue('D28',"'".$due_date."'");
					$objPHPExcel->getActiveSheet()->setCellValue('D30',"'".$prepared_by."'");
					$objPHPExcel->getActiveSheet()->setCellValue('D33',"'".$prepared_date."'");

		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save("attachments/bago.xls");
		$objPHPExcel->disconnectWorksheets();
		unset($objPHPExcel);
}
public function receivables_excel($value_criteria,$taon,$bulan){
#@PARAM $value criteria as brg code or "general", $taon as yyyy, $bulan as mm
#return .xls file loaded to dir:receivables filename: barangay_MM_YYYY;

   #@("Relan") 
		#clean the headrs sent and force the output   
		  if (ob_get_contents()!='') {
				ob_clean();
		   }   
/** PHPExcel */
		error_reporting(E_ALL);
		date_default_timezone_set('Europe/London');	
		require_once '../Classes/PHPExcel.php';
		$objReader = new PHPExcel_Reader_Excel5();
		$objPHPExcel = $objReader->load("attachments/collectibles.xls");
		ini_set("memory_limit","-1");
		/* EXCEL DECLARATION ends HERE */
		
				switch ($value_criteria){				
					case 'general':			
						$query = sql_retrieve::request_rows("*","profile order by acct_no asc","none");
						$count = 0;//TO DECLARE
						$sum = 0;
						$excel_column = 13; //EXCEL start column index
					/* SQL RETRIEVAL AND ITERATION OF ROWS IN EXCEL COMES HERE */
						while($row = mysql_fetch_assoc($query)){
							$quer = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$row['acct_no'])," extract(month from reading_date) = '$bulan' && extract(year from reading_date)='$taon' && OR_num IS NULL");
							//SELECT * FROM table1.ldg_2005_05_0299 l where extract(month from reading_date) = '12' && extract(year from reading_date)='2011'							
							$row_2 = mysql_fetch_assoc($quer);
							$count++;
							$sum = $sum + $row_2['total'];
							/*EXCEL OBJECT COMES HERE */
							set_time_limit(20);
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$excel_column,$count);
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$excel_column,$row['acct_no']);
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$excel_column,$row['applicant']);
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$excel_column,$row_2['total']);
								 $excel_column++;	
											
						}
							/*DATE, BARANGAY, TOTAL # OF ACCOUNTS, TOTAL # OF CLIENTS START HERE */
								$bulan_excel = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='$bulan' and category='month'"));
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,6,'GENERAL');//BARANGAY
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,7,$bulan_excel['descr']." ".$taon);//BULAN AND TAON
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,10,$count);//# OF ACCOUNTS
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,10,$sum);//SUM						 	
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="receivables_'.date('Y-m-d').".xls");
						header('Cache-Control: max-age=0');	
						$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
						$objWriter->save('php://output'); 
						$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
						$objWriter->save('receivables/receivables_'.$address_excel['descr'].'_'.$bulan.'_'.$taon.'.xls');							
						break;	 
					default:
						 #this force dowload the excel file to the browser
						$query = sql_retrieve::request_rows("*","profile","address_brgy='$value_criteria' order by acct_no asc");
						$count = 0; // tO DECLARE ONLY
						$excel_column = 13; // TO DECLARE ONLY
						$sum = 0;// TO DECLARE ONLY
						/* START OF SQL RETRIEVAL AND EXCCEL GENERATION*/
						$storage = array(); //creates a temporary storage

						while($row = mysql_fetch_assoc($query)){
							$quer = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$row['acct_no'])," extract(month from reading_date) = '$bulan' && extract(year from reading_date)='$taon' && OR_num IS NULL");
							$row_2 = mysql_fetch_assoc($quer);
							$count++;
							$sum = $sum + $row_2['total'];
							/*EXCEL OBJECT COMES HERE*/
							array_push($storage,array('count'=>$count,'acct_no'=>$row['acct_no'],'applicant'=>$row['applicant'],'total'=>$row_2['total']));									
						 }	
						 foreach($storage as $lalagyan){
							 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$excel_column,$lalagyan['count']);
							 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$excel_column,$lalagyan['acct_no']);
							  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$excel_column,$lalagyan['applicant']);
							  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$excel_column,$lalagyan['total']);                            
							 $excel_column++;     
						 }					
							/*DATE, BARANGAY, TOTAL # OF ACCOUNTS, TOTAL # OF CLIENTS START HERE */
								$address_excel = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='$value_criteria'"));
								$bulan_excel = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='$bulan' and category='month'"));
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,6,"Barangay"." ".$address_excel['descr']);//BARANGAY
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,7,$bulan_excel['descr']." ".$taon);//BULAN AND TAON						
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,10,$count);//# OF ACCOUNTS
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,10,$sum);//SUM
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="receivables_'.date('Y-m-d').".xls");
						header('Cache-Control: max-age=0');	
						$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
						$objWriter->save('php://output'); 
						$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
						$objWriter->save('receivables/receivables'.$address_excel['descr'].'_'.$bulan.'_'.$taon.'.xls');
						break;	
				}			/* terminates excel, save file .xls, unload instance of excel to memory */							
						
						$objPHPExcel->disconnectWorksheets();
						unset($objPHPExcel);
						 ob_end_flush();
						
}
public function cash_received_excel($value_criteria,$taon,$bulan){
#@PARAM $value criteria as brg code or "general", $taon as yyyy, $bulan as mm
#return .xls file loaded to dir:receipts filename: barangay_MM_YYYY;
/** PHPExcel */
		date_default_timezone_set('Europe/London');
		/** PHPExcel */
		require_once '../Classes/PHPExcel.php';
		$objReader = new PHPExcel_Reader_Excel5();
		$objPHPExcel = $objReader->load("attachments/collection.xls");
		ini_set("memory_limit","-1");
			/* EXCEL DECLARATION ends HERE */
		
					switch ($value_criteria){
						case 'general':
							$quer = sql_retrieve::request_rows("*","or_log"," extract(month from or_log.or_date) = '$bulan' && extract(year from or_log.or_date)='$taon'");		
							$count  = 0;//to declare
							$sum=0;//to declare
							$excel_column = 13; //excel column index
							/* generation of excel file from dbase*/
							$storage = array(); //declare storage of result from database
							while ($row=mysql_fetch_assoc($quer)){
									$quer2 = sql_retrieve::request_rows("applicant","profile","acct_no='".$row['issued_to_accnt']."'");
									$row2 = mysql_fetch_assoc($quer2);
									$count++;	
									$sum = $sum + $row['issued_amnt'];
										
									/* EXCEL OBJECT COMES HERE*/
									array_push($storage,array('count'=>$count,'issued_to_accnt'=>$row['issued_to_accnt'],'applicant'=>$row2['applicant'],'issued_amnt'=>$row['issued_amnt'],'or_number'=>$row['or_number']));
									
							}	
							foreach($storage as $lalagyan){
							   set_time_limit(20);                                
							   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$excel_column,$lalagyan['count']);
							   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$excel_column,$lalagyan['issued_to_accnt']);
							   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$excel_column,$lalagyan['applicant']);
							   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$excel_column,$lalagyan['issued_amnt']);
							   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$excel_column,$lalagyan['or_number']);
							   $excel_column++;    
							}
									/*DATE, BARANGAY, TOTAL # OF ACCOUNTS, TOTAL # OF CLIENTS START HERE */													
									$bulan_excel = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='$bulan' and category='month'"));
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,6,'GENERAL');//BARANGAY
									 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,7,$bulan_excel['descr']." ".$taon);//BULAN AND TAON
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,10,$count);//# OF ACCOUNTS
									 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,10,$sum);//SUM
							#start saving of values and then force download to browser
							header('Content-Type: application/vnd.ms-excel');
							header('Content-Disposition: attachment;filename="receipts_'.date('Y-m-d').".xls");
							header('Cache-Control: max-age=0');    
							$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
							$objWriter->save('php://output'); 
							$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
							$objWriter->save('receipts/receipts_'.$address_excel['descr'].'_'.$bulan.'_'.$taon.'.xls');    
						break;    
							
						default:
							$query = sql_retrieve::request_rows("*","profile","address_brgy='$value_criteria' order by acct_no asc");
							$count = 0;// to declare
							$sum = 0; //to declare
							$excel_column = 13; //excel column index
						/* generates excel from dbase */
						$storage = array(); //this will get all of the values from thre result set
							while($row = mysql_fetch_assoc($query)){
								$quer = sql_retrieve::request_rows("*","or_log"," extract(month from or_date) = '$bulan' && extract(year from or_date)='$taon' && issued_to_accnt='".$row['acct_no']."'" );
								//SELECT * FROM table1.ldg_2005_05_0299 l where extract(month from reading_date) = '12' && extract(year from reading_date)='2011'							
								$row_2 = mysql_fetch_assoc($quer);
								$count++;
								array_push($storage,array('count'=>$count,'acct_no'=>$row['acct_no'],'applicant'=>$row['applicant'],'issued_amnt'=>$row_2['issued_amnt'],'or_number'=>$row_2['or_number']));
								$sum = $sum + $row_2['issued_amnt'];
							}   
								/* excel object starts here */
								foreach($storage as $lalagyan){                                 
									 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$excel_column,$lalagyan['count']);
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$excel_column,$lalagyan['acct_no']);
									 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$excel_column,$lalagyan['applicant']);
									 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$excel_column,$lalagyan['issued_amnt']);
									 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$excel_column,$lalagyan['or_number']);
									 $excel_column++;
								}
									
					
														
								/*DATE, BARANGAY, TOTAL # OF ACCOUNTS, TOTAL # OF CLIENTS START HERE */
								$address_excel = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='$value_criteria'"));
								$bulan_excel = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='$bulan' and category='month'"));
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,6,"Barangay"." ".$address_excel['descr']);//BARANGAY
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,7,$bulan_excel['descr']." ".$taon);//BULAN AND TAON						
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,10,$count);//# OF ACCOUNTS
								 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,10,$sum);//SUM
								 
								 #start saving of values and then force download to browser
								header('Content-Type: application/vnd.ms-excel');
								header('Content-Disposition: attachment;filename="receipts_'.date('Y-m-d').".xls");
								header('Cache-Control: max-age=0');    
								$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
								$objWriter->save('php://output'); 
							   
								$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
								$objWriter->save('receipts/receipts_'.$address_excel['descr'].'_'.$bulan.'_'.$taon.'.xls');   
							break;					
					}	
					/* terminates excel, save file .xls, unload instance of excel to memory */
					
					$objPHPExcel->disconnectWorksheets();
					unset($objPHPExcel);

}
public function listings($start,$value_criteria,$year,$month,$descr){		
$month = str_pad($month,2,"0",STR_PAD_LEFT);	//ensure 2digit padded month
/* study above*/
	//include 'connection.php';//helps errorcode retrieveal
	//$page=$start;//shows in the value of selected
	//$main_count = ceil(mysql_num_rows(sql_retrieve::request_rows("*","profile","address_brgy='$value_criteria' order by acct_no asc"))/20);//used in getting number of pages
	//above shows the number of pages generated
	//below if checks the starting point of the list
	if ($start==0 || $start==1){
		$start = 0;
		$tracker=0; //used in showing the count in tables
		$page=0;//displays the page
	}else{
		$start = (($start-1)*20);
		$tracker=$start; //used in showing the count in tables
		$page = ($tracker/20)+1; //displays the page
	}	
	
		//inner if checks if "*" was chosen
	if ($value_criteria =="general"){
		$query = sql_retrieve::request_rows("acct_no,applicant,or_log.issued_amnt","table1.profile inner join table1.or_log on profile.acct_no = or_log.issued_to_accnt where acct_no in(select or_log.issued_to_accnt from table1.or_log where or_date like '".$year."_".$month."%') order by acct_no asc limit $start,20","none");
		$main_count = ceil(mysql_num_rows(sql_retrieve::request_rows("acct_no,applicant,or_log.issued_amnt","table1.profile inner join table1.or_log on profile.acct_no = or_log.issued_to_accnt where acct_no in(select or_log.issued_to_accnt from table1.or_log where or_date like '".$year."_".$month."%') order by acct_no asc","none"))/20);
	}else{
		$query = sql_retrieve::request_rows("acct_no,applicant,or_log.issued_amnt","table1.profile inner join table1.or_log on profile.acct_no = or_log.issued_to_accnt where acct_no in(select or_log.issued_to_accnt from table1.or_log where or_date like '".$year."_".$month."%') and address_brgy = '".$value_criteria."' order by acct_no asc limit $start,20","none");
		$main_count = ceil(mysql_num_rows(sql_retrieve::request_rows("acct_no,applicant,or_log.issued_amnt","table1.profile inner join table1.or_log on profile.acct_no = or_log.issued_to_accnt where acct_no in(select or_log.issued_to_accnt from table1.or_log where or_date like '".$year."_".$month."%') and address_brgy = '".$value_criteria."' order by acct_no asc","none"))/20);
		}
		//$count=5;	
		//$count=$start;
	?>
	 <table class="listings">
		<?php if ($value_criteria <> "general"){?>
					<caption style="font-size:15pt">LIST OF CLIENTS IN BARANGAY <?php ECHO strtoupper($descr);}
			  else{	
					?><caption style="font-size:15pt">LIST OF CLIENTS IN ALL BARANGAY <?php 
				}?>
	<?php if (!isset($page) || $page==0 || $page==1){	
			}
		   else{
				echo "<p style='font-size:12pt'>"."Page: ".$page. " of ".$main_count."</p>";} ?>
		</caption>
		<tr><th>Count<th>Name</th><th>Account Number</th><th>Ledger Status</th></tr> <?php 
			while ($list = mysql_fetch_assoc($query)){
								$tracker++;//used in displaying count of list in the table
								//below if is used in styles only
								if (($tracker % 2<>0)){?>
									<tr class="odd"><?php }
								else{
									?><tr><?php }?>	
							<!-- echo data -->	
							<td style="text-align:center"><?php echo $tracker;?></td>
							<td><?php echo $list['acct_no'];?></td>
							<td><?php echo $list['applicant'];?></td>
							<td><?php echo $list['issued_amnt'];?></td>												
			<?php //end of while
					}?></table><?php 
	/*	$sql_da = sql_retrieve::request_rows("*","or_log","id>0");
		while ($mama = mysql_fetch_assoc($sql_da)){
				$sql_str = "update table1.or_log set or_log.or_date = (SELECT DATE_FORMAT('".$mama['or_date']."', '%Y-%m-%d')) where or_date='".$mama['or_date']."'";
				$sql_query = mysql_query($sql_str);
			}
	*/		
	?>
	<div class="pagination"><center>
	<form name="pagination" method="POST" action="listings_content.php">Page: 
		<select id = "start" name="start" onchange="send_page()">
		<?php for($i=1;$i<=$main_count;$i++){?>
			<option><?php echo $i;?></option><?php }?>
		</select>  of  <?php echo $main_count;?> 
		<input type="hidden" name="barangay" value="<?php echo $value_criteria;?>"></input>
		<input type="hidden" name = "month" value="<?php echo $month;?>"></input>
		<input type="hidden" name="year" value="<?php echo $year;?>"></input>
		<input type="hidden" name = "type_show" value=""></input>
		<input type="hidden" name="descr" value="<?php echo $descr;?>"></input>
		<input type="button" class="art-button" value="SHOW" onclick="submitform()";></input>
		<input type="hidden" name="mga_post" value="SHOW"></input>
	</form>
	</center></div>
<?php 
} 
public function show_names($barangay,$descr,$start){
            //todo -p 15 -c FFU for listings [start] codes for updates
                include_once 'cls_codes.php';
                include_once 'cls_bill.php';
            //todo -p 15 -c FFU for listings [ends] codes for updates
            
			if ($start==0 || $start==1){
					$start = 0;
					$tracker=0; //used in showing the count in tables
					$page=0;//displays the page
			}else{
					$start = (($start-1)*20);
					$tracker=$start; //used in showing the count in tables
					$page = ($tracker/20)+1; //displays the page
			}	
			if ($barangay=="general"){
			$quer = sql_retrieve::request_rows("*","profile order by acct_no asc limit $start,20" ,"none");
			$main_count = ceil(mysql_num_rows(sql_retrieve::request_rows("*","profile order by acct_no asc" ,"none"))/20);
			}else{
			$quer = sql_retrieve::request_rows("*","profile where address_brgy='".$barangay."' order by acct_no asc limit $start,20" ,"none");
			$main_count = ceil(mysql_num_rows(sql_retrieve::request_rows("*","profile where address_brgy='".$barangay."' order by acct_no asc" ,"none"))/20);
			}
			?>
	 <table class="listings">
		<?php if ($barangay <> "general"){?>
                     <?php //todo -p 15 -c FFU for listings [start] codes for updates ?>
					<caption style="font-size:15pt">LIST OF CLIENTS IN BARANGAY <?php ECHO cls_misc::toString($barangay,'Barangay');}
                    //todo -p 15 -c FFU for listings [ends] codes for updates
			  else{	
					?><caption style="font-size:15pt">LIST OF CLIENTS IN ALL BARANGAY <?php 
				}?>
			  
	<?php if (!isset($page) || $page==0 || $page==1){	
			}
		   else{
				echo "<p style='font-size:12pt'>"."Page: ".$page. " of ".$main_count."</p>";} ?>
		</caption>
		<tr><th>Count<th>Account Number</th><th>Name</th><th>Date Installed</th></tr> <?php 
				while ($list = mysql_fetch_assoc($quer)){
						$tracker++;//used in displaying count of list in the table
								//below if is used in styles only
								if (($tracker % 2<>0)){?>
									<tr class="odd"><?php }
								else{
									?><tr><?php }?>	
							<!-- echo data -->	
							<td style="text-align:center"><?php echo $tracker;?></td>
							<td><?php echo $list['acct_no'];?></td>
							<td><?php echo $list['applicant'];?></td>
							<td><?php echo $list['date_installed'];?></td>												
			<?php //end of while
					}?></table>
			<div class="pagination">
	<center>
				<form name="pagination" method="POST" action="listings_content.php">Page: 
					<select id = "start" name="start" onchange="send_page()">
					<?php for($i=1;$i<=$main_count;$i++){?>
						<option><?php echo $i;?></option><?php }?>
					</select>  of  <?php echo $main_count;?> 
						<input type="hidden" name="barangay" value="<?php echo $barangay;?>"></input>
						<input type="hidden" name="descr" value="<?php echo $descr;?>"></input>
						<input type="hidden" name = "type_show" value="list_per_barangay"></input>
						<input type="button" class="art-button" value="SHOW" onclick="submitform()";></input>
						<input type="hidden" name="mga_post" value="SHOW"></input>
			</form>
            <br><br>
            
            <?php //FFU No. 7
                //todo -p 15 -c FFU for listings [start] codes for updates 
                if($barangay!='general'){
                    $sql="select * from profile where address_brgy='{$barangay}' order by applicant asc";
                    $e=new Exception();
                    $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
                    while($row=mysql_fetch_array($qry)){
                        $data[]=array('accnt_no'=>$row['acct_no'],'names'=>$row['applicant']);
                    }
                    $ReportsTitle="List of Barangay Concessionaires for ".cls_misc::toString($barangay,'Barangay');
                    cls_bill_set::ListofConcessionairesExportToExcel($data,$ReportsTitle,'ListofCons_template0.xls');
                }
                //todo -p 15 -c FFU for listings [start] codes for updates     
            ?>
	</center>
   
			</div><?php 
			
}
public function reporting($bulan,$taon){
						 
							$sum_one=0;
							$sum_two=0;
							 //get all barangays
							$query = sql_retrieve::request_rows('*',"codes where category='barangay'","none");                        
							$barangay=array();
							while($row=mysql_fetch_assoc($query)){
								 array_push($barangay,$row['code']);
							}   
							
							foreach($barangay as $value_criteria){                                                       
											  #cash received
											$query = mysql_fetch_assoc(mysql_query("SELECT sum(issued_amnt) as total FROM table1.or_log inner join table1.profile on profile.acct_no=or_log.issued_to_accnt where profile.address_brgy='$value_criteria' and extract(month from or_log.or_date) = '$bulan' and extract(year from or_log.or_date)='$taon'"));                            ;                                   
											$sum_one = $sum_one + $query['total']; 

											 #cash receivables
										   $query = sql_retrieve::request_rows("*","profile","address_brgy='$value_criteria' order by acct_no asc");
											 $sum_two     = 0;                         
											  /* START OF SQL RETRIEVAL AND EXCCEL GENERATION*/
											while($row = mysql_fetch_assoc($query)){
											  $quer = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$row['acct_no'])," extract(month from reading_date) = '$bulan' && extract(year from reading_date)='$taon' && OR_num IS NULL");                         
											   $row_2 = mysql_fetch_assoc($quer); 
												$sum_two = $sum_two + $row_2['total']; 
											  //  if($row_2['total']<>""){
											 //        echo $row['acct_no']."=".$row_2['total']."=".$value_criteria.""."<br>";
											  //  }
											   
											} 
											$tagalog_barangay = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes"," code='".$value_criteria."'"));       
											 #checks if availbe file exist
											$nagiisa = auxilliary::unique("reporting"," barangay ='".$tagalog_barangay['descr']."' and adlaw='".$taon."-".$bulan."'");
											 #insert to table
											if(!$nagiisa){
												 mysql_query("insert into reporting(adlaw,barangay,collectibles,collection)values('".$taon."-".$bulan."','".$tagalog_barangay['descr']."','".$sum_one."','".$sum_two."')") or die(mysql_error());   
											}else{
												 mysql_query("update reporting set collectibles='".$sum_two."',collection='".$sum_one."' where barangay='".$tagalog_barangay['descr']."' and adlaw='" .$taon."-".$bulan."'") or die(mysql_error());                    
											}                                           
							}
						 }
public function download_ledger($data){
/** PHPExcel */
		print
        ob_flush(); //memory cleanup
        date_default_timezone_set('Asia/Manila');
		/** PHPExcel */
		require_once '../Classes/PHPExcel.php';
		include_once 'classes/PHPExcel.php';
        include_once 'classes/PHPExcel/IOFactory.php';
        $objReader = new PHPExcel_Reader_Excel5();
		$objPHPExcel = $objReader->load("attachments/ledger.xls");
		set_time_limit(0);
        ini_set("memory_limit","-1");
        
			/* EXCEL DECLARATION ends HERE */
			
							#start insertion of profile to excel 
							$objPHPExcel->getActiveSheet()->setCellValue('C7', $data['acct_no']); //account no
							$objPHPExcel->getActiveSheet()->setCellValue('C8', $data['applicant']); //name
							$objPHPExcel->getActiveSheet()->setCellValue('C9', $data['address']); //address
							$objPHPExcel->getActiveSheet()->setCellValue('C10',$data['date_installed']); //date installed
							
							#next column
								$objPHPExcel->getActiveSheet()->setCellValue('J7', $data['serial_no']); //serial no
								$objPHPExcel->getActiveSheet()->setCellValue('J8', $data['brand']); //brand
								//$objPHPExcel->getActiveSheet()->setCellValue('J9', $data['terms']); //terms
								//$objPHPExcel->getActiveSheet()->setCellValue('J10', '');
							
							#new row, this is for the reading dates rows
								//define the start of row
								$i=14;
                                $rows_count=0;
								foreach($data as $ibutang){
									//$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $rows_count++); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $ibutang['reading_date']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $ibutang['meter_reading']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $ibutang['cu_used']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $ibutang['pen_fee']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $ibutang['bill_amnt']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $ibutang['loan_mlp']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $ibutang['loan_mf']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $ibutang['total']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $ibutang['or_no']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $ibutang['or_date']); //reading date
									$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $ibutang['remarks']); //reading date
									$i++;
								}
							
							ob_clean();
                            #start saving of values and then force download to browser
							header('Content-Type: application/vnd.ms-excel');
							header('Content-Disposition: attachment;filename="ledger_'.$data['acct_no'].".xls");
							header('Cache-Control: max-age=0');    
							$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
							$objWriter->save('php://output'); 
							  
	}
public function DumpRenderResultsToExcel($DataArray,$colCount,$ReportsTitle,$GrossAmount,$ReportType){ //FFu#50
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        include_once 'cls_user.php';
        include_once 'date_time.php';
        include_once 'classes/PHPExcel.php';
        include_once 'classes/PHPExcel/IOFactory.php';
        if(count($DataArray)==0){die();}
        $template_file='accnts/tpl_rec_rev.xls';
        
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
        //output excel files as filename will be based on reports title presented
        $file_output="accnts/".$ReportsTitle.".xls";
        $ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
        $ActiveSheet->setCellValue("A1",$ReportsTitle);
        
        $start_row=3; //initiate start row
        switch($ReportType){
            case '6': //cash receipts
                $ActiveSheet->mergeCells('A1:F1');
                //create column headers
                $ActiveSheet->setCellValue("A2","Count");
                $ActiveSheet->setCellValue("B2","Account No");
                $ActiveSheet->setCellValue("C2","Name");
                $ActiveSheet->setCellValue("D2","Receipts");
                $ActiveSheet->setCellValue("E2","OR Date");
                $ActiveSheet->setCellValue("F2","OR Number");
                
                //initiate data cells
                foreach($DataArray as $key => $value){
                    $ActiveSheet->setCellValue("A".$start_row,$value['Count']);
                    $ActiveSheet->setCellValue("B".$start_row,$value['AccountNo']);
                    $ActiveSheet->setCellValue("C".$start_row,$value['AccountName'] );
                    $ActiveSheet->setCellValue("D".$start_row,$value['CashReceipts']);
                    if($value['OrNum']=='N/A'){$out='-';}else{$out=$value['OrNum'];}
                    $ActiveSheet->setCellValue("E".$start_row,$value['OrDate']);
                    $ActiveSheet->setCellValue("F".$start_row,$out);
                    $start_row++;
                }
                $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
                $ActiveSheet->getColumnDimension('B')->setAutoSize(true);
                $ActiveSheet->getColumnDimension('C')->setAutoSize(true);
                $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
                $ActiveSheet->getColumnDimension('E')->setAutoSize(true);
                $ActiveSheet->getColumnDimension('F')->setAutoSize(true);
                break;
            
            case '7': //cash receivables
                $ActiveSheet->mergeCells('A1:G1');
                //create column headers
                $ActiveSheet->setCellValue("A2","Count");
                $ActiveSheet->setCellValue("B2","Account No");
                $ActiveSheet->setCellValue("C2","Name");
                $ActiveSheet->setCellValue("D2","Bill");
                $ActiveSheet->setCellValue("E2","Due Date");
                $ActiveSheet->setCellValue("F2","Penalty");    
                $ActiveSheet->setCellValue("G2","Total Receivables");

                //initiate data cells
                foreach($DataArray as $key => $value){
                    $ActiveSheet->setCellValue("A".$start_row,$value['count']);
                    $ActiveSheet->setCellValue("B".$start_row,$value['AccountNo']);
                    $ActiveSheet->setCellValue("C".$start_row,$value['AccountName']);
                    $ActiveSheet->setCellValue("D".$start_row,$value['Receivables']);
                    $ActiveSheet->setCellValue("E".$start_row,$value['PenaltyDate']);
                    $ActiveSheet->setCellValue("F".$start_row,$value['PenaltyAmount']);    
                    $ActiveSheet->setCellValue("G".$start_row,$value['PrintTotalReceivables']);
                    $start_row++;
                }
                    $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
                    $ActiveSheet->getColumnDimension('B')->setAutoSize(true);
                    $ActiveSheet->getColumnDimension('C')->setAutoSize(true);
                    $ActiveSheet->getColumnDimension('D')->setAutoSize(true);
                    $ActiveSheet->getColumnDimension('E')->setAutoSize(true);
                    $ActiveSheet->getColumnDimension('F')->setAutoSize(true);
                    $ActiveSheet->getColumnDimension('G')->setAutoSize(true);
                break;
        }
        
        
        //$start_row=$start_row +1;
        //$ActiveSheet->setCellValue("A".$start_row,"Total Collection for this Report=".cls_misc::gFormatNumber($SubTotal));
        //$ActiveSheet->getColumnDimension('A')->setAutoSize(true);
        //$ActiveSheet->getColumnDimension('D')->setAutoSize(true);
        ob_flush();
        //check if file exist
        if(file_exists($file_output)){unlink($file_output);}
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
        //create the download link for export
        include_once 'cls_awws.php';
        AWWS::CreateDownloadLink($file_output);
    } //function ends                  
}//class ends
?>