<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faglmsacctcategory extends Model
{
    protected $table = 'faglmsacctcategory';
    protected $primaryKey =['id','company_id'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
}
