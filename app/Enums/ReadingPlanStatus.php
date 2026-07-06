<?php

namespace App\Enums;

enum ReadingPlanStatus: int
{
    case NotStarted = 0;
    case InProgress = 1;
    case Completed = 2;
    case Overdue = 3;

    /**
     * 状態の日本語ラベルを取得する
     */
    public function label(): string
    {
        return match ($this) {
            self::NotStarted => '未着手',
            self::InProgress => '進行中',
            self::Completed => '読了',
            self::Overdue => '期限切れ',
        };
    }

    /**
     * 状態に応じたバッジ用CSSクラスを取得する
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::NotStarted => 'bg-gray-100 text-gray-800',
            self::InProgress => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-red-100 text-red-800',
        };
    }
}
