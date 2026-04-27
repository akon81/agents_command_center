<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Run extends Model {
    protected $fillable = ['task_id','agent_id','pid','status','exit_code','progress','current_action','started_at','finished_at','duration_ms','workspace_path','process_tree'];
    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime', 'process_tree' => 'array'];
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }
    public function logs(): HasMany { return $this->hasMany(Log::class); }
    public function dialogs(): HasMany { return $this->hasMany(Dialog::class); }
}
