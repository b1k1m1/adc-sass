<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorsController extends Controller
{
     public function DoctorsDashboard(){

        return view('doctors.doctors_index', ['title' => "Doctor's Management",'module' => 'doctors' ]);

    } // End Method
}
