<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\CallListController as AdminCallListController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\CodeLetterController as AdminCodeLetterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\IdCardController as AdminIdCardController;
use App\Http\Controllers\Admin\JudgeController as AdminJudgeController;
use App\Http\Controllers\Admin\MarkEntryController as AdminMarkEntryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PanelAccessController as AdminPanelAccessController;
use App\Http\Controllers\Admin\PointController as AdminPointController;
use App\Http\Controllers\Admin\PrintReportController as AdminPrintReportController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\StageController as AdminStageController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Admin\TopScorerController as AdminTopScorerController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Admin\WebsiteBuilderController as AdminWebsiteBuilderController;
use App\Http\Controllers\Admin\ZoneController as AdminZoneController;
use App\Http\Controllers\Announcer\AnnouncerController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GreenRoom\GreenRoomController;
use App\Http\Controllers\Judge\JudgeController;
use App\Http\Controllers\Leader\LeaderController;
use App\Http\Controllers\Media\MediaController;
use App\Http\Controllers\Media\MediaResultController;
use App\Http\Controllers\ProgramCommittee\ProgramCommitteeController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\ResultController;
use App\Http\Controllers\Public\VerificationController;
use App\Http\Controllers\Public\VideoController;
use App\Http\Controllers\Student\StudentController;
use App\Models\Group;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Portal Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/results', [ResultController::class, 'index'])->name('results.index');
Route::get('/results/{program}', [ResultController::class, 'show'])->name('results.show');
Route::get('/results/{result}/poster', [MediaResultController::class, 'publicPoster'])->name('media.results.public-poster');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');

// Public Verifications & Live Displays
Route::get('/verify/certificate/{certificateNumber}', [VerificationController::class, 'verifyCertificate'])->name('verify.certificate');
Route::get('/verify/student/{qrToken}', [VerificationController::class, 'verifyStudent'])->name('verify.student');
Route::get('/stages/{stage}/projector', [AdminStageController::class, 'projector'])->name('stages.projector');

// One-time Secure Database Initializer for Hostinger Deployment
Route::get('/init-database/{token}', function (string $token) {
    if ($token !== 'quaf2026setup') {
        abort(403, 'Unauthorized setup token.');
    }

    try {
        @set_time_limit(300);
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = Artisan::output();

        Artisan::call('app:sync-official-programs');
        $programsOutput = Artisan::output();

        Artisan::call('db:seed', ['--class' => 'OfficialGroupStudentsSeeder', '--force' => true]);
        $studentsOutput = Artisan::output();

        Artisan::call('db:seed', ['--class' => 'PanelPasswordsSeeder', '--force' => true]);

        Artisan::call('optimize:clear');
        Cache::flush();

        $usersCount = User::count();
        $groupsCount = Group::count();
        $zonesCount = Zone::count();
        $programsCount = Program::count();
        $studentsCount = Student::count();

        return response('<html><head><title>QUAF 9.0 Sync</title></head><body style="font-family:sans-serif;padding:40px;background:#0d1117;color:#c9d1d9;">'
            .'<h1 style="color:#3fb950;margin-bottom:20px;">Festival Database Initialized & Synced Successfully!</h1>'
            .'<div style="background:#161b22;padding:24px;border-radius:8px;border:1px solid #30363d;max-width:600px;">'
            .'<p style="margin:8px 0;font-size:16px;"><strong>Programs:</strong> '.$programsCount.'</p>'
            .'<p style="margin:8px 0;font-size:16px;"><strong>Groups:</strong> '.$groupsCount.'</p>'
            .'<p style="margin:8px 0;font-size:16px;"><strong>Students:</strong> '.$studentsCount.'</p>'
            .'<p style="margin:8px 0;font-size:16px;"><strong>Zones:</strong> '.$zonesCount.'</p>'
            .'<p style="margin:8px 0;font-size:16px;"><strong>Users:</strong> '.$usersCount.'</p>'
            .'<div style="margin-top:24px;">'
            .'<a href="/admin" style="display:inline-block;padding:10px 20px;background:#238636;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Go to Admin Dashboard &rarr;</a>'
            .'</div></div>'
            .'</body></html>', 200, ['Content-Type' => 'text/html']);
    } catch (Throwable $e) {
        return response('<html><head><title>QUAF 9.0 Sync Error</title></head><body style="font-family:sans-serif;padding:40px;background:#0d1117;color:#f85149;">'
            .'<h1>Sync Error</h1>'
            .'<pre style="background:#161b22;padding:20px;border-radius:6px;border:1px solid #da3633;color:#ff7b72;">'.htmlspecialchars($e->getMessage()).'</pre>'
            .'</body></html>', 500, ['Content-Type' => 'text/html']);
    }
});

