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
                
                <div class="card-body">
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

                    <form id="importForm" enctype="multipart/form-data">
                        <input type="file" name="file" id="file" accept=".xlsx, .xls, .csv">
                        <button type="button" class="btn btn-primary" id="btnImport">Import Excel</button>
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
        alert('asdasd');
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
                    $('.card-body').prepend(alertHtml);
                    
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
                    $('.card-body').prepend(alertHtml);
                    
                    // Show field errors
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                    });
                    
                } else if (xhr.status === 400) {
                    // Business logic errors
                    const message = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body').prepend(alertHtml);
                    
                } else {
                    // Other errors
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> Lỗi server. Vui lòng thử lại sau.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body').prepend(alertHtml);
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
            startDate: today, // không cho chọn ngày trước hôm nay
            autoclose: true,
            todayHighlight: true
        }).datepicker('setDate', today); // mặc định là ngày mai

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

    $('#btnImport').click(function (e) {
        e.preventDefault();

        let fileInput = $('#file')[0].files[0];
        if (!fileInput) {
            alert("Vui lòng chọn file Excel trước khi upload!");
            return;
        }

        // Tạo FormData
        let formData = new FormData();
        formData.append('file', fileInput);

        // Thêm token CSRF của Laravel
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("register-car.import-file") }}', // route trong Laravel
            type: 'POST',
            data: formData,
            processData: false,  // Không xử lý dữ liệu (bắt buộc)
            contentType: false,  // Không đặt header Content-Type (bắt buộc)
            success: function (response) {
                alert(response.message || "Import thành công!");
                console.log(response);
                setTimeout(function() {
                        window.location.href = '{{ route("register-car.index") }}';
                    }, 3000);
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || "Có lỗi xảy ra khi import!";
                alert(msg);
                console.error(xhr, xhr?.responseJSON?.message);
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