
<?php
error_reporting(E_ALL ^ E_NOTICE);
class cls_admin_forms{ //class for administrative page only

	public function add_edit($user_id,$action){ //function_edit_add_user values
		include_once 'db_conn.php';                                                                                          
		include_once 'forms.php';
		if($action=='edit'){
			$sql_str1="select * from tbl_usr_crd where un='{$user_id}'";
			$sql_qry1=mysql_query($sql_str1) or die(mysql_error());
			$row=mysql_fetch_array($sql_qry1);
		}
		?>
		<form name="form1" method="POST" action="form_handler.php">
		<input type="hidden" name="utype" value="2">
		<input type=hidden name="admin" value="user_action">
		<?php if($user_id!=''){ ?><input type="hidden" name="user_id" value="<?php echo $user_id?>"> <?php } ?> 
		<?php if($action=='add'){
			//declare all the ouotgoing variables for use in processing module under form_handler.php (see variables below)
			// post['admin']
			//case:user_action
			//condition:add
			?>
			<!--declaration starts here!-->
			<input type="hidden" name="action" value="add">
			<?php   
		}elseif($action=="edit"){
			//declare all the ouotgoing variables for use in processing module under form_handler.php
			// post['admin']
			//case:user_action
			//condition:edit
			?><!--declaration starts here!-->
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="user_id" value="<?php echo $row['un']?>">
			<input type="hidden" name="profile_id" value="<?php echo $row['profile_id']?>">
			<?php
		} ?>
			<table border="0">
			<tr><td>Username:&nbsp;</td><td><input type="text" size="50" maxlength="50" name="UserName" value="<?php echo $row['un']?>"></td> </tr>
			<tr><td><?php if($action=='edit'){echo "Old";}?>&nbsp;Password:&nbsp;</td><td><input type="password" name="UserPass" value="<?php echo $row['pwd']?>" maxlength="50" size="50"/></td></tr>
			<tr><td><?php if($action=='add'){echo "Confirm";}else{echo "New";}?>&nbsp;Password:&nbsp;</td><td><input type="password" name="UserPass2" size="50" maxlength="50"/></td></tr>
			<?php if($action=='edit'){ ?>
			<tr><td>&nbsp;Confirm New Password:&nbsp;</td><td><input type="password" name="UserPass3" size="50" maxlength="50"/></td></tr>
			<?php } ?>
			<tr><td>Full Name:&nbsp;</td><td><input type=text name="emp_name" value="<?php echo $row['fullname']?>" size="50" maxlength="50"></td></tr>
			<tr><td>Office Name:&nbsp;</td><td><?php echo cls_admin_forms::get_ofc_names($ofc_name)?></td></tr>   
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2"><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button"><?php if($action=='add'){echo 'Add';}else{echo "Save";}?></a></span></center></td></tr>
			</table>
		</form>
		<?php
		}//add or edit user data
		
	protected function get_ofc_names($ofc_name){
		include_once 'db_conn.php';
		$sql_str1="select * from codes where category='ofc_type' order by descr asc";
		$sql_qry1=mysql_query($sql_str1) or die(mysql_error());
		?><select name="ofc_name"><option value="" <?php if($ofc_name==''){echo "selected";}?>>Select Office Name</option> <?php
		while ($row=mysql_fetch_array($sql_qry1)){
			?><option value="<?php echo $row['code']?>" <?php 
				if(trim($ofc_name)==$row['descr']){ echo "selected"; }?>>
				<?php echo strtoupper($row['descr'])?></option><?php            
		}?></select>
		<?php 
	} //get office names for the user
	
	public function msg($message,$color){
		?>
		<center><h3><font color="<?php echo $color?>"><?php echo $message?></font></h2></center>
		<?php return(0);
	} //show error messages
	
