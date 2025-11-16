<?php
// Финальный тест всего
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔧 Финальный тест Bionrgg</h1>";

$username = 'chupserso';

echo "<h2>1. Проверка базы данных</h2>";

try {
    $connectDB = new mysqli("localhost", "u743896667_bionrgg", "Godzila#9145", "u743896667_bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("❌ Ошибка подключения к БД: " . $connectDB->connect_error);
    }
    
    $connectDB->set_charset("utf8mb4");
    echo "<p>✅ Подключение к БД успешно</p>";
    
    // Проверяем пользователя
    $query = "SELECT * FROM users_info WHERE username = ?";
    $stmt = $connectDB->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userInfo = $result->fetch_assoc();
        echo "<p>✅ Пользователь '$username' найден в БД</p>";
        echo "<p>📝 Описание: " . htmlspecialchars($userInfo['descr'] ?: 'Нет описания') . "</p>";
        echo "<p>👁 Просмотры: " . $userInfo['views'] . "</p>";
    } else {
        echo "<p>❌ Пользователь '$username' НЕ найден в БД</p>";
        
        // Показываем всех пользователей
        echo "<h3>Все пользователи в БД:</h3>";
        $query = "SELECT username FROM users_info";
        $result = $connectDB->query($query);
        
        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['username']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Нет пользователей в БД</p>";
        }
    }
    
    $connectDB->close();
    
} catch (Exception $e) {
    echo "<p>❌ Ошибка БД: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Проверка API</h2>";

// Тестируем API
$_GET['username'] = $username;

ob_start();
include 'api/get-profile.php';
$response = ob_get_clean();

echo "<p>📡 Ответ API:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);

if ($data) {
    if ($data['success']) {
        echo "<p>✅ API работает правильно</p>";
        echo "<p>👤 Пользователь: " . htmlspecialchars($data['profile']['username']) . "</p>";
        echo "<p>📝 Описание: " . htmlspecialchars($data['profile']['descr'] ?: 'Нет описания') . "</p>";
        echo "<p>👁 Просмотры: " . $data['profile']['views'] . "</p>";
    } else {
        echo "<p>❌ API ошибка: " . htmlspecialchars($data['message']) . "</p>";
    }
} else {
    echo "<p>❌ Неверный JSON ответ от API</p>";
}

echo "<h2>3. Проверка URL</h2>";

echo "<p>🔗 Текущий URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>🌐 Хост: " . $_SERVER['HTTP_HOST'] . "</p>";

// Тестируем URL профиля
$profileUrl = "http://" . $_SERVER['HTTP_HOST'] . "/" . $username;
echo "<p>🔗 URL профиля: <a href='$profileUrl' target='_blank'>$profileUrl</a></p>";

echo "<h2>4. Результат</h2>";

if ($data && $data['success']) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ВСЕ РАБОТАЕТ ПРАВИЛЬНО!</p>";
    echo "<p>✅ База данных: OK</p>";
    echo "<p>✅ API: OK</p>";
    echo "<p>✅ Пользователь найден: OK</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ ЕСТЬ ПРОБЛЕМЫ</p>";
    echo "<p>Проверьте базу данных и создайте пользователя</p>";
}
?>