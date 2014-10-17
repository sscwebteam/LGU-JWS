<?php
  //reserve for ajax authentications request and posting of data
error_reporting(E_ALL ^ E_NOTICE);
if($_SERVER['HTTP_X_REQUESTED_WITH']!='XMLHttpRequest'){echo "non-ajax request detected";die();}
  ?>

<?php
//------------------------------------------------------------------------------------------------------------------
	switch( strtolower($_POST['bill'])){
		//todo -o mike -p 10 -c For Upload [start] Coding status (in progress)
		case 'getmiscfeeavail':
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            $AccntNo=explode('|',$_POST['accnt_names']);
            $sql="select SUM(installment_value) as total_misc from installment where acc_no='{$AccntNo[1]}' and addons_type='misc_fee'";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            $row=mysql_fetch_array($qry);
            $total_value=$row['total_misc'];
            if($total_value=='' || $total_value==null){ //contains data,transport to ajax for process
                echo "false";
            }else{
                echo $row['total_misc'];
            }
            break;
        //todo -o mike -p 10 -c For Upload [end] Coding status(in progress)
        case 'batchbilling':
			include_once 'cls_bill.php';
			cls_bill_set::BatchBillingComputation($_POST);
			break;
		
		case 'bill_amount':
			include_once 'cls_bill.php';
            include_once 'cls_codes.php';
			$wcu_used=trim($_POST['cu_used']);
            //todo: FFU#57
            $AccntNo=trim($_POST['AccntNo']);
			$bill_amnt=cls_bill_get::compute_bill_amount($AccntNo,'',$wcu_used,'1');
          //render the results to ajax
            echo $bill_amnt;
			break;
	}
	
	//------------------------------------------------------------------------------------------------------------------
	switch (strtolower($_POST['cashier'])){
		case 'payment':
			include_once 'cls_bill.php';
			$AccountNO=$_POST['acnt_no'];$OR_No=$_POST['or_no'];
			$QryResults=cls_bill_get::OR_Duplicate($OR_No,$AccountNO);
			if($QryResults=='|'){ //no results found
				echo "ok";
			}else{ echo $QryResults; }
			break;
		
		case 'settle_other_payments':
		//todo: continue codings on handling other payments
            echo 'this is server results';
			break;
        
        case 'search_or':
            set_time_limit(0);
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            include_once 'cls_user.php';
            $accounts= cls_user_get::all_concessionaires_account_no();
            $or_num=trim($_POST['or_num']);
            //prepare headers
            
            $layout= "<center><table align=\"center\" width=\"80%\"><tr><td colspan=\"5\"><hr></td></tr><tr><td>OR No.</td><td>OR Date</td><td>Issued to</td><td>Issued Amnt</td><td>Encoded By</td></tr><tr><td colspan=\"5\"><hr></td></tr>";
/*            $sql="select * from or_log where or_number like '{$or_num}%' order by or_date asc,or_number asc";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File:&nbsp;".$e->getFile()."__Line:&nbsp;".$e->getline());
            if(mysql_numrows($qry) > 0){
                
                while($row=mysql_fetch_array($qry)){
                    $Amnt=cls_misc::gFormatNumber($row['issued_amnt']);
                    echo "<tr><td>{$row['or_number']}</td><td>{$row['or_date']}</td><td>{$row['issued_to_accnt']}</td><td>{$Amnt}</td><td>{$row['encodedBy']}</td></tr>";
                }
            }
*/
            
                for($i=0;$i < count($accounts);$i++){
                //echo $accounts[$i]."<br>";
                $LedgerAccnt=cls_misc::ConvertToTableName($accounts[$i]);
                $sql="select * from {$LedgerAccnt} where OR_num like '{$or_num}%'";
                $qry=mysql_query($sql);
                if(mysql_numrows($qry)> 0){
                    while($row=mysql_fetch_array($qry)){
                        $AccountName=cls_user_get::ProfileValue('acct_no',$accounts[$i],'applicant');
                        $TotalAmount=cls_misc::gFormatNumber($row['total']);    
                        $Encoder=cls_user_get::ORLogEncoder($or_num);
                        $cnt=+1;
                        $layout.= "<tr><td>{$row['OR_num']}</td><td>{$row['OR_date']}</td><td><a href=ledger_content.php?action=ledger_scan&account_no={$accounts[$i]}> {$AccountName}</a></td><td>{$TotalAmount}</td><td>{$Encoder}</td></tr>";
                    }
                }
            }
            $layout.= "</table></center>";
            
            
            $Tag=$_POST['bSearch']; //select the data to be returned
            if($Tag=='0'){
                echo $layout;
            }elseif($Tag=='1'){
                echo $cnt;
            }
            break;		
	}
	//------------------------------------------------------------------------------------------------------------------
