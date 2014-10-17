<?php
session_start();
	include_once 'header.php';
	include_once 'menu.php';
	#this will check unauthorized users
	if ($_SESSION['profileid'] ==""){
		echo "<center style='color:red;font-size:20pt;'>Unauthorized access of page is detected</br></br><a style='color:red;font-size:12pt;' href='main_file.php'>Go homepage</a><center>";	
		die;
	}
?>
<script type="text/javascript" src="noname2.js"></script>
<!--center content starts!-->
<div class="art-content-layout">
	<div class="art-content-layout-row">
		<div class="art-layout-cell art-content">
<div class="art-post">
	<div class="art-post-body">
<div class="art-post-inner art-article">
	<h2 class="art-postheader"><center>Cashier Unit</center></h2>
				<div class="cleared"></div>
<div class="art-postcontent">

<?php
//process content request
	
	//check login status first
	include_once 'forms.php';
	include_once 'forms.php';
		   
	switch ($_REQUEST['request']) {
        
        case 'collectors_report':
            $un=base64_decode($_REQUEST[un]);
            //echo "encodedBy value={$un}";
        
            break;
        
		case 'login_ok':
			include_once 'forms.php';
			//check ip,username and password,cookies declaration for authentication
			//if ok,open the form for payment
			cls_forms::frm_cashier_acnt();
			break;

		case 'to_settle':
			include_once 'forms.php';
			include_once 'cls_bill.php';
			$accnt_no=base64_decode($_REQUEST['accnt_no']);
			$biling_date=base64_decode($_REQUEST['bill_date']);
			$amount=base64_decode($_REQUEST['amnt']);
			$penalty=base64_decode($_REQUEST['penalty']);
			cls_forms::frm_cashier_payment($accnt_no,$biling_date,$amount,$penalty);
			break;
		
		case 'other_payments':
			//todo: [start] stage for commit to remote PS(status:ongoing)
			include_once 'forms.php';
			?>
			<br><br>
			<center>
				<form id='form1'>
					<table>
						<tr><td colspan="2"><center><strong>Settle Other Payments</strong></center></td></tr>
						<tr><td>Select Payment:&nbsp;</td><td><?php cls_forms::OtherPayments();?></td></tr>
						<tr><td>Payee Name:&nbsp;</td><td><input type="text" size="50" id='accnt_names' maxlength="50" name="payee" style="width: 500px;" onkeyup="AccntNamesOnly('#accnt_names')"></td></tr>
						<tr><td>Amount:&nbsp;</td><td><input type="text" name="amnt" id="amnt"></td></tr>
                        <tr><td>OR Number:&nbsp;</td><td><input type="text" name="or_num" id="or_num" onkeyup="cCheckAcceptedLength1('or_num','save')"></td></tr>
						<tr><td>Date:&nbsp;</td><td><input type="text" size="50" maxlength="50" name="or_date" id="date" onmouseover="test_date_picker()"></td></tr>
						<tr><td colspan="2"><?php cls_forms::jLinkButton('save','SettleOtherPayment()','Save');?></td></tr>
					</table>	
				</form>
			</center>
			
			<?php
			
			//todo: [end] stage for commit to remote PS
			break;

		default:
			include_once 'forms.php';
			//TODO:check cookies,if not force to login
//			 cls_user_get::login();
			 include_once 'cls_user.php';
			 include_once 'forms.php';
			 if(cls_user_get::chk_login()=='0'){
				cls_forms::frm_login("cashier","login","post");
			 }else{
				cls_forms::frm_cashier_acnt();
			break;
	} } 


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
	include_once 'sidebar.php';
	include_once 'footer.php';
?>