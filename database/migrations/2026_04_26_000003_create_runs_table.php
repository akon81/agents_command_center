<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->integer('pid')->nullable();
            $table->string('status')->default('pending');
            $table->integer('exit_code')->nullable();
            $table->tinyInteger('progress')->nullable();
            $table->string('current_action', 255)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('workspace_path')->nullable();
            $table->json('process_tree')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('runs'); }
};
