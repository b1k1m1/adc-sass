<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleMasterController extends Controller
{
    private string $table = 'vehiclemaster';

    public function index()
    {
        // Simple list (you can replace with Yajra later)
        $rows = DB::table('vehiclemaster')
            ->orderBy('vehicle_id')
            ->get();

          // Driver list (DRV only)
    $employees = DB::table('employees')
        ->select(
            'staffid',
            'stafflastname',
            'stafffirstname',
        )
        ->where('staffcategoryid', 'DRV')
        ->orderBy('stafflastname')
        ->orderBy('stafffirstname')
        ->get();

         // Build lookup array: staffid => "Last, First"
    $drivers = $employees->mapWithKeys(function ($e) {
        return [
            $e->staffid => trim($e->stafflastname . ', ' . $e->stafffirstname)
        ];
    });

        return view('vehicles.index', compact('rows', 'drivers'));

    } // End Method


    public function create()
    {
        $mode = 'create';
        $row  = null;

        $drivers = $this->getDrivers();

        // dropdowns (best effort: load distinct values already used)
        $dropdowns = $this->dropdowns();

        return view('vehicles.form', compact('mode', 'row', 'dropdowns', 'drivers'));

    } // End Method

     public function store(Request $request)
    {
        $data = $this->validateData($request, 'create');

        // handle photo upload (optional)
        $data['photo'] = $this->handlePhotoUpload($request, null);

        // common fields
        $user = Auth::user();
        $data['update_flag'] = 'Y';
        $data['user_cd']     = $user?->user_cd ?? ($user?->id ?? null);
        $data['company_id']  = $user?->company_id ?? 'DS02';
        $data['update_dt']   = now();

        DB::table($this->table)->insert($data);

        return redirect()->route('vehiclemaster.index')->with('success', 'Vehicle saved.');

    } // End Method

    public function edit(string $vehicle_id)
    {
        $mode = 'edit';

        $row = DB::table($this->table)->where('vehicle_id', $vehicle_id)->first();
        abort_if(!$row, 404);

        $drivers = $this->getDrivers();

        $dropdowns = $this->dropdowns();

        return view('vehicles.form', compact('mode', 'row', 'dropdowns','drivers'));

    } // End Method

    public function update(Request $request, string $vehicle_id)
    {
        $row = DB::table($this->table)->where('vehicle_id', $vehicle_id)->first();
        abort_if(!$row, 404);

        $data = $this->validateData($request, 'edit', $vehicle_id);

        // photo: if user uploads new => replace; else keep old
        $data['photo'] = $this->handlePhotoUpload($request, $row->photo);

        // common fields
        $user = Auth::user();
        $data['update_flag'] = 'Y';
        $data['user_cd']     = $user?->user_cd ?? ($user?->id ?? null);
        $data['company_id']  = $user?->company_id ?? 'DS02';
        $data['update_dt']   = now();

        DB::table($this->table)->where('vehicle_id', $vehicle_id)->update($data);

        return redirect()->route('vehiclemaster.index')->with('success', 'Vehicle updated.');

    } // End Method

    private function getDrivers()
    {
        return DB::table('employees')
            ->select('staffid', 'stafflastname', 'stafffirstname')
            ->where('staffcategoryid', 'DRV')
            ->orderBy('stafflastname')
            ->orderBy('stafffirstname')
            ->get();

    } // End Method

    private function validateData(Request $request, string $mode, ?string $vehicle_id = null): array
    {
        $uniqueRule = $mode === 'create'
            ? 'unique:vehiclemaster,vehicle_id'
            : 'unique:vehiclemaster,vehicle_id,' . $vehicle_id . ',vehicle_id';

        return $request->validate([
            'vehicle_id'        => ['required','string','max:3', $uniqueRule],
            'category'          => ['nullable','string','max:15'],
            'description'       => ['nullable','string','max:25'],
            'manufacturer'      => ['nullable','string','max:25'],
            'model'             => ['nullable','string','max:15'],
            'make_year'         => ['nullable','string','max:4'],
            'licence_plate_no'  => ['nullable','string','max:10'],
            'purchase_from'     => ['nullable','string','max:25'],
            'purchase_dt'       => ['nullable','date'],
            'vin_no'            => ['nullable','string','max:25'],
            'registration_from' => ['nullable','date'],
            'registration_to'   => ['nullable','date'],
            'lease_type'        => ['nullable','string','max:15'],
            'liento'            => ['nullable','string','max:25'],
            'capacity'          => ['nullable','numeric'],
            'driver_id'         => ['nullable','string','max:3'],
            'trans_flag'        => ['nullable','string','max:1'], // status dropdown in your screenshot
        ]);

    } // End Method


    private function handlePhotoUpload(Request $request, ?string $current): ?string
    {
        if (!$request->hasFile('photo_file')) {
            return $current;
        }

        $file = $request->file('photo_file');
        if (!$file->isValid()) {
            return $current;
        }

        // Your column is varchar(15) so keep filename SHORT.
        // Example: V01_8F3A2.jpg (<= 15)
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $id  = strtoupper(substr($request->input('vehicle_id', 'V'), 0, 3));
        $rand = strtoupper(substr(Str::random(5), 0, 5));
        $name = "{$id}_{$rand}." . substr($ext, 0, 3);

        // store in: storage/app/public/vehicles
        $file->storeAs('public/vehicles', $name);

        return $name;
    }

    private function dropdowns(): array
    {
        // Best effort: load distinct values already present in vehiclemaster.
        // Replace later with your dropdown master table, if you have one.
        $categories = DB::table($this->table)
            ->select('category')
            ->whereNotNull('category')
            ->where('category','<>','')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $leaseTypes = DB::table($this->table)
            ->select('lease_type')
            ->whereNotNull('lease_type')
            ->where('lease_type','<>','')
            ->distinct()
            ->orderBy('lease_type')
            ->pluck('lease_type')
            ->toArray();

        $flags = DB::table($this->table)
            ->select('trans_flag')
            ->whereNotNull('trans_flag')
            ->where('trans_flag','<>','')
            ->distinct()
            ->orderBy('trans_flag')
            ->pluck('trans_flag')
            ->toArray();

        // Driver dropdown: you likely have a driver table. For now we show distinct driver_id from vehiclemaster.
        $drivers = DB::table($this->table)
            ->select('driver_id')
            ->whereNotNull('driver_id')
            ->where('driver_id','<>','')
            ->distinct()
            ->orderBy('driver_id')
            ->pluck('driver_id')
            ->toArray();

        // add common defaults if empty
        if (empty($flags)) {
            $flags = ['A', 'I']; // change to your real codes if different
        }

        return [
            'categories' => $categories,
            'leaseTypes' => $leaseTypes,
            'flags'      => $flags,
            'drivers'    => $drivers,
        ];

    } // End Method


}
