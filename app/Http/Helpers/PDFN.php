<?php
namespace App\Http\Helpers;
use setasign\Fpdi\Fpdi;
 
class PDFN extends FPDI
{
    function Footer()
    {
        $this->SetFont('Helvetica', '', 6);
        $this->SetY(-15.7);
        $this->SetX(17);
        $this->Cell(0, 10, $this->PageNo().' de {nb}', 0, 0, 'L');
    }
}