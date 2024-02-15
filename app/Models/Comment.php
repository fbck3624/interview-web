<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'comment';

    protected $fillable = [
        'score',
        'comment',
    ];

    // 自定義欄位
    protected $appends = ['editable'];

    protected static function boot()
    {
        parent::boot();

        Gate::define('update-comment', function ($user, Comment $comment) {
            return $user->id === $comment->user_id;
        });

        Gate::define('delete-comment', function ($user, Comment $comment) {
            return $user->id === $comment->user_id;
        });

        // save前插入user_id
        static::saving(function ($comment) {
            $comment->user_id = Auth::user()->id;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // 定義欄位value
    public function getEditableAttribute()
    {
        return Auth::check() ? Auth::user()->id === $this->user_id : false;
    }
}
