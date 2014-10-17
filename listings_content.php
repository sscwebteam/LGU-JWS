<?php 
ob_start();
if(ob_get_contents()!=''){
	//ob_get_clean();
}
session_start();
	include_once 'header.php';
	include 'juban_functions.php';
    #this will check unauthorized users
	if ($_SESSION['profileid'] ==""){
		echo "<center style='color:red;font-size:20pt;'>Unauthorized access of page is detected</br></br><a style='color:red;font-size:12pt;' href='main_file.php'>Go homepage</a><center>";	
		die;
	}
?>

<div class="art-content-layout">
				<div class="art-content-layout-row">
					<div class="art-layout-cell art-content">
<div class="art-post">
	<div class="art-post-body">
<div class="art-post-inner art-article">
	   <?php include_once 'listings.php';?>      
<script type="text/javascript">
function submitform(){
	document.forms[0].submit();
}
</script>                   
								 <?php 
									if(isset($_POST['reporting'])){
										//[start] stage for commit to remote PS(status:ongoing)
										switch(strtolower($_POST['reporting'])){
											case 'prepare_awws_data':
												include_once 'cls_awws.php';
                                                include_once 'cls_cashier.php';
												$vars=$_POST;
												//redirect entry to class for reporting abstract
                                                if($vars['Collector']==''){
												    $AllAccounts=AWWS::fnEntry($vars['month'],$vars['year'],$vars['day']);
                                                }else{
                                                    $AllAccounts=cls_CollectorsReport::fnEntry($vars['month'],$vars['year'],$vars['day'],$vars['Collector']);
                                                }
												break;
										}
										//[end] stage for commit to remote PS(status:ongoing)
									}

									if (isset($_POST['mga_post'])){
										switch ($_POST['mga_post']){
											case 'SHOW REPORT':
												include_once 'listings.php';
                                                include_once 'cls_recvble.php';
                                                $date=$_POST['mdate']; 
                                                $month=$_POST['bulan'];    
                                                $Year=$_POST['taon'];
                                                Recievables::fnEntry($month,$Year,$date);    
/* stage for delete                             //[start] stage for commit to remote PS (status: uploaded)
												 $barangay=trim($_POST['barangay']);
												//assigns the barangay
												$quer = sql_retrieve::request_rows("code","codes","descr='".$_POST['bulan']."'" );
												$bulan = mysql_fetch_row($quer);///gets the code for bulan returns integer
												//checks if receivble or receipts
													if ($_POST['type_list'] =="receivables"){
														//listings_type::receivables($barangay,$_POST['taon'],$bulan[0]);
														listings_type::receivables2($barangay,$_POST['bulan'],$_POST['taon']);
													}	
													else{
														//listings_type::cash_received($barangay,$_POST['taon'],$bulan[0]);
														listings_type::receivables2($barangay,$_POST['bulan'],$_POST['taon'],'1');
														//listings_type::cash_received_excel($barangay,$_POST['taon'],str_pad($bulan[0],2,"0",STR_PAD_LEFT));
													}	// generates result to page and excel for receipts
*/												break; 
												
											case 'BILL CLIENT':
											
												include_once 'listings.php';
												listings_type::billings("2007-11-0783","10","2011");
												break;
												
											case 'SHOW':
												//shows the ledger status
												switch ($_POST['type_show']){
													case 'list_per_barangay':
														listings_type::show_names($_POST['barangay'],$_POST['descr'],$_POST['start']);
														break;
													default:
														listings_type::listings($_POST['start'],$_POST['barangay'],$_POST['year'],$_POST['month'],$_POST['descr']);
														break;
												}
												break;	
											case 'Apply Query':
													include_once 'listings.php';												
												//assigns the barangay
												if ($_POST['barangay']=="general"){
													$barangay = "general";
												}else{//gets the code of barangay						
													$quer = sql_retrieve::request_rows("code","codes","descr='".$_POST['barangay']."'" );
													$barangay = mysql_fetch_row($quer);
													$barangay =$barangay[0];}	
													
												$quer = sql_retrieve::request_rows("code","codes","descr='".$_POST['bulan']."'" );
												$bulan = mysql_fetch_row($quer);///gets the code for bulan returns integer
												//checks if receivble or receipts
													if ($_POST['type_list'] =="outdated"){
													$start = 0;
														listings_type::listings($start,$barangay,$_POST['taon'],$bulan[0],$_POST['barangay']);
														//generates result to page and excel for receivables
													}	
													else{
														//todo:function
													}	// generates result to page and excel for receipts											
												break;	
											case 'Show Names':
                                                    //FFU 8
                                                    $barangay=$_POST['barangay'];
													/*if ($_POST['barangay']=="general"){
														$barangay = "general";
													}else{//gets the code of barangay						
														$quer = sql_retrieve::request_rows("code","codes","descr='".$_POST['barangay']."'" );
														$barangay = mysql_fetch_row($quer);
														$barangay =$barangay[0];}	
													*/
                                                    listings_type::show_names( $barangay,$_POST['barangay'],0);
													//FFU 8
												break;	
											 case 'Show Graph': 
																															 
												break;   
											default:
												include_once 'ledger.php';
												ledger_type::general($_POST['accountno']);
												break;	
										}
							
									 }elseif (isset($_REQUEST['action'])){
										
										switch ($_REQUEST['action']){
                                            
                                            case 'optJWSreport1':
                                                include_once 'forms.php';
                                                cls_forms::frm_JWSReport1();
                                                break;
											//todo:[start] stage for commit to remote PS(status:done)
											case 'for_disconnection_all':
												include_once 'cls_bill.php';
												$FilterBrgy=$_REQUEST['filter'];
												if($FilterBrgy==''){ //null is different from empty
													cls_bill_get::for_disconn1();    
												}else{
													cls_bill_get::for_disconn1($FilterBrgy);
												}
												
												break;
											//todo:[end] stage for commit to remote PS
											
											//[start] create UI for filter set for awws
											case 'awws_select_filter':
												include_once 'cls_codes.php';
                                                include_once 'cls_user.php';
												?>
												<center>Select Filter for Abstract Reports</center>
												<center>
												<form id="form1" method="post" action="listings_content.php">
													<input type="hidden" name="reporting" value="prepare_awws_data"> 
													<table>
														<tr><td>Year</td><td>Month</td><td>Day</td><td>Cashier/Collector Names</td></tr>
														<tr>
															<td><select name="year">
															<option value="" selected><?php echo date('Y')?></option>
															<?php 
																for($i=1;$i<=3;$i++){
																	$value=date('Y') - $i;
																	echo "<option value='{$value}'>{$value}</option>";
																}
															?></select></td>
															<td>
															<select name="month">
																<option value="" selected>Select Month</option>
																<?php $month=cls_misc::getOptMonths();
																	  foreach($month as $key => $value){
																		  echo "<option value='{$value['code_value']}'>{$value['descr_value']}</option>";
																	  }
																?>	
															</select>
															</td>
															<td>
																<select name="day">
																
																	<option value="" selected>Select Date</option>
                                                                    <?php //FFU#95-S
                                                                        $out="<option value=01>01</option>";
                                                                        $out.="<option value=02>02</option>";
                                                                        $out.="<option value=03>03</option>";
                                                                        $out.="<option value=04>04</option>";
                                                                        $out.="<option value=05>05</option>";
                                                                        $out.="<option value=06>06</option>";
                                                                        $out.="<option value=07>07</option>";
                                                                        $out.="<option value=08>08</option>";
                                                                        $out.="<option value=09>09</option>";
                                                                        
                                                                        for($i=10;$i<=31;$i++){
                                                                            $out.= "<option value='{$i}'>{$i}</option>";
                                                                        }   
                                                                        echo $out;
                                                                        //FFU#95-E     
                                                                    ?>
																</select>
															</td>
                                                            <td>
                                                                <select name="Collector">
                                                                    <option value="" selected>Select Collector/Cashier</option>
                                                                    <?php $Names=cls_user_get::CollectorsName(); ?>
                                                                    <?php for($i=0;$i < count($Names);$i++){ ?>
                                                                    <option value="<?php echo $Names[$i]; ?>"><?php echo cls_user_get::FullCollectorsName($Names[$i])?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
														</tr>
														<tr>
															<tr><td colspan="4"><center><span class="art-button-wrapper"><span class="art-button-l">
</span><span class="art-button-r"></span><a href="javascript:submitform();" class="art-button">Download</a></span></center></td></tr>
														
													</table>
												</form>
												</center>
												<?php
												break;
											//[end] create UI for filter set for awws        											
											
											
											
											case 'receive_receipt': 
												include_once 'cls_codes.php';
											?>					 			
											<h2 class="art-postheader">
												<div class="filter">			 		
													<center>Please enter your filter</center>   
														<form method="POST" action="listings_content.php">
															<table class="listings">
																<tr><th>YEAR</th><th>MONTH</th><th>DAY</th></tr>
																	<tbody>
																<tr>
																	<!--<td>
																		<select name="type_list">
																			<option value="receivables">Cash Receivables</option>
																			<option value="receipts">Cash Receipts</option> 
																		</select>
																	</td>-->
																	<!--<td>
																		<?php 
																		$my_row=cls_misc::getOptBarangayNames();//gets address
																		//print_rgam($my_row);
																		?>
																		
																		<select name="barangay"><option value="general">GENERAL</option> 
																			<?php foreach($my_row as $Brgy_Col=>$value){?>
																			<option value="<?php echo $value["codes_value"];?>"><?php echo $value['descr_value'] ?></option>
																			<?php }?>
																		</select>				
																	</td>-->
																	<td><center>
																		<?php $my_row=year_explode();//iterates year?>
																		
																		<select name="taon"><?php foreach($my_row as $taon){?>
																			<option><?php echo $taon;}?></option>
																		</select></center>
																	</td>
																	<td><center>
																		<?php $month=cls_misc::getOptMonths();
																		//print_r($my_row);
																		//iterates month?>
																		<select name="bulan"><option value="">Select Month</option>
																			<?php foreach ($month as $col=>$value){?> 
																			<option value="<?php echo $value["code_value"];?>"> <?php echo $value["descr_value"];?></option>
																			<?php } ?>
																		</select></center>
																	</td>
                                                                    <td><center><select name="mdate"><option value="">Select Date</option>
                                                                            <?php
                                                                              for($i=1;$i<31;$i++){
                                                                              echo "<option value='{$i}'>{$i}</option>";
                                                                              }  
                                                                            ?>
                                                                        </select></center>
                                                                    </td>
																</tr>
																<tr>
																	<td colspan="4" >
																		<center>
																			<input  class="art-button" type="button" value="SHOW REPORT" onclick="submitform();"></input>
																		</center>
																	</td>			
																			<input type="hidden" name="mga_post" value="SHOW REPORT"></input>
																</tr>
																	</tbody>								
														</table>	
													</form>
												</div>
										</h2>
										<?php 			 		
												 break;
											case 'billings':
										?> <form action = "listings_content.php" method="post">
												<input type = "submit" name="submit" value = "BILL CLIENT"></input>
											</form>	<?php 	
												break;
																			
											case 'list_profile':?>
												<h2>
													<div class="filter">			 		
															<center>Please enter your filter</center>   
																<form method="POST" action="listings_content.php">
																	<table>
																		<tr><th>TYPE OF LIST</th><th>BARANGAY</th><th>YEAR</th><th>MONTH</th></tr>
																			<tbody>
																		<tr>
																			<td>
																				<select name="type_list">
																					<!-- <option value="outdated">Outdated Ledgers</option> -->
																				</select>
																			</td>  
																			<td>
																				<?php $my_row=address();//gets address?>
																				
																				<select name="barangay"><option value="general">GENERAL</option> 
																					<?php foreach ($my_row as $address){?>
																					<option><?php echo $address["descr"];}?></option>
																				</select>				
																			</td>
																			<td>
																				<?php $my_row=year_explode();//iterates year?>
																				
																				<select name="taon"><?php foreach($my_row as $taon){?>
																					<option><?php echo $taon;}?></option>
																				</select>
																			</td>
																			<td>
																				<?php $my_row=month_explode();//iterates month?>
																				
																				<select name="bulan"><?php foreach ($my_row as $bulan){?> 
																					<option><?php echo $bulan["descr"];}?></option>
																				</select>
																			</td>
																		</tr>
																		
																		<tr>
																			<td colspan="4" >
																				<center>
																					<input  class="art-button" type="button" value="Apply Query" onclick="submitform();"></input>
																				</center>
																			</td>
																				
																					<input type="hidden" name="mga_post" value="Apply Query"></input>
																		</tr>
																			</tbody>								
																</table>	
															</form>
														</div>
													</h2>	
														<?php 
												break;		
											case 'names_barangay':
                                                //FFU 9
                                                include_once 'cls_codes.php'; 
                                                $OptBarangay=cls_misc::getOptBarangayNames();
                                                //FFU 9
											?>
											<h2>
													<div class="filter">			 		
															<center>Please enter your filter</center>   
																<form method="POST" action="listings_content.php">
																<center>	
																	<table >
																		<tr><th>BARANGAY</th></tr>
																			<tbody>
																		<tr>
																			<td>
                                                                                <?php //FFU 10 ?>
																				<select name="barangay">
                                                                                    <option value="general" selected >GENERAL</option> 
																					<?php foreach ($OptBarangay as $key=>$value){?>
																					<option value="<?php echo $value['codes_value'];?>" >
                                                                                    <?php echo $value['descr_value'];}?></option>
																				</select>
                                                                                <?php //FFU 10 ?>				
																			</td>
																			
																		</tr>
																		
																		<tr>
																			<td colspan="4" >
																				<center>
																					<input  class="art-button" type="button" value="Show Names" onclick="submitform();"></input>
																				</center>
																			</td>
																				
																					<input type="hidden" name="mga_post" value="Show Names"></input>
																		</tr>
																			</tbody>								
																</table>	
															</center>
															</form>
														</div>
													</h2>	
														<?php
											
												break;	
											case 'download_recv':
													listings_type::receivables_excel($_REQUEST['brgy'],$_REQUEST['year'],$_REQUEST['month']);
													echo "natapos q";
												break;	
											case 'graph_report':         
												  ?>   <h2 class="art-postheader"> <?php
												  #gets data dates from dbase 
														$adlaw =  auxilliary::return_dates_for_graph(); 
												   #write to dbase or update  
													   foreach($adlaw as $adlawan){                                                                                                 
															listings_type::reporting($adlawan[1],$adlawan[0]);// bulan nan taon                                                       
													   }                                          
													   header("location:chart.php");  
													   ?></h2>  <?php                                                                                 
												break;    
											default:
												break;	
										}
									 }
									 //else{?>
									 <!-- <h2 class="art-postheader">
										<center>Please enter your account number</center>   
										
								<form method="POST" action="ledger_content.php">
									<?php 
									forms_handlers::form_types("text","accountno","wara");                			
									?>
										<input type="submit" name="submit" value="SHOW LEDGER" > </input>
								</form>
								</h2>--><?php //}?>
				
								<div class="cleared"></div>
								<div class="art-postcontent">
						   

								</div>
				<div class="cleared"></div>
				</div>

		<div class="cleared"></div>
	</div>
</div>

					  <div class="cleared"></div>
					</div></div></div>
<?php //include_once 'sidebar.php';
	include_once 'footer.php';?>                 