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
</head>

<body>

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
            <button class="btnLogin-popup"><strong>登入</strong></button>
        </nav>
    </header>

    <!-- Account Login & Registration -->
    <div class="wrapper">
        <span class="icon-close"><ion-icon name="close-outline"></ion-icon></span>
        <div class="form-box login">
            <h2>帳號登入</h2>
            <form action="#">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="email" required>
                    <label>Email信箱</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" required>
                    <label>密碼</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox">記住我</label>
                    <a href="#">忘記密碼?</a>
                </div>
                <button type="submit" class="btnnn">登入</button>
                <div class="login-register">
                    <p>還沒有帳號?<a href="#" class="register-link">註冊</a></p>
                </div>
            </form>
        </div>

        <div class="form-box register">
            <h2>註冊新帳號</h2>
            <form action="#">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                    <input type="text" required>
                    <label>使用者名稱</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></ion-icon></span>
                    <input type="email" required>
                    <label>Email信箱</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" required>
                    <label>密碼</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox">我已閱讀使用者協議和規範</label>
                </div>
                <button type="submit" class="btnnn">註冊</button>
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
</body>

</html>