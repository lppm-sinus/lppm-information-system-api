<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::create([
            'title' => 'Tentang Kami',
            'container' => fake()->sentence(100),
            'author_id' => 1,
            'page_id' => 1,
            'category_id' => 1,
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Sejarah',
            'container' => fake()->sentence(100),
            'author_id' => 1,
            'page_id' => 2,
            'category_id' => 1,
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Program & Kebijakan',
            'container' => fake()->sentence(100),
            'author_id' => 1,
            'page_id' => 3,
            'category_id' => 1,
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Visi',
            'container' => fake()->sentence(100),
            'author_id' => 1,
            'page_id' => 4,
            'category_id' => 1,
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Misi',
            'container' => fake()->sentence(100),
            'author_id' => 1,
            'page_id' => 4,
            'category_id' => 1,
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Struktur',
            'container' => fake()->sentence(100),
            'author_id' => 2,
            'page_id' => 5,
            'category_id' => 2,
            'image_url' => 'public/images/1727929205_layanan.png',
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Jurnal Ilmiah Sinus',
            'container' => fake()->sentence(100),
            'author_id' => 2,
            'page_id' => 5,
            'category_id' => 3,
            'image_url' => 'public/images/1727929205_layanan.png',
            'link_url' => 'https://www.facebook.com',
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Jurnal TIKomSin',
            'container' => fake()->sentence(100),
            'author_id' => 2,
            'page_id' => 5,
            'category_id' => 3,
            'image_url' => 'public/images/1727929205_layanan.png',
            'link_url' => 'https://www.facebook.com',
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Jurnal No Image',
            'container' => fake()->sentence(100),
            'author_id' => 2,
            'page_id' => 5,
            'category_id' => 3,
            'link_url' => 'https://www.facebook.com',
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Test',
            'container' => fake()->sentence(100),
            'author_id' => 2,
            'page_id' => 5,
            'category_id' => 4,
            'file_url' => 'public/file/1727929205_layanan.pdf',
            'status' => 'published'
        ]);

        Post::create([
            'title' => 'Test File 2',
            'container' => fake()->sentence(100),
            'author_id' => 2,
            'page_id' => 5,
            'category_id' => 4,
            'file_url' => 'public/file/1727929206_layanan.pdf',
            'status' => 'published'
        ]);
    }
}
