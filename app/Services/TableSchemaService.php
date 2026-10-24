<?php

namespace App\Services;

class TableSchemaService
{
    private array $schemas = [];

    public function __construct()
    {
        $this->schemas = [
            'members' => [
                'columns' => [
                    'ParticipantId' => 'string - Unique member ID (e.g., "001", "P005")',
                    'FirstName' => 'string - Member first name',
                    'MiddleName' => 'string - Member middle name (nullable)',
                    'LastName' => 'string - Member last name',
                    'DateOfBirth' => 'date - Format YYYY-MM-DD',
                    'Sex' => 'string - "M" for Male, "F" for Female',
                    'Trans_flag' => 'string - "A" = Morning shift, "N" = Noon shift',
                    'medicaid_PA_no' => 'string - PA authorization number',
                    'medicaid_PA_End' => 'date - PA expiration date',
                    'HNP_Expiry_date' => 'date - HNP program expiration date',
                    'HMO_enrolled' => 'string - HMO enrollment status',
                ],
                'description' => 'Main members/participants table for adult day care program',
                'key_column' => 'ParticipantId',
            ],
            'partattendence' => [
                'columns' => [
                    'ParticipantId' => 'string - Member ID (FK to members)',
                    'CalenderDate' => 'date - Format YYYY-MM-DD',
                    'AttendanceYN' => 'string - "Y" = Present, "N" = Absent',
                    'SerialNo' => 'integer - Auto-increment tracking ID',
                ],
                'description' => 'Daily attendance records for members',
                'key_columns' => ['ParticipantId', 'CalenderDate'],
            ],
            'partbp' => [
                'columns' => [
                    'ParticipantId' => 'string - Member ID (FK to members)',
                    'CalenderDate' => 'date - Format YYYY-MM-DD',
                    'LowBP' => 'integer - Systolic blood pressure (top number)',
                    'HighBP' => 'integer - Diastolic blood pressure (bottom number)',
                    'Sugar' => 'float - Blood sugar level',
                    'Pulse' => 'integer - Pulse rate',
                    'Temperature' => 'float - Body temperature',
                    'Weight' => 'float - Weight in pounds',
                    'ToiletYN' => 'string - Toileting assistance needed ("Y"/"N")',
                    'TransferYN' => 'string - Transfer assistance needed ("Y"/"N")',
                    'LocomotionYN' => 'string - Locomotion assistance needed ("Y"/"N")',
                    'EatingYN' => 'string - Eating assistance needed ("Y"/"N")',
                ],
                'description' => 'Vital signs and health measurements for members',
                'key_columns' => ['ParticipantId', 'CalenderDate'],
            ],
            'partadl' => [
                'columns' => [
                    'participantid' => 'string - Member ID (lowercase, FK to members)',
                    'calenderDate' => 'date - Format YYYY-MM-DD (lowercase)',
                    // Additional ADL-specific fields
                ],
                'description' => 'Activities of Daily Living assessments',
                'key_columns' => ['participantid', 'calenderDate'],
                'note' => 'Column names are lowercase in this table',
            ],
        ];
    }

    public function getSchema(string $tableName): ?array
    {
        return $this->schemas[strtolower($tableName)] ?? null;
    }

    public function getAllSchemas(): array
    {
        return $this->schemas;
    }

    public function getAllowedTables(): array
    {
        return array_keys($this->schemas);
    }

    public function getPromptContext(): string
    {
        $context = "You have access to these tables only:\n\n";

        foreach ($this->schemas as $tableName => $schema) {
            $context .= "** {$tableName} **\n";
            $context .= "Description: {$schema['description']}\n";
            
            if (isset($schema['key_column'])) {
                $context .= "Primary Key: {$schema['key_column']}\n";
            }
            if (isset($schema['key_columns'])) {
                $context .= "Composite Key: " . implode(', ', $schema['key_columns']) . "\n";
            }
            if (isset($schema['note'])) {
                $context .= "Note: {$schema['note']}\n";
            }
            
            $context .= "Columns:\n";
            foreach ($schema['columns'] as $column => $type) {
                $context .= "  - {$column}: {$type}\n";
            }
            $context .= "\n";
        }

        return $context;
    }
}
