<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class AttendanceReportExport implements FromView
{

        public function __construct(

        public array $attendanceData,
        public array $dates,
        public $startDate,
        public $endDate,
        public array $columnTotals,
        public int $grandTotal
        )
        {}


    public function view(): View
    {

            return view('exports.attendance_excel', [
                'attendanceData' => $this->attendanceData,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'dates' => $this->dates,
                'columnTotals' => $this->columnTotals,
                'grandTotal' => $this->grandTotal,
            ]);

    }

    public function styles(Worksheet $sheet)
    {
        // Optional: Set default font and size
        $sheet->getStyle('A:Z')->getFont()->setName('Arial')->setSize(10);

        // Header style
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFDEEAF6']],
            'alignment' => ['horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        // Apply border to all cells (adjust row count dynamically if needed)
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        return [];
    }

}

