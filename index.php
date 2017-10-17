<?php

// app.db がなければ処理しない（自動生成はさせない）
if (!file_exists('app.db')) {
    echo "Please copy app.template.db and rename it to app.db.<br>";
    echo "Setup is not comleted.";
    exit();
}

if (extension_loaded('zlib')) {
    //　ライブラリが存在していたら圧縮する
    ob_start('ob_gzhandler');
}

require_once("class/apiFunc.php");
require_once("config/define.php");
require_once("config/lang.php");
$apiFunc            = new apiFunc();
$_SESSION["active"] = '';

// Cookie に値がセット済みならPOSTに値を代入する
if (isset($_COOKIE['pass_word'])) {
    $pass = $_COOKIE['pass_word'];
} else {
    $pass          = '';
    $_POST['pass'] = '';
    $_POST['save'] = '';
}

// POSTされたらエスケープ処理をする
if ($apiFunc->is_post()) {
    $pass = filter_input(INPUT_POST, 'pass');
    $save = filter_input(INPUT_POST, 'save');
    // 「ログイン情報を記録する」にチェックが入っていたらクッキーを書き込む
    if ($save === 'on') {
        setcookie('pass_word', $pass, time() + 60 * 60 * 24 * 14);
    }
}

// パスワードが一致したらログイン処理を行う
if ($pass === APP_PASS) {
    session_name(SESSION_NAME);
    ini_set('session.hash_function', 'sha512');
    ini_set('session.hash_bits_per_character', 6);
    ini_set('session.use_strict_mode', 1);
    session_start();
    // ログイン済みの情報をセッションにセットする
    $_SESSION["active"] = "on";
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>SnippetNOTE</title>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="all">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="iconcubes.png">
        <link rel="stylesheet" href="css/remodal.css">
        <link rel="stylesheet" href="css/remodal-default-theme.css">
        <link rel="stylesheet" href="css/app.css" />
    </head>
<body>
    <?php
        if ($_SESSION["active"] !== "on") {
            echo '<div id="login_box" class="container">';
            echo '<div class="row">';
            echo '<div class="col-xs-12">';
            echo '<form method="post" action="">';
            echo '<label for="pass">Login:</label> ';
            echo '<input type="password" name="pass"><br>';
    echo '<input type="checkbox" name="save" value="on">&nbsp;<span class="white">'.LOGIN_COOKIE_MSG.'</span><br>';
            echo '<input type="submit" value="'.LOGIN_BUTTON.'">';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }
        ?>
    <div class="container">
        <div class="row">
            <!-- 記事一覧 -->
            <div class="col-sm-6">
                <div id="headerMenuGroup" class="row">
                    <div class="col-sm-3">
                        <h1>SnippetNOTE</h1>
                    </div>
                    <div class="col-sm-3 text-right">
                        <select name="labels" id="labels" class="form-control"></select>
                    </div>
                    <div class="col-sm-3 text-right">
                        <a id="editLabel" class="btn btn-success"><span class="glyphicon glyphicon-pencil"></span> Edit Label</a>
                    </div>
                    <div class="col-sm-3 text-right">
                        <a id="addLabel" class="btn btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> Add Label</a>
                    </div>
                </div>
                <div id="articles_box">
                    <ul id="articles" class="list-group">
                    </ul>
                </div>
            </div>
            <!-- 記事編集 -->
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="input-field">
                            <select name="label browser-default" class="browser-default form-control" id="label">
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a id="addArticle" class="btn btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> Snippet Add</a>
                    </div>
                </div>

                <!-- 編集エリア -->
                <form class="form-group">
                    <label for="title">Article</label>
                    <input type="text" id="title" class="form-control">
                    <!-- 記事本文 -->
                    <div id="article"></div>
                    <input type="hidden" name="editArticleId" id="editArticleId" value="0" />
                    <div id="buttonArea" class="text-right">
                        <!-- 記事モード -->
                        <select name="mode" id="mode">
                            <option value="html">-- Mode Choice --</option>
                            <option value="html">HTML</option>
                            <option value="css">CSS</option>
                            <option value="scss">SCSS</option>
                            <option value="javascript">JS</option>
                            <option value="php">PHP</option>
                            <option value="ruby">Ruby</option>
                            <option value="python">Python</option>
                            <option value="json">json</option>
                            <option value="xml">xml</option>
                            <option value="markdown">MarkDown</option>
                        </select>
                        <!-- 削除ボタン -->
                        <a class="btn btn-danger" id="delete"><span class="glyphicon glyphicon-remove-sign"></span> Delete</a>
                        <!-- 保存ボタン -->
                        <a class="btn btn-success" id="editArticle"><span class="glyphicon glyphicon-saved"></span> Save</a>
                        <!-- コピーボタン -->
                        <a class="btn btn-warning" id="copy"><span class="glyphicon glyphicon-copy"></span> Copy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-right">
                <a class="btn btn-default" href="logout.php"><span class="glyphicon glyphicon-log-out"></span> LogOut</a>
            </div>
        </div>
    </div>

    <!-- タイムアウト通知用モーダル(Remodal) -->
    <div class="remodal" data-remodal-id="modal">
        <button data-remodal-action="close" class="remodal-close"></button>
        <h1><?= TIMEOUT_TITLE ?></h1>
        <p class="msg"><?= TIMEOUT_MSG ?></p>
        <button data-remodal-action="confirm" class="remodal-confirm">OK</button>
    </div>

    <!-- ラベル編集用モーダル(Remodal) -->
    <div class="remodal" data-remodal-id="labelEdit">
        <button data-remodal-action="close" class="remodal-close"></button>
        <h1>ラベル編集</h1>
        <input type="text" name="labelTitle" id="labelTitle" maxlength="40">
        <input type="hidden" name="labelId" id="labelId">
        <div id="labelEditButtonArea" class="text-right">
            <!-- 削除ボタン -->
            <a class="btn btn-danger" id="deleteLabel"><span class="glyphicon glyphicon-remove-sign"></span> Delete</a>
            <!-- 保存ボタン -->
            <a class="btn btn-success" id="editLabel"><span class="glyphicon glyphicon-saved"></span> Save</a>
        </div>
    </div>

  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.0/ace.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.0/ext-language_tools.js"></script>
  <script src="https://cloud9ide.github.io/emmet-core/emmet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.0/ext-emmet.js"></script>
  <script src="js/remodal.min.js"></script>
  <script>
    var editor = ace.edit("article");
    editor.$blockScrolling = Infinity;
    editor.setOptions({
      enableBasicAutocompletion: true,
      enableSnippets: true,
      enableLiveAutocompletion: true,
      enableEmmet: true,
      tabSize: 2,
      useSoftTabs: true
    });
    editor.setTheme("ace/theme/twilight");
    editor.setFontSize(16);
    editor.getSession().setMode("ace/mode/php");
    editor.getSession().setUseWrapMode(true);
    editor.getSession().setUseSoftTabs(true);
    editor.getSession().setTabSize(2);
  </script>
  <script src="js/app.js"></script>
</body>
</html>
<?php
if (extension_loaded('zlib')) {
    ob_end_flush();
}