# Hướng dẫn sử dụng Postman với Dormitory Manager API

## 📥 **Import Collection vào Postman**

1. Mở Postman
2. Click **Import** (góc trên bên trái)
3. Chọn file `postman_collection.json` đã tạo
4. Click **Import**

## 🔧 **Cấu hình Environment Variables**

### **Tạo Environment mới:**
1. Click **Environments** (sidebar trái)
2. Click **Create Environment**
3. Đặt tên: `Dormitory Manager Local`

### **Thêm Variables:**
| Variable | Initial Value | Current Value |
|----------|---------------|---------------|
| `base_url` | `http://localhost:8000` | `http://localhost:8000` |
| `jwt_token` | `` (để trống) | `` (để trống) |

4. Click **Save**

## 🚀 **Cách sử dụng từng API**

### **1. Đăng ký User (Register)**

**Request:**
```http
POST {{base_url}}/api/auth/register
Content-Type: application/json

{
  "name": "Nguyen Van A",
  "email": "test@example.com",
  "password": "123456",
  "password_confirmation": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "test@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Lưu ý:** Copy `token` từ response để sử dụng cho các API khác!

### **2. Đăng nhập (Login)**

**Request:**
```http
POST {{base_url}}/api/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "test@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### **3. Lấy thông tin User (Cần JWT Token)**

**Request:**
```http
GET {{base_url}}/api/auth/me
Authorization: Bearer {{jwt_token}}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "test@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### **4. Refresh Token (Cần JWT Token)**

**Request:**
```http
POST {{base_url}}/api/auth/refresh
Authorization: Bearer {{jwt_token}}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "test@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### **5. Đăng xuất (Cần JWT Token)**

**Request:**
```http
POST {{base_url}}/api/auth/logout
Authorization: Bearer {{jwt_token}}
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

## 🔄 **Workflow sử dụng**

### **Bước 1: Đăng ký hoặc Đăng nhập**
1. Chạy request **Register** hoặc **Login**
2. Copy `token` từ response
3. Paste vào Environment variable `jwt_token`

### **Bước 2: Sử dụng các API protected**
1. Tất cả API khác sẽ tự động sử dụng `{{jwt_token}}`
2. Token sẽ được gửi trong header `Authorization: Bearer {{jwt_token}}`

### **Bước 3: Refresh token khi cần**
1. Khi token sắp hết hạn, chạy request **Refresh Token**
2. Copy token mới và cập nhật `jwt_token` variable

## ⚠️ **Lưu ý quan trọng**

1. **Token hết hạn**: JWT token có thời gian hết hạn (mặc định 60 phút)
2. **Refresh token**: Sử dụng refresh API để lấy token mới
3. **Logout**: Token sẽ bị vô hiệu hóa sau khi logout
4. **Environment**: Đảm bảo chọn đúng environment khi test

## 🐛 **Troubleshooting**

### **Lỗi 401 Unauthorized:**
- Kiểm tra token có đúng không
- Kiểm tra token có hết hạn không
- Thử refresh token

### **Lỗi 422 Validation Error:**
- Kiểm tra format JSON
- Kiểm tra các trường bắt buộc
- Kiểm tra email đã tồn tại chưa (cho register)

### **Lỗi 500 Internal Server Error:**
- Kiểm tra server có chạy không (`php artisan serve`)
- Kiểm tra database connection
- Kiểm tra JWT secret key

## 📝 **Test Cases mẫu**

### **Test Case 1: Đăng ký thành công**
```json
{
  "name": "Test User",
  "email": "testuser@example.com",
  "password": "123456",
  "password_confirmation": "123456"
}
```

### **Test Case 2: Đăng nhập thành công**
```json
{
  "email": "testuser@example.com",
  "password": "123456"
}
```

### **Test Case 3: Validation Error**
```json
{
  "name": "",
  "email": "invalid-email",
  "password": "123",
  "password_confirmation": "456"
}
```

Chúc bạn test API thành công! 🎉
