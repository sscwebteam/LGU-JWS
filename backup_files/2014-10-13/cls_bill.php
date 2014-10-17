<?php
//--------------getters
class cls_bill_get extends cls_bill_set {

    public function BillConsolidated($month=NULL,$barangay_code){ //consolidate all bill for specific barangay
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        include_once 'date_time.php';
        
        $AccntNos=cls_user_get::all_concessionaires_account_no($barangay_code);
        if(strlen($month)==1){$month='0'.$month;}else{$month=$month;}
        $Year=date('Y');$month=date('m');
        $crit="{$Year}-{$month}-%";
        $Template='tp_bill_cons';
        $TableName="bill_cons_{$Year}_{$month}_{$barangay_code}";
        //prepare table data for consolidation
            //check first
            if(self::chkBillConsol($TemplateName)==0){
                $sql="create table if not exists {$TableName} like {$Template}";
            }else{
                $sql="truncate table {$TableName}";
            }
             $e=new Exception();
             //execute statement for table consolidation
             mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."___Line: ".$e->getLine());
        
        for($i=0;$i < count($AccntNos);$i++){
            $LedgerTable=cls_misc::ConvertToTableName($AccntNos[$i]);
            if(cls_user_get::isPadLock($LedgerTable)==0){ //not PL or TPL
            
                $sql="select * from {$LedgerTable} where reading_date like '{$crit}'";
                $e=new Exception();
                $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."___Line: ".$e->getLine());
                if(mysql_numrows($qry)> 0){ //contains reading date based on criteria
                    //transfer to bill consolidation
                    $row=mysql_fetch_array($qry);
                    $accnt_nos=$AccntNos[$i];
                    $reading_date=$row['reading_date'];
                    $meter_reading=$row['meter_reading'];
                    $cu_used=$row['cu_used']; 
                    $bill_amnt=$row['bill_amnt']; 
                    $loans_MLP=$row['loans_MLP']; 
                    $loans_MF=$row['loans_MF']; 
                    $AF=$row['AF']; 
                    $misc=$row['misc']; 
                    $total=$row['total']; 
                    $payment_date=cls_date_get::MM_DD_yyyy_format(cls_bill_get::payment_date($reading_date,$AccntNos[$i]));
                    //echo "<br><br>";
                    $sqlInsRow="insert into {$TableName}(accnt_nos,reading_date,meter_reading,cu_used,penalty_date,bill_amnt,loans_MLP,loans_MF,AF,misc,total)";
                    $sqlInsRow.="values('{$accnt_nos}','{$reading_date}','{$meter_reading}','{$cu_used}','{$payment_date}','{$bill_amnt}','{$loans_MLP}','{$loans_MF}','{$AF}','{$misc}','{$total}')";
                    $e=new Exception();
                    $qryInsRow=mysql_query($sqlInsRow) or die(mysql_error()."___File: ".$e->getFile()."___Line: ".$e->getLine());
                }
            }
        }
                $sqlSelShowBilling="select accnt_nos from {$TableName} order by row_id asc";
                $e=new Exception();
                $qrySelShowBilling=mysql_query($sqlSelShowBilling) or die(mysql_error()."___File: ".$e->getFile()."___Line: ".$e->getLine());
                $i=0;
                $row_count=mysql_numrows($qrySelShowBilling);
                while($i <= mysql_numrows($qrySelShowBilling)){
/*                    echo "variable i={$i}<br>";*/
                    $row1=@mysql_result($qrySelShowBilling,$i);
                    if(($row1 == '') && ($row1== NULL) && ($i > $row_count) ){exit;}else{$names=cls_user_get::ProfileValue('acct_no',$row1,'applicant').'|';$i++;};
/*                    echo "variable i={$i}<br>";*/
                    $row2=@mysql_result($qrySelShowBilling,$i);
                    if(($row2 =='') && ($row2== NULL) && ($i > $row_count)){exit;}else{$names.=cls_user_get::ProfileValue('acct_no',$row2,'applicant').'|';$i++;};
/*                    echo "variable i={$i}<br>";*/
                    $row3=@mysql_result($qrySelShowBilling,$i);
                    if(($row3 =='') && ($row3== NULL) && ($i > $row_count)){exit;}else{$names.=cls_user_get::ProfileValue('acct_no',$row3,'applicant');$i++;};
/*                    echo "variable i={$i}<br>";*/
                    $link=$row1.'|'.$row2.'|'.$row3;                    
                    $B64ReadingDate=base64_encode($reading_date);$B64Link=base64_encode($link);
                    echo "<a href=\"dl2_bill.php?last_bill={$B64ReadingDate}&accnt_no={$B64Link}\">{$names}</a><br><br>";
                }
                
            }

    public function chkBillConsol($Table_Name){ //check table for bill consolidation
        include_once 'db_conn.php';
        $sql="show tables like '{$Table_Name}'";
        $qry=mysql_query($sql);
        $first_row=mysql_result($qry,0);
        if($Table_Name!=$first_row){
            $if_exist="0"; //not exist, create table
        }else{
            $if_exist="1"; // exist, truncate table
        }
        return $if_exist;
    }
    

    public function VerifyLedgerEntry($AccountNo,$Reading_Date){
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        $LedgerTable=cls_misc::ConvertToTableName($AccountNo);
        $sql="select * from {$LedgerTable} where reading_date='{$Reading_Date}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
        if(mysql_numrows($qry)> 0){$out="true";}else{$out="false";}
        return $out;
    }
    
    public function DeleteLedgerEntry($AccountNo,$Reading_Date){
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        $LedgerTable=cls_misc::ConvertToTableName($AccountNo);
        $e=new Exception();
        $sql="delete from {$LedgerTable} where reading_date='{$Reading_Date}'";
        mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
    }
    
    public function VerifyAddonsEntry($AccountNo,$BillingPeriod,$AddonType){
        include_once 'db_conn.php';
        $sql="select * from installment where acc_no='{$AccountNo}' and billed_date='{$BillingPeriod}' and addons_type='{$AddonType}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
        if(mysql_numrows($qry) > 0){ //duplicate found
            $sql="delete from installment where acc_no='{$AccountNo}' and addons_type='{$AddonType}' and status=''";    
            $e=new Exception();
            mysql_query($sql) or die(mysql_error()."_File:".$e->getFile()."__Line:".$e->getLine());
            //return "ok";
        }
    }
	
	// [start] stage for commit to remote PS(status:done)
	public function OR_Duplicate($OR_No,$AccountNo){
		//[start] stage for commit to remote PS (status:done) 2013-10-04
		include_once 'db_conn.php';
        include_once 'cls_user.php';
		$DateNow=date('Y-m-d');
		$sql="select * from or_log where or_number='{$OR_No}' and issued_to_accnt != '{$AccountNo}'";
		//$sql="select * from or_log where or_number='{$OR_No}'";
		$e=new Exception();
		$qry=mysql_query($sql) or mysql_error($e->getFile()."-".$e->getLine());
		$row=mysql_fetch_array($qry);
		if(mysql_numrows($qry) > 0){ //number released to another account
			$data=$row['or_number']."|".cls_user_get::ProfileValue('acct_no',$row['issued_to_accnt'],'applicant')."|".$row['or_date']; 
		}else{
			$data="|";
		}
		return $data;
	}
	//[start] stage for commit to remote PS
	
	
	//[start] stage for commit to remote PS (status: uploaded)
	public function BatchBillingTotal($AccountNo,$ReadingDate){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$LedgerTable=cls_misc::ConvertToTableName($AccountNo);
		$sql="select * from {$LedgerTable} where reading_date='{$ReadingDate}' limit 0,1";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error($e->getFile()."-".$e->getLine()));
		$row=mysql_fetch_array($qry);
		return  $row['total'];
	}
	
	//[end] stage for commit to remote PS
	
	public function collect_data($month, $year,$data=null){ //1-collectible 2-collection
		include_once 'db_conn.php';
		$criteria=$year."-".$month;
		$sql="select * from reporting where adlaw='{$criteria}' group by barangay asc";
		//$sql="select * from reporting where adlaw='{$criteria}' and barangay='{$barangay_name}'";
		$sql_qry=mysql_query($sql) or die(mysql_error());
			while($row=mysql_fetch_array($sql_qry)){
				$data_coordinates_value.=$row['collectibles'].",";        
			
		}
		return  $data_coordinates_value;
		//mysql_free_result($sql_qry);
		
		
	}
	
	public function collection_data($month, $year,$data=null){ //1-collectible 2-collection
		include_once 'db_conn.php';
		$criteria=$year."-".$month;
		$sql="select * from reporting where adlaw='{$criteria}' group by barangay asc";
		//$sql="select * from reporting where adlaw='{$criteria}' and barangay='{$barangay_name}'";
		$sql_qry=mysql_query($sql) or die(mysql_error());
		
			while($row=mysql_fetch_array($sql_qry)){
			$data_coordinates_value.=$row['collection'].",";
		}
		return  $data_coordinates_value;
		//mysql_free_result($sql_qry);
	}
	
	
	public function last_OR_Used(){
		include_once 'db_conn.php';
		$sql_str="select * from codes where code='last_or_no'";
		try {
			mysql_query($sql_str);
			//below is for debug purposes only,delete after deployment
			throw new Exception('database error');
		}catch(Exception $e){
			echo "error on line ".$e->getLine();
		}
		//priority(high)dev:mike(comments:::add +1 on success retrieval of the last or no from the datbase 
	}
	
	//[start]stage for commit to remote PS (coding status:uploaded)
	public function collection_today($month=null){//returns the collection for the day
		include_once 'db_conn.php';
		//set to current year
		$Year_Now=date('Y');
		if($month==null || $month==''){
			$sql_str1="select * from OR_log where OR_date=date(now())";    
		}elseif($month=='all'){
			$criteria=$Year_Now."%";
			//echo "criteria=".$criteria;
			$sql_str1="select * from OR_log where OR_date like '{$criteria}'";
		}else{
			$criteria=$Year_Now."-".$month."%";
			$sql_str1="select * from OR_log  where OR_date like '{$criteria}'";
						
		}
		
		$sql_qry1=mysql_query($sql_str1) or die(mysql_error());
		while($row1=mysql_fetch_array($sql_qry1)){
			$collection=$collection + $row1['issued_amnt'];
		}
		return  number_format($collection,2,".",",");
	}//returns the collection for the day
	
																					  
	public function for_disconn1($FilterBrgy=null){ //revision 2.0		
		//before executing codes below check the last update of table for_disconn to prevent redundant execution and preserve resources
		//prevent script timeout execution
		set_time_limit(0);
		//set_memory_limit(-1);
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
		$account_nos=cls_user_get::all_concessionaires_account_no($FilterBrgy);
		$date_now=date('Y-m-d');
		
		//prepare the table for_disconn for updates
		$sql_str="truncate table for_disconn";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		
		//start iterating to array
		$i=0;
		for($i=0;$i< count($account_nos);$i++){
            $LedgerTable=cls_misc::ConvertToTableName($account_nos[$i]);
			//$LedgerTable="ldg_".cls_misc::sanitize_hyp($account_nos[$i]); //acount number converted to ledger table format
			//get all billing period where they have not been paid or no activity at all
            if(cls_user_get::isPadLock($LedgerTable)==false){
			$sql_str="select * from {$LedgerTable} where OR_num='' or OR_num is null order by reading_date asc";
			$sql_qry=mysql_query($sql_str) or die(mysql_error());
			while($row=mysql_fetch_array($sql_qry)){
				//get the disconnection date of the current row
				$DisconnDate=cls_bill_get::disconn_date(cls_bill_get::payment_date($row['reading_date'],$LedgerTable));
				if($date_now > $DisconnDate){ //due date bill found
					 if(cls_misc::CheckDuplication('for_disconn','accnt_no','reading_date',$account_nos[$i],$row['reading_date'])=='0'){ //no duplication found
						//insert into for_disconn table
						$str_update_tblForDisconn="insert into for_disconn(accnt_no,reading_date)values('{$account_nos[$i]}','{$row['reading_date']}')";
						//execute statement
						$qry_update_tblForDisconn=mysql_query($str_update_tblForDisconn) or die(mysql_error());
					 }
				}
			}	
		}
        }
		  //retrieve all the data from table for_disconn
		  $DisConnAccount_nos=cls_user_get::ForDisconnectionNotice();
		  $MainText="<center>List of Concessionaires with Disconnection Notices for  ";
		  if($FilterBrgy==null){
			  $MainTextConcat="<strong>All Barangays</strong><br>";
			  $bFiltered=0;
		  }else{
			  $MainTextConcat="<strong>Barangay ". cls_misc::toString($FilterBrgy,"Barangay")."</strong><br><br>";
			  $bFiltered=1;
		  }
		  echo $MainText.$MainTextConcat;
		  ?><table width="100%">
			<tr>
				<td><center>Accnt. No</center></td>
				<td><center>Name</center></td>
				<td><center>No. of Unpaid Bills</center></td>
				<?php if($FilterBrgy==null){ ?> <td>Barangay</td> <?php } ?>
			</tr> <?php
		  if(count($DisConnAccount_nos)>0){
			  foreach($DisConnAccount_nos as $key=>$value){
				echo "<tr>
				<td><center>{$value['accnt_no']}</center></td>
				<td>".cls_user_get::ProfileValue('acct_no',$value['accnt_no'],'applicant')."</td>
				<td><center>{$value['count']}</center></td>";
				if($FilterBrgy==null){ echo "<td>".cls_misc::toString(cls_user_get::ProfileValue('acct_no',$value['accnt_no'],'address_brgy'),'Barangay')  ."</td>"; }
				 echo "</tr>";
			  }
			  echo "</table>";
		  }else{
			  ?><tr><td colspan="4"><center><?php echo "NO List of Concessionaires for Disconnection"; ?></center></td></tr></table><?php
		  }
		  echo "<br>";
		  cls_bill_set::ExportToExcel($DisConnAccount_nos,$MainText.$MainTextConcat,$bFiltered);
	}
	
    public function compute_bill_amount($account_no,$billing_date,$cu_used,$connection_type){
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        switch ($connection_type) {
            case '1'://residential
                if($cu_used >= 31){
                    //$bill_amnt=number_format(((($cu_used - 30) * 17) + 160.00 + 150.00 + 140.00),2,'.',',');
                    $bill_amnt=((($cu_used - 30) * 17) + 160.00 + 150.00 + 140.00);
                }elseif($cu_used <= 30 && $cu_used > 20 ){
                    //$bill_amnt=number_format(((($cu_used - 20) * 16.00) + 150.00 + 140.00),2,'.',',');
                    $bill_amnt=((($cu_used - 20) * 16.00) + 150.00 + 140.00);
                }elseif($cu_used <=20 && $cu_used > 10){
                    $bill_amnt=((($cu_used -10) * 15.00) + 140.00);
                //[start] stage for commit to remote PS (Status:done) 2013-10-04
                }elseif ($cu_used <=10 && $cu_used > 0){
                    $bill_amnt=140.00;
                }//todo: FFU#56
                elseif($cu_used==0){
                    $LedgerTable=cls_misc::ConvertToTableName($account_no);
                    if(cls_user_get::isPadLock($LedgerTable)==false){
                        $bill_amnt='140.00';
                    }elseif(cls_user_get::isPadLock($LedgerTable)==true){
                        $bill_amnt='0.00';
                    }
                }
                break;

            case '2'://commercial
                
                if($cu_used >= 31){
                    //$bill_amnt=number_format(((($cu_used - 30) *25.50) + 240 + 225.00 + 210.00),2,'.',',');
                    $bill_amnt=((($cu_used - 30) *25.50) + 240 + 225.00 + 210.00);
                }elseif($cu_used <= 30 && $cu_used > 20 ){
                    $bill_amnt=((($cu_used - 20) * 24.00) + 225.00 + 210.00);
                }elseif($cu_used <=20 && $cu_used > 10){
                    $bill_amnt=((($cu_used -10) * 22.50) + 210.00);
                }elseif ($cu_used <=10 && $cu_used > 0){
                    $bill_amnt=210.00;
                }elseif($cu_used == 0){
                    $bill_amnt='0.00';
                }
                            
            break;
        }
        //return data
        return  $bill_amnt;
    }
	
	//[start] stage for commit to remote PS (status: uploaded)
	public function total_bill_amount($account_no,$billing_date){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$accnt_no=cls_misc::sanitize_hyp($account_no);
		$sql_str="select * from ldg_{$accnt_no} where reading_date='{$billing_date}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		$total=str_replace(',','',$row['bill_amnt']) + $row['loans_MLP'] + $row['loans_MF'];
		//set total value to customer ledger
		parent::Total($accnt_no,$billing_date,$total);
	}
	//[end] stage for commit to remote PS

	public function payment_date($reading_date,$account_number){ //returns the payment date
		include_once 'db_conn.php';
		include_once 'date_time.php';
		include_once 'cls_user.php';
		$const_date=cls_user_get::const_payment_date($account_number);
		$month=cls_date_get::extract_month($reading_date);
		$year=cls_date_get::extract_year($reading_date);
		$new_date= $year.'-'.$month.'-'.$const_date;
		$sql_str="select date_add('{$new_date}',interval 1 month) as new_date";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['new_date'];
	}

	public function payment_grace($payment_date){ //returns the grace period for the payment
		$sql_str="select date_add('{$payment_date}',interval 15 day) as grace_period";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['grace_period'];
	}

	public function payment_ext($payment_date){ //returns the payment extensions
		$sql_str="select date_add('{$payment_date}',interval 20 day) as extension";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['extension'];
	}

	public function disconn_date($payment_date){ //returns the disconnection dates
		$sql_str="select date_add('{$payment_date}',interval 21 day) as disconn_date";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['disconn_date'];
	}

	public function date_diff($recent,$old){ //returns the difference of two dates
		//$sql_str="select datediff('{$recent}','{$old}') as result";
		$sql_str="select datediff(date(now()),'{$old}') as result";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['result'];
	}

	public function unsettled_bills($account){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		include_once 'date_time.php';
		?>
		<script type="text/javascript">//alert('please settle the oldest bill first');</script>
		<?php
		$ledger=cls_misc::sanitize_hyp($account);
		//$sql_str="select * from ldg_{$ledger} where isNAN()OR_num='0' or OR_num is null order by reading_date asc";
		$sql_str="select * from ldg_{$ledger} where OR_num='' or OR_num is null order by reading_date asc";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		?>
		<br>
		<center>
		<form name="form1" method="POST" action="form_handler.php">
		<input type="hidden" name="cashier" value="to_settle">
		<input type="hidden" name="accnt_no" value="<?php echo base64_encode(trim($account))?>">
		<table>
			<tr><td>No. of Unsettled Bills:<strong><?php echo mysql_num_rows($sql_qry)?></strong></td></tr>
		</table>
		<br>
		<table>
			<tr><th colspan="5">Select Billing Month to Settle</th></tr>
			<tr>
				<td>Billing Month</td>
				<td>Due Date</td>
				<td>Amount</td>
				<td>Penalty(5%)</td>
				<td>Total Amount</td>
			</tr>
		<?php
		$i=0;
		while ($row=mysql_fetch_array($sql_qry)) {
			$i++;
		?>
        <?php //FFU#93[starts] -c 10 ?>
        <tr><!--<b>Billing Month</b>!-->
            <?php  $billing_month=cls_misc::toString(cls_date_get::extract_month($row['reading_date']),'month').'-'. cls_date_get::extract_year($row['reading_date'])?>
            <?php //[start] stage for commit to remote PS[status: done]?>
            <?php if($row['total']==0){$amount=$row['bill_amnt'];}else{$amount=$row['total'];} ?>
            <?php //$amount_to_settle=$amount + cls_bill_get::compute_penalty($row['reading_date'],$row['bill_amnt'],trim($account))?>
            <?php $amount_to_settle=$row['bill_amnt'] + cls_bill_get::compute_penalty($row['reading_date'],$row['bill_amnt'],trim($account))?>
            <?php $penalty=cls_bill_get::compute_penalty($row['reading_date'],$row['bill_amnt'],$account)?> 
            <td><?php if($i==1){ ?>
            <a href="cashier.php?request=to_settle&accnt_no=<?php echo base64_encode(trim($account))?>&bill_date=<?php echo base64_encode($row['reading_date'])?>&amnt=<?php echo base64_encode($amount_to_settle)?>&penalty=<?php echo base64_encode($penalty)?>" title="<?php echo $billing_month?>"><b><?php echo $billing_month?></b></a><?php } else {echo $billing_month;}?> </td>
            <!--due date!-->
            <td><?php echo cls_date_get::MM_DD_yyyy_format(cls_bill_get::payment_ext(cls_bill_get::payment_date($row['reading_date'],trim($account))))?>
            <?php //echo cls_bill_get::payment_ext(cls_bill_get::payment_date($row['reading_date'],trim($account)))?> </td>
            <!--amount!-->
            <td><?php echo cls_misc::gFormatNumber($row['bill_amnt'])?></td>
            <!--penalty!-->
            <td> <?php echo cls_misc::gFormatNumber(cls_bill_get::compute_penalty($row['reading_date'],$row['bill_amnt'],$account))?> </td>
            <!--total payment to date plus penalty if ever!-->
            <td><?php echo number_format($row['bill_amnt'] + cls_bill_get::compute_penalty($row['reading_date'],$row['bill_amnt'],trim($account)),2,'.',',')?></td>
        </tr>
        <?php //FFU#93[ends] -c 10  ?>
		<?php //[end] stage for commit to remote PS[status: done]?>
		<?php
		}
		?>
		</table>
		</form>
		</center>
		<?php
	}

	// [start] stage for commit to remote PS
	public function compute_penalty($reading_date,$amount,$account_number){
		include_once 'cls_bill.php';
		$penalty_percentage=0.05;
		//start] stage for commit to remote PS(status:done)
		$date_diff=cls_bill_get::date_diff('current_date',cls_bill_get::payment_grace(cls_bill_get::payment_date($reading_date,$account_number)));
		//end] stage for commit to remote PS(status:done)
		if ($date_diff >= 1) {
			$penalty_amount=$penalty_percentage * $amount;
			return $penalty_amount;
		}else{
			return 0;
		}
	}
	//[end] stage for commit to remote PS

	public function last_bill_created($account_no){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$accnt_no=cls_misc::sanitize_hyp(trim($account_no));
		$sql_str="select * from ldg_{$accnt_no} where OR_num='0' or OR_num is null order by reading_date desc";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['reading_date'];
	}

	public function bool_dl($accnt_no,$reading_date){
		include_once 'db_conn.php';
		$sql_str="select * from dl_bill where accnt_no='{$accnt_no}' and last_bill='{$reading_date}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		if (mysql_num_rows($sql_qry)>=1) {
			$msg="yes";
		}else{
			$msg="no";
		}
		return $msg;
	}
	public function check_if_exist_in_discon($account_no,$reading_date,$disconn_date){
		$str = mysql_query("select * from disconn_dates where accnt_no='".$account_no."' and reading_date='".$reading_date."'") or die("error".mysql_error());
		$str = mysql_num_rows($str);
		if($str==0){
			mysql_query("insert into disconn_dates(accnt_no,reading_date,disconn_date)values('".$account_no."','".$reading_date."','".$disconn_date."')") or die("error in inserting to disconne".mysql_error());
		} else{
		  #no operation
		 
		}
		
	}
	public function write_to_disconn_table(){
		#this will insert accountno to disconn dates tabble
	   $mga_accounts = array();  //creates an array to store account no                                   
	   $str = mysql_query("select * from profile") or die('erri'.mysql_error());
	   while($row=mysql_fetch_assoc($str)){
	   array_push($mga_accounts,$row['acct_no']);
	   }
	   #this will iterate to each table
	   foreach($mga_accounts as $accountno){
		   $str = mysql_query("select * from ldg_".str_replace("-","_",$accountno)." where OR_date is null or OR_date=''" );
		   while($row=mysql_fetch_assoc($str)){
			   #this will get the disconn_date(payment date)
			   $disconn_date =  cls_bill_get::disconn_date(cls_bill_get::payment_date($row['reading_date'],$accountno)); 
			   #this will check if the reading date for acount no exists in table discnonn then insert
					cls_bill_get::check_if_exist_in_discon($accountno,$row['reading_date'],$disconn_date);
			   }
	   }
	}

}

