<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hybrid Chat System - Technical Documentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9.5pt; line-height: 1.4; color: #333; padding: 35px; }
        h1 { font-size: 18pt; color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 8px; margin-bottom: 15px; }
        h2 { font-size: 13pt; color: #2980b9; margin-top: 18px; margin-bottom: 8px; border-left: 4px solid #3498db; padding-left: 8px; }
        h3 { font-size: 11pt; color: #34495e; margin-top: 12px; margin-bottom: 6px; }
        p { margin-bottom: 8px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        th { background: #3498db; color: white; padding: 6px 8px; text-align: left; }
        td { border: 1px solid #ddd; padding: 5px 8px; }
        tr:nth-child(even) { background: #f9f9f9; }
        ul, ol { margin-left: 20px; margin-bottom: 8px; }
        li { margin-bottom: 4px; }
        .code { background: #f4f4f4; padding: 8px; border-left: 3px solid #3498db; font-family: 'Courier New', monospace; font-size: 8pt; margin: 8px 0; white-space: pre-wrap; word-wrap: break-word; }
        .note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 8px; margin: 8px 0; font-size: 9pt; }
        .tip { background: #d4edda; border-left: 4px solid #28a745; padding: 8px; margin: 8px 0; font-size: 9pt; }
        .warning { background: #f8d7da; border-left: 4px solid #dc3545; padding: 8px; margin: 8px 0; font-size: 9pt; }
        .box { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .header-info { margin-bottom: 20px; color: #7f8c8d; font-size: 9pt; }
        .flow-box { background: #e8f4fd; border: 1px solid #3498db; padding: 12px; margin: 10px 0; border-radius: 4px; }
        .flow-box strong { color: #2980b9; }
        .page-break { page-break-before: always; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 2px solid #3498db; font-size: 8pt; color: #7f8c8d; text-align: center; }
        .two-col { display: table; width: 100%; }
        .two-col > div { display: table-cell; width: 48%; vertical-align: top; padding: 5px; }
        code { background: #f4f4f4; padding: 2px 4px; font-family: 'Courier New', monospace; font-size: 8pt; }
    </style>
</head>
<body>
    <h1>Hybrid Chat System - Technical Documentation</h1>
    
    <div class="header-info">
        <p><strong>Project:</strong> Laravel Chat Application with Hybrid Intent + AI</p>
        <p><strong>Author:</strong> Auto-generated Documentation</p>
        <p><strong>Version:</strong> 1.0</p>
        <p><strong>Purpose:</strong> Understanding the chat flow for future modifications</p>
    </div>

    <!-- SECTION 1: ARCHITECTURE OVERVIEW -->
    <div class="section">
        <h2>1. Architecture Overview</h2>
        
        <div class="flow-box">
            <strong>User Question</strong>
                ↓
            <strong>ChatController::ask()</strong> (Entry Point)
                ↓
            <div style="margin-left: 20px;">
                ├→ <strong>detectIntent()</strong> (Rule-based) → Supported Intent? → Execute Query → Response<br>
                └→ <strong>ChatIntentService</strong> (Hybrid) → Direct DB Query or OpenRouter AI<br>
            </div>
                ↓
            <strong>JSON Response</strong> → Frontend Display
        </div>

        <h3>1.1 Flow Diagram</h3>
        <pre class="code">
┌─────────────────────────────────────────────────────────────────┐
│                    USER ASKS A QUESTION                          │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│              ChatController::ask()                              │
│              (app/Http/Controllers/ChatController.php)            │
└─────────────────────────────────────────────────────────────────┘
                              ↓
         ┌────────────────────┴────────────────────┐
         ↓                                          ↓
┌───────────────────────┐           ┌───────────────────────────────┐
│   Rule-Based Intent   │           │   Unsupported Intent        │
│   Detection           │           │   (detectIntent returns     │
│   (detectIntent)      │           │    supported = false)        │
└───────────────────────┘           └───────────────────────────────┘
         ↓                                          ↓
    ┌─────────┐                              ┌─────────────────────────┐
    │ Intent  │                              │ ChatIntentService       │
    │ Found?  │                              │ (Hybrid Service)        │
    └────┬────┘                              └───────────┬─────────┘
         ↓                                              ↓
    ┌────┴────┐                              ┌────────┴────────┐
    │          │                              │                │
┌───↓──┐  ┌──↓──────┐              ┌─────────↓───┐  ┌───────────↓───────┐
│  YES  │  │   NO    │              │ AI_ENABLED  │  │ AI_ENABLED        │
│      │  │         │              │   = FALSE   │  │   = TRUE          │
└──┬──┘  └────┬────┘              └──────┬──────┘  └───────┬───────────┘
   ↓          ↓                            ↓                   ↓
┌────────┐  ┌────────┐            ┌─────────────────┐  ┌─────────────────────┐
│Execute │  │ Check  │            │ Direct DB Query│  │ OpenRouter AI Call │
│ Rule   │  │ should │            │ (executeQuery)  │  │ (processWithOpen   │
│ Based  │  │ UseAI?│            │                │  │  Router)           │
└────┬───┘  └───┬────┘            └────────┬────────┘  └─────────┬─────────┘
     ↓          ↓                              ↓                     ↓
┌────────────┐  ┌────────────┐            ┌────────────┐        ┌────────────┐
│ JSON      │  │ Check     │            │ JSON       │        │ Parse AI   │
│ Response  │  │ AI Config │            │ Response    │        │ Response   │
└───────────┘  └───────────┘            └────────────┘        └─────┬───────┘
                                                                    ↓
                                                            ┌────────────┐
                                                            │ Execute    │
                                                            │ Structured │
                                                            │ Query      │
                                                            └──────┬─────┘
                                                                   ↓
                                                            ┌────────────┐
                                                            │ JSON       │
                                                            │ Response   │
                                                            └────────────┘
</pre>
    </div>

    <!-- SECTION 2: FILE STRUCTURE -->
    <div class="section">
        <h2>2. File Structure</h2>
        
        <table>
            <tr><th>File</th><th>Purpose</th><th>Key Methods</th></tr>
            <tr><td>app/Http/Controllers/ChatController.php</td><td>Main entry point</td><td>ask(), detectIntent(), index()</td></tr>
            <tr><td>app/Services/ChatIntentService.php</td><td>Hybrid intent handling</td><td>processWithAI(), executeQuery(), shouldUseAI()</td></tr>
            <tr><td>app/Services/TableSchemaService.php</td><td>Table schemas for AI</td><td>getPromptContext(), getSchema()</td></tr>
            <tr><td>app/Services/TableAccessValidator.php</td><td>Security validation</td><td>isAllowedQuery(), validateTableAccess()</td></tr>
            <tr><td>app/Services/AIService.php</td><td>OpenRouter API wrapper</td><td>askWithSystemPrompt()</td></tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 3: CHATCONTROLLER DETAILED FLOW -->
    <div class="section">
        <h2>3. ChatController - Detailed Flow</h2>
        
        <h3>3.1 The ask() Method (Lines 45-491)</h3>
        <p>This is the <strong>entry point</strong> for all chat requests.</p>
        
        <pre class="code">
ChatController::ask(Request $request)
    │
    ├─ 1. Get message from request (line 47)
    │       $q = trim((string)$request->input('message', ''));
    │
    ├─ 2. Check empty message (line 49-55)
    │       If empty → return error response
    │
    ├─ 3. Check pending vitals follow-up (line 58-92)
    │       If user previously asked for vitals, this handles the month/year response
    │
    ├─ 4. Check pending attendance follow-up (line 95-141)
    │       If user previously asked for attendance, this handles date range response
    │
    ├─ 5. Call detectIntent() (line 145)
    │       $intent = $this->detectIntent($q);
    │
    ├─ 6. If intent NOT supported (line 148-189)
    │       │
    │       ├─ Create ChatIntentService instance
    │       │
    │       ├─ Check shouldUseAI()
    │       │       Returns true if query contains data-related keywords
    │       │
    │       └─ Call processWithAI()
    │               Returns structured data or error message
    │
    └─ 7. If intent IS supported (line 191-483)
            │
            ├─ count_by_sex → countBySex() (line 191)
            ├─ birthdays_in_month → birthdaysInMonth() (line 202)
            ├─ birthdays_next_days → birthdaysNextDays() (line 215)
            ├─ pa_expires_this_month → paExpiresThisMonth() (line 232)
            ├─ pa_expires_next_days → paExpiresNextDays() (line 256)
            ├─ hnp_expires_this_month → HNPExpiresThisMonth() (line 282)
            ├─ hnp_expires_next_days → HNPExpiresNextDays() (line 307)
            ├─ vitals_monthly → vitalsMonthly() (line 332)
            ├─ last_attendance_by_shift → lastAttendanceSummary() (line 400)
            ├─ attendance_totals_month → attendanceTotalsByShift() (line 413)
            └─ attendance_totals_range → attendanceTotalsByShift() (line 433)
</pre>
    </div>

    <!-- SECTION 4: DETECT INTENT METHOD -->
    <div class="section">
        <h2>4. detectIntent() Method (Lines 512-776)</h2>
        
        <p>This method uses <strong>keyword matching</strong> to identify user intent.</p>
        
        <h3>4.1 Detection Order</h3>
        <pre class="code">
detectIntent(string $q)
    │
    ├─ 1. Convert to lowercase (line 514)
    │       $qLower = mb_strtolower($q);
    │
    ├─ 2. Detect shift (line 517-519)
    │       'morning' → 'A'
    │       'noon' → 'N'
    │
    ├─ 3. COUNT BY SEX (line 521-535)
    │       Keywords: 'male', 'female', 'how many', 'count'
    │       Pattern: "how many [male/female] in [morning/noon] shift?"
    │       Returns: ['intent' => 'count_by_sex', 'shift' => 'A'/'N']
    │
    ├─ 4. BIRTHDAYS (line 538-570)
    │       Keywords: 'birthday', 'birth day', 'birthdays'
    │       Sub-patterns:
    │         - 'this week' / 'next 7' → birthdays_next_days (7 days)
    │         - 'upcoming' / 'next 30' → birthdays_next_days (30 days)
    │         - month name → birthdays_in_month
    │
    ├─ 5. PA EXPIRY (line 573-603)
    │       Keywords: 'pa', 'expire'
    │       Sub-patterns:
    │         - 'this month' → pa_expires_this_month
    │         - 'in X days' / 'next X days' → pa_expires_next_days
    │
    ├─ 6. HNP EXPIRY (line 606-636)
    │       Keywords: 'hnp', 'expire'
    │       Sub-patterns:
    │         - 'this month' → hnp_expires_this_month
    │         - 'in X days' / 'next X days' → hnp_expires_next_days
    │
    ├─ 7. VITALS MONTHLY (line 678-723)
    │       Keywords: 'vital', 'bp', 'health'
    │       Requires: participant ID (e.g., "id 101")
    │       Optional: YYYY-MM format
    │
    ├─ 8. LAST ATTENDANCE (line 726-736)
    │       Keywords: 'last attendance'
    │       Returns: last_attendance_by_shift
    │
    └─ 9. ATTENDANCE TOTALS (line 739-772)
            Keywords: 'attendance'
            Sub-patterns:
              - date range "X to Y" → attendance_totals_range
              - month/year → attendance_totals_month
              - else → attendance_needs_date (asks follow-up)
</pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 5: SUPPORTED INTENTS -->
    <div class="section">
        <h2>5. Supported Intents Summary</h2>
        
        <table>
            <tr><th>Intent Name</th><th>Example Query</th><th>Parameters</th></tr>
            <tr><td>count_by_sex</td><td>"How many males in morning shift?"</td><td>shift (A/N)</td></tr>
            <tr><td>birthdays_in_month</td><td>"Birthdays in March"</td><td>month (1-12)</td></tr>
            <tr><td>birthdays_next_days</td><td>"Birthdays this week"</td><td>days (7/30)</td></tr>
            <tr><td>pa_expires_this_month</td><td>"PA expiring this month"</td><td>-</td></tr>
            <tr><td>pa_expires_next_days</td><td>"PA expires in 30 days"</td><td>days</td></tr>
            <tr><td>hnp_expires_this_month</td><td>"HNP expiring this month"</td><td>-</td></tr>
            <tr><td>hnp_expires_next_days</td><td>"HNP expires in 60 days"</td><td>days</td></tr>
            <tr><td>vitals_monthly</td><td>"Vitals for id 101 in 2026-02"</td><td>participant_id, year, month</td></tr>
            <tr><td>last_attendance_by_shift</td><td>"Last attendance"</td><td>-</td></tr>
            <tr><td>attendance_totals_month</td><td>"Attendance for January 2026"</td><td>year, month</td></tr>
            <tr><td>attendance_totals_range</td><td>"Attendance from 01/01/2026 to 01/31/2026"</td><td>from, to</td></tr>
        </table>
    </div>

    <!-- SECTION 6: HYBRID SERVICE -->
    <div class="section">
        <h2>6. ChatIntentService - Hybrid Handler</h2>
        
        <h3>6.1 Processing Flow</h3>
        <pre class="code">
ChatIntentService::processWithAI(string $query)
    │
    ├─ 1. Security Check (line 94-98)
    │       Call: isAllowedQuery(query)
    │       Blocks: password, admin, delete, drop, etc.
    │
    ├─ 2. Check AI_ENABLED config (line 102-104)
    │       │
    │       ├─ If AI_ENABLED = true → processWithOpenRouter()
    │       └─ If AI_ENABLED = false → executeQuery() (Direct DB)
    │
    └─ 3. Return result array
</pre>

        <h3>6.2 shouldUseAI() Method (Lines 47-71)</h3>
        <p>Determines if a query should be handled by the hybrid service.</p>
        <pre class="code">
shouldUseAI(string $query) → bool
    │
    ├─ 1. Check dataKeywords (line 51-54)
    │       Keywords include:
    │       'member', 'participant', 'attendance', 'vital', 'bp',
    │       'birthday', 'shift', 'morning', 'eat', 'toilet', etc.
    │
    └─ 2. Check question patterns (line 57-68)
            Patterns: 'what', 'who', 'which', 'how', 'show', 'list', 'get', '?'
</pre>

        <h3>6.3 executeQuery() Method (Lines 223-249)</h3>
        <p>Routes queries to appropriate handlers based on keywords.</p>
        <pre class="code">
executeQuery(string $query) → array
    │
    ├─ isMembersQuery() → handleMembersQuery()
    ├─ isAttendanceQuery() → handleAttendanceQuery()
    ├─ isVitalsQuery() → handleVitalsQuery()
    └─ isADLQuery() → handleADLQuery()
</pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 7: MEMBER QUERIES -->
    <div class="section">
        <h2>7. Member Query Handlers</h2>
        
        <h3>7.1 handleMembersQuery() (Lines 296-575)</h3>
        
        <h3>Age + ADL Query (Lines 455-531)</h3>
        <pre class="code">
Query Pattern: "Members over X [who need] [ADL keyword]"
Example: "Members over 70 who need transfer help"

Logic:
1. Extract age from query (preg_match '/over\s*(\d+)/i')
2. Check for ADL keywords: eating, bath, toilet, transfer, locomotion, bed_mobility
3. Build query with:
   - WHERE YEAR(DateOfBirth) BETWEEN 1900 AND 2100 (exclude bad data)
   - HAVING Age >= extracted_age
   - WHERE transfer_yn = 'Y' (if ADL keyword found)
4. Sort options: by id, by age (asc/desc)
5. Limit to MAX_ROWS (100)
</pre>

        <h3>7.2 Supported ADL Keywords</h3>
        <table>
            <tr><th>Keyword in Query</th><th>Database Field</th><th>Condition</th></tr>
            <tr><td>eating</td><td>eating_yn</td><td>= 'Y'</td></tr>
            <tr><td>bath / bathing</td><td>bathing_yn</td><td>= 'Y'</td></tr>
            <tr><td>toilet</td><td>toilet_yn</td><td>= 'Y'</td></tr>
            <tr><td>transfer</td><td>transfer_yn</td><td>= 'Y'</td></tr>
            <tr><td>locomotion</td><td>locomotion_yn</td><td>= 'Y'</td></tr>
            <tr><td>bed_mobility</td><td>bed_mobility_yn</td><td>= 'Y'</td></tr>
        </table>

        <h3>7.3 Other Member Queries</h3>
        <table>
            <tr><th>Query Pattern</th><th>Handler</th><th>Output</th></tr>
            <tr><td>"Show member P001"</td><td>extractParticipantId()</td><td>Member details table</td></tr>
            <tr><td>"How many members..."</td><td>countBySex logic</td><td>Male/Female counts</td></tr>
            <tr><td>"Birthdays this month"</td><td>birthday queries</td><td>List of birthdays</td></tr>
            <tr><td>"All members"</td><td>list all members</td><td>First 100 members</td></tr>
        </table>
    </div>

    <!-- SECTION 8: CONFIGURATION -->
    <div class="section">
        <h2>8. Configuration</h2>
        
        <h3>8.1 Environment Variables (.env)</h3>
        <pre class="code">
# OpenRouter API
OPENROUTER_API_KEY=sk-or-v1-...
OPENROUTER_URL=https://openrouter.ai/api/v1/chat/completions

# Hybrid Mode Toggle
AI_ENABLED=false   # false = Direct DB only, true = AI + DB
</pre>

        <h3>8.2 config/services.php</h3>
        <pre class="code">
'openrouter' => [
    'key' => env('OPENROUTER_API_KEY'),
    'url' => env('OPENROUTER_URL'),
],

'ai_enabled' => env('AI_ENABLED', false),
</pre>

        <h3>8.3 ChatIntentService Constants</h3>
        <pre class="code">
MAX_ROWS = 100   # Maximum rows returned per query
</pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 9: HOW TO ADD NEW FEATURES -->
    <div class="section">
        <h2>9. How to Add New Features</h2>
        
        <h3>9.1 Adding a New Rule-Based Intent</h3>
        <div class="box">
            <p><strong>Step 1:</strong> Add detection in <code>detectIntent()</code> (ChatController.php)</p>
            <pre class="code">
// Add new detection block in detectIntent() method (around line 772)

if (str_contains($qLower, 'your keyword')) {
    // Extract parameters using regex
    if (preg_match('/your pattern/', $qLower, $m)) {
        return [
            'supported' => true,
            'intent' => 'your_new_intent',
            'param1' => $m[1],
            'title' => 'Your Title',
        ];
    }
}
</pre>

            <p><strong>Step 2:</strong> Add handler in <code>ask()</code> (ChatController.php)</p>
            <pre class="code">
// Add handler in ask() method (around line 480)

if ($intent['intent'] === 'your_new_intent') {
    $rows = $this->yourNewMethod($intent['param1']);
    return response()->json([
        'ok' => true,
        'type' => 'table',
        'title' => $intent['title'],
        'columns' => ['Column1', 'Column2'],
        'data' => $rows,
        'suggestions' => $this->suggestions(),
    ]);
}
</pre>

            <p><strong>Step 3:</strong> Create the method (ChatController.php)</p>
            <pre class="code">
private function yourNewMethod($param)
{
    return DB::table('members')
        ->select('Column1', 'Column2')
        ->where('field', $param)
        ->limit(100)
        ->get();
}
</pre>
        </div>

        <h3>9.2 Adding a New ADL Keyword</h3>
        <div class="box">
            <p><strong>Step 1:</strong> Add to <code>adlFieldMap</code> in ChatIntentService.php</p>
            <pre class="code">
// Find adlFieldMap (around lines 459-465 or 537-543) and add:

$adlFieldMap = [
    'eating' => 'eating_yn',
    'bath' => 'bathing_yn',
    'toilet' => 'toilet_yn',
    'transfer' => 'transfer_yn',
    'locomotion' => 'locomotion_yn',
    'bed_mobility' => 'bed_mobility_yn',
    'NEW_KEYWORD' => 'new_field_yn',  // ADD THIS LINE
];
</pre>
        </div>

        <h3>9.3 Adding a New Query Type to ChatIntentService</h3>
        <div class="box">
            <p><strong>Step 1:</strong> Add detection in <code>executeQuery()</code></p>
            <pre class="code">
// Add new detection in executeQuery() (around line 244)

private function executeQuery(string $query): array
{
    $lowerQuery = strtolower(trim($query));

    // ADD NEW CHECK HERE
    if ($this->isYourNewQueryType($lowerQuery)) {
        return $this->handleYourNewQuery($query, $lowerQuery);
    }
    
    // ... existing code
}

// ADD NEW METHOD
private function isYourNewQueryType(string $query): bool
{
    return str_contains($query, 'your keyword');
}

private function handleYourNewQuery(string $query, string $lowerQuery): array
{
    // Your query logic here
    $results = DB::table('members')
        // ... query
        ->get();
    
    return [
        'ok' => true,
        'type' => 'table',
        'title' => 'Your Title',
        'columns' => ['Col1', 'Col2'],
        'data' => $results->toArray(),
    ];
}
</pre>
        </div>

        <h3>9.4 Adding a New Table</h3>
        <div class="box">
            <p><strong>Step 1:</strong> Update <code>TableAccessValidator.php</code></p>
            <pre class="code">
// Line 12 - Add new table to allowedTables array
private array $allowedTables = [
    'members', 
    'partattendence', 
    'partbp', 
    'partadl',
    'YOUR_NEW_TABLE'  // ADD THIS
];
</pre>

            <p><strong>Step 2:</strong> Update <code>TableSchemaService.php</code></p>
            <pre class="code">
// Add new schema in constructor
$this->schemas['YOUR_NEW_TABLE'] = [
    'columns' => [
        'Column1' => 'string - Description',
        'Column2' => 'integer - Description',
    ],
    'description' => 'Your table description',
    'key_column' => 'PrimaryKeyColumn',
];
</pre>

            <p><strong>Step 3:</strong> Update <code>ChatIntentService</code></p>
            <pre class="code">
// Add query handling in executeQuery() for the new table
</pre>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 10: SECURITY -->
    <div class="section">
        <h2>10. Security Features</h2>
        
        <h3>10.1 TableAccessValidator</h3>
        <p>Blocks queries containing dangerous keywords:</p>
        <pre class="code">
Blocked Terms:
- password, passwd, pwd
- login, credentials, auth
- user, users, admin, administrator
- email, mail
- role, permission
- token, api_key, apikey, secret
- bank, account, financial
- salary, wage, payment
- delete, drop, truncate, alter
- insert, update, replace
- union, exec, execute
- script, javascript
</pre>

        <h3>10.2 Only 4 Tables Allowed</h3>
        <ul>
            <li>members</li>
            <li>partattendence</li>
            <li>partbp</li>
            <li>partadl</li>
        </ul>

        <h3>10.3 MAX_ROWS Limit</h3>
        <p>All queries are limited to 100 rows maximum to prevent performance issues.</p>
    </div>

    <!-- SECTION 11: RESPONSE FORMATS -->
    <div class="section">
        <h2>11. Response Formats</h2>
        
        <h3>11.1 Table Response</h3>
        <pre class="code">
{
    "ok": true,
    "type": "table",
    "title": "Members over 70 years",
    "columns": ["ParticipantId", "Name", "Age", "Sex"],
    "data": [
        {"ParticipantId": "001", "Name": "John Doe", "Age": 75, "Sex": "M"},
        ...
    ],
    "truncated": false,
    "total_count": 45
}
</pre>

        <h3>11.2 Counts Response</h3>
        <pre class="code">
{
    "ok": true,
    "type": "counts",
    "title": "Member Count - Morning Shift",
    "data": [
        {"Category": "Male", "Count": 25},
        {"Category": "Female", "Count": 30},
        {"Category": "Total", "Count": 55}
    ]
}
</pre>

        <h3>11.3 Error/Unsupported Response</h3>
        <pre class="code">
{
    "ok": true,
    "type": "unsupported",
    "message": "I can't answer that yet.",
    "suggestions": [...]
}
</pre>
    </div>

    <!-- SECTION 12: QUICK REFERENCE -->
    <div class="section">
        <h2>12. Quick Reference</h2>
        
        <table>
            <tr><th>What You Want to Add</th><th>File to Modify</th><th>Lines</th></tr>
            <tr><td>New rule-based intent</td><td>ChatController.php</td><td>detectIntent() + ask()</td></tr>
            <tr><td>New ADL keyword</td><td>ChatIntentService.php</td><td>adlFieldMap arrays</td></tr>
            <tr><td>New direct query type</td><td>ChatIntentService.php</td><td>executeQuery()</td></tr>
            <tr><td>New table support</td><td>TableSchemaService.php + TableAccessValidator.php</td><td>schemas + allowedTables</td></tr>
            <tr><td>Change MAX_ROWS</td><td>ChatIntentService.php</td><td>Line 13</td></tr>
            <tr><td>Toggle AI mode</td><td>.env</td><td>AI_ENABLED=true/false</td></tr>
        </table>
    </div>

    <div class="footer">
        <p>Hybrid Chat System Documentation</p>
        <p>For support, refer to this document or contact your system administrator</p>
    </div>
</body>
</html>
