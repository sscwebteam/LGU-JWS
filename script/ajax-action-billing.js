	
	$(document).ready(function(){
		//initialize jquery state
	})
	
	$(Document).ajaxStart(function(){
		//$( "#ajx_status").hide;
		//$("#ajx_status").show('slow',function(){
			//$( "#ajx_status").html("Searching Database Please wait...");
			//$( "#msg" ).html("");
			//$( "#search" ).hide('slow');
		//})
		
	})
	
	$(Document).ajaxComplete(function(){
		
	})
	
	$(Document).ajaxSuccess(function(){
		//$("ajx_status").html("reques complete..");
	})
	$(document).ajaxStop(function(){
		//$("#ajx_status").html("Request Complete");
		//$( "#ajx_status").hide('slow',function(){
			//alert("load complete");
			//$("#search").show('slow');
		//});
	})        
	
	//--------------------------------------CUSTOM FUNCTIONS STARTS HERE-------------------
	function getdetails(){
	var name = $('#name').val();
	var rno = $('#rno').val();
	$.ajax({
	type: "POST",
	url: "details.php",
	data: {fname:name, id:rno}
	}).done(function( result ) {
	$("#msg").html( result );
	});
	//$("#search").hide();
	}

	function ProcessFormValues(FormName,FormID){
		var prev_read=$.trim($("#prev_reading_value"+FormID).val());
		var curr_read=parseInt($("#curr_read_value"+FormID).val());
		var cu_used;
		cu_used=curr_read - prev_read;
		$("#cubic_used"+FormID).val(cu_used);
		var form_values=$('#' + FormName).serialize()
		//alert('serialize value=' + form_values);
		
		$.ajax({
			type: "POST",
			url: "ajax-actions.php",
			data: form_values
			}).done(function( result ) {
				var FormSpan;
				var Button_DL;
				var Button_Create;
				var ResultOut=$.trim(result).split('|',$.trim(result.length))
				Button_Create=$("#button-create-bill"+FormID);
				FormSpan=$("#Form-"+FormID);
				Button_DL=$("#button-dl"+FormID);
				//FormSpan.text(result);
				if(ResultOut[0]=='ok'){
					FormSpan.text('Status: Billing is done');
					$('#bill_amount'+FormID).attr('readonly','');
					$('#bill_amount'+FormID).val(ResultOut[1]);    
					Button_DL.attr('style','display:block');
					//alert(ResultOut[2]);
					Button_DL.attr('target','_blank');
					Button_DL.attr('href',ResultOut[2]);
					Button_Create.attr('style',"display: none;")
					//$("#button-create-bill"+FormID).attr("style","display:none");
				}
				//FormSpan.text(result);
				//$("#"+TextInputID).attr('disabled',true);
				});
		}
	
	function CheckAcceptedLength(TextInputNameID){
		var TextInputName=$("#"+TextInputNameID).val();
		var idSaveButton=$("#ajx-B-OR-Save");
		idSaveButton.attr('style','display:none');
		if( $.isNumeric(TextInputName) && (TextInputName.length) > 6){
			getDuplicate();
		}else{
			//alert("length not accepted");
		}
	}
	
    function cCheckAcceptedLength1(TargetTextInput,TargetExecButton){
        var SourceAccnt=$('#accnt_names').val()
        var AccountNo=SourceAccnt.split('|');
        var TextInputName=$('#'+TargetTextInput).val();
        var idSaveButton=$("#"+TargetExecButton);
        idSaveButton.attr('style','display:none');
        
        if( $.isNumeric(TextInputName) && (TextInputName.length) > 6){
            cgetDuplicate1($.trim(AccountNo[1]),TextInputName,TargetExecButton);
        }else{
            //alert("length not accepted");
            
        }
        
    }

    function cgetDuplicate1(AccountNo,OR_Number,TargetExecButton){ //customized
        var OR_Number=OR_Number
        var AccountNo=AccountNo
        $.ajax({
            type: "POST",
            url: "ajax-actions.php",
            data: {or_no:OR_Number,cashier:'payment',acnt_no:AccountNo}
            }).done(function( result ) {
                var ajaxResult=$.trim(result);
                var idSaveButton=$("#"+TargetExecButton);
                var arr;
                var msg;
                var Str_length;
                if(ajaxResult=='ok'){
                    idSaveButton.attr('style','display:inline');
                }else{
                    arr=$.trim(result).split('|',$.trim(result).length);
                    alert( 'OR Number '+ arr[0] + ' had been release to ' + arr[1] + " last " + arr[2]);
                }
          });            
    }    

    

	function getDuplicate(){
		var OR_Number=$.trim($("#or_no").val());
		var CashierAction= $.trim($("#cashier").val());
		var AccountNo=$.trim($("#acnt_no").val());
		$.ajax({
			type: "POST",
			url: "ajax-actions.php",
			data: {or_no:OR_Number,cashier:CashierAction,acnt_no:AccountNo}
			}).done(function( result ) {
				var ajaxResult=$.trim(result);
				var idSaveButton=$("#ajx-B-OR-Save");
				var arr;
				var msg;
				var Str_length;
				if(ajaxResult=='ok'){
					idSaveButton.attr('style','display:inherit');
				}else{
					arr=$.trim(result).split('|',$.trim(result).length);
					alert( 'OR Number '+ arr[0] + ' had been release to ' + arr[1] + " last " + arr[2]);
				}
		});            
	}    

	function getBillAmount(){
		var wcu_used=parseInt($.trim($("#cu_used").val()));
		$.ajax({
			type: "POST",
			url: "ajax-actions.php",
			data: {cu_used:wcu_used,AccntNo:$('#AccntNo').val(),bill:'bill_amount'} 
			}).done(function( result ) {
				var billAmount=parseFloat(result);
				$('#bill_amnt').val(result);
			});            
	}    
