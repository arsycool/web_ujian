<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'session_title',
        'status',        // 'in_progress' | 'finished'
        'current_index',
        'score',
        'correct_count',
        'wrong_count',
        'total_questions',
        'raw_score',
        'duration_seconds',
        'answers',        // JSON: [{q, selected, correct}, ...]
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'finished_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accuracy(): int
    {
        if (! $this->total_questions) {
            return 0;
        }

        return (int) round(($this->correct_count / $this->total_questions) * 100);
    }
}
