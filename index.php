<?php
// 配置部分
$CATEGORY_DIR = 'categories'; // 存放分类txt文件的目录
$SITE_TITLE = '图片公链存储'; // 网站标题 (此版本中不再直接显示)
$DEFAULT_CATEGORY = 'PC'; // 默认展示的分类
$THUMB_SIZE = 300; // 缩略图大小（主要用于Unsplash URL参数，非服务器端处理）

// 获取所有分类文件及其图片数量
function getCategories($dir, $defaultCategory) {
    $categories = [];
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        // 创建示例文件，使用Unsplash优化参数
        file_put_contents("$dir/nature.txt", "https://images.unsplash.com/photo-1501854140801-50d01698950b?w=1200&q=80\nhttps://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&q=80");
        file_put_contents("$dir/city.txt", "https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=1200&q=80\nhttps://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=1200&q=80");
        file_put_contents("$dir/tech.txt", "https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1200&q=80");
    }
    
    foreach (scandir($dir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
            $categoryName = pathinfo($file, PATHINFO_FILENAME);
            $imagesInFile = file("$dir/$file", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $categories[$categoryName] = [
                'path' => "$dir/$file",
                'count' => count($imagesInFile)
            ];
        }
    }
    // 如果没有分类文件，确保至少有一个默认分类文件
    if (empty($categories)) {
        file_put_contents("$dir/{$defaultCategory}.txt", "https://images.unsplash.com/photo-1501854140801-50d01698950b?w=1200&q=80");
        $categories[$defaultCategory] = [
            'path' => "$dir/{$defaultCategory}.txt",
            'count' => 1
        ];
    }
    return $categories;
}

// 根据URL生成缩略图链接（仅支持Unsplash等带URL参数的CDN）
function generateThumbnail($url, $size) {
    if (strpos($url, 'images.unsplash.com') !== false) {
        // 移除现有尺寸参数，添加新参数
        $url = preg_replace('/(\?|&)w=\d+/', '', $url);
        $url = preg_replace('/(\?|&)h=\d+/', '', $url);
        $url = preg_replace('/(\?|&)q=\d+/', '', $url);
        
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . "{$separator}w={$size}&q=75&auto=format&fit=crop";
    }
    return $url; // 非Unsplash图片直接返回原URL
}

// 获取所有分类及其图片数量
$allCategories = getCategories($CATEGORY_DIR, $DEFAULT_CATEGORY);

// 获取当前分类
$currentCategoryName = isset($_GET['category']) ? $_GET['category'] : $DEFAULT_CATEGORY;
// 确保当前分类有效，否则使用第一个可用分类
if (!isset($allCategories[$currentCategoryName])) {
    $currentCategoryName = key($allCategories);
}

