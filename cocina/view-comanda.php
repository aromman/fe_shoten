<?php

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){

    // CONFIGURACIÓN PREVIA
    require_once '../fpdf186/fpdf.php';

    $idTicket = $_GET["id"];    

    define('EURO',chr(128)); // Constante con el símbolo Euro.
    $pdf = new FPDF('P','mm',array(80,150)); // Tamaño tickt 80mm x 150 mm (largo aprox)
    $pdf->AddPage();

    // CABECERA
    $pdf->SetFont('Helvetica','',12);
    $pdf->Cell(60,4,'Lacodigoteca.com',0,1,'C');
    $pdf->SetFont('Helvetica','',8);
    $pdf->Cell(60,4,'C.I.F.: 01234567A',0,1,'C');
    $pdf->Cell(60,4,'C/ Arturo Soria, 1',0,1,'C');
    $pdf->Cell(60,4,'C.P.: 28028 Madrid (Madrid)',0,1,'C');
    $pdf->Cell(60,4,'999 888 777',0,1,'C');
    $pdf->Cell(60,4,'alfredo@lacodigoteca.com',0,1,'C');
    
    // DATOS FACTURA
    $pdf->Ln(5);
    $pdf->Cell(60,4,'Factura Simpl.: F2019-000001',0,1,'');
    $pdf->Cell(60,4,'Fecha: 28/10/2019',0,1,'');
    $pdf->Cell(60,4,'Metodo de pago: Tarjeta',0,1,'');
    
    // COLUMNAS
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->Cell(30, 10, 'Articulo', 0);
    $pdf->Cell(5, 10, 'Ud',0,0,'R');
    $pdf->Cell(10, 10, 'Precio',0,0,'R');
    $pdf->Cell(15, 10, 'Total',0,0,'R');
    $pdf->Ln(8);
    $pdf->Cell(60,0,'','T');
    $pdf->Ln(0);
    
    // PRODUCTOS
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->MultiCell(30,4,'Manzana golden 1Kg',0,'L'); 
    $pdf->Cell(35, -5, '2',0,0,'R');
    $pdf->Cell(10, -5, number_format(round(3,2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Cell(15, -5, number_format(round(2*3,2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Ln(3);
    $pdf->MultiCell(30,4,'Malla naranjas 3Kg',0,'L'); 
    $pdf->Cell(35, -5, '1',0,0,'R');
    $pdf->Cell(10, -5, number_format(round(1.25,2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Cell(15, -5, number_format(round(1.25,2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Ln(3);
    $pdf->MultiCell(30,4,'Uvas',0,'L'); 
    $pdf->Cell(35, -5, '5',0,0,'R');
    $pdf->Cell(10, -5, number_format(round(1,2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Cell(15, -5, number_format(round(1*5,2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Ln(3);
    
    // SUMATORIO DE LOS PRODUCTOS Y EL IVA
    $pdf->Ln(6);
    $pdf->Cell(60,0,'','T');
    $pdf->Ln(2);    
    $pdf->Cell(25, 10, 'TOTAL SIN I.V.A.', 0);    
    $pdf->Cell(20, 10, '', 0);
    $pdf->Cell(15, 10, number_format(round((round(12.25,2)/1.21),2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Ln(3);    
    $pdf->Cell(25, 10, 'I.V.A. 21%', 0);    
    $pdf->Cell(20, 10, '', 0);
    $pdf->Cell(15, 10, number_format(round((round(12.25,2)),2)-round((round(2*3,2)/1.21),2), 2, ',', ' ').EURO,0,0,'R');
    $pdf->Ln(3);    
    $pdf->Cell(25, 10, 'TOTAL', 0);    
    $pdf->Cell(20, 10, '', 0);
    $pdf->Cell(15, 10, number_format(round(12.25,2), 2, ',', ' ').EURO,0,0,'R');
    
    // PIE DE PAGINA
    $pdf->Ln(10);
    $pdf->Cell(60,0,'EL PERIODO DE DEVOLUCIONES',0,1,'C');
    $pdf->Ln(3);
    $pdf->Cell(60,0,'CADUCA EL DIA  01/11/2019',0,1,'C');

    $pdfNamePath = '../tickets/ticket_'.$idTicket.'.pdf';

    //$pdf->Output('F',$pdfNamePath);
    $pdf->Output();

    //shell_exec("'C:\Program Files\Adobe\Acrobat 6.0\Reader\acrord32.exe' /t c:\ticket.pdf Microsoft Print to PDF");
    //shell_exec('cmd /C print /D:XP-58C '.$pdfNamePath);

}

//header('location: monitor.php');
//exit();
