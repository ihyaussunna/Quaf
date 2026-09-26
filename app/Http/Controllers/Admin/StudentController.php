<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Student;
use App\Models\Zone;
use App\Services\AuditLogger;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $groupId = $request->query('group');
        $zoneId = $request->query('zone_id');
        $category = $request->query('category');
        $gender = $request->query('gender');
        $search = $request->query('search');

        $query = Student::with(['group', 'user', 'zone'])->withCount('entries');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        } elseif ($category) {
            $query->where(function ($q) use ($category) {
                $q->where('category', $category)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $category)->orWhere('code', $category));
            });
        }

        if ($gender && in_array($gender, ['male', 'female'])) {
            $query->where('gender', $gender);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(15)->withQueryString();
        $groups = Group::all();
        $zones = Zone::orderBy('display_order')->get();
        $categories = Student::ZONES;

        return view('admin.students.index', compact('students', 'groups', 'zones', 'categories', 'groupId', 'zoneId', 'category', 'gender', 'search'));
    }

    public function studentWise(Request $request): View
    {
        $groups = Group::orderBy('name')->get();
        $zones = Zone::orderBy('display_order')->get();
        $categories = Student::ZONES;

        $selectedGroupId = $request->query('group');
        $selectedZoneId = $request->query('zone_id');
        $selectedCategory = $request->query('category');
        $selectedStudentId = $request->query('student');
        $search = $request->query('search');

        $studentsQuery = Student::with(['group', 'zone']);
        if ($selectedGroupId) {
            $studentsQuery->where('group_id', $selectedGroupId);
        }
        if ($selectedZoneId) {
            $studentsQuery->where('zone_id', $selectedZoneId);
        } elseif ($selectedCategory) {
            $studentsQuery->where('category', $selectedCategory);
        }

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }
        $students = $studentsQuery->orderBy('name')->get();

        $selectedStudent = null;
        if ($selectedStudentId) {
            $selectedStudent = Student::with([
                'group',
                'zone',
                'entries.program.category',
                'entries.program.stage',
                'entries.program.zone',
            ])->find($selectedStudentId);
        }

        return view('admin.students.student-wise', compact(
            'groups',
            'zones',
            'categories',
            'students',
            'selectedGroupId',
            'selectedZoneId',
            'selectedCategory',
            'selectedStudentId',
            'selectedStudent',
            'search'
        ));
    }

    public function create(): View
    {
        $groups = Group::all();
        $zones = Zone::orderBy('display_order')->get();
        $categories = Student::ZONES;

        return view('admin.students.create', compact('groups', 'zones', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:50', 'unique:students,student_id'],
            'group_id' => ['required', 'exists:groups,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'category' => ['nullable', 'string'],
            'class_level' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'dob' => ['nullable', 'date'],
            'contact' => ['nullable', 'string'],
            'photo_url' => ['nullable', 'string'],
        ]);

        $validated['gender'] = $validated['gender'] ?? 'Male';

        if (! empty($validated['zone_id'])) {
            $zone = Zone::find($validated['zone_id']);
            $validated['category'] = $zone?->name;
        } elseif (! empty($validated['category'])) {
            $zone = Zone::where('name', $validated['category'])->first();
            $validated['zone_id'] = $zone?->id;
        } elseif (! empty($validated['class_level'])) {
            $zone = Zone::getZoneForClass($validated['class_level']);
            if ($zone) {
                $validated['zone_id'] = $zone->id;
                $validated['category'] = $zone->name;
            }
        }

        // Auto-generate student_id if not provided
        if (empty($validated['student_id'])) {
            $group = Group::find($validated['group_id']);
            $validated['student_id'] = Student::generateNextChestNumber($group);
        }
        $validated['qr_token'] = Str::random(40);

        $student = Student::create($validated);

        AuditLogger::log('create_student', $student, null, $student->toArray());

        return redirect()->route('admin.students.index')->with('success', "Student '{$student->name}' registered successfully ({$student->student_id}).");
    }

    public function nextChestNumber(Request $request): JsonResponse
    {
        $groupId = $request->query('group_id');
        $group = Group::find($groupId);
        $nextChest = Student::generateNextChestNumber($group);

        return response()->json(['next_chest_number' => $nextChest]);
    }

    public function show(Student $student): View
    {
        $student->load([
            'group',
            'zone',
            'user',
            'entries.program.category',
            'entries.program.stage',
            'entries.program.zone',
            'entries.scoreSheets',
            'certificates.program',
        ]);

        $qrCodeSvg = QrCodeService::svg(route('verify.student', $student->qr_token), 220);

        return view('admin.students.show', compact('student', 'qrCodeSvg'));
    }

    public function edit(Student $student): View
    {
        $groups = Group::all();
        $zones = Zone::orderBy('display_order')->get();
        $categories = Student::ZONES;

        return view('admin.students.edit', compact('student', 'groups', 'zones', 'categories'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:50', "unique:students,student_id,{$student->id}"],
            'group_id' => ['required', 'exists:groups,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'category' => ['nullable', 'string'],
            'class_level' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'photo_url' => ['nullable', 'string'],
        ]);

        if (empty($validated['gender'])) {
            unset($validated['gender']);
        }

        if (! empty($validated['zone_id'])) {
            $zone = Zone::find($validated['zone_id']);
            $validated['category'] = $zone?->name;
        } elseif (! empty($validated['category'])) {
            $zone = Zone::where('name', $validated['category'])->first();
            $validated['zone_id'] = $zone?->id;
        } elseif (! empty($validated['class_level'])) {
            $zone = Zone::getZoneForClass($validated['class_level']);
            if ($zone) {
                $validated['zone_id'] = $zone->id;
                $validated['category'] = $zone->name;
            }
        }

        $old = $student->toArray();
        $student->update($validated);

        AuditLogger::log('update_student', $student, $old, $student->toArray());

        return redirect()->route('admin.students.index')->with('success', "Student '{$student->name}' updated.");
    }

    public function destroy(Student $student): RedirectResponse
    {
        $old = $student->toArray();
        $name = $student->name;
        $student->delete();

        AuditLogger::log('delete_student', null, $old, null);

        return redirect()->route('admin.students.index')->with('success', "Student '{$name}' deleted.");
    }

    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quaf-students-'.date('Ymd-His').'.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student ID', 'Name', 'Group', 'Zone', 'Class', 'Gender', 'Contact', 'Points']);

            Student::with(['group', 'zone'])->orderBy('student_id')->chunk(100, function ($students) use ($handle) {
                foreach ($students as $s) {
                    fputcsv($handle, [
                        $s->student_id,
                        $s->name,
                        $s->group->name ?? '',
                        $s->zone?->name ?? $s->category,
                        $s->class_level,
                        $s->gender,
                        $s->contact,
                        $s->points_cache,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
