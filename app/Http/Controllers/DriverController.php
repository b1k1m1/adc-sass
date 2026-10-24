<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class DriverController extends Controller
{
    public function DriversDashboard(){

        return view('drivers.drivers_index', ['title' => 'Driver Management','module' => 'members' ]);

    } // End Method

    public function selectDriver(){

         $drivers = DB::table('employees')
            ->select('staffid', 'stafflastname', 'stafffirstname')
            ->where('staffcategoryid', 'DRV')
            ->orderBy('stafflastname')
            ->orderBy('stafffirstname')
            ->get();

        return view('drivers.drivers_pickup_select', compact('drivers'));

    } // End Method

    public function driverPickupReport(Request $request){

        $staffId = $request->input('staffid');
        $serviceDate = $request->input('date', now()->toDateString());

        // Fetch driver info
        $driver = DB::table('employees')
            ->select('staffid', 'stafflastname', 'stafffirstname')
            ->where('staffid', $staffId)
            ->first();

        // Fetch members assigned to this driver
        $rows = DB::table('members')
            ->select(
                'Participantid as id',
                'Lastname as last',
                'Firstname as first',
                'Address1 as street',
                'City as city',
                'Phone1 as tele'
            )
            ->where('driverid', $staffId)
            ->where('trans_flag', 'A')
            ->orderBy('Lastname')
            ->orderBy('Firstname')
            ->get();

             return view('drivers.drivers_pickup', [
            'compname'    => config('company.comp_nm'),
            'title'       => 'Driver Schedule Pickup',
            'serviceDate' => $serviceDate,
            'rows'        => $rows,
            'driver'      => $driver,
            'pdfUrl'      => route('drivers_pickup.pdf', ['driverid' => $staffId, 'date' => $serviceDate]),
            'forPdf'      => false,
        ]);


    } // End Method

    public function driverPickupReportPdf(Request $request){

        $staffId = $request->input('driverid');

        $serviceDate = $request->input('date', now()->toDateString());

        // Fetch driver info
        $driver = DB::table('employees')
            ->select('staffid', 'stafflastname', 'stafffirstname')
            ->where('staffid', $staffId)
            ->first();

        // Fetch members assigned to this driver
        $rows = DB::table('members')
            ->select(
                'Participantid as id',
                'Lastname as last',
                'Firstname as first',
                'Address1 as street',
                'City as city',
                'Phone1 as tele'
            )
            ->where('driverid', $staffId)
            ->where('trans_flag', 'A')
            ->orderBy('Lastname')
            ->orderBy('Firstname')
            ->get();

            $html = view('drivers.drivers_pickup_pdf', [
            'compname'    => config('company.comp_nm'),
            'title'       => 'Driver Schedule Pickup',
            'serviceDate' => $serviceDate,
            'rows'        => $rows,
            'driver'      => $driver,

        ])->render();

         $mpdf = new Mpdf([
            'tempDir'=>storage_path('app/mpdf'),
            'format' => 'A4-L',
            'margin_top'    => 35,
            'margin_bottom' => 20,
            'margin_left'   => 10,
            'margin_right'  => 10,
        ]);

        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;
        $mpdf->shrink_tables_to_fit = 1;


        $driverName = $driver->stafflastname . ', ' . $driver->stafffirstname;
        // Header

        $mpdf->SetHTMLHeader('
            <table width="100%" style="border-bottom:1px solid #000; font-family: sans-serif; font-size:12px;">
                <tr>
                    <td width="33%" style="text-align:left;">
                        <strong>Driver:</strong> '.$driverName.'
                    </td>
                    <td width="34%" style="text-align:center; font-size:14px; font-weight:bold;">
                        '.config('company.comp_nm').'<br>
                        Driver Schedule Pickup
                    </td>
                     <td width="33%" style="text-align:right;">
        <table align="right" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
          <tr>
            <td style="padding-right:15px;"><strong>Date:_________________</strong></td>
            <td style="width:220px; border-top:2px solid #000;">&nbsp;</td>
          </tr>
        </table>
      </td>
                </tr>
            </table>
        ');


        // Footer
        $mpdf->SetHTMLFooter('
            <div style="text-align:right; font-size:10px;">
                Page {PAGENO} of {nbpg}
            </div>
        ');

        $mpdf->WriteHTML($html);

        //return $mpdf->Output('driver_pickup_'.$staffId.'_'.now()->format('Ymd_His').'.pdf', 'I');

        //return response($mpdf->Output('driver_pickup.pdf', 'S'))
        //    ->header('Content-Type', 'application/pdf');


        return $mpdf->Output('driver_pickup_'.$staffId.'.pdf', \Mpdf\Output\Destination::INLINE);

    } // End  Method

    public function RouteList()
    {
        $routes = DB::table('route')
            ->orderBy('Route_id')
            ->get();

        return view('drivers.route_list', compact('routes'), [
            'title'  => 'Driver Management',
            'module' => 'driver',
        ]);

    } // End Method

    public function AddRoute()
    {
        /*
        |--------------------------------------------------------------------------
        | Generate Next Route ID
        |--------------------------------------------------------------------------
        | Existing sequence:
        | R01, R02, R03 ... R99
        */

        $lastRoute = DB::table('route')
            ->whereRaw("Route_id REGEXP '^R[0-9]{2}$'")
            ->orderByRaw("CAST(SUBSTRING(Route_id, 2) AS UNSIGNED) DESC")
            ->first();

        $lastNumber = $lastRoute
            ? (int) substr($lastRoute->Route_id, 1)
            : 0;

        $nextNumber = $lastNumber + 1;

        if ($nextNumber > 99) {
            return redirect()
                ->route('route.list')
                ->with([
                    'message'    => 'Maximum Route ID R99 has already been reached.',
                    'alert-type' => 'error',
                ]);
        }

        $nextRouteId = 'R' . str_pad(
            $nextNumber,
            2,
            '0',
            STR_PAD_LEFT
        );

        return view('drivers.route_add', compact('nextRouteId'), [
            'title'  => 'Driver Management',
            'module' => 'driver',
        ]);

    } // End Method

    public function StoreRoute(Request $request)
    {
        $request->validate([
        'Route_name' => ['required','string', 'max:30', ],
        'shift_code' => ['required', Rule::in(['A', 'N']), ],
        'trans_flag' => ['required', Rule::in(['A', 'H']), ],
        ], [
        'Route_name.required' => 'Route name is required.',
        'Route_name.max'      => 'Route name cannot exceed 30 characters.',
        'shift_code.required' => 'Please select the shift.',
        'shift_code.in'       => 'The selected shift is invalid.',
        'trans_flag.required' => 'Route status is required.',
    ]);

       $user = Auth::user();

        try {
            DB::transaction(function () use ($request, $user) {

                /*
                |--------------------------------------------------------------------------
                | Recalculate Route ID During Save
                |--------------------------------------------------------------------------
                | We calculate it again here instead of trusting the value displayed
                | in the Blade file. This helps avoid duplicate IDs if two users open
                | the Add Route screen at approximately the same time.
                */

                $lastRoute = DB::table('route')
                    ->whereRaw("Route_id REGEXP '^R[0-9]{2}$'")
                    ->orderByRaw("CAST(SUBSTRING(Route_id, 2) AS UNSIGNED) DESC")
                    ->lockForUpdate()
                    ->first();

                $lastNumber = $lastRoute
                    ? (int) substr($lastRoute->Route_id, 1)
                    : 0;

                $nextNumber = $lastNumber + 1;

                if ($nextNumber > 99) {
                    throw new \RuntimeException(
                        'Maximum Route ID R99 has already been reached.'
                    );
                }

                $nextRouteId = 'R' . str_pad( $nextNumber, 2, '0', STR_PAD_LEFT );

                DB::table('route')->insert([
                    'Route_id'    => $nextRouteId,
                    'Route_name'  => strtoupper(trim($request->Route_name)),
                    'shift_code'  => $request->shift_code,
                    'trans_flag'  => $request->trans_flag,
                    'user_cd'     => $user->user_cd
                        ?? $user->staffid
                        ?? null,
                    'update_flag' => 'Y',
                    'update_dt'   => now(),
                    'company_id'  => 'DS02',
                ]);

            });

            return redirect()
                ->route('route.list')
                ->with([
                    'message'    => 'Route created successfully.',
                    'alert-type' => 'success',
                ]);

        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with([
                    'message'    => $e->getMessage(),
                    'alert-type' => 'error',
                ]);
        }

    } // End Method

    public function EditRoute($Route_id)
    {
        $route = DB::table('route')
            ->where('Route_id', $Route_id)
            ->first();

        if (!$route) {
            return redirect()
                ->route('route.list')
                ->with([
                    'message'    => 'Route record not found.',
                    'alert-type' => 'error',
                ]);
        }

        return view('drivers.route_edit', compact('route'), [
            'title'  => 'Driver Management',
            'module' => 'driver',
        ]);

    } // End Method


    public function UpdateRoute(Request $request, $Route_id)
    {
        $route = DB::table('route')
            ->where('Route_id', $Route_id)
            ->first();

        if (!$route) {
            return redirect()
                ->route('route.list')
                ->with([
                    'message'    => 'Route record not found.',
                    'alert-type' => 'error',
                ]);
        }

        $request->validate([
            'Route_name' => [
                'required',
                'string',
                'max:30',
            ],

            'shift_code' => [
                'required',
                Rule::in(['A', 'N']),
            ],

            'trans_flag' => [
                'required',
                Rule::in(['A', 'H']),
            ],
        ]);

        $user = Auth::user();

        DB::table('route')
            ->where('Route_id', $Route_id)
            ->update([
                'Route_name'  => strtoupper(trim($request->Route_name)),
                'shift_code'  => $request->shift_code,
                'trans_flag'  => $request->trans_flag,
                'user_cd'     => $user->user_cd
                    ?? $user->staffid
                    ?? null,
                'update_flag' => 'Y',
                'update_dt'   => now(),
            ]);

        return redirect()
            ->route('route.list')
            ->with([
                'message'    => 'Route updated successfully.',
                'alert-type' => 'success',
            ]);

    } // End Method


    public function ChangeRouteStatus($Route_id)
    {
        $route = DB::table('route')
            ->where('Route_id', $Route_id)
            ->first();

        if (!$route) {
            return redirect()
                ->route('route.list')
                ->with([
                    'message'    => 'Route record not found.',
                    'alert-type' => 'error',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Toggle Route Status
        |--------------------------------------------------------------------------
        | A = Active
        | H = Hold / Inactive
        */
        $newStatus = $route->trans_flag === 'A' ? 'H' : 'A';

        $user = Auth::user();

        DB::table('route')
            ->where('Route_id', $Route_id)
            ->update([
                'trans_flag'  => $newStatus,
                'user_cd'     => $user->user_cd ?? $user->staffid ?? null,
                'update_flag' => 'U',
                'update_dt'   => now(),
            ]);

        $statusText = $newStatus === 'A' ? 'activated' : 'placed on hold';

        return redirect()
            ->route('route.list')
            ->with([
                'message'    => "Route successfully {$statusText}.",
                'alert-type' => 'success',
            ]);

    } // End Method

    /**
     * Display the Daily Temporary Route Assignment screen.
     */
    public function DailyRouteAssignment(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Selected Date
        |--------------------------------------------------------------------------
        */
        try {
            $selectedDate = $request->filled('assignment_date')
                ? Carbon::parse($request->assignment_date)->format('Y-m-d')
                : now()->format('Y-m-d');
        } catch (\Throwable $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Active Routes
        |--------------------------------------------------------------------------
        */
        $routes = DB::table('route')
            ->select(
                'Route_id',
                'Route_name',
                'shift_code',
                'trans_flag'
            )
            ->where('trans_flag', 'A')
            ->whereIn('shift_code', ['A', 'N'])
            ->orderBy('shift_code')
            ->orderByRaw(
                "CAST(SUBSTRING(Route_id, 2) AS UNSIGNED) ASC"
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Temporary Assignments for Selected Date
        |--------------------------------------------------------------------------
        | No joins are used.
        */
        $assignments = DB::table('member_route_assignments')
            ->whereDate('assignment_date', $selectedDate)
            ->orderBy('assigned_route_id')
            ->orderBy('ParticipantId')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load Member Details Separately
        |--------------------------------------------------------------------------
        */
        $participantIds = $assignments
            ->pluck('ParticipantId')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $membersById = collect();

        if (!empty($participantIds)) {
            $membersById = DB::table('members')
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName',
                    'MiddleName',
                    'Trans_flag',
                    'Route'
                )
                ->whereIn('ParticipantId', $participantIds)
                ->get()
                ->keyBy(function ($member) {
                    return trim((string) $member->ParticipantId);
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Load Route Names Separately
        |--------------------------------------------------------------------------
        */
        $routeIds = $assignments
            ->pluck('original_route_id')
            ->merge($assignments->pluck('assigned_route_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $routesById = collect();

        if (!empty($routeIds)) {
            $routesById = DB::table('route')
                ->select(
                    'Route_id',
                    'Route_name',
                    'shift_code'
                )
                ->whereIn('Route_id', $routeIds)
                ->get()
                ->keyBy(function ($route) {
                    return trim((string) $route->Route_id);
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Attach Member and Route Information
        |--------------------------------------------------------------------------
        */
        $assignments = $assignments->map(function ($assignment) use (
            $membersById,
            $routesById
        ) {
            $participantId = trim(
                (string) $assignment->ParticipantId
            );

            $originalRouteId = trim(
                (string) $assignment->original_route_id
            );

            $assignedRouteId = trim(
                (string) $assignment->assigned_route_id
            );

            $member = $membersById->get($participantId);

            $originalRoute = $routesById->get($originalRouteId);
            $assignedRoute = $routesById->get($assignedRouteId);

            $assignment->LastName = $member->LastName ?? '';
            $assignment->FirstName = $member->FirstName ?? '';
            $assignment->MiddleName = $member->MiddleName ?? '';
            $assignment->member_shift = $member->Trans_flag ?? '';

            $assignment->original_route_name =
                $originalRoute->Route_name ?? '';

            $assignment->assigned_route_name =
                $assignedRoute->Route_name ?? '';

            return $assignment;
        });

        /*
        |--------------------------------------------------------------------------
        | Sort Final Collection
        |--------------------------------------------------------------------------
        */
        $assignments = $assignments
            ->sortBy(function ($assignment) {
                return sprintf(
                    '%s-%s-%s-%s',
                    $assignment->member_shift ?? '',
                    $assignment->assigned_route_id ?? '',
                    $assignment->LastName ?? '',
                    $assignment->FirstName ?? ''
                );
            })
            ->values();

        return view(
            'drivers.daily_route_assignment',
            compact(
                'routes',
                'assignments',
                'selectedDate'
            ),
            [
                'title'  => 'Driver Management',
                'module' => 'driver',
            ]
        );

    } // End Method


    /**
     * Load members belonging to the selected shift and permanent route.
     *
     * This method is intended to be called through AJAX.
     */
    public function GetRouteMembers(Request $request)
    {
        $validated = $request->validate([
            'assignment_date' => [
                'required',
                'date',
            ],

            'shift' => [
                'required',
                Rule::in(['A', 'N']),
            ],

            'route_id' => [
                'required',
                'string',
                'max:3',
                Rule::exists('route', 'Route_id'),
            ],
        ]);

        $assignmentDate = Carbon::parse(
            $validated['assignment_date']
        )->format('Y-m-d');

        $shift = strtoupper(
            trim($validated['shift'])
        );

        $routeId = strtoupper(
            trim($validated['route_id'])
        );

        /*
        |--------------------------------------------------------------------------
        | Verify Selected Route
        |--------------------------------------------------------------------------
        */
        $route = DB::table('route')
            ->select(
                'Route_id',
                'Route_name',
                'shift_code',
                'trans_flag'
            )
            ->where('Route_id', $routeId)
            ->where('shift_code', $shift)
            ->where('trans_flag', 'A')
            ->first();

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The selected route is not an active route for the selected shift.',
                'members' => [],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Members from Permanent Route
        |--------------------------------------------------------------------------
        | No joins are used.
        */
        $members = DB::table('members')
            ->select(
                'ParticipantId',
                'LastName',
                'FirstName',
                'MiddleName',
                'Route',
                'Trans_flag'
            )
            ->where('Trans_flag', $shift)
            ->where('Route', $routeId)
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        if ($members->isEmpty()) {
            return response()->json([
                'success' => true,

                'route' => [
                    'Route_id'   => $route->Route_id,
                    'Route_name' => $route->Route_name,
                    'shift_code' => $route->shift_code,
                ],

                'members' => [],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Existing Assignments Separately
        |--------------------------------------------------------------------------
        */
        $participantIds = $members
            ->pluck('ParticipantId')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $existingAssignments = DB::table(
            'member_route_assignments'
        )
            ->whereDate('assignment_date', $assignmentDate)
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(function ($assignment) {
                return trim((string) $assignment->ParticipantId);
            });

        /*
        |--------------------------------------------------------------------------
        | Load Temporary Route Names Separately
        |--------------------------------------------------------------------------
        */
        $assignedRouteIds = $existingAssignments
            ->pluck('assigned_route_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $assignedRoutesById = collect();

        if (!empty($assignedRouteIds)) {
            $assignedRoutesById = DB::table('route')
                ->select(
                    'Route_id',
                    'Route_name'
                )
                ->whereIn('Route_id', $assignedRouteIds)
                ->get()
                ->keyBy(function ($assignedRoute) {
                    return trim(
                        (string) $assignedRoute->Route_id
                    );
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Attach Existing Temporary Assignment Information
        |--------------------------------------------------------------------------
        */
        $members = $members->map(function ($member) use (
            $existingAssignments,
            $assignedRoutesById
        ) {
            $participantId = trim(
                (string) $member->ParticipantId
            );

            $assignment = $existingAssignments->get(
                $participantId
            );

            $assignedRouteId = $assignment
                ? trim((string) $assignment->assigned_route_id)
                : null;

            $assignedRoute = $assignedRouteId
                ? $assignedRoutesById->get($assignedRouteId)
                : null;

            return [
                'ParticipantId' => $member->ParticipantId,
                'LastName'      => $member->LastName,
                'FirstName'     => $member->FirstName,
                'MiddleName'    => $member->MiddleName,

                'permanent_route' => $member->Route,
                'shift'           => $member->Trans_flag,

                'assignment_id' => $assignment->id ?? null,

                'original_route_id' =>
                    $assignment->original_route_id ?? null,

                'assigned_route_id' =>
                    $assignment->assigned_route_id ?? null,

                'reason' =>
                    $assignment->reason ?? null,

                'assigned_route_name' =>
                    $assignedRoute->Route_name ?? null,

                'already_assigned' =>
                    $assignment ? 1 : 0,

                'effective_route' =>
                    $assignment->assigned_route_id
                    ?? $member->Route,
            ];
        })->values();

        return response()->json([
            'success' => true,

            'route' => [
                'Route_id'   => $route->Route_id,
                'Route_name' => $route->Route_name,
                'shift_code' => $route->shift_code,
            ],

            'members' => $members,
        ]);

    } // End Method


    /**
     * Save temporary route assignments for selected members.
     */
    public function StoreDailyRouteAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_date' => [
                'required',
                'date',
            ],

            'shift' => [
                'required',
                Rule::in(['A', 'N']),
            ],

            'original_route_id' => [
                'required',
                'string',
                'max:3',
                Rule::exists('route', 'Route_id'),
            ],

            'assigned_route_id' => [
                'required',
                'string',
                'max:3',
                Rule::exists('route', 'Route_id'),
                'different:original_route_id',
            ],

            'member_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'member_ids.*' => [
                'required',
                'string',
                'max:4',
                'distinct',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:100',
            ],
        ], [
            'assignment_date.required'    => 'Assignment date is required.',
            'shift.required'              => 'Please select the shift.',
            'original_route_id.required'  => 'Please select the original route.',
            'assigned_route_id.required'  => 'Please select the destination route.',
            'assigned_route_id.different' => 'The destination route must be different from the original route.',
            'member_ids.required'         => 'Please select at least one member.',
            'member_ids.min'              => 'Please select at least one member.',
        ]);

        $assignmentDate = Carbon::parse(
            $validated['assignment_date']
        )->format('Y-m-d');

        $shift = $validated['shift'];

        $originalRouteId = strtoupper(
            trim($validated['original_route_id'])
        );

        $assignedRouteId = strtoupper(
            trim($validated['assigned_route_id'])
        );

        $memberIds = array_values(
            array_unique($validated['member_ids'])
        );

        $reason = trim((string) ($validated['reason'] ?? ''));

        /*
        |--------------------------------------------------------------------------
        | Verify Both Routes
        |--------------------------------------------------------------------------
        | Both routes must:
        | - be active;
        | - belong to the selected shift;
        | - be different routes.
        */
        $validRoutes = DB::table('route')
            ->whereIn(
                'Route_id',
                [$originalRouteId, $assignedRouteId]
            )
            ->where('shift_code', $shift)
            ->where('trans_flag', 'A')
            ->pluck('Route_id')
            ->all();

        if (
            !in_array($originalRouteId, $validRoutes, true) ||
            !in_array($assignedRouteId, $validRoutes, true)
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with([
                    'message' =>
                        'Both the original and destination routes must be active routes for the selected shift.',
                    'alert-type' => 'error',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Selected Members
        |--------------------------------------------------------------------------
        | Members must belong to:
        | - the selected shift; and
        | - the selected permanent/original route.
        */
        $validMemberIds = DB::table('members')
            ->whereIn('ParticipantId', $memberIds)
            ->where('Trans_flag', $shift)
            ->where('Route', $originalRouteId)
            ->pluck('ParticipantId')
            ->all();

        $invalidMemberIds = array_values(
            array_diff($memberIds, $validMemberIds)
        );

        if (!empty($invalidMemberIds)) {
            return redirect()
                ->back()
                ->withInput()
                ->with([
                    'message' =>
                        'One or more selected members do not belong to the selected shift or original route.',
                    'alert-type' => 'error',
                ]);
        }

        $user = Auth::user();

        $userCode = $user->user_cd
            ?? $user->staffid
            ?? (string) $user->id;

        try {
            DB::transaction(function () use (
                $assignmentDate,
                $originalRouteId,
                $assignedRouteId,
                $validMemberIds,
                $reason,
                $userCode
            ) {
                foreach ($validMemberIds as $participantId) {

                    $existingAssignment = DB::table(
                        'member_route_assignments'
                    )
                        ->whereDate(
                            'assignment_date',
                            $assignmentDate
                        )
                        ->where(
                            'ParticipantId',
                            $participantId
                        )
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | Update Existing Assignment
                    |--------------------------------------------------------------------------
                    */
                    if ($existingAssignment) {
                        DB::table('member_route_assignments')
                            ->where('id', $existingAssignment->id)
                            ->update([
                                'original_route_id' => $originalRouteId,
                                'assigned_route_id' => $assignedRouteId,
                                'reason' => $reason !== ''
                                    ? $reason
                                    : null,
                                'user_cd'    => $userCode,
                                'updated_at' => now(),
                            ]);

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create New Assignment
                    |--------------------------------------------------------------------------
                    */
                    DB::table('member_route_assignments')
                        ->insert([
                            'assignment_date'  => $assignmentDate,
                            'ParticipantId'    => $participantId,
                            'original_route_id'=> $originalRouteId,
                            'assigned_route_id'=> $assignedRouteId,
                            'reason' => $reason !== ''
                                ? $reason
                                : null,
                            'user_cd'    => $userCode,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            });

            $memberCount = count($validMemberIds);

            return redirect()
                ->route('route.daily.assignment', [
                    'assignment_date' => $assignmentDate,
                ])
                ->with([
                    'message' =>
                        $memberCount .
                        ' member(s) temporarily assigned to route ' .
                        $assignedRouteId .
                        ' for ' .
                        Carbon::parse($assignmentDate)
                            ->format('m/d/Y') .
                        '.',
                    'alert-type' => 'success',
                ]);

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with([
                    'message' =>
                        'The temporary route assignments could not be saved.',
                    'alert-type' => 'error',
                ]);
        }

    } // End Method


    /**
     * Remove one temporary route assignment.
     */
    public function RemoveDailyRouteAssignment(Request $request, $id)
    {
        $assignment = DB::table('member_route_assignments')
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return redirect()
                ->back()
                ->with([
                    'message'    => 'Temporary route assignment not found.',
                    'alert-type' => 'error',
                ]);
        }

        try {
            DB::table('member_route_assignments')
                ->where('id', $id)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | No Members Table Update Is Needed
            |--------------------------------------------------------------------------
            | After deletion, the member automatically returns to the permanent
            | route stored in members.Route.
            */
            return redirect()
                ->route('route.daily.assignment', [
                    'assignment_date' =>
                        Carbon::parse($assignment->assignment_date)
                            ->format('Y-m-d'),
                ])
                ->with([
                    'message' =>
                        'Temporary route assignment removed successfully.',
                    'alert-type' => 'success',
                ]);

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with([
                    'message' =>
                        'The temporary route assignment could not be removed.',
                    'alert-type' => 'error',
                ]);
        }

    } // End Method

   public function RouteSigningSheet(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Active Routes
        |--------------------------------------------------------------------------
        */

        $routes = DB::table('route')
            ->select(
                'Route_id',
                'Route_name',
                'shift_code',
                'trans_flag'
            )
            ->where('trans_flag', 'A')
            ->whereIn('shift_code', ['A', 'N'])
            ->orderBy('shift_code')
            ->orderByRaw(
                "CAST(SUBSTRING(Route_id, 2) AS UNSIGNED) ASC"
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Selected Date
        |--------------------------------------------------------------------------
        */

        try {
            $selectedDate = $request->filled('report_date')
                ? Carbon::parse($request->report_date)->format('Y-m-d')
                : now()->format('Y-m-d');
        } catch (\Throwable $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Routes
        |--------------------------------------------------------------------------
        */

        $selectedRouteIds = collect(
            $request->input('route_ids', [])
        )
            ->map(function ($routeId) {
                return strtoupper(
                    trim((string) $routeId)
                );
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $routeReports = collect();
        $pdfUrl = null;

        if (!empty($selectedRouteIds)) {

            $request->validate([
                'route_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'route_ids.*' => [
                    'required',
                    'string',
                    'max:3',
                    'distinct',
                    Rule::exists('route', 'Route_id'),
                ],

                'report_date' => [
                    'required',
                    'date',
                ],
            ], [
                'route_ids.required' => 'Please select at least one route.',
                'route_ids.min'      => 'Please select at least one route.',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Retrieve Selected Routes
            |--------------------------------------------------------------------------
            | No joins are used.
            */

            $selectedRoutesById = DB::table('route')
                ->select(
                    'Route_id',
                    'Route_name',
                    'shift_code',
                    'trans_flag'
                )
                ->whereIn('Route_id', $selectedRouteIds)
                ->get()
                ->keyBy(function ($route) {
                    return trim(
                        (string) $route->Route_id
                    );
                });

            /*
            |--------------------------------------------------------------------------
            | Preserve User Selection Order
            |--------------------------------------------------------------------------
            */

            $selectedRoutes = collect($selectedRouteIds)
                ->map(function ($routeId) use (
                    $selectedRoutesById
                ) {
                    return $selectedRoutesById->get(
                        $routeId
                    );
                })
                ->filter()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Prepare On-Screen Report for Each Route
            |--------------------------------------------------------------------------
            */

            foreach ($selectedRoutes as $selectedRoute) {

                $rows = DB::table('members')
                    ->select(
                        'ParticipantId',
                        'LastName as last',
                        'FirstName as first',
                        'Address1 as address1',
                        'City as city',
                        'insulin_yn',
                        'Route',
                        'Trans_flag'
                    )
                    ->where(
                        'Route',
                        $selectedRoute->Route_id
                    )
                    ->where(
                        'Trans_flag',
                        $selectedRoute->shift_code
                    )
                    ->orderBy('ParticipantId')
                    ->get();

                $routeReports->push([
                    'route' => $selectedRoute,

                    'shiftName' =>
                        $selectedRoute->shift_code === 'N'
                            ? 'Noon'
                            : 'Morning',

                    'rows' => $rows,
                ]);
            }

            $pdfUrl = route(
                'route.signing.sheet.pdf',
                [
                    'route_ids'   => $selectedRouteIds,
                    'report_date' => $selectedDate,
                ]
            );
        }

        return view(
            'drivers.route_signing_sheet',
            compact(
                'routes',
                'selectedRouteIds',
                'selectedDate',
                'routeReports',
                'pdfUrl'
            ),
            [
                'title'  => 'Driver Management',
                'module' => 'driver',
            ]
        );

    } // End Method

    public function RouteSigningSheetPdf(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'route_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'route_ids.*' => [
                'required',
                'string',
                'max:3',
                'distinct',
                Rule::exists('route', 'Route_id'),
            ],

            'report_date' => [
                'required',
                'date',
            ],
        ], [
            'route_ids.required' => 'Please select at least one route.',
            'route_ids.min'      => 'Please select at least one route.',
        ]);

        $selectedRouteIds = collect(
            $validated['route_ids']
        )
            ->map(function ($routeId) {
                return strtoupper(
                    trim((string) $routeId)
                );
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $selectedDate = Carbon::parse(
            $validated['report_date']
        )->format('Y-m-d');

        $formattedDate = Carbon::parse(
            $selectedDate
        )->format('m/d/Y');

        /*
        |--------------------------------------------------------------------------
        | 2. Retrieve Selected Routes
        |--------------------------------------------------------------------------
        */

        $selectedRoutesById = DB::table('route')
            ->select(
                'Route_id',
                'Route_name',
                'shift_code',
                'trans_flag'
            )
            ->whereIn('Route_id', $selectedRouteIds)
            ->get()
            ->keyBy(function ($route) {
                return trim(
                    (string) $route->Route_id
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Preserve User Selection Order
        |--------------------------------------------------------------------------
        */

        $selectedRoutes = collect($selectedRouteIds)
            ->map(function ($routeId) use (
                $selectedRoutesById
            ) {
                return $selectedRoutesById->get(
                    $routeId
                );
            })
            ->filter()
            ->values();

        if ($selectedRoutes->isEmpty()) {
            return redirect()
                ->route('route.signing.sheet')
                ->with([
                    'message'    => 'No valid routes were selected.',
                    'alert-type' => 'error',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Member Image Locations
        |--------------------------------------------------------------------------
        */

        $mediaFolder = session('media_folder');

        if (empty($mediaFolder)) {
            abort(
                403,
                'Organization image folder is not configured.'
            );
        }

        $memberImageDir = public_path(
            "uploads/{$mediaFolder}/members/images"
        );

        $thumbDir = public_path(
            "uploads/{$mediaFolder}/members/images/thumbs"
        );

        if (!is_dir($thumbDir)) {
            if (
                !mkdir($thumbDir, 0775, true) &&
                !is_dir($thumbDir)
            ) {
                abort(
                    500,
                    'Unable to create member thumbnail directory.'
                );
            }
        }

        $fallback = public_path(
            'members/images/no_image.jpg'
        );

        if (!is_file($fallback)) {
            abort(
                500,
                'Fallback member image was not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Thumbnail Generator
        |--------------------------------------------------------------------------
        */

        $makeThumb = function (
            string $sourcePath,
            string $destinationPath,
            int $targetWidth = 90,
            int $targetHeight = 72,
            int $quality = 75
        ): bool {
            if (!is_file($sourcePath)) {
                return false;
            }

            if (!function_exists('imagecreatetruecolor')) {
                return false;
            }

            $imageInfo = @getimagesize(
                $sourcePath
            );

            if (!$imageInfo) {
                return false;
            }

            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = @imagecreatefromjpeg(
                        $sourcePath
                    );
                    break;

                case IMAGETYPE_PNG:
                    $sourceImage = @imagecreatefrompng(
                        $sourcePath
                    );
                    break;

                case IMAGETYPE_GIF:
                    $sourceImage = @imagecreatefromgif(
                        $sourcePath
                    );
                    break;

                default:
                    return false;
            }

            if (!$sourceImage) {
                return false;
            }

            $sourceWidth = imagesx(
                $sourceImage
            );

            $sourceHeight = imagesy(
                $sourceImage
            );

            if (
                $sourceWidth <= 0 ||
                $sourceHeight <= 0
            ) {
                imagedestroy(
                    $sourceImage
                );

                return false;
            }

            $destinationImage = imagecreatetruecolor(
                $targetWidth,
                $targetHeight
            );

            if (!$destinationImage) {
                imagedestroy(
                    $sourceImage
                );

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | White Background
            |--------------------------------------------------------------------------
            */

            $white = imagecolorallocate(
                $destinationImage,
                255,
                255,
                255
            );

            imagefill(
                $destinationImage,
                0,
                0,
                $white
            );

            /*
            |--------------------------------------------------------------------------
            | Center Crop
            |--------------------------------------------------------------------------
            */

            $sourceAspect =
                $sourceWidth / max(1, $sourceHeight);

            $targetAspect =
                $targetWidth / max(1, $targetHeight);

            if ($sourceAspect > $targetAspect) {

                $cropWidth = (int) round(
                    $sourceHeight * $targetAspect
                );

                $cropHeight = $sourceHeight;

                $sourceX = (int) max(
                    0,
                    ($sourceWidth - $cropWidth) / 2
                );

                $sourceY = 0;

            } else {

                $cropWidth = $sourceWidth;

                $cropHeight = (int) round(
                    $sourceWidth / $targetAspect
                );

                $sourceX = 0;

                $sourceY = (int) max(
                    0,
                    ($sourceHeight - $cropHeight) / 2
                );
            }

            $copied = imagecopyresampled(
                $destinationImage,
                $sourceImage,
                0,
                0,
                $sourceX,
                $sourceY,
                $targetWidth,
                $targetHeight,
                $cropWidth,
                $cropHeight
            );

            $saved = false;

            if ($copied) {
                $saved = @imagejpeg(
                    $destinationImage,
                    $destinationPath,
                    $quality
                );
            }

            imagedestroy(
                $sourceImage
            );

            imagedestroy(
                $destinationImage
            );

            return $saved &&
                is_file($destinationPath);
        };

        /*
        |--------------------------------------------------------------------------
        | 5. Prepare Every Route Separately
        |--------------------------------------------------------------------------
        */

        $rowsPerPage = 16;
        $routeReports = collect();

        foreach ($selectedRoutes as $selectedRoute) {

            /*
            |--------------------------------------------------------------------------
            | Retrieve Route Members — No Joins
            |--------------------------------------------------------------------------
            */

            $rows = DB::table('members')
                ->select(
                    'ParticipantId',
                    'LastName as last',
                    'FirstName as first',
                    'Address1 as address1',
                    'City as city',
                    'insulin_yn'
                )
                ->where(
                    'Route',
                    $selectedRoute->Route_id
                )
                ->where(
                    'Trans_flag',
                    $selectedRoute->shift_code
                )
                ->orderBy('ParticipantId')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Resolve Member Photos
            |--------------------------------------------------------------------------
            */

            $rows = $rows->map(
                function ($member) use (
                    $memberImageDir,
                    $thumbDir,
                    $fallback,
                    $makeThumb
                ) {
                    $participantId = trim(
                        (string) $member->ParticipantId
                    );

                    $numericId = $participantId;

                    if (
                        $participantId !== '' &&
                        ctype_digit($participantId)
                    ) {
                        $numericId = ltrim(
                            $participantId,
                            '0'
                        );

                        if ($numericId === '') {
                            $numericId = '0';
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Check Padded and Non-Padded Filenames
                    |--------------------------------------------------------------------------
                    */

                    $baseNames = array_values(
                        array_unique([
                            $participantId,
                            $numericId,
                        ])
                    );

                    $extensions = [
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'JPG',
                        'JPEG',
                        'PNG',
                        'GIF',
                    ];

                    $sourcePhoto = null;

                    foreach ($baseNames as $baseName) {

                        if ($baseName === '') {
                            continue;
                        }

                        foreach ($extensions as $extension) {

                            $candidate =
                                $memberImageDir .
                                DIRECTORY_SEPARATOR .
                                $baseName .
                                '.' .
                                $extension;

                            if (is_file($candidate)) {
                                $sourcePhoto = $candidate;
                                break 2;
                            }
                        }
                    }

                    if ($sourcePhoto === null) {
                        $sourcePhoto = $fallback;
                    }

                    $safeId = preg_replace(
                        '/[^A-Za-z0-9_-]/',
                        '_',
                        $participantId
                    );

                    if ($safeId === '') {
                        $safeId = 'unknown';
                    }

                    $thumbPath =
                        $thumbDir .
                        DIRECTORY_SEPARATOR .
                        $safeId .
                        '_route_signing_dompdf.jpg';

                    $thumbnailNeedsUpdate =
                        !is_file($thumbPath) ||
                        (
                            is_file($sourcePhoto) &&
                            filemtime($sourcePhoto) >
                            filemtime($thumbPath)
                        );

                    if ($thumbnailNeedsUpdate) {

                        if (is_file($thumbPath)) {
                            @unlink($thumbPath);
                        }

                        $thumbnailCreated = $makeThumb(
                            $sourcePhoto,
                            $thumbPath
                        );

                        if (!$thumbnailCreated) {
                            $thumbPath = $sourcePhoto;
                        }
                    }

                    if (!is_file($thumbPath)) {
                        $thumbPath = $sourcePhoto;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Convert Image to Base64
                    |--------------------------------------------------------------------------
                    */

                    $member->photo_uri = null;

                    if (is_file($thumbPath)) {

                        $imageContents = @file_get_contents(
                            $thumbPath
                        );

                        if ($imageContents !== false) {

                            $mimeType = @mime_content_type(
                                $thumbPath
                            );

                            if (!$mimeType) {
                                $mimeType = 'image/jpeg';
                            }

                            $member->photo_uri =
                                'data:' .
                                $mimeType .
                                ';base64,' .
                                base64_encode($imageContents);
                        }
                    }

                    return $member;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Route Pages
            |--------------------------------------------------------------------------
            */

            $pages = $rows
                ->chunk($rowsPerPage)
                ->values();

            if ($pages->isEmpty()) {
                $pages = collect([
                    collect(),
                ]);
            }

            $routeReports->push([
                'route' => $selectedRoute,

                'title' =>
                    $selectedRoute->Route_id .
                    ' - ' .
                    strtoupper(
                        trim(
                            (string) $selectedRoute->Route_name
                        )
                    ),

                'shiftName' =>
                    $selectedRoute->shift_code === 'N'
                        ? 'Noon'
                        : 'Morning',

                'pages'      => $pages,
                'totalPages' => $pages->count(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Generate PDF
        |--------------------------------------------------------------------------
        */

        $companyName = config(
            'company.comp_nm'
        );

        $pdf = Pdf::loadView(
            'drivers.route_signing_sheet_pdf',
            [
                'companyName'  => $companyName,
                'routeReports' => $routeReports,
                'rowsPerPage'  => $rowsPerPage,
                'selectedDate' => $selectedDate,
                'formattedDate'=> $formattedDate,
            ]
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        $pdf->setOptions([
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'defaultFont'          => 'Arial',
            'dpi'                  => 96,
            'fontHeightRatio'      => 1.0,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 7. Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            'route_signing_sheets_' .
            Carbon::parse($selectedDate)->format('Ymd') .
            '_' .
            now()->format('His') .
            '.pdf';

        return $pdf->stream(
            $filename
        );

    } // End Method

    public function DriverSigningSheet(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Driver List
        |--------------------------------------------------------------------------
        */

        $employees = DB::table('employees')
            ->select(
                'staffid',
                'stafflastname',
                'stafffirstname'
            )
            ->where('staffcategoryid', 'DRV')
            ->orderBy('stafflastname')
            ->orderBy('stafffirstname')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Selected Date
        |--------------------------------------------------------------------------
        */

        try {
            $selectedDate = $request->filled('report_date')
                ? Carbon::parse($request->report_date)->format('Y-m-d')
                : now()->format('Y-m-d');
        } catch (\Throwable $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Drivers
        |--------------------------------------------------------------------------
        */

        $selectedDriverIds = collect(
            $request->input('driver_ids', [])
        )
            ->map(function ($staffId) {
                return trim((string) $staffId);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $driverReports = collect();
        $pdfUrl = null;

        if (!empty($selectedDriverIds)) {
            $request->validate([
                'driver_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'driver_ids.*' => [
                    'required',
                    'string',
                    'max:4',
                    'distinct',
                    Rule::exists('employees', 'staffid')
                        ->where(function ($query) {
                            $query->where('staffcategoryid', 'DRV');
                        }),
                ],

                'report_date' => [
                    'required',
                    'date',
                ],
            ], [
                'driver_ids.required' => 'Please select at least one driver.',
                'driver_ids.min'      => 'Please select at least one driver.',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Retrieve Selected Drivers — No Joins
            |--------------------------------------------------------------------------
            */

            $selectedDriversById = DB::table('employees')
                ->select(
                    'staffid',
                    'stafflastname',
                    'stafffirstname'
                )
                ->where('staffcategoryid', 'DRV')
                ->whereIn('staffid', $selectedDriverIds)
                ->get()
                ->keyBy(function ($employee) {
                    return trim((string) $employee->staffid);
                });

            /*
            |--------------------------------------------------------------------------
            | Preserve Selection Order
            |--------------------------------------------------------------------------
            */

            $selectedDrivers = collect($selectedDriverIds)
                ->map(function ($staffId) use ($selectedDriversById) {
                    return $selectedDriversById->get($staffId);
                })
                ->filter()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Prepare Driver Reports
            |--------------------------------------------------------------------------
            */

            foreach ($selectedDrivers as $driver) {
                $rows = DB::table('members')
                    ->select(
                        'ParticipantId',
                        'LastName as last',
                        'FirstName as first',
                        'Address1 as address1',
                        'City as city',
                        'insulin_yn',
                        'driverid',
                        'Trans_flag'
                    )
                    ->where('driverid', $driver->staffid)
                    ->whereIn('Trans_flag', ['A', 'N'])
                    ->orderBy('ParticipantId')
                    ->get();

                $driverReports->push([
                    'driver' => $driver,
                    'rows'   => $rows,
                ]);
            }

            $pdfUrl = route('driver.signing.sheet.pdf') . '?' . http_build_query([
                'driver_ids' => $selectedDriverIds,
                'report_date'=> $selectedDate,
            ]);
        }

        return view(
            'drivers.driver_signing_sheet',
            compact(
                'employees',
                'selectedDriverIds',
                'selectedDate',
                'driverReports',
                'pdfUrl'
            ),
            [
                'title'  => 'Driver Management',
                'module' => 'driver',
            ]
        );

    } // End Method

    public function DriverSigningSheetPdf(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'driver_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'driver_ids.*' => [
                'required',
                'string',
                'max:4',
                'distinct',
                Rule::exists('employees', 'staffid')
                    ->where(function ($query) {
                        $query->where('staffcategoryid', 'DRV');
                    }),
            ],

            'report_date' => [
                'required',
                'date',
            ],
        ], [
            'driver_ids.required' => 'Please select at least one driver.',
            'driver_ids.min'      => 'Please select at least one driver.',
        ]);

        $selectedDriverIds = collect($validated['driver_ids'])
            ->map(function ($staffId) {
                return trim((string) $staffId);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $selectedDate = Carbon::parse(
            $validated['report_date']
        )->format('Y-m-d');

        $formattedDate = Carbon::parse(
            $selectedDate
        )->format('m/d/Y');

        /*
        |--------------------------------------------------------------------------
        | Selected Drivers — No Joins
        |--------------------------------------------------------------------------
        */

        $selectedDriversById = DB::table('employees')
            ->select(
                'staffid',
                'stafflastname',
                'stafffirstname'
            )
            ->where('staffcategoryid', 'DRV')
            ->whereIn('staffid', $selectedDriverIds)
            ->get()
            ->keyBy(function ($employee) {
                return trim((string) $employee->staffid);
            });

        $selectedDrivers = collect($selectedDriverIds)
            ->map(function ($staffId) use ($selectedDriversById) {
                return $selectedDriversById->get($staffId);
            })
            ->filter()
            ->values();

        if ($selectedDrivers->isEmpty()) {
            return redirect()
                ->route('driver.signing.sheet')
                ->with([
                    'message'    => 'No valid drivers were selected.',
                    'alert-type' => 'error',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Image Locations
        |--------------------------------------------------------------------------
        */

        $mediaFolder = session('media_folder');

        if (empty($mediaFolder)) {
            abort(
                403,
                'Organization image folder is not configured.'
            );
        }

        $memberImageDir = public_path(
            "uploads/{$mediaFolder}/members/images"
        );

        $thumbDir = public_path(
            "uploads/{$mediaFolder}/members/images/thumbs"
        );

        if (!is_dir($thumbDir)) {
            if (!mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
                abort(
                    500,
                    'Unable to create member thumbnail directory.'
                );
            }
        }

        $fallback = public_path(
            'members/images/no_image.jpg'
        );

        if (!is_file($fallback)) {
            abort(
                500,
                'Fallback member image was not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Thumbnail Generator
        |--------------------------------------------------------------------------
        */

        $makeThumb = function (
            string $sourcePath,
            string $destinationPath,
            int $targetWidth = 90,
            int $targetHeight = 72,
            int $quality = 75
        ): bool {
            if (
                !is_file($sourcePath) ||
                !function_exists('imagecreatetruecolor')
            ) {
                return false;
            }

            $imageInfo = @getimagesize($sourcePath);

            if (!$imageInfo) {
                return false;
            }

            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = @imagecreatefromjpeg($sourcePath);
                    break;

                case IMAGETYPE_PNG:
                    $sourceImage = @imagecreatefrompng($sourcePath);
                    break;

                case IMAGETYPE_GIF:
                    $sourceImage = @imagecreatefromgif($sourcePath);
                    break;

                default:
                    return false;
            }

            if (!$sourceImage) {
                return false;
            }

            $sourceWidth  = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            if ($sourceWidth <= 0 || $sourceHeight <= 0) {
                imagedestroy($sourceImage);

                return false;
            }

            $destinationImage = imagecreatetruecolor(
                $targetWidth,
                $targetHeight
            );

            if (!$destinationImage) {
                imagedestroy($sourceImage);

                return false;
            }

            $white = imagecolorallocate(
                $destinationImage,
                255,
                255,
                255
            );

            imagefill(
                $destinationImage,
                0,
                0,
                $white
            );

            $sourceAspect = $sourceWidth / max(1, $sourceHeight);
            $targetAspect = $targetWidth / max(1, $targetHeight);

            if ($sourceAspect > $targetAspect) {
                $cropWidth  = (int) round($sourceHeight * $targetAspect);
                $cropHeight = $sourceHeight;
                $sourceX    = (int) max(0, ($sourceWidth - $cropWidth) / 2);
                $sourceY    = 0;
            } else {
                $cropWidth  = $sourceWidth;
                $cropHeight = (int) round($sourceWidth / $targetAspect);
                $sourceX    = 0;
                $sourceY    = (int) max(0, ($sourceHeight - $cropHeight) / 2);
            }

            $copied = imagecopyresampled(
                $destinationImage,
                $sourceImage,
                0,
                0,
                $sourceX,
                $sourceY,
                $targetWidth,
                $targetHeight,
                $cropWidth,
                $cropHeight
            );

            $saved = false;

            if ($copied) {
                $saved = @imagejpeg(
                    $destinationImage,
                    $destinationPath,
                    $quality
                );
            }

            imagedestroy($sourceImage);
            imagedestroy($destinationImage);

            return $saved && is_file($destinationPath);
        };

        /*
        |--------------------------------------------------------------------------
        | Prepare Driver Reports
        |--------------------------------------------------------------------------
        */

        $rowsPerPage = 16;
        $driverReports = collect();

        foreach ($selectedDrivers as $driver) {
            $rows = DB::table('members')
                ->select(
                    'ParticipantId',
                    'LastName as last',
                    'FirstName as first',
                    'Address1 as address1',
                    'City as city',
                    'insulin_yn'
                )
                ->where('driverid', $driver->staffid)
                ->whereIn('Trans_flag', ['A', 'N'])
                ->orderBy('ParticipantId')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Member Photos
            |--------------------------------------------------------------------------
            */

            $rows = $rows->map(function ($member) use (
                $memberImageDir,
                $thumbDir,
                $fallback,
                $makeThumb
            ) {
                $participantId = trim(
                    (string) $member->ParticipantId
                );

                $numericId = $participantId;

                if (
                    $participantId !== '' &&
                    ctype_digit($participantId)
                ) {
                    $numericId = ltrim($participantId, '0');

                    if ($numericId === '') {
                        $numericId = '0';
                    }
                }

                $baseNames = array_values(
                    array_unique([
                        $participantId,
                        $numericId,
                    ])
                );

                $extensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'JPG',
                    'JPEG',
                    'PNG',
                    'GIF',
                ];

                $sourcePhoto = null;

                foreach ($baseNames as $baseName) {
                    if ($baseName === '') {
                        continue;
                    }

                    foreach ($extensions as $extension) {
                        $candidate =
                            $memberImageDir .
                            DIRECTORY_SEPARATOR .
                            $baseName .
                            '.' .
                            $extension;

                        if (is_file($candidate)) {
                            $sourcePhoto = $candidate;
                            break 2;
                        }
                    }
                }

                if ($sourcePhoto === null) {
                    $sourcePhoto = $fallback;
                }

                $safeId = preg_replace(
                    '/[^A-Za-z0-9_-]/',
                    '_',
                    $participantId
                );

                if ($safeId === '') {
                    $safeId = 'unknown';
                }

                $thumbPath =
                    $thumbDir .
                    DIRECTORY_SEPARATOR .
                    $safeId .
                    '_driver_signing_dompdf.jpg';

                $thumbnailNeedsUpdate =
                    !is_file($thumbPath) ||
                    (
                        is_file($sourcePhoto) &&
                        filemtime($sourcePhoto) > filemtime($thumbPath)
                    );

                if ($thumbnailNeedsUpdate) {
                    if (is_file($thumbPath)) {
                        @unlink($thumbPath);
                    }

                    $thumbnailCreated = $makeThumb(
                        $sourcePhoto,
                        $thumbPath
                    );

                    if (!$thumbnailCreated) {
                        $thumbPath = $sourcePhoto;
                    }
                }

                if (!is_file($thumbPath)) {
                    $thumbPath = $sourcePhoto;
                }

                $member->photo_uri = null;

                if (is_file($thumbPath)) {
                    $imageContents = @file_get_contents($thumbPath);

                    if ($imageContents !== false) {
                        $mimeType = @mime_content_type($thumbPath);

                        if (!$mimeType) {
                            $mimeType = 'image/jpeg';
                        }

                        $member->photo_uri =
                            'data:' .
                            $mimeType .
                            ';base64,' .
                            base64_encode($imageContents);
                    }
                }

                return $member;
            });

            $pages = $rows
                ->chunk($rowsPerPage)
                ->values();

            if ($pages->isEmpty()) {
                $pages = collect([
                    collect(),
                ]);
            }

            $driverName = trim(
                ($driver->stafffirstname ?? '') .
                ' ' .
                ($driver->stafflastname ?? '')
            );

            $driverReports->push([
                'driver' => $driver,

                'title' =>
                    $driver->staffid .
                    ' - ' .
                    strtoupper($driverName),

                'pages'      => $pages,
                'totalPages' => $pages->count(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'drivers.driver_signing_sheet_pdf',
            [
                'companyName'   => config('company.comp_nm'),
                'driverReports' => $driverReports,
                'rowsPerPage'   => $rowsPerPage,
                'selectedDate'  => $selectedDate,
                'formattedDate' => $formattedDate,
            ]
        );

        $pdf->setPaper('a4', 'landscape');

        $pdf->setOptions([
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'defaultFont'          => 'Arial',
            'dpi'                  => 96,
            'fontHeightRatio'      => 1.0,
        ]);

        $filename =
            'driver_signing_sheets_' .
            Carbon::parse($selectedDate)->format('Ymd') .
            '_' .
            now()->format('His') .
            '.pdf';

        return $pdf->stream($filename);

    } // End Method


}
