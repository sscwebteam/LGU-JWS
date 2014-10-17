<?php
session_start();   
	include_once 'juban_functions.php';
	include_once 'cls_codes.php'; 
	include_once 'header.php';
	include_once 'menu.php';
	//[start] stage for commit to remote PS (status: uploaded)
	?>
    
    <script type="text/javascript" src="script/ajax-action-billing.js"></script><?php
	//[end] stage for commit to remote PS
 
	#this will check unauthorized users
	//todo [temporary disabled for testing purposes only]
	if ($_SESSION['profileid'] ==""){
		echo "<center style='color:red;font-size:20pt;'>Unauthorized access of page is detected</br></br><a style='color:red;font-size:12pt;' href='main_file.php'>Go homepage</a><center>";	
		die;
	}
?>

<!--center content starts!-->
<div class="art-content-layout">
	<div class="art-content-layout-row">
		<div class="art-layout-cell art-content">
<div class="art-post">
	<div class="art-post-body">
<div class="art-post-inner art-article">
	<h2 class="art-postheader">Billing</h2>
				<div class="cleared"></div>
<div class="art-postcontent">

<?php
//process content request
	//[start] stage for commit to remote PS (status: uploaded)
	switch ($_REQUEST['custom']){
        case 'AddBilling':
            include_once 'forms.php';
            cls_forms::AddBillingPayment();
            break;
		case 'CreateReadingSheet':   
			include_once 'forms.php';
			cls_forms::optCreateReadingSheet();
			break;
		case 'BatchBilling':
			include_once 'forms.php';
			include_once 'cls_user.php';
			$brgy=$_REQUEST['brgy'];
			//$accnt_limit=cls_user_get::ForBatchBilling('brgy_1');
			$accnt_limit=cls_user_get::ForBatchBilling("{$brgy}");
			$TotalData_Array=count($accnt_limit)/25;
			$page=explode(".",$TotalData_Array);
			$TotalPages=$page[0];
			if($page[1] > 0){
				$TotalPages=$TotalPages + 1;
			}
			$i=0;
			while($i < 25){
				cls_forms::BatchBillingForm_Content($accnt_limit[$i],$i+1);
				$i++;    
			}
			?><center>
			<?php //[start] stage for commit to remore PS(status: done)?>
			<span class="art-button-wrapper"><span class="art-button-l">
			</span><span class="art-button-r"></span><a href="billing.php?custom=BatchBilling&brgy=<?php echo $brgy?>" class="art-button">Click here for Next <?php echo $TotalPages - 1;?> Page(s)</a></center><?php
			//[end] stage for commit to remote PS
			break;
			
			case 'ReadingFormOptBrgy':
				include_once 'forms.php';
				cls_forms::optBarangay_Codes();
				break;
	}
		
	//[start] stage for commit to remote PS
	switch ($_REQUEST['request']) {

        case "dl2_bill":
            include_once 'forms.php';
            cls_forms::CreateBillingForCurrentMonth();
            break;

		case 'accnt':
			include_once 'forms.php';
			include_once 'cls_user.php';
			//$login_status=cls_user_get::chk_login();
			//if($login_status=='0'){
				//cls_user_get::login();
				
			//}else{
				cls_forms::bill_accnt();
			//}
			
			break;

		case 'dl_bill': //used to download billing
			include_once 'forms.php';
            $ActionFilter=$_REQUEST['filter'];
            cls_forms::ListBrgy($ActionFilter); //show choices for creation of billing
			break;

		case 'init_dl': //used to create billing form on excel file,refer to billing for more details
			$accnt_no=base64_decode($_REQUEST['accnt_no']);
			$last_bill=base64_decode($_REQUEST['last_bill']);
			$ledger_row = sql_retrieve::request_rows("*","ldg_".str_replace("-","_",$accnt_no)," reading_date='".$last_bill."'"); //gets the billed row
			$last_bill_result=mysql_fetch_assoc($ledger_row); 
			break;

/*		default:
			include_once 'forms.php';
			cls_forms::frm_login_generic();
			break;
*/
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
	include_once 'sidebar.php';
	include_once 'footer.php';
?>
<?php
/*function ma_download($values,$acctno,$profile){
	 #excel declaration
			error_reporting(E_ALL);
			date_default_timezone_set('Europe/London');    
			require_once '../Classes/PHPExcel.php';
			$objReader = new PHPExcel_Reader_Excel5();
			$objPHPExcel = $objReader->load("accnts/gagamit.xls");    
			ini_set("memory_limit","-1");
	  ##end of excel declarationi      
	 #start of writing
		 $objPHPExcel->getActiveSheet()->setCellValue('b10',$values['meter_reading']); //present reading
		 $objPHPExcel->getActiveSheet()->setCellValue('c10',$values['meter_reading']-$values['cu_used']); //previous
		 $objPHPExcel->getActiveSheet()->setCellValue('d10',$values['cu_used']); //cu_used
		 $objPHPExcel->getActiveSheet()->setCellValue('e10',$values['bill_amnt']); //bill_amnt
		 //$objPHPExcel->getActiveSheet()->setCellValue('c10',$values['meter_reading']-$values['cu_used']); //previous
		 //$objPHPExcel->getActiveSheet()->setCellValue('c10',$values['meter_reading']-$values['cu_used']); //previous
		 $objPHPExcel->getActiveSheet()->setCellValue('e14',$values['loans_MLP']); //material loan        
		 $objPHPExcel->getActiveSheet()->setCellValue('d18',$values['total']); //amouunt
		 $objPHPExcel->getActiveSheet()->setCellValue('d20',cls_misc::toString(substr($values['reading_date'],5,2),"month")."(".substr($values['reading_date'],0,4).")"); //month of
		 $objPHPExcel->getActiveSheet()->setCellValue('d22',$acctno); //accout number
		 $objPHPExcel->getActiveSheet()->setCellValue('d23',$profile['meter_no']); //serial
		 $objPHPExcel->getActiveSheet()->setCellValue('c25',$profile['name']); //name
		 $objPHPExcel->getActiveSheet()->setCellValue('c26',$profile['address_brgy']); //address
		// $objPHPExcel->getActiveSheet()->setCellValue('d28',); //due_date
		 $objPHPExcel->getActiveSheet()->setCellValue('d30',$_SESSION['username']); //prepared by
		 $objPHPExcel->getActiveSheet()->setCellValue('d33',$values['meter_reading']-$values['cu_used']); //date
		
		 
	 #force browser to download         
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="relan.xls');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
	 ##end of force browser download           
	 #close excel        
				$objPHPExcel->disconnectWorksheets();                
				unset($objPHPExcel);       
	 ##end of close excel        
}    */
?>