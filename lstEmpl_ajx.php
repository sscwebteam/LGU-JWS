<?php
//add security layer here to access this file only through ajax request protocol only
error_reporting(E_ALL ^ E_NOTICE);
//file scope includes
include_once 'clsEmp.php';

  switch($_POST['set']){
    case 'add':
        include_once 'clsEmp.php';
        $AccntNo=trim($_POST['name']);
        echo clsEmp_Set::AddEmpl($AccntNo);
        break;
        
    case 'del':
         echo clsEmp_Set::DelEmpl(trim($_POST['accnt']));
        break;
  }
  
  switch($_POST['get']){
    case 'EmpList':
        include_once 'clsEmp.php';
        clsEmp_Get::GetEmpList();
        break;
    
    case 'UnpaidBills':
        clsEmp_Get::UnpaidBills(trim($_POST['accnt_no']));
        break;
        
    case 'AddForms':
        clsEmp_Form::SearchName($_POST['optAction']);
        break;
  }
  
  switch($_REQUEST['req']){
    case 'all_names':
        include_once 'db_conn.php';
        $crit=trim($_GET['term']);
        if(is_numeric($crit)){
            $where="accnt_no like '{$crit}%'";
        }else{
            $where="applicant like '%{$crit}%'";
        }
        $sql="select * from profile where {$where} order by applicant asc";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            $data[]=$row['applicant']."|".$row['acct_no'];
        }
        echo json_encode($data);
        break;
        
      case 'emp':
        include_once 'db_conn.php';
        $crit=trim($_GET['term']);
        $sql="select * from lst_emp where accnt_no like '%{$crit}%' order by row_id asc";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            $data[]=$row['accnt_no'];
        }
        echo json_encode($data);
        break;
        
      case 'ConsNames': //select names from employees table listings
        include_once 'db_conn.php';
        $crit=trim($_POST['term']);
        if(ctype_alpha($crit)){
            $sql="select * from profile where applicant like '%{$crit}%' order by applicant asc";    
        }elseif(ctype_digit($crit)){
            $sql="select * from profile where acct_no like '%{$crit}%' order by acct_no asc";
        }
        
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        while($row=mysql_fetch_array($qry)){
            $data[]= $row['applicant'].'|'.$row['acct_no'];
        }
        echo json_encode($data);
        break;

        
      case 'del':
        include_once 'clsEmp.php';
        
        break;

  }
  
?>
