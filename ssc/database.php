<?php
  class clsDatabase{
    
    var $UserName='root';
    var $Host='localhost';
    var $Pswd='ict';
    
    var $SourceDB='table1'; //export 
    var $DestDB='test2'; //import
    
    //location must be relative to parent execution
    var $BackupStructLocation="/struct/"; 
    var $BackupDataLocation="/data/";
    
    
    //do not change beyond this line----------------------------------------------
    
    var $SQLFileExt=".sql";
    
    public function Connection($DBName=null){
        $gVars=new clsDatabase();
        $DB_host=mysql_connect($gVars->Host,$gVars->UserName,$gVars->Pswd);
        if($DBName==null){$DefDB=$gVars->SourceDB;}else{$DefDB=$DBName;}
        mysql_selectdb($DefDB,$DB_host);
    }

    public function GetTables(){
        clsDatabase::Connection();
        $SQL_GetTables="show tables";
        $e=new Exception();
        $QRY_GetTables=mysql_query($SQL_GetTables) or die(mysql_error());
        while($ROW_GetTables=mysql_fetch_row($QRY_GetTables)){
            $TablesList[]=$ROW_GetTables[0];
         }
        return $TablesList;
    }
    
/**
*  Method Recreate directory based on parent file that initiate the command
*/
    public function CreateSourceDir(){
        $PathInfo=pathinfo(__FILE__);
        $Path=str_replace('\\','/',$PathInfo['dirname']);
        return $Path;
        
    }
    
    //----------------------------------------------------------------Export Procedures[starts]
    public function BackupStructure(){
        clsDatabase::Connection();
        $gVars=new clsDatabase();
        //$FolderLoc=$_SERVER['DOCUMENT_ROOT'].$gVars->BackupStructLocation;
        $FolderLoc=self::CreateSourceDir(). $gVars->BackupStructLocation;
        $TablesList=clsDatabase::GetTables();
        for($i=0;$i < count($TablesList);$i++){
            //if($TablesList[$i]!='collectorsreport_2013_12_02_cashier1'){ //temporary exception only
                $FileName=$FolderLoc.$TablesList[$i].$gVars->SQLFileExt;
                if(file_exists($FileName)){
                    unlink($FileName);
                }
                $FileHandle=fopen($FileName ,'a+');
                $strSQL="show create table {$TablesList[$i]}";
                $e=new Exception();
                $qQRY=mysql_query($strSQL) or die(mysql_error());
                $rROW=mysql_fetch_array($qQRY);
                $data=$rROW[1]."\r\n";
                fwrite($FileHandle,$data);
                fclose($FileHandle);
                echo "Status: Structure Backup ". number_format((($i+1)/count($TablesList)) * 100,2) . "% Completed<br>";
            //}
        }
    }
    
    public function BackupData(){
        ini_set("memory_limit",-1);set_time_limit(0);
        $gVars=new clsDatabase();
        $SourceDB=$gVars->SourceDB;
        //initiate connection
        clsDatabase::Connection($SourceDB);    
            $SQL_GetAllTableNames="show tables";
            $e=new Exception();
            $QRY_GetAllTableNames=mysql_query($SQL_GetAllTableNames) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
            //get all tables from the current connection                
            while($ROW_GetAllTableNames=mysql_fetch_array($QRY_GetAllTableNames)){
                $Tables[]=$ROW_GetAllTableNames[0];
            }
            
            $BackupDir=self::CreateSourceDir(). $gVars->BackupDataLocation;
            $FileExt=".csv";
            //iterate to all tables and save to assigned destination directory
            for($i=0;$i<count($Tables);$i++){
                //if($Tables[$i]!='collectorsreport_2013_12_02_cashier1'){ //temporary exception only
                    $outfile=$BackupDir.$Tables[$i].$FileExt;
                    $TableName=$Tables[$i];
                    //sleep(5);
                    if(file_exists($outfile)){unlink($outfile);}
                    $e=new Exception();
                    $sql="select * from {$Tables[$i]} into outfile '{$outfile}' fields terminated by ',' enclosed by '\"' lines terminated by '\n'";
                    mysql_query($sql) or die(mysql_error()."__FILE=".$e->getFile()."__LINE".$e->getLine());
                    echo "Status: Data Backup ". number_format((($i+1)/count($Tables)) * 100,2) . "% Completed<br>";
                //}
            }
    }
    
//----------------------------------------------------------------Export Procedures[ends]

//----------------------------------------------------------------Import Procedures[start]

/**    
* method: Read Files based on requested file type
* @param string FileType: 1=structure,2=data
* @return Array
*/
    public function ReadStructDirFiles($FileType){ //1-structure,2=data
        error_reporting(0);
        $gVars=new clsDatabase();
        if($FileType=='1'){
            $DirectoyToScan=self::CreateSourceDir() .$gVars->BackupStructLocation;
        }elseif($FileType=='2'){
            $DirectoyToScan=self::CreateSourceDir().$gVars->BackupDataLocation;
        }
        
        $ScanFiles=scandir($DirectoyToScan);
        return $ScanFiles;
}
    
    public function ImportStructure(){
        $gVars=new clsDatabase();
        $DestDB=$gVars->DestDB;
        $FileLocation=self::CreateSourceDir().$gVars->BackupStructLocation;
        clsDatabase::Connection($DestDB);
        $FilesList=clsDatabase::ReadStructDirFiles('1');
        //print_r($FilesList);
        for($i=0;$i < count($FilesList);$i++){
            //bypass non-file listings
            if(ctype_alnum(substr($FilesList[$i],0,1))){
                echo "Importing table struct={$FilesList[$i]}";
                $arrData=file_get_contents($FileLocation.$FilesList[$i]);
                $e=new Exception();
                mysql_query($arrData) or die(mysql_error());
                echo "...DONE!<br>";
                }
            }
            
        }
        
    public function ImportData(){
        set_time_limit(0);
        $gVars=new clsDatabase();
        $DestDB=$gVars->DestDB;
        clsDatabase::Connection($DestDB);        
        //const Table "tablelisting"
        $BackupDir=self::CreateSourceDir().$gVars->BackupDataLocation;
            
        //get all files containing data
        $DataFiles=clsDatabase::ReadStructDirFiles('2');
        for($i=0;$i < count($DataFiles);$i++){
            //filter out non-file listings
            if(ctype_alnum(substr($DataFiles[$i],0,1))){
                //get the table name from the filename
                $FullPath=$BackupDir.$DataFiles[$i];
                $TableName=explode('.',$DataFiles[$i]);
                $e=new Exception();
                $sql1="truncate table {$TableName[0]}";
                mysql_query($sql1) or die(mysql_error()."__File:".$e->getFile()."__Line:".$e->getLine());
                //load file to specific table in the database selecte
                //note:make sure that the structure of the same table exist before inserting data
               $sql2="load data infile '{$FullPath}' into table {$TableName[0]} fields terminated by ',' enclosed by '\"' lines terminated by '\n'";
               $e=new Exception();
               mysql_query($sql2) or die(mysql_error());
            }
            echo "Import Progress: ".number_format(($i+1)/count($DataFiles)*100,2)."%<br>";
        }
    }

//----------------------------------------------------------------Import Procedures[ends]
    
  }
  
?>
