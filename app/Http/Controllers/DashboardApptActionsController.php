<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardApptActionsController extends Controller
{
    public function store(Request $req)
    {
        $v = $req->validate([
            'participantid' => 'required|string|max:4',
            'appdate'       => 'required|string', // 'm/d/Y H:i'
            'appwith'       => 'nullable|string|max:4',
            'reason'        => 'nullable|string|max:25',
        ]);

        $appdate = $v['appdate'];
    $dt = null;

    // try strict formats first
    foreach (['m/d/Y H:i', 'm/d/Y h:i A'] as $fmt) {
        try {
            $dt = \Carbon\Carbon::createFromFormat($fmt, $appdate);
            break;
        } catch (\Exception $e) {}
    }
    // fallback: flexible parser (ISO, etc.)
    if (!$dt) {
        try { $dt = \Carbon\Carbon::parse($appdate); }
        catch (\Exception $e) {
            return response()->json(['ok'=>false,'error'=>'Invalid date format'], 422);
        }
    }

        DB::table('appointment')->insert([
            'participantid' => $v['participantid'],
            'appdate'       => $dt,
            'appwith'       => $v['appwith'] ?? null,
            'reason'        => $v['reason'] ?? null,
            'trans_flag'    => 'A',
            'update_flag'   => 'Y',
            'update_dt'     => now(),
            'user_cd'       => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
            'company_id'    => session('company_id','0000'),
            'updated_by'        => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
            'updated_dt'        => now(),
            'last_updated_by'   => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
            'last_updated_dt'   => now(),
        ]);

        return response()->json(['ok'=>true]);
    }

    public function update(Request $req)
    {
        $v = $req->validate([
            // Original keys from the event you clicked/dragged
            'original_participantid' => 'required|string|max:4',
            'original_appdate'       => 'required|date_format:Y-m-d H:i:s',
            // New values
            'participantid'          => 'required|string|max:4',
            'appdate'                => 'required|string', // 'm/d/Y H:i' from modal OR ISO when drag
            'appwith'                => 'nullable|string|max:4',
            'reason'                 => 'nullable|string|max:25',
            'mode'                   => 'nullable|in:modal,drag',
        ]);

        $newDt = ($v['mode'] ?? '') === 'drag'
            ? Carbon::parse($v['appdate'])                    // ISO from FullCalendar
            : Carbon::createFromFormat('m/d/Y H:i', $v['appdate']);

        DB::table('appointment')
          ->where('participantid', $v['original_participantid'])
          ->where('appdate', $v['original_appdate'])
          ->update([
              'participantid'   => $v['participantid'],
              'appdate'         => $newDt,
              'appwith'         => $v['appwith'] ?? null,
              'reason'          => $v['reason'] ?? null,
              'update_flag'     => 'Y',
              'update_dt'       => now(),
              'last_updated_by' => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
              'last_updated_dt' => now(),
          ]);

        return response()->json(['ok'=>true]);
    }

    public function cancel(Request $req)
    {
        $v = $req->validate([
            'participantid' => 'required|string|max:4',
            'appdate'       => 'required|date_format:Y-m-d H:i:s', // ISO
        ]);

        DB::table('appointment')
          ->where('participantid', $v['participantid'])
          ->where('appdate', $v['appdate'])
          ->update([
              'trans_flag'      => 'D',
              'update_flag'     => 'Y',
              'update_dt'       => now(),
              'last_updated_by' => '', //auth()->id() ? substr((string)auth()->id(),0,4) : '0000',
              'last_updated_dt' => now(),
          ]);

        return response()->json(['ok'=>true]);
    }
}
