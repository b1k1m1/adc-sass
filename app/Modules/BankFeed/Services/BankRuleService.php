<?php

namespace App\Modules\BankFeed\Services;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Modules\BankFeed\Models\BankImport;
use App\Modules\BankFeed\Models\BankRule;
class BankRuleService
{
    /**
     * Apply all rules to one transaction.
     *
     * @param  object  $txn  (bank_imports row)
     * @param  \Illuminate\Support\Collection  $rules
     * @return array{
     *     hits: array<int, array{rule_id:int, label:string}>,
     *     suggested_gl_account_id: int|null
     * }
     */
    public static function applyToTransaction(object $txn, Collection $rules): array
    {
        $description = (string) ($txn->description ?? '');
        $amount      = (float) ($txn->amount ?? 0);

        $direction = $amount < 0 ? 'D' : ($amount > 0 ? 'C' : 'Any');

        $hits = [];
        $suggestedAccountId = null;

        foreach ($rules as $rule) {
            // Check direction
            if ($rule->direction !== 'Any' && $rule->direction !== $direction) {
                continue;
            }

            // Check pattern
            if (! self::matches($description, $rule->pattern_type, $rule->pattern_value)) {
                continue;
            }

            // Rule hit
            $hits[] = [
                'rule_id' => $rule->id,
                'label'   => sprintf(
                    '%s "%s"',
                    strtoupper($rule->pattern_type),
                    $rule->pattern_value
                ),
            ];

            // First hit wins for category (because rules are ordered by priority)
            if ($suggestedAccountId === null && ! empty($rule->gl_account_id)) {
                $suggestedAccountId = (int) $rule->gl_account_id;
            }
        }

        return [
            'hits' => $hits,
            'suggested_gl_account_id' => $suggestedAccountId,
        ];

    } // End Method

    protected static function matches(string $description, string $type, string $pattern): bool
    {
        $desc = Str::lower($description);
        $pat  = Str::lower($pattern);

        switch ($type) {
            case 'starts_with':
                return Str::startsWith($desc, $pat);

            case 'ends_with':
                return Str::endsWith($desc, $pat);

            case 'equals':
                return trim($desc) === trim($pat);

            case 'regex':
                // Guard bad regex
                if (@preg_match($pattern, '') === false) {
                    return false;
                }
                return (bool) preg_match($pattern, $description);

            case 'contains':
            default:
                return Str::contains($desc, $pat);
        }

    } // End Method
}
