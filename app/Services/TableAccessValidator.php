<?php

namespace App\Services;

class TableAccessValidator
{
    private array $allowedTables;
    private array $blockedTerms;

    public function __construct()
    {
        $this->allowedTables = ['members', 'partattendence', 'partbp', 'partadl'];
        
        $this->blockedTerms = [
            'password', 'passwd', 'pwd',
            'login', 'credentials', 'auth',
            'user', 'users', 'admin', 'administrator',
            'email', 'mail',
            'role', 'permission', 'permissions',
            'token', 'api_key', 'apikey', 'secret',
            'bank', 'account', 'financial',
            'salary', 'wage', 'payment',
            'delete', 'drop', 'truncate', 'alter',
            'insert', 'update', 'replace',
            'union', 'exec', 'execute',
            'script', 'javascript',
        ];
    }

    public function isAllowedQuery(string $query): bool
    {
        $lowerQuery = strtolower($query);

        foreach ($this->blockedTerms as $term) {
            if (str_contains($lowerQuery, $term)) {
                return false;
            }
        }

        return true;
    }

    public function validateTableAccess(string $tableName): bool
    {
        return in_array(strtolower($tableName), $this->allowedTables);
    }

    public function validateColumnAccess(string $tableName, string $columnName): bool
    {
        $schemaService = new TableSchemaService();
        $schema = $schemaService->getSchema($tableName);

        if ($schema === null) {
            return false;
        }

        $columns = array_keys($schema['columns']);
        return in_array(strtolower($columnName), array_map('strtolower', $columns));
    }

    public function sanitizeQuery(string $query): string
    {
        $sanitized = preg_replace('/[;"\'<>|&$]/', '', $query);
        return trim($sanitized);
    }

    public function getAllowedTables(): array
    {
        return $this->allowedTables;
    }

    public function getBlockedTerms(): array
    {
        return $this->blockedTerms;
    }
}
