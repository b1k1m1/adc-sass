<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Bot User Guide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #333; padding: 30px; }
        h1 { font-size: 18pt; color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 8px; margin-bottom: 15px; text-align: center; }
        h2 { font-size: 13pt; color: #2980b9; margin-top: 18px; margin-bottom: 8px; border-left: 4px solid #3498db; padding-left: 8px; }
        h3 { font-size: 11pt; color: #34495e; margin-top: 10px; margin-bottom: 5px; }
        p { margin-bottom: 8px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        th { background: #3498db; color: white; padding: 8px; text-align: left; font-size: 10pt; }
        td { border: 1px solid #ddd; padding: 6px 8px; }
        tr:nth-child(even) { background: #f9f9f9; }
        ul, ol { margin-left: 20px; margin-bottom: 8px; }
        li { margin-bottom: 4px; }
        .header-info { margin-bottom: 20px; text-align: center; color: #7f8c8d; font-size: 9pt; }
        .tip { background: #d4edda; border: 1px solid #c3e6cb; padding: 8px; margin: 8px 0; border-radius: 4px; font-size: 9pt; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 8px; margin: 8px 0; border-radius: 4px; font-size: 9pt; }
        .box { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .two-col { display: table; width: 100%; border-spacing: 10px; }
        .two-col > div { display: table-cell; width: 50%; vertical-align: top; }
        hr { border: none; border-top: 1px solid #eee; margin: 15px 0; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 2px solid #3498db; font-size: 8pt; color: #7f8c8d; text-align: center; }
        .page-break { page-break-before: always; }
        .example-box { background: #e8f4fd; border-left: 4px solid #3498db; padding: 8px; margin: 8px 0; }
        .example-box strong { color: #2980b9; }
    </style>
</head>
<body>
    <h1>Chat Bot - User Guide</h1>
    
    <div class="header-info">
        <p><strong>Available Tables:</strong> Members, Attendance, Vitals (BP/Sugar/Pulse), ADL</p>
        <p>Simply type your question in natural language and the chat bot will help you!</p>
    </div>

    <div class="section">
        <h2>1. MEMBERS - Questions You Can Ask</h2>
        
        <div class="two-col">
            <div>
                <h3>Basic Information</h3>
                <div class="example-box">
                    <strong>How many members in morning shift?</strong><br>
                    <strong>Show all female members</strong><br>
                    <strong>List members in noon shift</strong><br>
                    <strong>Count total male members</strong>
                </div>
                
                <h3>Birthdays</h3>
                <div class="example-box">
                    <strong>Whose birthday is this week?</strong><br>
                    <strong>Show members with birthdays in March</strong><br>
                    <strong>List all January birthdays</strong><br>
                    <strong>Birthdays coming up in next 7 days</strong>
                </div>
            </div>
            <div>
                <h3>PA Authorization</h3>
                <div class="example-box">
                    <strong>PA expiring this month?</strong><br>
                    <strong>Members with PA ending soon</strong><br>
                    <strong>Show PA status for all members</strong><br>
                    <strong>PA expiring in next 30 days</strong>
                </div>
                
                <h3>HNP Status</h3>
                <div class="example-box">
                    <strong>HNP expiring this month?</strong><br>
                    <strong>Members with HNP expiring soon</strong><br>
                    <strong>Show HNP expiration dates</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>2. ATTENDANCE - Questions You Can Ask</h2>
        
        <div class="two-col">
            <div>
                <h3>Daily/Weekly Attendance</h3>
                <div class="example-box">
                    <strong>How many attended today?</strong><br>
                    <strong>Attendance for this week</strong><br>
                    <strong>Who was absent yesterday?</strong><br>
                    <strong>Total attendance last week</strong>
                </div>
            </div>
            <div>
                <h3>Monthly/Custom Range</h3>
                <div class="example-box">
                    <strong>Attendance for March 2026</strong><br>
                    <strong>Show attendance from Jan 1 to Jan 31</strong><br>
                    <strong>How many attended this month?</strong><br>
                    <strong>Attendance report for last 30 days</strong>
                </div>
            </div>
        </div>
        
        <h3>By Shift</h3>
        <div class="example-box">
            <strong>Morning shift attendance this week</strong> | 
            <strong>Noon shift attendance today</strong> | 
            <strong>Compare attendance between shifts</strong>
        </div>
    </div>

    <div class="section">
        <h2>3. VITALS (BP, Sugar, Pulse, Weight) - Questions</h2>
        
        <div class="two-col">
            <div>
                <h3>Blood Pressure</h3>
                <div class="example-box">
                    <strong>BP readings for P001</strong><br>
                    <strong>Show blood pressure this week</strong><br>
                    <strong>Who has high BP?</strong><br>
                    <strong>Latest BP for all members</strong>
                </div>
                
                <h3>Blood Sugar</h3>
                <div class="example-box">
                    <strong>Sugar levels for P002</strong><br>
                    <strong>Show members with high sugar</strong><br>
                    <strong>Blood sugar this month</strong>
                </div>
            </div>
            <div>
                <h3>Weight & Pulse</h3>
                <div class="example-box">
                    <strong>Weight for P003</strong><br>
                    <strong>Who gained weight this month?</strong><br>
                    <strong>Pulse rate for members</strong><br>
                    <strong>Temperature readings</strong>
                </div>
                
                <h3>Full Report</h3>
                <div class="example-box">
                    <strong>Full vitals for P001 on March 10</strong><br>
                    <strong>Monthly vitals summary</strong><br>
                    <strong>Health report for member</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>4. ADL (Daily Living) - Questions</h2>
        
        <div class="example-box">
            <strong>ADL status for P001</strong> | 
            <strong>Who needs help with eating?</strong> | 
            <strong>Members with locomotion issues</strong><br>
            <strong>ADL assessment for March</strong> | 
            <strong>Who requires transfer help?</strong> | 
            <strong>Toileting assistance needed</strong>
        </div>
    </div>

    <hr>

    <div class="section page-break">
        <h2>5. CROSS-TABLE QUESTIONS (Most Powerful!)</h2>
        
        <div class="box">
            <h3>Combined Data Queries</h3>
            <div class="example-box">
                <strong>Attendance and vitals for members with expiring PA</strong><br>
                <strong>BP readings for members who attended today</strong><br>
                <strong>Vitals of morning shift members this week</strong><br>
                <strong>ADL status for members with birthdays this month</strong><br>
                <strong>Which members with high BP have poor attendance?</strong><br>
                <strong>Full health summary for P001 this month</strong>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>6. NATURAL LANGUAGE TIPS</h2>
        
        <table>
            <tr><th>Instead of...</th><th>Try saying...</th></tr>
            <tr><td>Count attendance</td><td>How many attended / Total attendance / Count</td></tr>
            <tr><td>Specific dates</td><td>Today, Yesterday, This week, This month</td></tr>
            <tr><td>Shift names</td><td>Morning shift / Noon shift / Shift A / Shift N</td></tr>
            <tr><td>Member ID</td><td>Participant ID like P001, or use names</td></tr>
            <tr><td>Time ranges</td><td>Last 7 days, Next 30 days, This month</td></tr>
        </table>

        <div class="tip">
            <strong>Tip:</strong> Be specific for better results! "BP for P001 in March 2026" gets better results than just "BP"
        </div>
    </div>

    <div class="section">
        <h2>7. EXAMPLES OF FULL QUESTIONS</h2>
        
        <table>
            <tr><th>Category</th><th>Example Question</th></tr>
            <tr><td>Members</td><td>"How many male members are in morning shift?"</td></tr>
            <tr><td>Birthdays</td><td>"Whose birthday is coming up this week?"</td></tr>
            <tr><td>PA/HNP</td><td>"Which members have PA expiring in the next 30 days?"</td></tr>
            <tr><td>Attendance</td><td>"Show attendance for morning shift this week"</td></tr>
            <tr><td>Vitals</td><td>"Latest blood pressure readings for all members"</td></tr>
            <tr><td>Weight</td><td>"Weight changes for P003 this month"</td></tr>
            <tr><td>ADL</td><td>"Who needs assistance with eating?"</td></tr>
            <tr><td>Combined</td><td>"Show vitals for morning shift members who attended today"</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>8. WHAT I CANNOT ANSWER</h2>
        
        <div class="warning">
            <strong>I can only help with member data questions.</strong><br><br>
            I cannot answer:
            <ul style="margin-top:5px; margin-bottom:5px;">
                <li>Questions about weather, news, or general knowledge</li>
                <li>Questions about passwords, users, or system settings</li>
                <li>Queries about tables other than: members, partattendence, partbp, partadl</li>
            </ul>
        </div>
        
        <div class="tip">
            <strong>If I cannot answer your question, I'll let you know politely and suggest what I can help with!</strong>
        </div>
    </div>

    <div class="footer">
        <p>Chat Bot User Guide | Available 24/7 for member data queries</p>
        <p>Questions? Contact your system administrator</p>
    </div>
</body>
</html>
