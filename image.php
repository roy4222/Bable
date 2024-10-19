<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "27003378";
$dbname = "dc_bot2";

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 設置每頁顯示的圖片數量
$imagesPerPage = 51;

// 添加刪除功能
if (isset($_POST['delete']) && isset($_POST['image_name'])) {
    $imageToDelete = $_POST['image_name'];
    $deleteSQL = "DELETE FROM images WHERE image_name = ?";
    $stmt = $conn->prepare($deleteSQL);
    $stmt->bind_param("s", $imageToDelete);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
}

// 獲取排序方式
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// 獲取當前頁碼，如果沒有設置則默認為第1頁
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// 獲取總圖片數
$totalImages = $conn->query("SELECT COUNT(*) as total FROM images")->fetch_assoc()['total'];

// 計算總頁數
$totalPages = ceil($totalImages / $imagesPerPage);

// 確保當前頁碼在有效範圍內
$currentPage = max(1, min($currentPage, $totalPages));

// 計算 OFFSET
$offset = ($currentPage - 1) * $imagesPerPage;

// 根據排序方式選擇SQL查詢
if ($sortBy === 'time') {
    $sql = "SELECT image_name, tags, message_time FROM images ORDER BY message_time DESC LIMIT $offset, $imagesPerPage";
} else {
    $sql = "SELECT image_name, tags, message_time FROM images ORDER BY RAND() LIMIT $imagesPerPage";
}

$result = $conn->query($sql);
$imageData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imageData[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>相片瀏覽器</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="style.css">
    <style>

        body {
            background-color: rgb(36, 46, 54); /* 設定背景顏色 */
            color: black; /* 可選：設置文字顏色為白色，方便閱讀 */
        }

        h1{
           color: #ffdd57; 
        }
        
        .card-img-top {
            width: 100%;
            height: 500px;
            object-fit: cover;
            object-position: top; /* 將圖片對齊到容器的頂部 */
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }

        .card-img-top:hover {
            transform: scale(1.03);
        }

        .modal-dialog {
            max-width: 80%; /* 設置模態框最大寬度為80% */
            width: 100%;
        }

        .modal-img {
            width: 100%; /* 圖片佔據模態框的寬度 */
            height: auto; /* 根據寬度自動調整圖片高度，保持比例 */
            max-height: 80vh; /* 設置圖片的最大高度為螢幕高度的80%，防止圖片過高 */
            object-fit: contain; /* 保持圖片完整顯示，且不會被裁切 */
            object-position: center; /* 讓圖片在框內居中顯示 */
        }


        .modal-footer {
            justify-content: space-between;
        }

        .tag {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 3px 8px;
            margin-right: 5px;
            border-radius: 5px;
            font-size: 12px;
            color: #007bff;
            cursor: pointer;
        }

        .tag:hover {
            text-decoration: underline;
        }

        .card-title {
            display: none; /* 隱藏圖片編號 */
        }

        .card-body {
            padding: 0rem; /* 減少卡片內容的內邊距 */
        }

        .card-text {
            margin-bottom: 0; /* 移除標籤底部的邊距 */
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
        <h1 class="text-center mb-4">圖片展示</h1>
        
        <!-- 添加總數據筆數顯示 -->
        <p class="text-center mb-4" style="color: #ffdd57;">總共有 <?php echo $totalImages; ?> 筆資料</p>

        <!-- 排序選擇 -->
        <div class="mb-4 text-center">
            <a href="?sort=random" class="btn btn-primary <?php echo $sortBy === 'random' ? 'active' : ''; ?>">隨機排序</a>
            <a href="?sort=time" class="btn btn-primary <?php echo $sortBy === 'time' ? 'active' : ''; ?>">按時間排序</a>
        </div>

        <div class="row" data-aos="fade-up" data-masonry='{ "percentPosition": true }'>
            <?php
            foreach ($imageData as $index => $data) {
                $imageName = $data['image_name'];
                $tags = $data['tags'];
                $messageTime = $data['message_time'];

                echo "<div class='col-md-4' data-aos='fade-up'>";
                echo "<div class='card mb-4'>";
                echo "<img src='images/$imageName' class='card-img-top' alt='$imageName' data-index='$index'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>$imageName</h5>";
                echo "<p class='card-text'>";

                // 顯示標籤
                $tagsArray = explode(',', $tags);
                foreach ($tagsArray as $tag) {
                    echo "<span class='badge bg-secondary me-1'>$tag</span>";
                }

                echo "</p>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>

        <!-- 修改分頁導航部分 -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

    </div>

    <!-- 修改模態框 -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">查看圖片</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="選擇的圖片" class="modal-img">
                    <p id="modalMessageTime" class="mt-2"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="prevImage">上一張</button>
                    <button type="button" id="deleteButton" class="btn btn-danger">刪除</button>
                    <button type="button" class="btn btn-secondary" id="nextImage">下一張</button>
                </div>
            </div>
        </div>
    </div>

    

    <!-- 引入 Bootstrap、AOS 效果和其他 JavaScript 文件 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
        });

        const imageData = <?php echo json_encode($imageData); ?>;
        let currentIndex = 0;

        // 當點擊圖片時，打開模態框並顯示對應的圖片
        document.querySelectorAll('.card-img-top').forEach((img, index) => {
            img.addEventListener('click', function () {
                currentIndex = parseInt(this.getAttribute('data-index'));
                updateModalImage();
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            });
        });

        // 更新模態框中的圖片
        function updateModalImage() {
            const modalImage = document.getElementById('modalImage');
            const modalLabel = document.getElementById('imageModalLabel');
            const modalMessageTime = document.getElementById('modalMessageTime');
            modalImage.src = 'images/' + imageData[currentIndex].image_name;
            modalLabel.textContent = imageData[currentIndex].image_name;
            modalMessageTime.textContent = '發布時間: ' + imageData[currentIndex].message_time;
        }

        // 下一張圖片
        document.getElementById('nextImage').addEventListener('click', function () {
            currentIndex = (currentIndex + 1) % imageData.length;
            updateModalImage();
        });

        // 上一張圖片
        document.getElementById('prevImage').addEventListener('click', function () {
            currentIndex = (currentIndex - 1 + imageData.length) % imageData.length;
            updateModalImage();
        });

        // 刪除圖片
        document.getElementById('deleteButton').addEventListener('click', function () {
            const imageName = imageData[currentIndex].image_name;
            if (confirm('確定要刪除這張圖片嗎？')) {
                fetch('image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'delete=1&image_name=' + encodeURIComponent(imageName)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 從頁面和數據中移除圖片
                        const imageElement = document.querySelector(`img[alt="${imageName}"]`);
                        if (imageElement) {
                            imageElement.closest('.col-md-4').remove();
                        }
                        imageData.splice(currentIndex, 1);
                        if (imageData.length === 0) {
                            location.reload(); // 如果沒有更多圖片，刷新頁面
                        } else {
                            currentIndex = currentIndex % imageData.length;
                            updateModalImage();
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
