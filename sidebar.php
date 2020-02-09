        <aside class="aside">
            <h3>メニュー</h3>
            <ul class="sidebar">
                <li><a href="registComany.php">会社新規登録</a></li>
                <li><a href="accinput.php">仕訳入力数確認</a></li>
                <li><a href="home.php">ホーム画面へ</a></li>
            </ul>
            <h3 style="margin-bottom: 0;">担当者別で表示</h3>
            <form action="" method="post">
             <select id="staffnum" name="staff_id" id="" class="staffselect" style="margin-left: 30px;">
                <option value="0" <?php if(getFormData('staff_id') ==0){'selected';}?>>選択してください
                </option>
                 <?php foreach($dbstaffData as $key => $val){?>
                 <option value="<?php echo $val['id']?>"<?php if(getFormData('staff_id') == $val['id']){ echo 'selected';}?>>
                   <?php echo $val['staff_name'];?>
                 </option>
                 <?php
                 }
                 ?>
             </select>
                <div id="result"></div>
            </form>
        </aside>
