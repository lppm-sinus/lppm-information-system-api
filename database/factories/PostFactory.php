<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Page;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'container' => $this->faker->paragraphs(3, true),
            'author_id' => User::factory(),
            'page_id' => Page::factory(),
            'category_id' => Category::factory(),
            'status' => $this->faker->randomElement(['draft', 'published']),
        ];
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
            ];
        });
    }

    public function withImage()
    {
        return $this->state(function (array $attributes) {
            return [
                'image_url' => 'public/images/' . $this->faker->uuid . '.jpg',
            ];
        });
    }

    public function withFile()
    {
        return $this->state(function (array $attributes) {
            return [
                'file_url' => 'public/files/' . $this->faker->uuid . '.pdf',
            ];
        });
    }
}
