<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    protected $table = 'gnmscomp';
    protected $guarded = [];
    protected $primaryKey = 'comp_cd';
    public $timestamps = false;
}
