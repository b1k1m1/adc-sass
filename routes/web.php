<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ArController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardApptActionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EraController;
use App\Http\Controllers\FrequencyMonitorDropdownController;
use App\Http\Controllers\HistoryReportController;
use App\Http\Controllers\HMODropdownController;
use App\Http\Controllers\Icd10Controller;
use App\Http\Controllers\Icd10MasterController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPaymentPlanController;
use App\Http\Controllers\NursingController;
use App\Http\Controllers\ParticipantActivityAssesmentController as PAA;
use App\Http\Controllers\PhysicianCategoryController;
use App\Http\Controllers\PhysicianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftAccountController;
use App\Http\Controllers\StatusDropdownController;
use App\Http\Controllers\SysadminController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\VehicleMasterController;
use App\Http\Controllers\BillingMemberController;
use App\Http\Controllers\ClaimRejectionController;
use App\Models\Member;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\X12SettingsController;
use App\Http\Controllers\ClaimsReportsController;
use App\Http\Controllers\EraReportsController;
use App\Http\Controllers\SpecialReportController;




Route::redirect('/', 'login');

Route::middleware(['auth'])->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // JSON feeds used by the dashboard
    Route::get('/dash/attendance/weekly', [DashboardController::class, 'weeklyAttendance'])->name('dash.weeklyAttendance');
    Route::get('/dash/calendar/events', [DashboardController::class, 'calendarEvents'])->name('dash.calendarEvents');
    Route::get('/dash/calendar/events-by-day', [DashboardController::class, 'eventsByDay'])->name('dash.eventsByDay');
    Route::get('/dash/appt/events', [DashboardController::class, 'apptEvents'])->name('dash.apptEvents');
    Route::get('/dash/appt/by-day', [DashboardController::class, 'apptByDay'])->name('dash.apptByDay');
    Route::get('/lang/{locale}', function ($locale) {
        $allowed = ['en', 'es', 'ko']; // add languages here

        abort_unless(in_array($locale, $allowed), 404);

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return redirect()->back();
    })->name('lang.switch');
});

Route::middleware(['auth', 'perm:sysadmin.menu'])->controller(ProfileController::class)->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //Route::get('/profile/logout', [ProfileController::class, 'logout'])->name('profile.logout');
    Route::get('/profile/allusers', [ProfileController::class, 'AllUsers'])->name('all.users');
    Route::get('/profile/adduser', [ProfileController::class, 'AddUser'])->name('add.user');
    Route::get('/profile/edituser/{id}', [ProfileController::class, 'EditUser'])->name('edit.user');
    Route::post('/profile/store', [ProfileController::class, 'StoreUser'])->name('user.store');
    Route::post('/profile/update', [ProfileController::class, 'UpdateUser'])->name('user.update');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    Route::middleware(['perm:accounts.menu'])->group(function () {
        require __DIR__ . '/bankfeed.php';
    });

    Route::middleware(['perm:nursing.menu'])->group(function () {
        require __DIR__ . '/daycareform.php';
        require __DIR__ . '/pain.php';
        require __DIR__ . '/nursing.php';
        require __DIR__ . '/nutrition.php';
        require __DIR__ . '/dietary.php';
        require __DIR__ . '/pharmacistreview.php';
        require __DIR__ . '/medicine.php';
        require __DIR__ . '/medicineassessment.php';
    });
});

