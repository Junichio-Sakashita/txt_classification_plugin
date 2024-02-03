<?php
//このクラス/ファイルのための名前空間を設定
//通常同じファイルに同じクラスや関数名、定数名が存在することはできない
//同じ関数名を使用するためにはnamespaceを使用して、関数を明確に区別
//名前空間を使用することにより、関連するクラスや、インターフェイス、関数、定数などをグループ化することが可能
//同じ名前のクラスが他のプラグインやMoodleコアで定義されていても、名前の衝突を避けることが可能
namespace report_test\form;

defined('MOODLE_INTERNAL')  || die();

//Moodleの設定値を格納し、Moodleのインストールに関する重要な情報、例えばデータベース接続の詳細やMoodleのルートディレクトリのパスなどを含んでいる
//MoodleのフォームAPIを使用するためには、formslib.phpファイルを読み込む必要がある
//このファイルはMoodleのライブラリディレクトリ（libdir）にあり、フォーム関連の機能を提供する
require_once($CFG->libdir . '/formslib.php');
//echo '<link rel="stylesheet" type="text/css" href="../test/styles.css">';

//moodleformクラスの拡張
class test_form extends \moodleform {
    private $items;

    public function __construct($customdata = null, $action = null, $method = "post", $target = '', $attributes = null, $editable = true) {
        $this->items = $customdata['items'] ?? [];
        $courseid = $customdata['courseid'] ?? null;  // courseidが存在するかチェックし、存在しない場合はnullを使用
        $action = new \moodle_url('/report/test/index.php', ['id' => $courseid]);
        $method = 'post';
        //$attributes = array('id' => 'form_textclassification');
        parent::__construct($action, $method, $target, $attributes, $editable);
    }
    

    //フォーム要素は、definition()という関数で定義
    public function definition() {
        ////$thisは、moodleformクラスを拡張したmessage_formクラスのインスタンス（つまりオブジェクト）を指す
        $mform = $this->_form;
        $this->_form->_attributes['method'] = 'post';

        global $USER;
        // ユーザーの設定からGPT APIキーを取得
        $apikey = get_user_preferences('report_test_gptapi_key', '', $USER->id);

        //var_dump($this);
        //GPT APIキーの入力テキストエリアの設置
        $mform->addElement('html','<div class = "gptapi_inputarea">');
            $mform->addElement('passwordunmask', 'gptapi_key', 'GPT APIキーを入力してください:');
            $mform->setType('gptapi_key', PARAM_TEXT);
            $mform->setDefault('gptapi_key', $apikey);  // デフォルト値として設定
        $mform->addElement('html', '</div>');

        // GPTモデルの選択肢を定義
        $gptModels = array(
            'ft:gpt-3.5-turbo-0613:personal::7wnRXF0g' => 'GPT3.5-turbo(FT)',
            'gpt-4-1106-preview' => 'GPT4-turbo'
        );

        // フォームにセレクトボックスを追加
        $mform->addElement('select', 'gpt_model', 'GPTモデルを選択してください:', $gptModels);
        $mform->setDefault('gpt_model', 'gpt-3.5-turbo(FT)'); // デフォルト値の設定

        $mform->addElement('html', '<div>');
            $mform->addElement('html', '<h2>コメントシート一覧</h2>');
            $mform->addElement('html', '<div class = "wboard-w4">');
                // 表の開始
                //任意の HTML を Moodle フォームに追加(addElement('html',  '<div class="qheader">'のように))
                $mform->addElement('html', '<table border="1">');
                //$mform->addElement('html', '<tr><th style="text-align: center;">Item ID</th><th style="text-align: center;">Feedback ID</th><th style="text-align: center;">講義名</th><th style="text-align: center;">コメントシート名</th><th style="text-align: center;">質問内容</th><th style="text-align: center;">Course ID</th><th style="text-align: center;">チェック</th></tr>');
                $mform->addElement('html', '<tr><th style="text-align: center;">講義名</th><th style="text-align: center;">コメントシート名</th><th style="text-align: center;">質問内容</th><th style="text-align: center;">チェック</th></tr>');

                foreach ($this->items as $item) {
                    $mform->addElement('html', '<tr>');
                    // 各項目を追加
                    //$mform->addElement('html', '<td>' . $item->id . '</td>');
                    //$mform->addElement('html', '<td>' . $item->fid . '</td>');
                    $mform->addElement('html', '<td>' . $item->scname . '</td>');
                    $mform->addElement('html', '<td>' . $item->fname . '</td>');
                    $mform->addElement('html', '<td>' . $item->name . '</td>');
                    //$mform->addElement('html', '<td>' . $item->cid . '</td>');
                    //チェックボックス
                    //$mform -> addElement ( 'checkbox' ,  '評価時間' ,  get_string ( '評価時間' ,  'フォーラム' ));
                    //これはシンプルなチェックボックスです。この要素の 3 番目のパラメータは、フォームの左側に表示するラベル
                    //4 番目のパラメーターとして文字列を指定して、要素の右側に表示されるラベルを指定することも可
                    //チェックボックスとラジオ ボタンをグループ化し、右側に個別のラベルを付けることができる
                    //$checkboxname = 'selected_items[' . $item->id . ']';: チェックボックスの名前を設定
                    $checkboxname = 'selected_items[' . $item->id . ']';
                    $mform->addElement('html', '<td class = checkbox-center>');
                    //第1引数: 追加する要素のタイプ(進化版チェックボックス)
                    //第2引数: チェックボックスの名前
                    //第3/4/5引数:ラベル, 右に表示するラベル, 左に表示するラベル
                    //第6引数: チェックボックスの状態を表す, チェックが付けられていない場合0が返され、チェックがついている場合、itemIDとfeedbackIDの値が返される 
                    $mform->addElement('advcheckbox', $checkboxname, '', '', '', array(0, $item->id ."-". $item->fid ."-". $item->scname));
                    $mform->addElement('html', '</td>');
                    
                    $mform->addElement('html', '</tr>'); // 行の終了
                }
            
                $mform->addElement('html', '</table>'); // 表の終了
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class = "cs_index_subbtn">');
                // 送信ボタンを追加
                $mform->addElement('submit', '', '分類開始');
            $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        // フォーム要素の定義内に追加
        //$mform->addElement('html', '<div class="loader" id="loader" style="display:none;"></div>');
        //$mform->addElement('html', '<div id = "splash">');
        //$mform->addElement('html', '<div id = "splash_text"></div>');
        //$mform->addElement('html', '<div id = "container"></div>');
    }
}