//----------------setters
class cls_bill_set{

        public function ListofConcessionairesExportToExcel($DataArray,$ReportsTitle,$TemplateFile){
            if(count($DataArray)==0){
                echo "No data export can be shown for downloads<br>";
                exit;
            }
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            include_once 'cls_user.php';
            include_once 'date_time.php';
            include_once 'classes/PHPExcel.php';
            include_once 'classes/PHPExcel/IOFactory.php';

            //output excel files as filename will be based on reports title presented
            $file_output="accnts/".$ReportsTitle.".xls";
            $FileTemplate="accnts/".$TemplateFile;
            //declare filetype
            $FileType='Excel5';
            //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
            $objReader=PHPExcel_IOFactory::createReader($FileType);
            $objFileLoader=$objReader->load($FileTemplate);
            //$objWrkSheet=$objFileLoader->getActiveSheet();    
            $ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
            $ActiveSheet->setCellValue("A1",cls_misc::RemoveHtmlFormat($ReportsTitle));
            $start_row=3;
            $Nos=0;
            foreach($DataArray as $key=>$value){
                $Nos+=1;
                $ActiveSheet->setCellValue("A".$start_row,$Nos);
                $ActiveSheet->setCellValue("B".$start_row,$value['accnt_no']);
                $ActiveSheet->setCellValue("C".$start_row,$value['names']);
                $start_row++;
            }
            //resize columns
            $ActiveSheet->getColumnDimension('A')->setAutoSize(true);
            $ActiveSheet->getColumnDimension('B')->setAutoSize(true);
            $ActiveSheet->getColumnDimension('C')->setAutoSize(true);
            
            ob_end_clean();
            if(file_exists($file_output)){unlink($file_output);}
            //proceed to output creation
            $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
            $objWriter->save($file_output);
            unset($objReader);
            //create the download link for export
            cls_bill_set::CreateDownloadLink($file_output);
        }
		
