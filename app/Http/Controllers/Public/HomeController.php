<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FestivalSetting;
use App\Models\FundReceipt;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\News;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Result;
use App\Models\Stage;
use App\Models\Student;
use App\Models\VideoItem;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $launchStatus = FestivalSetting::get('launch_status', 'launched');

        // Only show coming-soon landing page if explicitly configured as coming_soon and not previewed
        if ($launchStatus === 'coming_soon' && ! $request->has('portal') && ! $request->has('preview')) {
            return view('public.coming-soon');
        }

        return $this->renderHomepage();
    }

    /**
     * Dedicated /home route to access the full public festival website.
     */
    public function homeView(): View
    {
        return $this->renderHomepage();
    }

    /**
     * Dedicated /launch route for official website launch ceremonies.
     */
    public function launch(): View
    {
        return view('public.launch');
    }

    public function about(): View
    {
        $groups = Group::orderBy('rank_cache')->get();
        $zones = Zone::orderBy('display_order')->get();
        $stats = [
            'programs' => Program::count(),
            'students' => Student::count(),
            'groups' => Group::count(),
            'stages' => Stage::count(),
            'zones' => Zone::count(),
        ];

        return view('public.about', compact('groups', 'zones', 'stats'));
    }

    public function contact(): View
    {
        $stages = Stage::orderBy('id')->get();

        return view('public.contact', compact('stages'));
    }

    /**
     * Dedicated public donation page for QUAF 2026.
     */
    public function donate(): View
    {
        return view('public.donate');
    }

    /**
     * Check if an officially verified receipt exists for a given transaction reference or receipt number.
     */
    public function checkReceipt(Request $request): JsonResponse
    {
        FundReceipt::ensureSchema();

        $query = trim((string) $request->input('transaction_id', $request->input('query')));

        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'ദയവായി ശരിയായ Transaction ID അല്ലെങ്കിൽ രസീത് നമ്പർ നൽകുക.',
            ], 422);
        }

        $receipt = FundReceipt::where('is_cancelled', false)
            ->where(function ($q) use ($query) {
                $q->where('transaction_reference', $query)
                    ->orWhere('receipt_number', $query);
            })
            ->latest('id')
            ->first();

        if (! $receipt) {
            return response()->json([
                'found' => false,
                'transaction_id' => $query,
                'message' => 'ഈ Transaction ID-യിൽ അംഗീകൃത രസീത് ലഭ്യമായിട്ടില്ല.',
            ]);
        }

        return response()->json([
            'found' => true,
            'receipt' => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'donor_name' => $receipt->donor_name,
                'amount' => (float) $receipt->amount,
                'formatted_amount' => number_format((float) $receipt->amount),
                'date' => $receipt->created_at ? $receipt->created_at->format('d/m/Y') : date('d/m/Y'),
                'download_url' => route('fund.receipts.download', $receipt),
                'image_url' => route('fund.receipts.image', $receipt),
                'view_url' => route('fund.receipts.show', $receipt),
                'screenshot_url' => $receipt->screenshot_url,
            ],
        ]);
    }

    /**
     * Upload transaction details with screenshot proof and generate/download receipt poster.
     */
    public function uploadReceipt(Request $request): JsonResponse
    {
        FundReceipt::ensureSchema();

        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'min:3', 'max:100'],
            'whatsapp_number' => ['required', 'string', 'min:7', 'max:25'],
            'donor_name' => ['required', 'string', 'min:2', 'max:150'],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'screenshot' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'donor_place' => ['nullable', 'string', 'max:100'],
        ]);

        $txnId = trim($validated['transaction_id']);
        $whatsapp = trim($validated['whatsapp_number']);

        // Store screenshot proof safely
        $screenshotPath = $request->file('screenshot')->store('receipt_proofs', 'public');

        // Check if receipt already exists for this transaction reference
        $receipt = FundReceipt::where('is_cancelled', false)
            ->where('transaction_reference', $txnId)
            ->latest('id')
            ->first();

        if ($receipt) {
            $receipt->update([
                'screenshot_path' => $screenshotPath,
                'whatsapp_number' => $whatsapp,
                'donor_phone' => $receipt->donor_phone ?: $whatsapp,
            ]);
        } else {
            $receiptNumber = FundReceipt::nextReceiptNumber();
            $amountInWords = FundReceipt::amountInWords($validated['amount']);

            $receipt = FundReceipt::create([
                'receipt_number' => $receiptNumber,
                'donor_name' => trim($validated['donor_name']),
                'donor_phone' => $whatsapp,
                'whatsapp_number' => $whatsapp,
                'donor_place' => trim($validated['donor_place'] ?? ''),
                'amount' => $validated['amount'],
                'amount_in_words' => $amountInWords,
                'payment_method' => 'upi',
                'transaction_reference' => $txnId,
                'screenshot_path' => $screenshotPath,
                'category' => '₹100 Challenge',
                'collector_name' => 'Online Donor Upload',
                'notes' => 'Uploaded via public donation portal with payment screenshot proof',
                'is_cancelled' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'receipt' => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'donor_name' => $receipt->donor_name,
                'amount' => (float) $receipt->amount,
                'formatted_amount' => number_format((float) $receipt->amount),
                'date' => $receipt->created_at ? $receipt->created_at->format('d/m/Y') : date('d/m/Y'),
                'download_url' => route('fund.receipts.download', $receipt),
                'image_url' => route('fund.receipts.image', $receipt),
                'view_url' => route('fund.receipts.show', $receipt),
                'screenshot_url' => $receipt->screenshot_url,
            ],
            'message' => 'പേയ്‌മെന്റ് വിവരങ്ങളും സ്ക്രീൻഷോട്ടും വിജയകരമായി സമർപ്പിച്ചു!',
        ]);
    }

    protected function renderHomepage(): View
    {
        $liveFestMode = FestivalSetting::get('live_fest_mode', '1') === '1';

        Cache::forget('public_home_data');

        $groups = Group::orderBy('rank_cache', 'asc')
            ->orderByDesc('points_cache')
            ->withCount(['students', 'entries'])
            ->get();

        $stages = Stage::with(['currentProgram.category', 'nextProgram.category'])->get();

        $latestResults = Result::where('status', 'published')
            ->with([
                'program.category',
                'firstEntry.student.group',
                'secondEntry.student.group',
                'thirdEntry.student.group',
            ])
            ->latest('published_at')
            ->take(6)
            ->get();

        $featuredPrograms = Program::with(['category', 'stage', 'schedule'])
            ->whereIn('status', ['upcoming', 'in_progress'])
            ->take(8)
            ->get();

        $announcements = Announcement::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('target_role')->orWhere('target_role', 'all');
            })
            ->latest()
            ->take(5)
            ->get();

        $latestNews = News::where('status', 'published')
            ->latest('published_at')
            ->take(3)
            ->get();

        $galleryPreview = GalleryItem::latest()
            ->take(6)
            ->get();

        $liveVideo = VideoItem::where('is_live', true)->first();
        $featuredVideo = $liveVideo
            ?? VideoItem::where('is_featured', true)->first()
            ?? VideoItem::latest()->first();
        $highlightVideos = VideoItem::where('id', '!=', $featuredVideo?->id ?? 0)
            ->latest()
            ->take(4)
            ->get();

        $categories = ProgramCategory::withCount('programs')->get();

        $dbZones = Zone::orderBy('display_order')->get();
        $zones = [];
        foreach ($dbZones as $z) {
            $zones[$z->name] = [
                'name' => $z->name,
                'sub' => $z->sub_text,
                'classes' => $z->classes,
                'color' => $z->color_hex ?: '#be1e2d',
                'programs_count' => Program::where('zone_id', $z->id)->orWhere('eligibility', $z->name)->count(),
                'students_count' => Student::where('zone_id', $z->id)->orWhere('category', $z->name)->count(),
            ];
        }

        $stats = [
            'programs' => Program::count(),
            'students' => Student::count(),
            'groups' => $groups->count(),
            'stages' => $stages->count(),
            'zones' => count($zones),
        ];

        $totalProgramsCount = $stats['programs'] ?? Program::count();
        $publishedResultsCount = Result::where('status', 'published')->count();
        $isFullResultsPublished = ($totalProgramsCount > 0 && $publishedResultsCount >= $totalProgramsCount);

        return view('public.home', compact(
            'liveFestMode',
            'groups',
            'stages',
            'latestResults',
            'featuredPrograms',
            'announcements',
            'latestNews',
            'galleryPreview',
            'featuredVideo',
            'highlightVideos',
            'categories',
            'zones',
            'stats',
            'isFullResultsPublished'
        ));
    }
}
