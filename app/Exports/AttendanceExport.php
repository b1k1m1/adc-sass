<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Http\Controllers\AttendanceController;
use Carbon\Carbon;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AttendanceExport implements FromArray, WithHeadings, WithTitle
{
    protected $startDate, $endDate, $type;
    protected $data;

    public function __construct($startDate, $endDate, $type)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->type = $type;
        // Manually call the logic from the controller to fetch structured data
        //$controller = new AttendanceController();
        $this->data = AttendanceController::getAttendanceReportData_new($this->startDate, $this->endDate, $this->$type);
    }

    public function array(): array
    {
        $output = [];

        foreach ($this->data['report'] as $row) {
            $line = [
                $row['ParticipantId'],
                $row['LastName'],
                $row['FirstName'],
            ];

            foreach ($this->data['dates'] as $date) {
                $line[] = $row['attendance'][$date];
            }

            $line[] = $row['row_total'];
            $output[] = $line;
        }

        // Add column totals at the end
        $totals = ['Total', '', ''];
        foreach ($this->data['dates'] as $date) {
            $totals[] = $this->data['columnTotals'][$date];
        }
        $totals[] = ''; // empty for total of totals
        $output[] = $totals;

        return $output;
    }

    public function headings(): array
    {
        $heading = ['Participant ID', 'Last Name', 'First Name'];

        foreach ($this->data['dates'] as $date) {
            $heading[] = Carbon::parse($date)->format('d-M');
        }

        $heading[] = 'Total';

        return $heading;
    }

    public function title(): string
    {
        return 'Attendance Report';
    }



}