    public function view(){
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        include_once 'forms.php';
        
        $sql_str1="select * from tbl_usr_crd order by ofc_name asc";
        $sql_qry1=mysql_query($sql_str1) or die(mysql_error());
        ?>
        <form id="form1">
        <table align="center">
            <tr>
                <td align="center">Delete</td>
                <td align="center">Edit</td>
                <td align="center">Full Name</td>
                <td  align="center">Office Name</td>
                <td align="center">User Name</td>
                <td align="center">Password</td>
                <td align="center">Status</td>
                <td align="center">Action</td>
            </tr>
        
        <?php
        while($row=mysql_fetch_array($sql_qry1)){
            ?>
            <input type="hidden" id="row_values<?php echo $row['row_id'];?>" name="row_values<?php echo $row['row_id'];?>" value="<?php echo $row['row_id'];?>">
            <tr>
                <!--icon here for delete!-->
                <td align="center"><a href="admin.php?req=del&user_id=<?php echo $row['un']?>" title="delete user"><img src="icon/icon_del.jpg"></a></td>
                <!--icon here for delete!-->
                <td align="center"><a href="admin.php?req=edit&user_id=<?php echo $row['un']?>" title="edit user"><img src="icon/icon_edit.jpg"></a></td>
                <td align="center"><?php echo strtoupper($row['fullname'])?></td>
                <td align="center"><?php echo strtoupper($row['ofc_name'])?><!--office Name!--></td>
                <td align="center"><?php echo $row['un']?><!--User Name!--></td>
                <td align="center"><?php echo cls_misc::strPassToMeta($row['pwd'])?><!--Password!--></td>
                <td align="center"><?php if(cls_user_get::isPersonnleLogout($row['status'])=='0'){echo "Logged Out";}else{echo "Logged In";}?><!--status!--></td>
                <td align="center">
                <?php if(cls_user_get::isPersonnleLogout($row['status'])=='1'){
                    cls_forms::ajxSubmitButton("logout","onClick=UserLogout('{$row['un']}')");
                }?></td>
            </tr>
            <?php }?> </table></form>
            <?php
        
    }//users/names listings
		
}

//class for administrative page only ends here

class cls_forms{ //general forms starts here cls_forms::error_msg(msg,return )

    public function CreateBillingForCurrentMonth(){
        include_once 'cls_codes.php';
        $BrgyNames=cls_misc::getOptBarangayNames();
        ?>
        <table>
            <tr><td colspan="2"><center>Select Barangay for Current Month Billing</center></td></tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr><td>Barangay</td><td><select name="barangay" onchange="RetBillingNames()" id="barangay">
            <option value="" selected="selected">Select Barangay</option>
            <?php
                foreach($BrgyNames as $key=>$value){
                    echo "<option value=\"{$value['codes_value']}\">{$value['descr_value']}</option>" ;
                }
            ?>
            </select></td></tr>
        </table><br><br><br>
        <span id="status" style="display:none;">Generating Results Please Wait..<img src="icon/ajax-loader.gif"></span>
        <span id="results"></span>
        
        <?php
    }


    public function GeneralSearch(){
    ?><br><br>
    <center>
    <form id="form1">
        <table>
            <tr><td colspan="2"><center>General Profile Search</center></td></tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr><td>Search:&nbsp;&nbsp;&nbsp;</td><td><input type="text" id='crit' maxlength="100" name='crit' onkeyup="GeneralProfileSearch('crit','search_results')" style="width: 300px; vertical-align: bottom"></td></tr>
        </table></form><br><br>
        <span id="search_results"></span>
        <span id="dlgProfile" style="display:none;">this is the content</span>
        </center><br><br>
    <?php
    }

    
    public function SearchOR(){ //todo: FFU#64
    ?><br><br>
    <center>
    <form id="form1">
        <table>
            <tr><td colspan="2">Search for Official Receipt Release</td></tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr><td>OR#:&nbsp;</td><td><input type="text" id='or_num' maxlength="8" name='or_num' onkeyup="SearchOR('or_num','search_results')" style="width: 300;"></td></tr>
        </table></form><br><br>
        <span id="search_results"></span>
        </center><br><br>
    <?php
    }
    
    public function frm_JWSReport1(){ //FFU#38
        set_time_limit(0);
        include_once 'cls_codes.php';
        $EndYear=date('Y')-5; $StartYear=date('Y');
        $arrMonth=cls_misc::getOptMonths();
        
        ?>
        <form id='form1'>
            <table>
                <tr><td colspan="4">Select Parameters for the Report</td></tr>
                <tr><td colspan="4"></td></tr>
                <tr>
                    <td>Year:&nbsp;</td><td><select name="year_value">
                        <?php while($StartYear!=$EndYear){ ?>
                            <option value="<?php echo $StartYear ?>"><?php echo $StartYear ?></option>
                            <?php $StartYear-- ; } ?>
                    </select> </td>
                    <td>Month:&nbsp;</td><td>
                        <select name="month_value" onchange="GenerateJWSReport1('JWSReport1_view')">
                                <option value="">Select Month</option>
                            <?php foreach($arrMonth as $key=>$value){ ?>
                                <option value="<?php echo $value['code_value']?>"><?php echo $value['descr_value']?></option>
                            <?php } ?>
                        </select>
                    <!--select box for month --></td>
                </tr>
            </table>
        </form><br><br>
        <center><span id='JWSReport1_view'></span></center>
        <?php
    }
    