Route::middleware(['auth'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/profile/logout', [ProfileController::class, 'logout'])->name('profile.logout');


    // 1) Lookup routes first (specific)
    Route::prefix('lookup')->group(function () {
        Route::get('icd10', [Icd10Controller::class, 'search'])->name('lookup.icd10');
        Route::get('icd10/{code}', [Icd10Controller::class, 'show'])->name('lookup.icd10.show');
    });


    // Route for members
    Route::middleware(['auth', 'perm:members.menu'])->controller(MemberController::class)->group(function () {

        Route::get('/members/dashboard', 'MembersDashboard')->name('members.dashboard');
        Route::get('/members/allmembers', 'AllMembers')->name('all.members');
        Route::get('/members/addmember',  'AddMember')->name('add.member');
        Route::post('/members/storemember',  'StoreMember')->name('store.member');
        Route::post('/members/update', 'UpdateMember')->name('member.update');
        Route::get('/members/editmember/{ParticipantId}', 'EditMember')->name('edit.member');
        Route::get('/members/{id}/medical-info', 'getMedicalInfo');
        Route::post('members/update-medical-info', 'updateMedicalInfo');
        Route::get('/members/uploadimage',  'UploadMemberImage')->name('uploadimage.member');
        Route::post('/members/upload-image', 'StoreImage')->name('image.upload');
        Route::get('/members/allmembers-list',  'AllMemberList')->name('all.member.list');
        Route::get('/members/exportpdf', 'exportPdf')->name('members.export.pdf');
        // HTML page
        Route::get('/members/members-census', 'membersSignSheet')->name('members_census_sheet');
        // PDF export (mPDF)
        Route::get('/members/members-census/pdf', 'membersSignSheetPdf')->name('members_census_sheet_pdf');

        Route::get('/members/import', 'importForm')->name('members.import.form');
        Route::post('/members/import/preview', 'importPreview')->name('members.import.preview');
        Route::post('/members/import/confirm',  'importConfirm')->name('members.import.confirm');
        Route::post('/members/import/cancel',  'importCancel')->name('members.import.cancel');
        Route::get('/members/import-guide', 'showImportGuide')->name('members.import.guide');

        Route::get('/members/table-preferences', 'getTablePreferences')->name('members.table-preferences.get');
        Route::post('/members/table-preferences', 'saveTablePreferences')->name('members.table-preferences.save');

        Route::get('/members/reports/dietary-restrictions/{type?}', 'dietaryRestrictionReport')->name('members.reports.dietary-restrictions');
        Route::get('/members/reports/dietary-restrictions-pdf/{type}', 'dietaryRestrictionReportPdf')->name('members.reports.dietary-restrictions.pdf');
        Route::get('/members/reports/allergy/{type?}', 'allergyReport')->name('members.reports.allergy');
        Route::get('/members/reports/allergy-pdf/{type}', 'allergyReportPdf')->name('members.reports.allergy.pdf');
    });

    Route::middleware(['auth', 'perm:members.menu'])->controller(HistoryReportController::class)->group(function () {
        Route::get('/members', 'index')->name('index');
        Route::get('/attendance',  'attendanceHistory')->name('attendance.history');
        Route::get('/vitals', 'vitalsHistory')->name('vitals.history');
        Route::get('/adl', 'adlHistory')->name('adl.history');
        Route::get('/historical-members/attendance/pdf', 'attendanceHistoryPdf')->name('attendance.history.pdf');
        Route::get('/historical-members/vitals/pdf', 'vitalsHistoryPdf')->name('vitals.history.pdf');
        Route::get('/historical-members/adl/pdf', 'adlHistoryPdf')->name('adl.history.pdf');
    });

    Route::middleware(['auth', 'perm:members.menu'])->controller(ApplicantController::class)->group(function () {
        Route::get('/applicants', 'index')->name('applicants.index');
        Route::get('/applicants/create', 'create')->name('applicants.create');
        Route::post('/applicants', 'store')->name('applicants.store');
        Route::get('/applicants/{applicant}/edit', 'edit')->name('applicants.edit');
        Route::put('/applicants/{applicant}', 'update')->name('applicants.update');
        Route::get('/applicants/{applicant}/pdf', 'pdf')->name('applicants.pdf');
    });

    Route::middleware(['auth', 'perm:nursing.menu'])->controller(NursingController::class)->group(function () {

        Route::get('/nursing/dashboard', 'NursingDashboard')->name('nursing.dashboard');
        //Route::get('/nursing/allmembers', 'NursingAllMembers')->name('nursing.select_members');
        // ✅ Default redirect: when user goes to /attendance/dates, show Active list by default
        Route::redirect('/nursing/allmembers', '/nursing/allmembers/A');
        // ✅ Unified route for both Active and Non-Active screens
        Route::get('/nursing/allmembers/{type}', 'NursingAllMembers')->whereIn('type', ['A', 'N', 'G'])->name('nursing.members');

        Route::get('/nursing/editbpdata/{ParticipantId}/{monthYear}/{type}', 'NursingEditBPData')->name('nursing.edit_bpdata');

        Route::post('/nursing/storebpdata/{ParticipantId}/{monthYear}/{type}', 'StoreBPData')->name('nursing.store_bpdata');

        Route::get('/vitals/print', 'printView')->name('vitals.print'); // optional: if you want a clean print-only view in a new tab
        Route::get('/vitals/pdf', 'exportMonthlyVitalsPdf')->name('vitals.pdf');
        // Route for ADL Data
        Route::get('/nursing/editadldata/{ParticipantId}/{monthYear}/{type}', 'NursingEditADLData')->name('nursing.edit_adldata');
        Route::get('/adl/print', 'printADLView')->name('adl.print'); // optional: if you want a clean print-only view in a new tab
        Route::get('/adl/pdf', 'exportADLPdf')->name('adl.pdf');

        // Report Part

        // Monthly Vitals Report selection screen
        Route::get('/nursing/reports/monthly-vitals', 'monthlyVitalsReport')->name('nursing.reports.monthly-vitals');
        // Export selected member/month to PDF
        Route::get('/nursing/reports/monthly-vitals/pdf', 'exportMonthlyVitalsPdf')->name('nursing.reports.monthly-vitals.pdf');
        Route::get('/nursing/reports/daily-vitals', 'dailyVitalsReport')->name('nursing.reports.daily-vitals');
        Route::get('/nursing/reports/daily-vitals/pdf/{type?}', 'exportDailyVitalsPdf')->name('nursing.reports.daily-vitals.pdf');
        Route::get('/nursing/reports/daily-adl', 'dailyAdlReport')->name('nursing.reports.daily-adl');
        Route::get('/nursing/reports/daily-adl/pdf/{type?}', 'exportDailyAdlPdf')->name('nursing.reports.daily-adl.pdf');

        Route::get('/bp-report/member', 'bpReportMember')->name('nursing.bp_report.member');
        Route::get('/bp-report/member/pdf', 'bpReportMemberPdf')->name('nursing.bp_report.member.pdf');
        Route::get('/bp-report/member/excel', 'bpReportMemberExcel')->name('nursing.bp_report.member.excel');
        Route::get('/bp-report/shift', 'bpReportShift')->name('nursing.bp_report.shift');
        Route::get('/bp-report/shift/pdf', 'bpReportShiftPdf')->name('nursing.bp_report.shift.pdf');
        Route::get('/bp-report/shift/excel',  'bpReportShiftExcel')->name('nursing.bp_report.shift.excel');

        Route::get('/nursing/monitoring-report', 'monitoringReport')->name('nursing.monitoring_report');
        Route::get('/nursing/monitoring-report/pdf', 'monitoringReportPdf')->name('nursing.monitoring_report.pdf');
        Route::get('/nursing/monitoring-report/excel', 'monitoringReportExcel')->name('nursing.monitoring_report.excel');

        // Nursing - Vitals Entry
        Route::get('/nursing/vitals-entry/latest', 'latestVitalsEntry')->name('nursing.vitals.entry.latest');
        Route::get('/nursing/vitals-entry/{type?}', 'vitalsEntry')->whereIn('type', ['A', 'N', 'G'])->name('nursing.vitals.entry');
        Route::post('/nursing/vitals-entry/save', 'saveVitalsEntry')->name('nursing.vitals.entry.save');
        Route::get('/nursing/vitals-entry/pdf/{type?}', 'exportDailyVitalsPdf')->whereIn('type', ['A', 'N'])->name('nursing.vitals.pdf');

        // Nursing - ADL Entry
        Route::get('/nursing/adl-entry/latest', 'latestAdlEntry')->name('nursing.adl.entry.latest');
        Route::get('/nursing/adl-entry/{type?}', 'adlEntry')->whereIn('type', ['A', 'N', 'G'])->name('nursing.adl.entry');
        Route::post('/nursing/adl-entry/save', 'saveAdlEntry')->name('nursing.adl.entry.save');
        Route::get('/nursing/adl-entry/pdf/{type?}', 'exportDailyAdlPdf')->whereIn('type', ['A', 'N'])->name('nursing.adl.pdf');

        Route::get('/nursing/comments', 'nursingComments')->name('nursing.comments');
        Route::get('/nursing/comments/members/{shift}', 'nursingCommentMembers')->whereIn('shift', ['A', 'N'])->name('nursing.comments.members');
        Route::get('/nursing/comments/load', 'loadNursingComments')->name('nursing.comments.load');
        Route::post('/nursing/comments/save', 'saveNursingComments')->name('nursing.comments.save');
        Route::get('/nursing/comments/pdf', 'exportNursingCommentsPdf')->name('nursing.comments.pdf');

        Route::get('/nursing/90day-summary/{type?}', 'nursing90DaySummary')->whereIn('type', ['A', 'N'])->name('nursing.90day.summary');
        Route::get('/nursing/90day-summary/load/{type?}', 'loadNursing90DaySummary')->whereIn('type', ['A', 'N'])->name('nursing.90day.summary.load');
        Route::post('/nursing/90day-summary/save', 'saveNursing90DaySummary')->name('nursing.90day.summary.save');
        Route::get('/nursing/90day-summary/pdf/{id}', 'exportNursing90DaySummaryPdf')->name('nursing.90day.summary.pdf');

        Route::get('/nursing/assessment-summary/{type?}', 'nursingAssessmentSummary')->whereIn('type', ['A', 'N'])->name('nursing.assessment.summary');
        Route::get('/nursing/assessment-summary/load/{type?}', 'loadNursingAssessmentSummary')->whereIn('type', ['A', 'N'])->name('nursing.assessment.summary.load');
        Route::post('/nursing/assessment-summary/save', 'saveNursingAssessmentSummary')->name('nursing.assessment.summary.save');
        Route::get('/nursing/assessment-summary/pdf/{id}', 'exportNursingAssessmentSummaryPdf')->name('nursing.assessment.summary.pdf');

        Route::get('/nursing/reports/quarterly-assessment-members', 'QuarterlyAssessmentMembersReport')->name('nursing.quarterly.assessment.members');
        Route::get('/nursing/reports/quarterly-assessment-members/pdf', 'QuarterlyAssessmentMembersPdf')->name('nursing.quarterly.assessment.members.pdf');
        Route::get('/nursing/reports/quarterly-assessment-members/excel', 'QuarterlyAssessmentMembersExcel')->name('nursing.quarterly.assessment.members.excel');

        Route::get('/nursing/nurse-log', 'NurseLog')->name('nursing.nurse.log');
        Route::get('/nursing/nurse-log/pdf', 'NurseLogPdf')->name('nursing.nurse.log.pdf');
    });

    Route::middleware(['auth', 'perm:attendance.menu'])->controller(AttendanceController::class)->group(function () {

        Route::get('/attendance/dashboard', 'AttendanceDashboard')->name('attendance.dashboard');
        //Route::get('/attendance/alldates', 'AttendanceAllDates')->name('attendance.select_dates');

        // ✅ Default redirect: when user goes to /attendance/dates, show Active list by default
        Route::redirect('/attendance/dates', '/attendance/dates/A');

        // ✅ Unified route for both Active and Non-Active screens
        Route::get('/attendance/dates/{type}', 'AttendanceDates')->whereIn('type', ['A', 'N'])->name('attendance.dates');

        Route::get('/attendance/daily/{CalenderDate}', 'AttendanceEntryDaily')->name('attendance.daily');

        Route::get('/attendance/{type?}', 'index')->whereIn('type', ['A', 'N'])->name('attendance.index');

        Route::post('/attendance/save', 'saveAttendance')->name('attendance.save');
        Route::get('/attendance/weekly', 'AttendanceEntryWeekly')->name('attendance.weekly');
        Route::get('/attendance/weekly/check', 'AttendanceEntryWeeklyCheck')->name('attendance.weekly_mark');
        Route::get('/attendance/weekly', 'weeklyAttendance')->name('attendance.weekly.entry');

        Route::get('/attendance/weekly/checkmark', 'weeklyAttendanceCheckmark')->name('attendance.weekly.entry_mark');
        Route::post('/attendance/weekly/save', 'saveWeeklyAttendance')->name('attendance.weekly.save');
        //
        // default redirect to Active
        Route::redirect('/attendance-report', '/attendance-report/A');
        Route::get('/attendance-report/{type?}', 'attendanceReport')->whereIn('type', ['A', 'N'])->name('attendance.report');
        //Route::get('/attendance-report', 'attendanceReport')->name('attendance.report');

        Route::redirect('/attendance-report-check', '/attendance-report-check/A');
        Route::get('/attendance-report-check/{type?}', 'attendanceReportCheck')->whereIn('type', ['A', 'N'])->name('attendance.report.check');
        //Route::get('/attendance-report-check', 'attendanceReportCheck')->name('attendance.report.check');

        Route::redirect('/attendance-export-pdf', '/attendance-export-pdf/A');
        Route::get('/attendance-export-pdf/{type?}', 'exportPDF')->whereIn('type', ['A', 'N'])->name('attendance.export.pdf');
        //Route::get('/attendance-export-pdf', 'exportPDF')->name('attendance.export.pdf');
        //
        Route::redirect('/attendance-export-pdf-checked', '/attendance-export-pdf-checked/A');
        Route::get('/attendance-export-pdf-checked/{type?}', 'exportPDFChecked')->whereIn('type', ['A', 'N'])->name('attendance.export.pdf.checked');
        //Route::get('/attendance-export-pdf-checked', 'exportPDFChecked')->name('attendance.export.pdf.checked');

        Route::redirect('/attendance-export-excel', '/attendance-export-excel/A');
        Route::get('/attendance-export-excel/{type?}', 'exportExcel')->whereIn('type', ['A', 'N'])->name('attendance.export.excel');
        //Route::get('/attendance-export-excel', 'exportExcel')->name('attendance.export.excel');

        Route::redirect('/attendance-export-excel-billing', '/attendance-export-excel-billing/A');
        Route::get('/attendance-export-excel-billing/{type?}', 'exportExcelBilling')->whereIn('type', ['A', 'N'])->name('attendance.export.excel.billing');

        Route::redirect('/attendance-export-excel-billingPA', '/attendance-export-excel-billingPA/A');
        Route::get('/attendance-export-excel-billingPA/{type?}', 'exportExcelBillingPA')->whereIn('type', ['A', 'N'])->name('attendance.export.excel.billingPA');

        //Route::get('/attendance-export-excel-billing', 'exportExcelBilling')->name('attendance.export.excel.billing');

        /*
    Route::get('/check-vars', function () {
        return response()->json([
            'max_input_vars' => ini_get('max_input_vars'),
            'loaded_php_ini' => php_ini_loaded_file(),
        ]);
    });
    */
        Route::get('/attendance/member-wise', 'memberWiseReport')->name('attendance.memberwise');
        Route::get('/attendance/member-wise/member-search', 'searchMembersForAttendance')->name('attendance.memberwise.member-search');
        Route::get('/attendance/member-wise/pdf', 'memberWisePdf')->name('attendance.memberwise.pdf');
        Route::get('/attendance/member-wise/excel', 'memberWiseExcel')->name('attendance.memberwise.excel');

        Route::get('/attendance/member-range-crosstab', 'memberRangeCrosstabReport')->name('attendance.memberrange');
        Route::get('/attendance/member-range-crosstab/member-search', 'searchMembersForAttendance')->name('attendance.memberrange.member-search');
        Route::get('/attendance/member-range-crosstab/pdf', 'memberRangeCrosstabPdf')->name('attendance.memberrange.pdf');
        Route::get('/attendance/member-range-crosstab/excel', 'memberRangeCrosstabExcel')->name('attendance.memberrange.excel');

        Route::get('/attendance/hmo-crosstab', 'hmoCrosstabReport')->name('attendance.hmocrosstab');
        Route::get('/attendance/hmo-crosstab/pdf', 'hmoCrosstabPdf')->name('attendance.hmocrosstab.pdf');
        Route::get('/attendance/hmo-crosstab/excel', 'hmoCrosstabExcel')->name('attendance.hmocrosstab.excel');
        Route::get('/attendance/statistical-report', 'attendanceStatisticalReport')->name('attendance.statistical.report');
        Route::get('/attendance/statistical-report/pdf', 'attendanceStatisticalReportPdf')->name('attendance.statistical.report.pdf');
    });


    // Role and Permission Route

    Route::middleware(['auth', 'perm:roles.menu'])->controller(RoleController::class)->group(function () {

        // Role and Permisson Route
        Route::get('/all/permission', 'AllPermission')->name('all.permission');
        Route::get('/add/permission', 'AddPermission')->name('add.permission');
        Route::post('/store/permission', 'StorePermission')->name('store.permission');
        Route::get('/edit/permission/{id}', 'EditPermission')->name('edit.permission');
        Route::post('/update/permission', 'UpdatePermission')->name('update.permission');
        Route::get('/delete/permission/{id}', 'DeletePermission')->name('delete.permission');
        // Roles
        Route::get('/all/role', 'AllRole')->name('all.role');
        Route::get('/add/role', 'AddRole')->name('add.role');
        Route::post('/store/role', 'StoreRole')->name('store.role');
        Route::get('/edit/role/{id}', 'EditRole')->name('edit.role');
        Route::post('/update/role', 'UpdateRole')->name('update.role');
        Route::get('/delete/role/{id}', 'DeleteRole')->name('delete.role');
        Route::get('/add/roles/permission', 'AddRolesPermission')->name('add.roles.permission');
        Route::post('store/roles/permission', 'StoreRolesPermission')->name('store.role.permission');
        Route::get('/all/roles/permission', 'AllRolesPermission')->name('all.roles.permission');
        Route::get('/edit/roles/permission/{id}', 'EditRolesPermission')->name('edit.roles.permission');
        Route::post('/update/roles/permission/{id}', 'UpdateRolesPermission')->name('update.roles.permission');
        Route::get('/delete/roles/permission/{id}', 'DeleteRolePermission')->name('delete.roles.permission');
    });

    // Seetings Dropdown Controller

    Route::controller(DropdownController::class)->group(function () {

        Route::get('/all/hmodropdown', 'AllHmoDropdown')->name('all.hmodropdown');
        Route::get('/all/statusdropdown', 'AllStatusDropdown')->name('all.statusdropdown');
        Route::get('/all/frequencydropdown', 'AllFrequencyDropdown')->name('all.frequencydropdown');
    });

    // User Profile Route

    Route::controller(AdminController::class)->group(function () {
        Route::get('/admin/profile', 'AdminProfile')->name('admin.profile');
        Route::get('/admin/edit', 'AdminEdit')->name('admin.edit');
        Route::post('/admin/profile/store', 'AdminProfileStore')->name('admin.profile.store');
    });

    // User Add and Edit Route
    Route::middleware(['auth', 'perm:sysadmin.menu'])->controller(SysadminController::class)->group(function () {

        Route::get('/sysadmin/dashboard', 'SysadminDashboard')->name('sysadmin.dashboard');
        Route::get('/user/uploadimage',  'UploadUserImage')->name('uploadimage.user');
        Route::post('/user/upload-image', 'StoreImage')->name('user.image.upload');
    });

    // Employee / Staff Menu
    Route::middleware(['auth', 'perm:staff.menu'])->controller(EmployeeController::class)->group(function () {

        Route::get('/employee/dashboard', 'EmployeeDashboard')->name('employee.dashboard');
        Route::get('/employee/listall', 'AllEmployeeList')->name('all.employee.list');
        //Route::get('/employee/all', 'AllEmployee')->name('all.employee');
        Route::get('/employee/add', 'AddEmployee')->name('add.employee');
        Route::get('/employee/edit/{id}', 'EditEmployee')->name('edit.employee');
        Route::post('/employee/store', 'StoreEmployee')->name('employee.store');
        Route::post('/employee/update', 'UpdateEmployee')->name('employee.update');
        Route::get('/employee/uploadimage',  'UploadEmployeeImage')->name('uploadimage.employee');
        Route::post('/employee/upload-image', 'StoreImage')->name('employee.image.upload');
        Route::get('/employees/exportpdf', 'exportPdf')->name('employee.export.pdf');
        Route::get('/all/employee', 'AllEmployee')->name('all.employee');
        Route::post('/employees/table-preferences/save', 'SaveEmployeeTablePreferences')->name('employees.table-preferences.save');
    });

    Route::post('/session/extend', function () {

        if (session()->has('lastActivityTime')) {
            session()->put('lastActivityTime', now());
        } else {
            session()->put('lastActivityTime', now());
        }
        return response()->json(['message' => 'Session extended']);
    })->name('session.extend')->middleware('auth');

    // Accounts Menu
    Route::middleware(['auth', 'perm:accounts.menu'])->controller(AccountController::class)->group(function () {

        Route::get('/account-masters', 'accountMasterIndex')->name('accountmasters.index');
        Route::get('/account-masters/create', 'accountMasterCreate')->name('accountmasters.create');
        Route::post('/account-masters', 'accountMasterStore')->name('accountmasters.store');
        Route::get('/account-masters/{party_cd}/edit', 'accountMasterEdit')->name('accountmasters.edit');
        Route::put('/account-masters/{party_cd}', 'accountMasterUpdate')->name('accountmasters.update');
        Route::delete('/account-masters/{party_cd}', 'accountMasterDestroy')->name('accountmasters.destroy');

        Route::get('/accounts/dashboard', 'AccountsDashboard')->name('accounts.dashboard');
        Route::get('/accounts/enterpayment',  'EnterPayment')->name('enter.payment');
        Route::post('/accounts/storepayment', 'StorePayment')->name('store.payment');
        Route::get('/accounts/listpayment', 'ListPayment')->name('list.payment');
        Route::get('/accounts/editpayment/{trans_no}/{trans_dt}', 'EditPayment')->name('edit.payment');
        Route::post('/accounts/updatepayment', 'UpdatePayment')->name('update.payment');
        Route::get('/accounts/enterdeposit',  'EnterDeposit')->name('enter.deposit');
        Route::post('/accounts/storedeposit', 'StoreDeposit')->name('store.deposit');
        Route::get('/accounts/listdeposit', 'ListDeposit')->name('list.deposit');
        Route::get('/accounts/editdeposit/{trans_no}/{trans_dt}', 'EditDeposit')->name('edit.deposit');
        Route::post('/accounts/updatedeposit', 'UpdateDeposit')->name('update.deposit');

        Route::get('/accounts/printcheck', 'PrintCheck')->name('print.checklist');
        Route::get('/accounts/printcheck/data', 'PrintCheckData')->name('print.checklist.data');
        //Route::get('/accounts/checkprint/{check_no}', 'CheckPrinting')->name('print.check');
        Route::get('/accounts/checkprint/{trans_no}', 'CheckPrinting')->name('print.check');

        Route::get('/accounts/bankreport',  'BankReport')->name('bank.report');
        Route::get('/accounts/bankreport/pdf', 'DownloadPdf')->name('bank.book.pdf');

        Route::get('/accounts/genledger',  'GeneralLedger')->name('genledger.report');
        Route::get('/accounts/genledger/pdf', 'DownloadGenLedgerPdf')->name('general.ledger.pdf');

        // Selected Party Wise General Ledger
        Route::get('/accounts/general-ledger', 'AccountGeneralLedger')->name('general.ledger');
        Route::get('/accounts/general-ledger/pdf', 'AccountGeneralLedgerPdf')->name('general.ledger.party.pdf');

        Route::get('/accounts/journalentry',  'JournalEntry')->name('journal.entry');
        Route::post('/accounts/storejournal', 'StoreJournalEntry')->name('journal_entries.store');
        Route::get('/accounts/listjournal', 'ListJournalEntry')->name('journal.list');
        Route::get('/accounts/editjournal/{trans_no}/{trans_dt}',  'EditJournalEntry')->name('journal.edit');
        Route::put('journal_entries/{trans_no}/{trans_dt}', 'UpdateJournalEntry')->name('journal.update');
        Route::post('/accounts/copyjournal/{trans_no}/{trans_dt}', 'CopyJournalEntry')->name('journal.copy');

        Route::get('/accounts/trialbalance',  'TrialBalance')->name('trialbalance.report');
        Route::get('/accounts/trialbalance/pdf', 'DownloadTrialBalancePdf')->name('trialbalance.pdf');
        Route::get('/trialbalance/monthwise',  'TrialBalanceMonthwise')->name('trialbalance.monthwise');
        Route::get('/trialbalance/monthwise/pdf', 'TrialBalanceMonthwisePDF')->name('trialbalance.monthwise.pdf');
        Route::get('/trialbalance/withbalancemonthwise',  'TrialBalanceWithBalanceMonthwise')->name('trialbalance.balance.monthwise');
        Route::get('/trialbalance/withbalancemonthwise/pdf',  'TrialBalanceWithBalanceMonthwisePdf')->name('withbalancetrialbalance.monthwise.pdf');
        Route::get('/trialbalance/withbalancemonthwise/excel',  'TrialBalanceWithBalanceMonthwiseExcel')->name('withbalancetrialbalance.monthwise.excel');

        Route::get('/profitloss',  'ProfitLoss')->name('profitloss');
        Route::get('/profitloss/pdf', 'ProfitLossPDF')->name('profitloss.pdf');
        Route::get('/profitloss/excel', 'ProfitLossExcel')->name('profitloss.excel');
        Route::get('/profitloss/details/{party_cd}', 'ProfitLossDetails')->name('profitloss.details');
        Route::get('/profitloss/new',  'ProfitLossNew')->name('profitloss.new');
        Route::get('/profitloss/monthwise',  'ProfitLossMonthwise')->name('profitloss.monthwise');
        Route::get('/profitloss/comparison/pdf', 'ProfitLossComparisonPDF')->name('profitloss.comparison.pdf');
        Route::get('/profitloss/comparison/excel', 'ProfitLossComparisonExcel')->name('profitloss.comparison.excel');

        Route::get('/balancesheet',  'BalanceSheet')->name('balancesheet');
        Route::get('/balancesheet/pdf', 'exportBalanceSheetPDF')->name('balancesheet.pdf');
        Route::get('/balancesheet/excel', 'exportBalanceSheetExcel')->name('balancesheet.excel');

        Route::get('/accounts/fiscal-year-closing', 'fiscalYearClosingPreview')->name('accounts.fiscal-year-closing');

        Route::get('/bankrecon', 'Bankrecon')->name('bankrecon');
        Route::post('/bankrecon/load-or-create', 'BankreconLoadOrCreate')->name('bankrecon.loadOrCreate'); // bank + month
        Route::get('/bankrecon/transactions', 'BankreconTransactions')->name('bankrecon.transactions');   // JSON list
        Route::post('/bankrecon/start', 'BankreconStartStore')->name('bankrecon.startStore');
        Route::post('/bankrecon/bulk-toggle', 'BankreconBulkToggle')->name('bankrecon.bulkToggle');
        Route::post('/bankrecon/toggle', 'BankreconToggleCleared')->name('bankrecon.toggle');             // checkbox
        Route::post('/bankrecon/save-header', 'BankreconSaveHeader')->name('bankrecon.saveHeader');       // balances/dates
        Route::post('/bankrecon/close', 'BankreconClose')->name('bankrecon.close');                       // lock month
        Route::get('/bankrecon/report/{recon_id}', 'BankreconReport')->name('bankrecon.report');          // on-screen
        Route::get('/bankrecon/report/{recon_id}/pdf', 'BankreconReportPdf')->name('bankrecon.reportPdf');
        Route::get('/bank-reconciliation/{recon_id}/simplified/pdf', 'BankreconSimplePdf')->name('bankrecon.simple.pdf');
        Route::get('/bank-reconciliation/{recon_id}/simplified/excel', 'BankreconSimpleExcel')->name('bankrecon.simple.excel');
        Route::get('/bankrecon/backfill-snapshots', 'BankreconBackfillSnapshots')->name('bankrecon.backfill.snapshots');

        Route::get('/bankrecon/history', 'BankreconHistory')->name('bankrecon.history');
        Route::get('/bankrecon/history/pdf', 'BankreconHistoryPdf')->name('bankrecon.history.pdf');
        Route::get('/bankrecon/history/excel', 'BankreconHistoryExcel')->name('bankrecon.history.excel');

        Route::get('/accounts/traditional-bank-reconciliation', 'TraditionalBankReconciliation')->name('accounts.traditional.bank.reconciliation');
        Route::get('/accounts/traditional-bank-reconciliation/dates', 'TraditionalBankReconciliationDates')->name('accounts.traditional.bank.reconciliation.dates');
        Route::get('/accounts/traditional-bank-reconciliation/pdf', 'TraditionalBankReconciliationPdf')->name('accounts.traditional.bank.reconciliation.pdf');
        Route::get('/accounts/traditional-bank-reconciliation/excel', 'TraditionalBankReconciliationExcel')->name('accounts.traditional.bank.reconciliation.excel');


        Route::get('/bank-deposit-register', 'bankDepositRegister')->name('bank.deposit.register');
        Route::get('/bank-deposit-register/pdf', 'bankDepositRegisterPdf')->name('bank.deposit.register.pdf');
        Route::get('/bank-payment-register', 'bankPaymentRegister')->name('bank.payment.register');
        Route::get('/bank-payment-register/pdf', 'bankPaymentRegisterPdf')->name('bank.payment.register.pdf');

        Route::get('/bank/categorization', 'BankCategorization')->name('bank.categorization');
        Route::get('/bank/categorization/data', 'BankCategorizationData')->name('bank.categorization.data');
        Route::get('/bank/categorization/accounts/search', 'BankCategorizationAccountSearch')->name('bank.categorization.accounts.search');
        Route::post('/bank/categorization/save-selected', 'SaveBankCategorization')->name('bank.categorization.save.selected');
        Route::post('/bank/categorization/apply-rules', 'ApplyBankCategorizationRules')->name('bank.categorization.apply.rules');
    });

    // Payment Plan
    Route::middleware(['auth', 'perm:accounts.menu'])->prefix('member-payment-plan')->controller(MemberPaymentPlanController::class)->group(function () {
        Route::get('/', 'index')->name('mpp.index');
        Route::get('/data', 'data')->name('mpp.data');              // yajra json
        Route::post('/update', 'bulkUpdate')->name('mpp.update');   // bottom Update button
        Route::get('/reports/payment-plan', 'report')->name('reports.payment_plan');
        // PDF Preview & Download
        Route::get('/payment-plan/pdf', 'pdf')->name('reports.payment_plan.pdf');
    });


    // Driver Menu
    Route::middleware(['auth', 'perm:drivers.menu'])->controller(DriverController::class)->group(function () {

        Route::get('/drivers/dashboard', 'DriversDashboard')->name('drivers.dashboard');
        // Step 1: Driver selection form
        Route::get('/reports/driver-pickup/select', 'selectDriver')->name('driver-pickup.select');
        // Step 2: Show report for driver
        Route::get('/reports/driver-pickup', 'driverPickupReport')->name('driver-pickup');
        // Step 3: PDF export
        Route::get('/reports/driver-pickup/pdf', 'driverPickupReportPdf')->name('drivers_pickup.pdf');

        Route::get('/route/list', 'RouteList')->name('route.list');
        Route::get('/route/add', 'AddRoute')->name('route.add');
        Route::post('/route/store', 'StoreRoute')->name('route.store');
        Route::get('/route/edit/{Route_id}', 'EditRoute')->name('route.edit');
        Route::put('/route/update/{Route_id}', 'UpdateRoute')->name('route.update');
        Route::get('/route/status/{Route_id}', 'ChangeRouteStatus')->name('route.status');

        Route::get('/route/daily-assignment', 'DailyRouteAssignment')->name('route.daily.assignment');
        Route::get('/route/daily-assignment/members', 'GetRouteMembers')->name('route.daily.members');
        Route::post('/route/daily-assignment/store', 'StoreDailyRouteAssignment')->name('route.daily.assignment.store');
        Route::delete('/route/daily-assignment/remove/{id}', 'RemoveDailyRouteAssignment')->name('route.daily.assignment.remove');

        Route::get('/route/signing-sheet', 'RouteSigningSheet')->name('route.signing.sheet');
        Route::get('/route/signing-sheet/pdf', 'RouteSigningSheetPdf')->name('route.signing.sheet.pdf');

        Route::get('/driver/signing-sheet', 'DriverSigningSheet')->name('driver.signing.sheet');
        Route::get('/driver/signing-sheet/pdf', 'DriverSigningSheetPdf')->name('driver.signing.sheet.pdf');
    });

    Route::middleware(['auth', 'perm:drivers.menu'])->controller(VehicleMasterController::class)->prefix('vehiclemaster')->name('vehiclemaster.')->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{vehicle_id}/edit', 'edit')->name('edit');
        Route::post('/{vehicle_id}/update', 'update')->name('update');
    });

    // Activity Menu
    Route::middleware(['auth', 'perm:activity.menu'])->controller(PAA::class)->group(function () {

        Route::get('activity/dashboard', 'ActivityDashboard')->name('activity.dashboard');
        Route::get('/assessments/activity',        [PAA::class, 'form'])->name('paa.form');     // Retrieve (load existing) & Create screen
        Route::post('/assessments/activity/save',   [PAA::class, 'save'])->name('paa.save');     // Create/Update (upsert)
        Route::post('/assessments/activity/insert', [PAA::class, 'insertOnly'])->name('paa.insert');
        Route::post('/assessments/activity/update', [PAA::class, 'updateOnly'])->name('paa.update');
        Route::get('/assessments/activity/pdf', [PAA::class, 'pdf'])->name('paa.pdf');
        Route::get('/assessments/activity/report-view', [PAA::class, 'reportView'])->name('paa.report.view');

        Route::get('/initial_assessment', [PAA::class, 'index'])->name('participant.activity.assessment.index');
        Route::get('/initial_assessment/members', [PAA::class, 'getMembersByShift'])->name('participant.activity.assessment.members');
        Route::get('/initial_assessment/list', [PAA::class, 'listByMember'])->name('participant.activity.assessment.list');
        Route::get('/initial_assessment/create', [PAA::class, 'create'])->name('participant.activity.assessment.create');
        Route::get('/initial_assessment/edit/{id}', [PAA::class, 'edit'])->name('participant.activity.assessment.edit');
        Route::get('/initial_assessment/view/{id}', [PAA::class, 'view'])->name('participant.activity.assessment.view');
        Route::post('/initial_assessment/store', [PAA::class, 'store'])->name('participant.activity.assessment.store');
        Route::get('/initial_assessment/pdf/{id}', [PAA::class, 'exportPdf'])->name('participant.activity.assessment.pdf');
    });

    // Doctors Menu
    Route::get('/doctors/dashboard', [DoctorsController::class, 'DoctorsDashboard'])->name('doctors.dashboard');
    // Physician Category
    Route::prefix('physician-categories')->name('physcat.')->group(function () {
        Route::get('/', [PhysicianCategoryController::class, 'index'])->name('index');
        Route::get('/create', [PhysicianCategoryController::class, 'create'])->name('create');
        Route::post('/', [PhysicianCategoryController::class, 'store'])->name('store');
        Route::get('/{id}', [PhysicianCategoryController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PhysicianCategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PhysicianCategoryController::class, 'update'])->name('update');
    });

    // Physician Master
    Route::prefix('physicians')->name('phys.')->group(function () {
        Route::get('/', [PhysicianController::class, 'index'])->name('index');
        Route::get('/create', [PhysicianController::class, 'create'])->name('create');
        Route::post('/', [PhysicianController::class, 'store'])->name('store');
        Route::get('/{id}', [PhysicianController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PhysicianController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PhysicianController::class, 'update'])->name('update');
    });

    // Appointment (composite key: participantid + appdate)
    Route::prefix('appointments')->name('appt.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/create', [AppointmentController::class, 'create'])->name('create');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/view/{participantid}/{appdate}', [AppointmentController::class, 'show'])->name('show');
        Route::get('/edit/{participantid}/{appdate}', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{participantid}/{appdate}', [AppointmentController::class, 'update'])->name('update');
        Route::post('/appointments/{participantid}/{appdate}/sms-driver', [AppointmentController::class, 'smsToDriver'])->name('smsDriver');
        Route::post('/{participantid}/{appdate}/email-driver', [AppointmentController::class, 'emailToDriver'])->name('emailDriver');
        // Calendar AJAX
        Route::get('/calendar/events', [AppointmentController::class, 'calendarEvents'])->name('calendar.events');
        Route::post('/calendar/move', [AppointmentController::class, 'calendarMove'])->name('calendar.move');
        Route::post('/calendar/status', [AppointmentController::class, 'calendarStatus'])->name('calendar.status');
        Route::get('/calendar/details', [AppointmentController::class, 'calendarDetails'])->name('calendar.details');
        Route::get('/calendar/table-data', [AppointmentController::class, 'tableData'])->name('table.data');
        Route::get('/calendar/summary-data', [AppointmentController::class, 'summaryData'])->name('summary.data');
    });

    Route::prefix('calendar/appointments')->name('cal.appt.')->group(function () {
        Route::post('/', [DashboardApptActionsController::class, 'store'])->name('store');   // create
        Route::put('/update', [DashboardApptActionsController::class, 'update'])->name('update'); // modify (or drag/drop)
        Route::put('/cancel', [DashboardApptActionsController::class, 'cancel'])->name('cancel'); // trans_flag='D'

    });

    // Claims & ERA Controller
    Route::middleware(['auth', 'perm:claims.menu'])->controller(ClaimController::class)->group(function () {

        Route::get('/claims/dashboard', 'ClaimsDashboard')->name('claims.dashboard');
        Route::get('/claims/create', 'create')->name('claims.create');
        Route::get('/claims/service-rate', 'getClaimServiceRate')->name('claims.service.rate');
        Route::get('/claims/member-lines/{participantId}', 'memberLines')->name('claims.memberLines');
        Route::get('/claims/member-summary/{id}', 'memberSummary')->name('claims.member.summary');
        Route::post('/claims', 'store')->name('claims.store');
        Route::get('/claims/drafts', 'draftIndex')->name('claims.draft.index');
        Route::get('/claims/{id}/edit', 'edit')->name('claims.edit');
        Route::post('/claims/{id}/update', 'update')->name('claims.update');
        Route::delete('/claims/{id}/delete', 'destroy')->name('claims.destroy');
        Route::post('/claims/generate', 'generateClaims')->name('claims.generate');
        Route::get('/claims/special', 'specialClaimIndex')->name('claims.special.index');
        Route::get('/claims/special/search', 'specialClaimSearch')->name('claims.special.search');
        Route::get('/claims/special/load/{id}', 'specialClaimLoad')->name('claims.special.load');
        Route::post('/claims/special/store', 'specialClaimStore')->name('claims.special.store');

        Route::get('/claims/validateclaims', 'validateClaims')->name('claims.check.form');
        Route::post('/claims/check', 'checkClaims')->name('claims.check');
        Route::get('/claims/{id}/cms1500-preview', 'previewCms1500')->name('claims.cms1500.preview');
        Route::get('/claims/{id}/cms1500-pdf', 'cms1500DomPdf')->name('claims.cms1500.dompdf');
        Route::post('/claims/send', 'sendClaims')->name('claims.send');
        Route::get('/claims/payments/{participantId}', 'memberPayments')->name('claims.memberPayments');
        Route::redirect('/claims/attendance-generate', '/claims/attendance-generate/A');
        Route::get('/claims/attendance-generate/{type?}', 'showAttendanceGenerateForm')
            ->whereIn('type', ['A', 'N'])
            ->name('claims.attendance.form');
        //Route::get('/claims/attendance-generate', 'showAttendanceGenerateForm')->name('claims.attendance.form');
        Route::post('/claims/generate-attendance/{type}', 'generateClaimsFromAttendance')->name('claims.attendance.generate');
        // Show "Generate 837P" screen
        Route::get('/claims/837p/{type}', 'show837Form')->name('claims.837.form');   // type = 'A' (Morning/Active) or 'N' (Noon/Inactive)
        // Download 837P file
        Route::get('/claims/837p/{type}/download', 'download837')->name('claims.837.download');
        Route::get('/claims/send/preview', 'sendPreview')->name('claims.send.preview');
        Route::post('/claims/send/generate', 'sendGenerate')->name('claims.send.generate');
        Route::post('/claims/send-preview/pdf', 'sendPreviewPdf')->name('claims.send.preview.pdf');
        Route::post('/claims/send/generate-with-pdf', 'sendGenerateWithPdf')->name('claims.send.generatePdf'); // NEW
        Route::post('/claims/send/generate-with-pdf_db', 'sendGenerateWithPdfDb')->name('claims.send.generatePdf.db'); // NEW
        Route::get('/claims/resubmit-batches', 'resubmitBatchIndex')->name('claims.resubmit.batches.index');
        Route::get('/claims/resubmit-batches/{batchId}', 'resubmitBatchShow')->name('claims.resubmit.batches.show');
        Route::post('/claims/resubmit-batches/{batchId}/selected', 'resubmitBatchSelected')->name('claims.resubmit.batches.selected');
        Route::post('/claims/resubmit-batches/{batchId}/all', 'resubmitBatchAll')->name('claims.resubmit.batches.all');
        Route::get('/claims/resubmission-ready','resubmissionReady')->name('claims.resubmission.ready');
        Route::post('/claims/resubmission-generate', 'generateResubmission837P')->name('claims.resubmission.generate');
        Route::get('/claims/resubmission-history', 'resubmissionHistory')->name('claims.resubmission.history');

        Route::get('/claims/batches/show/{batchId}', 'batchShow')->name('claims.batches.show');
        Route::get('/claims/batches/index', 'batchHistory')->name('claims.batches.index');
        Route::get('/claimmd/test-era-list',  'TestClaimMdEraList')->name('claimmd.test.era.list');
        Route::post('/claims/batches/{batchId}/claimmd-upload', 'uploadBatchToClaimMd')->name('claims.batches.claimmd.upload');
        Route::get('/claims/batches/{batchId}/pdf', 'batchOpenPdf')->name('claims.batches.pdf');
        Route::get('/claims/batches/{batchId}/edi', 'batchDownloadEdi')->name('claims.batches.edi');
        Route::get('/claims/batches/{batchId}/biller-report', 'billerBatchReport')->name('claims.biller.batch.report');

        // Void or Correction to claim
        // ------------------------------------------------------------
        // Corrected / Void Claims
        // ------------------------------------------------------------

        Route::get('/claims/corrections', 'claimCorrectionIndex')->name('claims.corrections.index');
        Route::get('/claims/corrections/{claimNo}/correct', 'claimCorrectionEdit')->name('claims.corrections.edit');
        Route::post('/claims/corrections/{claimNo}/correct', 'claimCorrectionPrepare')->name('claims.corrections.prepare');
        Route::get('/claims/corrections/{claimNo}/void', 'claimVoidEdit')->name('claims.void.edit');
        Route::post('/claims/corrections/{claimNo}/void', 'claimVoidPrepare')->name('claims.void.prepare');


        // Excel Import

        Route::get('/claims/excel-import', 'excelImportForm')->name('claims.excel.form');
        Route::post('/claims/excel-import/preview', 'excelImportPreview')->name('claims.excel.preview');
        Route::get('/claims/excel-import/preview/{token}', 'excelImportPreviewShow')->name('claims.excel.preview.show');
        Route::post('/claims/excel-import/fix-icd',  'excelImportFixIcd')->name('claims.excel.fix.icd');
        Route::post('/claims/excel-import/fix-pa', 'excelImportFixPa')->name('claims.excel.fix.pa');
        Route::post('/claims/excel-import/generate', 'excelImportGenerate')->name('claims.excel.generate');

        Route::get('/claims/medisoft-import', 'medisoftImportForm')->name('claims.medisoft.import.form');
        Route::post('/claims/medisoft-import/preview', 'medisoftImportPreview')->name('claims.medisoft.import.preview');
        Route::post('/claims/medisoft-import/process', 'medisoftImportProcess')->name('claims.medisoft.import.process');
        Route::get('/claims/medisoft-import/result', 'medisoftImportResult')->name('claims.medisoft.import.result');
        Route::get('/claims/medisoft-import/invalid/{token}', 'medisoftDownloadInvalid')->name('claims.medisoft.import.invalid');

        Route::get('/claims/pending-rebill', 'pendingRebillIndex')->name('claims.pending.rebill.index');
        Route::post('/claims/pending-rebill/{id}/reprocess', 'pendingRebillReprocess')->name('claims.pending.rebill.reprocess');
        Route::post('/claims/pending-rebill/reprocess-selected', 'pendingRebillReprocessSelected')->name('claims.pending.rebill.reprocess.selected');
        Route::post('/claims/pending-rebill/{id}/archive', 'pendingRebillArchive')->name('claims.pending.rebill.archive');
        Route::get('/claims/inquiry', 'claimInquiry')->name('claims.inquiry');

        // Pending Re-bill from export excel
        //
        //Route::get('/claims/pending-rebill', 'pendingRebillIndex')->name('claims.pending.rebill.index');
        //Route::post('/claims/pending-rebill/{id}/revalidate', 'pendingRebillRevalidate')->name('claims.pending.rebill.revalidate');

        // Re-submission of rejected claims
        Route::post('/claims/resubmission/generate-837', 'sendGenerateResubmission837')->name('claims.resubmission.generate837');
        Route::get('/claims/resubmission/preview', 'resubmissionPreview')->name('claims.resubmission.preview');

        //Bad 837P account number upload
        Route::get('/claims/legacy-837-import', 'legacy837ImportIndex')->name('claims.legacy837.import.index');
        Route::post('/claims/legacy-837-import', 'legacy837ImportProcess')->name('claims.legacy837.import.process');

        // Members statement Ledger
        Route::get('/claims/member-statement', 'memberStatement')->name('claims.member.statement');
        Route::get('/claims/member-statement/view', 'memberStatementView')->name('claims.member.statement.view');
        Route::get('/claims/member-statement/pdf', 'memberStatementPdf')->name('claims.member.statement.pdf');
        Route::get('/claims/member-statement/excel', 'memberStatementExcel')->name('claims.member.statement.excel');

    });

    // ERA related stuff

    Route::middleware(['auth', 'perm:claims.menu'])->controller(EraController::class)->group(function () {
        Route::get('/era/upload', 'showUploadForm')->name('era.upload.form');
        Route::post('/era/upload', 'processUpload')->name('era.upload.process');
        Route::get('/era', 'index')->name('era.index');         // upload + grid
        Route::post('/upload', 'processUploadNew')->name('era.upload');
        Route::get('/{id}/pdf', 'downloadPdf')->name('era.pdf');
        Route::get('/{id}/csv', 'downloadCsv')->name('era.csv');
        Route::post('/{id}/process', 'processEra')->name('era.process');
        Route::get('/era/claim/{id}', 'showClaim')->name('era.claim.show');
        Route::get('/era/file/{id}',  'showFile')->name('era.file.show');   // NEW

        // Medisoft
        Route::get('/era/medisoft-payments', 'medisoftPaymentImportForm')->name('era.medisoft.payments.form');
        Route::post('/era/medisoft-payments/preview', 'medisoftPaymentImportPreview')->name('era.medisoft.payments.preview');
        Route::post('/era/medisoft-payments/process', 'medisoftPaymentImportProcess')->name('era.medisoft.payments.process');
        Route::get('/era/medisoft-payments/invalid/{token}', 'medisoftPaymentDownloadInvalid')->name('era.medisoft.payments.invalid');
        Route::get('/era/medisoft-payments/result', 'medisoftPaymentImportResult')->name('era.medisoft.payments.result');

        // ---------------------------------------------------------
        // Claim.MD ERA
        // ---------------------------------------------------------
        Route::get('/era/claimmd', 'claimMdEraList')->name('era.claimmd.index');
        Route::post('/era/claimmd/{eraid}/process', 'claimMdEraProcess')->name('era.claimmd.process');

        Route::get('/era/exceptions', 'exceptions')->name('era.exceptions');
        Route::get('/era/exceptions/{id}',  'exceptionShow')->name('era.exception.show');
        Route::post('/era/exceptions/{id}/resolve', 'exceptionResolve')->name('era.exception.resolve');
        Route::post('/era/exceptions/{id}/legacy-recover', 'exceptionLegacyRecover')->name('era.exceptions.legacy.recover');
        Route::get('/era/legacy-payment-import', 'legacyPaymentImportIndex')->name('era.legacy.payment.import.index');
        Route::post('/era/legacy-payment-import', 'legacyPaymentImportProcess')->name('era.legacy.payment.import.process');
        Route::post('/era/legacy-payment/process-selected', 'legacyPaymentProcessSelected')->name('era.legacy.payment.process.selected');
        Route::post('/era/exceptions/{id}/cross-match', 'exceptionCrossMatch')->name('era.exception.cross-match');
        Route::post('/era/exceptions/{id}/cross-match-post', 'exceptionCrossMatchPost')->name('era.exception.cross-match.post');

        // ERA Denial Exception Handling
        Route::get('/era/denial-exceptions', 'denialExceptions')->name('era.denial.exceptions');
        Route::post('/era/denial-exceptions/{eraClaimId}/resolve', 'denialExceptionManualResolve')->name('era.denial.exceptions.resolve');
        Route::post('/era/denial-exceptions/{eraClaimId}/archive', 'denialExceptionArchive')->name('era.denial.exceptions.archive');
        Route::post('/era/denial-exceptions/{eraClaimId}/restore', 'denialExceptionRestore')->name('era.denial.exceptions.restore');
        Route::get('/era/denial-exceptions/{eraClaimId}/history', 'denialExceptionHistory')->name('era.denial.exceptions.history');
        Route::get('/era/denial-exceptions-view', 'denialExceptionsView')->name('era.denial.exceptions.view');
        Route::get('/era/denial-resolution', 'denialResolution')->name('era.denial.resolution');
        Route::post('/era/denial-resolution/resolve','resolveDeniedClaims')->name('era.denial.resolve');

    });

    // Claims Rejections
    Route::middleware(['auth', 'perm:claims.menu'])->controller(ClaimRejectionController::class)->group(function () {

        Route::get('/claims/rejections', 'index')->name('claims.rejections.index');
        Route::get('/claims/rejections/{id}', 'show')->name('claims.rejections.show');
        Route::get('/claims/rejections/{id}/edit', 'edit')->name('claims.rejections.edit');
        Route::put('/claims/rejections/{id}', 'update')->name('claims.rejections.update');
        Route::post('/claims/rejections/{id}/revalidate', 'revalidate')->name('claims.rejections.revalidate');
        Route::get('/claims/rejections-ready', 'ready')->name('claims.rejections.ready');
        Route::post('/claims/rejections/{id}/create-resubmission', 'createResubmission')->name('claims.rejections.createResubmission');
    });

    // Account Receivable for CLAIMS
    Route::middleware(['auth', 'perm:claims.menu'])->controller(ArController::class)->group(function () {
        Route::get('/ar/aging-summary', 'agingSummary')->name('ar.aging.summary');
        Route::get('/ar/aging-detail', 'agingDetail')->name('ar.aging.detail');
        Route::get('/ar/claim-transactions/{claimId}', 'transactionHistory')->name('ar.claim.transactions');
    });

    Route::middleware(['auth', 'perm:claims.menu'])->controller(BillingMemberController::class)->group(function () {

        Route::get('/billing-members', 'index')->name('billing.members.index');
        Route::get('/billing-members/create', 'create')->name('billing.members.create');
        Route::post('/billing-members/store', 'store')->name('billing.members.store');
        Route::get('/billing-members/edit/{id}', 'edit')->name('billing.members.edit');
        Route::post('/billing-members/update/{id}', 'update')->name('billing.members.update');
        Route::post('/billing-members/toggle-active/{id}', 'toggleActive')->name('billing.members.toggle');
    });

    Route::middleware(['auth', 'perm:claims.menu'])->controller(X12SettingsController::class)->prefix('settings/x12')->name('x12.settings.')->group(function () {

        Route::get('/', 'index')->name('index');
        //Clearinghouse CRUD
        Route::post('/clearinghouse', 'storeClearinghouse')->name('clearinghouse.store');
        Route::put('/clearinghouse/{id}', 'updateClearinghouse')->name('clearinghouse.update');
        Route::delete('/clearinghouse/{id}', 'destroyClearinghouse')->name('clearinghouse.destroy');
        //Provider CRUD
        Route::post('/provider', 'storeProvider')->name('provider.store');
        Route::put('/provider/{id}', 'updateProvider')->name('provider.update');
        Route::delete('/provider/{id}', 'destroyProvider')->name('provider.destroy');
        //Payer CRUD
        Route::post('/payer', 'storePayer')->name('payer.store');
        Route::put('/payer/{id}', 'updatePayer')->name('payer.update');
        Route::delete('/payer/{id}', 'destroyPayer')->name('payer.destroy');
    });


    // ICD10 routes
    Route::prefix('icd10')->middleware(['auth', 'perm:nursing.menu'])->controller(Icd10MasterController::class)->group(function () {
        Route::get('/', 'index')->name('icd10.index');
        Route::get('/data', 'data')->name('icd10.data');
    });
    Route::prefix('icd10')->middleware(['auth', 'perm:nursing.menu', 'perm:icd10.create'])->controller(Icd10MasterController::class)->group(function () {         // yajra JSON
        Route::get('/create', 'create')->name('icd10.create');
        Route::post('/store', 'store')->name('icd10.store');
    });
    Route::prefix('icd10')->middleware(['auth', 'perm:nursing.menu', 'perm:icd10.edit'])->controller(Icd10MasterController::class)->group(function () {
        Route::get('/{id}/edit', 'edit')->name('icd10.edit');
        Route::post('/{id}/update', 'update')->name('icd10.update');
    });


    // HMO Dropdown routes
    Route::prefix('HMO')->middleware(['auth', 'perm:members.menu'])->controller(HMODropdownController::class)->group(function () {
        Route::get('/', 'index')->name('hmo.index');
        Route::get('/data', 'data')->name('hmo.data');
        Route::get('/create', 'create')->name('hmo.create');
        Route::post('/store', 'store')->name('hmo.store');
        Route::get('/{id}/edit', 'edit')->name('hmo.edit');
        Route::post('/{id}/update', 'update')->name('hmo.update');
    });

    // Frequency Monitor Dropdown routes
    Route::prefix('Frequency')->middleware(['auth', 'perm:members.menu'])->controller(FrequencyMonitorDropdownController::class)->group(function () {
        Route::get('/', 'index')->name('frequency.index');
        Route::get('/data', 'data')->name('frequency.data');
        Route::get('/create', 'create')->name('frequency.create');
        Route::post('/store', 'store')->name('frequency.store');
        Route::get('/{id}/edit', 'edit')->name('frequency.edit');
        Route::post('/{id}/update', 'update')->name('frequency.update');
    });

    // Member Status Dropdown routes
    Route::prefix('Status')->middleware(['auth', 'perm:members.menu'])->controller(StatusDropdownController::class)->group(function () {
        Route::get('/', 'index')->name('member_status.index');
        Route::get('/data', 'data')->name('member_status.data');
        Route::get('/create', 'create')->name('member_status.create');
        Route::post('/store', 'store')->name('member_status.store');
        Route::get('/{id}/edit', 'edit')->name('member_status.edit');
        Route::post('/{id}/update', 'update')->name('member_status.update');
    });

    //Route for AccountMaster dropdown
    Route::middleware(['auth', 'perm:accounts.menu', 'perm:shift.menu',])->controller(LookupController::class)->group(function () {
        Route::get('/lookup/accountmaster', 'accountmaster')->name('lookup.accountmaster');
    });

    //Shift Account
    Route::middleware(['auth', 'perm:shift.menu'])->group(function () {

        Route::get('/shift/dashboard', [ShiftAccountController::class, 'ShiftDashboard'])->name('shift.dashboard');
        Route::get('/reports/shiftreport', [ShiftAccountController::class, 'IndexReport'])->name('reports.shift2.index');
        Route::post('/reports/shifreport', [ShiftAccountController::class, 'RunReport'])->name('reports.shift2.run');
        // later for PDF
        Route::post('/reports/shiftreport/export-pdf', [ShiftAccountController::class, 'RunPdfReport'])->name('reports.shift2.pdf');
        // DEPOSITS (DEPS)
        Route::prefix('shift/deposits')->name('shift.deposits.')->controller(ShiftAccountController::class)->group(function () {
            Route::get('/', 'depositIndex')->name('index');
            Route::get('/data', 'depositData')->name('data');         // yajra JSON
            Route::get('/create', 'depositCreate')->name('create');
            Route::post('/store', 'depositStore')->name('store');
            Route::get('/{trans_no}/edit', 'depositEdit')->name('edit');
            Route::post('/{trans_no}/update', 'depositUpdate')->name('update');
        });

        // PAYMENTS (PAYM)
        Route::prefix('shift/payments')->name('shift.payments.')->controller(ShiftAccountController::class)->group(function () {
            Route::get('/', 'paymentIndex')->name('index');
            Route::get('/data', 'paymentData')->name('data');         // yajra JSON
            Route::get('/create', 'paymentCreate')->name('create');
            Route::post('/store', 'paymentStore')->name('store');
            Route::get('/{trans_no}/edit', 'paymentEdit')->name('edit');
            Route::post('/{trans_no}/update', 'paymentUpdate')->name('update');
        });
    });

    // AI chat bot
    Route::middleware(['auth', 'perm:members.menu'])->prefix('chat')->controller(ChatController::class)->group(function () {
        Route::get('/', 'index')->name('chat.index');
        Route::get('/full', 'indexFull')->name('chat.full');
        Route::post('/ask', 'ask')->name('chat.ask');
        Route::get('/export/birthdays/excel', 'birthdaysExcel')->name('chat.export.birthdays.excel');
        Route::get('/export/birthdays/pdf', 'birthdaysPdf')->name('chat.export.birthdays.pdf');
        Route::get('/export/vitals/excel', 'vitalsExcel')->name('chat.export.vitals.excel');
        Route::get('/export/vitals/pdf', 'vitalsPdf')->name('chat.export.vitals.pdf');
    });

    Route::middleware(['auth'])->controller(RecommendationsController::class)->group(function () {
        Route::get('/chat/recommendations/pdf', 'viewPdf')->name('chat.recommendations.view');
        Route::get('/chat/recommendations/download', 'generatePdf')->name('chat.recommendations.download');
        Route::get('/chat/recommendations/faq/pdf', 'viewFaqPdf')->name('chat.recommendations.faq.view');
        Route::get('/chat/recommendations/faq/download', 'faqPdf')->name('chat.recommendations.faq.download');
        Route::get('/chat/recommendations/technical/pdf', 'viewTechnicalDocsPdf')->name('chat.recommendations.technical.view');
        Route::get('/chat/recommendations/technical/download', 'technicalDocsPdf')->name('chat.recommendations.technical.download');
    });

    Route::middleware(['auth',])->controller(TranslationController::class)->group(function () {
        // List + Search + Add form
        Route::get('/admin/translations', 'index')->name('translations.index');
        // Create new translation row
        Route::post('/admin/translations', 'store')->name('translations.store');
        // Update existing translation row (no delete)
        Route::post('/admin/translations/{id}', 'update')->name('translations.update');
    });

    // Claims and ERA for report purpose only.
    Route::middleware(['auth', 'perm:specialreport.menu'])->controller(ClaimsReportsController::class)->group(function () {

        Route::get('/reports/submitted-claims', 'index')->name('reports.submitted.claims.index');
        Route::get('/reports/submitted-claims/{batchId}', 'show')->name('reports.submitted.claims.show');
        Route::get('/reports/submitted-claims/{batchId}/pdf', 'pdf')->name('reports.submitted.claims.pdf');

    });

    // Claims and ERA for report purpose only.
    Route::middleware(['auth', 'perm:specialreport.menu'])->controller(EraReportsController::class)->group(function () {

        Route::get('/reports/processed-era', 'index')->name('reports.processed.era.index');
        Route::get('/reports/processed-era/{eraId}', 'show')->name('reports.processed.era.show');
        Route::get('/reports/processed-era/{eraId}/pdf', 'pdf')->name('reports.processed.era.pdf');

    });

    //Special Reports
    Route::middleware(['auth', 'perm:specialreport.menu'])->controller(SpecialReportController::class)->group(function () {

        Route::get('/specialreports/dashboard', 'SpecialReportDashboard')->name('specialreports.dashboard');
        Route::get('/specialreports/pending-rebill', 'pendingRebillReport')->name('specialreports.pending.rebill');
        Route::get('/specialreports/pending-rebill/pdf', 'pendingRebillReportPdf')->name('specialreports.pending.rebill.pdf');

    });

    Route::middleware(['auth', 'perm:specialreport.menu'])->controller(ERAController::class)->group(function () {

        Route::get('/era/denial-exceptions-view', 'denialExceptionsView')->name('era.denial.exceptions.view');
        Route::get('/era/denied-claims-report', 'deniedClaimsReport')->name('era.denied.claims.report');
        

    });

    /*
    Route::middleware(['auth', 'tenant',])->controller(BackupController::class)->group(function () {
        Route::get('/admin/backup', 'index')->name('backup.index');
        Route::post('/admin/backup/run','run')->name('backup.run');

    });
    */


    /// Testing

    Route::get('/infobip-test', function () {

        //$base = env('INFOBIP_BASE_URL');
        //$base = rtrim(config('services.infobip.base_url'), '/');

        //    dd(['base_url' => $base]); // just to confirm Laravel sees it

        $client = new Client([
            'base_uri' => rtrim(config('services.infobip.base_url'), '/') . '/',
            'timeout'  => 10,
        ]);

        $resp = $client->post('sms/2/text/advanced', [
            'headers' => [
                'Authorization' => 'App ' . config('services.infobip.api_key'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            'json' => [
                'messages' => [[
                    'from' => config('services.infobip.sender'),
                    'destinations' => [['to' => '+19089061085']], // ← your real test number
                    'text' => '✅ Infobip test message from Laravel',
                ]],
            ],
        ]);

        return 'Response: ' . $resp->getStatusCode() . ' ' . $resp->getBody();
    });

});
