<header>
    <div class="header">
        <h2 class="heading" style="font-size:90%;"><a href="<?php echo (empty($_SESSION['user_id']))? 'home.php' :  'login.php'; ?>">経理進捗管理システム（公開用）</a></h2>
        <nav>
            <ul class="title-list">
                <?php if(empty($_SESSION['user_id'])){ ?>
                <?php
                  }else{
                ?>
                <li><a href="logout.php" class="logaout">ログアウト</a></li>
                <?php
                 }
                ?>
            </ul>
        </nav>
    </div>
</header>
