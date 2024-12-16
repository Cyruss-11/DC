<?php
    session_start();
    if (!file_exists('data.json')) {
        file_put_contents('data.json', '{"collections":[]}');
    }

    $data = json_decode(file_get_contents('data.json'), true);

    // 处理删除请求
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id_to_delete = $_POST['collection_id'];
        foreach ($data['collections'] as $key => $collection) {
            if ($collection['id'] === $id_to_delete) {
                unset($data['collections'][$key]);
                // 重新索引数组
                $data['collections'] = array_values($data['collections']);
                file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));
                // 删除对应的上传文件夹
                array_map('unlink', glob("uploads/$id_to_delete/*.*"));
                rmdir("uploads/$id_to_delete");
                break;
            }
        }
        header('Location: admin.php');
        exit;
    }

    // 处理创建新收集页面的请求
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
        // 生成4位随机字符串（小写字母+数字）
        function generateRandomId() {
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $id = '';
            for ($i = 0; $i < 4; $i++) {
                $id .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $id;
        }

        // 确保生成的ID是唯一的
        do {
            $newId = generateRandomId();
            $idExists = false;
            foreach ($data['collections'] as $collection) {
                if ($collection['id'] === $newId) {
                    $idExists = true;
                    break;
                }
            }
        } while ($idExists);

        $newCollection = [
            'id' => $newId,  // 使用新生成的4位ID
            'title' => $_POST['title'],
            'collector' => $_POST['collector'],
            'deadline' => $_POST['deadline'],
            'announcement' => $_POST['announcement'],
            'announcement_title' => $_POST['announcement_title'] ?? '公告',
            'allowed_extensions' => $_POST['allowed_extensions'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data['collections'][] = $newCollection;
        file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));
        
        if (!file_exists('uploads/' . $newCollection['id'])) {
            mkdir('uploads/' . $newCollection['id'], 0777, true);
        }
        
        header('Location: admin.php');
        exit;
    }
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>作业收集系统 - 管理后台</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="UTF-8">
        <style>
            .admin-header {
                background: #2c3e50;
                color: white;
                padding: 2rem 0;
                margin-bottom: 2rem;
                text-align: center;
            }

            .admin-container {
                max-width: 1000px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .create-form {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                padding: 2rem;
                margin-bottom: 2rem;
            }

            .create-form h2 {
                color: #2c3e50;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #edf2f7;
            }

            .form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                color: #4a5568;
                font-weight: 500;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                width: 100%;
                padding: 0.8rem;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                font-size: 1rem;
                transition: all 0.2s;
            }

            .form-group textarea {
                min-height: 100px;
                resize: vertical;
            }

            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                outline: none;
                border-color: #4299e1;
                box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
            }

            .collections-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
                margin-top: 2rem;
            }

            .collection-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                padding: 1.5rem;
                transition: transform 0.2s;
            }

            .collection-card:hover {
                transform: translateY(-2px);
            }

            .collection-card h3 {
                color: #2d3748;
                margin-bottom: 1rem;
                font-size: 1.2rem;
            }

            .collection-meta {
                color: #718096;
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }

            .collection-link {
                color: #4299e1;
                text-decoration: none;
                font-size: 0.9rem;
                word-break: break-all;
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                margin-top: 1rem;
            }

            .action-btn {
                flex: 1;
                padding: 0.6rem;
                border: none;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                text-align: center;
                text-decoration: none;
                transition: all 0.2s;
            }

            .detail-btn {
                background: #4299e1;
                color: white;
            }

            .detail-btn:hover {
                background: #3182ce;
            }

            .download-btn {
                background: #48bb78;
                color: white;
            }

            .download-btn:hover {
                background: #38a169;
            }

            .delete-btn {
                background: #f56565;
                color: white;
            }

            .delete-btn:hover {
                background: #e53e3e;
            }

            .create-btn {
                background: #4299e1;
                color: white;
                padding: 0.8rem 2rem;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.2s;
            }

            .create-btn:hover {
                background: #3182ce;
                transform: translateY(-1px);
            }

            .section-title {
                color: #2d3748;
                margin: 2rem 0 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 2px solid #edf2f7;
            }

            .collection-link-group {
                display: flex;
                gap: 0.5rem;
                margin: 1rem 0;
            }

            .link-btn {
                flex: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.6rem;
                border: none;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
                background: #edf2f7;
                color: #4a5568;
            }

            .link-btn:hover {
                background: #e2e8f0;
            }

            .link-btn .icon {
                font-size: 1.1rem;
            }

            #copy-success-toast {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #48bb78;
                color: white;
                padding: 0.8rem 1.5rem;
                border-radius: 6px;
                font-size: 0.9rem;
                display: none;
                z-index: 1000;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>
    <body>
        <div class="admin-header">
            <div class="admin-container">
                <h1>作业收集系统管理后台</h1>
            </div>
        </div>

        <div class="admin-container">
            <div class="create-form">
                <h2>创建新的收集页面</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>标题：</label>
                            <input type="text" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label>收集者：</label>
                            <input type="text" name="collector" required>
                        </div>
                        
                        <div class="form-group">
                            <label>截止时间：</label>
                            <input type="datetime-local" name="deadline" required>
                        </div>
                        
                        <div class="form-group">
                            <label>允许的文件格式：</label>
                            <select name="allowed_extensions" id="format-select" required>
                                <option value="doc,docx,zip">Word文档 (doc,docx,zip)</option>
                                <option value="xls,xlsx,zip">Excel表格 (xls,xlsx,zip)</option>
                                <option value="ppt,pptx,zip">PPT演示文稿 (ppt,pptx,zip)</option>
                                <option value="custom">自定义格式</option>
                            </select>
                            <input type="text" name="custom_extensions" id="custom-extensions" 
                                placeholder="自定义格式（用逗号分隔）" style="display: none;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>公告：</label>
                        <textarea name="announcement"></textarea>
                    </div>
                    
                    <div class="form-group" style="text-align: center;">
                        <button type="submit" class="create-btn">创建收集页面</button>
                    </div>
                </form>
            </div>
            
            <h2 class="section-title">现有收集页面</h2>
            <div class="collections-grid">
                <?php foreach ($data['collections'] as $collection): ?>
                <div class="collection-card">
                    <h3><?php echo htmlspecialchars($collection['title']); ?></h3>
                    <div class="collection-meta">
                        <p>创建时间：<?php echo $collection['created_at']; ?></p>
                        <p>截止时间：<?php echo $collection['deadline']; ?></p>
                    </div>
                    <div class="collection-link-group">
                        <button class="link-btn" onclick="copyLink('<?php echo $collection['id']; ?>')">
                            <span class="icon">📋</span>
                            复制链接
                        </button>
                        <a href="./?id=<?php echo $collection['id']; ?>" class="link-btn" target="_blank">
                            <span class="icon">🔗</span>
                            打开页面
                        </a>
                    </div>
                    <div class="action-buttons">
                        <a href="details?id=<?php echo $collection['id']; ?>" class="action-btn detail-btn">查看详情</a>
                        <a href="download?id=<?php echo $collection['id']; ?>" class="action-btn download-btn">下载文件</a>
                        <form method="POST" style="flex: 1;" onsubmit="return confirm('确定要删除这个收集页面吗？');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="collection_id" value="<?php echo $collection['id']; ?>">
                            <button type="submit" class="action-btn delete-btn">删除</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="copy-success-toast">链接已复制到剪贴板</div>

        <script>
        document.getElementById('format-select').addEventListener('change', function() {
            var customInput = document.getElementById('custom-extensions');
            if (this.value === 'custom') {
                customInput.style.display = 'block';
                customInput.required = true;
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
            }
        });

        function copyLink(id) {
            // 构建完整的URL
            const baseUrl = window.location.origin;
            const link = `${baseUrl}/./?id=${id}`;
            
            // 创建临时输入框来复制链接
            const tempInput = document.createElement('input');
            tempInput.value = link;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // 显示提示
            const toast = document.getElementById('copy-success-toast');
            toast.style.display = 'block';
            
            // 2秒后隐藏提示
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2000);
        }
        </script>
    </body>
    </html> 