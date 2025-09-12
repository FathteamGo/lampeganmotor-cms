<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoriesBlog extends Model
{
    use HasFactory;

    protected $table = 'categories_blog'; // wajib karena nama tabelnya bukan "category_blogs"

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Auto set slug kalau belum ada
     */
    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Relasi ke posts blog
     */
    public function posts()
    {
        return $this->hasMany(PostBlog::class, 'category_id');
    }
}
