<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dialog extends Model {
    public $timestamps = false;
    protected $fillable = ['agent_id','run_id','role','content','tokens','created_at'];
    protected $casts = ['created_at' => 'datetime'];
    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }
    public function run(): BelongsTo { return $this->belongsTo(Run::class); }
}
