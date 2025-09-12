<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostBlog;

class FrontPostBlogController extends Controller
{
    public function show($slug)
    {
        $blog = PostBlog::with('category')->where('slug', $slug)->firstOrFail();
        return view('frontend.blog-show', compact('blog'));
    }

    public function category($id)
    {
        $blogs = PostBlog::with('category')
                    ->where('category_id', $id)
                    ->where('is_published', true)
                    ->latest()
                    ->get();
        return view('frontend.blog-category', compact('blogs'));
    }
}
