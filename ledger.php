<?php 
    include_once 'juban_functions.php';
    include_once 'scripts.php';
?>
<script type="text/javascript" src="multiplescripts.js"></script>
<?php class ledger_type{
	public function general($acct){
		include_once 'cls_codes.php';
        include_once 'cls_excel.php';
        //download data to file
        cls_Excel_Export::LedgerDownloadIND($acct);
?>

<!-- HEADER OF THE LEDGER -->
	<table class="header" border="0" align="center">
		<tr><td>Republic of the Philippines</td></tr>
		<tr><td>MUNICIPALITY OF JUBAN</td></tr>
		<tr><td>SORSOGON</td></tr>
		<tr><td></td></tr>
		<tr><td>JUBAN WATER SYSTEM</td></tr>
		<tr><td>CUSTOMER LEDGER CARD</td></tr>
	</table>
	<p>	</p>
	<p></p>
<!-- END OF HEADER -->
<!-- START OF CLIENTS PROFILE -->
	<table class="credentials" border="0" align="center">
	<?php 
		//if (isset($_POST['submit']))
		//{$acct= $_POST['accountno'];}else{}//entry point
		  $acct_no = mysql_fetch_row(sql_retrieve::request_rows("acct_no", "profile", "acct_no = '$acct'"));
		  $serial = mysql_fetch_row(sql_retrieve::request_rows("meterno","profile", "acct_no = '$acct'"));	
		  $name = mysql_fetch_row(sql_retrieve::request_rows("applicant","profile", "acct_no = '$acct'"));
		  $brand = mysql_fetch_row(sql_retrieve::request_rows("brand","profile", "acct_no = '$acct'"));
		  /*raw_address*/
		  $address = mysql_fetch_row(sql_retrieve::request_rows("address_brgy","profile", "acct_no = '$acct'"));
		  /*fine_address*/ 
		  $date = mysql_fetch_row(sql_retrieve::request_rows("date_installed","profile", "acct_no = '$acct'"));
		  $address = mysql_fetch_row(sql_retrieve::request_rows("descr","codes","code='$address[0]'"));
		  $terms = mysql_fetch_row(sql_retrieve::request_rows("mode_payment","profile","acct_no = '$acct'"));
	  ?>
		<tr><th>Account Number:</th><td><a href="ledger_content.php?action=edit&acct_no=<?php echo $acct_no[0];?>"><?php echo $acct_no[0];?></a></td><td></td><th>Meter Serial No:</th><td><?php echo $serial[0];?></td></tr>
		<tr><th>Name:</th><td><?php echo $name[0];?></td><td></td><th>Brand:</th><td><?php echo $brand[0];?></td></tr>
		<tr><th>Address:</th><td><?php echo $address[0];?></td><td></td><th>Terms:</th><td><?php if ($terms=1){echo "cash";}else {echo "installment";}
		?></td></tr>
		<tr><th>Date Installed:</th><td><?php echo $date[0];?></td><td></td><th>Settings:</th>
			<td><!-- below has change date: june 12, 2012 specifically the href
				<a href="ledger_content.php?action=min_row&read_low=&accountno=<?php echo $acct;?>">
					<input class="art-button" type="button" value="Show Minimum" onclick="reading_back();"></input>
				</a>
				<!-- below has change date: june 12, 2012 -->
			</td>
		</tr>
		
	</table>
<!-- END OF CLIENTS PROFILE -->
<!-- START OF LEDGER BODY -->	
<table  class="ledger_2">
	<tr><th>Reading Date</th><th>Meter Reading</th><th>CU Used</th><th>PEN Fee</th><th>Bill Amount</th>
		<th>Loan MLP</th><th>Loan MF</th><th>AF</th><th>Misc</th><th>Total</th><th>OR No</th><th>OR Date</th><th>Remarks</th>	
	</tr>
	<!-- Retrieve Ledger details -->
		<tbody>	
			<?php $acct_replace = str_replace("-","_",$acct);?>
			<?php $query = sql_retrieve::request_rows("*", "ldg_$acct_replace order by reading_date asc","none");?>
			<?php $counting= mysql_num_rows($query);?>
			<?php while($row = mysql_fetch_assoc($query)){?>
			<?php 
			if (($counting % 2 != 0)){?>
				<tr class="odd"><?php }
			else{?><tr><?php }?>
					<td style="text-align: center"><a href="ledger_content.php?action=update&reading_date=<?php echo $row['reading_date'];?>&acct_replace=<?php echo $acct_replace;?>"><?php echo $row['reading_date'];?></a></td>
                    
                    <td style="text-align: right"><?php echo $row['meter_reading'];?></td>
                    <td style="text-align: right"><?php echo $row['cu_used'];?></td>
					<td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['pen_fee']);?></td>
					<td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['bill_amnt']);?></td>
					<td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['loans_MLP']);?></td>
                    <td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['loans_MF']);?></td>
					<td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['AF']);?></td>
					<td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['misc']);?></td>
					<td style="text-align: right"><?php echo cls_misc::gFormatNumber($row['total']);?></td>
					<td style="text-align: right"><?php echo $row['OR_num'];?></td>
					<td style="text-align: right"><?php echo $row['OR_date'];?></td>
					<td style="text-align: right"><?php echo $row['remarks'];?></td>	
				</tr><?php $counting--; }?>
		</tbody>
	<!-- Retrieve Ledger details -->
	<!-- Start of New Input to Ledger -->
		<tr><form name = "ledger" method="post" action="ledger_content.php">
