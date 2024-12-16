<?php
if (!isset($_GET['id'])) {
    die('未指定收集ID');
}

$collection_id = $_GET['id'];
$upload_dir = 'uploads/' . $collection_id . '/';

if (!file_exists($upload_dir)) {
    die('没有找到相关文件');
}

$zip = new ZipArchive();
$zip_name = 'collection_' . $collection_id . '_' . date('YmdHis') . '.zip';

if ($zip->open($zip_name, ZipArchive::CREATE) !== TRUE) {
    die('无法创建ZIP文件');
}

$files = scandir($upload_dir);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $zip->addFile($upload_dir . $file, $file);
    }
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_name . '"');
header('Content-Length: ' . filesize($zip_name));
readfile($zip_name);
unlink($zip_name); 