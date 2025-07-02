# PrivaShots 📸

<div align="center">
  <img src="assets/images/privaShots-logo.png" alt="PrivaShots Logo" width="200">
  
  **Your Private, Secure Photo Server**
  
  [![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
  [![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
  [![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](CONTRIBUTING.md)
  
  *A modern, self-hosted photo server with Google Photos-like experience*
</div>

---

## 📦 Version

**Current Version**: v1.0.0

**Release Date**: January 2025

**Latest Features**:
- Complete photo server with modern UI
- JWT authentication and secure file handling
- Timeline view with floating navigation
- Album management and analytics
- EXIF data extraction and search
- Responsive design with glass morphism
- Database backup included for easy setup

---

## ✨ Features

### 🎯 Core Features
- **🔐 Secure Authentication** - JWT-based authentication with password hashing
- **📤 Smart Media Upload** - Support for 50+ image/video formats including RAW files
- **📅 Timeline View** - Browse photos by date with floating year navigation
- **📁 Album Management** - Create, organize, and manage photo albums
- **📊 Analytics Dashboard** - Storage usage, upload trends, and media statistics
- **📱 Responsive Design** - Modern UI that works perfectly on all devices
- **🔍 Advanced Search** - Find photos by date, type, camera, or EXIF data
- **💾 EXIF Data Extraction** - Camera info, GPS coordinates, shooting parameters

### 🚀 Advanced Features
- **🔄 Duplicate Detection** - Prevents uploading duplicate photos
- **⚡ WebP Optimization** - Automatic WebP conversion for better performance
- **📥 Secure Downloads** - Protected file downloads with authentication
- **💿 Storage Management** - Per-user storage limits and usage tracking
- **📝 Activity Logging** - Track user actions and system events
- **🎨 Modern UI** - Glass morphism design with smooth animations
- **⚙️ Auto-Organization** - Automatic date-based folder structure

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: Vanilla JavaScript, Tailwind CSS, Alpine.js
- **Authentication**: JWT (JSON Web Tokens)
- **Image Processing**: GD Library, EXIF extraction
- **UI/UX**: Modern responsive design with glass morphism

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ with extensions: `mysqli`, `gd`, `exif`, `zip`
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- At least 1GB RAM and 10GB storage

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/privashots.git
   cd privashots
   ```

2. **Set up the database**
   ```sql
   CREATE DATABASE cloudphoto_db;
   CREATE USER 'cloudphoto_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON cloudphoto_db.* TO 'cloudphoto_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Configure the application**
   ```bash
   cp config/database.php config/database.php.backup
   nano config/database.php
   ```

   Update the configuration:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cloudphoto_db');
   define('DB_USER', 'cloudphoto_user');
   define('DB_PASS', 'your_secure_password');
   define('JWT_SECRET', 'your_very_long_random_secret_key');
   define('BASE_URL', 'https://your-domain.com/Cloudphoto');
   ```

4. **Run the setup script**
   ```bash
   php setup.php
   ```

   **Alternative: Use the provided database backup**
   ```bash
   # Import the pre-configured database
   mysql -u cloudphoto_user -p cloudphoto_db < setup/cloudphoto_db.sql
   ```

5. **Set proper permissions**
   ```bash
   chmod 755 media/
   chmod 644 config/database.php
   mkdir -p logs && chmod 755 logs/
   ```

6. **Access the application**
   - Navigate to `http://your-domain.com/Cloudphoto`
   - Default login: `admin@cloudphoto.local` / `admin123`
   - **⚠️ Important**: Change the default password immediately!

## 📁 Project Structure

```
Cloudphoto/
├── 📁 api/                    # API endpoints
│   ├── index.php             # API entry point
│   └── router.php            # Request routing
├── 📁 assets/                # Static assets
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── images/               # Images and icons
├── 📁 classes/               # PHP classes
│   ├── Auth.php              # Authentication
│   ├── MediaManager.php      # Media handling
│   ├── AlbumManager.php      # Album management
│   ├── ExifManager.php       # EXIF data extraction
│   ├── ImageProcessor.php    # Image processing
│   └── TimelineManager.php   # Timeline management
├── 📁 config/                # Configuration files
│   ├── database.php          # Database and app config
│   └── production.php        # Production settings
├── 📁 media/                 # Uploaded media files
├── 📁 setup/                 # Setup scripts
│   └── cloudphoto_db.sql     # Database backup with sample data
├── dashboard.php             # Main dashboard
├── timeline.php              # Timeline view
├── albums.php                # Albums management
├── analytics.php             # Analytics dashboard
├── login.php                 # Login page
└── README.md                 # This file
```

## 🔌 API Endpoints

### Authentication
- `POST /api/router.php?action=login` - User login
- `POST /api/router.php?action=register` - User registration

### Media Management
- `POST /api/router.php?action=upload-media` - Upload media
- `POST /api/router.php?action=getMedia` - Get media list
- `POST /api/router.php?action=getRecentPhotos` - Get recent photos
- `POST /api/router.php?action=delete-media` - Delete media

### Albums
- `POST /api/router.php?action=create-album` - Create album
- `POST /api/router.php?action=list-albums` - List albums
- `POST /api/router.php?action=add-to-album` - Add media to album
- `POST /api/router.php?action=remove-from-album` - Remove media from album

### Analytics
- `POST /api/router.php?action=getDashboardStats` - Get dashboard stats
- `POST /api/router.php?action=exif-data` - Get EXIF data

## 🎯 Roadmap & Feature Requests

### 🚧 Planned Features
- [ ] **🔗 Sharing & Collaboration** - Share albums with other users
- [ ] **📱 Mobile App** - Native iOS/Android applications
- [ ] **☁️ Cloud Sync** - Sync with Google Drive, Dropbox, etc.
- [ ] **🤖 AI Features** - Face recognition, object detection
- [ ] **🎨 Photo Editing** - Basic editing tools (crop, filter, adjust)
- [ ] **📹 Video Processing** - Video thumbnails and metadata
- [ ] **🌐 Multi-language** - Internationalization support
- [ ] **📊 Advanced Analytics** - Usage patterns and insights
- [ ] **🔒 End-to-End Encryption** - Client-side encryption
- [ ] **📱 Progressive Web App** - Offline functionality

### 💡 Feature Requests
We welcome feature requests! Please:
1. Check existing [Issues](../../issues) first
2. Create a new issue with the `enhancement` label
3. Describe the feature and its use case
4. Add screenshots/mockups if applicable

## 🤝 Contributing

We love contributions! Here's how you can help:

### 🐛 Bug Reports
1. Check existing [Issues](../../issues) first
2. Create a new issue with the `bug` label
3. Include steps to reproduce, expected vs actual behavior
4. Add screenshots if applicable

### 💻 Code Contributions
1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### 📝 Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use meaningful commit messages
- Add comments for complex logic
- Test your changes thoroughly
- Update documentation if needed

### 🎨 UI/UX Contributions
- Follow the existing design system
- Ensure responsive design
- Test on different devices
- Maintain accessibility standards

## 🔧 Configuration

### Production Settings
For production deployment, use the production configuration:

```bash
cp config/production.php config/database.php
```

Key production settings:
- Strong JWT secret (minimum 32 characters)
- Secure database credentials
- Your domain URL
- Error logging enabled
- Security headers configured

### Security Best Practices
1. **🔐 Change default credentials** immediately after installation
2. **🔒 Use HTTPS** with valid SSL certificates
3. **📁 Set proper file permissions** (755 for directories, 644 for files)
4. **🛡️ Configure firewall** to allow only necessary ports
5. **💾 Regular backups** of database and media files
6. **📊 Monitor logs** for suspicious activity

### Performance Optimization
1. **⚡ Enable OPcache** for PHP
2. **🗄️ Optimize MySQL** configuration
3. **🌐 Use CDN** for static assets
4. **🗜️ Enable gzip compression**
5. **💾 Set up caching** headers for images

## 📊 Monitoring & Maintenance

### Regular Tasks
- **💾 Database backups** (daily)
- **📁 Media file backups** (weekly)
- **📝 Log rotation** (weekly)
- **🔒 Security updates** (monthly)
- **📈 Performance monitoring** (ongoing)

### Troubleshooting

**Common Issues:**
1. **📤 Upload fails**: Check file permissions and PHP upload limits
2. **🖼️ Images not displaying**: Verify file paths and database entries
3. **🔐 Authentication errors**: Check JWT secret and token expiration
4. **⚡ Performance issues**: Enable caching and optimize database queries

**Logs to check:**
- `/logs/error.log` - PHP errors
- `/logs/access.log` - API access logs
- `/logs/upload.log` - Upload activity

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **📚 Documentation**: Check this README and inline code comments
- **🐛 Issues**: Report bugs via [GitHub Issues](../../issues)
- **💬 Discussions**: Use [GitHub Discussions](../../discussions) for questions and ideas
- **📧 Email**: For private support, contact [your-email@domain.com]

## 🙏 Acknowledgments

- **Icons**: Font Awesome
- **CSS Framework**: Tailwind CSS
- **JavaScript Framework**: Alpine.js
- **Design Inspiration**: Google Photos, Apple Photos

## 📈 Project Stats

![GitHub stars](https://img.shields.io/github/stars/yourusername/privashots?style=social)
![GitHub forks](https://img.shields.io/github/forks/yourusername/privashots?style=social)
![GitHub issues](https://img.shields.io/github/issues/yourusername/privashots)
![GitHub pull requests](https://img.shields.io/github/issues-pr/yourusername/privashots)
![GitHub contributors](https://img.shields.io/github/contributors/yourusername/privashots)

---

<div align="center">
  **Made with ❤️ by the PrivaShots Community**
  
  [⭐ Star this repo](https://github.com/yourusername/privashots) | [🐛 Report a bug](../../issues) | [💡 Request a feature](../../issues/new)
</div> 