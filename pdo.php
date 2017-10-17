<?php
// 文字化け対策
mb_language('ja');
mb_internal_encoding("UTF-8") ;

require_once("class/apiFunc.php");
require_once("class/idiorm.php");
require_once("models/articles.php");
require_once("models/labels.php");

$apiFunc = new apiFunc();

ORM::configure('sqlite:app.db');
ORM::configure('caching', true);
ORM::configure('logging', true);
ORM::configure('id_column', 'id');

$db = ORM::get_db();

// 初回のみ
/*
// 記事テーブル
$db->exec("
    CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY,
        labelid INTEGER,
        title text,
        article text,
        ins_dt default CURRENT_TIMESTAMP,
        upd_date text,
        mode text,
        del_flg INTEGER default 0
    );"
);

// ラベルテーブル
$db->exec("
    CREATE TABLE IF NOT EXISTS labels (
        id INTEGER PRIMARY KEY,
        name text,
        del_flg INTEGER default 0
    );"
);


labels::create(["name"=> "HTML"]);
labels::create(["name"=> "CSS"]);
labels::create(["name"=> "PHP"]);
*/

// $res = articles::findLabel(1);
// $res = articles::find(1);

// foreach($res as $v){
//     var_dump($v->id);
//     var_dump($v->title);
//     // echo "<br><br>";
// }

if ($apiFunc->is_ajax() ){

    // 返り値を初期化
    $return = [];
    $articleDetail = [];
    $articleList = [];

    // エスケープするパラメータを設定
    $arrays = ['mode', 'action', 'id', 'label', 'article', 'title', 'a_mode'];
    foreach ($arrays as $value) {
        ${$value} = filter_input(INPUT_POST, $value);
    }

    if($mode === "label") {

        if($action === "list") {
            // ラベルを全件取得
            $result = labels::findAll();
            foreach( (array)$result as $val){
                $return[] = ["id" => $val->id, "name"=>$val->name];
            }
            echo json_encode($return);
        }

        if($action ==="add"){
            return labels::create(["name"=>$label]);
        }

        if($action === "edit"){
            return labels::edit($id, ["name"=>$label]);
        }

        if($action === "del"){
            return labels::del($id);
        }
    }

    if($mode === "article"){
        if($action === "add"){
            if($id === '0'){
                return articles::create(["labelid"=>$label, "article"=>$article, "title"=>$title, "mode"=>$a_mode]);
            }else{

            }
        }
        if($action === "list"){
            $result = articles::findLabel($label);

            foreach( (array)$result as $values){
                $articleList[] = ["id" => $values->id, "title"=>$values->title];
            }
            echo json_encode($articleList);
        }

        if($action === "find"){
            $result = articles::find($id);

            foreach( (array)$result as $val){
                $articleDetail= ["id" => $val->id, "title"=>$val->title, "labelid"=>$val->labelid, "article"=>$val->article, "mode"=>$val->mode];
            }
            echo json_encode($articleDetail);
        }

        if($action === "edit"){
            return articles::edit($id, ["labelid"=>$label, "article"=>$article, "title"=>$title, "mode"=>$a_mode]);
        }

        if($action === "del"){
            return articles::del($id);
        }
    }

    // var_dump(ORM::get_query_log());
}else{
    echo "system Error";
}