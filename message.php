<?php
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

// 根據搜尋詞和標籤篩選查詢資料庫
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

// 計算總記錄數
$countResult = $conn->query($sql);
$totalItems = $countResult->num_rows;

// 計算總頁數
$totalPages = ceil($totalItems / $itemsPerPage);

// 添加 LIMIT 和 OFFSET 到 SQL 查詢
$sql .= " ORDER BY message_time DESC LIMIT $itemsPerPage OFFSET $offset";

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
    <title>訊息展示</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- AOS (Animate On Scroll) for scroll animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"/>

    <link rel="stylesheet" href="style.css">
    
    <!-- Masonry.js for dynamic layout -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"></script>



    <!-- Custom CSS for styling -->
    <style>
        /* Navbar custom styling */
        .navbar {
            background-color: #1c1c1e;
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            color: #f5f5f7;
            transition: color 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: #ffdd57;
        }

        .navbar-nav .nav-link {
            color: #f5f5f7;
            margin-left: 15px;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffdd57;
        }

        .form-control {
            background-color: #2c2c2e;
            border: none;
            color: #f5f5f7;
        }

        .form-control::placeholder {
            color: #a1a1a3;
        }

        .form-control:focus {
            background-color: #2c2c2e;
            color: #f5f5f7;
            border-color: #ffdd57;
            outline: none;
            box-shadow: 0 0 5px rgba(255, 221, 87, 0.5);
        }

        .btn-search {
            background-color: #ffdd57;
            color: #1c1c1e;
            border: none;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-search:hover {
            background-color: #f5f5f7;
            color: #1c1c1e;
        }

        /* Add subtle hover effects */
        .nav-link, .form-control, .btn-search {
            transition: all 0.3s ease;
        }

        /* Sticky Navbar effect */
        .navbar.scrolled {
            background-color: #1a1a1c;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
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
            <a href="index.php"><strong><ion-icon name="home-outline"></ion-icon>首頁</strong></a>
            <a href="#"><strong><ion-icon name="information-circle-outline"></ion-icon>關於</strong></a>
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
        <h1 class="text-center mb-4 animate__animated animate__fadeInDown">訊息展示</h1>

        <!-- 顯示篩選結果筆數 -->
        <p style="color: white;">篩選結果：<?php echo $totalItems; ?> 筆資料</p>

        <div class="row" data-masonry='{ "percentPosition": true }'>
        <?php
        $maxLength = 300; // 設定超過多少字數會隱藏內容
        if ($resultCount > 0) {
            while($row = $result->fetch_assoc()) {
                $content = htmlspecialchars($row["content"]);
                $isLongContent = strlen($content) > $maxLength; // 判斷是否為長內容
                $displayContent = $isLongContent ? mb_substr($content, 0, $maxLength) . '...' : $content; // 縮短顯示的內容

                // 顯示文章
                echo "<div class='col-md-4 col-sm-6 mb-4' data-aos='fade-up'>";
                echo "<div class='card'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>作者: " . htmlspecialchars($row["author"]) . "</h5>";
                echo "<p class='card-text collapsible-content'>" . $displayContent . "</p>";
                echo "<p class='card-text full-content' style='display:none;'>" . $content . "</p>";
                echo "<p class='card-text'><small class='text-muted'>時間: " . htmlspecialchars($row["message_time"]) . "</small></p>";

                // 顯示閱讀更多按鈕僅在長內容的情況下
                if ($isLongContent) {
                    echo "<p class='toggle-button'>閱讀更多</p>";
                }

                // 複製按鈕
                echo "<p class='copy-button' data-content='" . htmlspecialchars($row["content"]) . "'>複製</p>";

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
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filterTag ? '&tag=' . urlencode($filterTag) : ''; ?><?php echo $startDate ? '&start_date=' . urlencode($startDate) : ''; ?><?php echo $endDate ? '&end_date=' . urlencode($endDate) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- 引入 Bootstrap 和 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS 效果的引入 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="script.js"></script>
    <!-- 啟動 AOS 效果 -->
    <script>
        AOS.init({
            duration: 800, // 動畫持續時間
            easing: 'ease-in-out', // 動畫效果
            once: true, // 滾動一次後只執行一次動畫
        });
    </script>

    <!-- Masonry.js 效果 -->
    <script>
        var elem = document.querySelector('.row');
        var msnry = new Masonry( elem, {
          itemSelector: '.col-md-4',
          percentPosition: true
        });
    </script>

    <!-- Collapsible Content Script -->
    <script>
        const toggleButtons = document.querySelectorAll('.toggle-button');
        toggleButtons.forEach(button => {
            const cardBody = button.closest('.card-body');
            const shortContent = cardBody.querySelector('.collapsible-content');
            const fullContent = cardBody.querySelector('.full-content');

            // 判斷是否顯示按鈕，只有當內容被截斷時才顯示
            if (shortContent.textContent.length !== fullContent.textContent.length) {
                button.style.display = 'block';
            }

            button.addEventListener('click', () => {
                shortContent.style.display = shortContent.style.display === 'none' ? 'block' : 'none';
                fullContent.style.display = fullContent.style.display === 'none' ? 'block' : 'none';

                button.textContent = shortContent.style.display === 'none' ? '隱藏內容' : '閱讀更多';
                
                // 重新布局 Masonry
                msnry.layout();
            });
        });
    </script>

    <!-- 複製按鈕功能 -->
    <script>
        const copyButtons = document.querySelectorAll('.copy-button');
        copyButtons.forEach(button => {
            button.addEventListener('click', () => {
                const content = button.getAttribute('data-content');
                navigator.clipboard.writeText(content).then(() => {
                    // 修改按鈕文本顯示「複製成功」
                    button.textContent = '複製成功';
                    button.style.color = '#006030';  // 更改按鈕顏色為綠色
                }).catch(err => {
                    console.error('複製失敗', err);
                });
            });
        });
    </script>

    <!-- Sticky Navbar on Scroll -->
    <script>
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>

    <!-- 在 </body> 標籤之前添加以下腳本 -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
