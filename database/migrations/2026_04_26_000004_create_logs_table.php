<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained()->cascadeOnDelete();
            $table->string('stream')->default('stdout');
            $table->text('content');
            $table->bigInteger('seq');
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->nullable();
            $table->index(['run_id', 'seq']);
            $table->index('occurred_at');
        });
    }
    public function down(): void { Schema::dropIfExists('logs'); }
};
