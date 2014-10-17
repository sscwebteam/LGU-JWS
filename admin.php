<?php
session_start();
	include_once 'header.php';
	include_once 'menu.php';
    include_once 'cls_user.php';
	#this will check unauthorized users
	if ($_SESSION['profileid'] ==""){
		echo "<center style='color:red;font-size:20pt;'>Unauthorized access of page is detected</br></br><a style='color:red;font-size:12pt;' href='main_file.php'>Go homepage</a><center>";	
		die;
	}
?>
<!--center content starts!-->
<div class="art-content-layout">
	<div class="art-content-layout-row">
		<div class="art-layout-cell art-content">
<div class="art-post">
	<div class="art-post-body">
<div class="art-post-inner art-article">
	<h2 class="art-postheader"><center>Administrative User Page</center></h2>
				<div class="cleared"></div>
<div class="art-postcontent">
<br><br>
<?php
	switch($_REQUEST['req']){
		case 'add':
			include_once 'forms.php';
			cls_admin_forms::add_edit('','add');
			break;
		
		case 'edit':
			include_once 'forms.php';
			$user_id=$_REQUEST['user_id'];
			cls_admin_forms::add_edit($user_id,'edit');
			break;
		
		case 'view':
			include_once 'forms.php';
			cls_admin_forms::view('admin.php?req=view');   
			break;
		
		case 'del':
			//include
			include_once 'forms.php';
			$user_id=$_REQUEST['user_id'];
			$sql_str1="delete from tbl_usr_crd where un='{$user_id}'";
			$sql_qry1=mysql_query($sql_str1) or die(mysql_error());
			?>
			<p style="text-decoration:blink;font-style: bold;font-size: small;">User ID and associated information Deleted!</p>
			<?php
			break;
		
		
	}
?>
				</div>
				<div class="cleared"></div>
				</div>

		<div class="cleared"></div>
	</div>
</div>

					  <div class="cleared"></div>
					</div>

<?php
	include_once 'sidebar.php';
	include_once 'footer.php';
?>