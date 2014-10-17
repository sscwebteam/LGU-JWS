<?php 
    include_once 'header.php';
    include_once 'scripts.php';
    include_once 'clsEmp.php';
?>
<br>
<div style="width: 100%; height: 2000px;">
    <div style="float: left; width: 30%;height: auto;">
    <?php clsEmp_Form::LeftPanel() ?>
    </div>
    <div  style="float: left; width: 70%; height: auto">
    <div id="data_status" style="display: none;">
        <img src="images/loader.gif" style=" position:relative;top:-200px"></div>
        <div id="data"></div> 
    </div>
</div>
<br><br><br>
<?php include_once 'footer.php' ?>