<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model {
    protected $fillable = ['agent_id','title','prompt','status','priority','started_at','finished_at'];
    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }
    public function runs(): HasMany { return $this->hasMany(Run::class); }
}