    public function AddBillingPayment(){
        include_once 'cls_codes.php';
        ?>
        <form id='form1' name='form1'>
        <!--<input type="hidden" name="bill" value='Addons'>-->
        <!--<input type="hidden" name="misc" value='getlastreadingdates'>-->
            <center>
            <table>
                <tr><td colspan="4">&nbsp;</td></tr>
                <tr><td colspan="4"><center>Select Concessionaire for Additional Billing</center></td></tr>
                <tr><td colspan="4">&nbsp;</td></tr>
                <tr><td>Name:</td> <td colspan="3"><input id="accnt_names" name="accnt_names" size="100" maxlength="100" style="width: 300px;" onkeyup="AccntNamesOnly('#accnt_names')" ></td></tr>
                <tr><td>Select Billing Addons</td><td colspan="3"><select id="addons" name="addons" onchange="GetMiscInstallment()">
                <?php  
                    $addons=cls_misc::getCodesDescrValuesToArray("select * from codes where category='other_payments'");
                    foreach($addons as $key=>$value){
                        ?>
                        <option value="<?php echo $value['codes']?>"><?php echo $value['description']?></option>
                    <?php }?> 
                    </select> </td> </tr>
                <tr>
                    <td>Total Amount</td><td><input type="text" id="total_amount" name="total_amount" size="10" maxlength="10"></td>
                    <td>Terms:&nbsp;</td><td><select name="terms" id="terms" onchange="Installment()">
                        <?php for($i=0;$i<=12;$i++){ ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php }?>
                         </select></td></tr>
                <tr>
                    <td>Amount for Installment</td>
                    <td colspan="3"><input type="text" id="instal_amnt" name="instal_amnt" size='10' maxlength="15"></td>
                </tr>
                <tr><td colspan="4">Installment Distribution on Reading Dates</td></tr>
                <tr><td colspan="4"><span id='CreateObj'></span></td></tr> 
                <tr><td colspan="4"><center><?php echo cls_forms::ajxSubmitButton("Confirm Reading Dates","onclick='BillingOptSave()'")?> </center></td></tr>
            </table>
            </center>
        </form>
         <?php
//todo -o mike -p 10 -c For Upload: [end]add the function below for additional billing option                
    }

	//[start] stage for commit to remote PS(status:done)
	public function BrgyTransfer(){
		?>
		<form id='form1' name='form1' method="GET" action="utility.php">
		<input type="hidden" name="getUtil" value='brgy_transfer'>
			<center>
			<table>
				<tr><td colspan="2"><hr></td></tr>
				<tr><td colspan="2"><center>Select Concessionaire for Barangay Transfer</center></td></tr>
				<tr><td colspan="2"><hr></td></tr>
				<tr><td>Name:</td> <td><input id="names" name="names" maxlength="100" autocomplete="false" onkeyup="AccntNames('#names')" style="width: 500px;"></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td>From Barangay&nbsp;&nbsp;&nbsp; </td><td><span id='from_brgy'></span></td> </tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td>To Barangay&nbsp;&nbsp;&nbsp;</td><td><span id='to_brgy'></span></td> </tr>
				<tr><td colspan="2"><hr></td></tr>
				<tr><td colspan="2"><center><?php echo cls_forms::SubmitButton("Transfer")?> </center></td></tr>
				<tr><td colspan="2"><hr></td></tr>
			</table>
			</center>
		</form>
		 <?php
	}
	public function OtherPayments(){
		include_once 'cls_codes.php';
		$RetSQL="select * from codes where category like '%payments%'";
		$values=cls_misc::getCodesDescrValuesToArray($RetSQL);
		echo "<select name='other_payments' id='other_payments'>";
		foreach($values as $key=>$value){echo "<option value='{$value['codes']}'>{$value['description']}</option>";}
		echo "</select>";
	}
	
	public function SubmitButton($ButtonLabel){
		?><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button"><?php echo $ButtonLabel; ?></a></span></center>
		<?php
	}
    
    public function ajxSubmitButton($ButtonLabel,$objEventPlusCallback){
        ?><center><span class="art-button-wrapper"><span class="art-button-l">
                </span><span class="art-button-r"></span><a href="#" class="art-button" <?php echo $objEventPlusCallback ?> ><?php echo $ButtonLabel ?> </a></span></center>
        <?php
    }

