<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hybrid Chat Enhancement Recommendations</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #333; padding: 40px; }
        h1 { font-size: 20pt; color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
        h2 { font-size: 14pt; color: #2980b9; margin-top: 25px; margin-bottom: 12px; border-left: 4px solid #3498db; padding-left: 10px; }
        h3 { font-size: 12pt; color: #34495e; margin-top: 15px; margin-bottom: 8px; }
        p { margin-bottom: 10px; }
        .section { margin-bottom: 25px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10pt; }
        th { background: #3498db; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background: #f9f9f9; }
        ul, ol { margin-left: 25px; margin-bottom: 10px; }
        li { margin-bottom: 5px; }
        .code { background: #f4f4f4; padding: 10px; border-left: 4px solid #3498db; font-family: 'Courier New', monospace; font-size: 9pt; margin: 10px 0; white-space: pre-wrap; word-wrap: break-word; }
        .note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }
        .highlight { background: #d4edda; border-left: 4px solid #28a745; padding: 10px; margin: 10px 0; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .header-info { margin-bottom: 30px; color: #7f8c8d; }
        hr { border: none; border-top: 1px solid #eee; margin: 20px 0; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #3498db; font-size: 9pt; color: #7f8c8d; text-align: center; }
    </style>
</head>
<body>
    <h1>Hybrid Chat: Intent-Based + AI-Driven Enhancement</h1>
    
    <div class="header-info">
        <p><strong>Project:</strong> Laravel Chat System Enhancement</p>
        <p><strong>Date:</strong> {{ date('F j, Y') }}</p>
        <p><strong>API Provider:</strong> OpenRouter</p>
        <p><strong>Scope:</strong> members, partattendence, partbp, partadl tables only</p>
    </div>

    <div class="section">
        <h2>1. Current State Analysis</h2>
        
        <table>
            <tr><th>Component</th><th>Status</th><th>Description</th></tr>
            <tr><td>ChatController</td><td style="color:green">Active</td><td>1,310 lines, keyword-based intent detection</td></tr>
            <tr><td>AIService</td><td style="color:orange">Not Integrated</td><td>OpenRouter/DeepSeek wrapper exists but unused</td></tr>
            <tr><td>Route</td><td style="color:green">Active</td><td>POST /chat/ask endpoint available</td></tr>
            <tr><td>Models</td><td style="color:green">Complete</td><td>Member, Attendance, Partbp, Partadl</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>2. Architecture Overview</h2>
        
        <div class="box">
            <strong>User Message Flow:</strong>
            <pre style="margin-top:10px; font-family:monospace;">
User Input
    |
    v
+-----------------------------+
|   Intent Detection Layer     |
|   (Hybrid: Rules + AI)      |
+-----------------------------+
    |
    +----------------+----------------+
    v                v                v
+---------+    +-----------+    +----------+
| Known   |    | Unknown   |    | Off-    |
| Intent  |    | (Fallback |    | Topic   |
| (Rules) |    | to AI)    |    | (Decline|
+---------+    +-----------+    +----------+</pre>
        </div>
    </div>

    <div class="section">
        <h2>3. Detection Strategy</h2>
        
        <h3>3.1 Intent Categories (Rule-Based)</h3>
        <table>
            <tr><th>Intent</th><th>Keywords</th><th>Parameters</th></tr>
            <tr><td>count_by_sex</td><td>male/female + how many</td><td>shift (A/N)</td></tr>
            <tr><td>birthdays_in_month</td><td>birthday + month name</td><td>month (1-12)</td></tr>
            <tr><td>birthdays_next_days</td><td>birthday + this week/upcoming</td><td>days</td></tr>
            <tr><td>pa_expires</td><td>PA + expire</td><td>days/month</td></tr>
            <tr><td>hnp_expires</td><td>HNP + expire</td><td>days/month</td></tr>
            <tr><td>vitals_monthly</td><td>vital/bp + ID</td><td>participant_id, month</td></tr>
            <tr><td>attendance_totals</td><td>attendance + date range</td><td>from, to</td></tr>
            <tr><td>last_attendance</td><td>last attendance</td><td>shift</td></tr>
        </table>

        <h3>3.2 AI Fallback Conditions</h3>
        <div class="highlight">
            <strong>Use AI when:</strong>
            <ul>
                <li>Query doesn't match any known intent keywords</li>
                <li>Question is about data in the 4 allowed tables</li>
                <li>Natural language variations of supported queries</li>
                <li>Complex multi-condition questions</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>4. System Prompt for OpenRouter</h2>
        
        <div class="code">You are a data assistant for a healthcare/day program.

ALLOWED TABLES:
1. members: ParticipantId, FirstName, MiddleName, LastName, DateOfBirth, Sex, 
   Trans_flag (A=Morning, N=Noon), medicaid_PA_no, medicaid_PA_End, HNP_Expiry_date
2. partattendence: ParticipantId, CalenderDate, AttendanceYN (Y/N)
3. partbp: ParticipantId, CalenderDate, LowBP, HighBP, Sugar, Pulse, Temperature, Weight
4. partadl: participantid, calenderDate, [ADL fields]

RULES:
- ONLY answer questions about the 4 tables above
- If asked about anything else, politely decline
- Format responses clearly
- Keep answers concise and helpful</div>
    </div>

    <div class="section">
        <h2>5. New Files to Create</h2>
        
        <table>
            <tr><th>File Path</th><th>Purpose</th><th>Priority</th></tr>
            <tr><td>app/Services/ChatIntentService.php</td><td>Hybrid intent detection logic</td><td>High</td></tr>
            <tr><td>app/Services/TableSchemaService.php</td><td>Provide table schemas to AI</td><td>Medium</td></tr>
            <tr><td>app/Http/Requests/ChatAskRequest.php</td><td>Request validation</td><td>Medium</td></tr>
            <tr><td>app/Http/Controllers/RecommendationsController.php</td><td>PDF generation</td><td>Low</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>6. Code Changes Required</h2>
        
        <h3>6.1 ChatController.php - Modified Flow</h3>
        <div class="code">// Current: Direct rule-based processing
// New: Hybrid approach

public function ask(Request $request)
{
    $message = $request->input('message');
    
    // Step 1: Try rule-based intent detection
    $intent = $this->detectIntent($message);
    
    if ($intent !== null) {
        // Execute rule-based response (existing logic)
        return $this->executeIntent($intent);
    }
    
    // Step 2: Check if AI should handle this
    $chatIntentService = app(ChatIntentService::class);
    
    if ($chatIntentService->shouldUseAI($message)) {
        return $chatIntentService->processWithAI($message);
    }
    
    // Step 3: Off-topic or unclear
    return response()-&gt;json([
        'response' =-&gt; "I can help with member data, attendance, vitals, 
            and authorizations. Try asking about birthdays, PA expirations, 
            or attendance records.",
        'type' =-&gt; 'fallback'
    ]);
}</div>

        <h3>6.2 ChatIntentService.php - Core Logic</h3>
        <div class="code">namespace App\Services;

class ChatIntentService
{
    private AIService $aiService;
    
    private array $allowedTables = [
        'members', 'partattendence', 'partbp', 'partadl'
    ];
    
    public function __construct(AIService $aiService)
    {
        $this-&gt;aiService = $aiService;
    }
    
    public function shouldUseAI(string $query): bool
    {
        $lowerQuery = strtolower($query);
        
        $dataKeywords = [
            'member', 'participant', 'attendance', 'vital', 'bp', 
            'blood', 'weight', 'pulse', 'birthday', 'expire', 
            'shift', 'morning', 'noon', 'today', 'yesterday',
            'this week', 'this month', 'how many', 'count'
        ];
        
        foreach ($dataKeywords as $keyword) {
            if (str_contains($lowerQuery, $keyword)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function processWithAI(string $query): array
    {
        $systemPrompt = $this-&gt;getSystemPrompt();
        
        $response = $this-&gt;aiService-&gt;askWithSystemPrompt(
            $systemPrompt,
            $query
        );
        
        if ($this-&gt;validateAIResponse($response)) {
            return [
                'response' =-&gt; $response,
                'type' =-&gt; 'ai'
            ];
        }
        
        return [
            'response' =-&gt; "I couldn't understand that query. Please try rephrasing.",
            'type' =-&gt; 'error'
        ];
    }
    
    private function getSystemPrompt(): string
    {
        return "You are a data assistant. Only answer questions 
            about: members, partattendence, partbp, partadl tables.
            Decline all other requests politely.";
    }
    
    private function validateAIResponse(string $response): bool
    {
        $forbidden = ['password', 'user table', 'admin', 'DELETE', 'DROP'];
        
        foreach ($forbidden as $word) {
            if (stripos($response, $word) !== false) {
                return false;
            }
        }
        
        return true;
    }
}</div>
    </div>

    <div class="section">
        <h2>7. AI Query Validation</h2>
        
        <div class="box">
            <strong>TableAccessValidator Class</strong>
            <div class="code">namespace App\Services;

class TableAccessValidator
{
    private array $allowedTables = [
        'members', 'partattendence', 'partbp', 'partadl'
    ];
    
    public function isAllowedQuery(string $query): bool
    {
        $lowerQuery = strtolower($query);
        
        $blocked = ['password', 'login', 'user', 'admin', 
                    'email', 'credential', 'auth'];
        
        foreach ($blocked as $term) {
            if (str_contains($lowerQuery, $term)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function validateTableAccess(string $tableName): bool
    {
        return in_array(strtolower($tableName), $this-&gt;allowedTables);
    }
}</div>
        </div>
    </div>

    <div class="section">
        <h2>8. Error Handling</h2>
        
        <table>
            <tr><th>Scenario</th><th>Response</th><th>Action</th></tr>
            <tr><td>AI API fails</td><td>"I couldn't process that..."</td><td>Log error, show fallback message</td></tr>
            <tr><td>Invalid query</td><td>"I couldn't understand..."</td><td>Suggest rephrasing</td></tr>
            <tr><td>Off-topic query</td><td>Decline + show capabilities</td><td>List supported queries</td></tr>
            <tr><td>Rate limited</td><td>"Please wait a moment..."</td><td>Queue or delay</td></tr>
            <tr><td>SQL injection attempt</td><td>"Invalid request"</td><td>Block, log security event</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>9. Configuration Updates</h2>
        
        <h3>9.1 Environment Variables (.env)</h3>
        <div class="code">OPENROUTER_API_KEY=sk-or-your-key-here
OPENROUTER_URL=https://openrouter.ai/api/v1/chat/completions
OPENROUTER_MODEL=deepseek/deepseek-chat-v3
AI_FALLBACK_ENABLED=true
AI_TABLE_RESTRICTION=true
AI_MAX_TOKENS=500</div>

        <h3>9.2 config/services.php (verify)</h3>
        <div class="code">openrouter' =&gt; [
    'key' =&gt; env('OPENROUTER_API_KEY'),
    'url' =&gt; env('OPENROUTER_URL', 'https://openrouter.ai/api/v1/chat/completions'),
],</div>
    </div>

    <div class="section">
        <h2>10. Implementation Checklist</h2>
        
        <div class="box">
            <strong>Phase 1 - Core Integration</strong>
            <ol>
                <li>Create ChatIntentService.php</li>
                <li>Create TableSchemaService.php</li>
                <li>Modify detectIntent() to return unknown intent</li>
                <li>Wire AIService into ChatController</li>
                <li>Test rule-based + AI fallback</li>
            </ol>
            
            <strong>Phase 2 - Security and Validation</strong>
            <ol>
                <li>Implement TableAccessValidator</li>
                <li>Add SQL injection protection</li>
                <li>Test off-topic query rejection</li>
                <li>Add rate limiting</li>
            </ol>
            
            <strong>Phase 3 - Polish</strong>
            <ol>
                <li>Add conversation context for multi-turn</li>
                <li>Improve error messages</li>
                <li>Add "AI-powered" indicator in UI</li>
                <li>Monitor AI usage/costs</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <h2>11. Summary of Changes</h2>
        
        <table>
            <tr><th>File</th><th>Action</th><th>Description</th></tr>
            <tr><td>ChatController.php</td><td>Modify</td><td>Add AI fallback in ask() method</td></tr>
            <tr><td>detectIntent()</td><td>Modify</td><td>Return 'unknown' for AI fallback</td></tr>
            <tr><td>ChatIntentService.php</td><td>Create</td><td>New: Hybrid intent detection</td></tr>
            <tr><td>TableSchemaService.php</td><td>Create</td><td>New: Schema definitions for AI</td></tr>
            <tr><td>TableAccessValidator.php</td><td>Create</td><td>New: Security validation</td></tr>
            <tr><td>AIService.php</td><td>No change</td><td>Already exists, just wire it in</td></tr>
            <tr><td>.env</td><td>Update</td><td>Add OpenRouter settings</td></tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated for Laravel Chat Enhancement Project</p>
        <p>API: OpenRouter | Model: DeepSeek | Tables: members, partattendence, partbp, partadl</p>
    </div>
</body>
</html>
