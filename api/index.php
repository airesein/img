<?php
// 配置设置
$category_dir = '../categories'; // 分类目录
$default_category = 'PC'; // 默认分类

// 获取请求的分类参数
$requested_categories = isset($_GET['category']) ? 
    explode(',', $_GET['category']) : 
    [$default_category];

// 获取所有可用分类
$categories = [];
$files = scandir($category_dir);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
        $categories[] = pathinfo($file, PATHINFO_FILENAME);
    }
}

// 筛选有效的分类
$valid_categories = array_intersect($requested_categories, $categories);
if (empty($valid_categories)) $valid_categories = [$default_category];

// 随机选择一个有效分类
$selected_category = $valid_categories[array_rand($valid_categories)];
$file_path = $category_dir . '/' . $selected_category . '.txt';

// 读取分类文件中的图片URL
$images = file_exists($file_path) ? 
    file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : 
    [];

// 随机选择一张图片或使用备用图片
$random_image = !empty($images) ? 
    $images[array_rand($images)] : 
    'https://previewengine.zoho.com.cn/image/WD/kpgnr3594a400711745ad934ebc7146c1059c';

// 跳转到图片地址
header('Referrer-Policy: no-referrer');
header('Location: ' . $random_image);
exit();