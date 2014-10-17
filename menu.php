<!--menu starts!-->
<?php 
	include_once 'cls_user.php';
	include_once 'forms.php';
	#this will check session id
	if($_SESSION['profileid']==""){
		$profile_id = "x";
	}else{
		$profile_id = $_SESSION['profileid'];
	}
?>

<div class="art-nav">
	<div class="art-nav-l"></div>
	<div class="art-nav-r"></div>
<div class="art-nav-outer">
	<ul class="art-hmenu">
	<?php 
	?>
			<li><a href="index.php" class="active"><span class="l"></span><span class="r"></span><span class="t">HOME</span></a></li>
			<!--for jws_ofc only!-->
			<?php
				if(cls_user_get::bool_priv_action($profile_id)=="2"){ //grant action for jws_ofc only
					?>
			<li>
			<a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Billing</span></a>
				<ul>
					<li><a href="#">Create Billing</a>
						<ul>
							<li><a href="billing.php?request=accnt">Individual Billing</a></li>
							<li><a href="billing.php?custom=ReadingFormOptBrgy">Batch Billing</a></li>
                            <li><a href="billing.php?request=dl2_bill">Get Current Month Billing</a></li>
						</ul>
					</li>
					<li><a href="#">Download Billing</a>
                        <ul>
                            <li><a href="billing.php?request=dl_bill">By Date</a></li>
                            <li><a href="billing.php?request=dl_bill&filter=<?php echo base64_encode(rand(1,10))?>">By Barangay</a></li>
                        </ul>
                    </li>
					<li><a href="#">Meter Reading</a>
						<ul>
							<li><a href="billing.php?custom=CreateReadingSheet">Create Meter Reading Sheet</a></li>
							<li><a href="#">View Reading Sheet Created</a></li>
						</ul>
					</li>
                    <?php //todo -o mike -p 10 -c For Upload: [start]add the menu for additional billing option ?>
                    <li><a href="billing.php?custom=AddBilling">Additional Billing</a></li>
                    <?php //todo -o mike -p 10 -c For Upload: [end]add the menu for additional billing option ?>
				</ul>
			</li>    
			<li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">LEDGER</span></a>	
			<ul>
				<li><a href="ledger_content.php?action=new">New</a></li>
				<li><a href="ledger_content.php?action=search">Search</a></li>
				<li><a href="ledger_content.php">Update</a></li>
				<li><a href="ledger_content.php?action=delete">Delete</a></li>
				 <?php //todo [start] stage for commit to remote PS(status:done) ?>  				
				<li><a href="utility.php?action=TransConBrgy">Barangay Transfer</a></li>
				<li><a href="utility.php?action=getPLUsers">Inactive Concessionaires</a></li>
                <li><a href="utility.php?action=test">Duplicated Entries</a></li>
				<?php //todo [end] stage for commit to remote PS ?>
			</ul>
			</li>
			<li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Disconnection</span></a>    
			<ul>
				<li><a href="listings_content.php?action=for_disconnection_all">ALL Notices</a></li>
				<li><a href="">Notices Per Barangay</a>
					<ul>
				 <?php include_once 'cls_codes.php'; $data=cls_misc::getCodesDescrValuesToArray("select * from codes where category='Barangay' order by descr asc");?>
				 <?php foreach($data as $key=>$value){ ?>
					 <li><a href="listings_content.php?action=for_disconnection_all&filter=<?php echo $value['codes']?>"><?php echo $value['description'] ?> </a></li>
				 <?php } ?>                 
				</ul>
				</li>
			</ul>
            
            <?php //todo: FFU#76 ?>
<!--designated for deletion starts here-->
<!--
                <li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Cashier</span></a>
                    <ul>
                        <li><a href="cashier.php">Bills Payment</a></li>
                        <li><a href="cashier.php?request=other_payments">Other Payments</a></li>
                    </ul>
                </li>
<!--designated for deletion ends here-->
                <?php //FFU#32  ?>
                 <li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Reports</span></a>
                    <ul>
                        <li><a href="listings_content.php?action=optJWSreport1">Collection/Usage Reports</a></li>
                        <li><a href="utility.php?action=ORSearch">OR Search</a></li>
                        <li><a href="utility.php?action=GeneralProfileSearch">General Profile Search</a></li>
                    </ul>
                </li> 
                <li><a href="lstEmpl.php" class="active"><span class="l"></span><span class="r"></span><span class="t">LGU-Employees</span></a></li>                               
		</li>    
<?php //[END]stage for commit to PS ?>
			<?php } ?>
			
		<?php
			if(cls_user_get::bool_priv_action($profile_id)=="3"){ //grant action for cashier only
                include_once 'cls_user.php';
                $CollectorsName=cls_user_get::CollectorsName();
				?>
				<li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Cashier</span></a>
					<ul>
						<li><a href="cashier.php">Bills Payment</a></li>
						<li><a href="cashier.php?request=other_payments">Other Payments</a></li>
					</ul>
				</li>
                <li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Collector's Report</span></a>
                    <ul>
                        <?php for($i=0;$i < count($CollectorsName);$i++){ ?>
                        <li><a href="cashier.php?request=collectors_report&un=<?php echo base64_encode($CollectorsName[$i]);?>"><?php echo $CollectorsName[$i]; ?></a></li>
                        <?php } ?>
                    </ul>
                </li>                
		<?php } ?>	
		<!--administrative page only!-->
		<?php  if(cls_user_get::bool_priv_action($profile_id)=="1"){?>
		<li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Account</span></a>
			<ul>
				<li><a href="#" class="active"><span class="l"></span><span class="r"></span><span class="t">Users</span></a>
					<ul>
						<li><a href="admin.php?req=add">Add</a></li>
						<li><a href="admin.php?req=view">View</a></li>
					</ul>
				</li>
			</ul>
		</li>
		<?php }?>	
		
					<!--for accounting only!-->
			<?php
				if(cls_user_get::bool_priv_action($profile_id)=="4"){ //grant action for accounting only
					?>
		<li>
			<a href="" class="active"><span class="l"></span><span class="r"></span><span class="t">REPORTS</span></a>
			<ul>
				<li><a href="listings_content.php?action=receive_receipt">Receivables</a></li>
			<!-- 	<li><a href="listings_content.php?action=billings">Process Billings</a></li> -->
				<li><a href="listings_content.php?action=list_profile">Profile & Ledger Status</a></li>
				<li><a href="listings_content.php?action=awws_select_filter">Abstract Reports for JWS</a></li>
				<li><a href="listings_content.php?action=names_barangay">Names Per Barangay</a></li>
				<li><a href="listings_content.php?action=graph_report">Represent Graph</a></li>  
			</ul>	
		</li>
        
        
			<?php } ?>		
		<li><a href="user_manual/index.htm" class="active" target="_blank"><span class="l"></span><span class="r"></span><span class="t">HELP</span></a></li>		
	</ul>
</div>
</div>
<div class="cleared reset-box"></div>