<?php

namespace App\Models;

use App\Services\PointCalculationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Group extends Model
{
    use HasFactory;

    public const PACTO = 'PACTO';

    public const YUGO = 'YUGO';

    public const CONCO = 'CONCO';

    public const LUMO = 'LUMO';

    public const UNIO = 'UNIO';

    public const GROUPS = [
        'PACTO_HIKMIC' => [
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'color_hex' => '#2E3192',
        ],
        'YUGO_RUSHDIC' => [
            'name' => 'Yugo Rushdic',
            'code' => 'YUGO',
            'color_hex' => '#AD1E56',
        ],
        'CONCO_MAJDIC' => [
            'name' => 'Conco Majdic',
            'code' => 'CONCO',
            'color_hex' => '#F8E709',
        ],
        'LUMO_FIKRIC' => [
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'color_hex' => '#56286B',
        ],
        'UNIO_HILMIC' => [
            'name' => 'Unio Hilmic',
            'code' => 'UNIO',
            'color_hex' => '#7F1518',
        ],
    ];

    public static function getColorForCode(?string $code): string
    {
        $colors = [
            'PACTO' => '#2E3192',
            'YUGO' => '#AD1E56',
            'CONCO' => '#F8E709',
            'LUMO' => '#56286B',
            'UNIO' => '#7F1518',
        ];

        return $colors[strtoupper(trim((string) $code))] ?? '#2E3192';
    }

    protected $fillable = [
        'name',
        'code',
        'slug',
        'logo_url',
        'color_hex',
        'leader_id',
        'manager_name',
        'manager_contact',
        'name_in_results',
        'name_in_certificates',
        'admin_username',
        'admin_password',
        'assistant_managers',
        'points_cache',
        'rank_cache',
        'public_points_cache',
        'public_rank_cache',
    ];

    protected function casts(): array
    {
        return [
            'assistant_managers' => 'array',
            'points_cache' => 'integer',
            'rank_cache' => 'integer',
            'public_points_cache' => 'integer',
            'public_rank_cache' => 'integer',
        ];
    }

    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (! Schema::hasTable('groups')) {
                return;
            }

            if (! Schema::hasColumn('groups', 'public_points_cache')) {
                Schema::table('groups', function (Blueprint $table) {
                    $table->integer('public_points_cache')->default(0)->after('points_cache');
                });
            }

            if (! Schema::hasColumn('groups', 'public_rank_cache')) {
                Schema::table('groups', function (Blueprint $table) {
                    $table->integer('public_rank_cache')->default(1)->after('rank_cache');
                });
            }

            $checked = true;
        } catch (\Throwable $e) {
            // Graceful fallback
        }
    }

    /**
     * Get the officially released public team standings.
     */
    public static function getPublicStandings(): Collection
    {
        self::ensureSchema();

        return self::orderBy('public_rank_cache', 'asc')
            ->orderByDesc('public_points_cache')
            ->orderBy('id', 'asc')
            ->withCount(['students', 'entries'])
            ->get();
    }

    /**
     * Publish / release the public team standings to match the current live points.
     */
    public static function publishStandings(?string $customLabel = null): int
    {
        self::ensureSchema();

        // 1. Ensure live points are completely recalculated
        app(PointCalculationService::class)->recalculateAllPoints();

        // 2. Count total published results
        $publishedResultsCount = Result::where('status', 'published')->count();

        // 3. Determine milestone label if not given
        $label = $customLabel ?: ($publishedResultsCount > 0
            ? "After {$publishedResultsCount} Results"
            : 'Initial Standings');

        // 4. Copy live points_cache & rank_cache to public_points_cache & public_rank_cache
        $sortedGroups = self::orderByDesc('points_cache')->orderBy('name')->get();
        $rank = 1;
        foreach ($sortedGroups as $grp) {
            $grp->update([
                'public_points_cache' => (int) $grp->points_cache,
                'public_rank_cache' => $rank++,
            ]);
        }

        FestivalSetting::set('public_standings_results_count', (string) $publishedResultsCount);
        FestivalSetting::set('public_standings_label', $label);
        FestivalSetting::set('public_standings_updated_at', now()->toIso8601String());

        // Invalidate all public standings caches
        Cache::forget('public_api_standings');
        Cache::forget('public_home_data');
        Cache::forget('public_groups_list');
        Cache::forget('public_results_groups');

        return $publishedResultsCount;
    }

    /**
     * Check if a milestone was reached upon publishing a result and update public standings if matching.
     */
    public static function checkAndApplyMilestone(): bool
    {
        self::ensureSchema();

        $mode = FestivalSetting::get('public_standings_mode', 'milestone');
        if ($mode !== 'milestone') {
            return false;
        }

        $milestonesStr = FestivalSetting::get('public_standings_milestones', '5,10,15,20,25,30,35,40');
        $milestones = array_map('intval', array_filter(explode(',', $milestonesStr)));

        $publishedResultsCount = Result::where('status', 'published')->count();
        $lastPublishedAtCount = (int) FestivalSetting::get('public_standings_results_count', '0');

        // Check if current published count hits one of the configured milestones and hasn't been queued/published yet
        if (in_array($publishedResultsCount, $milestones, true) && $publishedResultsCount !== $lastPublishedAtCount) {
            $alreadyExists = StandingAnnouncement::where('results_count', $publishedResultsCount)->exists();
            if (! $alreadyExists) {
                StandingAnnouncement::createFromCurrentStandings("Standings after {$publishedResultsCount} Results", auth()->id());
            }

            return true;
        }

        return false;
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function fundReceipts(): HasMany
    {
        return $this->hasMany(FundReceipt::class, 'group_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProgramEntry::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }
}
