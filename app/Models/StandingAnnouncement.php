<?php

namespace App\Models;

use App\Services\PointCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandingAnnouncement extends Model
{
    protected $fillable = [
        'results_count',
        'title',
        'script_text',
        'standings_data',
        'status',
        'sent_by_user_id',
        'announced_by_user_id',
        'published_by_user_id',
        'sent_at',
        'announced_at',
        'published_at',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'standings_data' => 'array',
        'sent_at' => 'datetime',
        'announced_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function announcedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'announced_by_user_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', 'ready');
    }

    public function scopeAnnounced(Builder $query): Builder
    {
        return $query->where('status', 'announced');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Create a new official Standing Announcement snapshot from current live points.
     */
    public static function createFromCurrentStandings(?string $customLabel = null, ?int $userId = null): self
    {
        // 1. Ensure latest live points are calculated
        app(PointCalculationService::class)->recalculateAllPoints();

        $resultsCount = Result::where('status', 'published')->count();
        $title = $customLabel ?: ($resultsCount > 0 ? "Standings after {$resultsCount} Results" : 'Initial Standings');

        $groups = Group::orderByDesc('points_cache')->orderBy('name')->get();

        $standingsData = [];
        $scriptLines = [];

        foreach ($groups as $idx => $grp) {
            $rank = $idx + 1;
            $standingsData[] = [
                'rank' => $rank,
                'group_id' => $grp->id,
                'name' => $grp->name,
                'code' => $grp->code,
                'color_hex' => $grp->color_hex ?? '#be1e2d',
                'points' => (int) $grp->points_cache,
            ];

            $malayalamRank = match ($rank) {
                1 => 'ഒന്നാം സ്ഥാനം',
                2 => 'രണ്ടാം സ്ഥാനം',
                3 => 'മൂന്നാം സ്ഥാനം',
                4 => 'നാലാം സ്ഥാനം',
                5 => 'അഞ്ചാം സ്ഥാനം',
                default => "{$rank}-ാം സ്ഥാനം",
            };

            $scriptLines[] = "{$malayalamRank}: {$grp->name} ({$grp->points_cache} പോയിന്റ്)";
        }

        $scriptIntro = $resultsCount > 0
            ? "ശ്രദ്ധിക്കുക, ക്വാഫ് 2026 കലാമേളയുടെ {$resultsCount} മത്സര ഫലങ്ങൾ പൂർത്തിയായപ്പോൾ ടീമുകളുടെ നിലവിലെ പോയിന്റ് നില പ്രഖ്യാപിക്കുന്നു: "
            : 'ശ്രദ്ധിക്കുക, ക്വാഫ് 2026 കലാമേളയുടെ ടീമുകളുടെ പ്രാരംഭ പോയിന്റ് നില പ്രഖ്യാപിക്കുന്നു: ';

        $scriptText = $scriptIntro.implode('. ', $scriptLines).'.';

        return self::create([
            'results_count' => $resultsCount,
            'title' => $title,
            'script_text' => $scriptText,
            'standings_data' => $standingsData,
            'status' => 'ready',
            'sent_by_user_id' => $userId,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark this standing announcement as announced on stage and route it to Media desk.
     */
    public function markAsAnnounced(?int $userId = null): bool
    {
        return $this->update([
            'status' => 'announced',
            'announced_by_user_id' => $userId,
            'announced_at' => now(),
        ]);
    }

    /**
     * Publish this announced standing publicly to the festival website.
     */
    public function publishPublicly(?int $userId = null): bool
    {
        // 1. Release public standings across all groups
        Group::publishStandings($this->title);

        // 2. Mark this announcement as published
        return $this->update([
            'status' => 'published',
            'published_by_user_id' => $userId,
            'published_at' => now(),
        ]);
    }
}
