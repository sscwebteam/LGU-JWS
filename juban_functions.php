<?php 
ob_start();
session_start();
//include 'connection.php';
include_once 'db_conn.php';
include_once 'forms.php';


function return_name_acctno($descr){
	#this will return the name and account number of the applicants
	$query = sql_retrieve::request_rows("*","profile","applicant like '%".$descr."%' limit 5");
		$my_array = array(); //this will hold all of the values
	while($row=mysql_fetch_assoc($query)){
		array_push($my_array,array('account_no'=>$row['acct_no'],'applicant'=>$row['applicant']));
	}
	echo json_encode($my_array);
}
function avoid_char($input){
		#escapes input from special characters
		$str = preg_replace("/[^A-Za-z0-9]/","",$input);
		return $str;		
}
function check_users($username,$password){
		#sanitize a variable from characters
		$username = avoid_char(trim($username));
		$password = avoid_char(trim($password));
		#checks the availability of the account
		$sql = mysql_query("select * from tbl_usr_crd where un='{$username}' and pwd='{$password}'" ) or die('Error in getting account credentials'.mysql_error());		
		$sql_row = mysql_num_rows($sql);
		$sql_use =  mysql_fetch_assoc($sql);		
			if($sql_row ==0){
				echo "false";
			}elseif($sql_use['status']=='1'){ //FFU#13
				//echo "<h2>Username already in use.</h2>";//
                echo "false";
			}else{ //equals to 1
				create_session($sql_use['profile_id'],$username,$password,$sql_use['fullname']);
				//todo: [start]stage for commit to remote PS (status:uploaded)
				return "true";
				//todo: [end]stage for commit to remote PS
				header('location:main_file.php');
			}
			//return true;	
}

function create_session($data,$username,$password,$name){
	$_SESSION['profileid'] = $data;
	$_SESSION['username'] = $username;
	$_SESSION['password'] = $password;
	$_SESSION['pangaran'] = $name;
    $sql="update tbl_usr_crd set status='1' where un='{$username}' and pwd='{$password}' and profile_id='{$data}' and fullname='{$name}'";
    $e=new Exception();
    $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
	echo "true";
}
function address(){//retrieves code and description from the database
	$sql_str = "select code,descr from codes where category='Barangay'";
	$sql_query = mysql_query($sql_str) or die('Error in retrieving address'.mysql_error());	
	while  ($my_row[]=mysql_fetch_assoc($sql_query)){}
	return $my_row;
}
function month_explode(){// gets code and description from the database
			$sql_str = "select code,descr from codes where category='month' limit 12";
			$sql_query=mysql_query($sql_str);
			while ($my_month[]=mysql_fetch_assoc($sql_query)){}
			return $my_month;
}
		