<!--below has change date:june 12 2012 -->		
		<?php if (isset($_REQUEST['read_low'])){
				$row = mysql_fetch_assoc(sql_retrieve::request_rows("min(reading_date) as reading_date, min(meter_reading) as meter_reading","ldg_$acct_replace","none"));
		}else{
				 $row = mysql_fetch_assoc(sql_retrieve::request_rows("max(reading_date) as reading_date, max(meter_reading) as meter_reading","ldg_$acct_replace","none"));
		}?>
<!-- above has change date:june 12 2012 -->			
				<?php $sked_reading =  mysql_fetch_assoc(sql_retrieve::request_rows("date_meter_reading","dates_sched","bryg_codes in (select  address_brgy FROM profile where acct_no='$acct')"));?>   
				<?php if ((substr($row['reading_date'],8))<($sked_reading['date_meter_reading'])){
							$reading_date = substr_replace($row['reading_date'],$sked_reading['date_meter_reading'],8);                      
						}elseif(((substr($row['reading_date'],8))>($sked_reading['date_meter_reading']))){
							$reading_date = substr_replace($row['reading_date'],$sked_reading['date_meter_reading'],8);
							$reading_date = auxilliary::dagdag_date($reading_date,"P1M");
						}else{
							$reading_date = auxilliary::dagdag_date($row['reading_date'],"P1M");}?>               	
			<td><center><input type="text"  id="wawa" name="reading_date" value="<?php echo $reading_date;?>" style="width: 100px;"></center></td>
			<td><center><input type="text" id="old_reading" name="meter_reading" onblur="difference(<?php echo $row['meter_reading'];?>);" style="width: 50px;"></center></td>
			<td ><input type="text" name="cu_used" id="cu_used" onclick="difference(<?php echo $row['meter_reading'];?>);" onblur="getBillAmount()"></input></td>
			<td><input type="text" name="pen_fee" id="pen_fee" onclick="compute($('#bill_amnt').val())"></td>
			<td><input type="text" name="bill_amnt" id="bill_amnt" onclick="getBillAmount()"></td>
			<td><input type="text" name="loans_MLP" id="loans_MLP" ></td>
            <td><input type="text" name="loans_MF" id="loans_MF"></td>
			<td><input type="text" name="AF" id="AF"></td>
			<td><input type="text" name="misc" id="misc"></td>
			<td><input type="text" name="total" id="total" onclick="sum()"></td>
			<td><input type="text" name="OR_num"></td>
			<td><input type="text" id="date" name="OR_date" onmouseover="test_date_picker()"></td>
			<td><input type="text" name="remarks"></td>
				<?php forms_handlers::form_types("hidden","acct_replace","$acct_replace")?>
				<?php forms_handlers::form_types("hidden","acct","$acct")?>
                <input type="hidden" id="AccntNo" name="AccntNo" value="<?php echo $acct?>">
				<?php //forms_handlers::form_types("hidden","reading_date","$reading_date")?>
		</tr>
	
