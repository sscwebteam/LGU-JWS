$(document).ready(function(){    
//confirmation class for errors    
	$('.error').addClass('error_input');    
	$('.confirmation').addClass('confirmation');
//hint for textboxes
	  $('input.clear').each(function() {
		$(this)
		  .data('default', $(this).val())
		  .addClass('inactive')
		  .focus(function() {
			$(this).removeClass('inactive');
			if($(this).val() == $(this).data('default') || '') {
			  $(this).val('');
			}
		  })
		  .blur(function() {
			var default_val = $(this).data('default');
			if($(this).val() == '') {
			  $(this).addClass('inactive');
			  $(this).val($(this).data('default'));
			}
		  });
	  });
//end of hint for texboxes
});
function reading_back(){    
	//shows the minimum reading date
	//alert ('ako');
		var downgrade = $('.ledger_2 tbody tr td:first').text();
		document.getElementById('wawa').value = '';
		document.getElementById('wawa').value = downgrade;
	}
function difference(previous){
	//var present = parseInt(document.getElementById('old_reading').value);
	var present = parseInt($('#old_reading').val());
	var result = present - (parseInt(previous));
	//document.getElementById('cu_used').value = result;
	$('#cu_used').val(result);
}

function compute(value){
	var results = parseFloat(value) * 0.05;
	$('#pen_fee').val(results.toFixed(2));
}

function sum(){
	var pen_fee= parseFloat($("#pen_fee").val());
	var bill_amount=parseFloat($("#bill_amnt").val());
	var loans_MLP=parseFloat($("#loans_MLP").val());
	var loans_MF=parseFloat($('#loans_MF').val());
	var misc=parseFloat($('#misc').val());
	//check values
	if($.isNumeric(pen_fee)==false){pen_fee=0.00;}
	if($.isNumeric(bill_amount)==false){bill_amount=0.00;}
	if($.isNumeric(loans_MLP)==false){loans_MLP=0.00;}
	if($.isNumeric(loans_MF)==false){loans_MF=0.00;}
	if($.isNumeric(misc)==false){misc=0.00;}
	
	var total= pen_fee + bill_amount + loans_MLP + loans_MF + misc;
	$("#total").val(total.toFixed(2));
}

function submitform(){
	document.forms[0].submit();
}

/* New method used to autocomplete*/

		function search_word(){  
				if( $('input[name="accountno"]').attr('value')==''){
					$('#resulta').remove();
				 }   
			var search_me = $('input[name="accountno"]').attr('value');
			$.getJSON('testing_jquery.php?search_me=' + search_me ,show_results);
		}
		function show_results(res){
			if($('#resulta')){
				$('#resulta').remove();
			}
			var to_display='';
			to_display = '<table id="resulta"><tbody>';
			for(idx in res.accountno){
				to_display +=  '<tr><td onclick="javascript:place_it(this.innerHTML);$(\'#butangan\').remove();">' + res.accountno[idx] + '<td></tr>';
			};
			to_display += '</tbody></table>';
			$(to_display).appendTo('#butangan');
		}
		function place_it(account){
			 $('input[name="accountno"]').attr('value',account);
		}
  /* End of autocomplete method*/        