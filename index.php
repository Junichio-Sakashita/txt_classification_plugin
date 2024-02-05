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
 * Displays live view of recent logs
 *
 * This file generates live view of recent logs.
 *
 * @package    report_test
 * @copyright  2011 Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\report_helper;

require('../../config.php');
global $DB;
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . '/report/test/lib.php');
$PAGE->requires->css(new moodle_url($CFG->dirroot . '/report/test/styles.css'));

//optional_paramの役割:URLから任意のパラメータを安全に取得するために使われる
//指定されたパラメータが存在しない場合にデフォルト値を返す(今回は0)
//また指定されたデータタイプ（この場合は整数PARAM_INT）に合わせて値をフィルタリングする
//これにより、不正なデータや想定外のタイプのデータが処理されるのを防ぐ
$id = optional_param('id', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

//チェックボックス用
//$check = optional_param_array("selected_items", '', PARAM_TEXT);

$params = array();
if (!empty($id)) {
    $params['id'] = $id;
} else {
    $id = $SITE->id;
}
if ($page !== 0) {
    $params['page'] = $page;
}/*
if ($check !== ''){
    $params['check'] = $check;
}*/

//指定されたパラメータを含むURLを作成
//このURLは、ページの$PAGE->set_url($url)メソッドを使用して設定される
//これにより、ページの基本的なURLが設定され、Moodleシステムが適切にページを認識し、ナビゲーションなどで正しく使用できるようになる
$url = new moodle_url("/report/test/index.php", $params);

$PAGE->set_url('/report/log/index.php', array('id' => $id));
//$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

//20231227
//ユーザーが特定のコースにアクセスしているのか、またはサイト全体のレポートを見ているのかを判断し、それに応じて適切な設定とアクセス権のチェック
//コースidが空(特定のコースidを持たない場合の処置)
if (empty($id)) {
    //admin_externalpage_setup関数は、管理者専用のページを設定するために使用
    //ここでは、特定のコースが指定されていない場合（つまりサイト全体のレポートを表示する場合）、この関数を呼び出す
    admin_externalpage_setup('reporttest', '', null, '', array('pagelayout' => 'report'));
    //システムレベルのコンテキスト（全サイトに関する設定や情報）を取得
    $context = context_system::instance();
    //$SITE->fullname: これはMoodleグローバル変数 $SITE からサイトのフルネーム（完全な名称）を取得
    //$SITE はMoodleのインスタンス全体に関する情報を格納する変数で、ここではサイト全体の名称を指す
    //与えられた文字列をMoodleのコンテキストに適した形でフォーマットする
    //true はフィルタリングを適用
    //array('context' => $context): この引数は、フォーマットする際にどのコンテキストを考慮するかを指定
    //この場合、$context は上述のコードで定義されたコンテキスト（システムレベルまたは特定のコース）を参照
    $coursename = format_string($SITE->fullname, true, array('context' => $context));
} else {
    $course = get_course($id);
    require_login($course);
    $context = context_course::instance($course->id);
    $coursename = format_string($course->fullname, true, array('context' => $context));
}
require_capability('report/test:view', $context);

//$PAGE->set_url($url);
//現在のページが属するMoodleコンテキストを指定
//コンテキスト:Moodle コンテキストは、(通常はロールを通じて) 権限がユーザーに割り当てられる場所
$PAGE->set_context($context);
//ページ タイトル(chromeのタブに出てくる見出し)
//$PAGE->set_title("$coursename: $strlivelogs");
//ページの主見出し
//$PAGE->set_heading($coursename);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($coursename);
//$PAGE->set_heading(get_string('pluginname', 'report_test'));

//ログインを要求する
require_login();
//ゲストユーザーを許可しない
if(isguestuser()){
    throw new moodle_exception('noguest');
}

echo $OUTPUT->header();
echo '<link rel="stylesheet" type="text/css" href="../test/styles.css">';
// Print selector dropdown.
$pluginname = get_string('pluginname', 'report_test');
report_helper::print_report_selector($pluginname);

echo '<p>GPT APIキーを入力後、コース内に設定されているコメントシートの一覧から分類したいコメントシートにチェックを入れて、分類開始のボタンを押してください。</p>';

// SQLクエリでデータを取得
/*$sql = "SELECT i.id, i.feedback, i.name, f.name AS csname, f.course FROM {feedback_item} i 
        LEFT JOIN {feedback} f ON i.feedback = f.id
        WHERE f.course = :courseid AND i.typ = 'textarea'";*/
$sql2 = "SELECT id, fid, scname, fname, name, cid FROM {feedback_item} fi JOIN 
        (SELECT cm.section AS section, cs.name AS scname, f.name AS fname, f.id AS fid, f.course AS cid 
        FROM {course_modules} cm 
        JOIN {feedback} f ON cm.instance = f.id 
        JOIN {course_sections} cs ON cs.id = cm.section 
        WHERE module = (SELECT id FROM {modules} WHERE name='feedback') AND cm.course = :courseid) p1 
        ON fi.feedback = p1.fid 
        WHERE typ = 'textarea'";
$cs = $DB->get_records_sql($sql2, array('courseid' => $params['id']));

// フォームにデータを渡す
$form = new \report_test\form\test_form(array('items' => $cs, 'courseid' => $params['id']), $method = "post");

//echo '<div class = "form_cs_index">';
    // フォームを表示
    $form->display();
//echo '</div>';
/*
echo '<div id = "splash">';
echo '<div id = "splash_text"></div>';
echo '</div>';*/
//echo '<div id = "container>';
// フォームの送信を処理
if ($data = $form->get_data()) {

    // ユーザーの設定としてGPT APIキーを保存
    set_user_preferences(array('report_test_gptapi_key' => $data->gptapi_key));

    //var_dump($data);
    $check = optional_param_array("selected_items", '', PARAM_TEXT);
    $isEmpty = true;

    //入力されたapiキーの取得
    $gptapi_key = optional_param("gptapi_key", '', PARAM_TEXT);
    //選択されたモデル名の取得
    $gptapi_model = optional_param("gpt_model", '', PARAM_TEXT);

    var_dump($gptapi_key, $gptapi_model);
    foreach ($check as $key => $value) {
        if ($value !== '0') {
            $isEmpty = false;
            break;
        }
    }

    if (!$isEmpty) {
        $jsonArray = [];
        echo $OUTPUT->heading("チェックしたコメントシートの回答結果一覧");

        echo '<div class = "wboard-w4">';
        echo "<table border='1'>";
        echo "<tr><th style='text-align: center;'>講義回</th>";
        echo "<th style='text-align: center;'>コメントシート名</th>";
        echo "<th style='text-align: center;'>質問内容</th>";
        echo "<th style='text-align: center;'>回答内容</th>";
        echo "<th style='text-align: center;'>Item ID</th>";
        echo "<th style='text-align: center;'>feedback ID</th>";
        echo "<th style='text-aligh: center;'>value ID</th></tr>";

        foreach ($check as $key => $value) {
            $ansdata = [];//初期化

            if ($value === '0') {
                continue;
            }
            //var_dump($params);

            list($itemID, $feedbackID, $scname) = explode('-', $value);
            $FIid = (int)$itemID;
            $Fid = (int)$feedbackID;

            $feedback_name = $DB->get_record_sql("SELECT name FROM {feedback} WHERE id = :feedbackid", array('feedbackid' => $Fid));
            $feedback_itemName = $DB->get_record_sql("SELECT name FROM {feedback_item} WHERE id = :id", array('id' => $FIid));
            $ans = $DB->get_records_sql("SELECT value, id FROM {feedback_value} WHERE item = :item", array('item' => $FIid));

            foreach ($ans as $row) {
                echo "<tr><td>" .$scname . "</td>";
                echo "<td>" . $feedback_name->name . "</td>";
                echo "<td>" . $feedback_itemName->name . "</td>";
                echo "<td>" . $row->value . "</td>";
                echo "<td>" . $FIid . "</td>";
                echo "<td>" . $Fid . "</td>";
                echo "<td>" . $row->id . "</td></tr>";

                // 前処理を行う
                $str = txt_preprocessing($row->value);

                //抽出したデータをJSON形式で保存する
                
                if ($str != NULL) {
                    // $ansdataに$idとして$row->idを使用し、$strの要素を追加する
                    if (!isset($ansdata[$row->id])) {
                        $ansdata[$row->id] = [];
                    }
                    foreach ($str as $sentence) {
                        $ansdata[$row->id][] = $sentence;
                    }
                }
            }
            //var_dump("API呼び出し前のapiキー値:" . $gptapi_key);
            foreach ($ansdata as $valueId => $texts) {
                foreach ($texts as $index => $text) {
                    try{
                        //ここでGPTAPIを用いて呼び出す?
                        $txt_label =  gpt_txtclassification($text, $gptapi_key, $gptapi_model);
                        if($txt_label == '0'){
                            $label = "感想";
                        }
                        elseif($txt_label == '1'){
                            $label = "質問";
                        }
                        elseif($txt_label == '2'){
                            $label = "要望";
                        }
                        elseif($txt_label == '3'){
                            $label = "その他";
                        }
                        else{
                            $label = "エラー";
                        }
                        $feedback_name = $DB->get_record_sql("SELECT name FROM {feedback} WHERE id = :feedbackid", array('feedbackid' => $Fid));
                        $feedback_itemName = $DB->get_record_sql("SELECT name FROM {feedback_item} WHERE id = :id", array('id' => $FIid));
                        $jsonArray[] = [
                            "Section Name" => $scname,
                            "Feedback Name" => $feedback_name->name,
                            "Item Name" => $feedback_itemName->name,
                            "Value ID" => $valueId,
                            "Value ID2" => $index,
                            "text" => $text,
                            "label" => $label
                        ];
                    }
                    catch (Exception $e) {
                        $txt_label = 'Error: ' . $e->getMessage();
                        $feedback_name = $DB->get_record_sql("SELECT name FROM {feedback} WHERE id = :feedbackid", array('feedbackid' => $Fid));
                        $feedback_itemName = $DB->get_record_sql("SELECT name FROM {feedback_item} WHERE id = :id", array('id' => $FIid));            
                        $jsonArray[] = [
                            "Section Name" => $scname,
                            "Feedback Name" => $feedback_name->name,
                            "Item Name" => $feedback_itemName->name,
                            "Value ID" => $valueId,
                            "Value ID2" => $index,
                            "text" => $text,
                            "label" => $txt_label
                        ];
                    }
                }
            }
        }
        //var_dump($jsonArray);
        echo "</table>";
        echo '</div>';
        //var_dump($jsonArray);
        echo $OUTPUT->heading("チェックしたコメントシートの分類結果一覧");
        //echo $OUTPUT->heading("チェックしたコメントシートの分類結果一覧");

        //絞り込み用チェックボックス一覧
        echo '<div id="labelFilter" class="my-parts">';//チェックboxをグループ化するコンテナ(div)
            //nameを統一することでグループ化
            //value属性は、チェックボックスがオンのときにサーバーに送信される値を指定(まぁここではいらねぇ)
            //checkedでデフォルトでチェックを入れる
            echo '<dl>';
            echo '<dt><label>表示するラベルを選択</label></dt>';
            echo '<dd>';
            echo '<input type="checkbox" id="感想" name="labelFilter" value="感想" checked /><label for="感想">感想</label>';
            echo '<input type="checkbox" id="質問" name="labelFilter" value="質問" checked /><label for="質問">質問</label>';
            echo '<input type="checkbox" id="要望" name="labelFilter" value="要望" checked /><label for="要望">要望</label>';
            echo '<input type="checkbox" id="その他" name="labelFilter" value="その他" checked /><label for="その他">その他</label>';
            echo '</dd>';
            echo '</dl>';
        echo '</div>';
    
        //分類結果(絞り込み検索結果)一覧
        echo '<div class = "wboard-w4">';
            //echo '<span class="title-w4">チェックしたコメントシートの分類結果一覧</span>';
            echo '<table border="1" id="resultsTable" style="width:90%;">';
            echo '<tr>';
            echo '<th style="text-align: center; width:8%;">講義名</th>';
            echo '<th style="text-align: center; width:10%;">コメントシート名</th>';
            echo '<th style="text-align: center; width:25%;">質問内容</th>';
            echo '<th style="text-align: center; width:4%;">回答者 ID</th>';
            echo '<th style="text-align: center; width:4%;">回答者 サブID</th>';
            echo '<th style="text-align: center; width:40%;">回答内容</th>';
            echo '<th style="text-align: center; width:8%;">分類結果</th>';
            echo '</tr>';            
            foreach ($jsonArray as $data) {
                echo '<tr>';
                echo '<td>' . $data['Section Name'] . '</td>';
                echo '<td>' . $data['Feedback Name'] . '</td>';
                echo '<td>' . $data['Item Name'] . '</td>';
                echo '<td>' . $data['Value ID'] . '</td>';
                echo '<td>' . $data['Value ID2'] . '</td>';
                echo '<td>' . $data['text'] . '</td>';
                echo '<td>' . $data['label'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        echo '</div>';
    } else {
        echo "<script>alert('何も選択されていません');</script>";
    }
}
//echo '</div>';
echo $OUTPUT->footer();

//echo '<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>';
//echo '<script src="https://rawgit.com/kimmobrunfeldt/progressbar.js/master/dist/progressbar.min.js"></script>';
//<!--IE11用-->
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>';
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/6.26.0/polyfill.min.js"></script>';

echo '<script src="../test/lib.js"></script>';