    /**
    * 
    * Method create link button
    * @param buttonLabel
    * @return value 
    */
	public function LinkButton($ButtonLabel,$Link_URL){
		?><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="<?php echo $Link_URL?>" class="art-button"><?php echo $ButtonLabel; ?></a></span></center>
		<?php
	}

    
    /**
    * Method: this is an upgrade of linkbutton function
    * @param $ButtonID -serves as the id name of the object
    * @param $fnCallback-refers to function 
    * @param $ButtonLabel - serves as the display label for the button
    * @return on screen output
    */    
    public function jLinkButton($ButtonID,$fnCallback,$ButtonLabel){
        ?><center><span class="art-button-wrapper"><span class="art-button-l">
                </span><span class="art-button-r"></span><a href="#" id=<?php echo $ButtonID ?> onclick="<?php echo $fnCallback ?>" class="art-button"><?php echo $ButtonLabel; ?></a></span></center>
        <?php
    }
    
    
    public function BatchBillingForm_Header($TableBgColor){
     ?>
     <table width="100%" bgcolor="<?php echo $TableBgColor?>">
	    <tr>
		    <td><center>Account No</center></td>
		    <td><center>Account Name</center></td>
		    <td><center>Previous Reading</center></td>
		    <td><center>Present Reading</center></td>
		    </tr>
     <?php   
    }

    
public function BatchBillingForm_Content($accnt_no=null,$FormCount=null,$TotalData_Array=null){
	echo "<span id=Form-{$FormCount}>Status: Waiting for Meter Reading Entry</span>";
	if($FormCount%2 == 0){$TableBgColor="white";}
		cls_forms::BatchBillingForm_Header($TableBgColor);
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		include_once 'date_time.php';
		include_once 'cls_user.php';
		$meter_fee=explode('|',cls_user_get::meter_fee($accnt_no));
		$str_conv=new cls_misc();
		$sql_str="select * from profile where acct_no='{$accnt_no}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		?>
		<form id="con<?php echo $FormCount?>">
		<?php //[start] declare all hidden variables and values ?>
		<input type="hidden" name="conn_type" value="<?php echo trim($row['type_connection'])?>">
		<input type="hidden" name="billing_count" value="<?php echo $meter_fee[1]?>">
		<input type="hidden" name="curr_reading_date" value="<?php echo cls_date_get::bool_date_render(cls_date_get::date_add(cls_date_get::last_reading_info($accnt_no,"reading_date"),"month","1"))?>">
		<input type="hidden" name="accnt_no" value="<?php echo $row['acct_no']?>">
		<input type="hidden" id="prev_reading_value<?php echo $FormCount?>" name="prev_reading_value" value="<?php echo cls_date_get::last_reading_info($accnt_no,"meter_reading")?>">
		<input type="hidden" name="bill" value="BatchBilling">
		<?php //[end] declare all hidden variables and values ?>
		<tr>
			<?php //follow format on header ?>
			<?php //open and readonly controls here ?>
			<td><center><?php echo $row['acct_no']?></center></td>
			<td><b><?php echo $row['applicant'] ?></b></td>
			<td><center><?php echo cls_date_get::last_reading_info($accnt_no,"meter_reading")?></center></td>
			<td><center><input type="text" id="curr_read_value<?php echo $FormCount?>" name="curr_read_value" size="20" maxlength="20" <?php
			if (cls_date_get::bool_date_render(cls_date_get::date_add(cls_date_get::last_reading_info($accnt_no,"reading_date"),"month","1"))=="Date not Scheduled") { echo "readonly"; }
		?>></center></td>
			</tr>
			<tr>
				<td><center>Cu. Used</center></td><td><center>Bill Amount</center></td><td><center>Download Bill</center></td><td><center>Action</center></td>
			</tr>
			<tr>
			<td><input type="text"  id="cubic_used<?php echo $FormCount?>" name="cubic_used" readonly value=""></td> <?php //data either returned by ajax or local script?>
			<td><input type="text"  id="bill_amount<?php echo $FormCount?>" name="bill_amount" readonly value=""></td><?php //data either returned by ajax or local script?>
			<td>
			
			<span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span>
				<!--<div id="button-dl<?php echo $FormCount?>" style="display: none;">-->
                <!-- disable this for new layout of billing-->
				<a  id="button-dl<?php echo $FormCount?>" style="display: none;" href="download_billing.php?request=init_dl&accnt_no=<?php echo base64_encode($row['acct_no'])?>&last_bill=<?php echo base64_encode($row_GetAllNonPaidBills['reading_date'])?>" class="art-button">Download</a>
				
				<!--</div>-->
				</span>
				
				</td><?php //data either returned by ajax or local script?>
			<td >
			<center>
			<span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span>
<!--<a href="download_billing.php?request=init_dl&accnt_no=<?php echo base64_encode($row['acct_no'])?>&last_bill=<?php echo base64_encode($row_GetAllNonPaidBills['reading_date'])?>" class="art-button">Create Bill</a>-->
				<a  id="button-create-bill<?php echo $FormCount?>" href="javascript:ProcessFormValues('<?php echo "con".$FormCount ?>','<?php echo $FormCount ?>')" class="art-button">Create Bill</a>
		</span></center>
			</td><?php //data either returned by ajax or local script?>
		</tr>
		<tr><td colspan="8"><hr></td></tr>
		</form>
		</table>
		<?php
	}
	
public function optBarangay_Codes(){
	
	include_once 'cls_codes.php';
	include_once 'db_conn.php';
	?>
	<form id="form1" name="form1" method="POST" action="form_handler.php">
	<input type="hidden" name="bill" value="CreateReadingForm"> 
		<table width="100%">
			
			<tr><td colspan="2"><center>Please select Barangay to Prepare Batch Billing Form for the month <?php echo cls_misc::toString(date('m'),'month');?></center></td></tr>
			<?php //todo: [end] stage for commit to remote PS ?>
			<tr>
				<td><center>Barangay
				<select name="barangay_code">
					
				<?php
						$barangay_names=cls_misc::getOptBarangayNames();
						foreach($barangay_names as $key=>$value){
							echo "<option value='{$value['codes_value']}'>".$value['descr_value']."</option>";
						}
						  ?> 
				</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Create</a></span></td></tr>
		</table>
	</form>
	<?php
}

//[end] stage to commit for remote PS

// [start] stage to commit for remote PS (status: uploaded)
public function optCreateReadingSheet(){
	include_once 'cls_codes.php';
	include_once 'db_conn.php';
	?>
	<form id="form1" name="form1" method="POST" action="form_handler.php">
	<input type="hidden" name="bill" value="CreateReadingSheet"> 
		<table width="100%">
			<tr><td colspan="2"><center>Please select Barangay to Create Reading Sheet for the month <?php echo cls_misc::toString(date('m'),'month');?></center></td></tr>
			<tr>
				<td><center>Barangay
				<select name="barangay_code">
					
				<?php
						$barangay_names=cls_misc::getOptBarangayNames();
						foreach($barangay_names as $key=>$value){
							echo "<option value='{$value['codes_value']}'>".$value['descr_value']."</option>";
						}
						  ?> 
				</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Create</a></span></td></tr>
		</table>
	</form>
	<?php
}

//[end] stage to commit for remote PS
public function ProfileWindow($account_number=null,$iteration_number=null){
	include_once 'db_conn.php';
	?>
		<!--sample2!-->
		<div id="basic-modal-content<?php echo $iteration_number?>">
			<h4><center>Basic Modal Dialog_test2</center></h4> 
			<p>Modal Dialog test_2</p>    
			<table align="center" border='0' width="100%" cellspacing="0" cellpadding="0">
				<tr><td><hr/></td></tr>
				<tr> <td align="center">Test Table for iteration number = <?php echo $iteration_number?></td></tr>
				<tr><td><hr/></td></tr>
			</table>
		</div>
		
		<!-- preload the images -->
		<div style='display:none'>
			<img src='images/x.png' alt='' />
		</div>
		</div>
	<?php
}

/*	
*  option for month to be used for visual presentation of the data such as graph
*/
	public function vis_report_opt($criteria,$table_name){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		//$sql_str="select distinct(adlaw) from {$table_name} group by {$criteria} asc";
		$sql_str="select distinct(adlaw) from {$table_name}";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		?>
		<select name="year-month">
			<?php
				while($row=mysql_fetch_array($sql_qry)){
					$option_value=$row['adlaw'];
					$data_option=explode('-',$option_value);
					$month_str=strtoupper(cls_misc::toString($data_option[1],"month"));
					$year=$data_option[0];
					?>
					<option value="<?php echo $option_value;?>"><?php echo $month_str.'-'.$year?></option>
					<?php
				}
			?>
		</select>
		<?php
	}

