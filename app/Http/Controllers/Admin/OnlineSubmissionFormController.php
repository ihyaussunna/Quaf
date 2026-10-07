<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnlineSubmission;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Services\AuditLogger;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnlineSubmissionFormController extends Controller
{
    public function index(Request $request): View
    {
        OnlineSubmissionForm::ensureSchema();

        $query = OnlineSubmissionForm::with(['program.category', 'program.zone'])
            ->withCount('submissions');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $forms = $query->latest()->paginate(20)->withQueryString();

        return view('admin.online-forms.index', compact('forms', 'search'));
    }

    public function create(Request $request): View
    {
        OnlineSubmissionForm::ensureSchema();

        $selectedProgramId = $request->query('program_id');
        $programs = Program::with(['category', 'zone'])
            ->orderBy('code')
            ->get();

        $selectedProgram = $selectedProgramId ? Program::find($selectedProgramId) : null;

        return view('admin.online-forms.create', compact('programs', 'selectedProgram'));
    }

    public function store(Request $request): RedirectResponse
    {
        OnlineSubmissionForm::ensureSchema();

        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'allow_text' => ['nullable', 'boolean'],
            'text_label' => ['nullable', 'string', 'max:255'],
            'text_placeholder' => ['nullable', 'string'],
            'is_text_required' => ['nullable', 'boolean'],
            'allow_image' => ['nullable', 'boolean'],
            'image_label' => ['nullable', 'string', 'max:255'],
            'is_image_required' => ['nullable', 'boolean'],
            'allow_video' => ['nullable', 'boolean'],
            'video_label' => ['nullable', 'string', 'max:255'],
            'is_video_required' => ['nullable', 'boolean'],
            'is_open' => ['nullable', 'boolean'],
            'closes_at' => ['nullable', 'date'],
        ]);

        $program = Program::findOrFail($validated['program_id']);

        // Check if a form already exists for this program
        $existing = OnlineSubmissionForm::where('program_id', $program->id)->first();
        if ($existing) {
            return redirect()->route('admin.online-forms.edit', $existing->id)
                ->with('info', 'ഈ പ്രോഗ്രാമിന് നേരത്തെ തന്നെ ഫോം നിലവിലുണ്ട്. നിങ്ങൾക്ക് അത് ഇവിടെ എഡിറ്റ് ചെയ്യാവുന്നതാണ്.');
        }

        $slug = strtolower(Str::slug($program->code).'-'.Str::random(6));

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => $slug,
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'allow_text' => $request->boolean('allow_text', true),
            'text_label' => ($validated['text_label'] ?? null) ?: 'Content / Text Submission',
            'text_placeholder' => $validated['text_placeholder'] ?? null,
            'is_text_required' => $request->boolean('is_text_required', false),
            'allow_image' => $request->boolean('allow_image', false),
            'image_label' => ($validated['image_label'] ?? null) ?: 'Upload Photo / Document',
            'is_image_required' => $request->boolean('is_image_required', false),
            'allow_video' => $request->boolean('allow_video', false),
            'video_label' => ($validated['video_label'] ?? null) ?: 'Video File or Link',
            'is_video_required' => $request->boolean('is_video_required', false),
            'is_open' => $request->boolean('is_open', true),
            'closes_at' => $validated['closes_at'] ?? null,
        ]);

        AuditLogger::log('online_form_created', $form);

        return redirect()->route('admin.online-forms.index')
            ->with('success', "'{$program->name}' എന്ന പ്രോഗ്രാമിന് ഓൺലൈൻ സബ്മിഷൻ ഫോം വിജയകരമായി തയ്യാറാക്കി. ഗ്രീൻ റൂമിൽ ഇതിന്റെ ക്യുആർ കോഡ് തത്സമയം ലഭ്യമായിരിക്കും.");
    }

    public function edit(OnlineSubmissionForm $form): View
    {
        $form->load(['program.category', 'program.zone']);
        $programs = Program::with(['category', 'zone'])->orderBy('code')->get();

        return view('admin.online-forms.edit', compact('form', 'programs'));
    }

    public function update(Request $request, OnlineSubmissionForm $form): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'text_label' => ['nullable', 'string', 'max:255'],
            'text_placeholder' => ['nullable', 'string'],
            'image_label' => ['nullable', 'string', 'max:255'],
            'video_label' => ['nullable', 'string', 'max:255'],
            'closes_at' => ['nullable', 'date'],
        ]);

        $form->update([
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'allow_text' => $request->boolean('allow_text'),
            'text_label' => ($validated['text_label'] ?? null) ?: 'Content / Text Submission',
            'text_placeholder' => $validated['text_placeholder'] ?? null,
            'is_text_required' => $request->boolean('is_text_required'),
            'allow_image' => $request->boolean('allow_image'),
            'image_label' => ($validated['image_label'] ?? null) ?: 'Upload Photo / Document',
            'is_image_required' => $request->boolean('is_image_required'),
            'allow_video' => $request->boolean('allow_video'),
            'video_label' => ($validated['video_label'] ?? null) ?: 'Video File or Link',
            'is_video_required' => $request->boolean('is_video_required'),
            'is_open' => $request->boolean('is_open'),
            'closes_at' => $validated['closes_at'] ?? null,
        ]);

        AuditLogger::log('online_form_updated', $form);

        return redirect()->route('admin.online-forms.index')
            ->with('success', 'ഓൺലൈൻ സബ്മിഷൻ ഫോം വിജയകരമായി അപ്‌ഡേറ്റ് ചെയ്തു.');
    }

    public function toggleStatus(OnlineSubmissionForm $form): RedirectResponse
    {
        $form->is_open = ! $form->is_open;
        $form->save();

        $statusText = $form->is_open ? 'തുറന്നു (Open)' : 'അടച്ചു (Closed)';

        return back()->with('success', "ഫോം സബ്മിഷൻ വിജയകരമായി {$statusText}.");
    }

    public function destroy(OnlineSubmissionForm $form): RedirectResponse
    {
        $programName = $form->program?->name ?? 'Program';
        AuditLogger::log('online_form_deleted', $form);
        $form->delete();

        return redirect()->route('admin.online-forms.index')
            ->with('success', 'ഓൺലൈൻ സബ്മിഷൻ ഫോം നീക്കം ചെയ്തു.');
    }

    public function submissions(OnlineSubmissionForm $form): View
    {
        $form->load(['program.category', 'program.zone']);
        $submissions = $form->submissions()
            ->with(['group', 'programEntry.student'])
            ->latest('submitted_at')
            ->paginate(30);

        return view('admin.online-forms.submissions', compact('form', 'submissions'));
    }

    public function destroySubmission(OnlineSubmission $submission): RedirectResponse
    {
        $formId = $submission->online_submission_form_id;
        $code = $submission->code_letter;
        $submission->delete();

        return redirect()->route('admin.online-forms.submissions', $formId)
            ->with('success', "കോഡ് '{$code}' സബ്മിഷൻ നീക്കം ചെയ്തു.");
    }

    public function qr(OnlineSubmissionForm $form): View
    {
        $form->load(['program.category', 'program.zone']);
        $publicUrl = $form->public_url;
        $qrCodeSvg = QrCodeService::svg($publicUrl, 420);

        return view('admin.online-forms.qr', compact('form', 'publicUrl', 'qrCodeSvg'));
    }
}