		public function UnpaidExportToExcel($DataArray,$ReportsTitle,$TemplateFile){ //FFU#59
            if(count($DataArray)==0){
                echo "No bills can be shown for downloads<br>";
                exit;
            }
			include_once 'db_conn.php';
			include_once 'cls_codes.php';
			include_once 'cls_user.php';
			include_once 'date_time.php';
			include_once 'classes/PHPExcel.php';
			include_once 'classes/PHPExcel/IOFactory.php';

			//output excel files as filename will be based on reports title presented
			$file_output="accnts/".$ReportsTitle.".xls";
			$FileTemplate="accnts/".$TemplateFile;
			//declare filetype
			$FileType='Excel5';
			//initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
			$objReader=PHPExcel_IOFactory::createReader($FileType);
			$objFileLoader=$objReader->load($FileTemplate);
			//$objWrkSheet=$objFileLoader->getActiveSheet();    
			$ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
			$ActiveSheet->setCellValue("A1",cls_misc::RemoveHtmlFormat($ReportsTitle));
			$start_row=3;
			foreach($DataArray as $key=>$value){
				$ActiveSheet->setCellValue("A".$start_row,$value['accnt_no']);
				$ActiveSheet->setCellValue("B".$start_row,$value['accnt_name']);
                $ActiveSheet->setCellValue("C".$start_row,$value['UnPaidBills']);
                $ActiveSheet->setCellValue("D".$start_row,$value['DueDate']);
				$ActiveSheet->setCellValue("E".$start_row,$value['Amount']);
                $ActiveSheet->setCellValue("F".$start_row,$value['Penalty']);
                $ActiveSheet->setCellValue("G".$start_row,$value['Total']);
				$start_row++;
			}
			//resize columns
			$ActiveSheet->getColumnDimension('A')->setAutoSize(true);
			$ActiveSheet->getColumnDimension('B')->setAutoSize(true);
			$ActiveSheet->getColumnDimension('C')->setAutoSize(true);
			$ActiveSheet->getColumnDimension('D')->setAutoSize(true);
			
            ob_end_flush();
			if(file_exists($file_output)){unlink($file_output);}
			//proceed to output creation
			$objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
			$objWriter->save($file_output);
			unset($objReader);
			//create the download link for export
			cls_bill_set::CreateDownloadLink($file_output);
		}

