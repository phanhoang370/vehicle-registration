@extends('layouts.master')

@section('title', 'Đăng Ký Xe Nhận Hàng Tại Mỏ')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-truck"></i> Đăng Ký Xe Nhận Hàng Tại Mỏ</h4>
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

                    <!-- Thông tin quy định -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Quy định đăng ký:</h6>
                        <ul class="mb-0">
                            <li>Đăng ký trước <strong>1 ngày</strong> khi muốn xe lên bốc hàng</li>
                            <li><strong>Lần 1:</strong> 08:00 - 16:00 | <strong>Lần 2:</strong> 20:00 - 22:00</li>
                            <li>Mỗi xe chỉ được đăng ký <strong>1 lần/ngày</strong></li>
                            <li>Biển số xe là <strong>duy nhất</strong>, không thay đổi</li>
                        </ul>
                    </div>

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
                                        <input type="text" 
                                               class="form-control @error('register_date') is-invalid @enderror" 
                                               id="register_date" 
                                               name="register_date" 
                                               value="{{ old('register_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                               disabled
                                               >
                                        @error('register_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Ngày Nhận Hàng -->
                                    <div class="col-md-6 mb-3">
                                        <label for="delivery_date" class="form-label">Ngày Nhận Hàng (Ngày Mai) <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('delivery_date') is-invalid @enderror" 
                                               id="delivery_date" 
                                               name="delivery_date" 
                                               value="{{ old('delivery_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                               >
                                        @error('delivery_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Tự động tính ngày mai</small>
                                    </div>

                                    <!-- Contract No -->
                                    <div class="col-md-12 mb-3">
                                        <label for="contract_no" class="form-label">Số Hợp Đồng <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('contract_no') is-invalid @enderror" 
                                               id="contract_no" 
                                               name="contract_no" 
                                               value="{{ old('contract_no') }}"
                                               placeholder="Ví dụ: 107Coal/NT-XPPL/2025"
                                               required>
                                        @error('contract_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <input type="text" 
                                               class="form-control text-uppercase @error('truck_plate') is-invalid @enderror" 
                                               id="truck_plate" 
                                               name="truck_plate" 
                                               value="{{ old('truck_plate') }}"
                                               placeholder="Ví dụ: 20C-102.61"
                                               required>
                                        @error('truck_plate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Trailer Plate -->
                                    <div class="col-md-6 mb-3">
                                        <label for="trailer_plate" class="form-label">Biển Số Rơ Moóc</label>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('trailer_plate') is-invalid @enderror" 
                                               id="trailer_plate" 
                                               name="trailer_plate" 
                                               value="{{ old('trailer_plate') }}"
                                               placeholder="Ví dụ: 20R-016.84">
                                        @error('trailer_plate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Country -->
                                    <div class="col-md-4 mb-3">
                                        <label for="country" class="form-label">Quốc Gia</label>
                                        <select class="form-select @error('country') is-invalid @enderror" 
                                                id="country" 
                                                name="country">
                                            <option value="Vietnam" {{ old('country') == 'Vietnam' ? 'selected' : '' }}>Vietnam</option>
                                            <option value="Laos" {{ old('country') == 'Laos' ? 'selected' : '' }}>Laos</option>
                                            <option value="Cambodia" {{ old('country') == 'Cambodia' ? 'selected' : '' }}>Cambodia</option>
                                            <option value="Thailand" {{ old('country') == 'Thailand' ? 'selected' : '' }}>Thailand</option>
                                        </select>
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Wheel -->
                                    <div class="col-md-4 mb-3">
                                        <label for="wheel" class="form-label">Số Bánh Xe</label>
                                        <input type="number" 
                                               class="form-control @error('wheel') is-invalid @enderror" 
                                               id="wheel" 
                                               name="wheel" 
                                               value="{{ old('wheel') }}"
                                               placeholder="Ví dụ: 22"
                                               min="4"
                                               max="50">
                                        @error('wheel')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Truck Weight -->
                                    <div class="col-md-4 mb-3">
                                        <label for="truck_weight" class="form-label">Trọng Lượng Xe (tấn)</label>
                                        <input type="number" 
                                               class="form-control @error('truck_weight') is-invalid @enderror" 
                                               id="truck_weight" 
                                               name="truck_weight" 
                                               value="{{ old('truck_weight') }}"
                                               placeholder="Ví dụ: 21"
                                               step="0.01"
                                               min="0">
                                        @error('truck_weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Pay Load -->
                                    <div class="col-md-4 mb-3">
                                        <label for="pay_load" class="form-label">Tải Trọng (tấn)</label>
                                        <input type="number" 
                                               class="form-control @error('pay_load') is-invalid @enderror" 
                                               id="pay_load" 
                                               name="pay_load" 
                                               value="{{ old('pay_load') }}"
                                               placeholder="Ví dụ: 30"
                                               step="0.01"
                                               min="0">
                                        @error('pay_load')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Container No1 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="container_no1" class="form-label">Số Container 1</label>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('container_no1') is-invalid @enderror" 
                                               id="container_no1" 
                                               name="container_no1" 
                                               value="{{ old('container_no1') }}"
                                               placeholder="Số container">
                                        @error('container_no1')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Container No2 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="container_no2" class="form-label">Số Container 2</label>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('container_no2') is-invalid @enderror" 
                                               id="container_no2" 
                                               name="container_no2" 
                                               value="{{ old('container_no2') }}"
                                               placeholder="Số container">
                                        @error('container_no2')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Vehicle Status -->
                                    <div class="col-md-12 mb-3">
                                        <label for="vehicle_status" class="form-label">Trạng Thái Xe</label>
                                        <select class="form-select @error('vehicle_status') is-invalid @enderror" 
                                                id="vehicle_status" 
                                                name="vehicle_status">
                                            <option value="RO" {{ old('vehicle_status', 'RO') == 'RO' ? 'selected' : '' }}>RO - Ready for Operation</option>
                                            <option value="MAINTENANCE" {{ old('vehicle_status') == 'MAINTENANCE' ? 'selected' : '' }}>Đang Bảo Trì</option>
                                            <option value="ACTIVE" {{ old('vehicle_status') == 'ACTIVE' ? 'selected' : '' }}>Đang Hoạt Động</option>
                                        </select>
                                        @error('vehicle_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <input type="text" 
                                               class="form-control @error('driver_name') is-invalid @enderror" 
                                               id="driver_name" 
                                               name="driver_name" 
                                               value="{{ old('driver_name') }}"
                                               placeholder="Ví dụ: Nguyễn Văn A"
                                               required>
                                        @error('driver_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- ID/Passport -->
                                    <div class="col-md-6 mb-3">
                                        <label for="id_passport" class="form-label">CMND/Passport <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('id_passport') is-invalid @enderror" 
                                               id="id_passport" 
                                               name="id_passport" 
                                               value="{{ old('id_passport') }}"
                                               placeholder="Ví dụ: P01714015"
                                               required>
                                        @error('id_passport')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Phone Number -->
                                    <div class="col-md-6 mb-3">
                                        <label for="phone_number" class="form-label">Số Điện Thoại</label>
                                        <input type="tel" 
                                               class="form-control @error('phone_number') is-invalid @enderror" 
                                               id="phone_number" 
                                               name="phone_number" 
                                               value="{{ old('phone_number') }}"
                                               placeholder="Ví dụ: 0325955043">
                                        @error('phone_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <input type="text" 
                                               class="form-control @error('transportation_company') is-invalid @enderror" 
                                               id="transportation_company" 
                                               name="transportation_company" 
                                               value="{{ old('transportation_company') }}"
                                               placeholder="Ví dụ: Nam Tien"
                                               required>
                                        @error('transportation_company')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Subcontractor -->
                                    <div class="col-md-6 mb-3">
                                        <label for="subcontractor" class="form-label">Nhà Thầu Phụ</label>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('subcontractor') is-invalid @enderror" 
                                               id="subcontractor" 
                                               name="subcontractor" 
                                               value="{{ old('subcontractor') }}"
                                               placeholder="Ví dụ: NT">
                                        @error('subcontractor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Destination EST -->
                                    <div class="col-md-12 mb-3">
                                        <label for="destination_est" class="form-label">Điểm Đến</label>
                                        <input type="text" 
                                               class="form-control @error('destination_est') is-invalid @enderror" 
                                               id="destination_est" 
                                               name="destination_est" 
                                               value="{{ old('destination_est') }}"
                                               placeholder="Ví dụ: ango">
                                        @error('destination_est')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <textarea class="form-control @error('note') is-invalid @enderror" 
                                                  id="note" 
                                                  name="note" 
                                                  rows="3"
                                                  placeholder="Nhập ghi chú nếu có...">{{ old('note') }}</textarea>
                                        @error('note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BUTTONS -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('register-car.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay Lại
                            </a>
                            {{-- <div>
                                <button type="reset" class="btn btn-warning me-2">
                                    <i class="fas fa-redo"></i> Làm Mới
                                </button> --}}
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Đăng Ký
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto uppercase cho biển số xe
    const plateInputs = ['truck_plate', 'trailer_plate', 'container_no1', 'container_no2', 'id_passport', 'subcontractor'];
    plateInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    });

    // Validate form trước khi submit
    const form = document.getElementById('formDangKy');
    form.addEventListener('submit', function(e) {
        const truckPlate = document.getElementById('truck_plate').value.trim();
        const driverName = document.getElementById('driver_name').value.trim();
        const idPassport = document.getElementById('id_passport').value.trim();
        const contractNo = document.getElementById('contract_no').value.trim();
        const company = document.getElementById('transportation_company').value.trim();

        // if (!truckPlate || !driverName || !idPassport || !contractNo || !company) {
        //     e.preventDefault();
        //     alert('Vui lòng điền đầy đủ các thông tin bắt buộc (có dấu *)');
        //     return false;
        // }
         if (!truckPlate) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ các thông tin bắt buộc (có dấu *)');
            return false;
        }
    });

    // Tự động tính ngày nhận hàng (ngày mai)
    const registerDate = document.getElementById('register_date');
    const receiveDate = document.getElementById('delivery_date');
    
    if (registerDate) {
        registerDate.addEventListener('change', function() {
            const date = new Date(this.value);
            date.setDate(date.getDate() + 1);
            const tomorrow = date.toISOString().split('T')[0];
            receiveDate.value = tomorrow;
        });
    }
});
</script>
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

    // Real-time check truck registration
    // let checkTimeout;
    // $('#truck_plate, #delivery_date').on('input change', function() {
    //     clearTimeout(checkTimeout);
        
    //     const truckPlate = $('#truck_plate').val();
    //     const ngayNhanHang = $('#delivery_date').val();
        
    //     if (truckPlate && ngayNhanHang) {
    //         checkTimeout = setTimeout(function() {
    //             $.ajax({
    //                 url: '{{ route("register-car.check-truck") }}',
    //                 method: 'POST',
    //                 data: {
    //                     truck_plate: truckPlate,
    //                     delivery_date: ngayNhanHang,
    //                     _token: $('meta[name="csrf-token"]').attr('content')
    //                 },
    //                 success: function(response) {
    //                     if (response.exists) {
    //                         $('#truck_plate').addClass('is-invalid');
    //                         $('#truck_plate').next('.invalid-feedback').remove();
    //                         $('#truck_plate').after(`
    //                             <div class="invalid-feedback d-block">
    //                                 <i class="fas fa-exclamation-circle"></i> ${response.message}
    //                             </div>
    //                         `);
    //                     } else {
    //                         $('#truck_plate').removeClass('is-invalid');
    //                         $('#truck_plate').next('.invalid-feedback').remove();
    //                     }
    //                 }
    //             });
    //         }, 500);
    //     }
    // });
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