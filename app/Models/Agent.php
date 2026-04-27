<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model {
    protected $fillable = ['slug','name','description','model','layer','tools','file_path','color','icon','is_active'];
    protected $casts = ['tools' => 'array', 'is_active' => 'boolean'];
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function runs(): HasMany { return $this->hasMany(Run::class); }
    public function dialogs(): HasMany { return $this->hasMany(Dialog::class); }
}
