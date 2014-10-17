<?php
error_reporting(E_ALL ^ E_NOTICE);
session_start();

include 'juban_functions.php';
if(isset($_POST['submit'])){
	switch($_POST['submit']){
		case 'Login':
			$username = trim($_POST['username']);
			$password= trim($_POST['password']);
/*                $_SESSION['profileid'] = $data;
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
                $_SESSION['pangaran'] = $name;
*/                check_users($username,$password); 
            
			break;
		case 'Logout':	
            $sql="update tbl_usr_crd set status='0' where un='{$_SESSION['username']}' and pwd='{$_SESSION['password']}' and profile_id='{$_SESSION['profileid']}' and fullname='{$_SESSION['pangaran']}'";
            $e=new Exception();
            $qry=mysql_query($sql) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            //destroy session
            session_destroy();	
			$_SESSION=array();
            
			header("location:index.php");
			break;
	}	
}
elseif(isset($_SESSION['profileid'])){
	include_once 'header.php';
	include_once 'center_content.php';
	include_once 'sidebar.php';
	include_once 'footer.php';
}
else{
	include_once 'header.php';
	include_once 'center_content.php';
	include_once 'sidebar.php';
		include_once 'footer.php';
}
?>