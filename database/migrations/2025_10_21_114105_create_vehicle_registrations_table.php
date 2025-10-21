<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_registrations', function (Blueprint $table) {
            $table->id();

            // Cột từ Excel
            $table->date('register_date')->comment('Ngày đăng ký');
            $table->string('contract_no', 100)->comment('Số hợp đồng');
            $table->string('truck_plate', 50)->comment('Biển số xe đầu kéo');
            $table->string('country', 50)->default('Vietnam')->comment('Quốc gia');
            $table->integer('wheel')->nullable()->comment('Số bánh xe');
            $table->string('trailer_plate', 50)->nullable()->comment('Biển số rơ moóc');
            $table->decimal('truck_weight', 10, 2)->nullable()->comment('Trọng lượng xe (tấn)');
            $table->decimal('pay_load', 10, 2)->nullable()->comment('Tải trọng');
            $table->string('container_no1', 50)->nullable()->comment('Số container 1');
            $table->string('container_no2', 50)->nullable()->comment('Số container 2');
            $table->string('driver_name', 100)->comment('Tên lái xe');
            $table->string('id_passport', 50)->comment('CMND/Passport');
            $table->string('phone_number', 100)->nullable()->comment('Số điện thoại');
            $table->string('destination_est', 100)->nullable()->comment('Điểm đến');
            $table->string('transportation_company', 255)->comment('Đơn vị vận chuyển');
            $table->string('subcontractor', 100)->nullable()->comment('Nhà thầu phụ');
            $table->string('vehicle_status', 50)->default('RO')->comment('Trạng thái xe');
            $table->enum('registration_status', ['pending_approval', 'approved', 'rejected', 'cancelled'])
                ->default('pending_approval')
                ->comment('Trạng thái đăng ký');
            $table->timestamp('time_approved')->nullable()->comment('Thời gian duyệt');

            // Thông tin bổ sung
            $table->date('delivery_date')->comment('Ngày nhận hàng (ngày mai)');
            $table->time('registration_time')->comment('Giờ đăng ký');
            $table->enum('registration_round', ['lan_1', 'lan_2'])->nullable()->comment('Lần 1: 08:00-16:00, Lần 2: 20:00-22:00');
            $table->text('rejection_reason')->nullable()->comment('Lý do từ chối');
            $table->string('approved_by', 100)->nullable()->comment('Người duyệt');
            $table->text('note')->nullable()->comment('Ghi chú');

            // Audit fields
            $table->timestamps(); // created_at, updated_at
            $table->string('created_by', 100)->nullable()->comment('Người tạo');
            $table->string('ip_address', 45)->nullable()->comment('IP address');

            // Constraints
            $table->unique(['truck_plate', 'delivery_date'], 'unique_truck_date');

            // Indexes
            $table->index('register_date', 'idx_register_date');
            $table->index('delivery_date', 'idx_delivery_date');
            $table->index('truck_plate', 'idx_truck_plate');
            $table->index('contract_no', 'idx_contract_no');
            $table->index('driver_name', 'idx_driver_name');
            $table->index('transportation_company', 'idx_transportation_company');
            $table->index('registration_status', 'idx_registration_status');
            $table->index('vehicle_status', 'idx_vehicle_status');

            // Table comment
            $table->comment('Bảng đăng ký xe nhận hàng tại mỏ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_registrations');
    }
};
