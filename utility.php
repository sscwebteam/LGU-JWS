<?php 
include_once 'header.php';
include_once 'menu.php'
?>
<!--	<link rel="stylesheet" href="jui/themes/base/jquery.ui.all.css">
	<script src="jui/jquery-1.9.1.js"></script>
	<script src="jui/ui/jquery.ui.core.js"></script>
	<script src="jui/ui/jquery.ui.widget.js"></script>
	<script src="jui/ui/jquery.ui.position.js"></script>
	<script src="jui/ui/jquery.ui.menu.js"></script>
	<script src="jui/ui/jquery.ui.autocomplete.js"></script>
	<link rel="stylesheet" href="jui/demos/demos.css">-->
	<?php include_once 'cls_codes.php' ?>
	<script>
	$(function() {
		var availableTags = [<?php echo cls_misc::sample_data_forAC()?>];
		$( "#names" ).autocomplete({
			minlength:5,
			source: availableTags,
			select:function(event,ui){if(event.keyCode==13) { getBrgyCode($.trim($('#names').val()))}}
		});

        $( "#accnt_names" ).autocomplete({
            minlength:5,
            source: availableTags
            //select:function(event,ui){if(event.keyCode==13) { getBrgyCode($.trim($('#names').val()))}}
        });
        

	});
	</script>
	<script>
		function getBrgyCode(NameValue){
			//alert('value passed' + NameValue);
			var SplitValues=NameValue.split('|');
			var AccountNo=$.trim(SplitValues[1]);
			//alert('split value='+AccountNo);
			getBarangayOpt(AccountNo,'from_brgy','from_brgy');
			getBarangayOpt(AccountNo,'to_brgy','to_brgy');
		}
	</script>

