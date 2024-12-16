<div align="center">

# 📚 File Collection System

[English](README.md) | [中文](README_zh.md)

A simple and efficient file collection system for homework submission and file gathering.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.0-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-demo-orange.svg)](https://github.com)

</div>

## ✨ Features

### 🔧 Admin Dashboard
- **Create Collection Page**
  - Set title, collector, and deadline
  - Customize announcement content and title
  - Restrict file formats (customizable)
  - Auto-generate 4-digit random short links
- **Management Functions**
  - View all collection pages
  - One-click copy/open collection links
  - View submission details
  - Download all files
  - Delete collection pages
  - Modify collection information

### 📝 Collection Page
- **Information Display**
  - Clear title and collector information
  - Deadline countdown
  - Formatted announcement content
  - Supported file format tips
- **File Submission**
  - Name validation
  - File format validation
  - Real-time upload feedback
  - Support for same-name file replacement
- **User Experience**
  - Modal window prompts
  - Elegant animations
  - Friendly error messages
  - Complete mobile adaptation

## 🛠️ Tech Stack

- **Frontend**
  - HTML5
  - CSS3
  - JavaScript (Vanilla)
  - Noto Sans SC Font

- **Backend**
  - PHP 7.0+
  - JSON Data Storage
  - File System Management

## 📁 Directory Structure

```
├── admin.php          # Admin dashboard
├── index.php          # File collection page
├── upload.php         # File upload handler
├── details.php        # Submission details page
├── style.css          # Global styles
├── data.json          # Data storage
└── uploads/           # Upload directory
    └── {collection_id}/  # Files for each collection
```

## 🚀 Quick Start

1. Ensure server supports PHP 7.0+
2. Upload all files to web directory
3. Make sure uploads directory is writable
4. Access admin.php to start using

## 📌 Notes

- This project is still in **demo stage**
- Security assessment recommended before production use
- Can be modified and extended according to needs
- Currently uses JSON file storage, database recommended for large-scale use
- User authentication system not yet implemented

## 🔮 Future Plans

- [ ] Add user authentication system
- [ ] Support database storage
- [ ] Batch download functionality
- [ ] File preview feature
- [ ] More customization options

## 📝 License

This project is for learning and research purposes only.

## 🤝 Contributing

Suggestions and improvements are welcome!

---

<div align="center">

**🌟 If you're interested in this project, feel free to explore and improve it!**

</div>