</table>
	<input type="hidden" name="mga_post" value="Enter to Ledger"></input>
	<span style="margin-left:35%"><input type="button" class="art-button" onclick="if(confirm('Are you sure you want to enter these account values?')) document.ledger.submit();" value="Enter to Ledger"><a onclick="if(confirm('Continue to download?')) return true;return false;" href="accnts/ledger_<?php echo ($acct);?>.xls"><input type="button" class="art-button" value="DOWNLOAD"></a></span>
		</form>
<!-- end of New Input to Ledger -->		
<?php }
	public function new_account(){ ?>
<p style="font-size:15pt;font-style:italic;">Please complete your credential to create a new ledger account.</p>
<div class="new_ledger">	
<table border="0" class="initial">
<form id = "new_ledger" action="ledger_content.php" name="form1" method="POST">

	<tr><th>Name of Applicant</th><td colspan="3">
	<input class="clear" type="text" name="s_name" value="Last name"></input>
	<input class="clear" type="text" name="f_name" value="First name"></input>
	<input class="clear" type="text" name="m_name" value="Middle initial"></input>
	</td></tr>
	
	<tr><th>Barangay Address</th><td>
<?php $my_row=address();?>
<select name="address"><?php foreach ($my_row as $address){?><option value="<?php echo $address["code"];?>"><?php echo $address["descr"];}?></option>
</select></td>

<th>Type of Connection</th><td><select name="type_connect">
	<option value="1">Residential</option>
	<option value="2">Commercial</option>
	</select></td>


</tr>

<tr><th>Inspection Report</th><td>
<select name="inspect_report">
<option value="1">Adequate</option>
<option value="0">Not Adequate</option>
<option>Others</option>
</select></td>

<th>Inspector</th><td>
<input type="text" name="inspector"></input></td>



<tr><th>Date of Inspection</th><td>
<?php $my_row=month_explode();?>
<select name="month_inspect"><?php foreach ($my_row as $bulan){?> <option value="<?php echo $bulan["code"];?>"><?php echo $bulan["descr"];}?></option>
</select>

<?php $my_row=day_explode();?>
<select name="day_inspect"><?php foreach($my_row as $adlaw){?>
<option><?php printf("%02s",$adlaw);}?></option></select>

<?php $my_row=year_explode();?>
<select name="year_inspect"><?php foreach($my_row as $taon){?>
<option><?php echo $taon;}?></option></select>
</td>

<th>Mode of Payment</th><td>
<select name="mode_payment"><option value="1">Cash</option>
<option value="2">Installment</option></select></td>


</tr>


<tr><th>OR Number</th><td>
<input type="text" id="or_no" name="or_no" onkeyup="bSearchOR('or_no','or_no')"></input>
<img id='loader' style="display:none;">
</td>

<th>OR Date</th><td>
<?php $my_row=month_explode();?>
<select name="month_or"><?php foreach ($my_row as $bulan){?>
<option value="<?php echo $bulan["code"];?>"><?php echo $bulan["descr"];}?></option></select>

<?php $my_row=day_explode();?>
<select name="day_or"><?php foreach($my_row as $adlaw){?>
<option><?php printf("%02s",$adlaw);}?></option></select>

<?php $my_row=year_explode();?>
<select name="year_or"><?php foreach($my_row as $taon){?>
<option><?php echo $taon;}?></option></select>
</td>

</tr>


<tr><th>JWS Chairman</th><td><input type="text" name="jws_chairman" value="N/A"></input></td>
<th>Plumber</th><td><input type="text" name="plumber" value="N/A"></input></td></tr>

<tr><th>Meter Number</th><td><input type="text" name="meter_no"></input></td><th>Meter Brand</th>
<td><input type="text" name="meter_brand" value="EVJET"></input></td></tr>


<tr><th>Meter Size</th><td><input type="text" name="size" value="15MM"></input></td><th>Initial Reading</th>
<td><input type="text" name="initial_reading"></input></td></tr>

<tr><th>Date of Reading</th><td>
<?php $my_row=month_explode();?>
<select name="month_installed"><?php foreach ($my_row as $bulan){?>
<option value="<?php echo $bulan["code"];?>"><?php echo $bulan["descr"];}?></option></select>

<?php $my_row=day_explode();?>
<select name="day_installed"><?php foreach($my_row as $adlaw){?>
<option><?php printf("%02s",$adlaw);}?></option></select>

<?php $my_row=year_explode();?>
<select name="year_installed"><?php foreach($my_row as $taon){?>
<option><?php echo $taon;}?></option></select>
</td>

<th>Account Number</th><td><input class="clear" value="0000-00-0000" type="text" name="account_no"></input></td>

</tr>
</table>
<center><input type="button" value="Add Account" onclick="if(confirm('Do you want to add these values to ledger and proceed?')) submitform();" class="art-button"></input></center>
<input type="hidden" name="mga_post" value="Add Account"></input>
</form>
</div>
<?php 
	
	}
