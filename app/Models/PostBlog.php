<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostBlog extends Model
{
    protected $table = 'posts_blog';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'cover_image',
        'excerpt',
        'content',
        'is_published',
    ];

    public function category()
    {
        return $this->belongsTo(CategoriesBlog::class, 'category_id');
    }
}

