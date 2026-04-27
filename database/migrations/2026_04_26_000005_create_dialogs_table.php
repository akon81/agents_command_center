<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dialogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role');
            $table->text('content');
            $table->integer('tokens')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['agent_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('dialogs'); }
};
