<?php

namespace App\Http\Controllers;

use App\Models\Category;

class TestController extends Controller
{

    public function test()
    {
        abort(404);
        $tools = Category::query()
            ->active()
            ->tool()
            ->with('translations')
            ->with(['tools' => function ($query) {
                $query->active()->with('translations')->orderBy('display');
            }])
            ->orderBy('order')
            ->get();
        foreach ($tools as $category) {
            echo '<ul>';
            echo '<li><h3>' . $category->title . '</h3></li>';
            echo '<ul>';
            foreach ($category->tools as $tool) {
                $drivers = null;
                $instance = new $tool->class_name();
                if (method_exists($instance, 'getFileds')) {
                    $tmp = collect(collect($instance->getFileds()['fields'] ?? [])?->where('id', 'driver')->first()['options'] ?? [])->pluck('text')->implode(', ');
                    $drivers = ' Drivers: (' . $tmp . ')';
                }
                echo '<li><strong>' . $tool->name . '</strong>' . $drivers . '</li>';
            }
            echo '</ul>';
            echo '</ul>';
        }
    }
}
