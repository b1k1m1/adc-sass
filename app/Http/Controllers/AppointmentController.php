<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{

    public function index(Request $request)
    {
        $rows = DB::table('appointment as a')
            ->leftJoin('members as m', 'm.ParticipantId', '=', 'a.participantid')
            ->leftJoin('physicianmaster as d', 'd.doctor_id', '=', 'a.appwith')
            ->select(
                'a.*',
                DB::raw("CONCAT(m.ParticipantId,' | ', m.LastName, ', ', m.FirstName) as member_name"),
                DB::raw("CONCAT(d.lastname, ', ', d.firstname) as doctor_name")
            )
            ->whereIn('a.trans_flag', ['A', 'H'])
            ->orderBy('a.appdate')
            ->limit(1000)
            ->get();

        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();
        $weekStart  = now()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $weekEnd    = now()->endOfWeek(\Carbon\Carbon::SATURDAY);

        $todayCount = DB::table('appointment')
            ->whereIn('trans_flag', ['A', 'H'])
            ->whereBetween('appdate', [$todayStart, $todayEnd])
            ->count();

        $holdCount = DB::table('appointment')
            ->where('trans_flag', 'H')
            ->count();

        $weekCount = DB::table('appointment')
            ->whereIn('trans_flag', ['A', 'H'])
            ->whereBetween('appdate', [$weekStart, $weekEnd])
            ->count();

        $physicianTodayCount = DB::table('appointment')
            ->whereIn('trans_flag', ['A', 'H'])
            ->whereBetween('appdate', [$todayStart, $todayEnd])
            ->whereNotNull('appwith')
            ->distinct()
            ->count('appwith');

        $summary = [
            'today' => $todayCount,
            'hold' => $holdCount,
            'week' => $weekCount,
            'physicians_today' => $physicianTodayCount,
        ];

        $physicians = DB::table('physicianmaster')
            ->select(
                'doctor_id',
                DB::raw("CONCAT(lastname, ', ', firstname) as doctor_name")
            )
            ->where('trans_flag', 'A')
            ->orderBy('lastname')
            ->get();

        return view('doctors.doctors_appointment_index', compact('rows', 'summary', 'physicians'));

    } // End Method

    public function create()
    {
    $members = DB::table('members')
             ->select('ParticipantId','LastName','FirstName')
             ->where('Trans_flag', 'A')
             ->orderBy('ParticipantId')
             ->get();
    $docs = DB::table('physicianmaster')
        ->where('trans_flag','A')
        ->orderBy('lastname')
        ->select(
        'doctor_id','lastname','firstname',
        'cadd1','cadd2','ccity','czip',
        'cphone1','cphone2','cfax','ccell'
         )
        ->get();
    $row = (object)[
        'participantid' => '',
        'appdate' => now()->format('m/d/Y H:i'),
        'appwith' => '',
        'reason' => '',
        'appadd1'=>'','appadd2'=>'','appcity'=>'','appzip'=>'',
        'apptel1'=>'','apptel2'=>'','appfax'=>'','appcell'=>'','appemer'=>'',
        'remarks1'=>'','remarks2'=>'',
        'trans_flag' => 'A',
        ];
    return view('doctors.doctors_appointment_form', ['mode'=>'create','row'=>$row,'members'=>$members,'docs'=>$docs]);

    } // End Method

    public function store(Request $req)
    {

    $user = Auth::user();
    $data = $req->validate([
        'participantid' => 'required|string|max:4',
        'appdate' => 'required|string', // mm/dd/yyyy HH:mm from UI
        'reason' => 'nullable|string|max:25',
        'appwith' => 'nullable|string|max:4',
        'appadd1'=>'nullable|string|max:35','appadd2'=>'nullable|string|max:35',
        'appcity'=>'nullable|string|max:10','appzip'=>'nullable|string|max:10',
        'apptel1'=>'nullable|string|max:13','apptel2'=>'nullable|string|max:13',
        'appfax'=>'nullable|string|max:13','appcell'=>'nullable|string|max:13','appemer'=>'nullable|string|max:13',
        'remarks1'=>'nullable|string|max:70','remarks2'=>'nullable|string|max:70',
        'trans_flag' => 'required|in:A,H,D',
        ]);
    $dt = Carbon::createFromFormat('m/d/Y H:i', $data['appdate']);
    $data['appdate'] = $dt; // store as datetime

    $data += [
        'user_cd' => $user->user_cd, //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
        'company_id' => session('company_id','0000'),
        'update_flag' => 'Y',
        'update_dt' => now(),
        'updated_by' => $user->user_cd, //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
        'updated_dt' => now(),
        'last_updated_by' => $user->user_cd, //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
        'last_updated_dt' => now(),
        ];
    DB::table('appointment')->insert($data);
    return redirect()->route('appt.index')->with('success','Appointment created.');

    } // End Method

    public function show(string $participantid, string $appdate)
    {
        $row = DB::table('appointment')
            ->where('participantid', $participantid)
            ->where('appdate', $appdate)
            ->first();

        abort_unless($row, 404);

        // Physician (doctor) row for this appointment
        $physician = DB::table('physicianmaster')
            ->where('doctor_id', $row->appwith)
            ->first();

        // Member row + allergies / special needs / dietary restrictions
        $member = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'AdmittingDiagnosis2',
                'PrimaryDiagnosis2',
                'SecondaryDiag2',
                'SpecialNeeds1',
                'SpecialNeeds2',
                'DiateryRestriction1',
                'DiateryRestriction2',
                'DiateryRestriction3'
            )
            ->where('ParticipantId', $participantid)
            ->first();

        // Medication summary from daycare_member_meds
        $medications = DB::table('daycare_member_meds')
            ->select(
                'medicine_nm',
                'dosage_nm',
                'dosage_freq',
                'comments',
                'status'
            )
            ->where('participant_id', $participantid)
            ->orderBy('line_no')
            ->get();

        // Build a single-line address for Google Maps
        $addrParts = [];
        if ($physician) {
            foreach (['cadd1', 'cadd2', 'ccity', 'czip'] as $k) {
                if (!empty($physician->$k)) {
                    $addrParts[] = $physician->$k;
                }
            }
        }
        $physicianFullAddress = implode(', ', $addrParts);

        // Lists you already use
        $members = DB::table('members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->get();

        $docs = DB::table('physicianmaster')->get();

        return view('doctors.doctors_appointment_form', [
            'mode'                 => 'view',
            'row'                  => $this->formatRow($row),
            'members'              => $members,
            'docs'                 => $docs,
            'member'               => $member,
            'physician'            => $physician,
            'physicianFullAddress' => $physicianFullAddress,
            'medications'          => $medications,
        ]);

    } // End Method

    private function formatRow($row){
        $iso = \Carbon\Carbon::parse($row->appdate)->format('Y-m-d H:i:s'); // safe for URL & DB WHERE
        $display = \Carbon\Carbon::parse($row->appdate)->format('m/d/Y H:i'); // for the input

        $row->appdate_iso = $iso;
        $row->appdate_display = $display;
    return $row;

    } // End Method

    public function edit(string $participantid, string $appdate)
    {
        $row = DB::table('appointment')
            ->where('participantid',$participantid)
            ->where('appdate',$appdate)
            ->first();
        abort_unless($row, 404);
        $members = DB::table('members')->select('ParticipantId','LastName','FirstName')->get();
        $docs = DB::table('physicianmaster')->orderBy('lastname')->get();

        return view('doctors.doctors_appointment_form', ['mode'=>'edit',
                                                         'row'=>$this->formatRow($row),
                                                         'members'=>$members,
                                                         'docs'=>$docs]);
    }

    public function update(Request $req, string $participantid, string $appdate)
    {

    $user = Auth::user();
    $data = $req->validate([
        'participantid' => 'required|string|max:4',
        'appdate' => 'required|string',
        'reason' => 'nullable|string|max:25',
        'appwith' => 'nullable|string|max:4',
        'appadd1'=>'nullable|string|max:35','appadd2'=>'nullable|string|max:35',
        'appcity'=>'nullable|string|max:10','appzip'=>'nullable|string|max:10',
        'apptel1'=>'nullable|string|max:13','apptel2'=>'nullable|string|max:13',
        'appfax'=>'nullable|string|max:13','appcell'=>'nullable|string|max:13','appemer'=>'nullable|string|max:13',
        'remarks1'=>'nullable|string|max:70','remarks2'=>'nullable|string|max:70',
        'trans_flag' => 'required|in:A,H,D',
        ]);
    $data['appdate'] = Carbon::createFromFormat('m/d/Y H:i', $data['appdate']);
    $data += [ 'update_flag'=>'Y',
               'user_cd' => $user->user_cd,
               'update_dt'=>now(),
               'last_updated_by'=>$user->user_cd, //>auth()->id() ? substr((string)auth()->id(),0,4) : '0000'',
               'last_updated_dt'=>now() ];

    DB::table('appointment')
        ->where('participantid',$participantid)
        ->where('appdate',$appdate)
        ->update($data);


    return redirect()->route('appt.index')->with('success','Appointment updated.');

    } // End Method


    public function smsToDriver(string $participantid, string $appdate, Request $req)
    {
        // 1. Fetch appointment info
        $row = DB::table('appointment')
            ->where('participantid', $participantid)
            ->where('appdate', $appdate)
            ->first();
            abort_unless($row, 404);

        $member = DB::table('members')
            ->select('ParticipantId','LastName','FirstName')
            ->where('ParticipantId',$participantid)
            ->first();

        $physician = DB::table('physicianmaster')
            ->where('doctor_id', $row->appwith)
            ->first();

        // 2. Build address + maps link
        $addrParts = [];
        if ($physician) {
            foreach (['cadd1','cadd2','ccity','czip'] as $k) {
                if (!empty($physician->$k)) $addrParts[] = $physician->$k;
            }
        }
        $address = implode(', ', $addrParts);
        $mapsUrl = $address ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address) : '';

        // 3. Message body
        $memberName = ($member?->FirstName ? "{$member->FirstName} {$member->LastName}" : $row->participantid);
        $docName = $physician ? "Dr. {$physician->lastname}, {$physician->firstname}" : "Physician";
        $apptTime = Carbon::parse($row->appdate)->format('m/d/Y h:i A');

        $company = config('company.comp_nm');

        $msg ="{$company}\n"
            . "Appt: {$memberName}\n"
            . "{$apptTime}\n"
            . "{$docName}\n"
            . ($address ? "{$address}\n" : '');

        // Only append Google Map link if user checked the box
        if ($req->boolean('include_map') && $mapsUrl) {
            $msg .= $mapsUrl;
        }

        // 4. Validate driver phone (convert to E.164)
        $driverNumber = preg_replace('/\D/', '', $req->input('driver_phone', ''));
        if (!$driverNumber) {
                return back()->with('error', 'Driver phone number required.');
            }
        if (strlen($driverNumber) === 10) $driverNumber = '+1' . $driverNumber;
        elseif (!str_starts_with($driverNumber, '+')) $driverNumber = '+' . $driverNumber;

        // 5️.  Prepare Infobip API call
        $client = new Client([
            'base_uri' => rtrim(config('services.infobip.base_url'), '/') . '/',
            'timeout'  => 10,
        ]);

        $payload = [
        'messages' => [[
            'from' => config('services.infobip.sender'),
            'destinations' => [['to' => $driverNumber]],
            'text' => $msg,
            ]],
         ];

        try {
                $response = $client->post('sms/2/text/advanced', [
                    'headers' => [
                        'Authorization' => 'App ' . config('services.infobip.api_key'),
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'json' => $payload,
                ]);

                $body = json_decode($response->getBody()->getContents(), true);
                $status = $body['messages'][0]['status']['name'] ?? 'UNKNOWN';
                $messageId = $body['messages'][0]['messageId'] ?? null;

            // Optional: Log message status
                Log::info('Infobip SMS sent', [
                    'to' => $driverNumber,
                    'status' => $status,
                    'messageId' => $messageId,
                ]);

                return back()->with('success', "Message sent to driver ({$driverNumber}) — Status: {$status}");
            } catch (\Throwable $e) {
                Log::error('Infobip SMS failed', ['error' => $e->getMessage()]);

                return back()->with('error', 'Failed to send SMS: ' . $e->getMessage());
            }

    } // End Method */

    public function emailToDriver(string $participantid, string $appdate, Request $req)
        {
            // 1) Get appointment, member, and physician
            $row = DB::table('appointment')
                ->where('participantid', $participantid)
                ->where('appdate', $appdate)
                ->first();
            abort_unless($row, 404);

            $member = DB::table('members')
                ->select('ParticipantId','LastName','FirstName')
                ->where('ParticipantId',$participantid)
                ->first();

            $physician = DB::table('physicianmaster')
                ->where('doctor_id', $row->appwith)
                ->first();

            // 2) Driver email (required)
            $driverEmail = $req->input('driver_email');
            if (!$driverEmail) {
                return back()->with('error', 'Driver email is required.');
            }

            // 3) Build address and message
            $addrParts = [];
            if ($physician) {
                foreach (['cadd1','cadd2','ccity','czip'] as $k) {
                    if (!empty($physician->$k)) $addrParts[] = $physician->$k;
                }
            } else {
                foreach (['appadd1','appadd2','appcity','appzip'] as $k) {
                    if (!empty($row->$k)) $addrParts[] = $row->$k;
                }
            }
            $address = implode(', ', $addrParts);
            $mapsUrl = $address ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address) : '';

            $memberName = ($member?->FirstName ? "{$member->FirstName} {$member->LastName}" : $row->participantid);
            $docName = $physician ? "Dr. {$physician->lastname}, {$physician->firstname}" : "Physician";
            $apptTime = Carbon::parse($row->appdate)->format('m/d/Y h:i A');

            $subject = "Appointment for {$memberName} on {$apptTime}";
            $body = <<<EOT
                Hello Driver,

                Please find the appointment details below:

                Member: {$memberName}
                Date & Time: {$apptTime}
                Physician: {$docName}
                Address: {$address}

                Google Map: {$mapsUrl}

                Regards,
                Appointment Desk
                EOT;

            // 4) Send the email
            try {
                Mail::raw($body, function ($mail) use ($driverEmail, $subject) {
                    $mail->to($driverEmail)
                        ->subject($subject);
                });

                return back()->with('success', "Appointment details emailed to driver ({$driverEmail}).");
            } catch (\Throwable $e) {
                return back()->with('error', 'Email failed: ' . $e->getMessage());
            }

        } // End Method

    public function calendarEvents(Request $request)
    {
        $start      = $request->get('start');
        $end        = $request->get('end');
        $status     = $request->get('status', 'active_hold');
        $doctorId   = $request->get('doctor_id');
        $rangeStart = $request->get('range_start');
        $rangeEnd   = $request->get('range_end');

        $query = DB::table('appointment as a')
            ->leftJoin('members as m', 'm.ParticipantId', '=', 'a.participantid')
            ->leftJoin('physicianmaster as d', 'd.doctor_id', '=', 'a.appwith')
            ->select(
                'a.*',
                'm.ParticipantId as mem_pid',
                'm.LastName as mem_lname',
                'm.FirstName as mem_fname',
                'd.lastname as doc_lname',
                'd.firstname as doc_fname'
            );

        if (!empty($rangeStart) && !empty($rangeEnd)) {
            $query->whereBetween('a.appdate', [
                \Carbon\Carbon::parse($rangeStart)->startOfDay(),
                \Carbon\Carbon::parse($rangeEnd)->endOfDay()
            ]);
        } elseif ($start && $end) {
            $query->whereBetween('a.appdate', [$start, $end]);
        }

        if (!empty($doctorId)) {
            $query->where('a.appwith', $doctorId);
        }

        if ($status === 'all') {
            // no status filter
        } elseif ($status === 'deleted') {
            $query->where('a.trans_flag', 'D');
        } else {
            $query->whereIn('a.trans_flag', ['A', 'H']);
        }

        $rows = $query->orderBy('a.appdate')->get();

        $events = $rows->map(function ($row) {
            $memberName = trim(($row->mem_lname ?? '') . ', ' . ($row->mem_fname ?? ''));
            $doctorName = trim(($row->doc_lname ?? '') . ', ' . ($row->doc_fname ?? ''));

            $classNames = match ($row->trans_flag) {
                'A' => ['fc-appt-active'],
                'H' => ['fc-appt-hold'],
                'D' => ['fc-appt-deleted'],
                default => ['fc-appt-default'],
            };

            $start = \Carbon\Carbon::parse($row->appdate);
            $end   = (clone $start)->addMinutes(60);

            return [
                'id' => $row->participantid . '|' . $start->format('Y-m-d H:i:s'),
                'title' => $memberName,
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end'   => $end->format('Y-m-d\TH:i:s'),
                'classNames' => $classNames,
                'extendedProps' => [
                    'participantid' => $row->participantid,
                    'appdate'       => $start->format('Y-m-d H:i:s'),
                    'appwith'       => $row->appwith,
                    'member_name'   => $memberName,
                    'doctor_name'   => $doctorName,
                    'reason'        => $row->reason,
                    'status'        => $row->trans_flag,
                    'time_label'    => $start->format('h:i A'),
                ],
            ];
        });

        return response()->json($events);

    } // End Method

    public function calendarDetails(Request $request)
    {
        $participantid = $request->participantid;
        $appdate = $request->appdate;

        $row = DB::table('appointment as a')
            ->leftJoin('members as m', 'm.ParticipantId', '=', 'a.participantid')
            ->leftJoin('physicianmaster as d', 'd.doctor_id', '=', 'a.appwith')
            ->select(
                'a.*',
                DB::raw("CONCAT(m.ParticipantId,' | ', m.LastName, ', ', m.FirstName) as member_name"),
                DB::raw("CONCAT(d.lastname, ', ', d.firstname) as doctor_name")
            )
            ->where('a.participantid', $participantid)
            ->where('a.appdate', $appdate)
            ->first();

        abort_unless($row, 404);

        return response()->json([
            'ok' => true,
            'row' => [
                'participantid' => $row->participantid,
                'member_name'   => $row->member_name,
                'doctor_name'   => $row->doctor_name,
                'appdate'       => \Carbon\Carbon::parse($row->appdate)->format('m/d/Y h:i A'),
                'appdate_iso'   => \Carbon\Carbon::parse($row->appdate)->format('Y-m-d H:i:s'),
                'reason'        => $row->reason,
                'status'        => $row->trans_flag,
                'view_url'      => route('appt.show', [$row->participantid, \Carbon\Carbon::parse($row->appdate)->format('Y-m-d H:i:s')]),
                'edit_url'      => route('appt.edit', [$row->participantid, \Carbon\Carbon::parse($row->appdate)->format('Y-m-d H:i:s')]),
            ],
        ]);

    } // End Method

    public function calendarMove(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'participantid' => 'required|string|max:4',
            'old_appdate'   => 'required|date',
            'new_appdate'   => 'required|date',
        ]);

        $updated = DB::table('appointment')
            ->where('participantid', $data['participantid'])
            ->where('appdate', $data['old_appdate'])
            ->update([
                'appdate' => $data['new_appdate'],
                'update_flag' => 'Y',
                'updated_by' => $user->user_cd,
                'updated_dt' => now(),
                'last_updated_by' => $user->user_cd,
                'last_updated_dt' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'ok' => false,
                'message' => 'Appointment not found or update failed.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Appointment rescheduled successfully.',
        ]);

    } // End Method

    public function calendarStatus(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'participantid' => 'required|string|max:4',
            'appdate'       => 'required|date',
            'trans_flag'    => 'required|in:A,H,D',
        ]);

        $updated = DB::table('appointment')
            ->where('participantid', $data['participantid'])
            ->where('appdate', $data['appdate'])
            ->update([
                'trans_flag' => $data['trans_flag'],
                'update_flag' => 'Y',
                'updated_by' => $user->user_cd,
                'updated_dt' => now(),
                'last_updated_by' => $user->user_cd,
                'last_updated_dt' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'ok' => false,
                'message' => 'Unable to update appointment status.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Appointment status updated successfully.',
        ]);

    } // End Method

    public function tableData(Request $request)
    {
        $status     = $request->get('status', 'active_hold');
        $doctorId   = $request->get('doctor_id');
        $rangeStart = $request->get('range_start');
        $rangeEnd   = $request->get('range_end');

        $query = DB::table('appointment as a')
            ->leftJoin('members as m', 'm.ParticipantId', '=', 'a.participantid')
            ->leftJoin('physicianmaster as d', 'd.doctor_id', '=', 'a.appwith')
            ->select(
                'a.participantid',
                'a.appdate',
                'a.appwith',
                'a.reason',
                'a.trans_flag',
                DB::raw("CONCAT(m.ParticipantId,' | ', m.LastName, ', ', m.FirstName) as member_name"),
                DB::raw("CONCAT(d.lastname, ', ', d.firstname) as doctor_name")
            );

        if (!empty($rangeStart) && !empty($rangeEnd)) {
            $query->whereBetween('a.appdate', [
                \Carbon\Carbon::parse($rangeStart)->startOfDay(),
                \Carbon\Carbon::parse($rangeEnd)->endOfDay()
            ]);
        }

        if (!empty($doctorId)) {
            $query->where('a.appwith', $doctorId);
        }

        if ($status === 'all') {
            // no filter
        } elseif ($status === 'deleted') {
            $query->where('a.trans_flag', 'D');
        } else {
            $query->whereIn('a.trans_flag', ['A', 'H']);
        }

        $rows = $query->orderBy('a.appdate')->get()->map(function ($r) {
            return [
                'participantid'    => $r->participantid,
                'appdate'          => \Carbon\Carbon::parse($r->appdate)->format('Y-m-d H:i:s'),
                'appdate_display'  => \Carbon\Carbon::parse($r->appdate)->format('m/d/Y H:i'),
                'doctor_name'      => $r->doctor_name,
                'member_name'      => $r->member_name,
                'reason'           => $r->reason,
                'trans_flag'       => $r->trans_flag,
                'view_url'         => route('appt.show', [$r->participantid, \Carbon\Carbon::parse($r->appdate)->format('Y-m-d H:i:s')]),
                'edit_url'         => route('appt.edit', [$r->participantid, \Carbon\Carbon::parse($r->appdate)->format('Y-m-d H:i:s')]),
            ];
        });

        return response()->json(['data' => $rows]);

    } // End Method

    public function summaryData(Request $request)
    {
        $status     = $request->get('status', 'active_hold');
        $doctorId   = $request->get('doctor_id');
        $rangeStart = $request->get('range_start');
        $rangeEnd   = $request->get('range_end');

        $makeQuery = function () use ($status, $doctorId, $rangeStart, $rangeEnd) {
            $query = DB::table('appointment');

            if (!empty($rangeStart) && !empty($rangeEnd)) {
                $query->whereBetween('appdate', [
                    \Carbon\Carbon::parse($rangeStart)->startOfDay(),
                    \Carbon\Carbon::parse($rangeEnd)->endOfDay()
                ]);
            }

            if (!empty($doctorId)) {
                $query->where('appwith', $doctorId);
            }

            if ($status === 'all') {
                // no status filter
            } elseif ($status === 'deleted') {
                $query->where('trans_flag', 'D');
            } else {
                $query->whereIn('trans_flag', ['A', 'H']);
            }

            return $query;
        };

        $appointmentsInRange = $makeQuery()->count();

        $holdCount = $makeQuery()
            ->where('trans_flag', 'H')
            ->count();

        $activeCount = $makeQuery()
            ->where('trans_flag', 'A')
            ->count();

        $physicianCount = $makeQuery()
            ->whereNotNull('appwith')
            ->distinct()
            ->count('appwith');

        return response()->json([
            'appointments_in_range' => $appointmentsInRange,
            'hold' => $holdCount,
            'active' => $activeCount,
            'physicians' => $physicianCount,
        ]);

    } // End Method
}
