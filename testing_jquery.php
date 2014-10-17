<?php
include_once 'juban_functions.php';

        
if(isset($_POST['action'])){
    switch($_POST['action']){
        case 'get_name':
            #get the names relevant to the keyword
            return_name_acctno($_POST['applicant']);
            break;
    }
}else{
    #prepare the variables
            $search = $_REQUEST['search_me'];
                #start the query for dbase
                $query = sql_retrieve::request_rows("*","profile"," acct_no like '%".$search."%'");
                $data = array();
                #prepare the result for output in json
               while($row = mysql_fetch_assoc($query)){
                   array_push($data,"\"".$row['acct_no']."\"");
               }
               $data = implode(",",$data);
               #return result
               echo '{"accountno":['.$data.']}';
}
?>
