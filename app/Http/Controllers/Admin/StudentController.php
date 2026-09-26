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
use Illuminate\Support\Facades\DB;
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

    public function bulkCreate(): View
    {
        $groups = Group::orderBy('name')->get();
        $zones = Zone::orderBy('display_order')->get();
        $categories = Student::ZONES;

        return view('admin.students.bulk', compact('groups', 'zones', 'categories'));
    }

    public function downloadTemplate(): StreamedResponse
    {
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Name', 'Group', 'Class / Zone', 'Contact', 'Chest Number (Optional)']);
            fputcsv($handle, ['Muhammed Faris', 'PACTO', 'Class 4', '9847000001', '']);
            fputcsv($handle, ['Ahmad Bilal', 'LUMO', 'Class 3', '9847000002', '']);
            fputcsv($handle, ['Zaid Ameen', 'CONCO', 'C Zone', '9847000003', '']);
            fputcsv($handle, ['Umar Farooq', 'UNIO', 'Class 1', '9847000004', '']);
            fputcsv($handle, ['Hassan Ali', 'YUGO', 'Mix Zone', '9847000005', '']);
            fclose($handle);
        };

        return response()->streamDownload($callback, 'students_bulk_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'default_group_id' => ['nullable', 'exists:groups,id'],
            'default_category' => ['nullable', 'string'],
            'paste_text' => ['nullable', 'string'],
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt'],
        ]);

        $defaultGroup = $request->input('default_group_id') ? Group::find($request->input('default_group_id')) : null;
        $defaultCategory = $request->input('default_category');
        $defaultZone = $defaultCategory ? Zone::where('name', $defaultCategory)->first() : null;

        $rows = [];

        // 1. Process CSV File if uploaded
        if ($request->hasFile('csv_file') && $request->file('csv_file')->isValid()) {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                $headerChecked = false;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $nonEmpty = array_filter($data, fn ($v) => trim((string) $v) !== '');
                    if (empty($nonEmpty)) {
                        continue;
                    }

                    if (! $headerChecked) {
                        $headerChecked = true;
                        $firstCol = strtolower(trim((string) ($data[0] ?? '')));
                        if (in_array($firstCol, ['name', 'student name', 'participant name', 'full name', 'പേര്'])) {
                            continue;
                        }
                    }

                    $rows[] = array_map('trim', $data);
                }
                fclose($handle);
            }
        }

        // 2. Process Paste Text
        $pasteText = trim((string) $request->input('paste_text', ''));
        if (! empty($pasteText)) {
            $lines = preg_split('/\r\n|\r|\n/', $pasteText);
            $headerChecked = false;

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $delimiter = ',';
                if (str_contains($line, '|')) {
                    $delimiter = '|';
                } elseif (str_contains($line, "\t")) {
                    $delimiter = "\t";
                }

                $cols = array_map('trim', explode($delimiter, $line));

                if (! $headerChecked) {
                    $headerChecked = true;
                    $firstCol = strtolower($cols[0] ?? '');
                    if (in_array($firstCol, ['name', 'student name', 'participant name', 'full name', 'പേര്'])) {
                        continue;
                    }
                }

                $rows[] = $cols;
            }
        }

        if (empty($rows)) {
            return back()->withInput()->with('error', 'No student entries found to import. Please paste student details or upload a CSV file.');
        }

        $allGroups = Group::all();
        $allZones = Zone::all();

        $groupCounters = [];
        $existingStudentIds = Student::pluck('student_id')->toArray();
        $usedStudentIds = array_flip($existingStudentIds);

        $createdStudents = [];
        $failedRows = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $cols) {
                $name = $cols[0] ?? '';
                if (empty($name)) {
                    $failedRows[] = 'Row #'.($rowIndex + 1).': Participant Name is empty.';

                    continue;
                }

                // Group
                $groupInput = $cols[1] ?? '';
                $group = null;
                if (! empty($groupInput)) {
                    $group = $allGroups->first(function ($g) use ($groupInput) {
                        return (string) $g->id === (string) $groupInput
                            || strcasecmp($g->code, $groupInput) === 0
                            || strcasecmp($g->name, $groupInput) === 0
                            || stripos($g->name, $groupInput) !== false;
                    });
                }
                if (! $group) {
                    $group = $defaultGroup;
                }

                if (! $group) {
                    $failedRows[] = 'Row #'.($rowIndex + 1)." ({$name}): Group not found or not specified.";

                    continue;
                }

                // Class / Zone
                $classOrZone = $cols[2] ?? '';
                $zoneId = null;
                $category = null;
                $classLevel = null;

                if (! empty($classOrZone)) {
                    $matchedZone = $allZones->first(function ($z) use ($classOrZone) {
                        return strcasecmp($z->name, $classOrZone) === 0
                            || strcasecmp($z->code, $classOrZone) === 0;
                    });

                    if ($matchedZone) {
                        $zoneId = $matchedZone->id;
                        $category = $matchedZone->name;
                    } else {
                        $detectedZoneName = Zone::determineZoneNameFromClass($classOrZone);
                        $classLevel = $classOrZone;
                        if ($detectedZoneName) {
                            $category = $detectedZoneName;
                            $zoneId = $allZones->firstWhere('name', $detectedZoneName)?->id;
                        }
                    }
                }

                if (! $category) {
                    if ($defaultZone) {
                        $zoneId = $defaultZone->id;
                        $category = $defaultZone->name;
                    } elseif ($defaultCategory) {
                        $category = $defaultCategory;
                        $zoneId = $allZones->firstWhere('name', $defaultCategory)?->id;
                    } else {
                        $category = 'A Zone';
                        $zoneId = $allZones->firstWhere('name', 'A Zone')?->id;
                    }
                }

                // Contact
                $contact = $cols[3] ?? null;

                // Chest number / Student ID
                $customChest = $cols[4] ?? null;
                if (! empty($customChest)) {
                    if (isset($usedStudentIds[$customChest])) {
                        $failedRows[] = 'Row #'.($rowIndex + 1)." ({$name}): Chest number '{$customChest}' is already assigned.";

                        continue;
                    }
                    $studentId = $customChest;
                    $usedStudentIds[$studentId] = true;
                } else {
                    if (! isset($groupCounters[$group->id])) {
                        $initialNext = Student::generateNextChestNumber($group);
                        if (preg_match('/(?:QF)?(\d+)/i', $initialNext, $m)) {
                            $groupCounters[$group->id] = (int) $m[1];
                        } else {
                            $groupCounters[$group->id] = 1001;
                        }
                    } else {
                        $groupCounters[$group->id]++;
                    }

                    $candidateId = 'QF'.$groupCounters[$group->id];
                    while (isset($usedStudentIds[$candidateId])) {
                        $groupCounters[$group->id]++;
                        $candidateId = 'QF'.$groupCounters[$group->id];
                    }
                    $studentId = $candidateId;
                    $usedStudentIds[$studentId] = true;
                }

                $student = Student::create([
                    'name' => $name,
                    'student_id' => $studentId,
                    'group_id' => $group->id,
                    'zone_id' => $zoneId,
                    'category' => $category,
                    'class_level' => $classLevel,
                    'gender' => 'Male',
                    'contact' => $contact,
                    'qr_token' => Str::random(40),
                ]);

                $createdStudents[] = $student;
            }

            if (empty($createdStudents) && ! empty($failedRows)) {
                DB::rollBack();

                return back()->withInput()->with('error', 'Bulk registration failed: '.implode(' | ', array_slice($failedRows, 0, 5)));
            }

            DB::commit();

            AuditLogger::log('bulk_create_students', null, null, [
                'count' => count($createdStudents),
                'first_id' => $createdStudents[0]->student_id ?? null,
                'last_id' => end($createdStudents)->student_id ?? null,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Error occurred during bulk registration: '.$e->getMessage());
        }

        $successMsg = count($createdStudents).' students registered successfully in bulk ('.($createdStudents[0]->student_id ?? '').' - '.(end($createdStudents)->student_id ?? '').').';
        if (! empty($failedRows)) {
            $successMsg .= ' [Note: '.count($failedRows).' rows skipped due to missing details.]';
        }

        return redirect()->route('admin.students.index')->with('success', $successMsg);
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
