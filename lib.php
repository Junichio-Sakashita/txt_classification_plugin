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

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @global stdClass $CFG
 * @global core_renderer $OUTPUT
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course     The course to object for the report
 * @param context         $context    The context of the course
 */


function report_test_get_greeting($user) {
    if ($user == null) {
        return get_string('greetinguser', 'report_greetings');
    }

    $country = $user->country;
    switch ($country) {
        case 'JP':
            $langstr = 'greetinguserjp';
            break;
        default:
            $langstr = 'greetingloggedinuser';
            break;
    }

    return get_string($langstr, 'report_greetings', fullname($user));
}

/*
function report_test_extend_navigation_course(navigation_node $frontpage) {
    $frontpage->add(
        get_string('pluginname', 'report_test'),
        new moodle_url('/report/test/index.php'),
        navigation_node::TYPE_CUSTOM,
    );
}
*/
function report_test_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/test:view', $context)) {
        $url = new moodle_url('/report/test/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_test'), $url, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('i/report', ''));
    }
}

//CSの回答データの前処理
function txt_preprocessing($text){
    //未解答判定
    $pattern = "/特になし[。.]?|ないです[。.]?|<未解答>|特にないです[。.]?|特に[は]?ありません[。.]?|とくにないです[。.]?|なし[。.]?|無し[。.]?/";
    //正規表現にマッチした場合は1が、マッチしない場合は0が返る
    $isBlank = preg_match($pattern, $text);
    if($isBlank == 1){
        return(NULL);
    }
    else{
        //未解答でない場合、次の処理に進む
        //htmlタグの削除
        $text = strip_tags($text, $allowed_tags = null);

        //複数行に渡る場合, 行を分割する
        // テキストが複数行にわたる場合、行を「。」で分割する
        $split_text = explode("。", $text);

        // 空の配列要素を削除
        $split_text = array_filter($split_text, function($value) { 
            return !is_null($value) && $value !== ''; 
        });

        // 分割されたテキストを配列として返す
        return array_values($split_text);

    }
}

function gpt_txtclassification($user_text, $apiKey, $model){
    // GPTのAPIを用いて文書分類を行う

    // 最大再試行回数を設定
    $maxRetries = 3;
    // 現在の再試行回数
    $retryCount = 0;
    // タイムアウトまでの時間（秒）
    $timeoutSeconds = 10;

    $endpoint = 'https://api.openai.com/v1/chat/completions';  // APIエンドポイント

    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    );

    //$model = 'gpt-4-1106-preview';  // モデルの選択
    //$model = $gpt_model;
    if($model == 'gpt-4-1106-preview'){
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
                    "role" => "system",
                    "content" => "例えば、「よろしくお願いします」・「ありがとうございま(す)した」・「(エラーの)内容が難しかった(分からなかった)」・「できた・分かった」などの内容は0に分類せよ"
                ],
                [
                    "role" => "user",
                    "content" => $user_text
                ]
            ],
            'max_tokens' => 50,
            'n' => 1,
            'temperature' => 0
        );

    }
    else{
        //FTモデル用
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
                    "content" => $user_text
                ]
            ],
            'max_tokens' => 50,
            'n' => 1,
            'temperature' => 0
        );
    }

    // cURLリクエストを初期化
    /*$ch = curl_init();

    // cURLオプションを設定
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // タイムアウト設定

    // APIにリクエストを送信
    $response = curl_exec($ch);

    // cURLエラーをチェック
    // 直近のエラーを取得
    // throw new Excwptionでエラー内容を投げる
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }

    // タイムアウトをチェック
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 0) {
        throw new Exception("GPT APIのタイムアウトエラー");
    }
    // cURLリクエストを閉じる
    curl_close($ch);

    // 応答を解析
    $result = json_decode($response, true);

    // API応答にエラーが含まれているかチェック
    if (empty($result) || !isset($result['choices'][0]['message']['content'])) {
        throw new Exception("Invalid response from API");
    }

    // 生成されたテキストを取得
    $result_cls = isset($result['choices'][0]['message']['content']) ? $result['choices'][0]['message']['content'] : null;

    return $result_cls;*/
    while ($retryCount < $maxRetries) {
        // cURLリクエストを初期化
        $ch = curl_init();
        // cURLオプションを設定
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);

        // APIにリクエストを送信
        $response = curl_exec($ch);

        // タイムアウトまたは他のエラーをチェック
        if (curl_errno($ch)) {
            if (curl_errno($ch) == CURLE_OPERATION_TIMEDOUT) {
                // タイムアウトした場合、再試行カウントを増やす
                $retryCount++;
                curl_close($ch);
                continue; // 次の再試行へ
            } else {
                // その他のエラー
                throw new Exception(curl_error($ch));
            }
        }

        // 応答が成功した場合、ループを抜ける
        curl_close($ch);
        break;
    }

    if ($response === false) {
        throw new Exception("API request failed after {$maxRetries} retries.");
    }

    // 応答を解析
    $result = json_decode($response, true);

    // API応答にエラーが含まれているかチェック
    if (empty($result) || !isset($result['choices'][0]['message']['content'])) {
        throw new Exception("Invalid response from API");
    }

    // 生成されたテキストを取得
    return $result['choices'][0]['message']['content'];
}
