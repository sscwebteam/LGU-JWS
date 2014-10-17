<?php
ob_start();
if (ob_get_contents()!='') {
	ob_get_clean();
	session_start();
}
session_start();
?>
<?php
	include_once 'header.php';
	include_once 'menu.php';
	include_once 'juban_functions.php';

?>
<!--center content starts!-->
<div class="art-content-layout">
	<div class="art-content-layout-row">
		<div class="art-layout-cell art-content">
<div class="art-post">
	<div class="art-post-body">
<div class="art-post-inner art-article">
	<h2 class="art-postheader"><!--Billing!--></h2>
				<div class="cleared"></div>
<div class="art-postcontent">

<?php
//algorithms for bill postings related
switch (strtolower($_POST['bill'])) {
	//[start] stage for commit to remote PS (status:uploaded)
	case 'createreadingform':
		include_once 'cls_user.php';
		$barangay_code=$_POST['barangay_code'];
		$NoOfConcessionaires=count(cls_user_get::ForBatchBilling($barangay_code));
		?>
		<center><?php //todo: [start] stage for commit to remote PS(done) ?>
		<span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="billing.php?custom=BatchBilling&brgy=<?php echo $barangay_code ?>" class="art-button">Batch BillingForm Had Been Prepared for <?php echo $NoOfConcessionaires ?> Concessionaires</a></span>
		<?php //todo: [end] stage for commit to remote PS(done) ?>
		</center>
		<?php
		break;
	//[end] stage for commit to remote PS
	
	//[start] stage for commit to remore PS (status: )
	case 'createreadingsheet': //create reading sheet for the month of the current year
		set_time_limit(0); ini_set("memory_limit",-1);
        include_once 'cls_bill.php';
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
        
		$barangay_code=$_POST['barangay_code'];
        //create reading sheet file
		cls_bill_set::reading_sheet_form2(trim($barangay_code)); //todo:FFU#73
		//get the filename
		$rs_filename= "accnts/rs_".date('Y-m')."_".$barangay_code.".xls";
		$DateNow=date('Y-m-d');
		$MonthNow=date('Y');
		$strMonth=cls_misc::toString(date('m'),'month');
		//check for duplication and insert if duplication not found
		//DONT IMPLEMENT THE CODES BELOW FOR PHP VER < 5.3 DUE TO RS_FILENAME VARIABLE CONTAINS FORWARD SLASH CHARACTER
/*		if(cls_misc::CheckDuplication_OpenSQL("select * from rs_log where rs_filename='{$rs_filename}'")=='0'){
			$SqlIns="insert into rs_log(rs_filename,rs_date_created)values('{$rs_filename}','{$DateNow}')";
			$e=new Exception();
			$QryIns=mysql_query($SqlIns) or die(mysql_error(mysql_error($e->getFile()."-".$e->getLine())));
		}
*/		?>
		<table align="center" width="100%">
			<tr><td><center>Reading Sheet for Barangay <?php echo cls_misc::toString($barangay_code,'Barangay')." for the Month of {$strMonth}-{$MonthNow} has been Created. <br>Please click the button below"?></center></td></tr>
			<tr><td><center><span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="<?php echo $rs_filename ?>" class="art-button">Download</a></span></center></td></tr>
		</table>
		<?php
		
		break;
	//[start] stage for commit to remore PS
	
	case 'show_brgy'://show barangay for download billing
		include_once 'db_conn.php';
		include_once 'forms.php';
		//show billing for downloads
		$brgy_code=trim($_POST['brgy_opt']);
        $year=trim($_POST['year']);
        $month=trim($_POST['month']);
		//transfer the process to forms for listings
		cls_forms::show_accnt_billings($brgy_code,$year,$month);
		break;
    
    case 'show_all_bills':
        include_once 'db_conn.php';
        include_once 'forms.php';
        $FilterBrgy=$_POST['brgy_opt'];
        $FilterYear=$_POST['year'];
        cls_forms::show_accnt_billings($FilterBrgy,$FilterYear);
        break;

	case 'pass_accnt':
		include_once 'db_conn.php';
		include_once 'forms.php';
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
        include_once 'date_time.php';
        
		$accnt_no=$_POST['accnt_no'];
		$sql_str="select * from profile where acct_no='{$accnt_no}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error('sql_error on line 34'));
		if (mysql_num_rows($sql_qry)!=1) { //data not found
			cls_forms::error_msg("Account Number Not Found!","billing.php?request=accnt");
		}elseif (mysql_num_rows($sql_qry)==1){ //data found
			//get the consumer paramaters:done
			if (cls_misc::bool_billing_done($accnt_no)=='false') {
			?>
				<form name="form1" action="form_handler.php" method="POST">
				<input type="hidden" name="bill" value="save_current_reading">
				<input type="hidden" name="accnt_no" value="<?php echo $accnt_no?>">
				<?php
                $reading_date=cls_date_get::bool_date_render(cls_date_get::date_add(cls_date_get::last_reading_info($accnt_no,"reading_date"),"month","1"));
				cls_user_get::user_info($accnt_no);
				cls_user_get::reading_info($accnt_no);
				cls_user_get::additional_payments($accnt_no,$reading_date);

				?><p align="right"><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Save</a></span> </p>
				</form><?php
			}else {
				?><h2><font color="Red">Billing is Done!</font></h2>
				<?php
			}
		}
		break;

	case 'save_current_reading':
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
			//--ADDITIONAL PAYMENTS
			$add_meter_fee=$_POST['add_meter_fee'] + $add_recon_fee=$_POST['add_recon_fee'];
			$add_mlp=$_POST['add_mlp'] + 0;
			//$add_recon_fee=$_POST['add_recon_fee'];

			//insert ledger value
			cls_user_set::ledger_value_ins($accnt_no,"reading_date",$reading_date);
			//update ledger value based on reference
			cls_user_set::ledger_value_update($accnt_no,"meter_reading",$curr_read_value,"=","reading_date",$reading_date);
			cls_user_set::ledger_value_update($accnt_no,"cu_used",$cubic_used,"=","reading_date",$reading_date);
			//update installment table based on parameters
			cls_user_set::meter_fee_billed_date($accnt_no,$billing_count);
			//update bill_amnt
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
            
			cls_user_set::ledger_value_update($accnt_no,'bill_amnt', $bill_amnt,"=","reading_date",$reading_date);
			//update ledger for additional payments
			cls_user_set::ledger_value_update($accnt_no,'loans_MLP',$add_mlp,"=","reading_date",$reading_date);
			cls_user_set::ledger_value_update($accnt_no,'loans_MF',$add_meter_fee,"=","reading_date",$reading_date);
			//sum up total bill to date
			cls_bill_get::total_bill_amount($accnt_no,$biling_date);
			//finalize data entry for acceptance
		 
		 ?> 
		 <h2>
		  Download bill for account number: <?php echo $accnt_no;?>
		<span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span>
		 <a href="download_billing.php?request=init_dl&accnt_no=<?php echo base64_encode($accnt_no)?>&last_bill=<?php echo base64_encode($reading_date)?>" class="art-button">Download</a></span> 
		 </h2>   <?php  
		}else {
			?>
			<script type="text/javascript">
				alert("saving of data not allowed");
			</script>
			<?php
			header("location:billing.php?request=accnt");

		}
		break;


}
//--------------------------------algorithm end for billing
switch ($_POST['cashier']) {
	case 'login':
		include_once 'cls_user.php';
		//variables:usn,usp
		$usn=trim($_POST['usn']);//username
		$usp=md5(trim($_POST['usp']));//userpassword
		cls_user_get::chk_login_value($usn,$usp);
		
		//echo 'user login from cashie invoked!';
		break;

	case 'show_ledger':
		include_once 'db_conn.php';
		include_once 'forms.php';
		//$_POST['accnt'];
		//variables:accnt
		//show ledger form passing account number
		cls_forms::show_ledger(trim($_POST['accnt']));
		break;

	case 'payment':
		//start includes
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		include_once 'cls_user.php';
        //start varaibles
		$billing_month=$_POST['billing_month'];
		$total_amount=str_replace(",","",$_POST['total_amnt']);
		$or_date=$_POST['or_date'];
		$or_no=$_POST['or_no'];
		$accnt_no=$_POST['acnt_no'];
		$penalty=$_POST['penalty'];
		//start procedures
		$consumer_ledger='ldg_'.cls_misc::sanitize_hyp($accnt_no);
		#insert to or_log
        $encodedBy=$_SESSION['username'];
		$sql_str=mysql_query("insert into or_log(or_number,or_date,issued_to_accnt,issued_amnt,encodedBy,remarks)values('".$or_no."','".$or_date."','".$accnt_no."','".$total_amount."','{$encodedBy}','PAID')") or die('Error in inserting to logs').mysql_error();
		//echo "consumer ledger={$consumer_ledger}";
		$sql_str="update ".$consumer_ledger." set pen_fee='{$penalty}',total='{$total_amount}',OR_num='{$or_no}',OR_date='{$or_date}' where reading_date='{$billing_month}'";
		//echo "sql_str=".$sql_str;
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		//update the for disconn table to update the sidebar for disconnection notice
		mysql_query("delete from disconn_dates where accnt_no='".$accnt_no."' and reading_date='".$billing_month."'") or die('Error in deleteing discoonection notice');
        //FFU #22 : update ledger table to clean
        if(cls_user_get::isPadLock($consumer_ledger)==true){ //user is in PL status
            //remove the PL status
            cls_user_set::removePL($accnt_no);
        }
		?>
		<script type="text/javascript">alert('data saved')</script>
		<?php
		break;

	default:
		break;
}

