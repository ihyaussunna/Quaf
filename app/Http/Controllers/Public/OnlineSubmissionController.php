<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OnlineSubmission;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OnlineSubmissionController extends Controller
{
    public function show(string $slug): View
    {
        OnlineSubmissionForm::ensureSchema();

        $form = OnlineSubmissionForm::where('slug', $slug)
            ->with(['program.category', 'program.zone'])
            ->firstOrFail();

        $isOpen = $form->isOpenForSubmissions();

        return view('public.online-submission.show', compact('form', 'isOpen'));
    }

    public function showByProgram(Program $program): RedirectResponse
    {
        OnlineSubmissionForm::ensureSchema();

        $form = $program->onlineSubmissionForm;
        if (! $form) {
            abort(404, 'ഈ പ്രോഗ്രാമിന് ഓൺലൈൻ സബ്മിഷൻ ഫോം തയ്യാറാക്കിയിട്ടില്ല.');
        }

        return redirect()->route('online-submission.show', $form->slug);
    }

    public function submit(Request $request, string $slug): View|RedirectResponse
    {
        OnlineSubmissionForm::ensureSchema();

        $form = OnlineSubmissionForm::where('slug', $slug)
            ->with(['program.category', 'program.zone'])
            ->firstOrFail();

        if (! $form->isOpenForSubmissions()) {
            return back()->withErrors([
                'code_letter' => 'ക്ഷമിക്കുക, ഈ പ്രോഗ്രാമിന്റെ ഓൺലൈൻ സബ്മിഷൻ സമയം അവസാനിച്ചിരിക്കുന്നു (Submissions are currently closed).',
            ])->withInput();
        }

        $rules = [
            'code_letter' => ['required', 'string', 'max:20'],
            'text_content' => ['nullable', 'string'],
            'submission_file' => ['nullable', 'file', 'max:30720'], // 30MB max
            'video_file' => ['nullable', 'file', 'max:102400'], // 100MB max
            'video_url' => ['nullable', 'url', 'max:1000'],
        ];

        if ($form->allow_text && $form->is_text_required) {
            $rules['text_content'] = ['required', 'string', 'min:5'];
        }

        if ($form->allow_image && $form->is_image_required) {
            $rules['submission_file'] = ['required', 'file', 'max:30720'];
        }

        if ($form->allow_video && $form->is_video_required) {
            $rules['video_url'] = ['required_without:video_file', 'nullable', 'url', 'max:1000'];
            $rules['video_file'] = ['required_without:video_url', 'nullable', 'file', 'max:102400'];
        }

        $validated = $request->validate($rules);

        $codeLetterInput = strtoupper(trim($validated['code_letter']));

        // Search in program entries for this program to associate participant
        $entry = ProgramEntry::where('program_id', $form->program_id)
            ->where(function ($q) use ($codeLetterInput) {
                $q->whereRaw('UPPER(TRIM(code_letter)) = ?', [$codeLetterInput])
                    ->orWhere('chest_number', $codeLetterInput);
            })
            ->with(['student.group', 'group'])
            ->first();

        // Handle File Upload (Photo / Drawing / PDF)
        $filePath = null;
        $fileName = null;
        $fileType = null;
        $fileSize = null;

        if ($request->hasFile('submission_file')) {
            $file = $request->file('submission_file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $filePath = $file->store("submissions/{$form->program_id}", 'public');
        }

        // Handle Video Upload or Link
        $videoUrl = $validated['video_url'] ?? null;
        if ($request->hasFile('video_file')) {
            $videoFile = $request->file('video_file');
            $videoPath = $videoFile->store("submissions/{$form->program_id}/videos", 'public');
            $videoUrl = Storage::disk('public')->url($videoPath);
        }

        // Check if an existing submission was made with this code letter for this form
        $submission = OnlineSubmission::where('online_submission_form_id', $form->id)
            ->where('code_letter', $codeLetterInput)
            ->first();

        if ($submission) {
            // Update existing submission
            $submission->text_content = $validated['text_content'] ?? $submission->text_content;
            if ($filePath) {
                $submission->file_path = $filePath;
                $submission->file_name = $fileName;
                $submission->file_type = $fileType;
                $submission->file_size = $fileSize;
            }
            if ($videoUrl) {
                $submission->video_url = $videoUrl;
            }
            $submission->ip_address = $request->ip();
            $submission->submitted_at = now();
            if ($entry) {
                $submission->program_entry_id = $entry->id;
                $submission->chest_number = $entry->chest_number;
                $submission->student_name = $entry->student?->name;
                $submission->student_id = $entry->student?->student_id;
                $submission->group_id = $entry->group_id;
            }
            $submission->save();
        } else {
            $submission = OnlineSubmission::create([
                'online_submission_form_id' => $form->id,
                'program_id' => $form->program_id,
                'program_entry_id' => $entry?->id,
                'code_letter' => $codeLetterInput,
                'chest_number' => $entry?->chest_number,
                'student_name' => $entry?->student?->name,
                'student_id' => $entry?->student?->student_id,
                'group_id' => $entry?->group_id,
                'text_content' => $validated['text_content'] ?? null,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'video_url' => $videoUrl,
                'ip_address' => $request->ip(),
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }

        if ($entry && $entry->attendance_status !== 'present') {
            $entry->update(['attendance_status' => 'present']);
        }

        AuditLogger::log('online_submission_received', $submission);

        return view('public.online-submission.success', compact('form', 'submission'));
    }
}
