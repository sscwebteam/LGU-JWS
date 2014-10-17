$(document).ready(function(){
		$("status").hide();
})
	
	$(Document).ajaxStart(function(){
		//$( "#status").hide;
		$("#status").show('slow',function(){
			$( "#status").html("loading...");
			$( "#search" ).hide('slow');
		})
		
	})
	$(document).ajaxStop(function(){
		$( "#status").hide('slow',function(){
			//alert("load complete");
			$("#search").show('slow');
		});
	})
	
	function getdetails(){
	var name = $('#accnt').val();
	//var rno = $('#rno').val();
	$.ajax({
	type: "POST",
	url: "details.php",
	data: {accnt:name}
	}).done(function( result ) {
	$("#results").html( result );
	});
	//$("#search").hide();
	}
	