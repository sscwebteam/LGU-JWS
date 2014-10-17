<?php
class cls_user_get { //user getters
    public function ORLogEncoder($OR_Number){
        include_once 'db_conn.php';
        $sql="select * from or_log where or_number like '%{$OR_Number}%'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ". $e->getFile()."__Line: ".$e->getLine());
        $row=mysql_fetch_array($qry);
        return $row['encodedBy'];
    }

    public function CollectorsName(){
        include_once 'db_conn.php';
        $sql="select distinct(encodedBy) from or_log";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            if($row['encodedBy']!=''){ $data[]=$row['encodedBy'];}
        }
        return $data;
    }
    
    public function FullCollectorsName($UserName){
        include_once 'db_conn.php';
        $sql="select fullname from tbl_usr_crd where un='{$UserName}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        $row=mysql_fetch_array($qry);
        return $row['fullname'];
    }
    
    public function isPersonnleLogout($status){
        if($status=='' || $status=='0'){$out='0';}elseif($status=='1'){$out='1';}
        return $out;
    }

    public function CountInactiveC($Barangay=null){ //FFU #23
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        include_once 'cls_codes.php';
        //get all the total concessionaires based on parameter specified
        $totalC=cls_user_get::all_concessionaires_account_no($Barangay);
        $InactiveC=0;
        for($i=0;$i < count($totalC);$i++){
            $LedgerTableName=cls_misc::ConvertToTableName($totalC[$i]);
            if(cls_user_get::isPadLock($LedgerTableName)=='true'){ //count only if the ledger table is in PL status
                $InactiveC=$InactiveC + 1;
            }
        }
        return $InactiveC;
    }

	//[start]	 stage for commit to remote PS (status: uploaded)
	 public function ForBatchBilling($barangay_code,$RowsLimit=null){
		include_once "db_conn.php";
		include_once "cls_codes.php";
		//get all concessionaires from that barangay code specified
		$sql="select * from profile where address_brgy='{$barangay_code}' order by applicant asc";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error($e->getFile()."-".$e->getLine()));
		while($row=mysql_fetch_array($qry)){
			$data[]=$row['acct_no'];
		}
		//assign the current month as readind date of the barangay specified
		$BarangayReadingDate=date('Y-m-').cls_user_get::tblDate_Sched_Value('bryg_codes',$barangay_code,'date_meter_reading');
		//verify if the have not billed yet for the current reading month
		$accounts=$data;
		//$data_out=array();
		for($i=0;$i<count($accounts);$i++){
			$LedgerTable=cls_misc::ConvertToTableName($accounts[$i]);
			$SQL_getBrgyReadingDate="select * from {$LedgerTable} where reading_date='{$BarangayReadingDate}' order by reading_date desc limit 0,1";
			$e=new Exception();
			$QRY_getBrgyReadingDate=mysql_query($SQL_getBrgyReadingDate) or die(mysql_error($e->getFile()."-".$e->getLine()));
			//if not found, add to the account number
			if(mysql_numrows($QRY_getBrgyReadingDate) == 0 && cls_user_get::isPadLock($LedgerTable)!=1){ //FFU#77
				//todo[start] stage for commit to remote PS
				$data_out[]=$accounts[$i];
				//todo[ends] stage for commit to remote PS
			}
		}
		return $data_out;
	}
	//[end] stage for commit to remote PS
	
	public function isPadLock($LedgerTableName){ //checked where the concessionaire is deactivated
		include_once 'db_conn.php';
		$sql="select * from {$LedgerTableName} where remarks like '%PL%'";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
		if(mysql_numrows($qry) > 0){ //concessionaire is padlock
			return true;
		}else{
			return false;
		}
	}
	
	public function brgy_code($account_no){
		include_once 'db_conn.php';
		$sql_str="select * from profile where acct_no='{$account_no}' limit 0, 1";
		$e=new Exception();
		$sql_qry=mysql_query($sql_str) or die(mysql_error($e->getFile()."<br>".$e->getLine()));
		$sql_row=mysql_fetch_array($sql_qry);
		$value=$sql_row['address_brgy']; //address_brgy
		return $value;
	}//show barangay code
	

	public function connection_type($account_no){
		include_once 'db_conn.php';
		$sql_str="select * from profile where acct_no='{$account_no}' limit 1";
		$sql_row=mysql_fetch_array(mysql_query($sql_str) or die(mysql_error()));
		$value=$sql_row['type_connection']; //address_brgy
		return $value;
	} //show connection ty[e

	public function user_info($accnt_no){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$str_conv=new cls_misc();
		$sql_str="select * from profile where acct_no='{$accnt_no}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		?>
		<input type="hidden" name="conn_type" value="<?php echo trim($row['type_connection'])?>">
		<table border="0" cellspacing="0" cellpadding="0" width="100%" >
		<tbody>
		<tr align="center">
			<td align="center" colspan="2"><p align="center"><b>CONSUMER INFORMATION</b></p></td>
		</tr>
		<tr>
			<td>Name:&nbsp;<b><?php echo $row['applicant'] ?></b></td>
			<td>Account No.:&nbsp;<b><?php echo $row['acct_no']?></b></td>
		</tr>
		<tr class="even">
			<td>Address:&nbsp;<b><?php echo $str_conv->toString ($row['address_brgy'],'Barangay')?></b></td>
			<td>Account Type:&nbsp;<b><?php echo $str_conv->toString(trim($row['type_connection']),'connection_type')?></b> </td>
		</tr>
	</tbody>
</table>
		<?php
	} //show user infor
	
	//[start] stage for commit to remote PS(code status:uploaded)
	public function tblDate_Sched_Value($TargetColumnName,$criteria,$OutputColumnName){
		include_once 'db_conn.php';
		$sql="select * from dates_sched where {$TargetColumnName}='{$criteria}'";
		$qry=mysql_query($sql) or die(mysql_error());
		$row=mysql_fetch_array($qry);
		$data=$row["{$OutputColumnName}"];
		return $data;
	}
	//[end] stage for commit to remote PS
	public function reading_info($accnt_no){
		include_once 'date_time.php';
		?>
		<table border="0" cellspacing="0" cellpadding="0" width="100%" >
			<tbody>
			<tr align="center">
				<td align="center" colspan="4"><p align="center"><b>METER READING INFORMATION</b></p></td>
			</tr>
			<input type="hidden" name="prev_reading_value" value="<?php echo cls_date_get::last_reading_info($accnt_no,"meter_reading")?>">
			<tr>
				<td>Last Reading Date:&nbsp;</td><td><b><?php echo cls_date_get::last_reading_info($accnt_no,"reading_date")?></b></td>
				<td>Meter Reading Value:&nbsp;</td><td><b><?php echo cls_date_get::last_reading_info($accnt_no,"meter_reading")?></b></td>
			</tr>
			<tr>
				<td>Present Reading Date:&nbsp;</td><td><b><input type="text" name="curr_reading_date" value="<?php echo   cls_date_get::bool_date_render(cls_date_get::date_add(cls_date_get::last_reading_info($accnt_no,"reading_date"),"month","1"))?>", readonly> </b></td>
				<td>Current Reading Value:&nbsp;</td><td><input type="text" name="curr_read_value" size="20" maxlength="20" <?php
			if (cls_date_get::bool_date_render(cls_date_get::date_add(cls_date_get::last_reading_info($accnt_no,"reading_date"),"month","1"))=="Date not Scheduled") { echo "readonly"; }
		?>></td>
			</tr>
			</tbody>
		</table>
		<?php
	} //show reading info

	public function additional_payments($accnt_no,$reading_date=null){
		include_once 'cls_user.php';
		$meter_fee=explode('|',cls_user_get::meter_fee($accnt_no,$reading_date));
        $add_mlp=cls_user_get::BillAddons($accnt_no,$reading_date,"MLP");
		?>
		<table class="art-article" border="0" cellspacing="0" cellpadding="0" width="100%">
			<input type="hidden" name="billing_count" value="<?php echo $meter_fee[1]?>">
			<tbody>
				<tr align="center">
					<td align="center" colspan="3"><p align="center"><b>ADDITIONAL PAYMENTS</b></p></td>
				</tr>
				<tr>
					<td align="right">Meter Fee</td>
					<td align="left"><input type="text" name="add_meter_fee" size="10" maxlength="10" value="<?php echo $meter_fee[0]?>"></td>
				</tr>
				<tr>
					<td align="right">Material Loan Program</td>
					<td align="left"><input type="text" name="add_mlp" size="10" maxlength="10" value="<?php echo $add_mlp; ?>"></td>
				</tr>
				<tr>
					<td align="right">Reconnection Fee</td>
					<td align="left"><input type="text" name="add_recon_fee" size="10" maxlength="10"></td>
				</tr>
			</tbody>
		</table>
		<?php
	} //looks for additional payment

	public function BillAddons($account_no,$reading_date,$addon_type){
        include_once 'db_conn.php';
        $sql_str="select * from installment where acc_no='{$account_no}' and billed_date='{$reading_date}' and addons_type='{$addon_type}'";
        $e=new Exception();
        $sql_qry=mysql_query($sql_str) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
        $row=mysql_fetch_array($sql_qry);
        if (mysql_num_rows($sql_qry)>=1) {//data found for billing
            $value=$row['installment_value'];
        }else {
            $value='00.00';
        }
        return $value;
        
    }
    
    public function meter_fee($account_no,$reading_date=null){
		include_once 'db_conn.php';
		$sql_str="select * from installment where acc_no='{$account_no}' and billed_date='{$reading_date}' and addons_type='met_fee'";
        $e=new Exception();
		$sql_qry=mysql_query($sql_str) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
		$row=mysql_fetch_array($sql_qry);
		if (mysql_num_rows($sql_qry)>=1) {//data found for billing
			$value=$row['installment_value'].'|'.$row['bill_count'];
		}else {
			$value='00.00';
		}
		return $value;
	} //compute meter fee

	public function billing_date($account_no){ //returns the billing date of the consumer
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$sql_str="select * from ldg_".cls_misc::sanitize_hyp($account_no)." order by reading_date desc";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		$last_reading=$row['reading_date'];
		$sql_str2="select date_add('{$last_reading}',interval 1 month) as billing_date";
		$sql_qry2=mysql_query($sql_str2) or die(mysql_error());
		$row2=mysql_fetch_array($sql_qry2);
		return $row2['billing_date'];
	} //returns the billing date of the consumer

	public function const_payment_date($account_no){ //returns the constant payment date such as date=1,date=2,date=3
		include_once 'db_conn.php';
		$brgy_code=trim(cls_user_get::brgy_code($account_no));
		$sql_str="select * from dates_sched where bryg_codes='{$brgy_code}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['date_payment'];
	} //returns the constant payment date such as date=1,date=2,date=3

	public function login(){ //force login form
		include_once 'db_conn.php';
		include_once 'forms.php';
		cls_forms::frm_login_generic();
	} //force user to login

	public function chk_login(){ //check login status
		include_once 'cls_user.php';
		if(!isset($_SESSION['profileid']) || ($_SESSION['profileid'] < 2)){
			$login_status='0'; //user not login
		}else{
			$login_status='1';
		}
		return $login_status;
	}
		
	 //check login variables from cookie

	public function chk_login_value($username,$userpwd){ //validate login values passed by user parameters
		include_once 'db_conn.php';
		include_once 'forms.php';
		
		
		//$expire=time() + 3600; //expire after 1 hour
		$sql_str="select * from tbl_usr_crd where un='{$username}' and pwd='{$userpwd}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		if (mysql_num_rows($sql_qry)==1) { //data found
			//setcookie('jws_login',base64_encode('1'),$expire);
			//setcookie('jws_user',base64_encode("{$username}"),$expire);
			cls_forms::error_msg('Login Successful','main.php');

		}else{
			//setcookie('login',base64_encode('0'),$expire);
			cls_forms::error_msg('Login Unsuccessful','main.php');
		}

	} //process user login credentials passed

	public function logout(){
		setcookie('login','0',time()-300);
		setcookie('user','',time()-300);
		//unset cookies here or destroy cookies here
		//$_COOKIE['login']="0";


		header('location:main.php');
	}
	
	public function bool_priv_action($menu_privilege_value){ //assign privilege here for action menu
		include_once 'db_conn.php';
	
		//$un=base64_decode(trim($_COOKIE['jws_user']));//username
		#replaced by sesion
		$un = $_SESSION['username'];
		$sql_str1="select * from tbl_usr_crd where un='{$un}'";
		$sql_qry=mysql_query($sql_str1) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		$ofc_name=$row['ofc_name']; //get only the originating office name
/*		$sql_str2="select * from codes where category='ofc_type' and code='{$ofc_name}'";
		$sql_qry2=mysql_query($sql_str2) or die(mysql_error());
		$row2=mysql_fetch_array($sql_qry2); 
		$ofc_type_code=$row2['code']; //get the office type code to validate with the second paramaeter
	
		if($menu_privilege_value==$ofc_type_code){
			$bool_value="1"; //grant privilege
		}else{
			$bool_value="0";
		}
		if($ofc_name=="1"){ //super_admin detected,override values
			$bool_value="1";
		}
			return $bool_value; */
		return $ofc_name;
	}
	
	//[start] stage for commit to remote PS (code status:uploaded)
	public function all_concessionaires_account_no($barangay_code=null){ //returns all concessionaires account number
		include_once 'db_conn.php';
		if($barangay_code==null || $barangay_code==''){ 
			$sql_str="select acct_no from profile order by applicant asc";
			//echo "test: null parameter executed<br>";
			}elseif($barangay_code != null){
			$sql_str="select address_brgy,acct_no from profile where address_brgy='{$barangay_code}' order by applicant asc";
		}
		$e=new Exception();
		$sql_qry=mysql_query($sql_str) or die(mysql_error($e->getFile()."<br>". $e->getLine()));// mysql_error());
		while($row=mysql_fetch_array($sql_qry)){
			$data[]=$row['acct_no']   ;
		}
		//echo "test data count for general=".count($data);
		return $data;
	} //returns all concessionaires account number
	
	public function ProfileValue($TargetColumnName,$criteria,$OutputColumnName){ //returns part of the table Profile based on criteria submitted
		include_once 'db_conn.php';
		$sql="select * from profile where {$TargetColumnName}='{$criteria}'";
		$qry=mysql_query($sql) or die(mysql_error());
		if(mysql_numrows($qry)){
			$row=mysql_fetch_array($qry);
			$data=$row["{$OutputColumnName}"];
		}else{
			$data='0';
		}
		return $data;
	}//returns part of the table Profile based on criteria submitted
	
	//[end]stage for commit to remote PS
	
	public function ForDisconnectionNotice(){ //get all users that is for disconnection notice
		include_once 'db_conn.php';
		$sql_str="select accnt_no,count(accnt_no) from for_disconn group by accnt_no having count(accnt_no)>2 order by count(accnt_no) desc";
		$qry_str=mysql_query($sql_str) or die(mysql_error());
		while($row=mysql_fetch_array($qry_str)){
			$data[]=array('accnt_no'=>$row['accnt_no'],'count'=>$row['count(accnt_no)']);
		}
		return $data;
	}//get all users that is for disconnection notice
	
}
//----------------------------------SETTERS----------------------------------------------
class cls_user_set extends cls_user_get { //user setters
    public function removePL($AccountNo){ //FFU #21
        include_once 'cls_codes.php';
        $LedgerTable=cls_misc::ConvertToTableName($AccountNo);
        $sql="update {$LedgerTable} set remarks='' where remarks like '%PL%'";
        $e=new Exception();
        mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
    }

	public function meter_fee_billed_date($account_no,$billing_count){
		include_once 'db_conn.php';
		//get billing date for specific account number
		$billed_date=parent::billing_date($account_no);
		$sql_str="update installment set billed_date='{$billed_date}' where bill_count='{$billing_count}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		//done
	}

	public function ledger_value_ins($account_no,$field,$value){
		//echo "ledger_value_insert invoked!<br>";
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$account_no=cls_misc::sanitize_hyp($account_no);
		$sql_str="insert into ldg_{$account_no}({$field})values('{$value}')";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
	}

	public function ledger_value_update($account_no,$field,$value,$operator,$ref_field,$ref_value){
		//echo "ledger_value_update invoked!<br>";
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$account_no=cls_misc::sanitize_hyp($account_no);
		$sql_str="update ldg_{$account_no} set {$field}='{$value}' where {$ref_field}{$operator}'{$ref_value}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
	}
}
?>