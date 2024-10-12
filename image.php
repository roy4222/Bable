<?php
$servername = "localhost";
$username = "root";
$password = "27003378";
$dbname = "dc_bot2";

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 取得標籤篩選與編號搜尋的選項
$filterTag = isset($_GET['tag']) ? $_GET['tag'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 根據標籤篩選或編號搜尋圖片
$sql = "SELECT image_name, tags FROM images WHERE 1";
if ($filterTag) {
    $sql .= " AND FIND_IN_SET('$filterTag', tags)";
}

if ($search) {
    $sql .= " AND image_name LIKE '%$search%'"; // 編號搜尋
}

// 隨機排序圖片
$sql .= " ORDER BY RAND()";
$result = $conn->query($sql);

// 抓取所有圖片名稱
$imageData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imageData[] = $row;
    }
}

// 取得所有唯一標籤
$tagResult = $conn->query("SELECT DISTINCT tags FROM images");
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
    <title>相片瀏覽器</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
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
            justify-content: center;
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

    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">圖片展示</h1>

        <!-- 搜尋編號表單 -->
        <form method="GET" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="搜尋圖片編號..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">搜尋</button>
            </div>
        </form>

        <!-- 標籤篩選區域 -->
        <div class="mb-4">
            <h5>篩選標籤：</h5>
            <?php
            foreach ($allTags as $tag) {
                echo "<a href='?tag=" . urlencode($tag) . "' class='tag'>$tag</a>";
            }
            ?>
        </div>

        <div class="row" data-aos="fade-up" data-masonry='{ "percentPosition": true }'>
            <?php
            foreach ($imageData as $index => $data) {
                $imageName = $data['image_name'];
                $tags = $data['tags'];

                echo "<div class='col-md-4' data-aos='fade-up'>"; // 每個圖片使用 col-md-4
                echo "<div class='card mb-4'>";
                echo "<img src='images/$imageName' class='card-img-top' alt='$imageName' data-index='$index'>"; // 圖片顯示
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
    </div>

    <!-- 模態框 -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">查看圖片</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="選擇的圖片" class="modal-img">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="prevImage">上一張</button>
                    <button type="button" class="btn btn-secondary" id="nextImage">下一張</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap 和 AOS 效果 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <!-- 啟用 AOS 滾動動畫 -->
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
            modalImage.src = 'images/' + imageData[currentIndex].image_name;
            modalLabel.textContent = imageData[currentIndex].image_name;
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
    </script>
</body>

</html>
