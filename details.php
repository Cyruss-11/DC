<?php
if (!isset($_GET['id'])) {
    die('未指定收集ID');
}

$collection_id = $_GET['id'];
$data = json_decode(file_get_contents('data.json'), true);
$collection = null;

foreach ($data['collections'] as $col) {
    if ($col['id'] === $_GET['id']) {
        $collection = $col;
        break;
    }
}

if (!$collection) {
    die('收集页面不存在');
}

$upload_dir = 'uploads/' . $collection_id . '/';
$files = [];
if (file_exists($upload_dir)) {
    $all_files = scandir($upload_dir);
    foreach ($all_files as $file) {
        if ($file != '.' && $file != '..') {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $files[] = [
                'name' => $name,
                'file' => $file,
                'extension' => $ext,
                'time' => date("Y-m-d H:i:s", filemtime($upload_dir . $file))
            ];
        }
    }
}

// 处理修改请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    foreach ($data['collections'] as &$col) {
        if ($col['id'] === $collection_id) {
            $col['collector'] = $_POST['collector'];
            $col['deadline'] = $_POST['deadline'];
            $col['announcement_title'] = $_POST['announcement_title'];
            $col['announcement'] = $_POST['announcement'];
            break;
        }
    }
    file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));
    // 刷新页面以显示更新后的数据
    header("Location: details?id=" . $collection_id);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($collection['title']); ?> - 详情</title>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <style>
        .detail-page {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #edf2f7;
        }

        .page-title {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .collection-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.2rem;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: #2d3748;
            font-weight: 500;
        }

        .announcement {
            background: #fff8e6;
            border: 1px solid #ffd480;
            border-radius: 8px;
            padding: 1.2rem;
            margin: 1.5rem 0;
        }

        .submissions {
            margin-top: 2rem;
        }

        .submissions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .submissions-count {
            background: #4299e1;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 999px;
            font-size: 0.9rem;
        }

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .submissions-table th {
            background: #f7fafc;
            padding: 1rem;
            text-align: left;
            color: #4a5568;
            font-weight: 500;
            border-bottom: 2px solid #edf2f7;
        }

        .submissions-table td {
            padding: 1rem;
            border-bottom: 1px solid #edf2f7;
            color: #4a5568;
        }

        .submissions-table tr:hover {
            background: #f7fafc;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #48bb78;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .download-btn:hover {
            background: #38a169;
            transform: translateY(-1px);
        }

        .no-submissions {
            text-align: center;
            padding: 3rem;
            color: #718096;
            background: #f7fafc;
            border-radius: 8px;
            border: 2px dashed #e2e8f0;
        }

        .back-link {
            margin-top: 2rem;
            text-align: center;
        }

        .back-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: #4299e1;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-link a:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }

        .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .edit-btn:hover {
            background: #3182ce;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #edf2f7;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #2d3748;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .announcement-section {
            background: #fff8e6;
            border: 1px solid #ffd480;
            border-radius: 8px;
            padding: 1.2rem;
            margin: 1.5rem 0;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .announcement-title {
            font-weight: 500;
            color: #2d3748;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="detail-page">
            <div class="page-header">
                <h1 class="page-title"><?php echo htmlspecialchars($collection['title']); ?></h1>
            </div>
            
            <div class="collection-info">
                <div class="info-card">
                    <div class="info-label">收集者</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($collection['collector']); ?>
                        <button onclick="openEditModal()" class="edit-btn" style="float: right; padding: 0.3rem 0.8rem;">
                            <span>✏️</span>
                            编辑
                        </button>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-label">创建时间</div>
                    <div class="info-value"><?php echo $collection['created_at']; ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">截止时间</div>
                    <div class="info-value"><?php echo $collection['deadline']; ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">允许的文件格式</div>
                    <div class="info-value"><?php echo $collection['allowed_extensions']; ?></div>
                </div>
            </div>
            
            <?php if ($collection['announcement']): ?>
            <div class="announcement-section">
                <div class="announcement-header">
                    <div class="announcement-title">
                        <?php echo htmlspecialchars($collection['announcement_title'] ?? '公告'); ?>
                    </div>
                </div>
                <div class="announcement-content">
                    <p><?php echo nl2br(htmlspecialchars($collection['announcement'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="submissions">
                <div class="submissions-header">
                    <h2>提交记录</h2>
                    <span class="submissions-count"><?php echo count($files); ?> 份文件</span>
                </div>
                
                <?php if (empty($files)): ?>
                <div class="no-submissions">
                    <p>暂无提交记录</p>
                </div>
                <?php else: ?>
                <table class="submissions-table">
                    <thead>
                        <tr>
                            <th>提交时间</th>
                            <th>姓名</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td><?php echo $file['time']; ?></td>
                            <td><?php echo htmlspecialchars($file['name']); ?></td>
                            <td>
                                <a href="<?php echo $upload_dir . $file['file']; ?>" 
                                   class="download-btn" download>
                                    <span>📥</span>
                                    下载文件
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <div class="back-link">
                <a href="admin">
                    <span>←</span>
                    返回管理后台
                </a>
            </div>
        </div>
    </div>

    <!-- 添加编辑模态框 -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">修改信息</h2>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                
                <div class="form-group">
                    <label class="form-label">收集者：</label>
                    <input type="text" name="collector" class="form-input" 
                           value="<?php echo htmlspecialchars($collection['collector']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">截止时间：</label>
                    <input type="datetime-local" name="deadline" class="form-input" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($collection['deadline'])); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">公告标题：</label>
                    <input type="text" name="announcement_title" class="form-input" 
                           value="<?php echo htmlspecialchars($collection['announcement_title'] ?? '公告'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">公告内容：</label>
                    <textarea name="announcement" class="form-input" style="min-height: 100px; resize: vertical;"
                    ><?php echo htmlspecialchars($collection['announcement']); ?></textarea>
                </div>
                
                <div class="form-group" style="text-align: center; margin-top: 2rem;">
                    <button type="submit" class="edit-btn">
                        <span>💾</span>
                        保存修改
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal() {
        document.getElementById('editModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // 点击模态框外部关闭
    window.onclick = function(event) {
        var modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeEditModal();
        }
    }
    </script>
</body>
</html> 