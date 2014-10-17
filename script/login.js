$(document).ready(function(){      
	$("#login").click(function(){
		username=$("#username").val();
		password=$("#password").val();
		$.ajax({
			type: "POST",
			url: "main_file.php",
			data: "username="+username+"&password="+password+"&submit=Login" ,
			success: function(html){
				if($.trim(html)=="true")
					{             
					 $("#loginform").fadeOut("normal");
					 $("#shadow").fadeOut();
					 window.location="index.php";
					}
				else{ 
					  $("#loginform").fadeIn("normal");
					 $("#add_err").remove();                       
					 alert("Wrong username-password combination Or these credentials where in currently in-Use");
					}
			},
			
			beforeSend:function(){
					$("#loginform").fadeOut("normal");
					$("#add_err").html("Loading...")
			}
		 });
  
		 return false;
 });
});