	public function bill_accnt(){
		?>
		<form name="form1" action="form_handler.php" method="POST">
		<input type="hidden" name="bill" value="pass_accnt">
		<p>
		<b>Enter Account Number</b>:&nbsp;<input type="text" name="accnt_no"  size="30" maxlength="20">&nbsp;&nbsp;
		<span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Search</a></span><br /></p>
</form>
	<table id="butangan" class="datagrid"></table>
		<?php
	}

	public function error_msg($msg,$return_link){
		?>
		<div class="art-blockheader">
		   <h3 class="t"><font color="red"><?php echo $msg?></font></h3>
		</div>

		<p align="center"> <span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="<?php echo $return_link?>" class="art-button">Back</a></span><br /></p>

		<?php
	}

	public function ListBrgy($ActionFilter=null){
		include_once 'db_conn.php';
        include_once 'cls_codes.php';
        $sql_str="select * from codes where category='Barangay' order by descr asc";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
        $YearNow=date('Y');
        $arrMonths=cls_misc::getOptMonths();
        if(!$ActionFilter){
		?>
		<br><br>
		<form name="form1" action="form_handler.php" method="POST">
		<input type="hidden" name="bill" value="show_brgy">
		<table class="art-article" align="center" cellpadding="0" cellspacing="0">
			<tr>
				<th colspan="2">Select Parameters for Billing Downloads</th>
			</tr>
			<tr>
				<td>Barangay:&nbsp;
					<select name="brgy_opt">
					<?php while ($row=mysql_fetch_array($sql_qry)) {?>
						<option value="<?php echo $row['code']?>"><?php echo $row['descr']?></option>
					<?php } ?>
					</select>&nbsp;&nbsp;Year:&nbsp;
                    <select name="year">
                        <option value="<?php echo $YearNow?>"><?php echo $YearNow?></option>
                        <?php $StartYear=$YearNow - 2; for($i=$StartYear;$i < $YearNow;$i++){ ?>
                            <option value="<?php echo $i?>"><?php echo $i?></option>
                            <?php } ?>
                    </select>
                    &nbsp;&nbsp; Month:&nbsp;
                    <select name="month">
                        <?php foreach($arrMonths as $key=>$value){ ?>
                            <option value="<?php echo $value['code_value']?>"><?php echo $value['descr_value']?></option>
                            <?php } ?>
                    </select>
				</td>
				<td><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Show</a></span></td>
			</tr>
		</table>
		</form>
		<?php
        }else{
            ?>
        <br><br>
        <form name="form1" action="form_handler.php" method="POST">
        <input type="hidden" name="bill" value="show_all_bills">
        <table class="art-article" align="center" cellpadding="0" cellspacing="0">
            <tr><th colspan="3">Select Parameters for Billing Downloads</th></tr>
            <tr>
                <td>Barangay:&nbsp;
                    <select name="brgy_opt">
                    <?php while ($row=mysql_fetch_array($sql_qry)) {?>
                        <option value="<?php echo $row['code']?>"><?php echo $row['descr']?></option>
                    <?php } ?>
                    </select></td>
                <td>Year:&nbsp;
                    <select name="year">
                        <option value="<?php echo $YearNow?>"><?php echo $YearNow?></option>
                        <?php $StartYear=$YearNow - 2; for($i=$StartYear;$i < $YearNow;$i++){ ?>
                            <option value="<?php echo $i?>"><?php echo $i?></option>
                            <?php } ?>
                    </select></td>
                <td><span class="art-button-wrapper"><span class="art-button-l">
                </span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Show</a></span></td>
            </tr>
        </table></form>
            <?php
        }
	}

	    
    public function show_accnt_billings($barangay_code,$year,$month=null){ //FFU#94 update for listing of bills for each barangay
		include_once 'db_conn.php';
		include_once 'date_time.php';
		include_once 'cls_bill.php';
		include_once 'cls_codes.php';
        include_once 'cls_user.php';
        if($month){
            $DateCriteria="{$year}-{$month}-%";
        }else{
            $DateCriteria="{$year}%";
        }
        
		//[start] stage for commit to remote PS
		$sql_str="select * from profile where address_brgy='{$barangay_code}' order by applicant asc";
		//[end] stage for commit to remote PS
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		//echo "record_count=".mysql_num_rows($sql_qry);
		if (mysql_num_rows($sql_qry)!=0) { //data found!
		?>
		<form name="form1" action="form_handler.php" method="POST">
		<input type="hidden" name="bill" value="dl_bill">
			<table cellpadding="0" cellspacing="0" align="center">
			<tr><td colspan="8"><strong>List of UnPaid Bills Created for Barangay <?php echo cls_misc::toString($barangay_code,"Barangay");?></strong></td></tr>
				<tr>
					<!--<td><strong>ID</strong></td>-->
					<td><b><center>Account No.</center></b></td>
					<td><b>Account Name</b></td>
					<td><b>Last Bill Created</b></td>
					<td><b>Due Date</b></td>
                    <td><b>Bill Amount</b></td>
                    <td><b>Penalty</b></td>
                    <td><b>Total</b></td>
					<td><b><center>Action</center></b></td>
				</tr>
				<?php
				while ($row=mysql_fetch_array($sql_qry)) {
					$legder_table=cls_misc::ConvertToTableName($row['acct_no']);
                    
                    if(cls_user_get::isPadLock($legder_table)==0){
					//$sql_GetAllNonPaidBills="SELECT * FROM {$legder_table} WHERE reading_date like '{$DateCriteria}'  and (OR_num='' or OR_num='0' or OR_num is null)";
                    $sql_GetAllNonPaidBills="SELECT * FROM {$legder_table} WHERE reading_date like '{$DateCriteria}'  AND ((OR_date is NULL or OR_date = '' and OR_date <> '0000-00-00') or (OR_num = '' or OR_num is NULL and OR_num <> '00000000'))";
					$qry_GetAllNonPaidBills=mysql_query($sql_GetAllNonPaidBills) or die(mysql_error());
					//$data=array();
					if(mysql_numrows($qry_GetAllNonPaidBills)!= 0){
						while($row_GetAllNonPaidBills=mysql_fetch_array($qry_GetAllNonPaidBills)){
							$i=$i + 1; if ($i % 2==0) { echo "<tr background=\"#FAF4D1\">"; }else{ echo "<tr>"; }                   
					 //start iterations 
					 ?>
					<td><?php echo $row['acct_no'];?></td><!--account number-->
					<td><?php echo $row['applicant'];?></td><!--name of the applicant-->
					<td><center>
					<?php $UnPaidReadingDate = cls_misc::toString(cls_date_get::extract_month($row_GetAllNonPaidBills['reading_date']),"month").'-'.
					cls_date_get::extract_year($row_GetAllNonPaidBills['reading_date']); echo $UnPaidReadingDate; ?>
					</center></td><!--bill created-->
                    <td><?php $due_date=cls_misc::DateAdd($row_GetAllNonPaidBills['reading_date'],'22','DAY');echo $due_date?></td><!--due date-->
					<td><?php $Amount = $row_GetAllNonPaidBills['bill_amnt']; echo cls_misc::gFormatNumber($Amount); ?> </td><!--bill amount-->
                    <td><?php if(date('Y-m-d') > $due_date){$Penalty=$Amount * 0.05; }else{$Penalty='0';} echo cls_misc::gFormatNumber($Penalty);?> </td><!--penalty-->
                    <td><?php $Total=$Amount + $Penalty; echo cls_misc::gFormatNumber($Total);?></td>
                    
					<td><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span>
				<a href="download_billing.php?request=init_dl&accnt_no=<?php echo base64_encode($row['acct_no'])?>&last_bill=<?php echo base64_encode($row_GetAllNonPaidBills['reading_date'])?>" class="art-button">Download</a>
				</span></td>
					</tr><?php  
					// -p 10 -o mike -c For Upload: [start]data preparation for excel export
						$data_values[]=array('accnt_no'=>$row['acct_no'],'accnt_name'=>$row['applicant'],'UnPaidBills'=>$UnPaidReadingDate,'Amount'=>$Amount,'DueDate'=>$due_date,'Total'=>$Total,'Penalty'=>$Penalty);
                        } //todo -p 10 -c For upload: update as of january 23,2014
						}
					}
				} 
		}
		cls_bill_set::UnpaidExportToExcel($data_values,"UnPaid Bills for Barangay ". cls_misc::toString($barangay_code,'Barangay'),'unpaid_temp.xls');
		// -p 10 -o mike -c For Upload: [end]data preparation for excel export
	?></table></form> <?php
	
	}
	public function frm_login_generic(){
		?>
		<br>
		<form name="form1" method="POST" action="form_handler.php">
			<input type="hidden" name="generic" value="login">
			<center>
			<table  align="center">
				<tr>
					<th colspan="2" align="center">User Login Required</th>
				</tr>
				<tr>
					<td>Username:</td>
					<td><input type="text" name="usn" size="20" maxlength="20"></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="usp" size="20" maxlength="20"></td>
				</tr>
				<tr>
					<td colspan="2"><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Login</a></span></center></td>
				</tr>
			</table>
			</center>
		</form>
		<?php
	}