public function update_ledger($acct_update,$readingdate_update){
	$query = mysql_fetch_assoc(sql_retrieve::request_rows("*", "ldg_".$acct_update,"reading_date='".$readingdate_update."'"));?>
			<table  class="update_ledger">
			<caption style="font-size:15pt">Account Number: <?php echo str_replace("_","-",$acct_update);?></caption>	
				<form action ="ledger_content.php" name="form1" method="POST">
				<thead><tr><td>HEADER</td><td>EXISTING</td><td>NEW ENTRY</td></tr></thead>
				<tbody>
					<tr class="odd"><td>Reading Date</td><td><?php echo $query['reading_date']?></td><td><?php echo $query['reading_date']?></td></tr>
						<input type="hidden" name="reading_date" value="<?php echo $query['reading_date'];?>"></input>
						<input type="hidden" name="acct_no_ledger" value="<?php echo $acct_update;?>"></input>
					<tr><td>Meter Reading</td><td><?php echo $query['meter_reading']?></td><td><input type="text" name="meter_reading" value="<?php echo $query['meter_reading']?>"></input></td></tr>
					<tr class="odd"><td>CU used</td><td><?php echo $query['cu_used']?></td><td><input type="text" name="cu_used" value="<?php echo $query['cu_used']?>"></td></tr>
					<tr><td>PEN Fee</td><td><?php echo $query['pen_fee']?></td><td><input type="text" name="pen_fee" value="<?php echo $query['pen_fee']?>"></td></tr>
					<tr class="odd"><td>Bill Amount</td><td><?php echo $query['bill_amnt']?></td><td><input type="text" name="bill_amnt" value="<?php echo $query['bill_amnt']?>"></td></tr>
					<tr><td>LOAN MLP</td><td><?php echo $query['loans_MLP']?></td><td><input type="text" name="loans_MLP" value="<?php echo $query['loans_MLP']?>"></td></tr>
					<tr class="odd"><td>LOAN MF</td><td><?php echo $query['loans_MF']?></td><td><input type="text" name="loans_MF" value="<?php echo $query['loans_MF']?>"></td></tr>
					<tr class="odd"><td>Misc</td><td><?php echo $query['misc']?></td><td><input type="text" name="misc" value="<?php echo $query['misc']?>"></td></tr>
					<tr><td>Total</td><td><?php echo $query['total']?></td><td><input type="text" name="total" value="<?php echo $query['total']?>"></td></tr>
					<tr class="odd"><td>OR No</td><td><?php echo $query['OR_num']?></td><td><input type="text" name="OR_num" value="<?php echo $query['OR_num']?>"></td></tr>
					<tr><td>OR Date<br><em><font size="-2">(yyyy-mm-dd)</font></em></td><td><?php echo $query['OR_date']?></td><td><input type="text" maxlength="10" name="OR_date" value="<?php echo $query['OR_date']?>"></td></tr>
					<tr class="odd"><td>Remarks</td><td><?php echo $query['remarks']?></td><td><input type="text" name="remarks" value="<?php echo $query['remarks']?>"></td></tr>
					<tr><td><input id="actibo" type="button" class="art-button" value="SAVE" onclick="if(confirm('Are you sure you want to commit these changes?')) submitform();"></input></td>
						<!-- <td><input name="submit" type="submit" value="Update DATE"></input></td>
						<td><input name="submit" type="submit" value="Delete DATE"></td> -->
						<td><input type="radio" name="mga_post" value="Update DATE" Checked><center style="font-weight:bolder;font-size:13pt;">Update DATE</center></input></td>
						<td><input type="radio"  name="mga_post" value="Delete DATE" ><center style="font-weight:bolder;font-size:13pt;">Delete DATE</center></input></td>
					</tr>
				</tbody>
				
				</form>
			</table>			

<?php 
}

