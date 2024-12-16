<?php
header('Content-Type: application/json');

function sendError($message) {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('非法访问');
    }

    if (!isset($_POST['collection_id']) || !isset($_POST['name']) || !isset($_FILES['file'])) {
        sendError('缺少必要参数');
    }

    $collection_id = $_POST['collection_id'];
    $name = trim($_POST['name']);
    $file = $_FILES['file'];

    if (empty($name)) {
        sendError('请输入姓名');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                sendError('文件超过系统允许的最大大小');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                sendError('文件超过表单允许的最大大小');
                break;
            case UPLOAD_ERR_PARTIAL:
                sendError('文件只有部分被上传');
                break;
            case UPLOAD_ERR_NO_FILE:
                sendError('没有文件被上传');
                break;
            default:
                sendError('文件上传出错（错误码：' . $file['error'] . '）');
        }
    }

    // 验证集合是否存在
    if (!file_exists('data.json')) {
        sendError('系统错误：数据文件不存在');
    }

    $data = json_decode(file_get_contents('data.json'), true);
    if ($data === null) {
        sendError('系统错误：数据文件损坏');
    }

    $collection = null;
    foreach ($data['collections'] as $col) {
        if ($col['id'] === $collection_id) {
            $collection = $col;
            break;
        }
    }

    if (!$collection) {
        sendError('收集页面不存在');
    }

    // 检查是否过期
    if (strtotime($collection['deadline']) < time()) {
        sendError('该收集已截止');
    }

    // 检查文件扩展名
    $allowed_extensions = array_map('trim', explode(',', strtolower($collection['allowed_extensions'])));
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        sendError('不支持的文件格式。允许的格式：' . $collection['allowed_extensions']);
    }

    // 创建上传目录
    $upload_dir = 'uploads/' . $collection_id . '/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            sendError('系统错误：无法创建上传目录');
        }
    }

    // 检查是否存在同名用户的文件并删除
    $existing_files = scandir($upload_dir);
    foreach ($existing_files as $existing_file) {
        if ($existing_file != '.' && $existing_file != '..') {
            $existing_name = pathinfo($existing_file, PATHINFO_FILENAME);
            if ($existing_name === $name) {
                if (!unlink($upload_dir . $existing_file)) {
                    sendError('系统错误：无法删除旧文件');
                }
                break;
            }
        }
    }

    // 保存新文件
    $new_filename = $name . '.' . $file_extension;
    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        sendError('文件保存失败，请重试');
    }

    // 返回成功信息
    echo json_encode([
        'success' => true,
        'message' => '文件上传成功'
    ]);

} catch (Exception $e) {
    sendError('系统错误：' . $e->getMessage());
} 