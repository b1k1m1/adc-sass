<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $guarded = [];
    protected $primaryKey = 'staffid';
    public $timestamps = false;
}