		public function ExportToExcel($DataArray,$ReportsTitle,$bFiltered){
			include_once 'db_conn.php';
			include_once 'cls_codes.php';
			include_once 'cls_user.php';
			include_once 'date_time.php';
			include_once 'classes/PHPExcel.php';
			include_once 'classes/PHPExcel/IOFactory.php';

			//select paper size format
			$template_file="accnts/disconn_template{$bFiltered}.xls";
			
			//output excel files as filename will be based on reports title presented
			$file_output="accnts/".cls_misc::RemoveHtmlFormat($ReportsTitle).".xls";
			
			//declare filetype
			$FileType='Excel5';
			//initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
			$objReader=PHPExcel_IOFactory::createReader($FileType);
			$objFileLoader=$objReader->load($template_file);
			//$objWrkSheet=$objFileLoader->getActiveSheet();    
			$ActiveSheet=$objFileLoader->setActiveSheetIndex(0); //workbook has only one worksheet,activate explicit
			$ActiveSheet->setCellValue("A1",cls_misc::RemoveHtmlFormat($ReportsTitle));
			$ActiveSheet->mergecells("A1:G1");
			$start_row=3;
			if(count($DataArray)==0){die();}
			foreach($DataArray as $key => $value){
				$ActiveSheet->setCellValue("A".$start_row,$value['accnt_no']);
				$AccountName=cls_user_get::ProfileValue('acct_no',$value['accnt_no'],'applicant');
				$ActiveSheet->setCellValue("B".$start_row,$AccountName);
				$ActiveSheet->setCellValue("C".$start_row,$value['count']);
				if($bFiltered=='0'){
					$strBarangay=cls_misc::toString(cls_user_get::ProfileValue('acct_no',$value['accnt_no'],'address_brgy'),'Barangay');
					$ActiveSheet->setCellValue("D".$start_row,$strBarangay);    
					$ActiveSheet->getColumnDimension('D')->setAutoSize(true);
				}
				$start_row++;
			}
			//$start_row=$start_row +1;
			//$ActiveSheet->setCellValue("A".$start_row,"Total Collection for this Report=".cls_misc::gFormatNumber($SubTotal));
			$ActiveSheet->getColumnDimension('A')->setAutoSize(true);
			$ActiveSheet->getColumnDimension('B')->setAutoSize(true);
			//check if file exist
            ob_end_flush();
            //ob_end_clean();
			if(file_exists($file_output)){unlink($file_output);}
			//proceed to output creation
			$objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
			$objWriter->save($file_output);
			unset($objReader);
			//create the download link for export
			cls_bill_set::CreateDownloadLink($file_output);
		} //function ends
		
