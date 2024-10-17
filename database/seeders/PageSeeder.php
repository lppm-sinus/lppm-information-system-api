<?php

namespace Database\Seeders;

use App\Models\Page;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::create([
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'link' => '/tentang-kami',
            'parent_id' => null
        ]);

        Page::create([
            'title' => 'Sejarah',
            'slug' => 'sejarah',
            'link' => '/tentang-kami/sejarah',
            'parent_id' => 1
        ]);

        Page::create([
            'title' => 'Program & Kebijakan',
            'slug' => SlugService::createSlug(Page::class, 'slug', 'Program & Kebijakan'),
            'link' => '/tentang-kami/' . SlugService::createSlug(Page::class, 'slug', 'Program & Kebijakan'),
            'parent_id' => 1
        ]);

        Page::create([
            'title' => 'Visi & Misi',
            'slug' => SlugService::createSlug(Page::class, 'slug', 'Visi & Misi'),
            'link' => '/tentang-kami/' . SlugService::createSlug(Page::class, 'slug', 'Visi & Misi'),
            'parent_id' => 1
        ]);

        Page::create([
            'title' => 'Struktur',
            'slug' => 'struktur',
            'link' => '/tentang-kami/struktur',
            'parent_id' => 1
        ]);
    }
}
