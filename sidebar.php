<?php
	//starts include files
	set_time_limit(0);
	include_once 'cls_bill.php';
	include_once 'cls_codes.php';
	
?>
<div class="art-layout-cell art-sidebar1">

<!-- this is for logout -->
<div class="art-block">
	<div class="art-block-body">
				<div class="art-blockheader">
					<div class="l"></div>
					<div class="r"></div>					
					<h3 class="t">User Information</h3>
				</div>
				<div class="art-blockcontent">
					<div class="art-blockcontent-body">
<?php if(($_SESSION['profileid'])<>""){?>
<table>
	<tr><td>Date:</td><td><?php echo date('Y-m-d'); ?></td></tr>
	<tr><td width="75%">Username:</td><td><?php echo $_SESSION['username'];?></td></tr>
	<tr><td width="75%">Fullname:</td><td><?php echo $_SESSION['pangaran'];?></td></tr>
</table>

<br>
<form method="POST" action="main_file.php" name="una">
	<center><input type="submit" name="submit" value="Logout" class="btnLogin" ></input></center>
</form>
<?php 
	}else{
		echo "<blink>No user available.</blink>";
}?>
										<div class="cleared"></div>
					</div>
				</div>
		<div class="cleared"></div>
	</div>
</div>
<!-- End of log out -->
<br>
<div class="art-block">
	<div class="art-block-body">
				<div class="art-blockheader">
					<div class="l"></div>
					<div class="r"></div>
					<h3 class="t">Payment Collections</h3>
				</div>
				<div class="art-blockcontent">
					<div class="art-blockcontent-body">
<!--collections for the day !-->
<?php //[start] start for commit to remote PS (code status: uploaded) ?>
<p><?php echo "Today: Php"."&nbsp;". cls_misc::gFormatNumber(cls_bill_get::collection_today());?> </p><br>
<p>FY:<?php echo date('Y');?>&nbsp;Monthly Collections
	<table width="100%">
		<tr><td>Month</td><td>Collection(Php)</td></tr>
		<tr><td>January&nbsp;</td><td><?php echo cls_bill_get::collection_today("01")?></td></tr>
		<tr><td>February&nbsp;</td><td><?php echo cls_bill_get::collection_today("02")?></td></tr>
		<tr><td>March&nbsp;</td><td><?php echo cls_bill_get::collection_today("03")?></td></tr>
		<tr><td>April&nbsp;</td><td><?php echo cls_bill_get::collection_today("04")?></td></tr>
		<tr><td>May&nbsp;</td><td><?php echo cls_bill_get::collection_today("05")?></td></tr>
		<tr><td>June&nbsp;</td><td><?php echo cls_bill_get::collection_today("06")?></td></tr>
		<tr><td>July&nbsp;</td><td><?php echo cls_bill_get::collection_today("07")?></td></tr>
		<tr><td>August&nbsp;</td><td><?php echo cls_bill_get::collection_today("08")?></td></tr>
		<tr><td>September&nbsp;</td><td><?php echo cls_bill_get::collection_today("09")?></td></tr>
		<tr><td>October&nbsp;</td><td><?php echo cls_bill_get::collection_today("10")?></td></tr>
		<tr><td>November&nbsp;</td><td><?php echo cls_bill_get::collection_today("11")?></td></tr>
		<tr><td>December&nbsp;</td><td><?php echo cls_bill_get::collection_today("12")?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>Total&nbsp;</td><td><strong><?php echo cls_bill_get::collection_today('all')?></strong></td></tr>
	</table>
</p>
<?php //[start] start for commit to remote PS ?>


										<div class="cleared"></div>
					</div>
				</div>
		<div class="cleared"></div>
	</div>
</div>

					  <div class="cleared"></div>
					</div>
				</div>
			</div>
			<div class="cleared"></div>