//todo[start] stage for commit to remote PS(status:done)
	switch (strtolower($_POST['misc'])){
        //todo -o mike -p 10 -c For Upload [start] 
        case 'getlastreadingdates':
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            $accnt=explode('|',$_POST['accnt_names']);
            $terms=$_POST['terms'];
            $accnt_no=$accnt[1];
            $LedgerTable=cls_misc::ConvertToTableName($accnt_no);
            $sql="select * from {$LedgerTable} order by reading_date desc limit 1";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            $row=mysql_fetch_array($qry);
            $LastDate=$row['reading_date'];
            $arrDates=array();
            for($i=1;$i<=$terms;$i++){
                $sql=$sql_str="select date_add('{$LastDate}',interval {$i} month) as distribution_dates";
                $qry=mysql_query($sql) or die(mysql_error());
                $row=mysql_fetch_array($qry);
                $arrDates[]=$row['distribution_dates'];
                //$dates[]=$arrDates;
            }
            //echo "print_r values=".print_r($arrDates);
/*            for($i=0;$i <= count($arrDates);$i++){
                $dates=$arrDates[$i]."|".$dates;                
                //$dates+=$dates."|";
            }
*/            echo json_encode($arrDates);
            break;
            //todo -o mike -p 10 -c For Upload [end]
            
		case 'getoptbrgy':
			include_once 'db_conn.php';
			include_once 'cls_user.php';
			include_once 'cls_codes.php';
			$values=cls_misc::getOptBarangayNames();
			$AccountNo=$_POST['accnt_no'];
			$objName=$_POST['objName'];
			$UserBrgyValue=cls_user_get::ProfileValue('acct_no',$AccountNo,'address_brgy');
			?>
			<select name="<?php echo $objName ?>">
				<?php foreach($values as $key=>$values){?>
					<option value="<?php echo $values['codes_value']?>" <?php if($UserBrgyValue==$values['codes_value']){echo "selected";}?>>
					<?php echo $values['descr_value']?> </option>
				<?php } ?>
			</select>			<?php
			break;
	}
    
    switch (strtolower($_POST['report'])){
        case 'jws1': //FFU#40
            include_once 'jws_report1.php';
            $Parameters=$_POST['year_value']."-".$_POST['month_value'];
            jws_reporting1::fnEntry($Parameters);
            break;
    
    }
    

    switch (strtolower($_POST['payment'])){
        case 'other_payment': //FFU#40
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            //parameter variables
            $AccntNo=explode('|',$_POST['payee']);
            $other_payments=$_POST['other_payments'];
            $amount=$_POST['amnt'];
            $or_num=$_POST['or_num'];
            $or_date=$_POST['or_date'];
            
            //local variables
            $AppFeeFull=2075;
            $LedgerTable=cls_misc::ConvertToTableName($AccntNo[1]);
            
            //reading date preparations
            $sql="select * from {$LedgerTable}";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
            $e=new Exception();
            $row=mysql_fetch_array($qry);
            $InitialReadingDate=$row['reading_date'];
            echo "<br>Reading date captured={$InitialReadingDate}";
            
            //start payment classification
            if($other_payments=='app-fee-par'){//user initiate partial payment FFU#49
                    $ForLoansMF=($AppFeeFull - $amount)/3;
                    $install_type='met_fee';
                    $sql="select * from {$LedgerTable}";
                    $e=new Exception();
                    $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                    $e=new Exception();
                    $row=mysql_fetch_array($qry);
                    $InitialReadingDate=$row['reading_date'];                    
                    for($i=1;$i<3;$i++){
                        $BilledDate=cls_misc::DateAdd($InitialReadingDate,1,'month');
                        //automatic insert to the table installment
                        $sql="insert into installment(installment_value,billed_date,addons_type)values('{$ForLoansMF}','{$BilledDate}','{$install_type}')";
                        $e=new Exception();
                        mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                        $sql="update {$LedgerTable} set AF='{$amount}' where reading_date='{$InitialReadingDate}'"; //insert into
                        $e=new Exception();
                        mysql_query($sql) or die(mysql_error()."____File: ".$e->getFile()."____Line: ".$e->getLine());
                      
                    }
                        include_once 'cls_user.php';
                        cls_user_set::NewRegCons_OrNo($AccntNo[1],$or_num); //insert the OR number to the profile registration
               //notes:
                    /*
                    * if the user ask for partial fee, the difference from the $AppFeeFull will be staggered to loans_MF automatically
                    */
            }elseif($other_payments=='app-fee-full'){ //full cash payment as initiated by the user FFU#48
                    $Payment=$AppFeeFull - ($AppFeeFull * 0.10);
                    $sql="select * from {$LedgerTable}";
                    $e=new Exception();
                    $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                    $e=new Exception();
                    $row=mysql_fetch_array($qry);
                    $InitialReadingDate=$row['reading_date'];
                    $sql="update {$LedgerTable} set AF='{$Payment}' where reading_date='{$InitialReadingDate}'";
                    $e=new Exception();
                    //rewrite to ledger table
                    mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                    //insert data to table or_log
                    $sql="insert into or_log(or_number,or_date,issued_to_accnt,issued_amnt,encodedBy)values('{$or_num}','{$or_date}','{$AccntNo[1]}','{$amount}','{$_SESSION['username']}')";
                    $e=new Exception();
                    mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                    include_once 'cls_user.php';
                    cls_user_set::NewRegCons_OrNo($AccntNo[1],$or_num); //insert the OR number to the profile registration
                    
            }elseif($other_payments=='met_fee'){
                //codes here
            }elseif($other_payments=='MLP'){//FFU#52
                    //codes here
            }elseif($other_payments=='misc_fee'){
                    //codes here
            }
            
            break;
    }

    switch(strtolower($_POST['utility'])){
        
        case 'general_search_profile':
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            $term=trim($_POST['term']);
            $operator=" OR " ;
            $table="<center><table width='80%'>
            <tr>
                <td>Concessionaire Name</td>
                <td>Account No</td>
                <td>Serial No</td>
                <td>Date Installed</td>
                <td>Barangay</td>
                <td>Brand</td>
                <td>Meter No</td>
            </tr><tr><td colspan='8'>&nbsp;</td></tr>";
            $crit="acct_no like '%{$term}%'".$operator;
            $crit.="applicant like '%{$term}%'".$operator;
            $crit.="date_applied like '%{$term}%'".$operator;
            $crit.="serial_no like '%{$term}%'".$operator;
            $crit.="date_installed like '%{$term}%'".$operator;
            $crit.="meterno like '%{$term}%'";
            $sql="select * from profile where {$crit} order by applicant asc";
            //echo $sql;
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
            if(mysql_numrows($qry)!=0){
                $count=mysql_numrows($qry);
                while($row=mysql_fetch_array($qry)){
                    $barangay=cls_misc::toString($row['address_brgy'],'Barangay');
                    $table.="<tr><td>{$row['applicant']}</td>
                             <td>{$row['acct_no']}</td>
                             <td>{$row['serial_no']}</td>
                             <td>{$row['date_installed']}</td>
                             <td>{$barangay}</td>
                             <td>{$row['brand']}</td>
                             <td>{$row['meterno']}</td>
                             </tr>";
                }
                //echo "mysql server response, there were {$count} records found!";
            }else{
                $table.="<tr><td>No Related data found!</td></tr>";
            }
            $table.="</table>";
            echo $table;
            break;
        
    }

    //todo[end] stage for commit to remote PS
?>