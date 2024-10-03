<?php
session_start(); // 啟用 Session

// 資料庫連接設定
$servername = "localhost";
$db_username = "root"; // 資料庫使用者
$db_password = "27003378"; // 替換為你的 MySQL 密碼
$dbname = "dc_bot3"; // 替換為你的資料庫名稱

// 建立連接
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);

// 檢查連接
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error_message = ""; // 初始化錯誤訊息
$success_message = ""; // 初始化成功訊息

// 檢查是否取得POST內容
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // 查詢資料庫，檢查email與密碼
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 檢查是否有符合的email和密碼
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['password'] === $password) {
            $_SESSION["username"] = $row['username'];
            $_SESSION["role"] = $row['role'];
            header("Location: index.php");
            exit;
        }
    }
    
    // 帳號或密碼錯誤，設置錯誤訊息
    $error_message = "帳號或密碼錯誤，請再試一次";
}

// 處理註冊
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST["username"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // 檢查郵箱是否已存在
    $check_sql = "SELECT * FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "該郵箱已被註冊";
    } else {
        // 插入新用戶
        $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'C')";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "sss", $username, $email, $password);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $success_message = "註冊成功，請登入";
        } else {
            $error_message = "註冊失敗，請稍後再試";
        }
    }
}

mysqli_close($conn); // 關閉連接
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bable平台，提供可愛內容與複製文分享，並支援帳號登入與註冊">
    <link rel="icon" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRu6w1L1n_jpEO94b80gNhWHTvkpCtCHvui2Q&s">
    <title>Bable</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: none;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: none;
        }
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        .user-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }
        .user-dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .user-dropdown-content a:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>
    <?php if ($error_message): ?>
    <div class="error-message" id="errorMessage"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
    <div class="success-message" id="successMessage"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <!-- Header Section -->
    <header>
        <h2 class="logo">
            <a href="#" style="text-decoration: none; color: #ffffffe0;">
                <img src="logo.webp" width="70" style="border-radius: 200px;">
                <strong>Bable</strong>
            </a>
        </h2>

        <nav class="navigation">
            <a href="#"><strong><ion-icon name="home-outline"></ion-icon>首頁</strong></a>
            <a href="#"><strong><ion-icon name="information-circle-outline"></ion-icon>關於</strong></a>
            <div class="dropdown">
                <a href="#" class="dropbtn"><strong><ion-icon name="compass-outline"></ion-icon>頁面</strong></a>
                <div class="dropdown-content">
                    <div class="dropdown-inner">
                        <div class="dropdown-column">
                            
                            <h3><a href="#">複製文查詢</a></h3>
                        </div>
                        <div class="dropdown-column">
                           
                            <h3><a href="#">圖片</a></h3>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#"><strong><ion-icon name="bulb-outline"></ion-icon>聯絡我們</strong></a>
            <?php if (isset($_SESSION["username"])): ?>
                <div class="user-dropdown">
                    <a href="#" class="dropbtn" style="color: white;">
                        <strong>
                            <ion-icon name="person-circle-outline"></ion-icon>
                            <?= htmlspecialchars($_SESSION["username"]) ?>
                        </strong>
                    </a>
                    <div class="user-dropdown-content">
                        <a href="logout.php">登出</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="btnLogin-popup"><strong>登入</strong></button>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Account Login & Registration -->
    <div class="wrapper">
        <span class="icon-close"><ion-icon name="close-outline"></ion-icon></span>
        <div class="form-box login">
            <h2>帳號登入</h2>
            <form action="index.php" method="post">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" name="email" required>
                    <label>Email信箱</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" name="password" required>
                    <label>密碼</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox">記住我</label>
                    <a href="#">忘記密碼?</a>
                </div>
                <button type="submit" name="login" class="btnnn" id="login-button">登入</button>
                <div class="login-register">
                    <p>還沒有帳號?<a href="#" class="register-link">註冊</a></p>
                </div>
            </form>
        </div>

        <div class="form-box register">
            <h2>註冊新帳號</h2>
            <form action="index.php" method="post">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                    <input type="text" name="username" required>
                    <label>使用者名稱</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" name="email" required>
                    <label>Email信箱</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" name="password" required>
                    <label>密碼</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" required>我已閱讀使用者協議和規範</label>
                </div>
                <button type="submit" name="register" class="btnnn" id="register-button">註冊</button>
                <div class="login-register">
                    <p>已經有帳號了?<a href="#" class="login-link">登入</a></p>
                </div>
            </form>
        </div>
    </div>
   
    <main>
     <!-- Welcome Animation Section -->
    <div class="welcome-container">
        <h1 class="welcome-text">Welcome to Bable!</h1>
        <br/>
        <p class="welcome-content">Effortless Content Management, One Click Away </p>
        <br/>
        <button class="look_more">啟動<ion-icon name="arrow-redo-circle-outline"></ion-icon></button>
    </div>

    <!-- New Content Section -->
    <section class="content-section">

    <h1><strong>複製文展示功能</strong></h1>
    <p>訊息清晰呈現，長訊息可折疊顯示。用戶可以使用標籤篩選訊息，並且支持複製內容的功能，方便記錄和分享</p>
    <br/>
    <?php
    // 数据库连接
    $servername = "localhost";
    $username = "root";
    $password = "27003378";
    $dbname = "dc_bot3";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // 检查连接
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }

    // 从messages表中获取content
    $sql = "SELECT content FROM messages ORDER BY RAND() LIMIT 360"; // 获取36条随机记录
    $result = $conn->query($sql);

    $contents = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $contents[] = $row["content"];
        }
    }

    $conn->close();

    // 定义一个函数来生成轮播项
    function generateCarouselItems($contents, $start, $count) {
        for ($i = $start; $i < $start + $count; $i++) {
            if (isset($contents[$i])) {
                echo '<div class="custom-carousel-item">';
                echo htmlspecialchars(substr($contents[$i], 0, 100)) . '...';
                echo '</div>';
            }
        }
    }
    ?>

    <div class="custom-carousel-container">
        <div class="custom-carousel scroll-left">
            <?php generateCarouselItems($contents, 0, 12); ?>
        </div>
    </div>
    <br/>

    <div class="custom-carousel-container">
        <div class="custom-carousel scroll-right">
            <?php generateCarouselItems($contents, 12, 12); ?>
        </div>
    </div>
    <br/>

    <div class="custom-carousel-container">
        <div class="custom-carousel scroll-left">
            <?php generateCarouselItems($contents, 24, 12); ?>
        </div>
    </div>

    <br/><br/>

    <h1><strong>圖片瀏覽功能</strong></h1>
    <p>用戶可以隨機瀏覽上傳的圖片，並通過標籤快速篩選出相關內容。點擊圖片即可放大查看，並支持上一張、下一張切換</p>
    <br/>

    <div class="unique-carousel-container">
    <div class="unique-carousel">
        <?php
        $imageDir = 'images/';
        $images = glob($imageDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        shuffle($images); // 隨機排序圖片
        foreach ($images as $image) {
            echo '<div class="unique-carousel-item">';
            echo '<img src="' . $image . '" alt="Carousel Image">';
            echo '</div>';
        }
        ?>
    </div>
    <button class="unique-prev-btn">‹</button>
    <button class="unique-next-btn">›</button>
</div>

</div>
<br/><br/><br/>

        <!-- 在這裡添加頁尾 -->
        <footer class="site-footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Bable</h3>
                    <ul>
                        <li><a href="#">概述</a></li>
                        <li><a href="#">索引</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><strong>頁面</strong></h3>
                    <ul>
                        <li><a href="#">複製文大廳</a></li>
                        <li><a href="#">可愛捏</a></li>
                        <li><a href="#">適合企業</a></li>
                        <li><a href="#">ChatGPT登入 ↗</a></li>
                        <li><a href="#">下載</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><strong>關注我們</strong></h3>
                    <ul>
                        <li><a href="#"><ion-icon name="logo-discord"></ion-icon>Discord↗</a></li>
                        <li><a href="#"><ion-icon name="logo-instagram"></ion-icon></ion-icon>Instagram↗</a></li>
                        <li><a href="#"><ion-icon name="logo-twitter"></ion-icon>Twitter↗</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><strong>公司</strong></h3>
                    <ul>
                        <li><a href="#">關於我們</a></li>
                        <li><a href="#">新聞</a></li>
                        <li><a href="#">安全性</a></li>
                        <li><a href="#">職位機會</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-links">
                    <a href="#">條款與政策</a>
                    <a href="#">隱私政策</a>
                    <a href="#">品牌指南</a>
                </div>
                <div class="footer-copyright">
                    Bable © 2024
                </div>
            </div>
        </footer>
    </section>
    </main>
    

    <!-- JavaScript Files -->
    <script src="script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.querySelector('.unique-carousel');
        const items = carousel.querySelectorAll('.unique-carousel-item');
        const prevBtn = document.querySelector('.unique-prev-btn');
        const nextBtn = document.querySelector('.unique-next-btn');
        let currentIndex = 0;
        let intervalId;
        const itemsPerSlide = 4; // 每次顯示4張圖片

        function showNextGroup() {
            currentIndex = (currentIndex + itemsPerSlide) % items.length;
            updateCarousel();
        }

        function showPrevGroup() {
            currentIndex = (currentIndex - itemsPerSlide + items.length) % items.length;
            updateCarousel();
        }

        function updateCarousel() {
            const itemWidth = items[0].offsetWidth;
            const offset = currentIndex * itemWidth;
            carousel.style.transform = `translateX(-${offset}px)`;
        }

        function startAutoPlay() {
            intervalId = setInterval(showNextGroup, 3000); // 每3秒自動切換4張圖片
        }

        function stopAutoPlay() {
            clearInterval(intervalId);
        }

        // 開始自動播放
        startAutoPlay();

        // 添加按鈕事件監聽器
        prevBtn.addEventListener('click', () => {
            stopAutoPlay();
            showPrevGroup();
            startAutoPlay();
        });

        nextBtn.addEventListener('click', () => {
            stopAutoPlay();
            showNextGroup();
            startAutoPlay();
        });

        // 滑鼠懸停時停止自動播放，離開時恢復
        carousel.addEventListener('mouseenter', stopAutoPlay);
        carousel.addEventListener('mouseleave', startAutoPlay);
    });
    </script>
    <script>
    // 在頁面加載完成後顯示錯誤訊息
    window.onload = function() {
        var errorMessage = document.getElementById('errorMessage');
        var successMessage = document.getElementById('successMessage');
        if (errorMessage) {
            errorMessage.style.display = 'block';
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 5000); // 5秒後隱藏錯誤訊息
        }
        if (successMessage) {
            successMessage.style.display = 'block';
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 5000); // 5秒後隱藏成功訊息
        }
    }
    </script>
</body>

</html>