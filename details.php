 <?php
//error_reporting(E_ALL ^ E_NOTICE);
sleep(3);
	$name = strtoupper(trim($_POST['accnt']));
	$con = mysql_connect("localhost","root","ict");
	$db= mysql_select_db("table1", $con);
	$sql = "SELECT * from profile where applicant like '%{$name}%'";
	$result = mysql_query($sql);
		//$row=mysql_fetch_array($result);
			if(mysql_num_rows($result)>0){
				?>
				<br><br><br>
				<center>
				<table>
					<tr>
						<td>Account No.</td>
						<td>Concessionaire Names</td>
					</tr>
				<?php
				while($row = mysql_fetch_array($result)){
					?><tr>
					<td><a href="form_handler.php?cashier=show_ledger1&accnt=<?php echo $row['acct_no'];?>"><?php echo $row['acct_no'];?></a></td>
					<td><?php echo $row['applicant']?></td> 
					</tr> <?php
					}?>
			</table>
			</center>
			<?php		
			}else{
				?><center><font color="red"><strong>Account Name Does Not Exist</strong></font></center><?php
			}
			
		
		
		
	//}catch(Exception $e){
		//throw new Exception("Database SQL statement Error");
		//echo "Error on PHP file&nbsp;".$e->getFile()."&nbsp;on line&nbsp;".$e->getLine()."&nbsp;Error Message=".$e->getMessage();
	//}



?>

