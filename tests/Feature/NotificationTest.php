<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3-11-1: 認証済みユーザーは自分の通知一覧を閲覧できる
     */
    public function test_authenticated_user_can_view_notification_list(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanReminder($readingPlan, 'three_days_before'));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
    }

    /**
     * 3-11-2: 未認証ユーザーはログイン画面にリダイレクトされる
     */
    public function test_guest_is_redirected_to_login_from_notification_index(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 3-11-3: 他人の通知は表示されない
     */
    public function test_other_users_notifications_are_not_displayed(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $otherUser->id]);
        $otherUser->notify(new ReadingPlanReminder($readingPlan, 'three_days_before'));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('通知はありません');
    }

    /**
     * 3-11-4: 「既読にする」操作で、read_atが記録される
     */
    public function test_marking_notification_as_read_records_read_at(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanReminder($readingPlan, 'three_days_before'));
        $notification = $user->notifications()->first();

        $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * 3-11-5: 既読化後、通知一覧にリダイレクトされる
     */
    public function test_marking_as_read_redirects_to_notification_index(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanReminder($readingPlan, 'three_days_before'));
        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('notifications.index'));
    }

    /**
     * 3-11-6: 他人の通知を既読にしようとすると404になる
     */
    public function test_cannot_mark_other_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create(['user_id' => $otherUser->id]);
        $otherUser->notify(new ReadingPlanReminder($readingPlan, 'three_days_before'));
        $notification = $otherUser->notifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertStatus(404);
    }
}
