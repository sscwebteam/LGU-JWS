    <script type="text/javascript" src="jui/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="jquery_migrate.js"></script>
    <script type="text/javascript" src="jquery-2.0.3.min.js"></script>
    
    <link rel="stylesheet" href="style.css" type="text/css" media="screen" />    <!-- this is my custom css -->
    <link rel="stylesheet" href="my_css.css" type="text/css" media="screen" />    <!-- this is my custom css -->
    <link rel="stylesheet" type="text/css" href="css/reset.css">                <!-- this is my css for login-->
    <link rel="stylesheet" type="text/css" href="css/structure.css">            <!-- this is my css for login-->
    <!--   <link rel="stylesheet" type="text/css" href="css/loading.css"> -->
    <!--[if IE 6]><link rel="stylesheet" href="style.ie6.css" type="text/css" media="screen" /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" href="style.ie7.css" type="text/css" media="screen" /><![endif]-->
    <!--<script type="text/javascript" src="script/script.js"></script>-->
    <script type="text/javascript" src="script/login.js"></script>
    <script type="text/javascript" src="script/ledger.js"></script>
    <script type="text/javascript" src="script/billing.js"></script>
    
    
    <script type="text/javascript" src="noname2.js"></script>
    <script type="text/javascript" src="script/ajax-action-billing.js"></script>
    <script type="text/javascript" src="script/Emp.js"></script>
    <link rel="stylesheet" href="jui/themes/base/jquery.ui.all.css">
    
    <?php //todo -o mike -p 10 -c For Upload: [start]Include Jquery UI files  ?>
    <script src="jui/ui/jquery.ui.core.js"></script>
    <script src="jui/ui/jquery.ui.widget.js"></script>
    <script src="jui/ui/jquery.ui.position.js"></script>
    <script src="jui/ui/jquery.ui.menu.js"></script>
    <script src="jui/ui/jquery.ui.autocomplete.js"></script>
    <script src="jui/ui/jquery.ui.datepicker.js"></script>
    <script src="jui/ui/jquery.ui.button.js"></script>
    <link rel="stylesheet" href="jui/demos/demos.css">
    <script type="text/javascript" src="script/zebra_dialog.js"></script>
    <link rel="stylesheet" href="css/zebra_dialog.css">

    <?php include_once 'cls_codes.php' ?>
    <script>
    $('body').ready(function(){
     
        var availableTags = [<?php echo cls_misc::sample_data_forAC()?>];
        $( "#accnt_names" ).autocomplete({
            minlength:5,
            source: availableTags
            //select:function(event,ui){if(event.keyCode==13) { getBrgyCode($.trim($('#names').val()))}}
        });
        
        $( "#names" ).autocomplete({
            minlength:5,
            source: availableTags,
            select:function(event,ui){if(event.keyCode==13) { getBrgyCode($.trim($('#names').val()))}}
        });

        
        $("#date").datepicker({
        //FFU #18
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd'
        });
        $("button").button({text:'true'});
        
    });
    
    function test_date_picker(){
        $.ajax({
        }).done(function(result){
            $("#date").datepicker({
            //FFU #18
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("button").button({text:'true'});
        });
        
    }//document ready ends    
    </script>
    <?php //todo -o mike -p 10 -c For Upload: [end]Include Jquery UI files  ?>      
    <script type="text/javascript">
        function submitform(){         
            //all forms have a generic name as form1
            document.form1.submit();
        }
    </script>