public function search_person(){
	?>     
			                                <h2 class="art-postheader">
															 <center>Please enter the name of client </center>
															  <center style="padding-top: 20px;"> 
																 <form method="POST" name="form1" action="ledger_content.php">
																	 <input id="accnt_names" name="pangaran" onkeyup="AccntNamesOnly('#accnt_names')" autocomplete="off" style="width: 500px;">                                         
																	 <input type="hidden" name="mga_post" value ="SEARCH NAME"></input>
                                                                     <?php //FFU#16 ?>
                                                                     <span class="art-button-wrapper"><span class="art-button-l">
                                                                     </span><span class="art-button-r"></span>
                                                                     <input type="button" class="art-button" value="Search Name" onclick="if(confirm('Are you sure you want to search for that name?')) submitform();"></input></span>
																 </form>    
																 <table id="butangan" class="datagrid"></table>                                                 
															 </center>  
														</h2>
	</form>
	<?php
}

public function edit_profile($acct){
	$request = mysql_fetch_assoc(sql_retrieve::request_rows("*","profile","acct_no='".$acct."'"));
?>
<p style="font-size:15pt;font-style:italic;">Please complete your credential to update your profile.</p>
<div class="new_ledger_update">	
<table border="0" class="initial">
<form id = "new_ledger" name="form1"  action="ledger_content.php" method="POST">
<TR><TH>CREDENTIALS</TH><TD>ORIGINAL ENTRY</TD><TD>NEW ENTRY</TD></TR>
	<tr><th>Name of Applicant</th>
	<td><?php echo $request['applicant'];?></td>
	<td><input type="text" name="name" value="<?php echo $request['applicant'];?>"></input>
	</td></tr>
	
	<tr><th>Barangay Address</th><td><?php $barangay = mysql_fetch_assoc(sql_retrieve::request_rows("descr","codes","code='".$request['address_brgy']."'"));
	echo $barangay['descr'];?></td>
	<td>
<?php $my_row=address();?>
<select name="address"><?php foreach ($my_row as $address){?><option value="<?php echo $address["code"];?>"><?php echo $address["descr"];}?></option>
</select></td></tr>

<tr><th>Type of Connection</th><td><?php if ($request['type_connection']=="1"){echo "Residential";}else{echo "Commercial";}?></td>
<td><select name="type_connect">
	<option value="1">Residential</option>
	<option value="2">Commercial</option>
	</select></td>


</tr>

<tr><th>Inspection Report</th><td><?php if($request['type_inspct']=="1"){echo "Adequate";}else{echo "Not Adequate";}?></td>
<td>
<select name="inspect_report">
<option value="1">Adequate</option>
<option value="0">Not Adequate</option>
<option>Others</option>
</select></td></tr>

<tr><th>Inspector</th><td><?php echo $request['inspector']?></td><td>
<input type="text" name="inspector" value="<?php echo $request['inspector']?>"> </input></td>



<tr><th>Date of Inspection</th><td><?php echo $request['date_applied'];?></td>
<td>
<?php $my_row=month_explode();?>
<select name="month_inspect"><?php foreach ($my_row as $bulan){?> <option value="<?php echo $bulan["code"];?>"><?php echo $bulan["descr"];}?></option>
</select>

<?php $my_row=day_explode();?>
<select name="day_inspect"><?php foreach($my_row as $adlaw){?>
<option><?php printf("%02s",$adlaw);}?></option></select>

<?php $my_row=year_explode();?>
<select name="year_inspect"><?php foreach($my_row as $taon){?>
<option><?php echo $taon;}?></option></select>
</td>
</tr>
<tr>
<th>Mode of Payment</th><td><?php if($request['mode_payment']=="1"){echo "Cash";}else{echo "Installment";};?></td>
<td>
<select name="mode_payment"><option value="1">Cash</option>
<option value="2">Installment</option></select></td>


</tr>


<tr><th>OR Number</th><td><?php echo $request['or_no'];?></td>
<td>
<input type="text" name="or_no" value="<?php echo $request['or_no'];?>"></input></td></tr>

<tr>
<th>OR Date</th><td><?php echo $request['or_date'];?></td>
<td>
<input type="text" name="or_date" readonly value="<?php echo $request['or_date'];?>"></input>
</td>

</tr>


<tr><th>JWS Chairman</th><td><?php echo $request['approval'];?></td><td><input type="text" name="jws_chairman" value="<?php echo $request['approval'];?>"></input></td></tr>
<tr><th>Plumber</th><td><?php echo $request['plumber'];?></td><td><input type="text" name="plumber" value="<?php echo $request['plumber'];?>"></input></td></tr>

<tr><th>Meter Number</th><td><?php echo $request['meterno'];?></td><td><input type="text" name="meter_no" value="<?php echo $request['meterno'];?>"></input></td></tr><tr><th>Meter Brand</th>
<td><?php echo $request['brand'];?></td><td><input type="text" name="meter_brand" value="<?php echo $request['brand'];?>"></input></td></tr>


<tr><th>Meter Size</th><td><?php echo $request['size'];?></td><td><input type="text" name="size" value="<?php echo $request['size'];?>"></input></td></tr><tr><th>Initial Reading</th>
<td><?php echo $request['initial_reading'];?></td><td><input type="text" name="initial_reading" value="<?php echo $request['initial_reading'];?>"></input></td></tr>

<tr><th>Date of Reading</th>
<td><?php echo $request['date_installed'];?></td><td>
<?php $my_row=month_explode();?>
<select name="month_installed"><?php foreach ($my_row as $bulan){?>
<option value="<?php echo $bulan["code"];?>"><?php echo $bulan["descr"];}?></option></select>

<?php $my_row=day_explode();?>
<select name="day_installed"><?php foreach($my_row as $adlaw){?>
<option><?php printf("%02s",$adlaw);}?></option></select>

<?php $my_row=year_explode();?>
<select name="year_installed"><?php foreach($my_row as $taon){?>
<option><?php echo $taon;}?></option></select>
</td></tr>
<tr>
<th>Account Number</th><td><?php echo $acct;?></td><td><input type="text" name="account_no" value="<?php echo $acct;?>"></input></td>

</tr>
</table>
<input type="hidden" name="old_acct_no" value="<?php echo $acct;?>"></input>
<input type="hidden" name = "old_date_installed" value="<?php echo $request['date_installed'];?>"></input>
<center><input type="button" class="art-button" value="Save Profile"  onclick="new_ledger.submit();"></input></center>
<input type="hidden" value="Save Profile" name="mga_post"></input>
</form>
</div>
<?php 
}

public function display_names($names){
		   ?>
			   <table align="center">
					<tr><th>Name</th><th>Account No</th></tr>
		   <?php
		   while($row = mysql_fetch_assoc($names)){
				?>
					<tr><td><a href="ledger_content.php?action=ledger_scan&account_no=<?php echo $row['acct_no'];?>"><?php echo $row['applicant'];?></a></td><td><?php echo $row['acct_no'];?></td></tr>
				<?php
			   
		   }
		   ?>
		   </table>
		   <?php
}
}?>		