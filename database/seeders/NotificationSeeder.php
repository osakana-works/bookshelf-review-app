<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * 通知の初期データを投入する（送信済み通知の再現）
     */
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->firstOrFail();
        $book = Book::where('isbn', '9784822289607')->firstOrFail();

        $plan = ReadingPlan::where('user_id', $yamada->id)
            ->where('book_id', $book->id)
            ->firstOrFail();

        $timings = [
            'three_days_before' => Carbon::today()->subDays(13),
            'on_due_date' => Carbon::today()->subDays(10),
        ];

        foreach ($timings as $timing => $sentAt) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\ReadingPlanReminder',
                'notifiable_type' => User::class,
                'notifiable_id' => $yamada->id,
                'data' => json_encode([
                    'title' => 'リマインダー',
                    'body' => "「{$book->title}」の読書期限が近づいています。",
                    'timing' => $timing,
                    'reading_plan_id' => $plan->id,
                ]),
                'read_at' => null,
                'created_at' => $sentAt,
                'updated_at' => $sentAt,
            ]);
        }
    }
}
