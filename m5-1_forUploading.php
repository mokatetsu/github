<!DOCTYPE HTML>
<html lang= "ja">
    <head>
        <meta charset= "UTF-8">
        <title>testCode</title>
    </head>
    
    <body>
        <form method="POST">
            <input type="textbox" name="commenter" placeholder= "名前"></input><br>
            <input type="textbox" name="comment" placeholder= "コメント"></input><br>
            <input type= "password" name= "setPW" placeholder= "任意のパスワードを設定" maxlength= "4"></input>
            <input type="submit" name="submitComment" value="送信"></input><br><br>
            <input type="textbox" name= "deleteComment" value= "" placeholder= "削除したい投稿番号"></input><br>
            <input type="password" name= "deletePW" value= "" placeholder= "パスワード" maxlength= "4"></input>
            <input type= "submit" name= "submitDelComment" value= "送信"></input><br><br>
            <input type= "textbox" name= "editComment" placeholder= "編集したい投稿番号"></input><br>
            <input type= "password" name= "editPW" placeholder= "パスワード"></input>
            <input type= "submit" name= "submitEditComment" value= "送信"></input><br><br>
        </form>
        <?php
            $dsn = 'mysql:dbname=データベース名;host=localhost';
            $user = 'ユーザ名';
            $password = 'パスワード';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            $query= "CREATE TABLE IF NOT EXISTS posts(
                num int AUTO_INCREMENT PRIMARY KEY,
                commenter CHAR(20),
                postDate DATETIME,
                comment VARCHAR(200),
                passWord int(4)
            )";
            $runQuery= $pdo-> query($query);

            date_default_timezone_set('Asia/Tokyo');
            
            if(!empty($_POST["commenter"])&& !empty($_POST["comment"])&& !empty($_POST["submitComment"])) {
                $commenter= $_POST["commenter"];
                $comment= $_POST["comment"];
                $postDate= date("Y/m/d H:i:s");
                $passWord= $_POST["setPW"];
                
                if(!empty($_POST["setPW"])) {
                    $pwHidden= str_repeat("*", strlen($passWord));
                } else {
                    $pwHidden= "[未設定]";
                    $passWord= "password is not exist";
                }
                
                echo "新たな投稿を送信しました<br>送信者:$commenter<br>投稿内容:$comment<br>パスワード:$pwHidden<br>($postDate)<br><br>";
                
                $query= "INSERT INTO posts(commenter, comment, postDate, passWord) VALUES(:commenter, :comment, :postDate, :passWord)";
                $runQuery= $pdo-> prepare($query);

                $runQuery-> bindParam(":commenter", $commenter, PDO:: PARAM_STR);
                $runQuery-> bindParam(":comment", $comment, PDO:: PARAM_STR);
                $runQuery-> bindParam(":postDate", $postDate, PDO:: PARAM_STR);
                $runQuery-> bindParam(":passWord", $passWord, PDO:: PARAM_INT);
                $runQuery-> execute();
            } //コメントの新規投稿

            
            if(!empty($_POST["deleteComment"])&& !empty($_POST["submitDelComment"])) {
                $delIndex= (int)$_POST["deleteComment"];
                $query= "SELECT passWord FROM posts WHERE num= :delIndex";
                $runQuery= $pdo-> prepare($query);
                $runQuery-> bindParam(":delIndex", $delIndex, PDO:: PARAM_INT);
                $runQuery-> execute();

                $truePW= $runQuery-> fetchColumn();

                $checkPW= $_POST["deletePW"];
                
                //if(!empty($commentList[$delIndex])) {
                    
                    if(empty($checkPW)) {
                        echo "<strong>パスワードを入力してください</strong><br><br>";
                    } else {
                        if($truePW== $checkPW) {
                            $query= "DELETE FROM posts WHERE num= :delIndex";
                            $runQuery= $pdo-> prepare($query);
                            $runQuery-> bindParam(":delIndex", $delIndex, PDO::PARAM_INT);
                            $runQuery-> execute();

                            echo "<strong>コメントを削除しました</strong><br><br>";

                        } else {
                            echo "<strong>パスワードが間違っているか、設定されていません</strong><br><br>";
                        }
                    }
                //} else {
                    //echo "<strong>指定された番号に対応するコメントが存在しません</strong><br><br>";
                //}
            } elseif(!empty($_POST["submitDelComment"])) {
                echo "<strong>削除したいコメントの番号を入力してください</strong><br><br>";
            } //コメント削除

            
            if(!empty($_POST["editComment"])&& !empty($_POST["submitEditComment"])) {
                if(empty($_POST["commenter"])|| empty($_POST["comment"])) {
                    echo "<strong>編集後の投稿者名とコメントを入力してください</strong><br><br>";
                } else {
                    $editIndex= (int)$_POST["editComment"];
                    $query= "SELECT passWord FROM posts WHERE num= :editIndex";
                    $runQuery= $pdo-> prepare($query);
                    $runQuery-> bindParam(":editIndex", $editIndex, PDO:: PARAM_INT);
                    $runQuery-> execute();
    
                    $truePW= $runQuery-> fetchColumn();
    
                    $checkPW= $_POST["editPW"];

                        if(empty($checkPW)) {
                            echo "<strong>パスワードを入力してください</strong><br><br>";
                        } else {
                            if($truePW== $checkPW) {
                                $newCommenter= $_POST["commenter"];
                                $newComment= $_POST["comment"];

                                $query= "UPDATE posts SET commenter= :newCommenter, comment= :newComment WHERE num= :editIndex";
                                $runQuery= $pdo-> prepare($query);
                                $runQuery-> bindParam(":newCommenter", $newCommenter, PDO:: PARAM_STR);
                                $runQuery-> bindParam(":newComment", $newComment, PDO:: PARAM_STR);
                                $runQuery-> bindParam(":editIndex", $editIndex, PDO:: PARAM_INT);
                                $runQuery-> execute();

                                echo "<strong>コメントを編集しました</strong><br><br>";
                            } else {
                                echo "<strong>パスワードが間違っているか、設定されていません</strong><br><br>";
                            }
                        }
                    }
                    
            } elseif(!empty($_POST["submitEditComment"])) {
                echo "<strong>編集したいコメントの番号を入力してください</strong><br><br>";
            } //コメント編集
            
            $query= "SELECT * FROM posts";
            $runQuery= $pdo-> query($query);
            $postList= $runQuery-> fetchAll();

            foreach($postList as $content) {
                echo $content['num']. ": ". $content['commenter']. "(". $content['postDate']. ")<br>". $content['comment']. "<br><br>";
            }
        ?>
    </body>
</html>