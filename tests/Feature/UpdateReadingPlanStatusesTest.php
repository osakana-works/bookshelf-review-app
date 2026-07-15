<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateReadingPlanStatusesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3-9-1: target_dateが3日後の計画に、3日前通知が送信される
     */
    public function test_sends_three_days_before_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('reading-plans:update-statuses');

        Notification::assertSentTo($user, ReadingPlanReminder::class, function ($notification) use ($plan) {
            return $notification->toArray($notification)['reading_plan_id'] === $plan->id
                && $notification->toArray($notification)['timing'] === 'three_days_before';
        });
    }

    /**
     * 3-9-2: target_dateが当日の計画に、当日通知が送信される
     */
    public function test_sends_on_due_date_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:update-statuses');

        Notification::assertSentTo($user, ReadingPlanReminder::class, function ($notification) use ($plan) {
            return $notification->toArray($notification)['reading_plan_id'] === $plan->id
                && $notification->toArray($notification)['timing'] === 'on_due_date';
        });
    }

    /**
     * 3-9-3: target_dateが3日前(過去)の計画に、3日後通知が送信される
     */
    public function test_sends_three_days_after_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->overdue()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->subDays(3),
        ]);

        $this->artisan('reading-plans:update-statuses');

        Notification::assertSentTo($user, ReadingPlanReminder::class, function ($notification) use ($plan) {
            return $notification->toArray($notification)['reading_plan_id'] === $plan->id
                && $notification->toArray($notification)['timing'] === 'three_days_after';
        });
    }

    /**
     * 3-9-4: 既に送信済みの通知は再送信されない
     */
    public function test_does_not_resend_already_sent_notification(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::today(),
        ]);
        $user->notify(new ReadingPlanReminder($plan, 'on_due_date'));

        Notification::fake();

        $this->artisan('reading-plans:update-statuses');

        Notification::assertNotSentTo($user, ReadingPlanReminder::class);
    }

    /**
     * 3-9-5: Completedの計画には通知が送信されない
     */
    public function test_does_not_send_notification_to_completed_plan(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:update-statuses');

        Notification::assertNothingSent();
    }

    /**
     * 3-10-1: target_dateが過去のInProgressの計画が、Overdueに変わる
     */
    public function test_overdue_status_is_applied_to_past_in_progress_plans(): void
    {
        $plan = ReadingPlan::factory()->create([
            'target_date' => Carbon::today()->subDays(1),
        ]);

        $this->artisan('reading-plans:update-statuses');

        $this->assertEquals(ReadingPlanStatus::Overdue, $plan->fresh()->status);
    }

    /**
     * 3-10-2: target_dateが過去でも、Completedの計画はOverdueに変わらない
     */
    public function test_completed_plans_are_not_changed_to_overdue(): void
    {
        $plan = ReadingPlan::factory()->completed()->create();

        $this->artisan('reading-plans:update-statuses');

        $this->assertEquals(ReadingPlanStatus::Completed, $plan->fresh()->status);
    }

    /**
     * 3-10-3: target_dateが未来の計画は変化しない
     */
    public function test_future_plans_remain_unchanged(): void
    {
        $plan = ReadingPlan::factory()->create([
            'target_date' => Carbon::today()->addDays(5),
        ]);

        $this->artisan('reading-plans:update-statuses');

        $this->assertEquals(ReadingPlanStatus::InProgress, $plan->fresh()->status);
    }
}
