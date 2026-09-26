<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Group;
use App\Models\Student;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(): View
    {
        $sampleStudent = Student::with('group')->first();
        $sampleGroup = Group::first();
        $sampleCertificate = Certificate::with(['student.group', 'program'])->first();

        return view('admin.templates.index', compact('sampleStudent', 'sampleGroup', 'sampleCertificate'));
    }

    public function show(string $type): View
    {
        $sampleStudent = Student::with('group')->first();
        $sampleGroup = Group::first();
        $sampleCertificate = Certificate::with(['student.group', 'program'])->first();

        return view('admin.templates.show', compact('type', 'sampleStudent', 'sampleGroup', 'sampleCertificate'));
    }
}
