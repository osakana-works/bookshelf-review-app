<?php

return [
    'required' => ':attributeは必須です。',
    'email' => ':attributeは有効なメールアドレス形式で入力してください。',
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
    ],
    'confirmed' => ':attributeと確認用の値が一致しません。',
    'unique' => 'この:attributeはすでに使用されています。',

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
    ],
];
