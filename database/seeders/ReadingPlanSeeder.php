<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * 読書計画の初期データを投入する
     */
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->firstOrFail();
        $suzuki = User::where('email', 'suzuki@example.com')->firstOrFail();
        $tanaka = User::where('email', 'tanaka@example.com')->firstOrFail();

        $bookByIsbn = fn (string $isbn) => Book::where('isbn', $isbn)->firstOrFail();

        $plans = [
            // 山田太郎：主要シナリオ
            ['user' => $yamada, 'book' => $bookByIsbn('9784422100524'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => Carbon::today()->addDays(5), 'completed_at' => null],
            ['user' => $yamada, 'book' => $bookByIsbn('9784873115658'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => Carbon::today()->addDays(3), 'completed_at' => null],
            ['user' => $yamada, 'book' => $bookByIsbn('9784101010021'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => Carbon::today(), 'completed_at' => null],
            ['user' => $yamada, 'book' => $bookByIsbn('9784048930598'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => Carbon::today()->subDays(1), 'completed_at' => null],
            ['user' => $yamada, 'book' => $bookByIsbn('9784478025819'), 'status' => ReadingPlanStatus::Overdue, 'target_date' => Carbon::today()->subDays(3), 'completed_at' => null],
            ['user' => $yamada, 'book' => $bookByIsbn('9784163902302'), 'status' => ReadingPlanStatus::Completed, 'target_date' => Carbon::today()->subDays(10), 'completed_at' => Carbon::today()->subDays(8)],
            ['user' => $yamada, 'book' => $bookByIsbn('9784822289607'), 'status' => ReadingPlanStatus::Overdue, 'target_date' => Carbon::today()->subDays(10), 'completed_at' => null],

            // 他ユーザー：認可テスト用
            ['user' => $suzuki, 'book' => $bookByIsbn('9784822251468'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => Carbon::today()->addDays(7), 'completed_at' => null],
            ['user' => $tanaka, 'book' => $bookByIsbn('9784101010014'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => Carbon::today()->addDays(14), 'completed_at' => null],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::firstOrCreate(
                ['user_id' => $plan['user']->id, 'book_id' => $plan['book']->id],
                [
                    'status' => $plan['status'],
                    'target_date' => $plan['target_date'],
                    'completed_at' => $plan['completed_at'],
                ]
            );
        }
    }
}
