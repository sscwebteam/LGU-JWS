<?php
class cls_misc{
	//[start] stage for commit to remote PS (create open string sql statements)[status: ongoing]
	public function CodestoString($WhereOpenSQL){
		include_once 'db_conn.php';
		$sql_str="select * from codes where {$WhereOpenSQL}";
		$e=new Exception();
		$sql_qry=mysql_query($sql_str) or die(mysql_error($e->getFile()."-".$e->getLine()));
		$row=mysql_fetch_array($sql_qry);
		$value=strtoupper($row['descr']);
		return $value;
	}
	//[end] stage for commit to remote PS (create open string sql statements)[status: ongoing]

	public function toString($str,$category){
		include_once 'db_conn.php';
		$sql_str="select * from codes where code='{$str}' and category='{$category}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		$value=strtoupper($row['descr']);
		return $value;
		mysql_freeresult();
	}

	public function sanitize_hyp($str){ //convert hyphen values to underscore values
		$ireplace_value="_";
		$search_value="-";
		$value=str_replace($search_value,$ireplace_value,$str);
		return $value;
	}//convert hyphen values to underscore values

	public function get_current_month(){
		include_once 'db_conn.php';
		$sql_str="select month(now()) as month_now";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		$row=mysql_fetch_array($sql_qry);
		return $row['month_now'];
	}

	public function bool_billing_done($account_no){
		include_once 'db_conn.php';
		include_once 'cls_codes.php';
		$ledger_table=cls_misc::sanitize_hyp($account_no);
		$sql_str="select * from ldg_{$ledger_table} order by reading_date desc";
		$sql_qry=mysql_query($sql_str) or die(mysql_error("error on line 33"));
		$row=mysql_fetch_array($sql_qry);
		if (cls_misc::get_current_month()==$row['reading_date']) {
			$done='true';
		 }else {
			$done='false';
		 }
		 return $done;
	}
	
	
	public function CheckDuplication($TableName,$ColumnName1,$ColumnName2=null,$Needle1,$Needle2=null){
		include_once 'db_conn.php';
		$sql_str="select * from {$TableName} where {$ColumnName1}='{$Needle1}' and {$ColumnName2}='{$Needle2}'";
		$sql_qry=mysql_query($sql_str) or die(mysql_error());
		if(mysql_num_rows($sql_qry)>0){ //data found
			$data="1";}else{$data='0';}
		return $data;//1-duplication found,0-not found
	}
	
	//[start] stage for commit to remote PS(coding status: uploaded)
	public function CheckDuplication_OpenSQL($SQL){
		include_once 'db_conn.php';
		$sql="{$SQL}";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error($e->getFile()."-".$e->getLine()));
		if(mysql_numrows($qry)==0){ //duplication not found
			$data='0';
		}else{$data='1';};
		return $data;
	}
	//[end] stage for commit to remote PS
	
	public function gFormatNumber($number){
		if($number==0.00){
			$data='0.00';
		}else{
			$data=number_format($number,2,".",",");    
		}
		
		return $data;
	}
	
	public function ConvertToTableName($account_no){
		$prefix="ldg_";
		$data=$prefix.cls_misc::sanitize_hyp($account_no);
		return $data;
	}
	
	//[start] stage for commit to remote PS(status: uploaded)
	public function getOptBarangayNames(){
		include_once 'db_conn.php';
		$sql="select * from codes where category='Barangay' order by descr asc";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error($e->getFile()."-".$e->getLine()));
		$data=array();
		while($row=mysql_fetch_array($qry)){
			$data[]=array('codes_value'=>$row['code'],'descr_value'=>$row['descr']);
		}
		//print_r($data);
		return $data;
	}
	//[start] stage for commit to remote PS

	//[start] stage for commit to remote PS(status: uploaded)

	public function getOptCodes_OpenSQL($Category){ //return all values from table COdes filtered by category
		include_once 'db_conn.php';
		$sql="select distinct(descr),code from codes where category='{$Category}' order by id asc";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error($e->getFile()."-".$e->getLine()));
		$data=array();
		while($row=mysql_fetch_array($qry)){
			$data[]=array('code_value'=>$row['code'],'descr_value'=>$row['descr']);
		}
		return $data;
	}

	public function getOptMonths(){ //return all values from table COdes filtered by category
		include_once 'db_conn.php';
		$sql="select * from codes where category='month' order by id,code asc limit 0,12";
		$e=new Exception();
		$qry=mysql_query($sql) or die(mysql_error($e->getFile()."-".$e->getLine()));
		$data=array();
		while($row=mysql_fetch_array($qry)){
			$data[]=array('code_value'=>$row['code'],'descr_value'=>$row['descr']);
		}
		return $data;
	}
	
	public function RemoveZeros($Value){
		$data=cls_misc::gFormatNumber($Value);
		if($data=='0.00'){
			$out='<center>-<center>';
		}else{
			$out=$data;
		}
		return $out;
	}
	
    public function RemoveZerosForExcel($Value){
        //$data=cls_misc::gFormatNumber($Value);
        $data=$Value;
        if($data=='0.00'){
            $out='-';
        }else{
            $out=$data;
        }
        return $out;
    }
	public function getCodesDescrValuesToArray($OpenSQL){
		include_once 'db_conn.php';
		$e=new Exception();
		$qry=mysql_query($OpenSQL) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
		while($row=mysql_fetch_array($qry)){
			$data[]=array('codes'=>$row['code'],'description'=>$row['descr']);
		}
		return $data;
	}
	
	public function RemoveHtmlFormat($str){
		$data=$str;
		$html_tags=array('<strong>','</strong>','<center>','</center>','<br>','&nbsp;');
		for($i=0;$i <= count($html_tags);$i++){
			$data=str_replace($html_tags[$i],"",$data);
		}
		return $data;
	}

    public function strPassToMeta(){
        $asterisk="********";
        return $asterisk;    
    }	
	
    public function OptArrayValues($Category){ //return values from codes table using indexed arrays
        include_once 'db_conn.php';
        $sql="select * from codes where category='{$Category}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            $data_out[]=$row['code'];
        }
        return $data_out;
    }
    
    /**
    * Method SQL Date Add procedure
    * @param $StartDate str format yyyy-mm-dd
    * @param $Interval (int) integer values
    * @param $IntervalType str 'month','day','year'
    * @return date str format results the same as format entry
    */
    public function DateAdd($StartDate,$Interval, $IntervalType){
        include_once 'db_conn.php';
        if(date('t',strtotime($StartDate))==31){$Interval+=1;}
        $sql="select date_add('{$StartDate}', interval {$Interval} {$IntervalType}) as date_result";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        $row=mysql_fetch_array($qry);
        return $row['date_result'];
    }
    
	//---------------miscellaneous codes-for sample only----------------
	public function sample_data_forAC(){
		include_once 'db_conn.php';
		$sql="select * from profile order by applicant desc";//"where address_brgy='brgy_1'";
		$qry=mysql_query($sql) or die(mysql_error());
		while($row=mysql_fetch_array($qry)){
			$data="\"{$row['applicant']}". "|{$row['acct_no']}\"," . $data;
		}
		return $data;
	}
	
}//end class
?>