		//todo: [end] stage for commit to remote PS(status: ongoing-edit full function)    

	public function CreateDownloadLink($filename){
		?><span class="art-button-wrapper"><span class="art-button-l"></span><span class="art-button-r"></span><a href="<?php echo $filename; ?>" class="art-button">Export to Excel File</a></center>
			<?php
	}

	//[start] stage for commit to remote PS (status: uploaded)
	public function BatchBillingComputation(){ //revision of $_post['bill']='save_current_reading' intended for ajax transport and response
		$reading_date=$_POST['curr_reading_date'];
		if ($reading_date!="Date not Scheduled") {
			include_once 'cls_codes.php';
			include_once 'cls_user.php';
			include_once 'cls_bill.php';
			//save to consumer ledger
			$accnt_no=$_POST['accnt_no'];
			$reading_date=$_POST['curr_reading_date'];
			$connetion_type=$_POST['conn_type'];
			$curr_read_value=$_POST['curr_read_value'];
			$prev_reading_value=$_POST['prev_reading_value'];
			$cubic_used=$curr_read_value - $prev_reading_value;
			$billing_count=$_POST['billing_count'];
			$biling_date=cls_user_get::billing_date($accnt_no);
			
			//insert ledger value
			cls_user_set::ledger_value_ins($accnt_no,"reading_date",$reading_date);
			
			//update ledger value based on reference
			cls_user_set::ledger_value_update($accnt_no,"meter_reading",$curr_read_value,"=","reading_date",$reading_date);
			cls_user_set::ledger_value_update($accnt_no,"cu_used",$cubic_used,"=","reading_date",$reading_date);
			
			//update installment table based on parameters
			cls_user_set::meter_fee_billed_date($accnt_no,$billing_count);
			
			//todo:FFU#54 update bill_amnt 
			$bill_amnt=cls_bill_get::compute_bill_amount($accnt_no,$reading_date,$cubic_used,$connetion_type);
/*            $LedgerTable=cls_misc::ConvertToTableName($accnt_no);
            if(($bill_amnt=='140.00'||$bill_amnt=='140')&&(cls_user_get::isPadLock($LedgerTable)==false)){
                $bill_out=$bill_amnt;
            }elseif(($bill_amnt=='140.00'||$bill_amnt=='140')&&(cls_user_get::isPadLock($LedgerTable)==true)) {
                $bill_out='0.00';
            }else{
                $bill_out=$bill_amnt;
            }
*/			
            cls_user_set::ledger_value_update($accnt_no,'bill_amnt',$bill_amnt,"=","reading_date",$reading_date);
			
			//update ledger for additional payments(codes activation in progress)
            //todo -o mike -p 10 -c Code Update: Continue Updates on codings for batch billing procedure
            $add_meter_fee=cls_user_get::BillAddons($accnt_no,$reading_date,"met_fee");
            $add_mlp=cls_user_get::BillAddons($accnt_no,$reading_date,"MLP");
            //todo -o mike -p 15 -c Urgent Action Needed: classify or any additional payments that can be classified as misc_fee
             //enter codes here
             //$add_recon_fee=$_POST['add_recon_fee'];
			cls_user_set::ledger_value_update($accnt_no,'loans_MLP',$add_mlp,"=","reading_date",$reading_date);
			cls_user_set::ledger_value_update($accnt_no,'loans_MF',$add_meter_fee,"=","reading_date",$reading_date);
            
			
			//sum up total bill to date
			cls_bill_get::total_bill_amount($accnt_no,$biling_date);
			$bill_amount=str_replace(',','',cls_bill_get::BatchBillingTotal($accnt_no,$reading_date));
			//finalize data entry for acceptance
		 
		 //ajax outputs: status output=ok, BillAmount=$bill_amount,link for billing
		 $AccountNo=base64_encode($accnt_no);
		 $ReadingDate=base64_encode($reading_date);
		 $status="ok";
		 //$link_reference="<a target=\"_blank\" href=\"download_billing.php?request=init_dl&accnt_no={$AccountNo}&last_bill={$ReadingDate}\" class=\"art-button\">Download</a>";
		 $link_reference="download_billing.php?request=init_dl&accnt_no={$AccountNo}&last_bill={$ReadingDate}";
		 echo "ok|".cls_misc::gFormatNumber($bill_amount)."|".$link_reference; 
		}else {
			echo "not ok"
			?>
			
<!--            <script type="text/javascript">
				alert("saving of data not allowed");
			</script>
-->            <?php
			//header("location:billing.php?request=accnt");

		}
		
	}
	//[end] stage for commit to remote PS
	
	
	public function setup_OR($first_OR_num,$last_OR_num){//used to setup OR numbers for series of receipts for cashier department only
		include_once 'db_conn.php';
		$sql_str="update codes set descr='{$first_OR_num}' where code='or_start'";
		try{
			mysql_query($sql_str);
			//below is for debug for purposes only,suppress mysql_query error after deployment
			throw new Exception(mysql_error());
		}catch(Exception $e){
			echo "error on ". $e->getLine();
		}
		$sql_str="update codes set descr='{$last_OR_num}' where code='or_stop'";
		try{
			mysql_query($sql_str);
			//below is for debug purposes only
			throw new Exception(mysql_error());
		}catch(Exception $e){
			echo "mysql_erron on line ". $e->getLine();
		}
	} //used to setup OR numbers for series of receipts for cashier department only