// 读取当前分类的图片链接
$images = [];
if (isset($allCategories[$currentCategoryName])) {
    $images = file($allCategories[$currentCategoryName]['path'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImagesGallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Reset & Variables */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary-color: #5e72e4; /* 主色调 */
            --accent-color: #42a5f5; /* 强调色 */
            --light-color: #ffffff; /* 亮色 */
            --text-color: #344767; /* 主要文本 */
            --text-light: #7b809a; /* 辅助文本 */
            --glass-bg: rgba(255, 255, 255, 0.6); /* 玻璃背景 */
            --glass-border: 1px solid rgba(255, 255, 255, 0.7); /* 玻璃边框 */
            --shadow: 0 8px 26px -4px rgba(0, 0, 0, 0.15); /* 柔和阴影 */
            --shadow-hover: 0 15px 35px -5px rgba(0, 0, 0, 0.25); /* 悬停阴影 */
            --transition-fast: all 0.2s ease-out; /* 快速过渡 */
            --transition-normal: all 0.3s ease-out; /* 普通过渡 */
        }
        
        body {
            font-family: 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #e4e8f8, #f0f5ff);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center;
        }
        
        .container { max-width: 1400px; width: 100%; margin: 0 auto; padding: 20px; }
        
        /* Common Glassmorphism Card Style */
        .card-style {
            background: var(--glass-bg);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: var(--glass-border);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: fadeIn 0.6s ease-out forwards;
            margin: 0 auto 30px; /* 统一居中和底部间距 */
            padding: 25px;
        }

        /* Header (now just a placeholder for overall layout) */
        header { text-align: center; padding: 0; margin-bottom: 20px; } /* Reduced padding/margin */

        /* Doc Link Panel - Now full width of container's content area */
        .doc-link-panel { /* Inherits .card-style for other properties */
            /* Removed max-width to allow it to expand to container width */
        }
        .doc-link-panel p {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--text-color);
            font-weight: 500;
        }
        .doc-link-panel i {
            color: var(--primary-color);
            font-size: 1.3rem;
            background: rgba(94, 114, 228, 0.1);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .doc-link-panel a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-fast);
        }
        .doc-link-panel a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        /* Category Tabs - Now full width of container's content area */
        .category-tabs { /* Inherits .card-style for other properties */
            display: flex; justify-content: center; flex-wrap: wrap; gap: 15px;
            /* Removed max-width to allow it to expand to container width */
        }
        .category-btn {
            padding: 12px 25px; background: rgba(255,255,255,0.7); color: var(--text-color);
            border: none; border-radius: 50px; font-size: 1rem; cursor: pointer;
            transition: var(--transition-fast); font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08); text-decoration: none;
            display: flex; align-items: center;
            gap: 8px;
        }
        .category-btn:hover, .category-btn.active {
            background: var(--primary-color); color: white; transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Gallery - Already full width of container's content area */
        .gallery-container { /* Inherits .card-style for other properties */
            padding: 30px; 
            /* Removed max-width as it will now naturally expand */
        } 
        .gallery-title {
            text-align: center; font-size: 2.2rem; margin-bottom: 30px;
            color: var(--text-color); font-weight: 600; position: relative;
        }
        .gallery-title:after {
            content: ''; display: block; width: 80px; height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            margin: 10px auto 0; border-radius: 2px;
        }
        .image-gallery {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;
        }
        .image-card {
            background: var(--glass-bg); border-radius: 16px; overflow: hidden;
            transition: var(--transition-normal); box-shadow: var(--shadow);
            height: 300px; position: relative; border: var(--glass-border);
            backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
        }
        .image-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .image-container { 
            width: 100%; height: 100%; overflow: hidden; position: relative;
            cursor: pointer;
        }
        .image-container img {
            width: 100%; height: 100%; object-fit: cover; transition: var(--transition-normal);
            filter: brightness(0.95);
        }
        .image-card:hover .image-container img { transform: scale(1.05); filter: brightness(1); }
        
        /* Fallback/Error */
        .error-message {
            background: rgba(255, 107, 107, 0.1); border: 1px solid rgba(255, 107, 107, 0.3);
            border-radius: 16px; padding: 25px; text-align: center; grid-column: 1 / -1;
            color: #d63333; box-shadow: var(--shadow);
        }
        .error-message h3 { margin-bottom: 10px; font-size: 1.5rem; color: #c0392b; }
        .error-message p { font-size: 1rem; }
        .error-message i { font-size: 2rem; color: #e74c3c; margin-bottom: 15px; }

        /* Footer */
        footer {
            text-align: center; padding: 30px 0 20px; color: var(--text-light);
            font-size: 1rem; width: 100%; margin-top: auto;
        }
        .footer-links { display: flex; justify-content: center; gap: 20px; margin-top: 10px; }
        .footer-links a { color: var(--accent-color); text-decoration: none; transition: var(--transition-fast); }
        .footer-links a:hover { color: var(--primary-color); text-decoration: underline; }

        /* Modal */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9); z-index: 1000; justify-content: center; align-items: center;
            opacity: 0; transition: opacity 0.3s ease-out;
        }
        .modal.show { opacity: 1; display: flex; }
        .modal-content {
            max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 8px;
            box-shadow: 0 15px 30px -8px rgba(0,0,0,0.5);
            transform: scale(0.9); opacity: 0; transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            cursor: pointer; /* 使图片可点击关闭 */
        }
        .modal.show .modal-content { transform: scale(1); opacity: 1; }
        .modal-close {
            position: absolute; top: 20px; right: 30px; color: white; font-size: 35px;
            cursor: pointer; z-index: 1001; opacity: 0.7; transition: var(--transition-fast);
        }
        .modal-close:hover { opacity: 1; transform: rotate(90deg); }
        
        /* Toast */
        .toast {
            position: fixed; bottom: 25px; left: 50%;
            transform: translateX(-50%) translateY(15px);
            background: linear-gradient(to right, #4CAF50, #8BC34A); color: white;
            padding: 12px 25px; border-radius: 50px; font-size: 1rem; z-index: 1000;
            display: flex; align-items: center; gap: 8px;
            opacity: 0; visibility: hidden;
            transition: opacity 0.3s ease-out, transform 0.3s ease-out, visibility 0s linear 0.3s;
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }
        .toast.show {
            opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0);
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .image-gallery { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
            /* On smaller screens, allow panels to fill more width without explicit max-width */
            .doc-link-panel, .category-tabs, .gallery-container { 
                max-width: 95%; /* Adjust to fill more of the container width */
            }
        }
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .doc-link-panel, .category-tabs, .gallery-container { padding: 15px 20px; margin-bottom: 20px;}
            .doc-link-panel p { font-size: 0.95rem; }
            .doc-link-panel i { width: 30px; height: 30px; font-size: 1.1rem; }
            .category-tabs { flex-direction: column; align-items: center; gap: 10px; }
            .category-btn { width: 100%; justify-content: center; padding: 10px 20px; font-size: 0.95rem; }
            .gallery-title { font-size: 1.8rem; }
            .image-card { height: 250px; }
            .toast { padding: 10px 20px; font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header></header>
        
        <!-- 说明文档链接面板 -->
        <div class="doc-link-panel card-style">
            <p><i class="fas fa-file-alt"></i> <a href="./api/readme.html">点击查看说明文档</a></p>
        </div>

        <!-- 分类标签面板 -->
        <div class="category-tabs card-style">
            <?php foreach ($allCategories as $name => $data): ?>
                <a href="?category=<?= $name ?>" class="category-btn <?= $name === $currentCategoryName ? 'active' : '' ?>">
                    <?= ucfirst($name) ?> (<?= $data['count'] ?>)
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- 图片画廊容器 -->
        <div class="gallery-container card-style">
            <h2 class="gallery-title"><?= ucfirst($currentCategoryName) ?> 图片集</h2>
            
            <div class="image-gallery" id="imageGallery">
                <?php if (count($images) > 0): ?>
                    <?php foreach ($images as $index => $url): 
                    ?>
                        <div class="image-card">
                            <div class="image-container" 
                                 data-url="<?= htmlspecialchars($url) ?>">
                                <img 
                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='280' height='250' viewBox='0 0 280 250'%3E%3Crect width='280' height='250' fill='%23e0e0e0'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-size='16' fill='%239e9e9e'%3ELoading...%3C/text%3E%3C/svg%3E"
                                    data-src="<?= generateThumbnail($url, $THUMB_SIZE) ?>" 
                                    alt="图片 <?= $index + 1 ?>"
                                    loading="lazy"
                                >
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>抱歉，未找到图片</h3>
                        <p>当前分类**<?= ucfirst($currentCategoryName) ?>**中没有图片，或者分类文件无效。</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            <div class="footer-links">
                <a href="https://github.com/airesein/ImagesGallery"><i class="fas fa-code"></i> 开发者</a>
                <a href="https://github.com/airesein/ImagesGallery"><i class="fas fa-heart"></i> Github</a>
                
            </div>
            <p>© 2025-<span id="current-year"><?= date('Y') ?></span> ImagesGallery | All Rights Reserved | Powered by Ziworld</p>
        </footer>
    </div>
    
    <!-- 图片预览模态框 -->
    <div class="modal" id="imageModal">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>
    
    <!-- 复制成功提示 -->
    <div class="toast" id="copyToast">
        <i class="fas fa-check-circle"></i>
        <span>链接已复制到剪贴板</span>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 图片懒加载
            const lazyImages = document.querySelectorAll('img[data-src]');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.onload = () => img.style.opacity = 1;
                        img.onerror = () => img.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='280' height='250' viewBox='0 0 280 250'%3E%3Crect width='280' height='250' fill='%23f5f5f5'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-size='16' fill='%23b0b0b0'%3EError loading%3C/text%3E%3C/svg%3E";
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '0px 0px 100px 0px' });
            lazyImages.forEach(img => {
                img.style.opacity = 0;
                img.style.transition = 'opacity 0.5s ease-in-out';
                observer.observe(img);
            });
            
            // 图片点击和模态框逻辑
            const imageContainers = document.querySelectorAll('.image-container');
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const closeModal = document.querySelector('.modal-close');
            const toast = document.getElementById('copyToast');
            
            function showModal(url) {
                modalImg.src = url;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
            
            function closeModalFunction() {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
            
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 2500);
                }).catch(err => {
                    console.error('复制失败:', err);
                    alert('复制失败，请手动复制: ' + text);
                });
            }
            
            imageContainers.forEach(container => {
                container.addEventListener('click', function(e) {
                    const url = this.dataset.url;
                    if (e.button === 0) { // Left click
                        showModal(url);
                    }
                });
                container.addEventListener('contextmenu', function(e) {
                    e.preventDefault(); // Prevent default right-click menu
                    const url = this.dataset.url;
                    copyToClipboard(url);
                });
            });
            
            closeModal.addEventListener('click', closeModalFunction);
            // Modified: Clicking on the modal background OR the image itself closes the modal
            modal.addEventListener('click', (e) => {
                if (e.target === modal || e.target === modalImg) {
                    closeModalFunction();
                }
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closeModalFunction();
                }
            });

            // 动态更新页脚年份
            document.getElementById('current-year').textContent = new Date().getFullYear();
        });
    </script>
</body>
</html>