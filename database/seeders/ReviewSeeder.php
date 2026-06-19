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

        $reviews = [
            // 吾輩は猫である
            ['user' => 'yamada@example.com', 'book' => '9784101010014', 'rating' => 5, 'comment' => '猫の視点から人間社会を鋭く風刺した傑作。ユーモアの中に深い洞察がある。'],
            ['user' => 'suzuki@example.com', 'book' => '9784101010014', 'rating' => 4, 'comment' => '独特の文体が心地よく、読み進めるうちに引き込まれた。'],
            ['user' => 'tanaka@example.com', 'book' => '9784101010014', 'rating' => 4, 'comment' => '明治時代の雰囲気が伝わってくる名作。何度読んでも新しい発見がある。'],

            // 人を動かす
            ['user' => 'sato@example.com', 'book' => '9784422100524', 'rating' => 5, 'comment' => '人間関係に悩んでいるすべての人に読んでほしい一冊。実践的なアドバイスが満載。'],
            ['user' => 'takahashi@example.com', 'book' => '9784422100524', 'rating' => 5, 'comment' => '何十年も読み継がれてきた理由がわかる。普遍的な真理が詰まっている。'],
            ['user' => 'yamada@example.com', 'book' => '9784422100524', 'rating' => 4, 'comment' => '具体的な事例が豊富でとても読みやすい。ビジネスだけでなく日常でも役立つ。'],

            // リーダブルコード
            ['user' => 'tanaka@example.com', 'book' => '9784873115658', 'rating' => 5, 'comment' => 'コードの書き方が根本から変わった。エンジニアなら必ず読むべき本。'],
            ['user' => 'suzuki@example.com', 'book' => '9784873115658', 'rating' => 4, 'comment' => '具体的なコード例が豊富で実践しやすい。チーム開発に特に役立つ。'],

            // 7つの習慣
            ['user' => 'sato@example.com', 'book' => '9784863940246', 'rating' => 5, 'comment' => '人生を変えた一冊。何度も読み返している。'],
            ['user' => 'takahashi@example.com', 'book' => '9784863940246', 'rating' => 4, 'comment' => '内容が濃くて読み応えがある。少しずつ実践していきたい。'],
            ['user' => 'tanaka@example.com', 'book' => '9784863940246', 'rating' => 4, 'comment' => '自己啓発本の中でも特に体系的にまとまっている。'],

            // 坊っちゃん
            ['user' => 'yamada@example.com', 'book' => '9784101010021', 'rating' => 5, 'comment' => 'テンポよく読めてとても面白い。漱石作品の中で一番好き。'],
            ['user' => 'suzuki@example.com', 'book' => '9784101010021', 'rating' => 4, 'comment' => '主人公の真っ直ぐな性格が気持ちいい。痛快な青春小説。'],
            ['user' => 'sato@example.com', 'book' => '9784101010021', 'rating' => 3, 'comment' => '古典としての価値は高いが、現代人には少し読みにくい部分もある。'],

            // サピエンス全史
            ['user' => 'takahashi@example.com', 'book' => '9784309226712', 'rating' => 5, 'comment' => '人類の歴史を俯瞰できる壮大な一冊。世界観が広がった。'],
            ['user' => 'yamada@example.com', 'book' => '9784309226712', 'rating' => 5, 'comment' => '歴史・科学・哲学が融合した知的興奮を味わえる。'],
            ['user' => 'tanaka@example.com', 'book' => '9784309226712', 'rating' => 4, 'comment' => '圧倒的な情報量だが読みやすい。知的好奇心が刺激される。'],

            // Clean Code
            ['user' => 'suzuki@example.com', 'book' => '9784048930598', 'rating' => 5, 'comment' => 'リーダブルコードと合わせて読むと効果抜群。プロのコードとは何かがわかる。'],
            ['user' => 'sato@example.com', 'book' => '9784048930598', 'rating' => 4, 'comment' => '厳しいけれど的確なアドバイスが多い。コードレビューの基準になる。'],

            // 嫌われる勇気
            ['user' => 'takahashi@example.com', 'book' => '9784478025819', 'rating' => 5, 'comment' => 'アドラー心理学がこんなにわかりやすく読めるとは。人生観が変わった。'],
            ['user' => 'yamada@example.com', 'book' => '9784478025819', 'rating' => 4, 'comment' => '対話形式で読みやすい。他者の目を気にしすぎていた自分に気づけた。'],
            ['user' => 'suzuki@example.com', 'book' => '9784478025819', 'rating' => 4, 'comment' => '共感できる部分が多く、自己肯定感が上がった気がする。'],

            // 火花
            ['user' => 'tanaka@example.com', 'book' => '9784163902302', 'rating' => 4, 'comment' => '芸人の世界をリアルに描いた作品。又吉さんの文章力に驚いた。'],
            ['user' => 'sato@example.com', 'book' => '9784163902302', 'rating' => 3, 'comment' => '芥川賞受賞作として話題になったが、好みが分かれそう。'],
            ['user' => 'takahashi@example.com', 'book' => '9784163902302', 'rating' => 4, 'comment' => '夢を追う人間の葛藤がリアルに伝わってくる。'],

            // FACTFULNESS
            ['user' => 'yamada@example.com', 'book' => '9784822289607', 'rating' => 5, 'comment' => 'データで世界を見る重要性を教えてくれる。思い込みがいかに危険かわかった。'],
            ['user' => 'suzuki@example.com', 'book' => '9784822289607', 'rating' => 5, 'comment' => '世界はこんなに良くなっているのかと驚いた。ポジティブになれる一冊。'],
            ['user' => 'tanaka@example.com', 'book' => '9784822289607', 'rating' => 4, 'comment' => 'ニュースの見方が変わった。データリテラシーを高めたい人に最適。'],

            // コンテナ物語
            ['user' => 'sato@example.com', 'book' => '9784822251468', 'rating' => 4, 'comment' => 'コンテナという発明がこれほど世界を変えたとは知らなかった。物流の奥深さを感じた。'],
            ['user' => 'takahashi@example.com', 'book' => '9784822251468', 'rating' => 3, 'comment' => '専門的な内容が多いが、経済史として面白い視点を提供してくれる。'],
            ['user' => 'yamada@example.com', 'book' => '9784822251468', 'rating' => 4, 'comment' => 'グローバル経済の裏側を知れる良書。ビジネスマンにおすすめ。'],
        ];

        foreach ($reviews as $reviewData) {
            $user = $users->firstWhere('email', $reviewData['user']);
            $book = $books->firstWhere('isbn', $reviewData['book']);

            Review::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'],
            ]);
        }
    }
}
