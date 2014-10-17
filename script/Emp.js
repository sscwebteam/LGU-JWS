var Empl_CustomScript={
    
    DelEmpl:function(Account){
        $.ajax({
            type: "POST",
            url: "lstEmpl_ajx.php",
            data: {accnt:Account,set:"del"}
        }).done(function( result ){
            Emp_ObjectScript.HideLoaderImage()
            if( result=='ok' ){
                $.Zebra_Dialog('Data Deletion Completed')
                $("#search").hide('slow')
            }else{
                $.Zebra_Dialog('Deletion Unsucessfull')
            }
        });
    },
    
    butEvent:function(ProcessID){
    switch(ProcessID){
        case '1': //used for adding employees
            $("#search").remove()
            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {optAction:ProcessID ,get:'AddForms'}
                }).done(function( result ) {
                    Emp_ObjectScript.HideLoaderImage()
                    $("#data").html(result)
                        $('body').on('keyup',$('.accnt_names'),function(){
                            $('.button').button();
                            $( ".accnt_names" ).autocomplete({
                                minlength:5,
                                source: 'lstEmpl_ajx.php?req=all_names'
                            });
                    })
                });            
                break;
         
         case '2': //used for unpaid bills
            $("#search").remove()
            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {optAction:ProcessID ,get:'AddForms'}
                }).done(function( result ) {
                    Emp_ObjectScript.HideLoaderImage()
                    $("#data").html(result)
                        $('body').on('keyup',$('.accnt_names'),function(){
                            $('button').button()
                            $( ".accnt_names" ).autocomplete({
                            minlength:5,
                            source: 'lstEmpl_ajx.php?req=emp'
                            });
                    })
                });            
            break;    
            
         case '3': //show listings
            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {get:'EmpList'}
                }).done(function( result ) {
                    Emp_ObjectScript.HideLoaderImage()
                    $("#data").html(result)
                });            
            
            break;

         case '4':
            $("#search").remove()
            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {optAction:ProcessID ,get:'AddForms'}
                }).done(function( result ) {
                    Emp_ObjectScript.HideLoaderImage()
                    $("#data").html(result)
                        $('body').on('keyup',$('.accnt_names'),function(){
                            $('button').button()
                            $( ".accnt_names" ).autocomplete({
                            minlength:5,
                            source: 'lstEmpl_ajx.php?req=emp'
                            });
                    })
                });            
            break;    
    }
    
    },

/*
*            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {name:AccountNo,set:'add'}
                }).done(function( result ) {
                
                });            
 
*/
    ViewUnpaidBills:function(AccountNo,idTargetOutput){
        Emp_ObjectScript.ShowLoaderImage()
        $.ajax({
            type: "POST",
            url: "lstEmpl_ajx.php",
            data: {accnt_no:AccountNo,get:'UnpaidBills'}
            }).done(function( result ) {
                Emp_ObjectScript.HideLoaderImage()
                $("#"+idTargetOutput).html(result)
            });            
    },

    AddEntry:function(AccountNo){
/*            var values=$("#form1").serialize();*/
/*            var SubmitKeysValue=values + "&set=add"*/
/*            $('#' + TargetInputBox).text('Searching for OR# please wait..')*/
        //var AccountNo=Empl_Misc.GetAccountNo(AccountNo)
            var StatusMsg
            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {name:AccountNo,set:'add'}
                }).done(function( result ) {
                if(result=='1'){ 
                    $.Zebra_Dialog('Successfully Save');
                    Empl_CustomScript.ShowEmpListings('emp_list') 
                    }
                else if(result=='0'){ 
                    $.Zebra_Dialog('Already Exist in the Listings');
                    Emp_ObjectScript.HideLoaderImage(); 
                    }
                }); 
        },
        
     ShowEmpListings:function(idTargetOutput){
            $.ajax({
                type: "POST",
                url: "lstEmpl_ajx.php",
                data: {get:'EmpList'}
                }).done(function( result ) {
                    Emp_ObjectScript.HideLoaderImage();
                    $("#"+idTargetOutput).html(result)
                    
                });
        }
                 
}

var Emp_EventScript={
    

    aHref_onClick:function(idEmpList){
        var idObj="#EmpList-" + idEmpList
        var data= $(idObj).attr('title')       
        var AccountNo=data.split('|')
        Empl_CustomScript.ViewUnpaidBills(AccountNo[1],'data')
    },
    
    ButAdd_onClick:function(ProcessID){
            Emp_ObjectScript.ShowLoaderImage()
            Empl_CustomScript.butEvent(ProcessID)
    },
    
    AddEntry:function(){
    
        var name= $("#accnt_names").val()
        Empl_Misc.FormDefAction('search')
        if(name==''){
            $.Zebra_Dialog('Please Type Name or Account Number');
        }else{
            Empl_CustomScript.AddEntry(name)
            Emp_ObjectScript.ShowLoaderImage();
        }
    },
    
    ViewUnpaidBills:function(){
        //alert('ongoing codes')
        Empl_Misc.FormDefAction('search');
        Emp_ObjectScript.ShowLoaderImage()
        var name= $("#accnt_names").val()
        var AccountNo=Empl_Misc.GetAccountNo(name)
        Empl_CustomScript.ViewUnpaidBills(AccountNo,'emp_list')
        
    },
    
    DelEmpl:function(){
        Empl_Misc.FormDefAction('search');
        Emp_ObjectScript.ShowLoaderImage()
        var name= $("#accnt_names").val()
        Empl_CustomScript.DelEmpl(name)
    }

}

var Empl_Misc={
    
    TR_BGC:function(Count,TR_Event){
        if(TR_Event=='over'){
            $("#trow" + Count).attr("style","background-color:gray")
        }else if(TR_Event=='out'){ 
            $("#trow" + Count).attr("style","background-color:inherit")
        }
        
    },
    
    FormDefAction:function(FormName){
        $("#" + FormName).submit(function(event){event.preventDefault();})
    },
    
    GetAccountNo:function(StringValue){ //return the account number
        var arrData=StringValue.split('|')
        return  arrData[1].trim()  
    },
    
    GetNameValue:function(StringValue){ //return the name only
        var arrData=StringValue.split('|')
        return arrData[0].trim()
    }
}

var Emp_ObjectScript={

    ShowLoaderImage:function(){
        $("#data_status").attr('style',"display:inherit")
/*        var Loader="<img src=images/loader.gif>"*/
/*        $("#data_status").html(Loader)*/
        
    },
    
    HideLoaderImage:function(){
        $("#data_status").attr('style',"display:none")
    }
    

}