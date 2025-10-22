@extends('layouts.master')

@section('title', 'Đăng Ký Xe Nhận Hàng Tại Mỏ')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-truck"></i> Chi tiết xe đã đăng ký</h4>
                </div>
                
                <div class="card-body card-body-msg">
                    <!-- Thông báo lỗi -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Thông báo thành công -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" id="formDangKy">
                        @csrf
                        <!-- PHẦN 1: THÔNG TIN ĐĂNG KÝ -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Thông Tin Đăng Ký</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Register Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="register_date" class="form-label">Ngày Đăng Ký <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="register_date" name="register_date" value="{{ $car->register_date ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Ngày Nhận Hàng -->
                                    <div class="col-md-6 mb-3">
                                        <label for="delivery_date" class="form-label">Ngày Nhận Hàng (Ngày Mai) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="delivery_date" name="delivery_date" value="{{ $car->delivery_date ?? 'N/A' }}" disabled>
                                        <small class="text-muted">Tự động tính ngày mai</small>
                                    </div>

                                    <!-- Contract No -->
                                    <div class="col-md-12 mb-3">
                                        <label for="contract_no" class="form-label">Số Hợp Đồng <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="contract_no" name="contract_no" value="{{ $car->contract_no ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PHẦN 2: THÔNG TIN XE -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-truck-moving"></i> Thông Tin Xe</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Truck Plate -->
                                    <div class="col-md-6 mb-3">
                                        <label for="truck_plate" class="form-label">Biển Số Xe Đầu Kéo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="truck_plate" name="truck_plate" value="{{ $car->truck_plate ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Trailer Plate -->
                                    <div class="col-md-6 mb-3">
                                        <label for="trailer_plate" class="form-label">Biển Số Rơ Moóc</label>
                                        <input type="text" class="form-control text-uppercase" id="trailer_plate" name="trailer_plate" value="{{ $car->trailer_plate ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-md-4 mb-3">
                                        <label for="country" class="form-label">Quốc Gia</label>
                                        <select class="form-select" id="country" name="country" disabled>
                                            <option value="Vietnam" {{ $car->country === 'Vietnam' ? 'selected' : '' }}>Vietnam</option>
                                            <option value="Laos" {{ $car->country === 'Laos' ? 'selected' : '' }}>Laos</option>
                                            <option value="Cambodia" {{ $car->country === 'Cambodia' ? 'selected' : '' }}>Cambodia</option>
                                            <option value="Thailand" {{ $car->country === 'Thailand' ? 'selected' : '' }}>Thailand</option>
                                        </select>
                                    </div>

                                    <!-- Wheel -->
                                    <div class="col-md-4 mb-3">
                                        <label for="wheel" class="form-label">Số Bánh Xe</label>
                                        <input type="number" class="form-control" id="wheel" name="wheel" value="{{ $car->wheel ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Truck Weight -->
                                    <div class="col-md-4 mb-3">
                                        <label for="truck_weight" class="form-label">Trọng Lượng Xe (tấn)</label>
                                        <input type="number" class="form-control" id="truck_weight" name="truck_weight" value="{{ $car->truck_weight ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Pay Load -->
                                    <div class="col-md-4 mb-3">
                                        <label for="pay_load" class="form-label">Tải Trọng (tấn)</label>
                                        <input type="number" class="form-control" id="pay_load" name="pay_load" value="{{ $car->pay_load ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Container No1 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="container_no1" class="form-label">Số Container 1</label>
                                        <input type="text" class="form-control text-uppercase" id="container_no1" name="container_no1" value="{{ $car->container_no1 ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Container No2 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="container_no2" class="form-label">Số Container 2</label>
                                        <input type="text" class="form-control text-uppercase" id="container_no2" name="container_no2" value="{{ $car->container_no2 ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Vehicle Status -->
                                    <div class="col-md-12 mb-3">
                                        <label for="vehicle_status" class="form-label">Trạng Thái Xe</label>
                                        <select class="form-select" id="vehicle_status" name="vehicle_status" disabled>
                                            <option value="RO" {{ $car->vehicle_status === 'RO' ? 'selected' : '' }}>RO - Sẵn sàng hoạt động</option>
                                            <option value="MAINTENANCE" {{ $car->vehicle_status === 'MAINTENANCE' ? 'selected' : '' }}>Đang Bảo Trì</option>
                                            <option value="ACTIVE" {{ $car->vehicle_status === 'ACTIVE' ? 'selected' : '' }}>Đang Hoạt Động</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PHẦN 3: THÔNG TIN LÁI XE -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-user"></i> Thông Tin Lái Xe</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Driver Name -->
                                    <div class="col-md-6 mb-3">
                                        <label for="driver_name" class="form-label">Tên Lái Xe <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="driver_name" name="driver_name" value="{{ $car->driver_name ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- ID/Passport -->
                                    <div class="col-md-6 mb-3">
                                        <label for="id_passport" class="form-label">CMND/Passport <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="id_passport" name="id_passport" value="{{ $car->id_passport ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Phone Number -->
                                    <div class="col-md-6 mb-3">
                                        <label for="phone_number" class="form-label">Số Điện Thoại</label>
                                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="{{ $car->phone_number ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PHẦN 4: THÔNG TIN ĐƠN VỊ VẬN CHUYỂN -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-building"></i> Thông Tin Đơn Vị Vận Chuyển</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Transportation Company -->
                                    <div class="col-md-6 mb-3">
                                        <label for="transportation_company" class="form-label">Đơn Vị Vận Chuyển <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="transportation_company" name="transportation_company" value="{{ $car->transportation_company ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Subcontractor -->
                                    <div class="col-md-6 mb-3">
                                        <label for="subcontractor" class="form-label">Nhà Thầu Phụ</label>
                                        <input type="text" class="form-control text-uppercase" id="subcontractor" name="subcontractor" value="{{ $car->subcontractor ?? 'N/A' }}" disabled>
                                    </div>

                                    <!-- Destination EST -->
                                    <div class="col-md-12 mb-3">
                                        <label for="destination_est" class="form-label">Điểm Đến</label>
                                        <input type="text" class="form-control" id="destination_est" name="destination_est" value="{{ $car->destination_est ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PHẦN 5: GHI CHÚ -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-comment"></i> Ghi Chú</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="note" class="form-label">Ghi Chú Thêm</label>
                                        <textarea class="form-control" id="note" name="note" rows="3" disabled>{{ $car->note ?? 'N/A' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   
$(document).ready(function() {
    // AJAX Submit Form
    $('#formDangKy').on('submit', function(e) {
        e.preventDefault();
        // Disable submit button
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.alert').remove();
        
        // Get form data
        const formData = new FormData(this);
        
        // AJAX request
        $.ajax({
            url: '{{ route("register-car.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body-msg').prepend(alertHtml);
                    
                    // Reset form
                    $('#formDangKy')[0].reset();
                    
                    // Scroll to top
                    $('html, body').animate({ scrollTop: 0 }, 500);
                    
                    // Optional: Redirect after 2 seconds
                    setTimeout(function() {
                        window.location.href = '{{ route("register-car.index") }}';
                    }, 2000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    
                    // Show general error message
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body-msg').prepend(alertHtml);
                    
                    // Show field errors
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                    });
                    
                } else if (xhr.status === 400) {
                    // Business logic errors
                    const message = xhr.responseJSON?.message || 'Mỗi xe chỉ được đăng ký 1 lần mỗi ngày. Biển số xe này đã được đăng ký trong hôm nay.';
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body-msg').prepend(alertHtml);
                    
                } else {
                    console.log(xhr,'response123');
                    
                    // Other errors
                    

                    if (xhr.status >= 200 && xhr.status < 300) {
                        // Dù bị jQuery nhầm, nhưng vẫn là thành công
                        const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> 'Đăng ký xe thành công!'
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body-msg').prepend(alertHtml);
                        $('html, body').animate({ scrollTop: 0 }, 500);
                        return;
                    }

                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> Lỗi server. Vui lòng thử lại sau.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body-msg').prepend(alertHtml);
                }
                
                // Scroll to top to show error
                $('html, body').animate({ scrollTop: 0 }, 500);
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });


    $(document).ready(function () {
        // Lấy ngày hôm nay và reset về 00:00
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);

        // Khởi tạo ngày bắt đầu
        $('#register_date').datepicker({
            format: 'yyyy-mm-dd',
            startDate: tomorrow, // không cho chọn ngày trước hôm nay
            autoclose: true,
            todayHighlight: true
        }).datepicker('setDate', tomorrow); // mặc định là ngày mai

        // Khởi tạo ngày kết thúc
        $('#delivery_date').datepicker({
            format: 'yyyy-mm-dd',
            startDate: tomorrow, // disable ngày trước ngày mai
            autoclose: true,
            todayHighlight: true
        }).datepicker('setDate', tomorrow);

        // Khi đổi ngày bắt đầu
        $('#register_date').on('changeDate', function (e) {
            const startDate = e.date;
            const endPicker = $('#delivery_date');

            // Cập nhật min cho ngày kết thúc
            endPicker.datepicker('setStartDate', startDate);

            // Nếu endDate hiện tại < startDate → reset lại
            const currentEndDate = endPicker.datepicker('getDate');
            if (currentEndDate < startDate) {
            endPicker.datepicker('setDate', startDate);
            }
        });
    });

});
</script>
@endsection

@push('styles')
<style>
.card-header {
    font-weight: 600;
}
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}
.text-danger {
    color: #dc3545 !important;
}
.text-uppercase {
    text-transform: uppercase;
}
.alert ul {
    padding-left: 1.5rem;
}
</style>
@endpush
@endsection