<?php
switch($_GET['getUtil']){
	case 'brgy_transfer':
			include_once 'db_conn.php';
			include_once 'cls_codes.php';
			include_once 'cls_user.php';
					   
			//variables
			$SplitValues=explode('|',$_GET['names']); 
			$account_no=$SplitValues[1] ;
			//echo "<br>".$account_no;
			$from_brgy= cls_user_get::tblDate_Sched_Value('bryg_codes',$_GET['from_brgy'],'date_meter_reading');
			//echo "<br>from_brgy={$from_brgy}";
			$to_brgy=cls_user_get::tblDate_Sched_Value('bryg_codes',$_GET['to_brgy'],'date_meter_reading');
			//echo "<br>to_brgy={$to_brgy}";
			$LedgerTable=cls_misc::ConvertToTableName($account_no);
			//echo "<br>Ledgertable=".$LedgerTable;
			//start sql
			$sql1="select * from {$LedgerTable} order by reading_date asc";
			$e=new Exception();
			$qry1=mysql_query($sql1) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
			while($row1=mysql_fetch_array($qry1)){
				$criteria=$row1['reading_date'];
				$DatesExplode=explode('-',$criteria);
				$sql2="update {$LedgerTable} set reading_date='{$DatesExplode[0]}-{$DatesExplode[1]}-{$to_brgy}' where reading_date='{$criteria}'";
				$e=new Exception();
				$qry2=mysql_query($sql2) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
			}
			$sql1="update profile set address_brgy='{$_GET['to_brgy']}' where acct_no='{$account_no}'";
			$e=new Exception();
			$qry1=mysql_query($sql1) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
			echo "<br><br><center><h3>TRANSFER HAS BEEN SUCCESSFULLY COMPLETED</h3></center><br><br>";
		
		break;
}

  switch($_REQUEST['action']){
		case 'test':
            include_once 'db_conn.php';
            include_once 'cls_user.php';
            include_once 'cls_codes.php';
            $Accounts=cls_user_get::all_concessionaires_account_no();
            echo "<center><table><tr><td colspan=3>List of Accounts with Duplicated Entry</td></tr>";
            for($i=0;$i < count($Accounts);$i++){
                $LedgerTable=cls_misc::ConvertToTableName($Accounts[$i]);
                $sqlDoubleEntry="select * from {$LedgerTable} group by reading_date having count(reading_date) > 1";
                $e=new Exception();
                $qryDoubleEntry=mysql_query($sqlDoubleEntry) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                if(mysql_num_rows($qryDoubleEntry) !=0){
                    while($rowDoubleEntry=mysql_fetch_array($qryDoubleEntry)){
                        echo "<tr><td>{$Accounts[$i]}</td>";
                        echo "<td>&nbsp;&nbsp&nbsp&nbsp</td>";
                        echo "<td>Duplicate on {$rowDoubleEntry['reading_date']}</td></tr>";
                    }
                }
            }
            echo "</table></center>";
            
           
            break;
            
        case 'ORSearch': //todo FFU#63
            include_once 'forms.php';
            cls_forms::SearchOR();
            break;
        case 'count_cu_used':
            include_once 'db_conn.php';
            include_once 'cls_codes.php';
            include_once 'cls_user.php';
            $brgy='brgy_4_2';
            $YearMonthValue='2013-11-%';
            $Cons=cls_user_get::all_concessionaires_account_no($brgy);
            echo "total concessionaires=".count($Cons).'<br>';
            for($i=0;$i < count($Cons);$i++){
                $LedgerTable=cls_misc::ConvertToTableName($Cons[$i]);
                
                $sql="select * from {$LedgerTable} where reading_date like '{$YearMonthValue}'";
                
                $e=new Exception();
                $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line: ".$e->getLine());
                $row=mysql_fetch_array($qry);
                echo "Ledger={$LedgerTable}-{$row['cu_used']}<br>";
                $totalCuMUsed=$row['cu_used'] + $totalCuMUsed;             
            }
            echo "total cubic used=".$totalCuMUsed."<br>";
            break;

        case 'alter_table':
            set_time_limit(0);
            include_once 'cls_user.php';
            include_once 'cls_codes.php';
            $ColumnsToAdd='AF';
            $ColumnsRefAfter='';
            $arrConcessionaires=cls_user_get::all_concessionaires_account_no();
            for($i=0;$i < count($arrConcessionaires);$i++){
                $LedgerTable=cls_misc::ConvertToTableName($arrConcessionaires[$i]);
                echo "Processing TableName={$LedgerTable}<br><br><br>";
                $sql="select AF from {$LedgerTable}";
                $qry=mysql_query($sql);// or die(mysql_error());
                echo mysql_errno()."<br>";
                if(mysql_errno()!=0){
                    $sql="ALTER TABLE {$LedgerTable} ADD AF TEXT NOT NULL AFTER loans_MF";
                    $qry=mysql_query($sql) or die(mysql_error());
                }
            }
            echo "done!";
            break;
        
        case 'admin_override':
            include_once 'db_conn.php';
            $sql="update tbl_usr_crd set status='0' where un='admin'";
            @mysql_query($sql);
            break;
            
        case 'getPLUsers':
			//include_once 'db_conn.php';
            include_once 'cls_codes.php';
            include_once 'cls_user.php';
            include_once 'cls_excel.php';
            //create table to database for arbitrary storage
            $TableName="tp_inactive_".date('YmdHis');
            $sqlCreateTable="create table {$TableName} like tp_inactive";
            $e=new Exception();
            $qryCreateTable=mysql_query($sqlCreateTable) or die(mysql_error()."___File=". $e->getFile()."___Line=". $e->getLine());
            //end of table creation
			
            
            $accounts=cls_user_get::all_concessionaires_account_no();
			echo "<br><br><center><table cellspacing='1' cellpadding='1' width='80%' bgcolor='white'><tr><td colspan='4'><center><h3><strong>Ledger with PL status</strong></h3></center></td></tr>";
			echo "<tr><td colspan='4'>&nbsp;</td></tr>";
			echo "<tr bgcolor='#AAAAAA'><td>Account No</td><td>Name</td><td>Barangay</td><td>Inactive Since</td></tr>";
			for($i=0;$i<count($accounts);$i++){
				$LedgerTable=cls_misc::ConvertToTableName($accounts[$i]);
				if(cls_user_get::isPadLock($LedgerTable)==true){
					//table columns=accnt_no, name, brgy,inactive since
					$data_row="<tr>";
					$data_row=$data_row."<td>".$accounts[$i]."</td>";
					$data_row=$data_row."<td>".cls_user_get::ProfileValue('acct_no',$accounts[$i],'applicant')."</td>";
					$data_row=$data_row."<td>".cls_misc::toString(cls_user_get::ProfileValue('acct_no',$accounts[$i],'address_brgy'),'Barangay')."</td>";
					$sql_1="select * from {$LedgerTable} where remarks like '%PL%'";
					$e=new Exception();
					$qry_1=mysql_query($sql_1) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
					$row_1=mysql_fetch_array($qry_1);
					$data_row=$data_row."<td>".$row_1['reading_date']."</td></tr>";
                    //parameters
                    $account_no=$accounts[$i];
                    $account_name=cls_user_get::ProfileValue('acct_no',$accounts[$i],'applicant');
                    $account_barangay=cls_misc::toString(cls_user_get::ProfileValue('acct_no',$accounts[$i],'address_brgy'),'Barangay');
                    $account_inactive_since=$row_1['reading_date'];
                    //end of parameters
                    //start inserting to arbitrary storage
                    $sqlInsertArbStorage="insert into {$TableName}(accnt_no,accnt_name,inactive_since,barangay)values('{$account_no}','{$account_name}','{$account_inactive_since}','{$account_barangay}')";
                    $e=new Exception();
                    $qryInsertArbStorage=mysql_query($sqlInsertArbStorage) or die(mysql_error()."____File: ".$e->getFile()."____Line: ".$e->getLine());
                    //end inserting to arbitrary storage
                                        
					echo $data_row;
				}
			}
			echo "<tr bgcolor='#AAAAAA'><td>Account No</td><td>Name</td><td>Barangay</td><td>Inactive Since</td></tr>";
			echo "</table>"	;
            //call an external function to process the data export to excel
            cls_Excel_Export::InactiveUsers($TableName);
            
			break;
     
        case 'backup_database':
            //procedure
            //1. url=utility.php?action=backup_database&db={sourceDb}
            
            ini_set("memory_limit",-1);set_time_limit(0);
            //parameters require
            //make sure that each database contains the table named //tablelisting// for table audit
            $SourceDB=$_REQUEST['db'];
            
            $host=mysql_connect("localhost","root","webteam2012");
            $db=mysql_selectdb("table1",$host);
            
            //$SQL_GetAllTableNames="select * from tables where table_schema='{$SourceDB}'";
            $SQL_GetAllTableNames="show tables";
            $e=new ErrorException();
            $QRY_GetAllTableNames=mysql_query($SQL_GetAllTableNames) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            
            while($ROW_GetAllTableNames=mysql_fetch_array($QRY_GetAllTableNames)){
                $Tables[]=$ROW_GetAllTableNames['Tables_in_table1'];
            }
            
            mysql_selectdb($SourceDB,$host);
			
			//include_once 'db_conn.php';
			include_once 'cls_codes.php';
            $BackupDir=$_SERVER['DOCUMENT_ROOT']."/lgu_jws/backup_data/";
			$FileExt=".csv";
			//Proced-1
			for($i=0;$i<count($Tables);$i++){
                $outfile=$BackupDir.$Tables[$i].$FileExt;
                $TableName=$Tables[$i];
                //sleep(5);
                if(file_exists($outfile)){unlink($outfile);}
                $e=new Exception();
                $sql="select * from {$Tables[$i]} into outfile '{$outfile}' fields terminated by ',' enclosed by '\"' lines terminated by '\n'";
                mysql_query($sql) or die(mysql_error()."__FILE=".$e->getFile()."__LINE".$e->getLine());
                echo "Status: ". number_format((($i+1)/count($Tables)) * 100,2) . "% Completed<br>";
                $sql="insert into tablelisting(TableName)values('{$TableName}')";
                mysql_query($sql) or die(mysql_error());
            }
			break;
        
          case "restore_database":
        //procedure:
            //1. export the source database to be restore by copying the structure only
                //note: importing and exporting tables structure only is much faster that recreating all tables by means of php codes
                
            //2. import the sql file exported from the source database to the destination database
            //3. use this function to export data to the destination database
                //url: utility.php?action=restore_database&destDB={destinationDb}
        set_time_limit(0);
            //parameters: 
            $DestDb=$_REQUEST['destDB'];
            $host=mysql_connect("localhost","root","webteam2012");
            $db=mysql_selectdb($DestDb,$host);
            //const Table "tablelisting"
            $BackupDir=$_SERVER['DOCUMENT_ROOT']."/lgu_jws/backup_data/RPS/2013-11-28/";
            $FileExt=".csv";
            
            //import first the tablelisting table as the source of the table names
            $ConstTable="tablelisting";
            $outfile=$BackupDir.$ConstTable.$FileExt;
            $e=new Exception();
            $sql="truncate table {$ConstTable}";
            mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            //load file to database
            $sql="load data infile '{$outfile}' into table {$ConstTable} fields terminated by ',' enclosed by '\"' lines terminated by '\n'";
            $e=new Exception();
            mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."_Line:".$e->getLine());      
            echo "data loaded to tablelisting<br>";     
            
            //iterate all files present in the constant table
            $sql="select * from tablelisting";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error());
            while($row=mysql_fetch_array($qry)){
                $TableNames[]=$row['TableName'];
            }
            echo "test:total tables to be inserted=".count($TableNames)."<br>";
            //break;
            for($i=0;$i < count($TableNames);$i++){
            if($TableNames[$i]!='tablelisting'){
                $outfile=$BackupDir.$TableNames[$i].$FileExt;
                    //load data
                $sql="load data infile '{$outfile}' into table {$TableNames[$i]} fields terminated by ',' enclosed by '\"' lines terminated by '\n'";
                $e=new Exception();
                mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());    
                }
                echo "Restore Progress: ".number_format(($i+1)/count($TableNames)*100,2)."%<br>";
            }
            break;

		case 'TransConBrgy':
			include_once 'forms.php';
			cls_forms::BrgyTransfer();
		break;

	  case 'trim': //trim all values in the OR_num column
		$TargetColumn=trim($_REQUEST['TargetColumn']);
		include_once 'db_conn.php';
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
		$accounts=cls_user_get::all_concessionaires_account_no();
		echo "<br>Test:Total Accounts for checking=".count($accounts);
		for($i=0;$i < count($accounts);$i++){
			$LedgerTable=cls_misc::ConvertToTableName($accounts[$i]);
			echo "<br>Checking on LedgerTable[{$i}]=".$LedgerTable;
			$sql_1="select * from {$LedgerTable}";
			$e=new Exception();
			$qry=mysql_query($sql_1) or die(mysql_error()."__FILE:".$e->getFile()."__LINE:".$e->getLine());
			while($row_1=mysql_fetch_array($qry_1)){
				$TrimColumn=trim($row[$TargetColumn]);
				$sql_2="update {$LedgerTable} set {$TargetColumn}='{$TrimColumn}' where {$TargetColumn}='{$row['$TargetColumn']}";
			}
		}
		break;

	  case 'strip_comma':
		include_once 'db_conn.php';
		include_once 'cls_user.php';
		include_once 'cls_codes.php';
		$accounts=cls_user_get::all_concessionaires_account_no();
		echo "<br>Test:Total Accounts for checking=".count($accounts);
		for($i=0;$i < count($accounts);$i++){
			$LedgerTable=cls_misc::ConvertToTableName($accounts[$i]);
			echo "<br>Checking on LedgerTable[{$i}]=".$LedgerTable;
			$sql_1="select * from {$LedgerTable} where total like '%,%' or bill_amnt like '%,%' or pen_fee like '%,%' ";
			echo "<br>sql statement=".$sql_1;
			$e=new Exception();
			$qry_1=mysql_query($sql_1) or die(mysql_error()."File:".$e->getFile()."__Line:".$e->getLine());
			echo "<br>record counts=".mysql_numrows($qry_1);
			while($row_1=mysql_fetch_array($qry_1)){
				echo "<br>Starting comma stripping..";
				$bill_amnt=$row_1['bill_amnt'];
				echo "<br>bill_amnt value=".$bill_amnt;
				$total=$row_1['total'];
				echo "<br>total value=".$total;
				$new_bill_amnt=str_replace(',','',$bill_amnt);
				echo "<br>new_bill_amnt=".$new_bill_amnt;
				$new_total= str_replace(',','',$total);
				echo "<br>new_total value=".$new_total;
				$sql_2="update {$LedgerTable} set bill_amnt='{$new_bill_amnt}' where bill_amnt='{$bill_amnt}'";
				mysql_query($sql_2) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
				$sql_2="update {$LedgerTable} set total='{$new_total}' where total='{$total}'";
				mysql_query($sql_2) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
			}
		}
		echo "<br>Process Done";
		break;

      case 'GeneralProfileSearch':
        include_once 'forms.php';
            cls_forms::GeneralSearch();
        break;
     
  }
?>
<?php include_once 'footer.php'?>