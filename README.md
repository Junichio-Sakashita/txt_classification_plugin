# 文書分類プラグイン#

このプラグインは, GPT APIを用いてmoodleにおけるコメントシートの回答内容を「質問・感想・要望・その他」のいずれかに分類するプラグインです.

利用者はまず, moodle にログイン後, コメントシートを設定しているコースにアクセスを行う. 
その後, インデックス覧にある「レポート」を選択し, 文書分類を行うプラグイン(文書分類システムという名前) を選択する. 
すると, コース内に設定しているコメントシートの一覧と, GPT API キーを入力するテキストボックス, モデルを選択するプルダウンメニューが表示され, 分類したいコメントシートとモデルの選択, API キーを入力することで, コメントシートの回答データが分類される. 
この回答データをどのような流れで分類するかは, 先ほど説明した通りである.
その後, 回答データ並びに分類ラベルが表示され, 必要に応じて表示したいラベルの絞り込みを行う流れである. 
分類画面には, 講義名(moodle におけるセクション名にあたる), コメントシートの質問内容, 回答者のID, また複数行に渡り書いている場合, 前処理にて文書が分割されるため, 同じ人によって回答されたコメントシートだと識別するために回答者サブID を設定する. 
最後のカラムに, コメントシートのラベルを表示させる.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/report/test

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 Junichiro Sakashita <m221334@hiroshima-u.ac.jp>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
