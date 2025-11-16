<?

$connectDB = new mysqli("localhost", "u743896667_bionrgg", "Godzila#9145", "u743896667_bionrgg");
if($connectDB->connect_error){
    die("Ошибка: " . $connectDB->connect_error);
}

header("Access-Control-Allow-Origin: *");
$postData = file_get_contents('php://input');
$data = json_decode($postData, true)["data"];
$viewsData = json_decode($postData, true)["viewsData"];

if (isset($_FILES["image"])) {
    $file = $_FILES["image"];
    $username = $_POST["username"];

    $imageData = base64_encode(file_get_contents($file['tmp_name']));

    $sql = "UPDATE `users_info` SET `avatar` = '$imageData' WHERE `users_info`.`username` = '$username';";
    $connectDB->query($sql);
    echo "hello";
}

if (isset($_FILES["bg"])) {
    $file = $_FILES["bg"];
    $username = $_POST["username"];

    $imageData = base64_encode(file_get_contents($file['tmp_name']));

    $sql = "UPDATE `users_info` SET `bg` = '$imageData' WHERE `users_info`.`username` = '$username';";
    $connectDB->query($sql);
    echo "hello";
}

if ($data["action"] == "register") {
    $username = $data["username"];
    $password = $data["password"];
    $checkValue = 0;
    $query = $connectDB->query("SELECT * FROM users WHERE username='$username'");
    while ($result = $query->fetch_assoc()) {
        $checkValue += 1;
    }
    if ($checkValue > 0) {
        echo "Error";
    } else {
        $connectDB->query("INSERT INTO users(username, password) VALUES('$username', '$password')");
        $connectDB->query("INSERT INTO users_info(username) VALUES('$username')");
        $user = [$username, $password];
        echo json_encode($user);
    }
} else if ($data["action"] == "login") {
    $username = $data["username"];
    $password = $data["password"];
    $query = $connectDB->query("SELECT * FROM users WHERE username='$username'");
    $result;
    while ($result = $query->fetch_assoc()) {
        if ($result["username"] != "") {
            $checkValue += 1;
        }
        if ($checkValue > 0) {
            if ($result["password"] == $password) {
                $user = [$username, $password];
                echo json_encode($user);
            } else {
                echo "Error";
            }
        }
    }
} else if ($data["action"] == "change") {
    $username = $data["username"];
    $descr = $data["descr"];
    $inst = $data["inst"];
    $discord = $data["discord"];
    $fb = $data["fb"];
    $steam = $data["steam"];
    $twitch = $data["twitch"];
    $tiktok = $data["tiktok"];
    $tg = $data["tg"];
    $color = $data["color"];
    $colorText = $data["colorText"];
    $linkedin = $data["linkedin"];
    $youtube = $data["youtube"];
    $olx = $data["olx"];
    $amazon = $data["amazon"];
    $prom = $data["prom"];
    $github = $data["github"];
    $binance = $data["binance"];
    $fhunt = $data["fhunt"];
    $upwork = $data["upwork"];
    $fiverr = $data["fiverr"];
    $x = $data["x"];
    $whatsapp = $data["whatsapp"];
    $reddit = $data["reddit"];
    $site = $data["site"];
    $djinni = $data["djinni"];
    $dou = $data["dou"];

    $connectDB->query("UPDATE `users_info` SET `descr` = '$descr' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `inst` = '$inst' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `discord` = '$discord' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `fb` = '$fb' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `steam` = '$steam' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `twitch` = '$twitch' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `tiktok` = '$tiktok' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `tg` = '$tg' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `color` = '$color' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `colorText` = '$colorText' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `linkedin` = '$linkedin' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `youtube` = '$youtube' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `olx` = '$olx' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `amazon` = '$amazon' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `prom` = '$prom' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `github` = '$github' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `binance` = '$binance' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `fhunt` = '$fhunt' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `upwork` = '$upwork' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `fiverr` = '$fiverr' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `x` = '$x' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `whatsapp` = '$whatsapp' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `reddit` = '$reddit' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `site` = '$site' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `djinni` = '$djinni' WHERE `users_info`.`username` = '$username';");
    $connectDB->query("UPDATE `users_info` SET `dou` = '$dou' WHERE `users_info`.`username` = '$username';");


} else if ($data["action"] == "get") {
    $username = $data["username"];
    $query = $connectDB->query("SELECT * FROM users_info WHERE username='$username'");
    $result;
    while ($result = $query->fetch_assoc()) {
        $user = [$result["username"], $result["descr"], $result["inst"], $result["fb"], $result["discord"], $result["steam"], $result["views"], $result["avatar"], $result["twitch"], $result["tiktok"], $result["tg"], $result["color"], $result["bg"], $result["colorText"], $result["linkedin"], $result["youtube"], $result["olx"], $result["amazon"], $result["prom"], $result["github"], $result["binance"], $result["fhunt"], $result["upwork"], $result["fiverr"], $result["x"], $result["whatsapp"], $result["reddit"], $result["site"], $result["djinni"], $result["dou"]];
        echo json_encode($user);
    }
} else if ($viewsData["action"] == "views") {
    $views;
    $username = $viewsData["username"];
    $query = $connectDB->query("SELECT * FROM users_info WHERE username='$username'");
    $result;
    while ($result = $query->fetch_assoc()) {
        $views = $result["views"] + 1;
    }
    $connectDB->query("UPDATE `users_info` SET `views` = '$views' WHERE `users_info`.`username` = '$username';");
} else if ($data["action"] == "leaderboard") {
    $query = $connectDB->query("SELECT * FROM users_info ORDER BY views DESC;");
    $profiles = [];
    $result;
    $num = 0;
    while ($result = $query->fetch_assoc()) {
        $num += 1;
        if ($num <= 5) {
            $profiles[] = ["username" => $result["username"], "descr" => $result["descr"], "views" => $result["views"]];
        } else {
            break;
        }
    }
    echo json_encode($profiles);
} else if ($data["action"] == "delete") {
    $username = $data["username"];
    $connectDB->query("DELETE FROM users WHERE username='$username'");
    $connectDB->query("DELETE FROM users_info WHERE username='$username'");
} else if ($data["action"] == "check") {
    $username = $data["username"];
    $password = $data["password"];
    $query = $connectDB->query("SELECT * FROM users WHERE username='$username'");
    $result;
    while ($result = $query->fetch_assoc()) {
        if ($result["password"] == $password) {
            echo "User is correct";
        } else {
            echo "Error";
        }
    }
}
?>