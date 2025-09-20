# Dormitory Manager - JWT Authentication API

Hệ thống quản lý ký túc xá với JWT authentication cho Laravel 12.

## 🚀 Tính năng

- **JWT Authentication**: Đăng ký, đăng nhập, đăng xuất với JWT token
- **Token Management**: Refresh token, vô hiệu hóa token
- **User Management**: Quản lý thông tin người dùng
- **API Security**: Middleware bảo vệ các route cần xác thực
- **Laravel 12**: Sử dụng phiên bản Laravel mới nhất

## 📋 Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Laragon (Windows) hoặc XAMPP/WAMP
- Git

## 🛠️ Cài đặt và Setup

### 1. Clone project

```bash
git clone <repository-url>
cd dormitory_manager
```

### 2. Cài đặt dependencies

```bash
composer install
```

### 3. Cấu hình môi trường

```bash
# Copy file cấu hình
cp .env.example .env

# Tạo application key
php artisan key:generate

# Tạo JWT secret key
php artisan jwt:secret
```

### 4. Cấu hình database trong `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dormitory_manager
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Chạy migrations

```bash
php artisan migrate
```

### 6. Khởi động server

```bash
php artisan serve
```

Server sẽ chạy tại: `http://localhost:8000`

## 🔧 Cấu hình Laragon (Windows)

### 1. Tạo database

1. Mở Laragon
2. Click vào MySQL → phpMyAdmin
3. Tạo database mới: `dormitory_manager`
4. Cập nhật `.env` với thông tin database

### 2. Cấu hình Virtual Host (Optional)

1. Tạo file `dormitory_manager.conf` trong `laragon/etc/apache2/sites-enabled/`
2. Thêm cấu hình:

```apache
<VirtualHost *:80>
    DocumentRoot "E:/dormitory_manager/public"
    ServerName dormitory-manager.test
    <Directory "E:/dormitory_manager/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Thêm vào `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 dormitory-manager.test
```

4. Restart Laragon

## 📚 API Endpoints

### Public Routes (Không cần token)

#### Đăng ký
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Đăng nhập
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Protected Routes (Cần JWT token)

#### Lấy thông tin user
```http
GET /api/auth/me
Authorization: Bearer {jwt_token}
```

#### Đăng xuất
```http
POST /api/auth/logout
Authorization: Bearer {jwt_token}
```

#### Refresh token
```http
POST /api/auth/refresh
Authorization: Bearer {jwt_token}
```

#### Lấy thông tin user (alternative)
```http
GET /api/user
Authorization: Bearer {jwt_token}
```

## 🧪 Testing API

### 1. Sử dụng Postman

1. Import file `postman_collection.json`
2. Chạy request Register để tạo user mới
3. Copy JWT token từ response
4. Sử dụng token cho các request khác

### 2. Sử dụng cURL

#### Đăng ký
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Đăng nhập
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

#### Lấy thông tin user
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 3. Sử dụng HTML Test Page

Mở file `public/test-api.html` trong browser để test API với giao diện web.

## 🔐 JWT Token Details

### Token Structure
JWT token có 3 phần: `header.payload.signature`

### Token Usage
- **Header**: `Authorization: Bearer {token}`
- **Expiration**: 60 phút (có thể refresh)
- **Blacklist**: Token bị vô hiệu hóa khi logout

### Token Lifecycle
1. **Login/Register**: Tạo token mới
2. **API Requests**: Sử dụng token trong header
3. **Token Expired**: Refresh token hoặc login lại
4. **Logout**: Token bị blacklist

## 📁 Cấu trúc Project

```
dormitory_manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AuthController.php    # JWT Authentication
│   │   └── Middleware/
│   │       └── JWTMiddleware.php     # JWT Middleware
│   └── Models/
│       └── User.php                  # User model với JWT
├── config/
│   ├── auth.php                      # Auth config với JWT guard
│   └── jwt.php                       # JWT configuration
├── routes/
│   └── api.php                       # API routes
├── bootstrap/
│   └── app.php                       # Laravel 12 bootstrap
└── public/
    └── test-api.html                 # Test page
```

## 🚨 Troubleshooting

### Lỗi thường gặp

#### 1. "Token expired"
```bash
# Refresh token
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 2. "Token invalid"
- Kiểm tra format token
- Đảm bảo có `Bearer ` prefix
- Token có thể đã bị blacklist

#### 3. "Could not create token"
```bash
# Tạo lại JWT secret
php artisan jwt:secret
```

#### 4. Database connection error
- Kiểm tra Laragon đã start
- Kiểm tra database `dormitory_manager` đã tạo
- Kiểm tra thông tin trong `.env`

#### 5. Route not found
```bash
# Kiểm tra routes
php artisan route:list -v
```

### Debug Commands

```bash
# Kiểm tra routes
php artisan route:list -v

# Kiểm tra config
php artisan config:show database
php artisan config:show auth

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Kiểm tra JWT config
php artisan config:show jwt
```

## 📖 Tài liệu tham khảo

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [JWT Auth Package](https://github.com/tymondesigns/jwt-auth)
- [JWT.io](https://jwt.io/) - JWT Token Debugger
- [Postman Documentation](https://learning.postman.com/docs/)

## 🤝 Đóng góp

1. Fork project
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Support

Nếu gặp vấn đề, hãy tạo issue hoặc liên hệ qua email.

---

**Happy Coding! 🎉**