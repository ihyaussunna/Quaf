<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Student;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IdCardController extends Controller
{
    public function index(Request $request): View
    {
        $groupId = $request->query('group');
        $category = $request->query('category');
        $search = $request->query('search');

        $query = Student::with('group');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(12)->withQueryString();
        $groups = Group::all();
        $zones = Student::ZONES;

        return view('admin.idcards.index', compact('students', 'groups', 'zones', 'groupId', 'category', 'search'));
    }

    public function print(Request $request): View
    {
        $groupId = $request->query('group');
        $students = Student::with('group')
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->orderBy('name')
            ->get();

        return view('admin.idcards.print', compact('students'));
    }

    public function chestSlips(Request $request): View
    {
        $groupId = $request->query('group');
        $category = $request->query('category');
        $students = Student::with(['group', 'entries.program'])
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('student_id')
            ->get();

        $groups = Group::all();
        $zones = Student::ZONES;

        return view('admin.idcards.chest-slips', compact('students', 'groups', 'zones', 'groupId', 'category'));
    }

    public function show(Student $student): View
    {
        $student->load('group');
        $qrCodeSvg = QrCodeService::svg(route('verify.student', $student->qr_token), 200);

        return view('admin.idcards.show', compact('student', 'qrCodeSvg'));
    }
}
