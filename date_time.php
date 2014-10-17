<?php
class cls_date_get{

	public function last_reading_info($accnt_no,$field){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$accnt_no=cls_misc::sanitize_hyp($accnt_no);
		$sql_str="select * from ldg_{$accnt_no} order by reading_date desc";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row["{$field}"];
	}

	public function date_add($date_value,$interval_type,$interval_value){
		include_once 'db_conn.php';
		$sql_str="select date_add('{$date_value}',interval {$interval_value} {$interval_type}) as date_sum";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['date_sum'];
	}

	public function bool_date_render($date_entry){
		include_once 'db_conn.php';
		include_once 'date_time.php';
		include_once 'cls_codes.php';
        #commented by admin.rbd
	/*	$sql_str="select month(date(now())) as current_month";
		$sql_qry=mysql_query($sql_str);// or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		$cur_month=cls_misc::toString($row['current_month'],'month');
		$sql_str1="select month('{$date_entry}') as entry_month";
		$sql_qry1=mysql_query($sql_str1);// or die(mysql_error());
		$row2=mysql_fetch_array($sql_qry1);                                               
		$entry_month=cls_misc::toString($row2['entry_month'],'month');
		//if ($cur_month<=$entry_month) { //reading date is acceptable  */ 
           // if(sprintf("%02d",$row['current_month'])>=sprintf("%02d",$row2['entry_month']) || (substr($date_entry,0,4)<=date('Y'))){
           if(date('Y-m-d')>=$date_entry){
			return $date_entry;
		}else {
			$msg="Date not Scheduled";
			return $msg;
		}
	}

	public function year_now(){
		include_once 'db_conn.php';
		$sql_str="select year(now()) as year_now";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['year_now'];
	}

	public function month_now(){
		include_once 'db_conn.php';
		$sql_str="select month(now()) as month_now";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['month_now'];
	}

	public function extract_month($str_date){ //extract the month code,then passed to the cls_misc(cls_codes.php) toStr function
		include_once 'db_conn.php';
		$sql_str="select month('{$str_date}') as month";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['month'];
	}

	public function extract_year($str_date){
		include_once 'db_conn.php';
		$sql_str="select year('{$str_date}') as year";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['year'];
	}

	public function extract_date($str_date){
		include_once 'db_conn.php';
		$sql_str="select day('{$str_date}') as day";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['day'];
	}

	public function MM_DD_yyyy_format($str_date){//convert the given str_date into eg. January 1,2012
		include_once 'date_time.php';
		include_once 'cls_codes.php';
		$year=cls_date_get::extract_year($str_date);
		$month=cls_misc::toString(cls_date_get::extract_month($str_date),'month');
		$day=cls_date_get::extract_date($str_date);
		$out=$month.'&nbsp;'.$day.",".$year;
		return $out;
	}//convert the given str_date into eg. January 1,2012

	public function date_now(){
		include_once 'db_conn.php';
		$sql_str="select date(now())as result";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['result'];
	}
}

class cls_time{

}
?>