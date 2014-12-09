<?php
	
// !Get Data
require_once 'php/data.php';
$data = new data();

//Uncomment for debugging data
/*
echo '<pre>';
	print_r(print_r($data->getData()));
echo '</pre>';
exit;
*/

// !Make PDF
require_once 'php/pdf.php';
$pdf = new pdf();
$pdf = $pdf->makePdf($data->getData(), 'I');

print_r($pdf);