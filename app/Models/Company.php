<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'principal',
    ];

    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function sortCompany(string $column, string $order): Builder
    {
        return $this->query()->orderBy($column, $order);
    }
}
