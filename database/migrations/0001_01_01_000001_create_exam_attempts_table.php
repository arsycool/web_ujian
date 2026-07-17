<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('session_id'); // 0..3 = Sesi 1..4
            $table->string('session_title');
            $table->string('status')->default('in_progress'); // in_progress | finished
            $table->unsignedInteger('current_index')->default(0);
            $table->unsignedInteger('score')->default(0);          // jumlah benar (sementara berjalan)
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedTinyInteger('raw_score')->default(0);  // nilai 0-100
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('answers')->nullable(); // [{q, selected, correct}]
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
