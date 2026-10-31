<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\PosterSetting;
use App\Models\Program;
use App\Models\Result;
use App\Models\ResultMedal;
use App\Models\ResultTemplate;
use App\Models\StandingAnnouncement;
use App\Services\AuditLogger;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use App\Services\ResultPosterGenerator;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            // Priority: Announced from stage or declared awaiting media poster
            $query->whereIn('status', ['announced', 'published'])
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
            'announced_count' => Result::whereIn('status', ['announced', 'published'])->where('is_media_published', false)->count(),
            'published_count' => Result::where('is_media_published', true)->count(),
            'total_count' => Result::whereIn('status', ['announced', 'published', 'send', 'delivered'])->count(),
        ];

        $standingsInfo = [
            'published_results_count' => Result::where('status', 'published')->whereNotNull('published_at')->count(),
            'public_standings_count' => (int) FestivalSetting::get('public_standings_results_count', 0),
            'public_standings_label' => FestivalSetting::get('public_standings_label', 'Official Standings'),
            'public_standings_mode' => FestivalSetting::get('public_standings_mode', 'milestone'),
            'public_standings_milestones' => FestivalSetting::get('public_standings_milestones', '10,15,20,27,35,40'),
            'public_standings_updated_at' => FestivalSetting::get('public_standings_updated_at'),
        ];

        $incomingStandings = collect();
        $publishedStandingsHistory = collect();
        try {
            StandingAnnouncement::ensureSchema();
            if (Schema::hasTable('standing_announcements')) {
                $incomingStandings = StandingAnnouncement::where('status', 'announced')->latest()->get();
                $publishedStandingsHistory = StandingAnnouncement::where('status', 'published')->latest()->take(6)->get();
            }
        } catch (\Throwable) {
            $incomingStandings = collect();
            $publishedStandingsHistory = collect();
        }

        return view('media.results.index', compact('results', 'stats', 'tab', 'search', 'standingsInfo', 'incomingStandings', 'publishedStandingsHistory'));
    }

    /**
     * Publish team standings announcement received from Announcer to the public website.
     */
    public function publishStandingAnnouncement(StandingAnnouncement $standingAnnouncement): RedirectResponse
    {
        $standingAnnouncement->publishPublicly(auth()->id());

        AuditLogger::log('media_publish_standing', $standingAnnouncement, ['status' => 'announced'], ['status' => 'published']);

        return back()->with('success', "ടീം സ്റ്റാൻഡിങ്സ് വിജയകരമായി പബ്ലിക് വെബ്സൈറ്റിൽ പ്രസിദ്ധീകരിച്ചു ({$standingAnnouncement->title}).");
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

        // 1. Determine active template:
        $savedDefaultTemplateId = FestivalSetting::get('default_poster_template_id');
        $activeTemplate = $result->template;
        if (! $activeTemplate && $savedDefaultTemplateId) {
            $activeTemplate = $templates->firstWhere('id', (int) $savedDefaultTemplateId);
        }
        if (! $activeTemplate) {
            $activeTemplate = $templates->first();
        }

        // 2. Layered preset loading
        $baseDefaults = PosterSetting::defaultSettings();
        $dbPosterSettings = $activeTemplate?->posterSetting?->toArray() ?? [];
        $templateJsonSettings = is_array($activeTemplate?->default_settings) ? $activeTemplate->default_settings : [];

        $templatePresetJson = FestivalSetting::get('poster_preset_template_'.$activeTemplate?->id);
        $templatePreset = $templatePresetJson ? json_decode($templatePresetJson, true) : [];
        if (! is_array($templatePreset)) {
            $templatePreset = [];
        }

        $globalPresetJson = FestivalSetting::get('global_active_poster_preset');
        $globalPreset = $globalPresetJson ? json_decode($globalPresetJson, true) : [];
        if (! is_array($globalPreset)) {
            $globalPreset = [];
        }

        // 3. Result's own saved settings (if edited before)
        $customSettings = $result->custom_poster_settings ?? [];
        if (! is_array($customSettings)) {
            $customSettings = [];
        }

        // Merge in order of precedence: base -> db table -> template JSON -> global preset -> template preset -> result custom settings
        $currentSettings = array_merge(
            $baseDefaults,
            $dbPosterSettings,
            $templateJsonSettings,
            $globalPreset,
            $templatePreset,
            $customSettings
        );

        // Extract 1st, 2nd, and 3rd place winners (including ties or saved custom winners)
        $hasCustomWinners = false;
        if (! empty($customSettings['custom_winners']) && is_array($customSettings['custom_winners'])) {
            foreach ($customSettings['custom_winners'] as $cwList) {
                if (is_array($cwList)) {
                    foreach ($cwList as $cw) {
                        if (! empty($cw['name']) || ! empty($cw['unit'])) {
                            $hasCustomWinners = true;
                            break 2;
                        }
                    }
                }
            }
        }

        $winners = $hasCustomWinners
            ? $customSettings['custom_winners']
            : $result->getPosterWinners();

        // Compute sequence result number (e.g. 37, 61) rather than program code
        $resultNo = Result::where(function ($q) use ($result) {
            if ($result->published_at) {
                $q->where('published_at', '<', $result->published_at)
                    ->orWhere(function ($sub) use ($result) {
                        $sub->where('published_at', '=', $result->published_at)
                            ->where('id', '<=', $result->id);
                    });
            } else {
                $q->where('id', '<=', $result->id);
            }
        })->count() ?: ($result->id ?? 1);

        $themeColor = FestivalSetting::get('theme_color', '#be1e2d');

        $templatesMap = $templates->keyBy('id')->map(function ($t) {
            $presetJson = FestivalSetting::get('poster_preset_template_'.$t->id);
            $preset = $presetJson ? json_decode($presetJson, true) : null;
            if (! is_array($preset)) {
                $preset = is_array($t->default_settings) ? $t->default_settings : ($t->posterSetting?->toArray() ?? []);
            }

            return [
                'id' => $t->id,
                'name' => $t->name,
                'image' => asset($t->image_path),
                'layout_mode' => $t->posterSetting?->layout_mode ?? ($preset['layout_mode'] ?? 'default'),
                'preset' => $preset,
            ];
        });

        $medalPacks = collect();
        if (! Schema::hasTable('result_medals')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Ignore if artisan call is restricted
            }
        }

        if (Schema::hasTable('result_medals')) {
            try {
                $medalPacks = ResultMedal::where('is_active', true)->orderByDesc('id')->get();
            } catch (\Throwable $e) {
                $medalPacks = collect();
            }
        }

        if ($medalPacks->isEmpty()) {
            $medalPacks = collect([
                (object) [
                    'id' => 1,
                    'name' => 'Classic Official Medals',
                    'first_image' => asset('images/medals/first.png'),
                    'second_image' => asset('images/medals/second.png'),
                    'third_image' => asset('images/medals/third.png'),
                    'is_active' => true,
                ],
            ]);
        }

        return view('media.results.studio', compact(
            'result',
            'templates',
            'templatesMap',
            'medalPacks',
            'activeTemplate',
            'currentSettings',
            'winners',
            'resultNo',
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

        $updates = [
            'poster_image' => $posterUrl,
            'template_id' => $request->input('template_id') ?? $result->template_id,
            'custom_poster_settings' => $request->input('settings') ?? $result->custom_poster_settings,
        ];

        if ($isPublish) {
            $updates['is_media_published'] = true;
            $updates['media_published_at'] = Carbon::now();
            $updates['published_at'] = $result->published_at ?? Carbon::now();
            $updates['status'] = 'published';
        }

        $result->update($updates);

        if ($isPublish) {
            $result->program->update(['status' => 'completed']);
            $this->issueCertificates($result);
            app(PointCalculationService::class)->recalculateAllPoints();
            app(CoinRewardService::class)->distributeCoinsForResult($result);

            // Check if milestone (e.g. after 10, 15, 20, 27, 35, 40) was reached
            Group::checkAndApplyMilestone();
        }

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

        // Distribute coins to winners
        app(CoinRewardService::class)->distributeCoinsForResult($result);

        // Check if milestone (e.g. after 10, 15, 20, 27, 35, 40) was reached
        Group::checkAndApplyMilestone();

        AuditLogger::log('media_publish_result', $result, $old, $result->toArray());

        return back()->with('success', "പ്രോഗ്രാം '{$result->program->name}' ഫലം പബ്ലിക് ആയി പ്രസിദ്ധീകരിച്ചു.");
    }

    /**
     * Manual on-demand release of team standings from Media Desk.
     */
    public function publishStandings(Request $request): RedirectResponse
    {
        $customLabel = $request->input('custom_label', $request->input('label'));
        $count = Group::publishStandings($customLabel);

        return back()->with('success', "ടീം സ്റ്റാൻഡിങ് വിജയകരമായി അപ്‌ഡേറ്റ് ചെയ്തു (After {$count} Results). പബ്ലിക് വെബ്‌സൈറ്റിൽ പുതിയ പോയിന്റ് നില ലഭ്യമാണ്.");
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
            if ($entry) {
                // If individual entry
                if ($entry->student_id) {
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

                // If group entry with participants
                $participants = $entry->participants()->get();
                foreach ($participants as $participant) {
                    $certNum = 'QUAF09-'.strtoupper(Str::slug($program->code)).'-'.$participant->student_id;
                    Certificate::firstOrCreate(
                        ['certificate_number' => $certNum],
                        [
                            'entry_id' => $entry->id,
                            'student_id' => $participant->id,
                            'program_id' => $program->id,
                            'position' => $pos,
                            'issued_at' => Carbon::now(),
                            'qr_verification_url' => route('verify.certificate', $certNum),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Save settings as permanent defaults for the template.
     */
    public function saveDefaultSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'template_id' => ['required'],
                'result_id' => ['nullable', 'exists:results,id'],
                'settings' => ['required', 'array'],
            ]);

            $template = ResultTemplate::find($validated['template_id']);
            if (! $template) {
                $template = ResultTemplate::first();
            }

            if (! $template) {
                return response()->json([
                    'success' => false,
                    'message' => 'ടെംപ്ലേറ്റ് കണ്ടെത്താൻ കഴിഞ്ഞില്ല.',
                ], 404);
            }

            // 0. Save permanent preset in FestivalSetting (persists across all databases & sessions)
            $presetJson = json_encode($validated['settings']);
            FestivalSetting::set('poster_preset_template_'.$template->id, $presetJson);
            FestivalSetting::set('global_active_poster_preset', $presetJson);
            FestivalSetting::set('default_poster_template_id', (string) $template->id);

            // 1. Ensure default_settings column exists on result_templates table
            if (! Schema::hasColumn('result_templates', 'default_settings')) {
                try {
                    Schema::table('result_templates', function (Blueprint $table) {
                        $table->json('default_settings')->nullable();
                    });
                } catch (\Throwable $e) {
                    // Ignore schema alter errors
                }
            }

            if (Schema::hasColumn('result_templates', 'default_settings')) {
                $template->update(['default_settings' => $validated['settings']]);
            }

            // 2. Safely update poster_settings only with valid existing table columns
            $settingsRecord = null;
            if (Schema::hasTable('poster_settings')) {
                $columns = Schema::getColumnListing('poster_settings');
                $settingPayload = collect($validated['settings'])
                    ->filter(fn ($val, $key) => in_array($key, $columns) && ! is_array($val) && ! is_object($val))
                    ->toArray();

                $settingsRecord = PosterSetting::updateOrCreate(
                    ['template_id' => $template->id],
                    $settingPayload
                );
            }

            // 3. If result_id provided, also update current result's custom settings
            if (! empty($validated['result_id'])) {
                $result = Result::find($validated['result_id']);
                if ($result) {
                    $result->update([
                        'template_id' => $template->id,
                        'custom_poster_settings' => $validated['settings'],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'ടെംപ്ലേറ്റിന്റെ ഡിഫോൾട്ട് സെറ്റിംഗ്സുകൾ വിജയകരമായി സേവ് ചെയ്തു.',
                'settings' => $settingsRecord,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'ഡിഫോൾട്ട് സേവ് ചെയ്യുന്നതിൽ പിഴവ് സംഭവിച്ചു: '.$e->getMessage(),
            ], 500);
        }
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
     * Medals list & management index.
     */
    public function medalsIndex(): View
    {
        if (! Schema::hasTable('result_medals')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        $medals = collect();
        if (Schema::hasTable('result_medals')) {
            try {
                $medals = ResultMedal::orderByDesc('id')->get();
            } catch (\Throwable $e) {
                $medals = collect();
            }
        }

        return view('media.results.medals', compact('medals'));
    }

    /**
     * Store new custom medal set.
     */
    public function medalStore(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('result_medals')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_image' => ['required', 'image', 'mimes:png,webp,svg,jpg,jpeg', 'max:5120'],
            'second_image' => ['required', 'image', 'mimes:png,webp,svg,jpg,jpeg', 'max:5120'],
            'third_image' => ['required', 'image', 'mimes:png,webp,svg,jpg,jpeg', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $firstPath = Storage::url($request->file('first_image')->store('media/medals', 'public'));
        $secondPath = Storage::url($request->file('second_image')->store('media/medals', 'public'));
        $thirdPath = Storage::url($request->file('third_image')->store('media/medals', 'public'));

        ResultMedal::create([
            'name' => $validated['name'],
            'first_image' => $firstPath,
            'second_image' => $secondPath,
            'third_image' => $thirdPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('media.results.medals')
            ->with('success', 'പുതിയ മെഡൽ സെറ്റ് വിജയകരമായി അപ്‌ലോഡ് ചെയ്തു.');
    }

    /**
     * Upload custom medal set on-the-fly directly from the Studio via AJAX.
     */
    public function medalAjaxUpload(Request $request): JsonResponse
    {
        if (! Schema::hasTable('result_medals')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'first_image' => ['nullable', 'image', 'max:5120'],
            'second_image' => ['nullable', 'image', 'max:5120'],
            'third_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $firstPath = $request->hasFile('first_image')
            ? Storage::url($request->file('first_image')->store('media/medals', 'public'))
            : '/images/medals/first.png';

        $secondPath = $request->hasFile('second_image')
            ? Storage::url($request->file('second_image')->store('media/medals', 'public'))
            : '/images/medals/second.png';

        $thirdPath = $request->hasFile('third_image')
            ? Storage::url($request->file('third_image')->store('media/medals', 'public'))
            : '/images/medals/third.png';

        $name = $request->input('name') ?: ('Custom Medals '.date('d M H:i'));

        $medal = ResultMedal::create([
            'name' => $name,
            'first_image' => $firstPath,
            'second_image' => $secondPath,
            'third_image' => $thirdPath,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'മെഡലുകൾ വിജയകരമായി അപ്‌ലോഡ് ചെയ്തു.',
            'medal' => [
                'id' => $medal->id,
                'name' => $medal->name,
                'first_image' => asset($medal->first_image),
                'second_image' => asset($medal->second_image),
                'third_image' => asset($medal->third_image),
            ],
        ]);
    }

    /**
     * Toggle medal pack active state.
     */
    public function medalToggleActive(ResultMedal $medal): RedirectResponse
    {
        $medal->is_active = ! $medal->is_active;
        $medal->save();

        return back()->with('success', 'മെഡൽ സ്റ്റാറ്റസ് മാറ്റി.');
    }

    /**
     * Delete medal set.
     */
    public function medalDestroy(ResultMedal $medal): RedirectResponse
    {
        $medal->delete();

        return back()->with('success', 'മെഡൽ സെറ്റ് നീക്കം ചെയ്തു.');
    }

    /**
     * Public Poster view for sharing.
     */
    public function publicPoster(Result $result): View
    {
        abort_unless($result->status === 'published' && (bool) $result->is_media_published, 404);

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
     * Download Result Poster directly as an image file.
     */
    public function downloadPoster(Result $result): Response|StreamedResponse
    {
        abort_unless($result->status === 'published' && (bool) $result->is_media_published, 404);

        $result->loadMissing([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
        ]);

        // 1. If static canvas poster already saved & published, download that image file
        if (! empty($result->poster_image)) {
            $rawPath = $result->getRawOriginal('poster_image') ?? $result->poster_image;
            $parsed = parse_url($rawPath, PHP_URL_PATH) ?? $rawPath;
            $cleanPath = ltrim($parsed, '/');

            $diskPath = $cleanPath;
            if (str_starts_with($diskPath, 'storage/')) {
                $diskPath = substr($diskPath, strlen('storage/'));
            }

            $ext = pathinfo($cleanPath, PATHINFO_EXTENSION) ?: 'png';
            $downloadFilename = $result->getDownloadFilename($ext);

            if (Storage::disk('public')->exists($diskPath)) {
                $fullPath = Storage::disk('public')->path($diskPath);

                return response()->download($fullPath, $downloadFilename, [
                    'Content-Type' => mime_content_type($fullPath) ?: 'image/png',
                ]);
            }

            $publicPath = public_path($cleanPath);
            if (file_exists($publicPath)) {
                return response()->download($publicPath, $downloadFilename, [
                    'Content-Type' => mime_content_type($publicPath) ?: 'image/png',
                ]);
            }
        }

        // 2. Otherwise generate dynamic high-resolution poster using ResultPosterGenerator
        $img = ResultPosterGenerator::generate($result);
        $downloadFilename = $result->getDownloadFilename('jpg');

        return ResultPosterGenerator::streamResponse($img, $downloadFilename, true);
    }

    /**
     * Visual Customizer for a specific template.
     */
    public function templateCustomize(ResultTemplate $template): View
    {
        $template->load('posterSetting');

        $baseDefaults = PosterSetting::defaultSettings();
        $dbPosterSettings = $template->posterSetting?->toArray() ?? [];
        $templateJsonSettings = is_array($template->default_settings) ? $template->default_settings : [];

        $templatePresetJson = FestivalSetting::get('poster_preset_template_'.$template->id);
        $templatePreset = $templatePresetJson ? json_decode($templatePresetJson, true) : [];
        if (! is_array($templatePreset)) {
            $templatePreset = [];
        }

        $globalPresetJson = FestivalSetting::get('global_active_poster_preset');
        $globalPreset = $globalPresetJson ? json_decode($globalPresetJson, true) : [];
        if (! is_array($globalPreset)) {
            $globalPreset = [];
        }

        $currentSettings = array_merge(
            $baseDefaults,
            $dbPosterSettings,
            $templateJsonSettings,
            $globalPreset,
            $templatePreset
        );
        $currentSettings['template_id'] = $template->id;
        $currentSettings['template_image'] = asset($template->image_path);

        $templates = ResultTemplate::where('is_active', true)->get();
        if (! $templates->contains('id', $template->id)) {
            $templates->prepend($template);
        }

        $templatesMap = $templates->keyBy('id')->map(function ($t) {
            $presetJson = FestivalSetting::get('poster_preset_template_'.$t->id);
            $preset = $presetJson ? json_decode($presetJson, true) : null;
            if (! is_array($preset)) {
                $preset = is_array($t->default_settings) ? $t->default_settings : ($t->posterSetting?->toArray() ?? []);
            }

            return [
                'id' => $t->id,
                'name' => $t->name,
                'image' => asset($t->image_path),
                'layout_mode' => $t->posterSetting?->layout_mode ?? ($preset['layout_mode'] ?? 'default'),
                'preset' => $preset,
            ];
        });

        $medalPacks = collect();
        if (Schema::hasTable('result_medals')) {
            try {
                $medalPacks = ResultMedal::where('is_active', true)->orderByDesc('id')->get();
            } catch (\Throwable $e) {
                $medalPacks = collect();
            }
        }
        if ($medalPacks->isEmpty()) {
            $medalPacks = collect([
                (object) [
                    'id' => 1,
                    'name' => 'Classic Official Medals',
                    'first_image' => asset('images/medals/first.png'),
                    'second_image' => asset('images/medals/second.png'),
                    'third_image' => asset('images/medals/third.png'),
                    'is_active' => true,
                ],
            ]);
        }

        $sampleProgName = 'MAPPILAPPATTU';
        $sampleProgCode = 'Q9-224';
        $sampleZoneName = 'MIX ZONE';

        $result = (object) [
            'id' => 1,
            'status' => 'template_design',
            'template_id' => $template->id,
            'program' => (object) [
                'name' => $sampleProgName,
                'code' => $sampleProgCode,
                'eligibility' => $sampleZoneName,
                'zone' => (object) ['name' => $sampleZoneName],
            ],
            'custom_poster_settings' => $currentSettings,
        ];

        $winners = [
            'first' => [
                ['name' => 'MUHAMMED YASEEN P.', 'unit' => 'RED FOX TEAM'],
            ],
            'second' => [
                ['name' => 'AHMAD RAYYAN K.', 'unit' => 'GREEN WARRIORS'],
            ],
            'third' => [
                ['name' => 'BILAL MUHAMMED', 'unit' => 'BLUE RIDERS'],
            ],
        ];

        $activeTemplate = $template;
        $resultNo = 50;
        $themeColor = FestivalSetting::get('theme_color', '#be1e2d');
        $isTemplateCustomizer = true;

        return view('media.results.studio', compact(
            'result',
            'template',
            'isTemplateCustomizer',
            'templates',
            'templatesMap',
            'medalPacks',
            'activeTemplate',
            'currentSettings',
            'winners',
            'resultNo',
            'themeColor'
        ));
    }

    /**
     * Save custom layout settings for a template.
     */
    public function templateSaveCustomization(Request $request, ResultTemplate $template): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $settings = $validated['settings'];

        // 1. Save permanent preset in FestivalSetting
        $presetJson = json_encode($settings);
        FestivalSetting::set('poster_preset_template_'.$template->id, $presetJson);
        FestivalSetting::set('global_active_poster_preset', $presetJson);
        FestivalSetting::set('default_poster_template_id', (string) $template->id);

        // 2. Ensure default_settings column exists on result_templates table
        if (! Schema::hasColumn('result_templates', 'default_settings')) {
            try {
                Schema::table('result_templates', function (Blueprint $table) {
                    $table->json('default_settings')->nullable();
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasColumn('result_templates', 'default_settings')) {
            $template->update(['default_settings' => $settings]);
        }

        // 3. Update poster_settings table
        if (Schema::hasTable('poster_settings')) {
            $columns = Schema::getColumnListing('poster_settings');
            $settingPayload = collect($settings)
                ->filter(fn ($val, $key) => in_array($key, $columns) && ! is_array($val) && ! is_object($val))
                ->toArray();

            PosterSetting::updateOrCreate(
                ['template_id' => $template->id],
                $settingPayload
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "'{$template->name}' ടെംപ്ലേറ്റിന്റെ ലേഔട്ട് ഡിഫോൾട്ടായി സേവ് ചെയ്തു. ഇനി ഈ ടെംപ്ലേറ്റ് തിരഞ്ഞെടുക്കുന്ന എല്ലാ റിസൾട്ടുകൾക്കും ഈ ക്രമീകരണം ലഭ്യമാകും.",
            ]);
        }

        return redirect()->route('media.results.templates')
            ->with('success', "'{$template->name}' ടെംപ്ലേറ്റിന്റെ ലേഔട്ട് ഡിഫോൾട്ടായി സേവ് ചെയ്തു.");
    }

    /**
     * Standalone Photopea Illustrator/Photoshop Web Editor for festival designers.
     */
    public function photopeaEditor(): View
    {
        $templates = ResultTemplate::where('is_active', true)->get();

        return view('media.photopea', compact('templates'));
    }
}
