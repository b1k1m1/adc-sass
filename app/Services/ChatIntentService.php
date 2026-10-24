<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ChatIntentService
{
    private AIService $aiService;
    private TableSchemaService $schemaService;
    private TableAccessValidator $validator;

    private const MAX_ROWS = 100;

    private array $dataKeywords = [
        'member', 'participant', 'attendance', 'attended', 'absent',
        'vital', 'vitals', 'bp', 'blood pressure', 'sugar', 'glucose',
        'weight', 'pulse', 'temperature', 'fever',
        'birthday', 'born', 'dob', 'date of birth',
        'expire', 'expiring', 'expiration', 'pa ', 'hnp',
        'shift', 'morning', 'noon', 'afternoon',
        'today', 'yesterday', 'tomorrow', 'this week', 'last week',
        'this month', 'last month', 'next week', 'next month',
        'how many', 'count', 'total', 'list', 'show', 'get',
        'who', 'which', 'all', 'male', 'female',
        'eat', 'eating', 'toilet', 'toileting', 'transfer', 'locomotion',
        'adl', 'daily living', 'assess',
    ];

    private string $fallbackMessage = "I can help you with:\n" .
        "- Member information (count, birthdays, shift details)\n" .
        "- Attendance records (daily, weekly, monthly)\n" .
        "- Vitals (BP, sugar, weight, pulse)\n" .
        "- PA/HNP expiration tracking\n" .
        "Try asking: 'How many members in morning shift?' or 'Show attendance this week'";

    private function getDefaultShift(string $query): ?string
    {
        $lowerQuery = strtolower($query);
        if (str_contains($lowerQuery, 'noon')) {
            return 'N';
        }
        return 'A';
    }

    public function __construct(
        AIService $aiService,
        TableSchemaService $schemaService,
        TableAccessValidator $validator
    ) {
        $this->aiService = $aiService;
        $this->schemaService = $schemaService;
        $this->validator = $validator;
    }

    public function shouldUseAI(string $query): bool
    {
        $lowerQuery = strtolower(trim($query));

        foreach ($this->dataKeywords as $keyword) {
            if (str_contains($lowerQuery, $keyword)) {
                return true;
            }
        }

        $questionPatterns = [
            '/^(what|who|which|how|show|list|get|find|tell)/i',
            '/\?$/',
        ];

        foreach ($questionPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                if ($this->isDataRelated($query)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isDataRelated(string $query): bool
    {
        $lowerQuery = strtolower($query);

        $dataIndicators = [
            'member', 'participant', 'attendance', 'vital', 'bp', 'shift',
            'birthday', 'id ', 'show', 'list', 'get', 'display', 'find',
            'dob', 'date of birth', 'dateofbirth'
        ];

        foreach ($dataIndicators as $indicator) {
            if (str_contains($lowerQuery, $indicator)) {
                return true;
            }
        }

        return false;
    }

    public function processWithAI(string $query): array
    {
        if (!$this->validator->isAllowedQuery($query)) {
            return [
                'response' => "I can only help with member-related questions.",
                'type' => 'fallback',
            ];
        }

        // Check if AI is enabled
        if (config('services.ai_enabled', false)) {
            return $this->processWithOpenRouter($query);
        }

        // Default: use direct database queries (faster, free)
        try {
            $result = $this->executeQuery($query);
            return $result;

        } catch (\Exception $e) {
            return [
                'response' => "I'm having trouble processing your request. " . $this->fallbackMessage,
                'type' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function processWithOpenRouter(string $query): array
    {
        try {
            $response = $this->aiService->askWithSystemPrompt(
                $this->getSystemPrompt(),
                $query
            );

            if (isset($response['error'])) {
                // Fallback to direct query on AI error
                return $this->executeQuery($query);
            }

            // Extract AI response content
            $content = $this->extractAIResponse($response);

            // Try to parse AI response as JSON (structured query)
            $parsed = null;
            if (preg_match('/\{[\s\S]*\}/', $content, $match)) {
                $parsed = json_decode($match[0], true);
            }

            // If AI returned structured query, execute it directly
            if ($parsed && isset($parsed['table']) && isset($parsed['filters'])) {
                $result = $this->executeStructuredQuery($parsed['table'], $parsed['filters'], $query);
                if ($result) {
                    $result['ai_powered'] = true;
                    return $result;
                }
            }

            // Fallback to direct query execution
            $result = $this->executeQuery($query);
            $result['ai_powered'] = true;
            return $result;

        } catch (\Exception $e) {
            // Fallback to direct queries
            $result = $this->executeQuery($query);
            $result['ai_powered'] = true;
            return $result;
        }
    }

    private function executeStructuredQuery(string $table, array $filters, string $originalQuery): ?array
    {
        $lowerQuery = strtolower($originalQuery);

        // Only handle members table for now
        if (strtolower($table) !== 'members') {
            return null;
        }

        $queryBuilder = DB::table('members')
            ->select('ParticipantId', 'FirstName', 'LastName', 'Sex', 'DateOfBirth')
            ->selectRaw("YEAR(CURDATE()) - YEAR(DateOfBirth) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(DateOfBirth, '%m%d')) as Age");

        // Apply filters
        if (isset($filters['Sex'])) {
            $queryBuilder->where('Sex', $filters['Sex']);
        }
        if (isset($filters['ParticipantId'])) {
            $queryBuilder->where('ParticipantId', $filters['ParticipantId']);
        }

        // Handle ADL filters
        $adlFields = ['EatingYN', 'BathingYN', 'ToiletYN', 'TransferYN', 'LocomotionYN'];
        foreach ($adlFields as $field) {
            $dbField = strtolower($field[0]) . substr($field, 1);
            if (isset($filters[$field]) && $filters[$field] === 'Y') {
                $queryBuilder->where($dbField, 'Y');
            }
        }

        $members = $queryBuilder->limit(self::MAX_ROWS)->get();

        if ($members->isEmpty()) {
            return null;
        }

        $totalCount = DB::table('members')->count();
        $truncated = $totalCount > self::MAX_ROWS;

        $data = $members->map(function ($m) {
            return [
                'ParticipantId' => $m->ParticipantId,
                'Name' => trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? '')) ?: 'N/A',
                'Age' => $m->Age ?? 'N/A',
                'Sex' => $m->Sex ?? 'N/A',
            ];
        })->toArray();

        return [
            'ok' => true,
            'type' => 'table',
            'title' => 'Members (AI-powered query)',
            'columns' => ['ParticipantId', 'Name', 'Age', 'Sex'],
            'data' => $data,
            'truncated' => $truncated,
            'total_count' => $totalCount,
        ];
    }

    private function executeQuery(string $query): array
    {
        $lowerQuery = strtolower(trim($query));

        // Members queries
        if ($this->isMembersQuery($lowerQuery)) {
            return $this->handleMembersQuery($query, $lowerQuery);
        }

        // Attendance queries
        if ($this->isAttendanceQuery($lowerQuery)) {
            return $this->handleAttendanceQuery($query, $lowerQuery);
        }

        // Vitals queries
        if ($this->isVitalsQuery($lowerQuery)) {
            return $this->handleVitalsQuery($query, $lowerQuery);
        }

        // ADL queries
        if ($this->isADLQuery($lowerQuery)) {
            return $this->handleADLQuery($query, $lowerQuery);
        }

        return [
            'response' => "I'm not sure how to answer that. " . $this->fallbackMessage,
            'type' => 'unsupported',
        ];
    }

    private function isMembersQuery(string $query): bool
    {
        $keywords = [
            'member', 'participant', 'birthday', 'dob', 'date of birth', 'dateofbirth',
            'male', 'female', 'shift', 'pa ', 'hnp', 'expir', 'name', 'first', 'last',
            'id ', 'show', 'list', 'get', 'details', 'info', 'information'
        ];
        foreach ($keywords as $keyword) {
            if (str_contains($query, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function isAttendanceQuery(string $query): bool
    {
        return str_contains($query, 'attendance') || str_contains($query, 'attended') || str_contains($query, 'absent');
    }

    private function isVitalsQuery(string $query): bool
    {
        $keywords = ['bp', 'blood pressure', 'sugar', 'glucose', 'weight', 'pulse', 'temperature', 'vital'];
        foreach ($keywords as $keyword) {
            if (str_contains($query, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function isADLQuery(string $query): bool
    {
        $keywords = ['adl', 'eating', 'toilet', 'toileting', 'transfer', 'locomotion', 'daily living'];
        foreach ($keywords as $keyword) {
            if (str_contains($query, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function handleMembersQuery(string $query, string $lowerQuery): array
    {
        // Check for age-based queries FIRST (before participant ID)
        $hasAgePattern = preg_match('/over\s*(\d+)/i', $query) || preg_match('/(\d+)\s*years?\s*old/i', $query);
        $hasAdlKeyword = str_contains($lowerQuery, 'assistance') || str_contains($lowerQuery, 'help') || str_contains($lowerQuery, 'need');

        // Only extract participant ID if there's no age pattern AND no ADL keywords
        // This prevents "over 70" from being interpreted as ID 70
        $participantId = null;
        if (!$hasAgePattern && !$hasAdlKeyword) {
            $participantId = $this->extractParticipantId($query);
        }

        // Show specific member by ID (only if not an age/adl query)
        if ($participantId && !$hasAgePattern && !$hasAdlKeyword) {
            $member = DB::table('members')
                ->where('ParticipantId', $participantId)
                ->orWhereRaw("RIGHT('000' + ParticipantId, 3) = ?", [$participantId])
                ->first();

            if ($member) {
                return [
                    'ok' => true,
                    'type' => 'table',
                    'title' => "Member Details for ID: {$participantId}",
                    'columns' => ['Field', 'Value'],
                    'data' => [
                        ['Field' => 'Participant ID', 'Value' => $member->ParticipantId],
                        ['Field' => 'Name', 'Value' => trim(($member->FirstName ?? '') . ' ' . ($member->LastName ?? ''))],
                        ['Field' => 'Date of Birth', 'Value' => $member->DateOfBirth ?? 'N/A'],
                        ['Field' => 'Sex', 'Value' => $member->Sex ?? 'N/A'],
                        ['Field' => 'Shift', 'Value' => ($member->Trans_flag === 'A' ? 'Morning' : 'Noon')],
                        ['Field' => 'PA Expires', 'Value' => $member->medicaid_PA_End ?? 'N/A'],
                        ['Field' => 'HNP Expires', 'Value' => $member->HNP_Expiry_date ?? 'N/A'],
                    ],
                ];
            } else {
                return [
                    'response' => "No member found with ID: {$participantId}",
                    'type' => 'not_found',
                ];
            }
        }

        // Count by shift (with optional age filter)
        if (str_contains($lowerQuery, 'how many') || str_contains($lowerQuery, 'count')) {
            $shift = $this->getDefaultShift($query);

            // Check for age pattern
            $age = null;
            if (preg_match('/over\s*(\d+)/i', $query, $ageMatch)) {
                $age = (int) $ageMatch[1];
            } elseif (preg_match('/(\d+)\s*years?\s*old/i', $query, $ageMatch)) {
                $age = (int) $ageMatch[1];
            }

            // Calculate birth year threshold
            $birthYearMax = date('Y') - $age;
            
            $queryBuilder = DB::table('members')
                ->where('Trans_flag', $shift)
                ->whereRaw("YEAR(DateOfBirth) BETWEEN 1900 AND 2100");
            
            if ($age) {
                $queryBuilder->whereRaw("YEAR(DateOfBirth) < ?", [$birthYearMax]);
            }

            $maleCount = (clone $queryBuilder)->where('Sex', 'M')->count();
            $femaleCount = (clone $queryBuilder)->where('Sex', 'F')->count();
            $total = $maleCount + $femaleCount;

            $shiftLabel = $shift === 'A' ? 'Morning' : 'Noon';
            $ageLabel = $age ? " over {$age}" : '';
            $title = "Member Count - {$shiftLabel} Shift{$ageLabel}";

            return [
                'ok' => true,
                'type' => 'counts',
                'title' => $title,
                'data' => [
                    ['Category' => 'Male', 'Count' => $maleCount],
                    ['Category' => 'Female', 'Count' => $femaleCount],
                    ['Category' => 'Total', 'Count' => $total],
                ],
            ];
        }

        // Birthday queries
        if (str_contains($lowerQuery, 'birthday')) {
            $month = (int) date('n');
            $monthName = date('F');

            if (preg_match('/\b(january|february|march|april|may|june|july|august|september|october|november|december)\b/i', $query, $m)) {
                $monthName = ucfirst(strtolower($m[1]));
                $month = date('n', strtotime($monthName . ' 1'));
            }

            $shift = $this->getDefaultShift($query);

            $members = DB::table('members')
                ->select('ParticipantId', 'FirstName', 'LastName', 'DateOfBirth')
                ->whereRaw("MONTH(DateOfBirth) = ?", [$month])
                ->where('Trans_flag', $shift)
                ->orderByRaw("DAY(DateOfBirth)")
                ->limit(self::MAX_ROWS)
                ->get();

            if ($members->isEmpty()) {
                return [
                    'response' => "No members with birthdays in {$monthName}",
                    'type' => 'not_found',
                ];
            }

            $data = $members->map(function ($m) {
                $fullName = trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? ''));
                return [
                    'ParticipantId' => $m->ParticipantId,
                    'Name' => $fullName ?: 'N/A',
                    'DateOfBirth' => $m->DateOfBirth ?? 'N/A',
                ];
            })->toArray();

            $totalCount = DB::table('members')
                ->whereRaw("MONTH(DateOfBirth) = ?", [$month])
                ->where('Trans_flag', $shift)
                ->count();

            $truncated = $totalCount > self::MAX_ROWS;
            $shiftLabel = $shift === 'A' ? 'Morning' : 'Noon';
            $title = "Members with Birthdays in {$monthName} ({$shiftLabel} Shift)";
            if ($truncated) {
                $title .= " (Showing " . self::MAX_ROWS . " of {$totalCount})";
            }

            return [
                'ok' => true,
                'type' => 'table',
                'title' => $title,
                'columns' => ['ParticipantId', 'Name', 'DateOfBirth'],
                'data' => $data,
                'truncated' => $truncated,
                'total_count' => $totalCount,
            ];
        }

        // List all members
        if (str_contains($lowerQuery, 'all member') || str_contains($lowerQuery, 'list member')) {
            $sortBy = 'ParticipantId';
            $sortOrder = 'asc';
            if (str_contains($lowerQuery, 'descending') || str_contains($lowerQuery, 'z to a')) {
                $sortOrder = 'desc';
            }

            $shift = $this->getDefaultShift($query);

            $members = DB::table('members')
                ->select('ParticipantId', 'FirstName', 'LastName', 'Trans_flag')
                ->where('Trans_flag', $shift)
                ->orderBy($sortBy, $sortOrder)
                ->limit(self::MAX_ROWS)
                ->get();

            $totalCount = DB::table('members')->where('Trans_flag', $shift)->count();

            $shiftLabel = $shift === 'A' ? 'Morning' : 'Noon';
            $truncated = $totalCount > self::MAX_ROWS;

            $data = $members->map(function ($m) {
                return [
                    'ParticipantId' => $m->ParticipantId,
                    'Name' => trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? '')) ?: 'N/A',
                    'Shift' => ($m->Trans_flag === 'A' ? 'Morning' : 'Noon'),
                ];
            })->toArray();

            $title = $truncated ? "All {$shiftLabel} Shift Members (Showing " . self::MAX_ROWS . " of {$totalCount})" : "All {$shiftLabel} Shift Members";

            return [
                'ok' => true,
                'type' => 'table',
                'title' => $title,
                'columns' => ['ParticipantId', 'Name', 'Shift'],
                'data' => $data,
                'truncated' => $truncated,
                'total_count' => $totalCount,
            ];
        }

        // Age-based queries (members over X years old)
        if (preg_match('/over\s*(\d+)/i', $query, $ageMatch) || preg_match('/(\d+)\s*years?\s*old/i', $query, $ageMatch)) {
            $age = (int) $ageMatch[1];

            // Check if query also contains ADL keywords
            $adlFieldMap = [
                'eating' => 'eating_yn',
                'bath' => 'bathing_yn',
                'toilet' => 'toilet_yn',
                'transfer' => 'transfer_yn',
                'locomotion' => 'locomotion_yn',
                'bed_mobility' => 'bed_mobility_yn',
            ];

            $selectedAdlFields = [];
            foreach ($adlFieldMap as $keyword => $field) {
                if (str_contains($lowerQuery, $keyword)) {
                    $selectedAdlFields[] = $field;
                }
            }

            // Sort options (default by ParticipantId)
            $sortBy = 'ParticipantId';
            $sortOrder = 'asc';
            if (str_contains($lowerQuery, 'descending') || str_contains($lowerQuery, 'z to a') || str_contains($lowerQuery, 'oldest first')) {
                $sortOrder = 'desc';
            }
            if (str_contains($lowerQuery, 'youngest first') || str_contains($lowerQuery, 'a to z')) {
                $sortOrder = 'asc';
            }
            if (str_contains($lowerQuery, 'by age')) {
                $sortBy = 'Age';
            }

            $shift = $this->getDefaultShift($query);

            // Filter valid dates (year between 1900 and 2100) to exclude bad data
            // For "over X" - someone born in current_year - X turns X this year, so we need < (not <=)
            $birthYearMax = date('Y') - $age;

            $queryBuilder = DB::table('members')
                ->select('ParticipantId', 'FirstName', 'LastName', 'DateOfBirth', 'Sex')
                ->selectRaw("YEAR(CURDATE()) - YEAR(DateOfBirth) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(DateOfBirth, '%m%d')) as Age")
                ->whereRaw("YEAR(DateOfBirth) BETWEEN 1900 AND 2100")
                ->where('Trans_flag', $shift)
                ->whereRaw("YEAR(DateOfBirth) < ?", [$birthYearMax]);

            // Add ADL filters if present
            foreach ($selectedAdlFields as $field) {
                $queryBuilder->where($field, 'Y');
            }

            $members = $queryBuilder->orderBy($sortBy, $sortOrder)->limit(self::MAX_ROWS)->get();

            $countBuilder = DB::table('members')
                ->selectRaw("YEAR(CURDATE()) - YEAR(DateOfBirth) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(DateOfBirth, '%m%d')) as Age")
                ->whereRaw("YEAR(DateOfBirth) BETWEEN 1900 AND 2100")
                ->where('Trans_flag', $shift)
                ->whereRaw("YEAR(DateOfBirth) < ?", [$birthYearMax]);

            foreach ($selectedAdlFields as $field) {
                $countBuilder->where($field, 'Y');
            }

            $totalCount = $countBuilder->count();

            $shiftLabel = $shift === 'A' ? 'Morning' : 'Noon';

            $truncated = $totalCount > self::MAX_ROWS;

            $adlLabel = '';
            if (!empty($selectedAdlFields)) {
                $adlNames = array_map(fn($f) => ucfirst(str_replace('_yn', '', $f)), $selectedAdlFields);
                $adlLabel = ' needing ' . implode('/', $adlNames) . ' assistance';
            }

            $title = "{$shiftLabel} Shift Members over {$age} years{$adlLabel}";
            if ($truncated) {
                $title .= " (Showing " . self::MAX_ROWS . " of {$totalCount})";
            } else {
                $title .= " ({$totalCount} found)";
            }

            $data = $members->map(function ($m) {
                return [
                    'ParticipantId' => $m->ParticipantId,
                    'Name' => trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? '')) ?: 'N/A',
                    'Age' => $m->Age ?? 'N/A',
                    'Sex' => $m->Sex ?? 'N/A',
                ];
            })->toArray();

            return [
                'ok' => true,
                'type' => 'table',
                'title' => $title,
                'columns' => ['ParticipantId', 'Name', 'Age', 'Sex'],
                'data' => $data,
                'truncated' => $truncated,
                'total_count' => $totalCount,
            ];
        }

        // ADL assistance queries
        $adlField = null;
        $adlFieldMap = [
            'eating' => 'eating_yn',
            'bath' => 'bathing_yn',
            'toilet' => 'toilet_yn',
            'transfer' => 'transfer_yn',
            'locomotion' => 'locomotion_yn',
            'bed_mobility' => 'bed_mobility_yn',
        ];

        foreach ($adlFieldMap as $keyword => $field) {
            if (str_contains($lowerQuery, $keyword)) {
                $adlField = $field;
                break;
            }
        }

        if ($adlField) {
            // Sort options
            $sortBy = 'ParticipantId';
            $sortOrder = 'asc';
            if (str_contains($lowerQuery, 'descending') || str_contains($lowerQuery, 'z to a')) {
                $sortOrder = 'desc';
            }

            $shift = $this->getDefaultShift($query);

            $members = DB::table('members')
                ->select('ParticipantId', 'FirstName', 'LastName', 'Sex', $adlField)
                ->where($adlField, 'Y')
                ->where('Trans_flag', $shift)
                ->orderBy($sortBy, $sortOrder)
                ->limit(self::MAX_ROWS)
                ->get();

            $totalCount = DB::table('members')
                ->where($adlField, 'Y')
                ->where('Trans_flag', $shift)
                ->count();

            $shiftLabel = $shift === 'A' ? 'Morning' : 'Noon';

            $truncated = $totalCount > self::MAX_ROWS;
            $fieldLabel = ucfirst(str_replace('_yn', '', $adlField));
            $title = $truncated
                ? "{$shiftLabel} Shift - Members needing {$fieldLabel} assistance (Showing " . self::MAX_ROWS . " of {$totalCount})"
                : "{$shiftLabel} Shift - Members needing {$fieldLabel} assistance ({$totalCount} found)";

            $data = $members->map(function ($m) use ($fieldLabel) {
                return [
                    'ParticipantId' => $m->ParticipantId,
                    'Name' => trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? '')) ?: 'N/A',
                    'Sex' => $m->Sex ?? 'N/A',
                    $fieldLabel => 'Yes',
                ];
            })->toArray();

            return [
                'ok' => true,
                'type' => 'table',
                'title' => $title,
                'columns' => ['ParticipantId', 'Name', 'Sex', $fieldLabel],
                'data' => $data,
                'truncated' => $truncated,
                'total_count' => $totalCount,
            ];
        }

        // Combined age + ADL query
        if (preg_match('/over\s*(\d+)/i', $query) && ($adlField || str_contains($lowerQuery, 'assistance') || str_contains($lowerQuery, 'help'))) {
            $age = (int) (preg_match('/over\s*(\d+)/i', $query, $ageMatch) ? $ageMatch[1] : 65);

            $adlConditions = [];
            foreach ($adlFieldMap as $keyword => $field) {
                if (str_contains($lowerQuery, $keyword)) {
                    $adlConditions[] = $field;
                }
            }

            if (empty($adlConditions)) {
                $adlConditions = ['eating_yn', 'bathing_yn', 'toilet_yn', 'transfer_yn', 'locomotion_yn', 'bed_mobility_yn'];
            }

            $shift = $this->getDefaultShift($query);
            $birthYearMax = date('Y') - $age;

            $queryBuilder = DB::table('members')
                ->select('ParticipantId', 'FirstName', 'LastName', 'Sex')
                ->selectRaw("YEAR(CURDATE()) - YEAR(DateOfBirth) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(DateOfBirth, '%m%d')) as Age");

            foreach ($adlConditions as $field) {
                $queryBuilder->addSelect($field);
            }

            $queryBuilder->where('Trans_flag', $shift);
            $queryBuilder->whereRaw("YEAR(DateOfBirth) < ?", [$birthYearMax]);
            foreach ($adlConditions as $field) {
                $queryBuilder->where($field, 'Y');
            }

            $members = $queryBuilder->limit(self::MAX_ROWS)->get();
            $totalCount = DB::table('members')
                ->where('Trans_flag', $shift)
                ->whereRaw("YEAR(DateOfBirth) < ?", [$birthYearMax])
                ->where(function($q) use ($adlConditions) {
                    foreach ($adlConditions as $field) {
                        $q->where($field, 'Y');
                    }
                })
                ->count();

            $shiftLabel = $shift === 'A' ? 'Morning' : 'Noon';

            $truncated = $totalCount > self::MAX_ROWS;
            $fieldLabels = implode(', ', array_map(fn($f) => ucfirst(str_replace('_yn', '', $f)), $adlConditions));

            $data = $members->map(function ($m) use ($adlConditions) {
                $row = [
                    'ParticipantId' => $m->ParticipantId,
                    'Name' => trim(($m->FirstName ?? '') . ' ' . ($m->LastName ?? '')) ?: 'N/A',
                    'Age' => $m->Age ?? 'N/A',
                    'Sex' => $m->Sex ?? 'N/A',
                ];
                return $row;
            })->toArray();

            $columns = ['ParticipantId', 'Name', 'Age', 'Sex'];

            return [
                'ok' => true,
                'type' => 'table',
                'title' => "{$shiftLabel} Shift - Members over {$age} needing assistance ({$totalCount} found)",
                'columns' => $columns,
                'data' => $data,
                'truncated' => $truncated,
                'total_count' => $totalCount,
            ];
        }

        // Default: show member info
        return [
            'response' => "I can help with members. Try: 'How many members in morning shift?' or 'Show birthdays this month'",
            'type' => 'help',
        ];
    }

    private function handleAttendanceQuery(string $query, string $lowerQuery): array
    {
        $date = date('Y-m-d');

        if (str_contains($lowerQuery, 'today')) {
            $date = date('Y-m-d');
        } elseif (str_contains($lowerQuery, 'yesterday')) {
            $date = date('Y-m-d', strtotime('-1 day'));
        } elseif (preg_match('/\b(\d{4}-\d{2}-\d{2})\b/', $query, $m)) {
            $date = $m[1];
        } elseif (preg_match('/\b(\d{1,2})\/(\d{1,2})\/(\d{4})\b/', $query, $m)) {
            $date = sprintf('%04d-%02d-%02d', $m[3], $m[1], $m[2]);
        }

        $shift = null;
        if (str_contains($lowerQuery, 'morning')) $shift = 'A';
        if (str_contains($lowerQuery, 'noon')) $shift = 'N';

        $presentCount = DB::table('partattendence')
            ->where('CalenderDate', $date)
            ->where('AttendanceYN', 'Y')
            ->when($shift, fn($q) => $q->whereIn('ParticipantId', function($sub) use ($shift) {
                $sub->from('members')
                    ->select('ParticipantId')
                    ->where('Trans_flag', $shift);
            }))
            ->count();

        $absentCount = DB::table('partattendence')
            ->where('CalenderDate', $date)
            ->where('AttendanceYN', 'N')
            ->when($shift, fn($q) => $q->whereIn('ParticipantId', function($sub) use ($shift) {
                $sub->from('members')
                    ->select('ParticipantId')
                    ->where('Trans_flag', $shift);
            }))
            ->count();

        $shiftLabel = $shift === 'A' ? 'Morning Shift ' : ($shift === 'N' ? 'Noon Shift ' : '');
        $formattedDate = date('F j, Y', strtotime($date));

        return [
            'ok' => true,
            'type' => 'counts',
            'title' => "Attendance for {$shiftLabel}on {$formattedDate}",
            'data' => [
                ['Status' => 'Present', 'Count' => $presentCount],
                ['Status' => 'Absent', 'Count' => $absentCount],
                ['Status' => 'Total', 'Count' => $presentCount + $absentCount],
            ],
        ];
    }

    private function handleVitalsQuery(string $query, string $lowerQuery): array
    {
        $participantId = $this->extractParticipantId($query);

        if ($participantId) {
            $vitals = DB::table('partbp')
                ->where('ParticipantId', $participantId)
                ->orderBy('CalenderDate', 'desc')
                ->limit(10)
                ->get();

            if ($vitals->isEmpty()) {
                return [
                    'response' => "No vitals found for member ID: {$participantId}",
                    'type' => 'not_found',
                ];
            }

            $data = $vitals->map(function ($v) {
                return [
                    'Date' => $v->CalenderDate,
                    'BP' => ($v->LowBP ?? 'N/A') . '/' . ($v->HighBP ?? 'N/A'),
                    'Sugar' => $v->Sugar ?? 'N/A',
                    'Pulse' => $v->Pulse ?? 'N/A',
                    'Weight' => $v->Weight ?? 'N/A',
                ];
            })->toArray();

            return [
                'ok' => true,
                'type' => 'table',
                'title' => "Vitals History for Member: {$participantId}",
                'columns' => ['Date', 'BP', 'Sugar', 'Pulse', 'Weight'],
                'data' => $data,
            ];
        }

        // Show all recent vitals
        $recentVitals = DB::table('partbp')
            ->join('members', 'partbp.ParticipantId', '=', 'members.ParticipantId')
            ->select('partbp.ParticipantId', 'members.FirstName', 'members.LastName',
                     'partbp.CalenderDate', 'partbp.LowBP', 'partbp.HighBP',
                     'partbp.Sugar', 'partbp.Pulse')
            ->orderBy('partbp.CalenderDate', 'desc')
            ->limit(self::MAX_ROWS)
            ->get();

        if ($recentVitals->isEmpty()) {
            return [
                'response' => "No vitals records found",
                'type' => 'not_found',
            ];
        }

        $totalCount = DB::table('partbp')->count();
        $truncated = $totalCount > self::MAX_ROWS;

        $data = $recentVitals->map(function ($v) {
            return [
                'ID' => $v->ParticipantId,
                'Name' => trim(($v->FirstName ?? '') . ' ' . ($v->LastName ?? '')) ?: 'N/A',
                'Date' => $v->CalenderDate,
                'BP' => ($v->LowBP ?? 'N/A') . '/' . ($v->HighBP ?? 'N/A'),
                'Sugar' => $v->Sugar ?? 'N/A',
                'Pulse' => $v->Pulse ?? 'N/A',
            ];
        })->toArray();

        $title = $truncated ? "Recent Vitals Records (Showing " . self::MAX_ROWS . " of {$totalCount})" : "Recent Vitals Records";

        return [
            'ok' => true,
            'type' => 'table',
            'title' => $title,
            'columns' => ['ID', 'Name', 'Date', 'BP', 'Sugar', 'Pulse'],
            'data' => $data,
            'truncated' => $truncated,
            'total_count' => $totalCount,
        ];
    }

    private function handleADLQuery(string $query, string $lowerQuery): array
    {
        $participantId = $this->extractParticipantId($query);

        // Find which ADL flag is being asked about
        $adlField = null;
        if (str_contains($lowerQuery, 'eating')) $adlField = 'EatingYN';
        if (str_contains($lowerQuery, 'toilet')) $adlField = 'ToiletYN';
        if (str_contains($lowerQuery, 'transfer')) $adlField = 'TransferYN';
        if (str_contains($lowerQuery, 'locomotion')) $adlField = 'LocomotionYN';

        if ($participantId && $adlField !== null) {
            $adl = DB::table('partbp')
                ->select('ParticipantId', 'CalenderDate', $adlField)
                ->where('ParticipantId', $participantId)
                ->orderBy('CalenderDate', 'desc')
                ->limit(10)
                ->get();

            $fieldLabel = str_replace('YN', '', $adlField);

            $data = $adl->map(function ($a) use ($fieldLabel, $adlField) {
                return [
                    'ParticipantId' => $a->ParticipantId,
                    'Date' => $a->CalenderDate,
                    $fieldLabel => $a->{$adlField},
                ];
            })->toArray();

            return [
                'ok' => true,
                'type' => 'table',
                'title' => "{$fieldLabel} Assistance for Member: {$participantId}",
                'columns' => ['ParticipantId', 'Date', $fieldLabel],
                'data' => $data,
            ];
        }

        return [
            'response' => "For ADL info, please specify: member ID and type (eating, toilet, transfer, locomotion)",
            'type' => 'help',
        ];
    }

    private function extractParticipantId(string $query): ?string
    {
        // Try various patterns
        $patterns = [
            '/\bid[:\s]*(\d+)/i',
            '/\bparticipant[:\s]*(\d+)/i',
            '/\bmember[:\s]*(\d+)/i',
            '/\b(P\d+)\b/i',
            '/\b(\d{3,})\b/',
            '/\b(\d{1,3})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public function getFallbackMessage(): string
    {
        return $this->fallbackMessage;
    }

    private function getSystemPrompt(): string
    {
        $schemaContext = $this->schemaService->getPromptContext();

        return "You are a helpful data assistant for an Adult Day Care system.

{$schemaContext}

RULES:
1. ONLY answer questions about the 4 tables listed above
2. If asked about anything else, politely decline
3. Format responses clearly
4. Keep answers concise and helpful
5. DO NOT generate SQL queries

If you need to provide data, explain it in plain language.";
    }

    private function extractAIResponse(array $response): string
    {
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }

        if (isset($response['choices']) && is_array($response['choices'])) {
            foreach ($response['choices'] as $choice) {
                if (isset($choice['message']['content'])) {
                    return $choice['message']['content'];
                }
            }
        }

        return json_encode($response);
    }
}
