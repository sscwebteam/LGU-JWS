$(document).ready(function(){  
    //this function will monitor the billing entry   
        $('input[name="accnt_no"]').keyup(function(){
            if($('#butangan')){
                    $('#butangan').fadeOut();
                 }else{
                     $('#butangan').fadeIn();
                 }  
            search_name();     
        }); 
});        
            function search_name(){
                var laog = $('input[name="accnt_no"]').attr('value');
                $.ajax({
                    type:'POST',
                    url:'testing_jquery.php',
                    data:'action=get_name&applicant='+laog,
                    dataType:'json',
                    success:function(data){
                         var to_display='';
                         to_display = '<tbody>';
                         for(idx in data){                            
                                to_display +=  '<tr><td onclick="javascript:place_it('+data[idx].account_no+')"";$(\'#butangan\').fadeOut();">' + data[idx].applicant+ '<td></tr>';                         
                        }
                        to_display += '</tbody></table>';                            
                        $(to_display).appendTo('#butangan');
                    }
                });           
            }
          


         function place_it(account){
             $('input[name="accnt_no"]').attr('value',account);
        }
        

 //todo -o mike -p 10 -c For Upload: [start]update script files for billing
//customized function starts here

function Installment(){
    var terms=parseInt($("#terms").val());
    var Amount=parseFloat($("#total_amount").val());
    var InstalAmnt= Amount/terms;
    var objPrefix="RDates";
    
    $("#instal_amnt").val(InstalAmnt.toFixed(2));
    
    if(!$.isNumeric($("#instal_amnt").val())){$("#instal_amnt").val('0.00')
     }else{
        //create object on demand
        $("#CreateObj").html(ObjOnReq('text',objPrefix,terms));
        //process the reading date of the concessionair specified
        var form_values=$("#form1").serialize();
        //alert("form_values="+form_values);
        $.ajax({
        type: "POST",
        url: "ajax-actions.php",
        data: form_values + "&misc=" + "getlastreadingdates"
        }).done(function( result ) {
            var iterate,arrData,arrLen,arrSplit;
            arrData=$.parseJSON( result )
            iterate=0;
            //enter the reading dates to each text box created
            $.each(arrData,function(index,values){
                $("#" + objPrefix + iterate).attr('value',values);
                iterate=iterate + 1;
            })
        });
    }           
        //FillValues('text','sample',terms);
}
    
function ObjOnReq(objType,PrefixValue,objCount){
    var iterate; var HtmlOut; var objControl;
        if(objCount==1){
            HtmlOut="<tr><td><input type=" + objType + " name=" + PrefixValue + objCount + " id=" + PrefixValue + objCount+ " readonly size='10'></td></tr>"
            //HtmlOut="<tr><td><input type=" + objType + " name=0" + PrefixValue + iterate + " id=0" + PrefixValue + iterate + " size='10'></td><td><input type=" + objType + " name=1" + PrefixValue + iterate + " id=1" + PrefixValue + iterate + " size='10'></td></tr>";
        }else{iterate=0;
        while(iterate < objCount)
        {
            objControl="<input type=" + objType + " name=" + PrefixValue + iterate + " id=" + PrefixValue + iterate + " readonly size='10'><br/>";
            //objControl="<tr><td><input type=" + objType + " name=0" + PrefixValue + iterate + " id=0" + PrefixValue + iterate + " size='10'></td><td><input type=" + objType + " name=1" + PrefixValue + iterate + " id=1" + PrefixValue + iterate + " size='10'></td></tr>";
            HtmlOut= objControl + HtmlOut;
            iterate++;
        } }
    return HtmlOut;
}

function FillValues(TargetType,PrefixValue,ObjCount){
    var iterate;
    if(ObjCount==1){
        $("#" + PrefixValue + iterate).val("sample values" + ObjCount);
    }else{
    var Count=ObjCount -1;
    for(iterate=0;iterate <= Count;iterate++){
        $("#" + PrefixValue + iterate).attr('value',"sample values" + iterate);
    }
} }

function BillingOptSave(){
    var form_values=$('#form1').serialize();
    var SubmitKeysValues=form_values + "&bill=" + "Addons";
    //$("#form1").empty();
   
    $.ajax({
        type: "POST",
        url: "ajax-post-actions.php",
        data: SubmitKeysValues
        }).done(function( result ) {
            var StatusMessage
        if( result!="error" ){
            $.Zebra_Dialog("<center><strong><h1>Data Save</h1></center></strong>",{'type':'information','title':'Status'});
        }else{
            $.Zebra_Dialog("<center><strong><h1>Error Saving Data</h1></center></strong>",{'type':'error','title':'Status'});
        }
    });
}


function GetMiscInstallment(){
    var AddonsTypeValue=$("#addons").val();
    var AccountNamevalue=$("#accnt_names").val();
    if(AddonsTypeValue=='misc_fee'){
        $.ajax({
        type: "POST",
        url: "ajax-actions.php",
        data: {accnt_names: AccountNamevalue, bill:"getmiscfeeavail"}
        }).done(function( result ) {
            if( result!="false" ){
            $.Zebra_Dialog("<center><strong><h1>Your account number " + AccountNamevalue +  " Contains a sum of "+ result +" for Miscellaneous Installment</h1></center></strong>",{'type':'information','title':'Status'});
        }else{
            $.Zebra_Dialog("<center><strong><h1>No Installment Payment for Miscellaneous Found</h1></center></strong>",{'type':'error','title':'Status'});
        }
         $("#total_amount").val( result )
    });
    }
}

function UserLogout(row_id){ //candidate for deletion
    var Keys_Values=$("#form1").serialize();
    var SubmitKeysValues=Keys_Values + "&user=" + "logout" + "&row_id=" + row_id;
    var dataKeys="user=" + "logout" + "&row_id=" + row_id;
    //alert(dataKeys);
     $.ajax({
        type: "POST",
        url: "ajax-post-actions.php",
        data: SubmitKeysValues
        }).done(function( result ) {
        if(result==1){
            $.Zebra_Dialog("<center><strong><h1>User had been sucessfully logout</h1></center></strong>",{'type':'information','title':'Status'});
            window.location.reload() //todo: FFU#75
        }else{
            $.Zebra_Dialog("<center><strong><h1>Error in User Logout</h1></center></strong>",{'type':'Error','title':'Status'});
        }
     });
}

function RetBillingNames(){
    var Filter=$.trim($("#barangay").val())
    $("#status").attr("style=display:inherit")
    $("#results").empty()   
    $.ajax({
        //parameters and options
        type: "POST",
        url:"ajax-post-actions.php",
        data: "bill=dl2_bill&filter=" + Filter
    }).done(function( result ){
       //send results to id_name=results 
       $('#results').html( result)
       $("#status").attr("style=display:none")   
    });    
}
//todo -o mike -p 10 -c For Upload: [end]update script files for billing