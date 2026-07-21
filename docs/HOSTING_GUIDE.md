# Hosting Guide — AWS EC2 + Apache2

This guide deploys LightCar so `public/` is the web root (not the project
root) — required so `src/`, `config/`, and `database/` are never reachable
by a browser.

## 1. Launch the EC2 instance
- AMI: Ubuntu Server 22.04/24.04 LTS, t2.micro (Free Tier is fine)
- Security Group inbound rules: SSH (22, your IP only), HTTP (80, 0.0.0.0/0),
  HTTPS (443, 0.0.0.0/0)
- Create/download a key pair, then connect:
  ```bash
  ssh -i your-key.pem ubuntu@<EC2_PUBLIC_IP>
  ```

## 2. Install the LAMP stack
```bash
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql libapache2-mod-php git unzip
sudo a2enmod headers rewrite
sudo systemctl enable apache2 mysql
```

## 3. Secure MySQL and create the database
```bash
sudo mysql_secure_installation
sudo mysql -u root -p
```
```sql
CREATE DATABASE car_sales_db;
CREATE USER 'lightcar_app'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
GRANT ALL PRIVILEGES ON car_sales_db.* TO 'lightcar_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
Don't use `root` for the app's DB connection in production — a dedicated,
limited user (as above) is safer.

## 4. Get the code onto the server
```bash
cd /var/www
sudo git clone https://github.com/fussy0064/LightCar.git
sudo mysql -u lightcar_app -p car_sales_db < /var/www/LightCar/database/schema.sql
```

## 5. Set environment variables (don't hardcode secrets)
Create `/etc/apache2/conf-available/lightcar-env.conf`:
```apache
SetEnv DB_HOST localhost
SetEnv DB_USER lightcar_app
SetEnv DB_PASS CHANGE_THIS_PASSWORD
SetEnv DB_NAME car_sales_db
SetEnv ENCRYPTION_KEY <generate-a-long-random-string-here>
```
Generate a strong key: `openssl rand -hex 32`
```bash
sudo a2enconf lightcar-env
sudo a2enmod env
```

## 6. Point Apache's DocumentRoot at `public/`
Create `/etc/apache2/sites-available/lightcar.conf`:
```apache
<VirtualHost *:80>
    ServerName your-domain-or-ec2-public-dns
    DocumentRoot /var/www/LightCar/public

    <Directory /var/www/LightCar/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/lightcar-error.log
    CustomLog ${APACHE_LOG_DIR}/lightcar-access.log combined
</VirtualHost>
```
```bash
sudo a2ensite lightcar.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

## 7. File ownership and permissions
```bash
sudo chown -R www-data:www-data /var/www/LightCar
sudo find /var/www/LightCar -type d -exec chmod 755 {} \;
sudo find /var/www/LightCar -type f -exec chmod 644 {} \;
```

## 8. Turn off PHP error display in production
Edit `/etc/php/8.3/apache2/php.ini` (version number may differ):
```ini
display_errors = Off
log_errors = On
```
```bash
sudo systemctl restart apache2
```

## 9. (Recommended) Add HTTPS with a free certificate
Only if you have a domain name pointed at the EC2 instance:
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

## 10. Test
- Visit `http://<EC2_PUBLIC_IP>/` (or your domain) — you should see the
  LightCar login page.
- Confirm `/var/www/LightCar/src`, `/config`, `/database` are **not**
  reachable directly in a browser (each folder has a `.htaccess` denying
  access; this is a second layer of defense on top of DocumentRoot).
- Log in with `admin` / `admin123`, then **change that password
  immediately** (register a new Admin-equivalent account manually in the
  DB, or add a "change password" feature, then disable/rename the seed
  account).

## 11. Redeploying after future code changes
```bash
cd /var/www/LightCar
sudo git pull
sudo systemctl restart apache2
```
