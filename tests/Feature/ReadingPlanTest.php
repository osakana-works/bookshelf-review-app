<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3-7-1 認証済みユーザーは読書計画一覧を閲覧できる
     */
    public function test_authenticated_user_can_view_reading_plan_list(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('reading-plans.index'));
        $response->assertStatus(200);
    }

    /**
     * 3-7-2 未認証ユーザーはログイン画面にリダイレクトされる
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('reading-plans.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 3-7-3 状態(InProgress)で絞り込むと、InProgressの計画のみ表示される
     */
    public function test_filter_reading_plans_by_in_progress_status(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);
        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', ['status' => ReadingPlanStatus::InProgress]));
        $response->assertStatus(200);
        $response->assertSee($readingPlan->book->title);
        $response->assertDontSee($readingPlan2->book->title);
    }

    /**
     * 3-7-4 状態(Overdue)で絞り込むと、Overdueの計画のみ表示される
     */
    public function test_filter_reading_plans_by_overdue_status(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Overdue,
        ]);
        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', ['status' => ReadingPlanStatus::Overdue]));
        $response->assertStatus(200);
        $response->assertSee($readingPlan->book->title);
        $response->assertDontSee($readingPlan2->book->title);
    }

    /**
     * 3-7-5 状態(Completed)で絞り込むと、Completedの計画のみ表示される
     */
    public function test_filter_reading_plans_by_completed_status(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);
        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', ['status' => ReadingPlanStatus::Completed]));
        $response->assertStatus(200);
        $response->assertSee($readingPlan->book->title);
        $response->assertDontSee($readingPlan2->book->title);
    }

    /**
     * 3-7-6 状態を指定しない(すべて)場合、全状態の計画が表示される
     */
    public function test_filter_reading_plans_without_status(): void
    {
        $user = User::factory()->create();
        $readingPlan1 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);
        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index'));
        $response->assertStatus(200);
        $response->assertSee($readingPlan1->book->title);
        $response->assertSee($readingPlan2->book->title);
    }

    /**
     * 3-7-7 読書計画を新規作成できる(初期状態はInProgress)
     */
    public function test_create_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);
    }

    /**
     * 3-7-8 同じ書籍で2件目の計画を作ろうとするとバリデーションエラーになる
     */
    public function test_cannot_create_duplicate_reading_plan_for_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);
        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertSessionHasErrors('book_id');
    }

    /**
     * 3-7-9 過去日付で計画を作成しようとするとバリデーションエラーになる
     */
    public function test_cannot_create_reading_plan_with_past_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    /**
     * 3-7-10 所有者は自分の計画を編集画面にアクセスできる
     */
    public function test_owner_can_access_edit_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));
        $response->assertStatus(200);
    }

    /**
     * 3-7-11 所有者でないユーザーは他人の計画の編集画面にアクセスできない(403)
     */
    public function test_non_owner_cannot_access_edit_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));
        $response->assertStatus(403);
    }

    /**
     * 3-7-12 Completedの計画は編集できない
     */
    public function test_cannot_edit_completed_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));
        $response->assertStatus(403);
    }

    /**
     * 3-7-13 所有者は自分の計画を削除できる
     */
    public function test_owner_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /**
     * 3-7-14 所有者でないユーザーは他人の計画を削除できない(403)
     */
    public function test_non_owner_cannot_delete_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);
        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));
        $response->assertStatus(403);
    }

    /**
     * 3-7-15 Overdueの計画も編集・削除できる
     */
    public function test_overdue_reading_plan_can_be_edited_and_deleted(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Overdue,
        ]);

        $responseEdit = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));
        $responseEdit->assertStatus(200);

        $responseDelete = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));
        $responseDelete->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /**
     * 3-7-16 「読了する」操作で、ステータスがCompletedになり、completed_atが記録される
     */
    public function test_complete_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));
        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed,
        ]);
        $this->assertNotNull(ReadingPlan::find($readingPlan->id)->completed_at);
    }

    /**
     * 3-7-17 Completedの計画は再度「読了する」操作ができない
     */
    public function test_cannot_complete_already_completed_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));
        $response->assertStatus(403);
    }

    /**
     * 3-7-18 所有者でないユーザーは他人の計画を読了できない(403)
     */
    public function test_non_owner_cannot_complete_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));
        $response->assertStatus(403);
    }

    /**
     * 3-8-1 target_dateを未来の日付に変更できる
     */
    public function test_update_target_date_of_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $newTargetDate = now()->addDays(10)->toDateString();
        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => $newTargetDate,
        ]);
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertEquals($newTargetDate, $readingPlan->fresh()->target_date->toDateString());
    }

    /**
     * 3-8-2 target_dateを過去の日付に変更しようとするとバリデーションエラーになる
     */
    public function test_cannot_update_target_date_to_past(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->subDay()->toDateString(),
        ]);
        $response->assertSessionHasErrors('target_date');
    }

    /**
     * 3-8-3 book_idは変更できない(送信しても無視される)
     */
    public function test_cannot_change_book_id_of_reading_plan(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);
        $newBook = Book::factory()->create();

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'book_id' => $newBook->id,
            'target_date' => now()->addDays(10)->toDateString(),
        ]);
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'book_id' => $readingPlan->book_id, // book_idは変更されていないことを確認
        ]);
    }
}
