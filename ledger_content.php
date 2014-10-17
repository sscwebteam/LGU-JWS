<?php
    include_once 'header.php';
    include_once 'scripts.php';
    include_once 'juban_functions.php';
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
       <?php include_once 'ledger.php';    
       ?>            
 
    
                                 <?php 
                                    if (isset($_POST['mga_post'])){
                                        switch ($_POST['mga_post']){
                                            case 'Add Account':
                                            //validates the input to server
                                                if (auxilliary::validation($_POST['account_no'])){
                                                    echo '<p class="error">Check your account number!</p>';
                                                    include_once 'footer.php';die; 
                                                }elseif(auxilliary::validation($_POST['f_name']) && auxilliary::validation($_POST['s_name'])){
                                                    echo '<p class="error">Check your name entries!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif (auxilliary::validation($_POST['inspector'])){
                                                    echo '<p class="error">Check your inspector!</p>';
                                                    include_once 'footer.php';die;
                                                }
                                                /*elseif (auxilliary::validation($_POST['or_no'])){
                                                    echo '<p class="error">Check your OR number!</p>';
                                                    include_once 'footer.php';die;                                            
                                                }*/
                                                elseif(auxilliary::validation($_POST['jws_chairman'])){
                                                    echo '<p class="error">Check your JWS Chairman!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['meter_no'])){
                                                    echo '<p class="error">Check your meter number!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['meter_brand'])){
                                                    echo '<p class="error">Check your meter brand!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['size'])){
                                                    echo '<p class="error">Check your size entry!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['plumber'])){
                                                    echo '<p class="error">Check your plumber!</p>';
                                                    include_once 'footer.php';die;    
                                                }elseif (auxilliary::numeric($_POST['initial_reading'])){
                                                    echo '<p class="error">Check your initial reading!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::unique("profile","acct_no ='".$_POST['account_no']."'")){
                                                    echo '<p class="error">Duplicate account number is encountered!</p>';
                                                    include_once 'footer.php';die;
                                                }else{    //ends the validation starts to insert into databse
                                                /*///////////////////////*/
                                                sql_retrieve::insert_entry("profile","acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","applicant",$_POST['s_name'].", ".$_POST['f_name']." ".$_POST['m_name'],"acct_no",$_POST['account_no']);                                    
                                                    /*get address code*/ 
                                                //$address = mysql_fetch_array(sql_retrieve::request_rows("code","codes","descr='".$_POST['address']."'"));
                                                sql_retrieve::update_entry("profile","address_brgy",$_POST['address'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","date_applied",$date = auxilliary::date_used($_POST['year_inspect']."-".$_POST['month_inspect']."-".$_POST['day_inspect']),"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","type_connection",$_POST['type_connect'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","type_inspct",$_POST['inspect_report'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","inspector",$_POST['inspector'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","plumber",$_POST['plumber'],"acct_no",$_POST['account_no']);
                                                //sql_retrieve::update_entry("profile","or_date",$date = auxilliary::date_used($_POST['year_or']."-".$_POST['month_or']."-".$_POST['day_or']),"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","mode_payment",$_POST['mode_payment'],"acct_no",$_POST['account_no']);
                                                //sql_retrieve::update_entry("profile","or_no",$_POST['or_no'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","approval",$_POST['jws_chairman'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","meterno",$_POST['meter_no'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","brand",$_POST['meter_brand'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","size",$_POST['size'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","initial_reading",$_POST['initial_reading'],"acct_no",$_POST['account_no']);
                                                sql_retrieve::update_entry("profile","date_installed",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']),"acct_no",$_POST['account_no']);
                                                    //create a new ledger
                                                $acct_no_ledger  = str_replace("-","_",$_POST['account_no']);    
                                                sql_retrieve::create_table($acct_no_ledger);
                                                //inserts into new ledger
                                                sql_retrieve::insert_entry("ldg_".$acct_no_ledger,"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));    
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"meter_reading",$_POST['initial_reading'],"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"cu_used","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"pen_fee","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"bill_amnt","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"loans_MLP","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"loans_MF","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"total","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"OR_num",$_POST['or_no'],"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                //sql_retrieve::update_entry("ldg_".$acct_no_ledger,"OR_date",$date = auxilliary::date_used($_POST['year_or']."-".$_POST['month_or']."-".$_POST['day_or']),"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                sql_retrieve::update_entry("ldg_".$acct_no_ledger,"remarks","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                    //inserts to or_log table
                                                sql_retrieve::insert_entry("or_log","issued_to_accnt",$_POST['account_no']);
                                                sql_retrieve::update_entry("or_log","or_number",$_POST['or_no'],"issued_to_accnt",$_POST['account_no']);
                                                sql_retrieve::update_entry("or_log","or_date",$date = auxilliary::date_used($_POST['year_or']."-".$_POST['month_or']."-".$_POST['day_or']),"issued_to_accnt",$_POST['account_no']);
                                                sql_retrieve::update_entry("or_log","issued_amnt","wara aq idea","issued_to_accnt",$_POST['account_no']);
                                                sql_retrieve::update_entry("or_log","remarks","0","issued_to_accnt",$_POST['account_no']);                                                
                                                }//shows the ledger
                                                    ledger_type::general($_POST['account_no']);
                                                    break;    
                                                
                                                
                                            case 'Update DATE':
                                            //update the ledger reference is the reading date
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"meter_reading",$_POST['meter_reading'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"cu_used",$_POST['cu_used'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"bill_amnt",$_POST['bill_amnt'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"pen_fee",$_POST['pen_fee'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"loans_MLP",$_POST['loans_MLP'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"loans_MF",$_POST['loans_MF'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"total",$_POST['total'],"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"OR_num",trim($_POST['OR_num']),"reading_date",$_POST['reading_date']);
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"OR_date",trim($_POST['OR_date']),"reading_date",$_POST['reading_date']);                                
                                                sql_retrieve::update_entry("ldg_".$_POST['acct_no_ledger'],"remarks",$_POST['remarks'],"reading_date",$_POST['reading_date']);                        
                                                //update the or_log reference is the  or_date                                                                                                
                                                sql_retrieve::update_entry("or_log","or_number",trim($_POST['OR_num']),"issued_to_accnt",str_replace("_","-",$_POST['acct_no_ledger'])."' and or_date='".$_POST['OR_date']);            
                                                sql_retrieve::update_entry("or_log","issued_amnt",$_POST['total'],"issued_to_accnt",str_replace("_","-",$_POST['acct_no_ledger'])."' and or_date='".$_POST['OR_date']);
                                                sql_retrieve::update_entry("or_log","remarks",$_POST['remarks'],"issued_to_accnt",str_replace("_","-",$_POST['acct_no_ledger'])."' and or_date='".$_POST['OR_date']);                                                            
                                                
                                                //sql_update above wont suffice, update will be simultaneous in effect
                                                //solution: detailed specific values instead
                                                $sql1="update or_log set encodedBy='{$_SESSION['username']}' where or_number='{$_POST['OR_num']}' and issued_to_accnt='{$_POST['acct']}' and or_date='{$_POST['OR_date']}' and issued_amnt='{$_POST['total']}'";
                                                $e=new Exception();
                                                mysql_query($sql1) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                                                
                                                //shows the ledger
                                                ledger_type::general(str_replace("_","-",$_POST['acct_no_ledger']));
                                                break;
                                            case 'Save Profile':
                                            //updates the profile of client
                                            //validates the input to server
                                            $acct_no_ledger  = str_replace("-","_",$_POST['account_no']);
                                                if (auxilliary::validation($_POST['account_no'])){
                                                    echo '<p class="error">Check your account number!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['name'])){
                                                    echo '<p class="error">Check your name entries!;</p>';
                                                    include_once 'footer.php';die;
                                                }elseif (auxilliary::validation($_POST['inspector'])){
                                                    echo '<p class="error">Check your inspector!</p>'; 
                                                    include_once 'footer.php';die;
                                                }/*elseif (auxilliary::validation($_POST['or_no'])){
                                                    echo '<p class="error">Check your OR number!</p>';    
                                                    include_once 'footer.php';die;                                        
                                                }*/elseif(auxilliary::validation($_POST['jws_chairman'])){
                                                    echo '<p class="error">Check your JWS Chairman!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['meter_no'])){
                                                    echo '<p class="error">Check your meter number!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['meter_brand'])){
                                                    echo '<p class="error">Check your meter brand!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['size'])){
                                                    echo '<p class="error">Check your size entry!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif(auxilliary::validation($_POST['plumber'])){
                                                    echo '<p class="error">Check your plumber!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif (auxilliary::numeric($_POST['initial_reading'])){
                                                    echo '<p class="error">Check your initial reading!</p>';
                                                    include_once 'footer.php';die;
                                                }elseif ($_POST['account_no']<>$_POST['old_acct_no']){                                        
                                                    if(auxilliary::unique("profile","acct_no ='".$_POST['account_no']."'")){
                                                        echo '<p class="error">Duplicate account number is encountered!</p>';
                                                        include_once 'footer.php';die;
                                                    }else{ //updates the ledger and or_log in case has created new account number
                                                        sql_retrieve::update_entry("profile","acct_no",$_POST['account_no'],"acct_no",$_POST['old_acct_no']);
                                                        sql_retrieve::change_name("ldg_".str_replace("-","_",$_POST['old_acct_no']),"ldg_".str_replace("-","_",$_POST['account_no']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']),"reading_date",$_POST['old_date_installed']);    
                                                        sql_retrieve::update_entry("or_log","issued_to_accnt",$_POST['account_no'],"issued_to_accnt",$_POST['old_acct_no']);
                                                    }                                                                    
                                                }else{    ///updates the ledger in case change does not affect account number
                                                    sql_retrieve::update_entry("profile","acct_no",$_POST['account_no'],"acct_no",$_POST['old_acct_no']);
                                                    sql_retrieve::update_entry("ldg_".$acct_no_ledger,"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']),"reading_date",$_POST['old_date_installed']);    
                                                    sql_retrieve::update_entry("or_log","issued_to_accnt",$_POST['account_no'],"issued_to_accnt",$_POST['old_acct_no']);
                                                }
                                                        sql_retrieve::update_entry("profile","applicant",$_POST['name'],"acct_no",$_POST['account_no']);                                    
                                                            /*get address code*/ 
                                                        //$address = mysql_fetch_array(sql_retrieve::request_rows("code","codes","descr='".$_POST['address']."'"));
                                                        sql_retrieve::update_entry("profile","address_brgy",$_POST['address'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","date_applied",$date = auxilliary::date_used($_POST['year_inspect']."-".$_POST['month_inspect']."-".$_POST['day_inspect']),"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","type_connection",$_POST['type_connect'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","type_inspct",$_POST['inspect_report'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","inspector",$_POST['inspector'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","plumber",$_POST['plumber'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","or_date",$_POST['or_date'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","mode_payment",$_POST['mode_payment'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","or_no",$_POST['or_no'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","approval",$_POST['jws_chairman'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","meterno",$_POST['meter_no'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","brand",$_POST['meter_brand'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","size",$_POST['size'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","initial_reading",$_POST['initial_reading'],"acct_no",$_POST['account_no']);
                                                        sql_retrieve::update_entry("profile","date_installed",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']),"acct_no",$_POST['account_no']);
                                                            //checks if there is existing ledger, create a new ledger
                                                            
                                                        //inserts into new ledger
                                                                            
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"meter_reading",$_POST['initial_reading'],"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"cu_used","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"pen_fee","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"bill_amnt","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"loans_MLP","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"loans_MF","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"total","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"OR_num",$_POST['or_no'],"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"OR_date",$_POST['or_date'],"reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                        sql_retrieve::update_entry("ldg_".$acct_no_ledger,"remarks","0","reading_date",$date = auxilliary::date_used($_POST['year_installed']."-".$_POST['month_installed']."-".$_POST['day_installed']));
                                                            //inserts to or_log
                                                        sql_retrieve::update_entry("or_log","or_number",$_POST['or_no'],"issued_to_accnt",$_POST['account_no']."' and or_date='".$_POST['or_date']);                                                
                                                        sql_retrieve::update_entry("or_log","issued_amnt","wara aq idea","issued_to_accnt",$_POST['account_no']."' and or_date='".$_POST['or_date']);
                                                        sql_retrieve::update_entry("or_log","remarks","0","issued_to_accnt",$_POST['account_no']."' and or_date='".$_POST['or_date']);                
                                                            //shows the ledger                                                                                                                            
                                                            ledger_type::general($_POST['account_no']);
                                                    break;    
                                        
                                                case 'DELETE LEDGER':
                                                //deletes the ledger and profile
                                                    $result = mysql_num_rows(sql_retrieve::request_rows("*", "profile", "acct_no='".$_POST['accountno']."'"));
                                                    if ($result==1){
                                                        sql_retrieve::delete_entry(" ", "profile", "acct_no","'".$_POST['accountno']."'");
                                                        sql_retrieve::delete_entry(" ", "or_log", "issued_to_accnt","'".$_POST['accountno']."'");
                                                        sql_retrieve::drop_table("ldg_".str_replace("-","_",$_POST['accountno']));
                                                        echo "<p class='error'>DELETION SUCCESSFUL!</p>";}
                                                    else{
                                                        echo '<p class="error">Cannot delete non-existing account number: '.$_POST['accountno']."</p>";
                                                        include_once 'footer.php';die;
                                                    }                        
                                                    break;
                                                case 'Delete DATE':
                                                //deletes part of ledger with reference to reading date
                                                    sql_retrieve::delete_entry(" ", "ldg_".$_POST['acct_no_ledger'], "reading_date","'". $_POST['reading_date']."'"." limit 1");
                                                    sql_retrieve::delete_entry(" ","or_log","issued_to_accnt","'".str_replace("_","-",$_POST['acct_no_ledger'])."' and or_date='".$_POST['OR_date']."'");
                                                    ledger_type::general(str_replace("_","-",$_POST['acct_no_ledger']));
                                                    break;    
                                                case 'Enter to Ledger':
                                                    include_once 'db_conn.php';
                                                    echo "test:account number passed={$_POST['acct']}<br>";
                                                    if(auxilliary::validate_date($_POST['OR_date']) && auxilliary::validate_date($_POST['reading_date'])){                                                    
                                                        //inserts into new ledger
                                                        sql_retrieve::insert_entry("ldg_".$_POST['acct_replace'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));    
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"meter_reading",$_POST['meter_reading'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"cu_used",$_POST['cu_used'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"pen_fee",$_POST['pen_fee'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"bill_amnt",$_POST['bill_amnt'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"loans_MLP",$_POST['loans_MLP'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"loans_MF",$_POST['loans_MF'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"total",$_POST['total'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"OR_num",$_POST['OR_num'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"OR_date",$date = auxilliary::date_used($_POST['OR_date']),"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        sql_retrieve::update_entry("ldg_".$_POST['acct_replace'],"remarks",$_POST['remarks'],"reading_date",$date = auxilliary::date_used($_POST['reading_date']));
                                                        
                                                        //inserts to or_log in case of payments
                                                        sql_retrieve::insert_entry("or_log","issued_to_accnt,or_date",str_replace("_","-",$_POST['acct_replace'])."','".$date = auxilliary::date_used($_POST['OR_date']));
                                                        sql_retrieve::update_entry("or_log","or_number",$_POST['OR_num'],"issued_to_accnt",str_replace("_","-",$_POST['acct_replace'])."' and or_date='".$date = auxilliary::date_used($_POST['OR_date']));                                                
                                                        sql_retrieve::update_entry("or_log","issued_amnt",$_POST['total'],"issued_to_accnt",str_replace("_","-",$_POST['acct_replace'])."' and or_date='".$date = auxilliary::date_used($_POST['OR_date']));
                                                        sql_retrieve::update_entry("or_log","remarks",$_POST['remarks'],"issued_to_accnt",str_replace("_","-",$_POST['acct_replace'])."' and or_date='".$date = auxilliary::date_used($_POST['OR_date']));
                                                        //sql_update above wont suffice, update will be simultaneous in effect
                                                        //solution: detailed specific values instead
                                                        $sql1="update or_log set encodedBy='{$_SESSION['username']}' where or_number='{$_POST['OR_num']}' and issued_to_accnt='{$_POST['acct']}' and or_date='{$_POST['OR_date']}' and issued_amnt='{$_POST['total']}'";
                                                        $e=new Exception();
                                                        mysql_query($sql1) or die(mysql_error()."__File: ".$e->getFile()."__Line: ".$e->getLine());
                                                        
                                                        //shows the updated ledger
                                                        ledger_type::general(str_replace("_","-",$_POST['acct_replace']));
                                                    }else{
                                                        echo '<p class="error">Invalid OR Date: '.$_POST['OR_date']." or Reading Date: ".$_POST['reading_date']."</p>";
                                                        }
                                                    break;        
                                                case 'SHOW LEDGER':
                                                //shows the ledger for update
                                                    if(strpos($_POST['accountno'],'|') > -1 ){ //user select instead from the dropdown
                                                        $arrAccnt=explode('|',$_POST['accountno']); //get the account number from the array
                                                        $AccountNO_C=str_replace('|','',$arrAccnt[1]);   
                                                    }else{
                                                        $AccountNO_C=$_POST['accountno'];
                                                    }
                                                    
                                                    $result = mysql_num_rows(sql_retrieve::request_rows("*", "profile", "acct_no='".$AccountNO_C."'"));
                                                    if ($result==1){
                                                        ledger_type::general($AccountNO_C);}else{
                                                        echo '<p class="error">No account is related to account number: '.$AccountNO_C."</p><br><br><br>";
                                                        include_once 'footer.php';die;
                                                        }
                                                    break;
                                                
                                                case 'SEARCH NAME':
                                                            $name = explode("|",$_POST['pangaran']);
                                                            $row = sql_retrieve::request_rows("*","profile"," applicant like '%".$name[0]."%'");
                                                            ledger_type::display_names($row);
                                                    break;
                                                        
                                                default:
                                                    include_once 'ledger.php';
                                                    $result = mysql_num_rows(sql_retrieve::request_rows("*", "profile", "acct_no='".$_POST['accountno']."'"));
                                                    if ($result==1){              
                                                        ledger_type::general($_POST['accountno']);}else{
                                                        echo '<p class="error">No account is related to account number: '.$_POST['accountno']."</p>";
                                                        include_once 'footer.php';die;
                                                        }
                                                    break;    
                                        }// ends switch
                            
                                     }elseif (isset($_REQUEST['action'])){
                                        switch ($_REQUEST['action']){
                                            
                                            case 'ledger_scan':
                                                    ledger_type::general($_REQUEST['account_no']);
                                                break;

                                            case 'search':
                                                #this will search information from users
                                                ledger_type::search_person();
                                                break;
                                            case 'new':                             
                                                ledger_type::new_account();    //enters credential and creates ledger                                
                                                 break;
                                            case 'update':                                    
                                                ledger_type::update_ledger($_REQUEST['acct_replace'],$_REQUEST['reading_date']);    
                                                //updates the ledger with reference to reading date
                                                break;
                                            case 'edit':
                                                ledger_type::edit_profile($_REQUEST['acct_no']);//edits the profile(credentials)
                                                break;    
                                            case 'delete': //shows for for deletetion
                                                    ?>
                                                        <h2 class="art-postheader">
                                                            <center>Please enter your account number </center></h2>
                                                              <center style="padding-top: 20px;"> 
                                                                <form method="POST" name="form1" action="ledger_content.php" autocomplete="off">
                                                                    <input type="text" name="accountno"></input>                                         
                                                                    <input type="hidden" name="mga_post" value ="DELETE LEDGER"></input>
                                                                    <input type="button" class="art-button" value="DELETE LEDGER" onclick="if(confirm('Are you sure you want to delete this ledger account?')) submitform();"></input>
                                                                </form>    
                                                                 <table id="butangan" class="datagrid"></table>                                                 
                                                            </center>  
                                                    <?php                
                                                break;        
                                            case 'min_row':// this has change june 12, 2012 specifically the case min_row
                                            //shows the ledger for update
                                                    $result = mysql_num_rows(sql_retrieve::request_rows("*", "profile", "acct_no='".$_REQUEST['accountno']."'"));
                                                    if ($result==1){
                                                        ledger_type::general($_REQUEST['accountno']);}else{
                                                        echo '<p class="error">No account is related to account number: '.$_REQUEST['accountno']."</p>";
                                                        include_once 'footer.php';die;
                                                        }
                                            default:            
                                                break;    
                                        }//ends switch
                                     }//ends elseif
                                     else{?>
                                        <h2 class="art-postheader">
                                            <center>Please enter your account number </center>
                                            <center style="padding-top: 20px;"> 
                                                <form method="POST" name="form1" action="ledger_content.php" autocomplete="off">
                                                    <input id="accnt_names" name="accountno">
                                                    <input type="hidden" name="mga_post" value="SHOW LEDGER"></input>
                                                    <span class="art-button-wrapper"><span class="art-button-l">
                </span><span class="art-button-r"></span><input class="art-button" type="button" value="SHOW LEDGER" onclick="submitform();"></span>                        
                                                </form>
                                                <table id="butangan" class="datagrid"></table>
                                            </center> 
                                        </h2>

                               <?php }?>
                
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
    include_once 'footer.php';   ?>                 