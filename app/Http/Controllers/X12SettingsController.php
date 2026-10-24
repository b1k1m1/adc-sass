<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class X12SettingsController extends Controller
{
    /**
     * Display all X12 setup information.
     *
     * CRUD is provided for:
     * - x12_clearinghouse_settings
     * - x12_provider_settings
     * - x12_payers
     *
     * System-managed tables are displayed read-only:
     * - x12_control_numbers
     * - x12_submissions
     * - x12_submission_claims
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'clearinghouse');

        $clearinghouses = DB::table('x12_clearinghouse_settings')
            ->orderByDesc('is_default')
            ->orderBy('setting_name')
            ->get();

        $providers = DB::table('x12_provider_settings')
            ->orderByDesc('is_default')
            ->orderBy('provider_name')
            ->get();

        $payers = DB::table('x12_payers as xp')
            ->leftJoin('hmodropdown as h', 'h.Id', '=', 'xp.hmo_id')
            ->select(
                'xp.*',
                'h.name as hmo_name'
            )
            ->orderBy('xp.payer_name')
            ->get();

        $hmos = DB::table('hmodropdown')
            ->where('use_flag', 'Y')
            ->orderBy('name')
            ->get();

        $controlNumbers = DB::table('x12_control_numbers as c')
            ->join(
                'x12_clearinghouse_settings as h',
                'h.id',
                '=',
                'c.clearinghouse_setting_id'
            )
            ->select(
                'c.*',
                'h.setting_name'
            )
            ->orderBy('h.setting_name')
            ->get();

        $submissions = DB::table('x12_submissions as s')
            ->join(
                'x12_clearinghouse_settings as h',
                'h.id',
                '=',
                's.clearinghouse_setting_id'
            )
            ->select(
                's.*',
                'h.setting_name'
            )
            ->orderByDesc('s.generated_at')
            ->limit(100)
            ->get();

        $submissionClaims = DB::table('x12_submission_claims as sc')
            ->join(
                'x12_submissions as s',
                's.id',
                '=',
                'sc.x12_submission_id'
            )
            ->select(
                'sc.*',
                's.file_name'
            )
            ->orderByDesc('sc.id')
            ->limit(200)
            ->get();

        return view('claims.settings.x12.index', compact(
            'activeTab',
            'clearinghouses',
            'providers',
            'payers',
            'hmos',
            'controlNumbers',
            'submissions',
            'submissionClaims'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Clearinghouse CRUD
    |--------------------------------------------------------------------------
    */

    public function storeClearinghouse(Request $request)
    {
        $data = $this->validateClearinghouse($request);

        DB::transaction(function () use ($data) {
            if (! empty($data['is_default'])) {
                DB::table('x12_clearinghouse_settings')->update([
                    'is_default' => 0,
                    'updated_at' => now(),
                ]);
            }

            $clearinghouseId = DB::table('x12_clearinghouse_settings')
                ->insertGetId(array_merge(
                    $this->normalizeClearinghouse($data),
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ));

            DB::table('x12_control_numbers')->insert([
                'clearinghouse_setting_id' => $clearinghouseId,
                'next_isa_number' => 1,
                'next_gs_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('x12.settings.index', ['tab' => 'clearinghouse'])
            ->with('success', 'Clearinghouse settings saved successfully.');
    }

    public function updateClearinghouse(Request $request, int $id)
    {
        abort_unless(
            DB::table('x12_clearinghouse_settings')->where('id', $id)->exists(),
            404
        );

        $data = $this->validateClearinghouse($request, $id);

        DB::transaction(function () use ($data, $id) {
            if (! empty($data['is_default'])) {
                DB::table('x12_clearinghouse_settings')
                    ->where('id', '<>', $id)
                    ->update([
                        'is_default' => 0,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('x12_clearinghouse_settings')
                ->where('id', $id)
                ->update(array_merge(
                    $this->normalizeClearinghouse($data),
                    ['updated_at' => now()]
                ));

            if (! DB::table('x12_control_numbers')
                ->where('clearinghouse_setting_id', $id)
                ->exists()) {
                DB::table('x12_control_numbers')->insert([
                    'clearinghouse_setting_id' => $id,
                    'next_isa_number' => 1,
                    'next_gs_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('x12.settings.index', ['tab' => 'clearinghouse'])
            ->with('success', 'Clearinghouse settings updated successfully.');
    }

    public function destroyClearinghouse(int $id)
    {
        abort_unless(
            DB::table('x12_clearinghouse_settings')->where('id', $id)->exists(),
            404
        );

        if (DB::table('x12_submissions')
            ->where('clearinghouse_setting_id', $id)
            ->exists()) {
            return redirect()
                ->route('x12.settings.index', ['tab' => 'clearinghouse'])
                ->with(
                    'error',
                    'This clearinghouse cannot be deleted because submission history exists.'
                );
        }

        DB::transaction(function () use ($id) {
            DB::table('x12_control_numbers')
                ->where('clearinghouse_setting_id', $id)
                ->delete();

            DB::table('x12_clearinghouse_settings')
                ->where('id', $id)
                ->delete();
        });

        return redirect()
            ->route('x12.settings.index', ['tab' => 'clearinghouse'])
            ->with('success', 'Clearinghouse settings deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Provider CRUD
    |--------------------------------------------------------------------------
    */

    public function storeProvider(Request $request)
    {
        $data = $this->validateProvider($request);

        DB::transaction(function () use ($data) {
            if (! empty($data['is_default'])) {
                DB::table('x12_provider_settings')->update([
                    'is_default' => 0,
                    'updated_at' => now(),
                ]);
            }

            DB::table('x12_provider_settings')->insert(array_merge(
                $this->normalizeProvider($data),
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ));
        });

        return redirect()
            ->route('x12.settings.index', ['tab' => 'provider'])
            ->with('success', 'Provider settings saved successfully.');
    }

    public function updateProvider(Request $request, int $id)
    {
        abort_unless(
            DB::table('x12_provider_settings')->where('id', $id)->exists(),
            404
        );

        $data = $this->validateProvider($request, $id);

        DB::transaction(function () use ($data, $id) {
            if (! empty($data['is_default'])) {
                DB::table('x12_provider_settings')
                    ->where('id', '<>', $id)
                    ->update([
                        'is_default' => 0,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('x12_provider_settings')
                ->where('id', $id)
                ->update(array_merge(
                    $this->normalizeProvider($data),
                    ['updated_at' => now()]
                ));
        });

        return redirect()
            ->route('x12.settings.index', ['tab' => 'provider'])
            ->with('success', 'Provider settings updated successfully.');
    }

    public function destroyProvider(int $id)
    {
        abort_unless(
            DB::table('x12_provider_settings')->where('id', $id)->exists(),
            404
        );

        DB::table('x12_provider_settings')
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('x12.settings.index', ['tab' => 'provider'])
            ->with('success', 'Provider settings deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Payer CRUD
    |--------------------------------------------------------------------------
    */

    public function storePayer(Request $request)
    {
        $data = $this->validatePayer($request);

        DB::table('x12_payers')->insert(array_merge(
            $this->normalizePayer($data),
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ));

        return redirect()
            ->route('x12.settings.index', ['tab' => 'payer'])
            ->with('success', 'Payer settings saved successfully.');
    }

    public function updatePayer(Request $request, int $id)
    {
        abort_unless(
            DB::table('x12_payers')->where('id', $id)->exists(),
            404
        );

        $data = $this->validatePayer($request, $id);

        DB::table('x12_payers')
            ->where('id', $id)
            ->update(array_merge(
                $this->normalizePayer($data),
                ['updated_at' => now()]
            ));

        return redirect()
            ->route('x12.settings.index', ['tab' => 'payer'])
            ->with('success', 'Payer settings updated successfully.');
    }

    public function destroyPayer(int $id)
    {
        abort_unless(
            DB::table('x12_payers')->where('id', $id)->exists(),
            404
        );

        DB::table('x12_payers')
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('x12.settings.index', ['tab' => 'payer'])
            ->with('success', 'Payer settings deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateClearinghouse(
        Request $request,
        ?int $id = null
    ): array {
        return $request->validate([
            'setting_name' => ['required', 'string', 'max:100'],
            'setting_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'x12_clearinghouse_settings',
                    'setting_code'
                )->ignore($id),
            ],
            'isa_authorization_qualifier' => ['required', 'string', 'size:2'],
            'isa_authorization_information' => ['nullable', 'string', 'max:10'],
            'isa_security_qualifier' => ['required', 'string', 'size:2'],
            'isa_security_information' => ['nullable', 'string', 'max:10'],
            'isa_sender_qualifier' => ['required', 'string', 'size:2'],
            'isa_sender_id' => ['required', 'string', 'max:15'],
            'isa_receiver_qualifier' => ['required', 'string', 'size:2'],
            'isa_receiver_id' => ['required', 'string', 'max:15'],
            'isa_repetition_separator' => ['required', 'string', 'size:1'],
            'isa_version' => ['required', 'string', 'max:5'],
            'isa_ack_requested' => ['required', Rule::in(['0', '1'])],
            'isa_usage_indicator' => ['required', Rule::in(['T', 'P'])],
            'isa_component_separator' => ['required', 'string', 'size:1'],
            'gs_functional_code' => ['required', 'string', 'max:2'],
            'gs_sender_code' => ['required', 'string', 'max:15'],
            'gs_receiver_code' => ['required', 'string', 'max:15'],
            'gs_responsible_agency_code' => ['required', 'string', 'size:1'],
            'implementation_version' => ['required', 'string', 'max:20'],
            'bht_structure_code' => ['required', 'string', 'max:4'],
            'bht_purpose_code' => ['required', 'string', 'max:2'],
            'bht_transaction_type' => ['required', 'string', 'max:2'],
            'submitter_name' => ['required', 'string', 'max:100'],
            'submitter_entity_type' => ['required', Rule::in(['1', '2'])],
            'submitter_id_qualifier' => ['required', 'string', 'max:2'],
            'submitter_id' => ['required', 'string', 'max:80'],
            'submitter_contact_name' => ['nullable', 'string', 'max:100'],
            'submitter_phone' => ['nullable', 'string', 'max:25'],
            'submitter_email' => ['nullable', 'email', 'max:100'],
            'submitter_fax' => ['nullable', 'string', 'max:25'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'receiver_entity_type' => ['required', Rule::in(['1', '2'])],
            'receiver_id_qualifier' => ['required', 'string', 'max:2'],
            'receiver_transaction_id' => ['required', 'string', 'max:80'],
            'element_separator' => ['required', 'string', 'size:1'],
            'segment_terminator' => ['required', 'string', 'size:1'],
            'line_break_after_segment' => ['nullable'],
            'is_default' => ['nullable'],
            'status' => ['required', Rule::in(['A', 'I'])],
        ]);
    }

    private function validateProvider(
        Request $request,
        ?int $id = null
    ): array {
        return $request->validate([
            'provider_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'x12_provider_settings',
                    'provider_code'
                )->ignore($id),
            ],
            'provider_name' => ['required', 'string', 'max:100'],
            'entity_type_qualifier' => ['required', Rule::in(['1', '2'])],
            'npi' => ['required', 'digits:10'],
            'taxonomy_code' => ['required', 'string', 'max:20'],
            'tax_id_qualifier' => ['required', 'string', 'max:2'],
            'tax_id' => ['required', 'string', 'max:20'],
            'address1' => ['required', 'string', 'max:100'],
            'address2' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:50'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:15'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:25'],
            'fax' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
            'use_as_billing_provider' => ['nullable'],
            'use_as_rendering_provider' => ['nullable'],
            'use_as_service_facility' => ['nullable'],
            'is_default' => ['nullable'],
            'status' => ['required', Rule::in(['A', 'I'])],
        ]);
    }

    private function validatePayer(
        Request $request,
        ?int $id = null
    ): array {
        return $request->validate([
            'hmo_id' => [
                'required',
                'string',
                'max:30',
                Rule::exists('hmodropdown', 'Id'),
                Rule::unique('x12_payers', 'hmo_id')->ignore($id),
            ],
            'payer_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('x12_payers', 'payer_code')->ignore($id),
            ],
            'payer_name' => ['required', 'string', 'max:100'],
            'payer_identifier_qualifier' => ['required', 'string', 'max:2'],
            'payer_identifier' => ['required', 'string', 'max:80'],
            'address1' => ['nullable', 'string', 'max:100'],
            'address2' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:50'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'max:15'],
            'claim_filing_indicator_code' => ['required', 'string', 'max:5'],
            'subscriber_relationship_code' => ['required', 'string', 'max:2'],
            'place_of_service_code' => ['required', 'string', 'max:2'],
            'claim_frequency_code' => ['required', 'string', 'max:1'],
            'provider_signature_indicator' => ['required', Rule::in(['Y', 'N'])],
            'assignment_participation_code' => ['required', 'string', 'max:1'],
            'benefits_assignment_indicator' => ['required', Rule::in(['Y', 'N'])],
            'release_information_code' => ['required', 'string', 'max:1'],
            'include_payer_address' => ['nullable'],
            'include_payer_taxonomy_ref' => ['nullable'],
            'include_rendering_provider' => ['nullable'],
            'include_service_facility' => ['nullable'],
            'status' => ['required', Rule::in(['A', 'I'])],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Data normalization
    |--------------------------------------------------------------------------
    */

    private function normalizeClearinghouse(array $data): array
    {
        foreach ([
            'setting_code',
            'isa_authorization_qualifier',
            'isa_security_qualifier',
            'isa_sender_qualifier',
            'isa_sender_id',
            'isa_receiver_qualifier',
            'isa_receiver_id',
            'isa_version',
            'isa_ack_requested',
            'isa_usage_indicator',
            'gs_functional_code',
            'gs_sender_code',
            'gs_receiver_code',
            'gs_responsible_agency_code',
            'implementation_version',
            'bht_structure_code',
            'bht_purpose_code',
            'bht_transaction_type',
            'submitter_id_qualifier',
            'submitter_id',
            'receiver_id_qualifier',
            'receiver_transaction_id',
            'status',
        ] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = strtoupper(trim($data[$field]));
            }
        }

        $data['isa_authorization_information'] =
            trim($data['isa_authorization_information'] ?? '');

        $data['isa_security_information'] =
            trim($data['isa_security_information'] ?? '');

        $data['line_break_after_segment'] =
            ! empty($data['line_break_after_segment']) ? 1 : 0;

        $data['is_default'] =
            ! empty($data['is_default']) ? 1 : 0;

        return $data;
    }

    private function normalizeProvider(array $data): array
    {
        foreach ([
            'provider_code',
            'taxonomy_code',
            'tax_id_qualifier',
            'tax_id',
            'state',
            'status',
        ] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = strtoupper(trim($data[$field]));
            }
        }

        $data['npi'] = preg_replace('/\D/', '', (string) $data['npi']);

        $data['use_as_billing_provider'] =
            ! empty($data['use_as_billing_provider']) ? 1 : 0;

        $data['use_as_rendering_provider'] =
            ! empty($data['use_as_rendering_provider']) ? 1 : 0;

        $data['use_as_service_facility'] =
            ! empty($data['use_as_service_facility']) ? 1 : 0;

        $data['is_default'] =
            ! empty($data['is_default']) ? 1 : 0;

        return $data;
    }

    private function normalizePayer(array $data): array
    {
        foreach ([
            'payer_code',
            'payer_identifier_qualifier',
            'payer_identifier',
            'state',
            'claim_filing_indicator_code',
            'subscriber_relationship_code',
            'place_of_service_code',
            'claim_frequency_code',
            'provider_signature_indicator',
            'assignment_participation_code',
            'benefits_assignment_indicator',
            'release_information_code',
            'status',
        ] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = strtoupper(trim($data[$field]));
            }
        }

        foreach ([
            'include_payer_address',
            'include_payer_taxonomy_ref',
            'include_rendering_provider',
            'include_service_facility',
        ] as $field) {
            $data[$field] = ! empty($data[$field]) ? 1 : 0;
        }

        return $data;
    }
}
