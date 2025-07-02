# CloudPhoto Deployment Guide

## Shared Hosting Deployment (cPanel)

### Prerequisites
- PHP 8.0+ enabled
- MySQL database access
- File manager or FTP access
- At least 1GB available space

### Step-by-Step Deployment

1. **Download & Extract**
   - Download CloudPhoto files
   - Extract to your local computer

2. **Upload Files**
   - Using cPanel File Manager or FTP:
   - Upload all files to `public_html/cloudphoto/` (or your desired folder)
   - Ensure all files are uploaded including hidden `.htaccess`

3. **Create Database**
   - In cPanel, go to MySQL Databases
   - Create a new database (e.g., `yourusername_cloudphoto`)
   - Create a database user with full privileges
   - Note down: hostname, database name, username, password

4. **Run Installation**
   - Visit: `https://yourdomain.com/cloudphoto/setup/install.php`
   - Enter your database credentials
   - Click "Install CloudPhoto"
   - Delete the `setup/install.php` file after installation

5. **Test Installation**
   - Visit: `https://yourdomain.com/cloudphoto/`
   - Login with default credentials:
     - Email: admin@cloudphoto.local
     - Password: admin123
   - Change admin password immediately

### File Permissions
Ensure these permissions are set:
```
- All PHP files: 644
- media/ directory: 755 (writable)
- .htaccess: 644
```

## VPS/Dedicated Server Deployment

### Prerequisites
- Ubuntu 20.04+ or CentOS 8+
- Root or sudo access
- LAMP stack (Apache, MySQL, PHP)

### Installation Commands

1. **Install LAMP Stack**
   ```bash
   # Ubuntu
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql php-gd php-curl
   
   # Enable Apache modules
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Create Virtual Host**
   ```bash
   sudo nano /etc/apache2/sites-available/cloudphoto.conf
   ```
   
   Add:
   ```apache
   <VirtualHost *:80>
       ServerName cloudphoto.yourdomain.com
       DocumentRoot /var/www/cloudphoto
       
       <Directory /var/www/cloudphoto>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/cloudphoto_error.log
       CustomLog ${APACHE_LOG_DIR}/cloudphoto_access.log combined
   </VirtualHost>
   ```

3. **Enable Site**
   ```bash
   sudo a2ensite cloudphoto.conf
   sudo systemctl reload apache2
   ```

4. **Setup Database**
   ```bash
   sudo mysql
   CREATE DATABASE cloudphoto_db;
   CREATE USER 'cloudphoto_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON cloudphoto_db.* TO 'cloudphoto_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

5. **Deploy Files**
   ```bash
   sudo git clone https://github.com/yourusername/cloudphoto.git /var/www/cloudphoto
   sudo chown -R www-data:www-data /var/www/cloudphoto
   sudo chmod -R 755 /var/www/cloudphoto
   sudo chmod -R 777 /var/www/cloudphoto/media
   ```

6. **Run Installation**
   - Visit: `http://cloudphoto.yourdomain.com/setup/install.php`
   - Complete installation
   - Remove install file: `sudo rm /var/www/cloudphoto/setup/install.php`

## Docker Deployment

### Docker Compose Setup

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  web:
    image: php:8.1-apache
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
      - ./media:/var/www/html/media
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=cloudphoto
      - DB_USER=cloudphoto
      - DB_PASS=cloudphoto123

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=rootpass
      - MYSQL_DATABASE=cloudphoto
      - MYSQL_USER=cloudphoto
      - MYSQL_PASSWORD=cloudphoto123
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

Deploy:
```bash
docker-compose up -d
```

## Security Hardening

### Production Security Steps

1. **Change Default Credentials**
   - Change admin password immediately
   - Use strong passwords (12+ characters)

2. **SSL/HTTPS Setup**
   ```bash
   # Using Let's Encrypt
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d cloudphoto.yourdomain.com
   ```

3. **File Permissions**
   ```bash
   # Restrict config files
   chmod 600 config/database.php
   
   # Protect sensitive directories
   chmod 700 setup/
   chmod 700 classes/
   ```

4. **Environment Variables**
   - Move sensitive config to environment variables
   - Use `.env` file for local development

5. **Regular Updates**
   - Keep PHP and MySQL updated
   - Monitor CloudPhoto releases for security updates

## Backup Strategy

### Database Backup
```bash
# Daily backup
mysqldump -u cloudphoto_user -p cloudphoto_db > backup_$(date +%Y%m%d).sql

# Automated daily backup
echo "0 2 * * * mysqldump -u cloudphoto_user -p'password' cloudphoto_db > /backups/cloudphoto_$(date +\%Y\%m\%d).sql" | crontab -
```

### Media Backup
```bash
# Backup media files
tar -czf media_backup_$(date +%Y%m%d).tar.gz media/

# Sync to remote storage
rsync -av media/ user@backup-server:/backups/cloudphoto/media/
```

## Monitoring

### Log Files
- Apache error log: `/var/log/apache2/error.log`
- CloudPhoto activity: Check database `activity_logs` table
- PHP errors: Enable error logging in production

### Health Check Script
```bash
#!/bin/bash
# health_check.sh

curl -f http://cloudphoto.yourdomain.com/api/login || echo "CloudPhoto is down!" | mail -s "CloudPhoto Alert" admin@yourdomain.com
```

## Troubleshooting

### Common Issues

1. **Upload failures**
   - Check PHP upload limits
   - Verify media directory permissions
   - Check disk space

2. **Database connection errors**
   - Verify credentials in config/database.php
   - Check MySQL service status
   - Confirm database exists

3. **404 errors on API calls**
   - Ensure mod_rewrite is enabled
   - Check .htaccess file exists
   - Verify Apache configuration

4. **Permission denied errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/cloudphoto
   sudo chmod -R 755 /var/www/cloudphoto
   sudo chmod -R 777 /var/www/cloudphoto/media
   ```

## Performance Optimization

### PHP Configuration
```ini
# php.ini optimizations
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
```

### Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_user_upload_date ON media_files(user_id, uploaded_at);
CREATE INDEX idx_user_type ON media_files(user_id, mimetype);
```

### Apache Optimization
```apache
# Enable compression
LoadModule deflate_module modules/mod_deflate.so

# Cache static files
<LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
</LocationMatch>
```

---

For additional support, check the main README.md or create an issue on GitHub. 