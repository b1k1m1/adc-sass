<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backup.index');
    }

    public function run()
    {
        dispatch(function () {
            exec('php artisan backup:run-local');
        });

        return back()->with([
            'message' => 'Backup started successfully',
            'alert-type' => 'success',
        ]);
    }
}
