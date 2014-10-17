<!--center content starts!-->
<script type="text/javascript">
    $(document).ready(function (){  
         $('input[name="search_me"]').keyup(search_word);
    });
function search_word(){  
        if( $('input[name="search_me"]').attr('value')==''){
            $('#resulta').remove();
         }   
    var search_me = $('input[name="search_me"]').attr('value');
    var category_me = $('select[name="category"]').attr('value');
    $.getJSON('testing_jquery.php?search_me=' + search_me + '&category=' + category_me,show_results);
}
function show_results(res){
    if($('#resulta')){
        $('#resulta').remove();
    }
    var to_display='';
    to_display = '<table id="resulta"><tbody>';
    for(idx in res.cities){
        to_display +=  '<tr><td>' + res.cities[idx] + '<td></tr>';
    };
    to_display += '</tbody></table>';
    $(to_display).appendTo('#butangan');
}
function place_it(account){
   alert(account);  
    // $('input[name="search_me"]').attr('value',account);
}
</script> 
<div class="art-content-layout">
                <div class="art-content-layout-row">
                    <div class="art-layout-cell art-content">
<div class="art-post">
    <div class="art-post-body">
<div class="art-post-inner art-article">
                            
                <div class="cleared"></div>
                                <div class="art-postcontent">
          <div class="cleared"></div>    
                 <?php
                    if(isset($_SESSION['profileid'])){
                        ?>
                        <table>
                             <tr>
                                <td><img src="icon/administration.jpg" style="width: 80px;height: 80px;border:double;"></td>
                                <td><p style="text-align: justify;"><b style="font: 20pt bold;">Admin</b><br>
                                                                                                            Admin panel allows you to customize user accounts. Creating user accounts, passwords  with their corresponding priviledges in the software.</p></td>    
                            </tr>
                            <tr>
                                <td><img src="icon/bill.jpg" style="width: 80px;height: 80px;border:double;float:left"></td>
                                <td><p style="text-align: justify;"><b style="font: 20pt bold;">JWS</b><br>
                                                                                                            JWS panel allows you to check and update ledgers of clients. Importantly, it enables you to process billings transactions.</p></td>                                
                            </tr>
                            <tr>
                                <td><img src="icon/cashier.jpg" style="width: 80px;height: 80px;border:double;"></td>
                                <td><p style="text-align: justify;"><b style="font: 20pt bold;">Cashier</b><br>Cashier panel processes payments of clients to settle their billings. It provides information to prioritize payments of accounts with due dates and notices.</p></td>    
                            </tr>
                             <tr>
                                <td><img src="icon/accounting.jpg" style="width: 80px;height: 80px;border:double;"></td>
                                <td><p style="text-align: justify;"><b style="font: 20pt bold;">Accounting</b><br>Accounting panel allows you to manage reports, download files for cash receivables and receipts, generate charts and assess user ledgers.</p></td>    
                            </tr>
                        </table>
                       
                       
                           <?php
                    }else{
                             #this is the login box form
                        ?>
                          <h2 class="art-postheader"> LOG IN </h2> 
                        <div style="margin:1em 0 0 0;position:absolute" align="left" id="loginform"> 
                         
                             <div class="err" id="add_err"></div>     
                                            
                            <form class="box login" method="POST" action="main_file.php">
                                <fieldset class="boxBody">
                                <!-- -   <label>Username</label>-->
                                      <input name="username" type="text" id="username" tabindex="1" placeholder="Username" required>
                                             <!--  <label><a href="#" class="rLink" tabindex="5">Forget your password?</a>Password</label> -->
                            
                                      <input name="password" type="password" id="password" tabindex="2" placeholder="Password" required>
                                </fieldset>
                                <footer>
                                             <!--  <label><input type="checkbox" tabindex="3">Keep me logged in</label>  -->
                                  <input name="submit" type="submit" id="login" class="btnLogin" value="Login" tabindex="4">
                                </footer>
                            </form>                             
                      </div> 
                      
   <!--                    <div id="shadow" class="popup"></div>
                       

                      <div style="float: right;margin-right: 2em;">
                          <h2 class="art-postheader">Search for Confirmation</h2> 
                             <input type="text" name="search_me">
                              <select name="category">
                                    <option value="accnt_no">Account Number</option>
                                    <option value="app_name">Name</option>
                                    <option value="meter_no">Meter Number</option>
                              </select>
                              
                              <div class="datagrid" id="butangan"></div>
                      </div>           -->
                        <?php
                    }                 
                                 ?>
                                       
				             
            <!-- this is used to put space around the login page -->
              <div style="margin-top: 10em;padding-top:10em;border-top:10em;margin-bottom: 5em;"> </div>                   
                                
                                
                                
<!--  
<p>Enter Page content here...</p>
<p style="text-align:justify;"><img src="images/preview.jpg" alt="an image" style="float:left;" />Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam pharetra, tellus sit amet congue vulputate, nisi erat iaculis nibh, vitae feugiat sapien ante eget mauris. Cras elit nisl, rhoncus nec iaculis ultricies, feugiat eget sapien. Pellentesque ac felis tellus.</p>
<p style="text-align:justify;"><a>Link </a></p>
<table class="art-article" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
	<tbody>
		<tr class="even">
			<td><br /></td>
			<td><br /></td>
			<td><br /></td>
		</tr>
		<tr>
			<td><br /></td>
			<td><br /></td>
			<td><br /></td>
		</tr>
		<tr class="even">
			<td><br /></td>
			<td><br /></td>
			<td><br /></td>
		</tr>
	</tbody>
</table>
<p style="text-align:justify;"></p>
<p style="text-align:justify;">Aenean sollicitudin imperdiet arcu, vitae dignissim est posuere id. Duis placerat justo eu nunc interdum ultrices. Phasellus elit dolor, porttitor id consectetur sit amet, posuere id magna. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
<p style="text-align:justify;"> Suspendisse pharetra auctor pharetra. Nunc a sollicitudin est. Curabitur ullamcorper gravida felis, sit amet scelerisque lorem iaculis sed. Donec vel neque in neque porta venenatis sed sit amet lectus. Fusce ornare elit nisl, feugiat bibendum lorem. Vivamus pretium dictum sem vel laoreet. In fringilla pharetra purus, semper vulputate ligula cursus in. Donec at nunc nec dui laoreet porta eu eu ipsum. Sed eget lacus sit amet risus elementum dictum.</p>

-->
                </div>
                <div class="cleared"></div>
                </div>

		<div class="cleared"></div>
    </div>
</div>

                      <div class="cleared"></div>
                    </div>