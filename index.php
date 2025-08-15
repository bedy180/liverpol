<?php
$conn = new mysqli("localhost","root","","match_game");
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$tab = $_GET['tab'] ?? 'add';
$message = '';
?>
<link rel="stylesheet" href="style.css">
<h1>لعبة توقع مباريات ليفربول</h1>
<nav>
    <a href="?tab=add">إضافة توقعات</a> |
    <a href="?tab=result">إدخال النتيجة</a> |
    <a href="?tab=ranking">عرض الترتيب</a>
</nav>
<hr>

<?php
if($tab == 'add'){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $name = $_POST['name'];
        $score = $_POST['score'];
        $first_scorer = $_POST['first_scorer'];
        $first_assist = $_POST['first_assist'];

        $stmt = $conn->prepare("INSERT INTO players(name) VALUES(?)");
        $stmt->bind_param("s",$name);
        $stmt->execute();
        $player_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO predictions(player_id, score, first_scorer, first_assist) VALUES(?,?,?,?)");
        $stmt->bind_param("isss",$player_id,$score,$first_scorer,$first_assist);
        $stmt->execute();
        $stmt->close();

        $message = "تم حفظ توقعات $name بنجاح!";
    }
    if($message) echo "<p>$message</p>";
    ?>
    <form method="post">
        الاسم: <input type="text" name="name" required>
        نتيجة المباراة (مثال: 3-1): <input type="text" name="score" required>
        أول من يسجل: <input type="text" name="first_scorer" required>
        أول من يمرر: <input type="text" name="first_assist" required>
        <button type="submit">حفظ التوقعات</button>
    </form>
    <?php
}

elseif($tab == 'result'){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $score = $_POST['score'];
        $first_scorer = $_POST['first_scorer'];
        $first_assist = $_POST['first_assist'];

        $conn->query("DELETE FROM result");
        $stmt = $conn->prepare("INSERT INTO result(id, score, first_scorer, first_assist) VALUES(1,?,?,?)");
        $stmt->bind_param("sss",$score,$first_scorer,$first_assist);
        $stmt->execute();
        $stmt->close();

        $res = $conn->query("SELECT * FROM predictions");
        $row = $conn->query("SELECT * FROM result")->fetch_assoc();
        $r_score = $row['score'];
        $r_scorer = $row['first_scorer'];
        $r_assist = $row['first_assist'];

        while($p = $res->fetch_assoc()){
            $points = 0;
            if($p['score'] === $r_score) $points++;
            if(strtolower($p['first_scorer']) === strtolower($r_scorer)) $points++;
            if(strtolower($p['first_assist']) === strtolower($r_assist)) $points++;

            $conn->query("UPDATE predictions SET points=$points WHERE id=".$p['id']);
        }

        $message = "تم تسجيل النتيجة وحساب النقاط!";
    }
    if($message) echo "<p>$message</p>";
    ?>
    <form method="post">
        نتيجة المباراة: <input type="text" name="score" required>
        أول من يسجل: <input type="text" name="first_scorer" required>
        أول من يمرر: <input type="text" name="first_assist" required>
        <button type="submit">حفظ النتيجة</button>
    </form>
    <?php
}

elseif($tab == 'ranking'){
    $res = $conn->query("SELECT p.name, pr.score, pr.first_scorer, pr.first_assist, pr.points 
                         FROM predictions pr
                         JOIN players p ON pr.player_id = p.id
                         ORDER BY pr.points DESC");
    echo "<table><tr><th>الاسم</th><th>نتيجة التوقع</th><th>أول من يسجل</th><th>أول من يمرر</th><th>النقاط</th></tr>";
    while($p = $res->fetch_assoc()){
        echo "<tr>
            <td>".$p['name']."</td>
            <td>".$p['score']."</td>
            <td>".$p['first_scorer']."</td>
            <td>".$p['first_assist']."</td>
            <td>".$p['points']."</td>
        </tr>";
    }
    echo "</table>";
}
?>