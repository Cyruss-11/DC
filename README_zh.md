<div align="center">

# 📚 文件收集系统

[English](README.md) | 中文

一个简单高效的文件收集系统，适用于作业提交、文件收集等场景。

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.0-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-demo-orange.svg)](https://github.com)

</div>

## ✨ 功能特点

### 🔧 管理后台
- **创建收集页面**
  - 设置标题、收集者、截止时间
  - 自定义公告内容和标题
  - 限制文件格式（支持自定义）
  - 自动生成4位随机短链接
- **管理功能**
  - 查看所有收集页面
  - 一键复制/打开收集链接
  - 查看提交详情
  - 下载所有文件
  - 删除收集页面
  - 修改收集信息

### 📝 收集页面
- **信息展示**
  - 清晰的标题和收集者信息
  - 截止时间倒计时
  - 格式化的公告内容
  - 支持的文件格式提示
- **文件提交**
  - 姓名验证
  - 文件格式验证
  - 实时上传反馈
  - 支持同名文件替换
- **交互体验**
  - 模态窗口提示
  - 优雅的动画效果
  - 友好的错误提示
  - 完整的移动端适配

## 🛠️ 技术栈

- **前端**
  - HTML5
  - CSS3
  - JavaScript (原生)
  - Noto Sans SC 字体

- **后端**
  - PHP 7.0+
  - JSON 数据存储
  - 文件系统管理

## 📁 目录结构

```
├── admin.php          # 管理后台
├── index.php          # 文件收集页面
├── upload.php         # 文件上传处理
├── details.php        # 提交详情页面
├── style.css          # 全局样式
├── data.json          # 数据存储
└── uploads/           # 上传文件目录
    └── {collection_id}/  # 各收集页面的文件
```

## 🚀 快速开始

1. 确保服务器支持PHP 7.0+
2. 上传所有文件到网站目录
3. 确保uploads目录可写
4. 访问admin.php开始使用

## 📌 注意事项

- 本项目仍处于**演示阶段**
- 建议在正式环境使用前进行安全性评估
- 可根据需求自行修改和扩展功能
- 目前使用JSON文件存储数据，大规模使用建议迁移到数据库
- 暂未添加用户认证系统

## 🔮 未来计划

- [ ] 添加用户认证系统
- [ ] 支持数据库存储
- [ ] 批量下载功能
- [ ] 文件预览功能
- [ ] 更多自定义选项

## 📝 License

本项目仅供学习和研究使用。

## 🤝 贡献

欢迎提出建议和改进意见！

---

<div align="center">

**🌟 如果对这个项目感兴趣，欢迎自行研究和改进！**

</div> 
