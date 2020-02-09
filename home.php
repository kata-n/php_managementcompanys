<?php
//共通変数・関数ファイル読み込み
require('function.php');

debug('================================');
debug('===========ホーム画面=============');
debug('================================');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//================================
// 画面表示用データ取得
//================================
$companyDataAll = companyDataAll();
//決算月のデータを取得
$companyFstate = companyFstate($f_id);
//データベースからお気に入りデータを取得
$favoData = getfavo();
//DBから担当者データを取得
$dbstaffData = getStaff();

// DBからきちんとデータがすべて取れているかのチェックは行わず、取れなければ何も表示しないこととする
debug('取得した会社データ：'.print_r($companyDataAll,true));
debug('取得した【決算申告月】会社データ：'.print_r($companyFstate,true));
debug('取得した【お気に入り】会社データ：'.print_r($favoData,true));
debug('===============画面表示処理終了=================');
?>
<?php
//画面表示
    $siteTitle = 'ホーム画面';
    require("head.php");
?>

<body>
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>

    <!--コンテンツ-->
    <div class="container">
        <main class="main">
            <section>
                <h2 class="heading">ホーム（基本情報）</h2>
                <h3 class="title">ブックマークした会社一覧</h3>
                <div class="cdata">
                    <?php if(!empty($favoData)): foreach($favoData as $key => $val): ?>
                    <a href="registComany.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>">
                        <div class="panel-body">
                            <p class="panel-title"><?php echo sanitize($val['cname']); ?></p>
                        </div>
                    </a>
                    <?php
                    endforeach;
                    endif;
                    ?>
                </div>
            </section>

            <section>
                <h3 class="title">今月末決算申告期限会社一覧</h3>
                <div class="cdata">
                    <?php if(!empty($companyFstate)): foreach($companyFstate as $key => $val): ?>
                    <a href="registComany.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>">
                        <div class="panel-body">
                            <p class="panel-title"><?php echo sanitize($val['cname']); ?></p>
                        </div>
                    </a>
                    <?php
                    endforeach;
                    endif;
                    ?>
                </div>
            </section>
            <section>
                <h3>全データ会社一覧</h3>
                <div class="cdata">
                    <?php if(!empty($companyDataAll)): foreach($companyDataAll as $key => $val): ?>
                    <a href="registComany.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>">
                        <div class="panel-body">
                            <p class="panel-title"><?php echo sanitize($val['cname']); ?></p>
                        </div>
                    </a>
                    <?php
                    endforeach;
                    endif;
                    ?>
                </div>
            </section>
        </main>
        <!--サイドバー-->
        <?php 
        require('sidebar.php');
        ?>

    </div>

    <!--フッター-->
    <?php
    require('footer.php');
    ?>
