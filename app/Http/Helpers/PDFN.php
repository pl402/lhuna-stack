<?php
namespace App\Http\Helpers;
use setasign\Fpdi\Fpdi;

class PDFN extends FPDI
{
    function Footer()
    {
        $meses = [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre",
        ];
        $fecha =
            date("j") .
            " de " .
            $meses[date("n")] .
            " del " .
            date("Y") .
            " a las " .
            date("H:i:s");
        $this->SetFont("Helvetica", "", 5);
        $this->SetTextColor(50);
        $this->SetY(-10);
        $this->SetX(9);
        $this->Cell(0, 0, "Generado el " . $fecha, 0, 0, "C");
        $this->SetY(-10);
        $this->SetX(9);
        $this->Cell(
            0,
            0,
            utf8_decode("Página ") . $this->PageNo() . " de {nb}",
            0,
            0,
            "L"
        );
        $this->SetY(-10);
        $this->SetX(9);
        $this->Cell(0, 0, "Sistema PDI", 0, 0, "R");
    }
}