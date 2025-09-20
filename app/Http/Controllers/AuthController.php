<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * AuthController - Controller xử lý authentication (đăng ký, đăng nhập, đăng xuất)
 * 
 * Controller này sử dụng JWT (JSON Web Token) để xác thực người dùng
 * JWT là một chuẩn để tạo token an toàn cho API
 */
class AuthController extends Controller
{
    public function __construct()
    {
        // Middleware được cấu hình trong routes/api.php
        // Các method login và register không cần token
        // Các method khác (me, logout, refresh) cần token
    }

    /**
     * Đăng ký tài khoản mới
     * 
     * @param Request $request - Dữ liệu từ client gửi lên
     * @return \Illuminate\Http\JsonResponse - Trả về JSON response
     */
    public function register(Request $request)
    {
        // Bước 1: Validate dữ liệu đầu vào
        // Validator kiểm tra các quy tắc validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',           // Tên: bắt buộc, kiểu string, tối đa 255 ký tự
            'email' => 'required|string|email|max:255|unique:users', // Email: bắt buộc, đúng format email, unique trong bảng users
            'password' => 'required|string|min:6|confirmed', // Password: bắt buộc, tối thiểu 6 ký tự, phải có password_confirmation
        ]);

        // Bước 2: Kiểm tra validation có lỗi không
        if ($validator->fails()) {
            // Trả về lỗi validation với mã status 422 (Unprocessable Entity)
            return response()->json([
                'success' => false,
                'message' => 'Validation errors', // Thông báo lỗi
                'errors' => $validator->errors()  // Chi tiết các lỗi validation
            ], 422);
        }

        // Bước 3: Tạo user mới trong database
        $user = User::create([
            'name' => $request->name,                                    // Lấy tên từ request
            'email' => $request->email,                                  // Lấy email từ request
            'password' => Hash::make($request->password),               // Hash password trước khi lưu
        ]);

        // Bước 4: Tạo JWT token cho user vừa tạo
        // JWTAuth::fromUser() tạo token từ user object
        $token = JWTAuth::fromUser($user);

        // Bước 5: Trả về response thành công
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,                    // Thông tin user vừa tạo
            'token' => $token,                 // JWT token để client sử dụng
            'token_type' => 'bearer',           // Loại token (Bearer)
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // Thời gian hết hạn (giây)
        ], 201); // Status code 201 = Created
    }

    /**
     * Đăng nhập với email và password
     * 
     * @param Request $request - Dữ liệu từ client (email, password)
     * @return \Illuminate\Http\JsonResponse - Trả về JSON response
     */
    public function login(Request $request)
    {
        // Bước 1: Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',         // Email: bắt buộc, đúng format
            'password' => 'required|string|min:6', // Password: bắt buộc, tối thiểu 6 ký tự
        ]);

        // Bước 2: Kiểm tra validation
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Bước 3: Lấy credentials (thông tin đăng nhập)
        $credentials = $request->only('email', 'password');

        // Bước 4: Thử đăng nhập với JWT
        try {
            // JWTAuth::attempt() kiểm tra email/password và tạo token nếu đúng
            if (!$token = JWTAuth::attempt($credentials)) {
                // Nếu đăng nhập thất bại (sai email/password)
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials' // Thông báo sai thông tin đăng nhập
                ], 401); // Status code 401 = Unauthorized
            }
        } catch (JWTException $e) {
            // Nếu có lỗi khi tạo token (lỗi hệ thống)
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500); // Status code 500 = Internal Server Error
        }

        // Bước 5: Đăng nhập thành công, trả về thông tin
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => Auth::user(),           // Thông tin user hiện tại
            'token' => $token,                 // JWT token
            'token_type' => 'bearer',           // Loại token
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // Thời gian hết hạn
        ]);
    }

    /**
     * Lấy thông tin user hiện tại (cần token)
     * 
     * Method này được bảo vệ bởi middleware 'auth:api'
     * Chỉ user đã đăng nhập (có token hợp lệ) mới có thể truy cập
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // auth()->user() trả về thông tin user hiện tại từ JWT token
        return response()->json([
            'success' => true,
            'user' => Auth::user()
        ]);
    }

    /**
     * Đăng xuất (vô hiệu hóa token)
     * 
     * Khi user đăng xuất, token sẽ bị vô hiệu hóa (blacklist)
     * Token này sẽ không thể sử dụng được nữa
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // JWTAuth::invalidate() vô hiệu hóa token hiện tại
        // Token sẽ được thêm vào blacklist và không thể sử dụng
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh token (gia hạn token)
     * 
     * Thay vì đăng nhập lại khi token hết hạn,
     * có thể sử dụng refresh token để lấy token mới
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // JWTAuth::refresh() tạo token mới từ token hiện tại
        // Token cũ sẽ bị vô hiệu hóa, token mới có thời gian hết hạn mới
        return response()->json([
            'success' => true,
            'user' => Auth::user(),           // Thông tin user (không đổi)
            'token' => JWTAuth::refresh(JWTAuth::getToken()), // Token mới
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // Thời gian hết hạn mới
        ]);
    }
}

/*
 * GIẢI THÍCH JWT TOKEN:
 * 
 * JWT Token có 3 phần được ngăn cách bởi dấu chấm (.)
 * Ví dụ: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIiwiaWF0IjoxNjQwOTk1MjAwLCJleHAiOjE2NDA5OTg4MDB9.signature
 * 
 * 1. Header (Phần đầu): Chứa thuật toán mã hóa và loại token
 * 2. Payload (Phần giữa): Chứa thông tin user và thời gian hết hạn
 * 3. Signature (Phần cuối): Chữ ký để xác thực token
 * 
 * Ưu điểm của JWT:
 * - Stateless: Không cần lưu token trong database
 * - Self-contained: Token chứa đầy đủ thông tin user
 * - Scalable: Dễ dàng scale hệ thống
 * - Secure: Được ký bằng secret key
 * 
 * Cách sử dụng token:
 * - Gửi token trong header: Authorization: Bearer {token}
 * - Token có thời gian hết hạn (mặc định 60 phút)
 * - Có thể refresh token mà không cần đăng nhập lại
 * - Token có thể bị vô hiệu hóa khi logout
 */