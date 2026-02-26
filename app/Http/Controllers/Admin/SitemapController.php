<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tool;
use App\Models\Category;
use Spatie\Sitemap\Sitemap;
use App\Http\Controllers\Controller;
use Spatie\Sitemap\SitemapGenerator;
use Illuminate\Support\Facades\Artisan;

class SitemapController extends Controller
{
    public function generate()
    {
        try {
            // Artisan::call('optimize:clear');
            Sitemap::create()
                ->add(config('app.url'))
                ->add(route('login'))
                ->add(route('register'))
                ->add(Tool::active()->with('translations')->get())
                ->add(Page::published()->with('translations')->get())
                ->add(Post::published()->with('translations')->get())
                ->add(Category::active()->with('translations')->get())
                ->add(Tag::active()->with('translations')->get())
                ->add(route('ads.remove'))
                ->add(route('plans.list'))
                ->add(route('contact'))
                ->writeToFile(public_path('sitemap.xml'));

            // Artisan::call('optimize');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => __('settings.sitemapGeneratedSuccessfully')]);
    }
}
