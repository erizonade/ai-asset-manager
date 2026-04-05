<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'schedule_type',
        'run_at',
        'days',
        'generate_prompts',
        'generate_assets',
        'asset_count',
        'is_active',
        'last_run_at',
    ];

    protected $casts = [
        'days' => 'array',
        'generate_prompts' => 'boolean',
        'generate_assets' => 'boolean',
        'is_active' => 'boolean',
        'run_at' => 'datetime:H:i',
        'last_run_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function shouldRun(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        $currentTime = $now->format('H:i');
        
        // Check if it's the right time
        if ($this->run_at && $currentTime !== $this->run_at->format('H:i')) {
            return false;
        }

        // Check day constraints
        if ($this->schedule_type === 'weekly' && $this->days) {
            $currentDay = $now->dayOfWeek; // 0 = Sunday, 1 = Monday
            if (!in_array($currentDay, $this->days)) {
                return false;
            }
        }

        return true;
    }
}