function day_explode(){// iterates 1 to 31 days 
			$i=1;
			while ($i<>32){
				$my_row[]=$i;
				$i++;
			}
			return $my_row;
}
function year_explode(){ //iteraties 2008 t0 2025
			$cur_year=date('Y');
			for($i=0;$i<=3;$i++){
				$my_row[]=$cur_year - $i;
			}
//			$i=2008;
/*			while ($i<>2026){
				$my_row[]=$i;
				$i++;
			}
*/			return $my_row;
}
function delete_entry($accountno){//delete records from database
			$sql_str = "delete from profile where acct_no='".$accountno."'";
			$sql_query = mysql_query($sql_str) or die('Error in deletion'.mysql_error());
			//to do: delete ledger
			//to do: delete or_logs
}
function show_code($descr){ //returns the code from descr
		$str = mysql_fetch_assoc(sql_retrieve::request_rows("code","codes where descr='".$descr."'","none"));
		 return $str['code'];
}
class sql_retrieve {
		public function request_rows($demand, $table, $filter){
		$demand = auxilliary::remove_tags($demand);
		$table = auxilliary::remove_tags($table);
		$filter = $filter;
				if ($filter == "none") { 
							$sql_query = mysql_query("select $demand from $table") or die('Error in query'.mysql_error());//counts field requested;							
							return $sql_query;							
				}
				else{		
							$sql_query = mysql_query("select $demand from $table where $filter") or die('Error in query'.mysql_error());							
							return $sql_query;			
				}
}
		public function update_entry($table, $field_name,$new_entry,$basis,$criteria ){// update an entry to the database
			$sql_str = "update " .$table. " set ".$field_name."='".$new_entry."' where $basis='".$criteria."'";
			$sql_query = mysql_query($sql_str) or die('Error in upadate'.mysql_error());
}
		public function insert_entry($table,$fieldname,$entry){//inserts directly to the database
			$sql_str = "insert into $table($fieldname)values('$entry')";
			$sql_query = mysql_query($sql_str) or die('Error in insertion'.mysql_error());
}
		public function create_table($acct){ //FFU#
        include_once 'cls_codes.php';
        $LedgerTableName=cls_misc::ConvertToTableName($acct);
        $TemplateLedgerTable='ldg_tpl';
        $sql="create table {$LedgerTableName} like {$TemplateLedgerTable}";
        		
		/*$str_1 = "create TABLE  `table1`.`ldg_$acct`(`id` int(99) NOT NULL AUTO_INCREMENT PRIMARY KEY, `reading_date` text ,`meter_reading` text ,`cu_used` text  COMMENT 'cubic meter used',";
		$str_2 = "`pen_fee` text  COMMENT 'penalty fee',`bill_amnt` text  COMMENT 'billing amount',`loans_MLP` text  COMMENT 'loans on material loan program',";
		$str_3 = "`loans_MF` text  COMMENT 'loans on meter fee',`misc` text  COMMENT 'misc payments',`total` text ,`OR_num` text  COMMENT 'OR number',`OR_date` text  COMMENT 'OR date',";
		$str_4 = "`remarks` text  COMMENT 'other remarks goes here')ENGINE=InnoDB DEFAULT CHARSET=latin1;";*/
		//$sql_query = mysql_query($str_1.$str_2.$str_3.$str_4) or die('Error in creating a ledger'.mysql_error());
        $e=new Exception();		
        $sql_query = mysql_query($sql) or die('Error in creating a ledger'.mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
}
		public function delete_entry($demand, $table, $criteria, $basis){
			$sql_str = "delete $demand from $table where $criteria =$basis";
			$sql_query = mysql_query($sql_str) or die('Error in deletion'.mysql_error());		
}
		public function drop_table($table){
			$sql_str = "drop table $table";
			$sql_query = mysql_query($sql_str) or die('Error in dropping'.mysql_error());
}
		public function change_name($old_table,$new_table){
			$sql_str = "alter table $old_table rename to $new_table";
			$sql_query =mysql_query($sql_str) or die('Error in renaming table'.mysql_error());		
}
}
class forms_handlers{
		public function form_types($uri,$pangalan,$value){
			if ($value=="wara"){?>
		<input type="<?php echo $uri;?>" name="<?php echo $pangalan;?>">
		<?php }else { ?>
		<input type="<?php echo $uri;?>" name="<?php echo $pangalan;?>" value="<?php echo $value;?>"> 
				<?php }		
		}
}		
class auxilliary{
		public function dagdag_date($pre_date,$quantity){
				$date = new DateTime($pre_date);
				$adlaw = date_add($date,new DateInterval($quantity));
				$adlaw = date_format($adlaw,"Y-m-d");
				return $adlaw;
		}
		public function validation($validate){
				if(trim($validate)==""){
					return true;
				} 
		}	
		public function unique($table,$criteria){
				$sql_str = "select * from $table where $criteria";
				$sql_query = mysql_query($sql_str);
				$count = mysql_numrows($sql_query);
				if ($count>0){
				return true;
				}else{
				return false;
				}
		} 
		public function numeric($number){
			$number = !(is_numeric($number));
			return $number;
				
		}
		public function date_used($adlaw){
			$date = new DateTime($adlaw);
			$date = $date->format('Y-m-d');
			return $date;
		}
		public function remove_tags($raw){
			//$raw = mysql_real_escape_string($raw);
			//$raw = str_replace("\'","",$raw);
			//$raw = str_replace("&","",$raw);
			//$raw = str_replace("\\","",$raw);
			return $raw;
		}
		public function validate_date($adlaw){
				preg_match("/([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})/", $adlaw, $regs); 
				 if($regs[0]==false){
					 return false;
				 }else{
					 return true;
				 }		
		}
		public function return_dates_for_graph(){
			#returns the month and day as array to be used as parameters in graph
			#creates an array
			$adlaw=array();
			$str = sql_retrieve::request_rows("distinct(or_date) as adlaw","or_log","none");
			while($row=mysql_fetch_assoc($str)){
				array_push($adlaw,array(substr($row['adlaw'],0,4),substr($row['adlaw'],5,2)));
			}
			return $adlaw;
		}
		   
}
?>