	//[start] stage for commit to remote PS(status: ongoing and being tested)
	public function Total($account_no,$billing_date,$total_value){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$ledger_no=cls_misc::sanitize_hyp($account_no);
		//$Ftotal_value=cls_misc::gFormatNumber($total_value);
		$sql_str="update ldg_{$ledger_no} set total='{$total_value}' where reading_date='{$billing_date}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
	}
	//[end] stage for commit to remote PS

	public function consumer_payment($account_no){

	}

	public function bill_form($account_no,$reading_date){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		include_once 'date_time.php';
		include_once 'classes/PHPExcel.php';
		include_once 'classes/PHPExcel/IOFactory.php';

		//prepare the data that will be written to the excel file
		$ledger_table=cls_misc::sanitize_hyp(trim($account_no));
		$sql_str="select * from {$ledger_table} where reading_date='{$reading_date}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);


		//write the date to excel file

		//declare filetype
		$FileType='Excel5';
		//declare file template
		$file_template='accnts/template.xls';
		$file_output="accnts/{$account_no}.xls ";

		$objReader=PHPExcel_IOFactory::createReader($FileType);
		$objFileLoader=$objReader->load($file_template);
		$objWrkSheet=$objFileLoader->getActiveSheet();
		//commit changes to the file
		//present reading=cell(b10)
		$present_reading=$row['meter_reading'];
		$objWrkSheet->getCell('B10')->setValue("{$present_reading}");
		//usage amount=cell E10
		$cu_m_usage=$row['cu_used'];
		$objWrkSheet->getCell('E10')->setValue("{$cu_m_usage}");
		//previous reading=cell c10
		$previous_reading=$present_reading - $cu_m_usage;
		$objWrkSheet->getCell('C10')->setValue("{$previous_reading}");
		//meter fee=cell E12
		$meter_fee=$row['loans_MF'];
		$objWrkSheet->getCell('E12')->setValue("{$meter_fee}");
		//penalty=E13
		$penalty=$row['pen_fee'];
		$objWrkSheet->getCell('E13')->setValue("{$penalty}");
		//material loan program=E14
		$mlp_fee=$row['loans_MLP'];
		$objWrkSheet->getCell('E14')->setValue("{$mlp_fee}");
		//interest=E15
		//reconnection fee=E16
		//billing month=D20
		$billing_month=cls_misc::toString(cls_date_get::extract_month($row['reading_date']),'month')."-".cls_date_get::extract_year($row['reading_date']);
		$objWrkSheet->getCell('D20')->setValue("{$billing_month}");
		//consumer account number=D22
		$consumer_accnt=trim($account_no);
		$objWrkSheet->getCell('D22')->setValue("{$consumer_accnt}");

		//-------------prepare connection to profile
		$sql_str2="select * from profile where acct_no='{$consumer_accnt}'";
		$sql_qry2=mysql_query($sql_str2) or die(mysql_error());
		$row2=mysql_fetch_array($sql_qry2);

		//serial number=D23
		$serial_no=$row['serial_no'];
		$objWrkSheet->getCell('D23')->setValue("{$serial_no}");
		//account holder=C25
		$acct_name=$row['applicant'];
		$objWrkSheet->getCell('C25')->setValue("{$acct_name}");
		//account holder address=C26
		$brgy_add=cls_misc::toString($row['address_brgy'],'Barangay');
		$objWrkSheet->getCell('C26')->setValue("{$brgy_add}");
		//payment date=D28
		$payment_date=cls_date_get::MM_DD_yyyy_format(cls_bill_get::payment_date($reading_date,$consumer_accnt));
		$objWrkSheet->getCell('D28')->setValue("{$payment_date}");
		//prepared by=D30

		//check if file exist
		if (var_dump(unlink($file_output))==false) {
			$objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
			$objWriter->save($file_output);
		}
		//unset($objReader);
	
	} //function ends


