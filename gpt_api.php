<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Libs, public API.
 *
 * NOTE: page type not included because there can not be any blocks in popups
 *
 * @package    report_test
 * @copyright  2023 Junichiro Sakashita
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//このソースコードは使わない

defined('MOODLE_INTERNAL') || die;

$result array();

 // APIキー
$apiKey = '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';

//openAI APIエンドポイント
$endpoint = 'https://api.openai.com/v1/chat/completions';

$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
);

$model = 'gpt4-turbo';

// リクエストのペイロード
$data = array(
    'model' => $model,
    'messages' => [
      [
        "role" => "system",
        "content" => "テキスト内容が、感想か質問か要望かラベル付与せよ。いずれでもない場合はその他と判定。その際、最も当てはまるラベル1つのみを付与。感想の数字ラベルを0、質問の数字ラベルを1、要望の数字ラベルを2、その他の数字ラベルを3として、読み込んだテキストに対して、対応する数字ラベルのみを付与する"
      ],
      [
        "role" => "system",
        "content" => "自分がなりたい・やってみたいことや個人的な苦労を述べている場合は0。疑問や解決・対処方法を聞いているものや、授業する側に尋ねているものは1。授業アンケートを分類しているので、授業をする側にして欲しいことや、改善やより詳しい説明を求めている内容を書いてある場合は2、「特になし」や「諸事情を伝える内容」などいずれにも該当しない場合は3"
      ],
      [
        "role" => "user",
        "content" => $req_question
      ]
    ],
    'max_tokens' => 50,
    'n' => 1,
    'temperature' => 0
  );

// cURLリクエストを初期化
$ch = curl_init();

// cURLオプションを設定
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// APIにリクエストを送信
$response = curl_exec($ch);

// cURLリクエストを閉じる
curl_close($ch);

// 応答を解析
$result = json_decode($response, true);

// 生成されたテキストを取得
$text = $result['choices'][0]['message']['content'];

echo json_encode($text, JSON_PRETTY_PRINT);