<?php

namespace App\Modules\BankFeed\Models;

use Illuminate\Database\Eloquent\Model;

class BankImport extends Model
{
    // If your table name is exactly `bank_imports`, this is optional
    protected $table = 'bank_imports';

    // If you have an auto-increment `id` primary key, this is fine.
    // If not, adjust accordingly.
    protected $primaryKey = 'id';

    // Allow mass assignment on these fields.

    protected $fillable = [
        'fingerprint',
        'bank_id',
        'bank_name',
        'source_file',
        'txn_date',
        'description',
        'debit',
        'credit',
        'category_id',
        'is_posted',
        'trans_no',
    ];

    // If you don't have created_at / updated_at, set to false
    public $timestamps = true; // or false if your table has no timestamps
}
