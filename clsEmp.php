<?php
error_reporting(E_ALL ^ E_NOTICE);
  class clsGlobalVariables{
        var $tblEmp='lst_emp';
        var $PenaltyDays='P22D'; //days elapsed for penalty computation
  }

  class clsEmp_Form{
    
/**    
* Method: Search for employees name for possible data entry to the database
* @param optAction='1' (search for names for add purposes) optAction='2'(search for unpaidbills)
* @return data block form
*/
    public function SearchName($optAction=null){ //PRocessID 1, 2
        ?>
        <span><center><form id="search">
        Search Name:&nbsp;<input id="accnt_names" class="accnt_names" name="accnt_names" style="width: 300px;"><br><br>
        <?php if($optAction=='1'){  ?>
        <button class="button" onClick='Emp_EventScript.AddEntry()'>Add To Listing</button>
        <?php } elseif($optAction=='2') { ?>
        <button class="button" onClick="Emp_EventScript.ViewUnpaidBills()">Unpaid Bills</button>
        <?php } elseif($optAction=='4'){ ?>
        <button class="button" onClick="Emp_EventScript.DelEmpl()">Delete Employee</button>
        <?php  }?>
        </form></center></span><br><br>
        <span id='emp_list'></span>
        <?php
    }
    
    public function ListEmpList(){
        
    }
        
    public function LeftPanel(){
        ?>
        <table width="100%" id="optBut">
            <tr><td><button style="width: 100%; text-align: left;" onclick="Emp_EventScript.ButAdd_onClick('1')">Add Employee Listings</button></td></tr>
            <tr><td><button style="width: 100%; text-align: left;" onclick="Emp_EventScript.ButAdd_onClick('2')">View Unpaid Bills</button></td></tr>
            <tr><td><button style="width: 100%; text-align: left;" onclick="Emp_EventScript.ButAdd_onClick('3')">List All Employees</button></td></tr>
            <tr><td><button style="width: 100%; text-align: left;" onclick="Emp_EventScript.ButAdd_onClick('4')">Delete Employee</button></td></tr>
        </table>
        <?php
    }
    
  }
  
  class clsEmp_Get{
  
/**  
* Method: Get the List of Unpaid Bills based on AccountNo
* @param str Account Number
* @return data block form 
*/
    public function UnpaidBills($AccountNo){
        include_once 'db_conn.php';
        include_once 'cls_codes.php';
        include_once 'cls_user.php';
        
        $tbl_Ledger=cls_misc::ConvertToTableName(trim($AccountNo));
        $sql="select * from {$tbl_Ledger} where (OR_num='' or OR_num is null) and (OR_date='' or OR_date is null) order by id asc";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        $ProfileName=cls_user_get::ProfileValue('acct_no',$AccountNo,'applicant');
        
        $data.="<center><table width=80% cellspacing=1 cellpadding=1>";
        $data.= "<tr><td colspan=7>Account Name <strong>{$ProfileName}</strong></td></tr>";
        $data.="<tr><td colspan=7>Account Number <strong>{$AccountNo}</strong></td></tr>";
        $data.="<tr><td colspan=7>&nbsp;</td></tr>";
        $data.="<tr>
            <td>Reading Date</td>
            <td>Due Date</td>
            <td>Penalty</td>
            <td>Bill Amount</td>
            <td>Loans(MLP)</td>
            <td>Loans(MF)</td>
            <td>Tota Amount</td>
        </tr>";
        $data.="<tr><td>&nbsp;</td></tr>";
        while($row=mysql_fetch_array($qry)){ $i++;
            $data.="<tr id=trow{$i} onmouseover=Empl_Misc.TR_BGC({$i},'over') onmouseout=Empl_Misc.TR_BGC({$i},'out')>";
        
            $DueDate=cls_misc::DateAdd($row['reading_date'],22,'DAY');
            $data.="<td>{$row['reading_date']}</td>";
            $data.="<td>{$DueDate}</td>";
            $penalty=(date('Y-m-d') > $DueDate)?$penalty=$row['bill_amnt'] * 0.05:$penalty='0.00';
            $Print_Penalty=cls_misc::gFormatNumber($penalty);
            $data.="<td>{$Print_Penalty}</td>";
            $data.="<td>{$row['bill_amnt']}</td>";
            $data.="<td>{$row['loans_MLP']}</td>";
            $data.="<td>{$row['loans_MF']}</td>";
            $total=$row['bill_amnt'] + $penalty + $row['loans_MF'] + $row['loans_MLP'];
            $Print_Total=cls_misc::gFormatNumber($total);
            $data.="<td>Php&nbsp;{$Print_Total}</td>";
            $data.="</tr>";
        }    
        $data.="</table></center>";
        echo $data;
    }

  
/**  
* Method: Get all listings of Employees
* @return data block form
*/
    public function GetEmpList(){
        include_once 'db_conn.php';
        include_once 'cls_user.php';
        $gVars= new clsGlobalVariables();
        $SQL_GetList="select * from {$gVars->tblEmp} order by row_id desc";
        $e=new Exception();
        $QRY_GetList=mysql_query($SQL_GetList) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        
        //start iterating data
        $data="<center><table width=80%><tr><td>EMPLOYEE LISTINGS</td></tr>";
        $data.="<tr><td>&nbsp;</td></tr>";
        while($row=mysql_fetch_array($QRY_GetList)){
            $i++;
            //$EmpNames= cls_user_get::ProfileValue('acct_no',$row['accnt_no'],'applicant');
            /*
            $data.="<tr><td><strong><span id=EmpList-{$i} onClick=\"Emp_EventScript.aHref_onClick({$i},)\" ?>{$row['accnt_no']}</span> </strong></td></tr>";
            */
            $data.="<tr><td>
            <span title=\"{$row['accnt_no']}\" id=EmpList-{$i} onClick=Emp_EventScript.aHref_onClick({$i})>{$row['accnt_no']}</span></td></tr>";
        }
        $data.="</table></center>";
        echo $data;
    }
    
/**    
*  Method: Check the possible duplication of data entry based on SQL statment passed
* @return str/int data rows count based on parameters passed
*/
    public function CheckEntry($SQL){
        $e=new Exception();
        $qry=mysql_query($SQL) or die(mysql_error()."__File: ".$e->getFile()."__Line:".$e->getLine());
        //check if it contains data
        if(mysql_numrows($qry)){
            return true;
        }else{
            return false;
        }
    }
  }

  
  
  class clsEmp_Set extends clsEmp_Get{
    
/**  
* Method: Add account number to employees listing table
*/
    public function AddEmpl($AccountNumber=null){
        include_once 'db_conn.php';
        $tblEmpl="lst_emp";
        $SQL_CheckEntry="select * from {$tblEmpl} where accnt_no='{$AccountNumber}'";
        if(!parent::CheckEntry($SQL_CheckEntry)){ //if no duplication 
            //insert the data
            $SQL_InsertData="insert into {$tblEmpl}(accnt_no)values('{$AccountNumber}')";
            $e=new Exception();
            $QRY_InsertData=mysql_query($SQL_InsertData) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            return '1';
        }else{
            return '0';
        }
    }
    
    public function DelEmpl($AccountNoField){
        include_once 'db_conn.php';
        $gVars=new clsGlobalVariables();
        $sql="delete from {$gVars->tblEmp} where accnt_no='{$AccountNoField}'";
        $e=new Exception();
        $qry=mysql_query($sql) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
        return "ok";
    }
  }
  
  
?>