	public function reading_sheet_form($BarangayCode){ //return the filename of the output
        set_time_limit(0);ini_set("memory_limit",-1);
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        include_once 'date_time.php';
        include_once 'classes/PHPExcel.php';
        include_once 'classes/PHPExcel/IOFactory.php';
        //local scope page variable
        $template_file="accnts/rs_template.xls";
        //output and download the excel as rs_yyyy_mm_brgy_code(all separated by _)
        $file_output="accnts/rs_".date('Y-m')."_".$BarangayCode.".xls";
        
        //get the list of concessionaires from profile where BarangayCode is the same as the parameter given
        $Sql_NoOfApplicant="select acct_no from profile where address_brgy='{$BarangayCode}'";
        $Qry_NoOfApplicant=mysql_query($Sql_NoOfApplicant) or die(mysql_error());
        //count the output rows and divide by 25
        //the quotient will corresponds to the number of worksheed that will be created
        $Pages=explode(".",mysql_num_rows($Qry_NoOfApplicant)/25);
        if(mysql_numrows($Qry_NoOfApplicant) % 25 !=0){
            $Min_NumOfPages=$Pages[0] + 1;
        }else{ //exact number is result
            $Min_NumOfPages=mysql_num_rows($Qry_NoOfApplicant)/25;
        }
        $Num_OfPages=$Min_NumOfPages;
        
        //get the Account numbers
        while($ApplicanstRetrieve=mysql_fetch_array($Qry_NoOfApplicant)){
            $AccntNoRetrieve[]=$ApplicanstRetrieve['acct_no'];
        }
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
        $objWrkSheet=$objFileLoader->getActiveSheet();
        
        $no_start="1";$no_end="25";
        //create clone worksheets
        for($i=1;$i < $Num_OfPages;$i++){
            $no_start=$no_start + 25;
            $no_end=$no_end + 25;
            $objClone=clone $objWrkSheet;    
            //create temporary worksheet name
            $objClone->setTitle("P{$i}");
            $objFileLoader->addSheet($objClone);
        }
        //start populating the worksheets
        $MaxSheetIndex=$Num_OfPages;
        $no_start=1;$no_end=25;
        $value=0;
        for($i=0;$i < $MaxSheetIndex;$i++){
            $Start_RecordSet=$no_start - 1;
            //$End_RecordSet=$no_end -1;
            $SQL_ReCreateForEachSheet="select * from profile where address_brgy='{$BarangayCode}' order by applicant asc limit {$Start_RecordSet},25";
            $e=new ErrorException();
            $QRY_ReCreateForEachSheet=mysql_query($SQL_ReCreateForEachSheet) or die(mysql_error($e->getFile()."-".$e->getLine()));
            while($ROW_ReCreateForEachSheet=mysql_fetch_array($QRY_ReCreateForEachSheet)){
                $AccountNo_Values[]=$ROW_ReCreateForEachSheet['acct_no'];
                $AccountName_Values[]=$ROW_ReCreateForEachSheet['applicant'];
                $AccountMeterNo_Values[]=$ROW_ReCreateForEachSheet['meterno'];
            }
            
            $ActiveSheet=$objFileLoader->setActiveSheetIndex($i);
            $ActiveSheet->SetCellValue("A6","Barangay: ".cls_misc::toString($BarangayCode,"Barangay")."      Month: ".cls_misc::toString(date('m'),'month')."-".date('Y'));
            $objFileLoader->setActiveSheetIndex($i);
            $start_row=9;$stop_row=$start_row + 25;
            while($start_row < $stop_row){
                $ActiveSheet->setCellValue("A".$start_row,$value+1);
                $ActiveSheet->setCellValue("B".$start_row,$AccountNo_Values[$value]);
                $ActiveSheet->setCellValue("C".$start_row,$AccountName_Values[$value]);
                $ActiveSheet->setCellValue("D".$start_row,$AccountMeterNo_Values[$value]);
                //get the last reading value on each ledger and get the 
                if($AccountNo_Values[$value]!=null||$AccountNo_Values[$value]!=''){
                $LedgerTable=cls_misc::ConvertToTableName($AccountNo_Values[$value]);
                $SQL_RetLastMeterReading="select * from {$LedgerTable} order by reading_date desc limit 0,1";
                $QRY_RetLastMeterReading=mysql_query($SQL_RetLastMeterReading) or die(mysql_error($e->getFile()."-".$e->getLine()));
                $ROW_RetLastMeterReading=mysql_fetch_array($QRY_RetLastMeterReading);
                $ActiveSheet->setCellValue("E".$start_row,$ROW_RetLastMeterReading['meter_reading']);
                }
                $value++;
                $start_row++;
            }
            $ActiveSheet->setTitle("C{$no_start}-{$no_end}");
            $no_start=$no_start + 25;
            $no_end=$no_start + 25-1;
        }
        
        $objFileLoader->setActiveSheetIndex(0);
        //check if file exist
        if(file_exists($file_output)){
            unlink($file_output);
        }
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
    } //function ends
    
