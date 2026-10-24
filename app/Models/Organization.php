<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $connection = 'master';

    protected $table = 'organizations';

    protected $fillable = [
        'organization_name',
        'database_name',
        'status'
    ];
}