// Run pending migrations safely via browser URL
Route::get('/migrate-db', function (Request $request) {
    $hasAccess = (auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin', 'program_committee', 'program_coordinator']))
        || $request->query('token') === 'quaf2026setup';

    if (! $hasAccess) {
        abort(403, 'Unauthorized. Please login or pass ?token=quaf2026setup');
    }

    try {
        @set_time_limit(300);
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        Artisan::call('optimize:clear');

        return response('<html><head><title>QUAF 9.0 Database Migration</title></head><body style="font-family:sans-serif;padding:40px;background:#0d1117;color:#c9d1d9;">'
            .'<h1 style="color:#3fb950;margin-bottom:20px;">Database Migrations Executed Successfully!</h1>'
            .'<div style="background:#161b22;padding:24px;border-radius:8px;border:1px solid #30363d;max-width:700px;">'
            .'<pre style="margin:0;font-size:14px;color:#58a6ff;white-space:pre-wrap;">'.htmlspecialchars($output ?: 'Database is up to date. No pending migrations.').'</pre>'
            .'</div>'
            .'<div style="margin-top:24px;">'
            .'<a href="javascript:history.back()" style="display:inline-block;padding:10px 20px;background:#238636;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">&larr; Return to Previous Page</a>'
            .'</div></body></html>', 200, ['Content-Type' => 'text/html']);
    } catch (Throwable $e) {
        return response('<html><head><title>Migration Error</title></head><body style="font-family:sans-serif;padding:40px;background:#0d1117;color:#c9d1d9;">'
            .'<h1 style="color:#f85149;">Migration Error</h1>'
            .'<pre style="background:#161b22;padding:20px;border-radius:6px;border:1px solid #da3633;color:#ff7b72;">'.htmlspecialchars($e->getMessage()).'</pre>'
            .'</body></html>', 500, ['Content-Type' => 'text/html']);
    }
});

Route::get('/clear-cache/{token}', function (string $token) {
    if ($token !== 'quaf2026setup') {
        abort(403, 'Unauthorized setup token.');
    }

    Cache::flush();
    Artisan::call('view:clear');

    return response()->json([
        'status' => 'success',
        'message' => 'Cache and compiled views cleared successfully!',
    ]);
});

Route::get('/git-pull/{token}', function (string $token) {
    if ($token !== 'quaf2026setup') {
        abort(403, 'Unauthorized setup token.');
    }

    $output = '';
    if (function_exists('shell_exec')) {
        $output = (string) shell_exec('git pull origin main 2>&1');
    } else {
        $output = 'shell_exec is disabled on this server.';
    }

    try {
        @set_time_limit(300);
        Artisan::call('migrate', ['--force' => true]);
        $output .= "\n\n--- Migration Output ---\n".(Artisan::output() ?: 'Database up to date.');
        Artisan::call('optimize:clear');
        Cache::flush();
        $output .= "\n\nCache cleared successfully.";
    } catch (Throwable $e) {
        $output .= "\n\nMigration Error: ".$e->getMessage();
    }

    return response('<html><body style="font-family:sans-serif;padding:30px;background:#0d1117;color:#c9d1d9;">'
        .'<h2 style="color:#3fb950;">Git Pull & Migration Output</h2>'
        .'<pre style="background:#161b22;padding:16px;border-radius:6px;border:1px solid #30363d;white-space:pre-wrap;color:#58a6ff;">'
        .htmlspecialchars($output).'</pre>'
        .'<p style="margin-top:20px;"><a href="/judge" style="display:inline-block;padding:10px 18px;background:#238636;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Go to Judge Panel &rarr;</a></p>'
        .'</body></html>');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Judge 4-Digit Unique PIN Login
Route::get('/judge/login', [JudgeController::class, 'showPinLogin'])->name('judge.login');
Route::post('/judge/login', [JudgeController::class, 'pinLogin'])->name('judge.login.submit');

// Student Chest Number Quick Login
Route::get('/student/login', [StudentController::class, 'showLogin'])->name('student.login');
Route::post('/student/login', [StudentController::class, 'login'])->name('student.login.submit');

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Fest Management)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::match(['get', 'post'], '/system/sync-festival-data', [AdminDashboardController::class, 'syncFestivalData'])->name('system.sync-festival-data');

    // Admin Profile
    Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [AdminProfileController::class, 'update'])->name('profile.update');

    // Groups / Teams
    Route::resource('groups', AdminGroupController::class);

    // Zones
    Route::get('zones', [AdminZoneController::class, 'index'])->name('zones.index');

    // Students & 360° Profile
    Route::get('students/export', [AdminStudentController::class, 'export'])->name('students.export');
    Route::get('students/bulk', [AdminStudentController::class, 'bulkCreate'])->name('students.bulk');
    Route::post('students/bulk', [AdminStudentController::class, 'bulkStore'])->name('students.bulk-store');
    Route::get('students/bulk-template', [AdminStudentController::class, 'downloadTemplate'])->name('students.bulk-template');
    Route::get('students/next-chest-number', [AdminStudentController::class, 'nextChestNumber'])->name('students.next-chest-number');
    Route::get('students-wise', [AdminStudentController::class, 'studentWise'])->name('students.student-wise');
    Route::resource('students', AdminStudentController::class);

    // Programs & Categories
    Route::get('programs-wise', [AdminProgramController::class, 'programWise'])->name('programs.program-wise');
    Route::post('programs/{program}/criteria', [AdminProgramController::class, 'updateCriteria'])->name('programs.criteria.update');
    Route::resource('programs', AdminProgramController::class);

    // Code Letters Handler
    Route::get('code-letters', [AdminCodeLetterController::class, 'index'])->name('code-letters.index');
    Route::post('code-letters/{program}/save', [AdminCodeLetterController::class, 'save'])->name('code-letters.save');
    Route::post('code-letters/{program}/auto-assign', [AdminCodeLetterController::class, 'autoAssign'])->name('code-letters.auto-assign');

    // Printable Forms
    Route::get('forms/call-list', [AdminFormController::class, 'callList'])->name('forms.call-list');
    Route::get('forms/evaluation', [AdminFormController::class, 'evaluation'])->name('forms.evaluation');

    // Stages & Live Status
    Route::post('stages/{stage}/live-status', [AdminStageController::class, 'updateLiveStatus'])->name('stages.live-status');
    Route::resource('stages', AdminStageController::class);

    // Program Registrations / Entries
    Route::post('registrations/verify-all', [AdminRegistrationController::class, 'verifyAllPending'])->name('registrations.verify-all');
    Route::post('registrations/{entry}/verify', [AdminRegistrationController::class, 'verify'])->name('registrations.verify');
    Route::post('registrations/{entry}/reject', [AdminRegistrationController::class, 'reject'])->name('registrations.reject');
    Route::resource('registrations', AdminRegistrationController::class)->parameters([
        'registrations' => 'entry',
    ]);

    // Call List & Attendance Center
    Route::get('call-list', [AdminCallListController::class, 'index'])->name('call-list.index');
    Route::post('call-list/{entry}/attendance', [AdminCallListController::class, 'markAttendance'])->name('call-list.attendance');

    // Evaluation Monitor Dashboard
    Route::get('evaluation-monitor', [AdminCallListController::class, 'evaluationMonitor'])->name('evaluation-monitor.index');
    Route::get('evaluation-monitor/{program}', [AdminCallListController::class, 'evaluationDetails'])->name('evaluation-monitor.show');

    // Dedicated Judge Marks Dashboard
    Route::get('judge-marks', [AdminCallListController::class, 'judgeMarks'])->name('judge-marks.index');

    // Schedule Management
    Route::resource('schedules', AdminScheduleController::class);

    // Judges Management
    Route::resource('judges', AdminJudgeController::class);
    Route::post('judges/{judge}/regenerate-pin', [AdminJudgeController::class, 'regeneratePin'])->name('judges.regenerate-pin');
    Route::post('judges/{judge}/update-password', [AdminJudgeController::class, 'updatePassword'])->name('judges.update-password');

    // Result Management & Approval Workflow
    Route::get('results/declare', [AdminResultController::class, 'declareIndex'])->name('results.declare');
    Route::get('results/declared', [AdminResultController::class, 'declaredResults'])->name('results.declared');
    Route::delete('results/{result}/undeclare', [AdminResultController::class, 'undeclare'])->name('results.undeclare');
    Route::get('results/specified', [AdminResultController::class, 'specified'])->name('results.specified');
    Route::get('results/all', [AdminResultController::class, 'allResults'])->name('results.all');
    Route::post('results/{result}/publish', [AdminResultController::class, 'publish'])->name('results.publish');
    Route::post('results/{result}/send-to-announcer', [AdminResultController::class, 'sendToAnnouncer'])->name('results.send-to-announcer');
    Route::resource('results', AdminResultController::class);

    // Points & Rankings
    Route::get('points', [AdminPointController::class, 'index'])->name('points.index');
    Route::post('points', [AdminPointController::class, 'update'])->name('points.update');
    Route::post('points/recalculate', [AdminPointController::class, 'recalculate'])->name('points.recalculate');

    // Media & Editorial
    Route::resource('news', AdminNewsController::class);
    Route::resource('gallery', AdminGalleryController::class);
    Route::resource('videos', AdminVideoController::class);
    Route::resource('announcements', AdminAnnouncementController::class);

    // Certificates & ID Badges
    Route::get('certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}', [AdminCertificateController::class, 'show'])->name('certificates.show');
    Route::get('idcards', [AdminIdCardController::class, 'index'])->name('idcards.index');
    Route::get('idcards-print', [AdminIdCardController::class, 'print'])->name('idcards.print');
    Route::get('idcards-chest-slips', [AdminIdCardController::class, 'chestSlips'])->name('idcards.chest-slips');
    Route::get('idcards/{student}', [AdminIdCardController::class, 'show'])->name('idcards.show');

    // Mark Entry, View Marks, Marks Handler & Check
    Route::get('mark-entry/view-marks', [AdminMarkEntryController::class, 'viewMarks'])->name('mark-entry.view-marks');
    Route::get('mark-entry/handler', [AdminMarkEntryController::class, 'marksHandler'])->name('mark-entry.handler');
    Route::post('mark-entry/{program}/status-transition', [AdminMarkEntryController::class, 'updateHandlerStatus'])->name('mark-entry.status-transition');
    Route::get('mark-entry/check', [AdminMarkEntryController::class, 'markCheck'])->name('mark-entry.check');
    Route::get('mark-entry', [AdminMarkEntryController::class, 'index'])->name('mark-entry.index');
    Route::get('mark-entry/{program}', [AdminMarkEntryController::class, 'show'])->name('mark-entry.show');
    Route::post('mark-entry/{program}/save', [AdminMarkEntryController::class, 'saveMarks'])->name('mark-entry.save');
    Route::post('mark-entry/{program}/publish', [AdminMarkEntryController::class, 'publish'])->name('mark-entry.publish');

    // Templates (Certificates & ID Badges)
    Route::get('templates', [AdminTemplateController::class, 'index'])->name('templates.index');
    Route::get('templates/{type}', [AdminTemplateController::class, 'show'])->name('templates.show');

    // Data Exports (Participants, Judges, Competitions, Results, Teams)
    Route::get('exports', [AdminExportController::class, 'index'])->name('exports.index');
    Route::get('exports/{type}', [AdminExportController::class, 'export'])->name('exports.download');

    // Selective Customizable Print & PDF Reports
    Route::get('print/results', [AdminPrintReportController::class, 'results'])->name('print.results');
    Route::get('print/students', [AdminPrintReportController::class, 'students'])->name('print.students');
    Route::get('print/programs', [AdminPrintReportController::class, 'programs'])->name('print.programs');

    // Achievements & Top Scorers
    Route::get('achievements/team-score', [AdminTopScorerController::class, 'teamScore'])->name('achievements.team-score');
    Route::get('achievements/zone-score', [AdminTopScorerController::class, 'zoneScore'])->name('achievements.zone-score');
    Route::get('achievements/stage-score', [AdminTopScorerController::class, 'stageScore'])->name('achievements.stage-score');
    Route::get('achievements/all-students', [AdminTopScorerController::class, 'allStudentScore'])->name('achievements.all-students');
    Route::get('top-scorers', [AdminTopScorerController::class, 'index'])->name('top-scorers.index');

    // Website Builder (Dynamic Landing Page Settings)
    Route::get('website-builder', [AdminWebsiteBuilderController::class, 'index'])->name('website-builder.index');
    Route::post('website-builder', [AdminWebsiteBuilderController::class, 'update'])->name('website-builder.update');

    // Settings & Audit Logs + Settings Drawer Endpoints
    Route::post('settings/mark-settings', [AdminSettingController::class, 'markSettings'])->name('settings.mark-settings');
    Route::post('settings/limit-settings', [AdminSettingController::class, 'limitSettings'])->name('settings.limit-settings');
    Route::post('settings/broadcast', [AdminSettingController::class, 'broadcastMessage'])->name('settings.broadcast');
    Route::post('settings/deadline', [AdminSettingController::class, 'deadlineSettings'])->name('settings.deadline');
    Route::post('settings/score-display', [AdminSettingController::class, 'scoreDisplaySettings'])->name('settings.score-display');
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::post('settings/toggle-registration', [AdminSettingController::class, 'toggleRegistration'])->name('settings.toggle-registration');
    Route::post('settings/toggle-student-editing', [AdminSettingController::class, 'toggleStudentEditing'])->name('settings.toggle-student-editing');
    Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

    // Panel Access & Credentials Hub
    Route::get('panel-access', [AdminPanelAccessController::class, 'index'])->name('panel-access.index');
    Route::post('panel-access/{user}/toggle-status', [AdminPanelAccessController::class, 'toggleStatus'])->name('panel-access.toggle-status');
    Route::post('panel-access/{user}/update-password', [AdminPanelAccessController::class, 'updatePassword'])->name('panel-access.update-password');
});

