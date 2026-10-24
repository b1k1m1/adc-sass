<?php

namespace App\Modules\BankFeed\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\BankFeed\Services\BankFeedService;
use App\Modules\BankFeed\Services\BankRulesService;
use App\Modules\BankFeed\Models\BankImport;
use App\Modules\BankFeed\Models\BankRule;

class BankRuleController extends Controller
{

    /**
     * List rules + show "Add New Rule" form.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1) Bank dropdown data
        |--------------------------------------------------------------------------
        */
        $banks = DB::table('fashmsbank')
            ->orderBy('account_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2) Optional filter by bank
        |--------------------------------------------------------------------------
        */
        $filterBankId = $request->input('bank_id');

        /*
        |--------------------------------------------------------------------------
        | 3) Base query on bank_rules only
        |--------------------------------------------------------------------------
        */
        $rulesQuery = DB::table('bank_rules')
            ->orderBy('bank_id')
            ->orderBy('priority')
            ->orderBy('id');

        if ($filterBankId) {
            $rulesQuery->where('bank_id', $filterBankId);
        }

        $rules = $rulesQuery->paginate(50)->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | 4) Build bank lookup map
        |--------------------------------------------------------------------------
        */
        $bankMap = $banks->keyBy('account_id');

        /*
        |--------------------------------------------------------------------------
        | 5) Load only account names needed for current page rules
        |--------------------------------------------------------------------------
        */
        $partyCodes = $rules->getCollection()
            ->pluck('party_cd')
            ->filter()
            ->unique()
            ->values();

        $accountMap = collect();

        if ($partyCodes->count() > 0) {
            $accounts = DB::table('accountmasters')
                ->select('party_cd', 'party_nm')
                ->whereIn('party_cd', $partyCodes)
                ->get();

            $accountMap = $accounts->keyBy('party_cd');
        }

        /*
        |--------------------------------------------------------------------------
        | 6) Decorate each rule with bank_name + party_name
        |--------------------------------------------------------------------------
        */
        $rules->getCollection()->transform(function ($rule) use ($bankMap, $accountMap) {
            $bank = $bankMap->get($rule->bank_id);
            $account = $accountMap->get($rule->party_cd);

            $rule->bank_name = $bank ? $bank->account_name : null;
            $rule->party_name = $account ? $account->party_nm : null;

            return $rule;
        });

        return view('bank_feed.bank_rules.index', compact(
            'rules',
            'banks',
            'filterBankId'
        ));

    } // End Method

    /**
     * Store a new rule from the maintenance screen.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_id'       => ['nullable', 'integer'],
            'pattern_type'  => ['required', 'in:starts_with,ends_with,contains,regex,equals'],
            'bank_category' => ['nullable', 'string', 'max:100'],
            'pattern_value' => ['required', 'string'],
            'direction'     => ['required', 'in:D,C,Any'],
            'party_cd'      => ['nullable', 'string', 'max:20'],
            'priority'      => ['nullable', 'integer'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $priority = $data['priority'] ?? 100;
        $isActive = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        DB::table('bank_rules')->insert([
            'bank_id'       => !empty($data['bank_id']) ? $data['bank_id'] : null,
            'pattern_type'  => $data['pattern_type'],
            'pattern_value' => $data['pattern_value'],
            'bank_category' => $data['bank_category'] ?? null,
            'pattern'       => $data['pattern_value'],   // legacy column
            'direction'     => $data['direction'],
            'party_cd'      => !empty($data['party_cd']) ? $data['party_cd'] : null,
            'priority'      => $priority,
            'is_active'     => $isActive ? 1 : 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()
            ->route('bank-rules.index', ['bank_id' => $data['bank_id'] ?? null])
            ->with('message', 'Rule created successfully.');

    } // End Method

    /**
     * Show edit form for a rule.
     */
    public function edit($id)
    {
        $rule = DB::table('bank_rules')->where('id', $id)->first();
        if (!$rule) {
            abort(404, 'Rule not found');
        }

        $banks = DB::table('fashmsbank')
            ->orderBy('account_name')
            ->get();

        $accounts = DB::table('accountmasters')
            ->orderBy('party_cd')
            ->get();

        return view('bank_feed.bank_rules.edit', compact(
            'rule',
            'banks',
            'accounts'
        ));

    } // End Method

    /**
     * Update a rule from the maintenance screen.
     */
    public function update(Request $request, $id)
    {
        $rule = DB::table('bank_rules')->where('id', $id)->first();
        if (!$rule) {
            abort(404, 'Rule not found');
        }

        $data = $request->validate([
            'bank_id'       => ['nullable', 'integer'],
            'pattern_type'  => ['required', 'in:starts_with,ends_with,contains,regex,equals'],
            'bank_category' => ['nullable', 'string'],
            'pattern_value' => ['required', 'string'],
            'direction'     => ['required', 'in:D,C,Any'],
            'party_cd'      => ['nullable', 'string', 'max:20'],
            'priority'      => ['nullable', 'integer'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $priority  = $data['priority'] ?? 100;
        $isActive  = isset($data['is_active']) ? (bool)$data['is_active'] : false;

        DB::table('bank_rules')
            ->where('id', $id)
            ->update([
                'bank_id'       => $data['bank_id'] ?: null,
                'pattern_type'  => $data['pattern_type'],
                'bank_category' => $data['bank_category'],
                'pattern_value' => $data['pattern_value'],
                'pattern'       => $data['pattern_value'],   // keep in sync
                'direction'     => $data['direction'],
                'party_cd'      => $data['party_cd'] ?: null,
                'priority'      => $priority,
                'is_active'     => $isActive ? 1 : 0,
                'updated_at'    => now(),
            ]);

        return redirect()
            ->route('bank-rules.index', ['bank_id' => $data['bank_id'] ?? null])
            ->with('message', 'Rule updated successfully.');

    } // End Method

    /**
     * Create a bank rule from a sample transaction (Learn button).
     * (Already wired from the grid.)
     */
    public function storeFromTransaction(Request $request)
    {
        $data = $request->validate([
            'bank_id'       => ['nullable', 'integer'],
            'pattern_type'  => ['required', 'in:starts_with,ends_with,contains,regex,equals'],
            'pattern_value' => ['required', 'string'],
            'direction'     => ['required', 'in:D,C,Any'],
            'party_cd'      => ['nullable', 'string', 'max:20'],
            'priority'      => ['nullable', 'integer'],
        ]);

        $priority = $data['priority'] ?? 100;


        $ruleId = DB::table('bank_rules')->insertGetId([
            'bank_id'       => $data['bank_id'] ?: null,
            'pattern_type'  => $data['pattern_type'],
            'pattern_value' => $data['pattern_value'],
            'pattern'       => $data['pattern_value'],   // legacy column
            'direction'     => $data['direction'],
            'party_cd'      => $data['party_cd'] ?: null,
            'priority'      => $priority,
            'is_active'     => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'status'  => 'ok',
            'rule_id' => $ruleId,
            'message' => 'Rule created successfully.',
        ]);

    } //End Method


    public function BankRulesAccountSearch(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $query = DB::table('accountmasters')
            ->select('party_cd', 'party_nm');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('party_cd', 'like', '%' . $search . '%')
                ->orWhere('party_nm', 'like', '%' . $search . '%');
            });
        }

        $accounts = $query
            ->orderBy('party_nm')
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $accounts->map(function ($account) {
                return [
                    'id'   => $account->party_cd,
                    'text' => $account->party_cd . ' - ' . $account->party_nm,
                ];
            }),
        ]);

    } // End Method
}