switch ($_REQUEST['cashier']){
	case 'show_ledger1':
		include_once 'forms.php';
		//$account=$_REQUEST['accnt'];
		cls_forms::show_ledger(trim($_REQUEST['accnt']));
	break;
}

switch ($_POST['generic']) {
	case 'login':
		include_once 'db_conn.php';
		include_once 'cls_user.php';
		$user_name=trim($_POST['usn']);
		$user_pwd=md5(trim($_POST['usp']));
		//pass to cls_user for verification
		//manifest action for return value
		cls_user_get::chk_login_value($user_name,$user_pwd);
		break;

	default:
		break;
}

//administrative functions only
switch ($_POST['admin']) {
	case 'user_action':
		include_once 'db_conn.php';
		include_once 'forms.php';
		//variables listings
		$action=$_POST['action'];
		$user_type=$_POST['utype'];
		$user_id=$_POST['user_id'];
		$user_name=$_POST['UserName'];
		$password1=$_POST['UserPass'];
		$password2=$_POST['UserPass2'];
		$password3=$_POST['UserPass3'];
		$full_name=$_POST['emp_name'];
		$ofc_name=$_POST['ofc_name'];
			#added in case blank profile id;
			if($_POST['profile_id']==""){
			$profile_id=$_POST['ofc_name']; 
			}else{
			$profile_id=$_POST['profile_id'];}			
		if($action=='add'){
			//validate data submitted
			if($password1!=$password2){ $error="passwords are not identical<br>";$error_count=$error_count+1;}
			if($user_name==''){$error=$error."No username specified<br>";$error_count=$error_count+1;}
			if($full_name==''){$error=$error."No employee name specified<br>";$error_count=$error_count+1;}
			if($ofc_name==''){$error=$error."No Office name selected<br>";$error_count=$error_count+1;}
			if($error_count==0){ //all forms validated
				 $sql_str1="insert into tbl_usr_crd(un,pwd,fullname,profile_id,ofc_name)values('{$user_name}','{$password1}','{$full_name}','{$profile_id}','{$ofc_name}')";
				 $sql_qry1=mysql_query($sql_str1) or die(mysql_error());
				 //inform user that the data had been save...
				 cls_admin_forms::msg("New User Data Saved!","blue");
				 break;
			}else{ //errors occur
				 cls_admin_forms::msg("'{$error_count}'&nbsp;Error Occurs:Please Complete the Data<br><br>"."
				 <a href=\"javascipt:history.go(-1)\">Back</a>","red");
				 break;
			}
		}elseif($action=='edit'){
			//edit user data
			if($password2!=$password3){ $error="new passwords are not identical<br>";$error_count=$error_count+1;}
			if($user_name==''){$error=$error."No username specified<br>";$error_count=$error_count+1;}
			if($full_name==''){$error=$error."No employee name specified<br>";$error_count=$error_count+1;}
			if($ofc_name==''){$error=$error."No Office name selected<br>";$error_count=$error_count+1;}            
			if($error_count==0){ //no error occurs
				$sql_str1="update tbl_usr_crd set un='{$user_name}',pwd='{$password2}',fullname='{$full_name}',ofc_name='{$ofc_name}',profile_id='{$ofc_name}' where un='{$user_id}'";
				$sql_qry1=mysql_query($sql_str1) or die(mysql_error());
				cls_admin_forms::msg("User Data Updated!","blue");
				break;
			}else{ //error occurs
				cls_admin_forms::msg("<a href=\"javascript:history.go(-1)\">Data Submitted contains '{$error_count}' error(s):Please Checked!</a>","red");
				break;
			}
		}
		
		break;

	default:
		break;
}




?>
				</div>
				<div class="cleared"></div>
				</div>

		<div class="cleared"></div>
	</div>
</div>

					  <div class="cleared"></div>
					</div>
<?php
	//process the sidebar for personal information of the user based on ledger such as number of accounts to be unsettled,etc
	include_once  'sidebar.php';
	include_once  'footer.php';
?>