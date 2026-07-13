<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateReadingPlanStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '期日超過の読書計画をOverdueに変更し、リマインダー通知を送信する';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->markOverduePlans();
        $this->sendReminders();
    }

    /**
     * 期日を過ぎたInProgressの計画をOverdueに変更する
     */
    private function markOverduePlans(): void
    {
        ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->where('target_date', '<', Carbon::today())
            ->update(['status' => ReadingPlanStatus::Overdue]);
    }

    /**
     * 期日の3日前・当日・3日後に該当する計画にリマインダー通知を送信する
     */
    private function sendReminders(): void
    {
        $timings = [
            'three_days_before' => Carbon::today()->addDays(3),
            'on_due_date' => Carbon::today(),
            'three_days_after' => Carbon::today()->subDays(3),
        ];

        foreach ($timings as $timing => $targetDate) {
            ReadingPlan::where('status', '!=', ReadingPlanStatus::Completed)
                ->whereDate('target_date', $targetDate)
                ->with('user', 'book')
                ->get()
                ->each(function (ReadingPlan $plan) use ($timing) {
                    $alreadySent = DB::table('notifications')
                        ->where('notifiable_id', $plan->user_id)
                        ->whereJsonContains('data->reading_plan_id', $plan->id)
                        ->whereJsonContains('data->timing', $timing)
                        ->exists();

                    if (! $alreadySent) {
                        $plan->user->notify(new ReadingPlanReminder($plan, $timing));
                    }
                });
        }
    }
}