//todo: [start] stage for commit to remote PS(status:ongoing)
	function getBarangayOpt(AccountNo,TargetHTMLElementID,SelectObjectName){
		$.ajax({
			type: "POST",
			url: "ajax-actions.php",
			data: {accnt_no:AccountNo,misc:'getOptBrgy',objName:SelectObjectName}
			}).done(function( result ) {
				//alert("ajax results="+ html(result));
				$('#'+TargetHTMLElementID).html(result);
			});            
	}    

	function SettleOtherPayment(){
        var InputValue=$('#accnt_names').val().split('|').length
        if(InputValue==2){
		var form_data=$("#form1").serialize();
        var SubmitKeysValue=form_data + '&payment=other_payment'
		$.ajax({
			type: "POST",
			url: "ajax-actions.php",
			data: SubmitKeysValue
			}).done(function( result ) {
				alert("ajax results="+ result);
				//$('#'+TargetHTMLElementID).html(result);
			});
        }else{alert('invalid account detected')}
    }
    
    function GenerateJWSReport1(TargetHTMLElementID){ //FFU#39
    $('#' + TargetHTMLElementID).text('Serializing Form Values Submitted..')
    var values=$("#form1").serialize();
    var SubmitKeysValue=values + "&report=jws1"
    $('#' + TargetHTMLElementID).text('Submitting Form Values Acquired..')
    $('#' + TargetHTMLElementID).text('Generating Reports..')
    $.ajax({
        type: "POST",
        url: "ajax-actions.php",
        data: SubmitKeysValue
        }).done(function( result ) {
            $('#'+TargetHTMLElementID).html(result);
        });            
    }
    
    function SearchOR(TargetInputBox,TargetOutput){
        $('#'+TargetOutput).html("<strong>Searching...</strong>");
        var InputOR=$('#'+TargetInputBox).val()
        if(InputOR.length > 4 && $.isNumeric(InputOR)){ 
            var values=$("#form1").serialize();
            var SubmitKeysValue=values + "&cashier=search_or&bSearch=0"
            $('#' + TargetInputBox).text('Searching for OR# please wait..')
            $.ajax({
                type: "POST",
                url: "ajax-actions.php",
                data: SubmitKeysValue
                }).done(function( result ) {
                    $('#'+TargetOutput).html(result);
                });            
        }
    }


    function bSearchOR(TargetInputBox,TargetOutput){
        var InputOR=$('#'+TargetInputBox).val()
        $("#loader").attr('src','icon/ajax-loader.gif')
        $("#loader").attr('style','display:inherit')
        if(InputOR.length > 6 && $.isNumeric(InputOR)){ 
            //var values=$("#new_ledger").serialize();
            var values=$("#"+TargetInputBox).val();
            var SubmitKeysValue="cashier=search_or&bSearch=1&or_num="+values;
            $.ajax({
                type: "POST",
                url: "ajax-actions.php",
                data: SubmitKeysValue
                }).done(function( result ) {
                    $("#loader").attr('style','display:none')
                    //alert('result=' + result);
                    if(( result == '' )){
                        alert('OR# '+ InputOR +' Entered Accepted!');
                        $('#'+TargetOutput).val( InputOR );    
                                            
                    }else if(result=='1'){
                        $('#'+TargetOutput).val('');
                        alert('OR# '+ InputOR +' Entered Already Issued!');
                    }
                    
                });            
        }
    }
    
    function AccntNames(objControlName){ //object control name can be class or id value
        var strTerm=$.trim($(objControlName).val());
        if(strTerm.length!=0){
            var strQry
            $.ajax({
               type: "POST" ,
               url: "lstEmpl_ajx.php",
               data: "req=ConsNames&term=" + strTerm //prepare query strings
            }).done(function(result){
                var Tags=$.parseJSON(result)
                $('body').on('keyup',$(objControlName),function(){
                $( objControlName ).autocomplete({
                                minlength:1,
                                source: Tags,
                                select: function(event,ui){
                                    if(event.keyCode==13){
                                        selAccntNames($(this).val())
                                    }
                                } 
                                });
                        })
            });
            
        }
    }
    function AccntNamesOnly(objControlName){ //object control name can be class or id value
        var strTerm=$.trim($(objControlName).val());
        if(strTerm.length!=0){
            var strQry
            $.ajax({
               type: "POST" ,
               url: "lstEmpl_ajx.php",
               data: "req=ConsNames&term=" + strTerm //prepare query strings
            }).done(function(result){
                var Tags=$.parseJSON(result)
                $('body').on('keyup',$(objControlName),function(){
                $( objControlName ).autocomplete({
                                minlength:1,
                                source: Tags,
                                select: function(event,ui){
                                    if(event.keyCode==13){
                                        //selAccntNames($(this).val())
                                    }} });})});}}
    
    function selAccntNames(DataValue){
        if(DataValue.indexOf('|',0)){
                var Data=DataValue.split('|')
                alert("barangay=" + Data[1])
                getBarangayOpt(Data[1],'from_brgy','from_brgy')
                getBarangayOpt(Data[1],'to_brgy','to_brgy')
            }
    }
    
    function GeneralProfileSearch(objControlNameID,objSearchResultsHolderID){
        var strText=$.trim($('#' + objControlNameID).val())
        $.ajax({
            type: "POST",
            url: "ajax-actions.php",
            data: "utility=general_search_profile&term=" + strText
        }).done(function(result){
              //$.Zebra_Dialog('this is the content',{'type': 'information','buttons':'Close','title':'Profile View'})
             $('#'+ objSearchResultsHolderID).html(result)        
        });   
    }