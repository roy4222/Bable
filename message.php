<?php

session_start(); 

$servername = "localhost";
$username = "root";
$password = "27003378";
$dbname = "dc_bot3";

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 取得搜尋詞與標籤篩選
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filterTag = isset($_GET['tag']) ? $_GET['tag'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// 設定每頁顯示的文章數量
$itemsPerPage = 51;

// 獲取當前頁碼
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// 計算 OFFSET
$offset = ($page - 1) * $itemsPerPage;

// 獲取排序方式
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// 修改 SQL 查詢
$sql = "SELECT author, content, message_time, tags FROM messages WHERE 1";

// 根據搜尋內容篩選
if ($search) {
    $sql .= " AND (content LIKE '%$search%' OR author LIKE '%$search%')";
}

// 根據標籤篩選
if ($filterTag) {
    $sql .= " AND FIND_IN_SET('$filterTag', tags)";
}

// 根據時間範圍篩選
if ($startDate && $endDate) {
    $sql .= " AND message_time BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}

// 根據選擇的排序方式修改 SQL 查詢
switch ($sortBy) {
    case 'time_desc':
        $sql .= " ORDER BY message_time DESC";
        break;
    case 'time_asc':
        $sql .= " ORDER BY message_time ASC";
        break;
    case 'random':
    default:
        $sql .= " ORDER BY RAND()";
        break;
}

// 計算總記錄數
$countResult = $conn->query($sql);
$totalItems = $countResult->num_rows;

// 計算總頁數
$totalPages = ceil($totalItems / $itemsPerPage);

// 添加 LIMIT 和 OFFSET 到 SQL 查詢
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

$result = $conn->query($sql);

// 取得篩選結果的筆數
$resultCount = $result->num_rows;

// 取得所有唯一的標籤
$tagResult = $conn->query("SELECT DISTINCT tags FROM messages");
$allTags = [];

while ($tagRow = $tagResult->fetch_assoc()) {
    $tagsArray = explode(',', $tagRow['tags']);
    foreach ($tagsArray as $tag) {
        $tag = trim($tag);
        if (!in_array($tag, $allTags)) {
            $allTags[] = $tag;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRu6w1L1n_jpEO94b80gNhWHTvkpCtCHvui2Q&s">
    <title>訊息展示</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- AOS (Animate On Scroll) for scroll animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"/>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Masonry.js for dynamic layout -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"></script>
    <!-- imagesLoaded for ensuring all images are loaded before layout -->
    <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
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
            <a href="index.php"><strong><ion-icon name="home-outline" ></ion-icon>首頁</strong></a>
            <a href="#"><strong><ion-icon name="information-circle-outline" ></ion-icon>關於</strong></a>
            <div class="dropdownb">
                <a href="#"><strong><ion-icon name="compass-outline"></ion-icon>頁面</strong></a>
                <div class="dropdownb-content">
                    <a href="message.php">複製文</a>
                    <a href="image.php">可愛捏</a>
                    <a href="#">這我</a>
                    <a href="#">三小啦</a>
                </div>
            </div>
            <a href="#"><strong><ion-icon name="bulb-outline"></ion-icon>聯絡我們</strong></a>
            <?php if (isset($_SESSION["username"])): ?>
                <div class="user-dropdown">
                    <a href="#" class="dropbtn">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <span><?= htmlspecialchars($_SESSION["username"]) ?></span>
                    </a>
                    <div class="user-dropdown-content">
                        <a href="#">設定</a>
                        <a href="logout.php">登出</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="btnLogin-popup"><strong>登入</strong></button>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container mt-5">
        <h1><strong>訊息展示</strong></h1>
        <h1 class="text-center mb-4 animate__animated animate__fadeInDown" style="color: #ffdd57;"><strong>訊息展示</strong></h1>

        <div class="row align-items-center mb-4">
            <div class="col-md-3">
                <p style="color: white; margin-bottom: 0;">篩選結果：<?php echo $totalItems; ?> 筆資料</p>
            </div>
            <div class="col-md-12">
                <form method="GET" action="" class="row g-3" id="searchForm">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="搜尋作者或內容" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="tag">
                            <option value="">選擇標籤</option>
                            <?php foreach ($allTags as $tag): ?>
                                <option value="<?php echo htmlspecialchars($tag); ?>" <?php echo $filterTag === $tag ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tag); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="sort">
                            <option value="random" <?php echo $sortBy === 'random' ? 'selected' : ''; ?>>隨機排序</option>
                            <option value="time_desc" <?php echo $sortBy === 'time_desc' ? 'selected' : ''; ?>>最新上傳</option>
                            <option value="time_asc" <?php echo $sortBy === 'time_asc' ? 'selected' : ''; ?>>最舊上傳</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">搜尋</button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-secondary w-100" id="clearButton">清除</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row" id="masonry-container">
        <?php
        $maxLength = 300;
        if ($resultCount > 0) {
            while($row = $result->fetch_assoc()) {
                $content = htmlspecialchars($row["content"]);
                $isLongContent = strlen($content) > $maxLength;
                $displayContent = $isLongContent ? mb_substr($content, 0, $maxLength) . '...' : $content;

                echo "<div class='col-md-4 col-sm-6 mb-4'>";
                echo "<div class='card'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>作者: " . htmlspecialchars($row["author"]) . "</h5>";
                echo "<p class='card-text collapsible-content'>" . $displayContent . "</p>";
                echo "<p class='card-text full-content' style='display:none;'>" . $content . "</p>";
                echo "<p class='card-text'><small class='text-muted'>時間: " . htmlspecialchars($row["message_time"]) . "</small></p>";

                // 添加標籤顯示
                $tags = explode(',', $row["tags"]);
                echo "<div class='tags-container'>";
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                        echo "<span class='tag'>" . htmlspecialchars($tag) . "</span>";
                    }
                }
                echo "</div>";

                if ($isLongContent) {
                    echo "<button class='btn btn-primary toggle-button'>閱讀更多</button>";
                }
                echo "<br>";
                echo "<br>";

                echo "<button class='btn btn-success copy-button' data-content='" . htmlspecialchars($row["content"]) . "'>複製</button>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-center'>沒有找到相關的訊息</p>";
        }
        ?>
        </div>

        <!-- 分頁導航 -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filterTag ? '&tag=' . urlencode($filterTag) : ''; ?><?php echo $startDate ? '&start_date=' . urlencode($startDate) : ''; ?><?php echo $endDate ? '&end_date=' . urlencode($endDate) : ''; ?><?php echo $sortBy ? '&sort=' . urlencode($sortBy) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

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

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elem = document.querySelector('#masonry-container');
            imagesLoaded(elem, function() {
                var msnry = new Masonry(elem, {
                    itemSelector: '.col-md-4',
                    percentPosition: true
                });

                // Collapsible content script
                const toggleButtons = document.querySelectorAll('.toggle-button');
                toggleButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const cardBody = button.closest('.card-body');
                        const shortContent = cardBody.querySelector('.collapsible-content');
                        const fullContent = cardBody.querySelector('.full-content');

                        shortContent.style.display = shortContent.style.display === 'none' ? 'block' : 'none';
                        fullContent.style.display = fullContent.style.display === 'none' ? 'block' : 'none';

                        button.textContent = shortContent.style.display === 'none' ? '隱藏內容' : '閱讀更多';

                        imagesLoaded(elem, function() {
                            msnry.layout();
                        });
                    });
                });

                // 複製功能
                const copyButtons = document.querySelectorAll('.copy-button');
                copyButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const content = this.getAttribute('data-content');
                        navigator.clipboard.writeText(content).then(() => {
                            const originalText = this.textContent;
                            this.textContent = '已複製！';
                            setTimeout(() => {
                                this.textContent = originalText;
                            }, 2000);
                        }).catch(err => {
                            console.error('複製失敗：', err);
                            alert('複製失敗，請手動複製。');
                        });
                    });
                });
            });
        });
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 現有的 Masonry、collapsible content 和複製功能代碼保持不變

            // 添加清除按鈕功能
            const clearButton = document.getElementById('clearButton');
            const searchForm = document.getElementById('searchForm');

            clearButton.addEventListener('click', function() {
                // 清除搜索輸入框
                searchForm.querySelector('input[name="search"]').value = '';
                
                // 重置標籤選擇
                searchForm.querySelector('select[name="tag"]').selectedIndex = 0;
                
                // 重置排序方式
                searchForm.querySelector('select[name="sort"]').selectedIndex = 0;
                
                // 提交表單以刷新頁面
                searchForm.submit();
            });
        });
    </script>

</body>
</html>
