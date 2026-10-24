<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RecommendationsController extends Controller
{
    public function faqPdf()
    {
        $data = [
            'title' => 'Chat Bot User Guide',
        ];

        $pdf = Pdf::loadView('chat.recommendations.faq', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'dejavu sans',
        ]);

        return $pdf->download('chat-bot-user-guide.pdf');
    }

    public function viewFaqPdf()
    {
        $data = [
            'title' => 'Chat Bot User Guide',
        ];

        $pdf = Pdf::loadView('chat.recommendations.faq', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('chat-bot-user-guide.pdf');
    }

    public function generatePdf()
    {
        $data = [
            'title' => 'Hybrid Chat Enhancement Recommendations',
            'date' => date('F j, Y'),
        ];

        $pdf = Pdf::loadView('chat.recommendations.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'dejavu sans',
        ]);

        return $pdf->download('chat-enhancement-recommendations.pdf');
    }

    public function viewPdf()
    {
        $data = [
            'title' => 'Hybrid Chat Enhancement Recommendations',
            'date' => date('F j, Y'),
        ];

        $pdf = Pdf::loadView('chat.recommendations.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('chat-enhancement-recommendations.pdf');
    }

    public function technicalDocsPdf()
    {
        $data = [
            'title' => 'Hybrid Chat System - Technical Documentation',
            'date' => date('F j, Y'),
        ];

        $pdf = Pdf::loadView('chat.recommendations.technical-docs', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'dejavu sans',
        ]);

        return $pdf->download('chat-technical-documentation.pdf');
    }

    public function viewTechnicalDocsPdf()
    {
        $data = [
            'title' => 'Hybrid Chat System - Technical Documentation',
            'date' => date('F j, Y'),
        ];

        $pdf = Pdf::loadView('chat.recommendations.technical-docs', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('chat-technical-documentation.pdf');
    }
}
