<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaycareMedicineMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        // Morning=A, Noon=N (used to filter member dropdown like your other screens)
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $selectedParticipantId = $request->input('participant_id');

        // Members dropdown
        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->where('Trans_flag', $type)
            ->orderBy('ParticipantId')
            ->get();

        // Dropdown masters
        $physicians = DB::table('physicianmaster')
            ->select('doctor_id', 'lastname', 'firstname')
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        $dosages = DB::table('dosagemaster')
            ->select('dosage_id', 'dosage_nm')
            ->orderBy('dosage_nm')
            ->get();

        $freqs = DB::table('dosagefreqmaster')
            ->select('dosagefreq_id', 'dosagefreq_nm')
            ->orderBy('dosagefreq_nm')
            ->get();

        // Existing meds for selected member
        $rows = collect();
        if (!empty($selectedParticipantId)) {
            $rows = DB::table('daycare_member_meds')
                ->where('participant_id', $selectedParticipantId)
                ->orderBy('line_no')
                ->get();
        }

        return view('daycareform.medicine.index', compact(
            'type', 'members', 'selectedParticipantId', 'rows',
            'physicians', 'dosages', 'freqs'
        ));
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'A');
        if (!in_array($type, ['A','N'])) $type = 'A';

        $data = $this->validateRequest($request);

        $participantId = $data['participant_id'];
        $rows = $data['details'] ?? [];

        DB::beginTransaction();
        try {
            // replace rows for this member
            DB::table('daycare_member_meds')->where('participant_id', $participantId)->delete();

            $this->saveRows($participantId, $rows);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('medicine_maint.index', ['type' => $type, 'participant_id' => $participantId])
            ->with('message', 'Medication list saved.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable','in:A,N'],
            'participant_id' => ['required','string','max:10'],

            'details' => ['nullable','array'],

            'details.*.medicine_nm' => ['nullable','string','max:150'],
            'details.*.doctor_id'   => ['nullable','string','max:4'],

            // dropdown ids + manual text
            'details.*.dosage_id'      => ['nullable','string','max:20'],
            'details.*.dosage_other'   => ['nullable','string','max:50'],
            'details.*.dosagefreq_id'  => ['nullable','string','max:15'],
            'details.*.freq_other'     => ['nullable','string','max:50'],

            // row comments + status
            'details.*.comments'    => ['nullable','string','max:150'],
            'details.*.status'      => ['nullable','string','max:20'],
            'details.*.status_date' => ['nullable','date'],
        ]);
    }

    private function saveRows(string $participantId, array $rows): void
    {
        // Cache master lookups (fast)
        $dosageMap = DB::table('dosagemaster')->pluck('dosage_nm', 'dosage_id')->toArray();
        $freqMap   = DB::table('dosagefreqmaster')->pluck('dosagefreq_nm', 'dosagefreq_id')->toArray();

        $physMap = DB::table('physicianmaster')
            ->select('doctor_id', 'lastname', 'firstname')
            ->get()
            ->mapWithKeys(fn($p) => [
                $p->doctor_id => trim(($p->lastname ?? '') . ', ' . ($p->firstname ?? ''))
            ])
            ->toArray();

        $line = 1;

        foreach ($rows as $r) {
            $medicine = trim($r['medicine_nm'] ?? '');
            $doctorId = trim($r['doctor_id'] ?? '');

            $rowComments = trim($r['comments'] ?? '');
            $status      = trim($r['status'] ?? 'active');
            $statusDate  = $r['status_date'] ?? null;

            $dosageId    = trim($r['dosage_id'] ?? '');
            $dosageOther = trim($r['dosage_other'] ?? '');

            $freqId      = trim($r['dosagefreq_id'] ?? '');
            $freqOther   = trim($r['freq_other'] ?? '');

            // final text: manual overrides dropdown
            $dosageText = $dosageOther !== ''
                ? $dosageOther
                : ($dosageId !== '' ? trim($dosageMap[$dosageId] ?? '') : '');

            $freqText = $freqOther !== ''
                ? $freqOther
                : ($freqId !== '' ? trim($freqMap[$freqId] ?? '') : '');

            $physName = $doctorId !== '' ? ($physMap[$doctorId] ?? null) : null;

            // skip fully blank rows
            if ($medicine === '' && $doctorId === '' && $dosageText === '' && $freqText === '' && $rowComments === '') {
                continue;
            }

            DB::table('daycare_member_meds')->insert([
                'participant_id'  => $participantId,
                'line_no'         => $line++,
                'medicine_nm'     => $medicine ?: null,
                'doctor_id'       => $doctorId ?: null,
                'physician_name'  => $physName,
                'dosage_nm'       => $dosageText ?: null,
                'dosage_freq'     => $freqText ?: null,
                'comments'        => $rowComments ?: null,
                'status'          => $status !== '' ? $status : 'active',
                'status_date'     => $statusDate ?: null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
