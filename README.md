
## 1. Yêu cầu môi trường

Trước khi bắt đầu, đảm bảo máy bạn đã có:

| Phần mềm | Phiên bản tối thiểu | Ghi chú |
|-----------|----------------------|----------|
| PHP | 8.2 trở lên | Bật các extension: `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json` |
| Composer | 2.x | Quản lý package PHP |
| Node.js | >= 18.x | Dùng cho Vite frontend |
| NPM | >= 9.x | |
| MySQL | >= 5.7 hoặc 8.x | Tạo sẵn 1 database trống |

---

## 2. Clone project

```bash
git clone https://github.com/<ten-tai-khoan>/<ten-du-an>.git
cd <ten-du-an>

## 3. Cài đặt backend (Composer)

Cài toàn bộ package Laravel và các dependency:

composer install


Tạo file .env từ mẫu:

cp .env.example .env


Tạo key cho ứng dụng:

php artisan key:generate


Cấu hình database trong file .env (ví dụ MySQL local):

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vehicle_registration
DB_USERNAME=root
DB_PASSWORD=

## 4. Chạy migrate và seed (nếu có)

Tạo bảng trong database:

php artisan migrate
hoặc chạy riêng php artisan migrate --path=database/migrations/2025_10_21_114105_create_vehicle_registrations_table.php


## 5. Chạy project
php artisan serve

Truy cập: 👉 http://127.0.0.1:8000