    public function reading_sheet_form2($BarangayCode){ //return the filename of the output to
        //todo: FFU#74
        set_time_limit(0);ini_set("memory_limit",-1);
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        include_once 'date_time.php';
        include_once 'classes/PHPExcel.php';
        include_once 'classes/PHPExcel/IOFactory.php';
        //local scope page variable
        $template_file="accnts/rs_template.xls";
        //output and download the excel as rs_yyyy_mm_brgy_code(all separated by _)
        $file_output="accnts/rs_".date('Y-m')."_".$BarangayCode.".xls";
        
        //get the list of concessionaires from profile where BarangayCode is the same as the parameter given
        $Sql_NoOfApplicant="select acct_no from profile where address_brgy='{$BarangayCode}' order by applicant asc";
        $Qry_NoOfApplicant=mysql_query($Sql_NoOfApplicant) or die(mysql_error());
        
        //get the Account numbers
        while($ApplicanstRetrieve=mysql_fetch_array($Qry_NoOfApplicant)){
            $AccntNoRetrieve[]=$ApplicanstRetrieve['acct_no'];
        }
        //declare filetype
        $FileType='Excel5';
        //initiate phpexcel algorithms and create necessary worksheets required to create reading sheets
        $objReader=PHPExcel_IOFactory::createReader($FileType);
        $objFileLoader=$objReader->load($template_file);
//        $objWrkSheet=$objFileLoader->getActiveSheet();
        $ActiveSheet=$objFileLoader->getActiveSheet();
        //set title for the output
        $ActiveSheet->SetCellValue("A6","Barangay: ".cls_misc::toString($BarangayCode,"Barangay")."      Month: ".cls_misc::toString(date('m'),'month')."-".date('Y'));
        
        //set entry at row 9
        $start_row=9;
        //counting number start at 0
        $value=0;
        
        //start populating the worksheets
        for($i=0;$i < count($AccntNoRetrieve);$i++){
            $LedgerTable=cls_misc::ConvertToTableName($AccntNoRetrieve[$i]);
            if(cls_user_get::isPadLock($LedgerTable)!=1){
                $SQL_ReCreateForEachSheet="select * from profile where address_brgy='{$BarangayCode}' and acct_no='{$AccntNoRetrieve[$i]}'";
                $e=new ErrorException();
                $QRY_ReCreateForEachSheet=mysql_query($SQL_ReCreateForEachSheet) or die(mysql_error($e->getFile()."-".$e->getLine()));
                //return all values related to account number
                $ROW_ReCreateForEachSheet=mysql_fetch_array($QRY_ReCreateForEachSheet);
                
                $AccountNo_Values=$ROW_ReCreateForEachSheet['acct_no'];
                $AccountName_Values=$ROW_ReCreateForEachSheet['applicant'];
                $AccountMeterNo_Values=$ROW_ReCreateForEachSheet['meterno'];
            
                $ActiveSheet->setCellValue("A".$start_row,$value+1);
                $ActiveSheet->setCellValue("B".$start_row,$AccountNo_Values);
                $ActiveSheet->setCellValue("C".$start_row,$AccountName_Values);
                $ActiveSheet->setCellValue("D".$start_row,$AccountMeterNo_Values);
                //get the last reading value on each ledger and get the 
                if($AccntNoRetrieve[$i]!=null||$AccntNoRetrieve[$i]!=''){
                $LedgerTable=cls_misc::ConvertToTableName($AccntNoRetrieve[$i]);
                $SQL_RetLastMeterReading="select * from {$LedgerTable} order by reading_date desc limit 0,1";
                $QRY_RetLastMeterReading=mysql_query($SQL_RetLastMeterReading) or die(mysql_error($e->getFile()."-".$e->getLine()));
                $ROW_RetLastMeterReading=mysql_fetch_array($QRY_RetLastMeterReading);
                $ActiveSheet->setCellValue("E".$start_row,$ROW_RetLastMeterReading['meter_reading']);
                }
                $value++;
                $start_row++;
            }
        }
        
//        
        //check if file exist
        if(file_exists($file_output)){
            unlink($file_output);
        }
        //proceed to output creation
        $objWriter=PHPExcel_IOFactory::createWriter($objFileLoader,$FileType);
        $objWriter->save($file_output);
        unset($objReader);
    } //function ends
	
}//class ends
?>