/*
|--------------------------------------------------------------------------
| Judges Panel Routes
|--------------------------------------------------------------------------
*/
Route::prefix('judge')->name('judge.')->middleware(['auth', 'role:judge'])->group(function () {
    Route::get('/', [JudgeController::class, 'dashboard'])->name('dashboard');
    Route::get('/evaluate/{program}', [JudgeController::class, 'showProgram'])->name('evaluate');
    Route::post('/evaluate/{program}/{entry}', [JudgeController::class, 'saveScore'])->name('evaluate.save');
});

/*
|--------------------------------------------------------------------------
| Green Room Panel Routes
|--------------------------------------------------------------------------
*/
Route::prefix('greenroom')->name('greenroom.')->middleware(['auth', 'role:green_room_coordinator,admin,super_admin'])->group(function () {
    Route::get('/', [GreenRoomController::class, 'index'])->name('index');
    Route::get('/call-list', [GreenRoomController::class, 'callList'])->name('call-list');
    Route::post('/call-list/{program}/toggle-lock', [GreenRoomController::class, 'toggleLockCallList'])->name('toggle-lock');
    Route::get('/code-letters', [GreenRoomController::class, 'codeLetters'])->name('code-letters');
    Route::post('/status/{call}', [GreenRoomController::class, 'updateStatus'])->name('update-status');
    Route::post('/call-next/{program}', [GreenRoomController::class, 'callNext'])->name('call-next');
    Route::post('/attendance/{entry}', [GreenRoomController::class, 'markAttendance'])->name('mark-attendance');
    Route::post('/generate-codes/{program}', [GreenRoomController::class, 'generateCodeLetters'])->name('generate-codes');
});

