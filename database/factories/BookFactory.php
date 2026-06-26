<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->numerify('9784#########'),
            'published_date' => fake()->date(),
            'description' => fake()->paragraph(),
            'image_url' => fake()->imageUrl(),
            'user_id' => User::factory(),
        ];
    }
}