	public function frm_login($LinkName='',$LinkID='',$form_method=''){
		//cashier department: linkname=cashier,linkid=const "login",form_method=post
		?>
		<br>
		<form name="form1" method="<?php echo strtoupper($form_method) ?>" action="form_handler.php">
			<input type="hidden" name="<?php echo $LinkName?>" value="login">
			<center>
			<table  align="center">
				<tr>
					<th colspan="2" align="center">User Login Required</th>
				</tr>
				<tr>
					<td>Username:</td>
					<td><input type="text" name="usn" size="20" maxlength="20"></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="usp" size="20" maxlength="20"></td>
				</tr>
				<tr>
					<td colspan="2"><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Login</a></span></center></td>
				</tr>
			</table>
			</center>
		</form>
		<?php
	}

	public function frm_cashier_acnt(){ //form to search for consumer account number
		?>
		<br><!--
		<center>
			<form name="form1" method="POST" action="form_handler.php">
			<input type="hidden" name="cashier" value="show_ledger1">
				<table>
					<tr>
						<th align="center">Enter Account Number</th>
					</tr>
					<tr>
						<td><input type="text" name="accnt" size="15" maxlength="15"></td>
					</tr>
					<tr>
						<td><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:submitform()" class="art-button">Show Ledger</a></span></center></td>
					</tr>
				</table>
			</form>
		</center><br><p align="center"><strong>OR</strong></p> <br>
		<!--payment through account search form-->
		<center>
				<form id="search">
				<table>
					<tr>
						<th align="center">Search Account Number</th>
					</tr>
					<tr>
						<td><input type="text" id="accnt" name="accnt" size="15" maxlength="15"></td>
					</tr>
					<tr>
						<td><center><span class="art-button-wrapper"><span class="art-button-l">
				</span><span class="art-button-r"></span><a href="javascript:getdetails()"  class="art-button">Search</a></span></center></td>
					</tr>
				</table>
				</form>
				<div id="status"></div>
				<div id="results"></div>

		</center>
		
		<?php
	} //form to search for consumer account number

	
	
