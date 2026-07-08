<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            1 => [
                '期待していた内容とは違い、残念な読書体験でした。',
                '正直、最後まで読むのが辛かったです。',
                '自分には合わなかったようです。',
            ],
            2 => [
                '悪くはないが、特に印象に残る部分もなかった。',
                'もう少し工夫が欲しいと感じました。',
                '期待していたほどではありませんでした。',
            ],
            3 => [
                '普通に読めましたが、特別な感動はありませんでした。',
                '可もなく不可もなく、といった内容でした。',
                '悪くはないが、人には積極的には勧めにくい。',
            ],
            4 => [
                '読み応えがあり、満足のいく内容でした。',
                '学びも多く、読んでよかったと思います。',
                'テンポよく読み進められる良い本でした。',
            ],
            5 => [
                '文句なしの傑作。多くの人におすすめしたい一冊です。',
                '期待以上の内容で、深く感動しました。',
                '何度でも読み返したくなる素晴らしい本でした。',
            ],
        ];

        foreach ($books as $book) {
            $reviewerCount = rand(2, 4);
            $reviewers = $users->random(min($reviewerCount, $users->count()));

            foreach ($reviewers as $user) {
                $score = rand(1, 5);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $score,
                    'comment' => $comments[$score][array_rand($comments[$score])],
                ]);
            }
        }
    }
}