/*
|--------------------------------------------------------------------------
| Announcer Panel Routes
|--------------------------------------------------------------------------
*/
Route::prefix('announcer')->name('announcer.')->middleware(['auth', 'role:admin,super_admin,announcer'])->group(function () {
    Route::get('/', [AnnouncerController::class, 'index'])->name('index');
    Route::get('/stage', [AnnouncerController::class, 'stageCalling'])->name('stage');
    Route::post('/call-stage/{call}', [AnnouncerController::class, 'callToStage'])->name('call-stage');
    Route::post('/enter-stage/{call}', [AnnouncerController::class, 'enterStage'])->name('enter-stage');
    Route::post('/complete-stage/{call}', [AnnouncerController::class, 'completeStage'])->name('complete-stage');
    Route::post('/{result}/announced', [AnnouncerController::class, 'markAnnounced'])->name('announced');
});

/*
|--------------------------------------------------------------------------
| Leaders Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::prefix('leader')->name('leader.')->middleware(['auth', 'role:group_leader'])->group(function () {
    Route::get('/', [LeaderController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [LeaderController::class, 'students'])->name('students');
    Route::put('/students/{student}', [LeaderController::class, 'updateStudent'])->name('students.update');
    Route::get('/programs', [LeaderController::class, 'programs'])->name('programs');
    Route::get('/programs-wise', [LeaderController::class, 'programWise'])->name('programs-wise');
    Route::get('/students-wise', [LeaderController::class, 'studentWise'])->name('students-wise');
    Route::get('/registrations', [LeaderController::class, 'registrations'])->name('registrations');
    Route::post('/registrations', [LeaderController::class, 'storeRegistration'])->name('registrations.store');
    Route::post('/registrations/group', [LeaderController::class, 'storeGroupRegistration'])->name('registrations.group.store');
    Route::get('/registrations/{entry}/edit', [LeaderController::class, 'editRegistration'])->name('registrations.edit');
    Route::put('/registrations/{entry}', [LeaderController::class, 'updateRegistration'])->name('registrations.update');
    Route::delete('/registrations/{entry}', [LeaderController::class, 'destroyRegistration'])->name('registrations.destroy');
    Route::delete('/registrations/by-program/{program}', [LeaderController::class, 'destroyByProgram'])->name('registrations.destroy-by-program');
});

/*
|--------------------------------------------------------------------------
| Students Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/id-card', [StudentController::class, 'idCard'])->name('idcard');
    Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates');
});

/*
|--------------------------------------------------------------------------
| Program Samithi (Program Committee) Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('program-committee')->name('program-committee.')->middleware(['auth', 'role:program_committee,program_coordinator,admin,super_admin'])->group(function () {
    Route::get('/', [ProgramCommitteeController::class, 'dashboard'])->name('dashboard');
    Route::get('/programs', [ProgramCommitteeController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [ProgramCommitteeController::class, 'create'])->name('programs.create');
    Route::post('/programs', [ProgramCommitteeController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}', [ProgramCommitteeController::class, 'show'])->name('programs.show');
    Route::get('/programs/{program}/edit', [ProgramCommitteeController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/{program}', [ProgramCommitteeController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [ProgramCommitteeController::class, 'destroy'])->name('programs.destroy');

    // Dedicated Niyamavali Management
    Route::get('/niyamavali', [ProgramCommitteeController::class, 'niyamavaliIndex'])->name('niyamavali.index');
    Route::get('/programs/{program}/rules', [ProgramCommitteeController::class, 'editRules'])->name('programs.rules');
    Route::put('/programs/{program}/rules', [ProgramCommitteeController::class, 'updateRules'])->name('programs.rules.update');
    Route::get('/programs/{program}/rules/print', [ProgramCommitteeController::class, 'printRules'])->name('programs.rules.print');
    Route::get('/niyamavali/print-book', [ProgramCommitteeController::class, 'printAllRules'])->name('niyamavali.print-book');
});

/*
|--------------------------------------------------------------------------
| Media Team Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('media')->name('media.')->middleware(['auth', 'role:media_team,media_manager,admin,super_admin'])->group(function () {
    Route::get('/', [MediaController::class, 'dashboard'])->name('dashboard');

    // News Articles
    Route::get('/news', [MediaController::class, 'newsIndex'])->name('news.index');
    Route::get('/news/create', [MediaController::class, 'newsCreate'])->name('news.create');
    Route::post('/news', [MediaController::class, 'newsStore'])->name('news.store');
    Route::get('/news/{news}/edit', [MediaController::class, 'newsEdit'])->name('news.edit');
    Route::put('/news/{news}', [MediaController::class, 'newsUpdate'])->name('news.update');
    Route::delete('/news/{news}', [MediaController::class, 'newsDestroy'])->name('news.destroy');
    Route::post('/news/{news}/toggle-featured', [MediaController::class, 'newsToggleFeatured'])->name('news.toggle-featured');

    // Gallery Photos
    Route::get('/gallery', [MediaController::class, 'galleryIndex'])->name('gallery.index');
    Route::get('/gallery/create', [MediaController::class, 'galleryCreate'])->name('gallery.create');
    Route::post('/gallery', [MediaController::class, 'galleryStore'])->name('gallery.store');
    Route::get('/gallery/{gallery}/edit', [MediaController::class, 'galleryEdit'])->name('gallery.edit');
    Route::put('/gallery/{gallery}', [MediaController::class, 'galleryUpdate'])->name('gallery.update');
    Route::delete('/gallery/{gallery}', [MediaController::class, 'galleryDestroy'])->name('gallery.destroy');

    // Videos & Live
    Route::get('/videos', [MediaController::class, 'videosIndex'])->name('videos.index');
    Route::get('/videos/create', [MediaController::class, 'videosCreate'])->name('videos.create');
    Route::post('/videos', [MediaController::class, 'videosStore'])->name('videos.store');
    Route::get('/videos/{video}/edit', [MediaController::class, 'videosEdit'])->name('videos.edit');
    Route::put('/videos/{video}', [MediaController::class, 'videosUpdate'])->name('videos.update');
    Route::delete('/videos/{video}', [MediaController::class, 'videosDestroy'])->name('videos.destroy');
    Route::post('/videos/{video}/toggle-live', [MediaController::class, 'videosToggleLive'])->name('videos.toggle-live');

    // Results & Poster Designer
    Route::get('/results', [MediaResultController::class, 'index'])->name('results.index');
    Route::get('/results/{result}/studio', [MediaResultController::class, 'studio'])->name('results.studio');
    Route::post('/results/{result}/save-poster', [MediaResultController::class, 'savePoster'])->name('results.save-poster');
    Route::post('/results/{result}/publish', [MediaResultController::class, 'publishPublic'])->name('results.publish');
    Route::post('/results/save-default-settings', [MediaResultController::class, 'saveDefaultSettings'])->name('results.save-default-settings');
    Route::get('/results/templates', [MediaResultController::class, 'templatesIndex'])->name('results.templates');
    Route::post('/results/templates', [MediaResultController::class, 'templateStore'])->name('results.templates.store');
    Route::post('/results/templates/{template}/toggle', [MediaResultController::class, 'templateToggleActive'])->name('results.templates.toggle');
    Route::delete('/results/templates/{template}', [MediaResultController::class, 'templateDestroy'])->name('results.templates.destroy');
    Route::get('/results/templates/{template}/customize', [MediaResultController::class, 'templateCustomize'])->name('results.templates.customize');
    Route::post('/results/templates/{template}/customize', [MediaResultController::class, 'templateSaveCustomization'])->name('results.templates.save-customization');
});
