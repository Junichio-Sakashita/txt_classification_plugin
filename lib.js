function filterTable() {
    //checked プロパティの値はチェックされていれば true、
    //チェックされていなければ false
    //各変数にはチェックボックスの状態(true/false)を格納
    var showOpinion = document.getElementById('感想').checked;
    var showQuestion = document.getElementById('質問').checked;
    var showRequest = document.getElementById('要望').checked;
    var showOther = document.getElementById('その他').checked;

    //テーブルと行の取得
    //テーブル要素が全体としてtable変数に格納
    var table = document.getElementById('resultsTable');
    //table変数に格納されているテーブル要素内の全ての行(<tr>要素)を取得
    //headerを含むすべての各行が、ノードリストとして格納される
    var tr = table.getElementsByTagName('tr');

    //各行の表示・非表示の制御
    //行数分処理を行う
    for (var i = 0; i < tr.length; i++) {
        //各行の7番目のセル(分類結果)を取得
        var td = tr[i].getElementsByTagName('td')[6];
        if (td) {
            //取得したセル（td）のテキスト内容を取得
            var label = td.textContent;
            //取得したテキスト（label）が特定のカテゴリ(感想・質問・要望・その他)に該当するかどうか、
            //関連するチェックボックスがチェックされているかどうかに基づいて、行を表示するかどうかを決定
            var shouldShow = 
                (label === '感想' && showOpinion) ||
                (label === '質問' && showQuestion) ||
                (label === '要望' && showRequest) ||
                (label === 'その他' && showOther);
            
            //三項演算子で条件に一致する行は表示され（style.display = ''）、
            //一致しない行は非表示
            //shouldShowがtrueの場合、行はデフォルトの表示状態（''）に設定
            //falseの場合は非表示（'none'）に設定
            tr[i].style.display = shouldShow ? '' : 'none';
        }
    }
}

//DOMContentLoaded イベントリスナーを追加
//これは、HTMLドキュメントが完全に読み込まれて解析された後に発生するイベント
document.addEventListener('DOMContentLoaded', (event) => {
    //addEventListener メソッドを使用して、特定の要素にイベントリスナーを追加
    //change: フォーム部品の状態が変更されたとき、filterTable関数を実行する
    document.getElementById("感想").addEventListener("change", filterTable);
    document.getElementById("質問").addEventListener("change", filterTable);
    document.getElementById("要望").addEventListener("change", filterTable);
    document.getElementById("その他").addEventListener("change", filterTable);
});
