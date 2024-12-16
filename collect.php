<?php
if (!isset($_GET['id'])) {
    die('未指定收集页面ID');
}

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

$isExpired = strtotime($collection['deadline']) < time();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($collection['title']); ?> - 文件收集</title>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .collection-page {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .status-bar {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .status-icon {
            font-size: 1.2rem;
        }

        .announcement-card {
            background: #fff8e6;
            border: 1px solid #ffd480;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .announcement-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(255, 212, 128, 0.5);
        }

        .announcement-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #92400e;
        }

        .announcement-content {
            color: #78350f;
            line-height: 1.6;
        }

        .upload-section {
            margin-top: 2.5rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            color: #4a5568;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .file-upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-upload-area:hover {
            border-color: #4299e1;
            background: #ebf8ff;
        }

        .file-upload-area.has-file {
            border-style: solid;
            border-color: #48bb78;
            background: #f0fff4;
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #4a5568;
        }

        .file-upload-label {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #4299e1;
            color: white;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .file-upload-label:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }

        .file-name-display {
            color: #718096;
            margin-top: 0.8rem;
            font-size: 0.9rem;
        }

        .file-name-display.file-selected {
            color: #2d3748;
            font-weight: 500;
        }

        .file-formats {
            margin-top: 1rem;
            color: #718096;
            font-size: 0.9rem;
        }

        .submit-button {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 2rem;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .submit-button:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }

        .expired-notice {
            text-align: center;
            padding: 3rem 2rem;
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 12px;
            color: #c53030;
        }

        .expired-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 640px) {
            .collection-page {
                margin: 1rem;
                padding: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .status-bar {
                flex-direction: column;
            }

            .upload-section {
                padding: 1.5rem;
            }
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: modalPop 0.3s ease-out;
        }

        @keyframes modalPop {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: iconBounce 0.5s ease-out;
        }

        @keyframes iconBounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }

        .success-title {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .success-message {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .modal-close-btn {
            background: #4299e1;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }

        .footer {
            margin-top: 3rem;
            padding: 1.5rem 0;
            text-align: center;
            color: #718096;
            font-size: 0.9rem;
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        @media (max-width: 640px) {
            .footer {
                margin-top: 2rem;
                padding: 1rem 0;
            }
        }

        .error-modal {
            border-top: 4px solid #f56565;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #f56565;
            animation: iconShake 0.5s ease-out;
        }

        @keyframes iconShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-title {
            color: #c53030;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .error-btn {
            background: #f56565;
        }

        .error-btn:hover {
            background: #e53e3e;
        }

        .footer a:hover {
            color: #4299e1 !important;
        }
    </style>
</head>
<body>
    <div class="collection-page">
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($collection['title']); ?></h1>
            <div class="status-bar">
                <div class="status-item">
                    <span class="status-icon">👤</span>
                    <span>收集者：<?php echo htmlspecialchars($collection['collector']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-icon">⏰</span>
                    <span>截止时间：<?php echo $collection['deadline']; ?></span>
                </div>
            </div>
        </div>

        <?php if ($collection['announcement']): ?>
        <div class="announcement-card">
            <div class="announcement-header">
                <span class="status-icon">📢</span>
                <div class="announcement-title">
                    <?php echo htmlspecialchars($collection['announcement_title'] ?? '公告'); ?>
                </div>
            </div>
            <div class="announcement-content">
                <?php echo nl2br(htmlspecialchars($collection['announcement'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($isExpired): ?>
        <div class="expired-notice">
            <div class="expired-icon">⚠️</div>
            <h2>该收集已截止</h2>
            <p>当前时间已超过截止时间，无法继续提交文件</p>
        </div>
        <?php else: ?>
        <div class="upload-section">
            <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data" onsubmit="return submitForm(event)" novalidate>
                <input type="hidden" name="collection_id" value="<?php echo $collection['id']; ?>">
                
                <div class="form-group">
                    <label class="form-label">姓名</label>
                    <input type="text" name="name" class="form-input"
                           placeholder="请输入您的姓名">
                </div>
                
                <div class="form-group">
                    <label class="form-label">文件上传</label>
                    <input type="file" name="file" id="file-input" style="display: none;">
                    <div class="file-upload-area" id="upload-area" onclick="document.getElementById('file-input').click();">
                        <div class="upload-icon">📎</div>
                        <div class="file-upload-label">选择文件</div>
                        <div class="file-name-display">未选择文件</div>
                        <div class="file-formats">
                            允许的文件格式：<?php echo $collection['allowed_extensions']; ?>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="submit-button">
                        <span>📤</span>
                        提交文件
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div id="successModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="success-icon">✅</div>
            <h2 class="success-title">提交成功</h2>
            <p class="success-message">您的文件已成功上传</p>
            <button onclick="closeSuccessModal()" class="modal-close-btn">关闭</button>
        </div>
    </div>

    <div id="errorModal" class="modal" style="display: none;">
        <div class="modal-content error-modal">
            <div class="error-icon">❌</div>
            <h2 class="error-title">上传失败</h2>
            <p class="error-message"></p>
            <button onclick="closeErrorModal()" class="modal-close-btn error-btn">关闭</button>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <p>© <?php echo date('Y'); ?> <a href="https://github.com/Cyruss-11/DC/" target="_blank" style="color: #718096; text-decoration: none; transition: color 0.2s;">文件收集系统</a> - All Rights Reserved.</p>
        </div>
    </footer>

    <script>
    document.getElementById('file-input').addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : '未选择文件';
        const fileDisplay = document.querySelector('.file-name-display');
        const uploadArea = document.getElementById('upload-area');
        
        fileDisplay.textContent = fileName;
        fileDisplay.classList.add('file-selected');
        uploadArea.classList.add('has-file');
    });

    function submitForm(event) {
        event.preventDefault();
        
        const form = document.getElementById('uploadForm');
        const formData = new FormData(form);

        // 验证姓名
        const name = formData.get('name').trim();
        if (!name) {
            showErrorModal('请输入您的姓名');
            return false;
        }

        // 验证文件
        const file = formData.get('file');
        if (!file || file.size === 0) {
            showErrorModal('请选择要上传的文件');
            return false;
        }

        // 显示加载状态
        const submitBtn = form.querySelector('.submit-button');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span>⏳</span>正在上传...';
        submitBtn.disabled = true;

        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal();
                form.reset();
                const uploadArea = document.getElementById('upload-area');
                const fileDisplay = document.querySelector('.file-name-display');
                uploadArea.classList.remove('has-file');
                fileDisplay.textContent = '未选择文件';
                fileDisplay.classList.remove('file-selected');
            } else {
                showErrorModal(data.message);
            }
        })
        .catch(error => {
            showErrorModal('网络错误，请检查网络连接后重试');
        })
        .finally(() => {
            // 恢复按钮状态
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });

        return false;
    }

    function showSuccessModal() {
        document.getElementById('successModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function showErrorModal(message) {
        const modal = document.getElementById('errorModal');
        const messageElement = modal.querySelector('.error-message');
        messageElement.textContent = message;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeErrorModal() {
        document.getElementById('errorModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // 点击模态框外部关闭
    window.onclick = function(event) {
        const successModal = document.getElementById('successModal');
        const errorModal = document.getElementById('errorModal');
        if (event.target === successModal) {
            closeSuccessModal();
        }
        if (event.target === errorModal) {
            closeErrorModal();
        }
    }
    </script>
</body>
</html> 