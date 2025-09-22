<?php

namespace App\Http\Controllers;

use App\Models\HeaderSetting;
use Illuminate\Http\Request;
use App\Models\PostBlog;

class FrontPostBlogController extends Controller
{

    public function index(){
        return view('frontend.index');
    }
    
    public function show($slug)
    {
           $header = HeaderSetting::first() ?? (object) [
            'site_name'     => 'Lampegan Motor',
            'logo'          => null,
            'instagram_url' => 'https://www.instagram.com/lampeganmotorbdg',
            'tiktok_url'    => 'https://www.tiktok.com/@lampeganmotorbdg',
        ];


        $blog = PostBlog::with('category')->where('slug', $slug)->firstOrFail();

        $blog->increment('views');

          $relatedBlogs = PostBlog::where('category_id', $blog->category_id)
                    ->where('id', '!=', $blog->id)
                    ->where('is_published', true)
                    ->latest()
                    ->take(4)
                    ->get();

        return view('frontend.blog-show', compact('blog','header','relatedBlogs'));
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
