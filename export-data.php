<?php 

session_start();

if(isset($_REQUEST['entityName']) and $_REQUEST['entityName']!=""){

    $entityName = $_REQUEST['entityName'];
    $rowsName = $entityName . 'Rows';
    //error_log(PHP_EOL.'Exportando '.$entityName. "[".$rowsName."]", 3, "my-errors.log");
    $filename = $entityName."_data_export_".date('Ymd') . ".csv";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=".$filename."");
    $show_coloumn = false;
    $result = $_SESSION[$rowsName];
    if(!empty($result)) {
        foreach($result as $record) {
            if(!$show_coloumn) {
                // display field/column names in first row
                echo implode(',', array_keys($record)).PHP_EOL;
                $show_coloumn = true;
            }
            echo '"' . implode('","', array_values($record)) . '"' . PHP_EOL;
        }
    }
    exit;
}
?>