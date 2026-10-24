<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    private array $allowedLocales = ['en', 'ko', 'es'];

    public function index(Request $request)
    {
        $locale = $request->get('locale', session('locale', 'en'));
        if (!in_array($locale, $this->allowedLocales)) {
            $locale = 'en';
        }

        $q = trim((string) $request->get('q', ''));

        $query = DB::table('translations')
            ->where('locale', $locale);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('key', 'like', "%{$q}%")
                    ->orWhere('value', 'like', "%{$q}%");
            });
        }

        $rows = $query->orderBy('key')->paginate(25)->withQueryString();

        return view('admin.translations.index', [
            'rows' => $rows,
            'locale' => $locale,
            'q' => $q,
            'allowedLocales' => $this->allowedLocales,
        ]);

    } // End Method

    public function store(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10', function ($attr, $value, $fail) {
                if (!in_array($value, $this->allowedLocales)) {
                    $fail('Invalid locale selected.');
                }
            }],
            'key' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        // Normalize key (optional but helpful)
        $validated['key'] = trim($validated['key']);

        // Prevent duplicates: updateOrInsert for (locale, key)
        DB::table('translations')->updateOrInsert(
            ['locale' => $validated['locale'], 'key' => $validated['key']],
            [
                'value' => $validated['value'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Cache::forget('tr_map_' . $validated['locale']);

        return redirect()
            ->route('translations.index', ['locale' => $validated['locale']])
            ->with('success', 'Translation saved.');

    } // End Method

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'value' => ['nullable', 'string'],
        ]);

        // Fetch row to know its locale (so we can clear cache)
        $row = DB::table('translations')->where('id', $id)->first();
        if (!$row) {
            return redirect()->back()->with('error', 'Translation not found.');
        }

        DB::table('translations')
            ->where('id', $id)
            ->update([
                'value' => $validated['value'],
                'updated_at' => now(),
            ]);

        Cache::forget('tr_map_' . $row->locale);

        return redirect()->back()->with('success', 'Translation updated.');

    } // End Method

}
