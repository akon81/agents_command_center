<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model {
    public $timestamps = false;
    protected $fillable = ['run_id','stream','content','seq','occurred_at','created_at'];
    protected $casts = ['occurred_at' => 'datetime', 'created_at' => 'datetime'];
    public function run(): BelongsTo { return $this->belongsTo(Run::class); }
}
