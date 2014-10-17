<?php
  include_once 'database.php';
  
  //Procedures-export
        clsDatabase::BackupStructure();
        clsDatabase::BackupData();
  
  //procedure-import
        //clsDatabase::ImportStructure();
        //clsDatabase::ImportData();
?>
