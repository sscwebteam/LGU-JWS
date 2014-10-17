<?php
  //reserve for ajax authentications request and posting of data
error_reporting(E_ALL ^ E_NOTICE);
if($_SERVER['HTTP_X_REQUESTED_WITH']!='XMLHttpRequest'){echo "non-ajax request detected";die();}
  ?>

<?php
//------------------------------------------------------------------------------------------------------------------
    switch( strtolower($_POST['bill'])){
        
        case 'dl2_bill':
            include_once 'cls_bill.php';
            $Filter=trim($_POST['filter']);
            cls_bill_get::BillConsolidated('',$Filter);
            break;
        
        //todo -o mike -p 10 -c For Upload [start] Coding status (in progress)
        case 'addons':
            include_once 'db_conn.php';
            include_once 'cls_bill.php';
            $Prefix='RDates';
            print_r($_POST);
            $AccntNames=explode('|',$_POST['accnt_names']);
            $AccntNo=$AccntNames[1];
            $instal_amnt=$_POST['instal_amnt'];
            $AddonType=$_POST['addons'];
            $Terms=$_POST['terms']-1; //preparation for object index at 0
            for($i=0;$i<=$Terms;$i++){
                $RDates=$_POST[$Prefix.$i] ;
                cls_bill_get::VerifyAddonsEntry($AccntNo,$RDates,$AddonType);
                    $sql="insert into installment(acc_no,installment_value,billed_date,addons_type)values('{$AccntNo}','{$instal_amnt}','{$RDates}','{$AddonType}')";
                    $e=new Exception();
                    $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            }
            if(mysql_errno()==0){echo "ok";}else{echo "error";}
            break;
    }
    
    switch(strtolower($_POST['user'])){
        case 'logout':
            include_once 'db_conn.php';
            $values=$_POST;
            $sql="update tbl_usr_crd set status='0' where un='{$values['row_id']}'";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
             echo true;
            break;
    }