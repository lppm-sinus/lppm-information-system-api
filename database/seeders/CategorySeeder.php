<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Text',
            'slug' => 'text'
        ]);

        Category::create([
            'name' => 'Media',
            'slug' => 'media'
        ]);

        Category::create([
            'name' => 'Journal',
            'slug' => 'journal'
        ]);

        Category::create([
            'name' => 'File',
            'slug' => 'file'
        ]);
    }
}
