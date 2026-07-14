<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ReadingPlan>
 */
class ReadingPlanFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => Carbon::today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ];
    }

    /**
     * 期限切れの状態にする
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReadingPlanStatus::Overdue,
            'target_date' => Carbon::today()->subDays(3),
        ]);
    }

    /**
     * 読了済みの状態にする
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReadingPlanStatus::Completed,
            'target_date' => Carbon::today()->subDays(10),
            'completed_at' => Carbon::today()->subDays(8),
        ]);
    }
}
