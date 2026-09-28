<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\FestivalSetting;
use App\Models\PosterSetting;
use App\Models\Result;
use App\Models\ResultTemplate;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaResultController extends Controller
{
    /**
     * Display list of announced & published results for media team.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'announced');
        $search = trim((string) $request->query('search'));

        $query = Result::with([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
            'template',
        ]);

        if ($search) {
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($tab === 'announced') {
            // Priority: Announced from stage awaiting media poster
            $query->where('status', 'announced')
                ->where('is_media_published', false);
        } elseif ($tab === 'published') {
            // Posters published
            $query->where('is_media_published', true);
        } else {
            // All available results
            $query->whereIn('status', ['announced', 'published', 'send', 'delivered']);
        }

        $results = $query->latest('updated_at')->paginate(12)->withQueryString();

        $stats = [
            'announced_count' => Result::where('status', 'announced')->where('is_media_published', false)->count(),
            'published_count' => Result::where('is_media_published', true)->count(),
            'total_count' => Result::whereIn('status', ['announced', 'published', 'send', 'delivered'])->count(),
        ];

        return view('media.results.index', compact('results', 'stats', 'tab', 'search'));
    }

    /**
     * Open the Live Result Poster Designer & Settings Studio.
     */
    public function studio(Result $result): View
    {
        $result->load([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
            'template',
        ]);

        $templates = ResultTemplate::with('posterSetting')->where('is_active', true)->orderByDesc('id')->get();
        if ($templates->isEmpty()) {
            // Fallback template
            $templates = ResultTemplate::with('posterSetting')->orderByDesc('id')->get();
        }

        $activeTemplate = $result->template ?? $templates->first();

        // Get settings: either custom saved on this result, or from template, or defaults
        $defaultSettings = PosterSetting::defaultSettings();
        $templateSettings = null;
        if ($activeTemplate && $activeTemplate->posterSetting) {
            $templateSettings = $activeTemplate->posterSetting->toArray();
        }

        $currentSettings = array_merge(
            $defaultSettings,
            $templateSettings ?? [],
            $result->custom_poster_settings ?? []
        );

        // Extract ONLY 1st, 2nd, and 3rd place winners
        $winners = $result->getPosterWinners();

        $themeColor = FestivalSetting::get('theme_color', '#be1e2d');

        return view('media.results.studio', compact(
            'result',
            'templates',
            'activeTemplate',
            'currentSettings',
            'winners',
            'themeColor'
        ));
    }

    /**
     * Save generated poster image and custom layout settings.
     */
    public function savePoster(Request $request, Result $result): JsonResponse|RedirectResponse
    {
        $request->validate([
            'poster_data' => ['nullable', 'string'],
            'poster_file' => ['nullable', 'image', 'max:10240'],
            'template_id' => ['nullable', 'exists:result_templates,id'],
            'settings' => ['nullable', 'array'],
            'publish_now' => ['nullable', 'boolean'],
        ]);

        $posterUrl = $result->poster_image;

        // 1. Process base64 canvas data if provided
        if ($posterData = $request->input('poster_data')) {
            if (preg_match('/^data:image\/(\w+);base64,/', $posterData, $type)) {
                $posterData = substr($posterData, strpos($posterData, ',') + 1);
                $type = strtolower($type[1]); // png, jpeg, etc.
                $decoded = base64_decode($posterData);

                if ($decoded !== false) {
                    $filename = 'media/posters/result_'.$result->id.'_'.time().'.'.$type;
                    Storage::disk('public')->put($filename, $decoded);
                    $posterUrl = Storage::url($filename);
                }
            }
        } elseif ($request->hasFile('poster_file')) {
            $path = $request->file('poster_file')->store('media/posters', 'public');
            $posterUrl = Storage::url($path);
        }

        $isPublish = $request->boolean('publish_now', true);

        $result->update([
            'poster_image' => $posterUrl,
            'template_id' => $request->input('template_id') ?? $result->template_id,
            'custom_poster_settings' => $request->input('settings') ?? $result->custom_poster_settings,
            'is_media_published' => $isPublish ? true : $result->is_media_published,
            'media_published_at' => $isPublish ? Carbon::now() : $result->media_published_at,
            'status' => $isPublish ? 'published' : $result->status,
        ]);

        AuditLogger::log('save_result_poster', $result, null, [
            'is_published' => $result->is_media_published,
            'poster_image' => $posterUrl,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'റിസൾട്ട് പോസ്റ്റർ വിജയകരമായി തയ്യാറാക്കി പബ്ലിഷ് ചെയ്തു.',
                'poster_url' => $posterUrl,
            ]);
        }

        return redirect()->route('media.results.index', ['tab' => 'published'])
            ->with('success', "പ്രോഗ്രാം '{$result->program->name}' പോസ്റ്റർ വിജയകരമായി പബ്ലിഷ് ചെയ്തു.");
    }

    /**
     * Publish result publicly from Media Desk.
     */
    public function publishPublic(Result $result): RedirectResponse
    {
        $old = $result->toArray();
        $result->update([
            'status' => 'published',
            'is_media_published' => true,
            'media_published_at' => Carbon::now(),
            'published_at' => $result->published_at ?? Carbon::now(),
        ]);

        $result->program->update(['status' => 'completed']);

        // Issue certificates
        $this->issueCertificates($result);

        // Recalculate points
        app(PointCalculationService::class)->recalculateAllPoints();

        AuditLogger::log('media_publish_result', $result, $old, $result->toArray());

        return back()->with('success', "പ്രോഗ്രാം '{$result->program->name}' ഫലം പബ്ലിക് ആയി പ്രസിദ്ധീകരിച്ചു.");
    }

    protected function issueCertificates(Result $result): void
    {
        $program = $result->program;

        $placements = [
            '1st Place' => $result->firstEntry,
            '2nd Place' => $result->secondEntry,
            '3rd Place' => $result->thirdEntry,
        ];

        foreach ($placements as $pos => $entry) {
            if ($entry && $entry->student_id) {
                $certNum = 'QUAF09-'.strtoupper(Str::slug($program->code)).'-'.$entry->chest_number;
                Certificate::firstOrCreate(
                    ['certificate_number' => $certNum],
                    [
                        'entry_id' => $entry->id,
                        'student_id' => $entry->student_id,
                        'program_id' => $program->id,
                        'position' => $pos,
                        'issued_at' => Carbon::now(),
                        'qr_verification_url' => route('verify.certificate', $certNum),
                    ]
                );
            }
        }
    }

    /**
     * Save settings as permanent defaults for the template.
     */
    public function saveDefaultSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:result_templates,id'],
            'settings' => ['required', 'array'],
        ]);

        $template = ResultTemplate::findOrFail($validated['template_id']);
        $settings = PosterSetting::updateOrCreate(
            ['template_id' => $template->id],
            $validated['settings']
        );

        return response()->json([
            'success' => true,
            'message' => 'ടെംപ്ലേറ്റിന്റെ ഡിഫോൾട്ട് സെറ്റിംഗ്സുകൾ സേവ് ചെയ്തു.',
            'settings' => $settings,
        ]);
    }

    /**
     * Template management index.
     */
    public function templatesIndex(): View
    {
        $templates = ResultTemplate::orderByDesc('id')->get();

        return view('media.results.templates', compact('templates'));
    }

    /**
     * Store new uploaded template.
     */
    public function templateStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template_file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('template_file')->store('media/result-templates', 'public');
        $imagePath = Storage::url($path);

        $template = ResultTemplate::create([
            'name' => $validated['name'],
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Create default settings
        PosterSetting::create(array_merge(
            ['template_id' => $template->id],
            PosterSetting::defaultSettings()
        ));

        return redirect()->route('media.results.templates')
            ->with('success', 'പുതിയ റിസൾട്ട് ടെംപ്ലേറ്റ് അപ്‌ലോഡ് ചെയ്തു.');
    }

    /**
     * Toggle template active state.
     */
    public function templateToggleActive(ResultTemplate $template): RedirectResponse
    {
        $template->is_active = ! $template->is_active;
        $template->save();

        return back()->with('success', 'ടെംപ്ലേറ്റ് സ്റ്റാറ്റസ് മാറ്റി.');
    }

    /**
     * Delete template.
     */
    public function templateDestroy(ResultTemplate $template): RedirectResponse
    {
        $template->delete();

        return back()->with('success', 'ടെംപ്ലേറ്റ് നീക്കം ചെയ്തു.');
    }

    /**
     * Public Poster view for sharing.
     */
    public function publicPoster(Result $result): View
    {
        $result->load([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
        ]);

        $winners = $result->getPosterWinners();

        return view('public.poster-view', compact('result', 'winners'));
    }

    /**
     * Visual Customizer for a specific template.
     */
    public function templateCustomize(ResultTemplate $template): View
    {
        $template->load('posterSetting');
        $defaultSettings = PosterSetting::defaultSettings();
        $savedSettings = $template->posterSetting?->toArray() ?? [];
        $settings = array_merge($defaultSettings, $savedSettings);

        $themeColor = FestivalSetting::get('theme_color', '#be1e2d');

        // Sample placeholder data for visual adjustment
        $sampleData = [
            'result_no' => '01',
            'category' => 'General / Zone A',
            'competition' => 'മാപ്പിളപ്പാട്ട് (Mappilappattu)',
            'winners' => [
                'first' => [
                    ['name' => 'മുഹമ്മദ് യാസീൻ പി. (Muhammed Yaseen)', 'unit' => 'റെഡ് ഫോക്സ് (Red Fox Team)'],
                ],
                'second' => [
                    ['name' => 'അഹ്മദ് റയ്യാൻ കെ. (Ahmad Rayyan)', 'unit' => 'ഗ്രീൻ വാരിയേഴ്സ് (Green Warriors)'],
                ],
                'third' => [
                    ['name' => 'ബിലാൽ മുഹമ്മദ് (Bilal Muhammed)', 'unit' => 'ബ്ലൂ റൈഡേഴ്സ് (Blue Riders)'],
                ],
            ],
        ];

        return view('media.results.template-customizer', compact('template', 'settings', 'themeColor', 'sampleData'));
    }

    /**
     * Save custom layout settings for a template.
     */
    public function templateSaveCustomization(Request $request, ResultTemplate $template): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $posterSetting = PosterSetting::updateOrCreate(
            ['template_id' => $template->id],
            $validated['settings']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "'{$template->name}' ടെംപ്ലേറ്റിന്റെ ലേഔട്ട് സെറ്റിംഗ്സുകൾ സേവ് ചെയ്തു.",
                'settings' => $posterSetting,
            ]);
        }

        return redirect()->route('media.results.templates')
            ->with('success', "'{$template->name}' ടെംപ്ലേറ്റിന്റെ ലേഔട്ട് സെറ്റിംഗ്സുകൾ സേവ് ചെയ്തു.");
    }
}