	public function show_ledger($account_number){ //show ledger for specific consumers using unsettled accounts
		//show consumer ledger
		include_once 'db_conn.php';
		include_once 'cls_user.php';
		include_once 'cls_bill.php';
		?>
		<center>
			<form name="form1" action="form_handler.php" method="POST">

				<center>Consumer Legder Summary</center>
				<!--user information !-->
				<?php cls_user_get::user_info($account_number); ?>
				<!--show unpaid bills only!-->
				<?php cls_bill_get::unsettled_bills($account_number);?>
			</form>
		</center>
		<?php  
		//return(void);
	}//show ledger for specific consumers using unsettled accounts

	public function frm_cashier_payment($account_number,$billing_month,$total_amount,$penalty){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		include_once 'date_time.php';
		include_once 'cls_user.php';
		$consumer_ledger=cls_misc::sanitize_hyp(trim($account_number));
		$date_now=cls_date_get::date_now();
		cls_user_get::user_info($account_number);
		?>
		<br><br>
		<center>
		<form name="form1" method="POST" action="form_handler.php">
		<input type="hidden" id="cashier" name="cashier" value="payment">
		<table>
			<tr><th colspan="4">Cashier Payment Form</th></tr>
			<tr>
				<td>Billing Month:&nbsp;</td>
				<input type="hidden" name="billing_month" value="<?php echo $billing_month?>">
				<input type="hidden" id="acnt_no" name="acnt_no" value="<?php echo trim($account_number)?>">
				<input type="hidden" name="penalty" value="<?php echo $penalty?>">
				<td><strong><?php echo cls_misc::toString(cls_date_get::extract_month($billing_month),'month').'-'.cls_date_get::extract_year($billing_month)?></td>
				<td>Total Amount:&nbsp;</td>
				<td><input type="text" name="total_amnt" value="<?php echo number_format($total_amount,2,'.',',');?>" readonly></td>
			</tr>
			<tr>
				<td>OR Date:&nbsp;</td>
				<td><input type="text" name="or_date" value="<?php echo cls_date_get::date_now()?>" readonly></td>
				<td>OR No.&nbsp;</td>
				<?php //[start] stage for commit to remote PS (status: ongoing) ?>
				<td><input type="text" id="or_no" onkeyup="CheckAcceptedLength('or_no');" name="or_no" size="20" maxlength="20"></td>
				<?php //[end] stage for commit to remote PS ?>
			</tr>
			<tr>
				<td colspan="4"><center>
					<span class="art-button-wrapper"><span class="art-button-l">
				<?php //[start] stage for commit to remote PS (status: ongoing) ?>
				</span><span class="art-button-r"></span><a style="display: none;" id="ajx-B-OR-Save"  href="javascript:submitform()" class="art-button">Save</a></span></center>
				<?php //[end] stage for commit to remote PS ?>
				</td>
			</tr>
		</table>
		</form>
		</center>
		<?php

	}

	public function frm_opt_user($action){
		include_once 'db_conn.php';
		if ($action='edit') { //process request for data entry in the form below

		}
		?>
		<br>
		<form name="form1" method="POST" action="form_handler.php">
			<input type="hidden" name="generic" value="opt_user">
			<center>
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr><th>User Credential Entry:Action=<?php echo $action?></th></tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>Fullname:&nbsp;</td>
						<td><input type="text" name="fn" value="" size="30" maxlength="30"></td>
					</tr>
					<tr>
						<td>User Name:&nbsp;</td>
						<td><input type="text" name="usn" value="" size="20" maxlength="20"></td>
					</tr>
					<tr>
						<td>Password:&nbsp;</td>
						<td><input type="password" name="usp" size="20" maxlength="20" value=""></td>
						<td>Confirm:&nbsp;</td>
						<td><input type="password" name="usp2" size="20" maxlength="20" value=""></td>
					</tr>
					<tr>
						<td>Office Name:&nbsp;</td>
						<td><select name="ofc"><!-- create option from codes for office name values!--> </select></td>
					</tr>
				</table>
			</center>
		</form>
		<?php
	}
}

?>