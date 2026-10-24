<?php

namespace App\Support;

use Mpdf\Mpdf;

class PdfFactory
{
    public static function mpdf(array $cfg = []): Mpdf
    {
        // Force mPDF temp folder to Laravel storage (writable on Apache)
        $cfg['tempDir'] = storage_path('app/mpdf');

        return new Mpdf($cfg);
    }
}

