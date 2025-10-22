@extends('layouts.master')

@section('title', 'Đăng Ký Xe Nhận Hàng Tại Mỏ')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-file-excel"></i> Import Đăng Ký Xe Từ Excel</h4>
                </div>
                
                <div class="card-body">
                    <!-- Hướng dẫn -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Hướng dẫn:</h6>
                        <ol class="mb-0">
                            <li>Tải file Excel mẫu bên dưới</li>
                            <li>Điền thông tin đăng ký xe vào file Excel</li>
                            <li>Upload file Excel đã điền thông tin</li>
                            <li>Hệ thống sẽ tự động kiểm tra và import dữ liệu</li>
                        </ol>
                    </div>

                    <!-- Download template -->
                    <div class="mb-4 text-center">
                        <a href="{{ route('register-car.download-template') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-download"></i> Tải File Excel Mẫu
                        </a>
                    </div>

                    <hr>

                    <!-- Thông báo lỗi -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi:</strong>
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
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Kết quả import -->
                    @if (session('import_result'))
                        @php $result = session('import_result'); @endphp
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Kết Quả Import</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="stat-box">
                                            <h3 class="text-primary">{{ $result['total'] }}</h3>
                                            <p class="text-muted">Tổng số dòng</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-box">
                                            <h3 class="text-success">{{ $result['success'] }}</h3>
                                            <p class="text-muted">Import thành công</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-box">
                                            <h3 class="text-danger">{{ $result['errors_count'] }}</h3>
                                            <p class="text-muted">Lỗi</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chi tiết lỗi -->
                                @if (!empty($result['errors']))
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-danger mb-0">
                                            <i class="fas fa-exclamation-circle"></i> Chi tiết lỗi:
                                        </h6>
                                        @if (session('error_file'))
                                            <a href="{{ asset('storage/' . session('error_file')) }}" 
                                               class="btn btn-danger btn-sm"
                                               download>
                                                <i class="fas fa-file-excel"></i> Tải File Lỗi (Excel)
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Hướng dẫn xử lý:</strong> Tải file Excel chứa các dòng lỗi, 
                                        sửa lại dữ liệu theo cột "LỖI", sau đó upload lại file.
                                    </div>
                                    
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-danger sticky-top">
                                                <tr>
                                                    <th width="80">Dòng</th>
                                                    <th width="200">Xe</th>
                                                    <th width="200">Lái xe</th>
                                                    <th width="200">Công ty</th>
                                                    <th>Lỗi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($result['errors'] as $error)
                                                    <tr>
                                                        <td class="text-center">{{ $error['row'] }}</td>
                                                        <td><code>{{ $error['data'][3] ?? 'N/A' }}</code></td>
                                                        <td>{{ $error['data'][11] ?? 'N/A' }}</td>
                                                        <td>{{ $error['data'][15] ?? 'N/A' }}</td>
                                                        <td class="text-danger">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            {{ $error['error'] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <button id="downloadErrorBtn" class="btn btn-danger d-none">Tải file lỗi</button>
                    <!-- Form upload -->
                    <form action="{{ route('register-car.import-process') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="formImport">
                        @csrf

                        <div class="mb-4">
                            <label for="excel_file" class="form-label">
                                <i class="fas fa-file-upload"></i> Chọn File Excel
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('excel_file') is-invalid @enderror" 
                                   id="excel_file" 
                                   name="excel_file"
                                   accept=".xlsx,.xls,.csv"
                                   required>
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Chỉ chấp nhận file: .xlsx, .xls, .csv (tối đa 10MB)
                            </small>
                        </div>

                        <!-- Preview file name -->
                        <div id="filePreview" class="alert alert-secondary d-none">
                            <i class="fas fa-file-excel text-success"></i>
                            <strong>File đã chọn:</strong> <span id="fileName"></span>
                            (<span id="fileSize"></span>)
                        </div>

                        <!-- Progress bar -->
                        <div id="uploadProgress" class="d-none mb-3">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%"
                                     id="progressBar">0%</div>
                            </div>
                            <p class="text-center mt-2">
                                <small id="progressText">Đang xử lý...</small>
                            </p>
                        </div>

                        <div id="result" class="mt-3"></div>
                        

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('register-car.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay Lại
                            </a>
                            <button type="submit" class="btn btn-success" id="btnSubmit">
                                <i class="fas fa-upload"></i> Upload & Import
                            </button>
                        </div>
                    </form>

                    <!-- Lưu ý -->
                    <div class="alert alert-warning mt-4">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng:</h6>
                        <ul class="mb-0">
                            <li><strong>Mỗi xe chỉ được đăng ký 1 lần/ngày</strong></li>
                            <li><strong>Biển số xe chỉ được đăng ký cho 1 đơn vị vận chuyển duy nhất</strong></li>
                            <li>Phải đăng ký trước 1 ngày (ngày nhận hàng = ngày đăng ký + 1)</li>
                            <li>Chỉ import trong khung giờ: 08:00-16:00 hoặc 20:00-22:00</li>
                            <li>Hệ thống sẽ tự động bỏ qua các dòng bị lỗi và import các dòng hợp lệ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
     $(document).ready(function() {
        // Preview file info
    $('#excel_file').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            $('#fileName').text(file.name);
            $('#fileSize').text((file.size / 1024).toFixed(2) + ' KB');
            $('#filePreview').removeClass('d-none');
        } else {
            $('#filePreview').addClass('d-none');
        }
    });

    // AJAX Submit
    $('#formImport').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $('#btnSubmit');
        const originalText = submitBtn.html();
        
        // Disable button
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
        
        // Show progress
        $('#uploadProgress').removeClass('d-none');
        let progress = 0;
        
        const progressInterval = setInterval(function() {
            progress += 5;
            if (progress >= 90) {
                clearInterval(progressInterval);
            }
            $('#progressBar').css('width', progress + '%').text(progress + '%');
        }, 200);
        const downloadBtn = document.getElementById('downloadErrorBtn');
        $.ajax({
            url: '{{ route("register-car.import-process") }}', // route trong Laravel
            type: 'POST',
            data: formData,
            processData: false,  // Không xử lý dữ liệu (bắt buộc)
            contentType: false,  // Không đặt header Content-Type (bắt buộc)
            success: function (response) {
                clearInterval(progressInterval);
                $('#progressBar').css('width', '100%').text('100%');
                $('#progressText').text('Hoàn tất!');
                
                if (response.success) {
                    // Show success and reload page to show results
                    console.log(response,'response');
                    setTimeout(function() {
                        // window.location.reload();
                    }, 1000);
                } else {
                    alert('Có lỗi xảy ra: ' + response.message);
                }

                let resultHtml = '';
                        if (response.success) {
                            resultHtml = `<div class="alert alert-${response.data.errors > 0 ? 'warning' : 'success'}">
                                <strong>${response.message}</strong>
                                <p>Tổng số dòng: ${response.data.total}</p>
                                <p>Thành công: ${response.data.success}</p>
                                <p>Lỗi: ${response.data.errors}</p>`;

                            if (response.data.errors > 0) {
                                // resultHtml += '<ul>';
                                // response.error_details.forEach(function(error) {
                                //     resultHtml += `<li>Dòng ${error.row_number}: ${error.error}</li>`;
                                // });
                                // resultHtml += '</ul>';
                                if (response.error_file) {
                                    downloadBtn.classList.remove('d-none');
                                    downloadBtn.onclick = () => window.location.href = response.error_file;
                                }
                                // Tự động tải file lỗi
                                // if (response.error_file) {
                                //     const link = document.createElement('a');
                                //     link.href = response.error_file;
                                //     link.download = ''; // Trình duyệt sẽ tự lấy tên file từ URL
                                //     document.body.appendChild(link);
                                //     link.click();
                                //     document.body.removeChild(link);
                                // }
                            }
                            resultHtml += '</div>';
                        } else {
                            resultHtml = `<div class="alert alert-danger">
                                <strong>${response.message}</strong>
                            </div>`;
                        }
                        $('#result').html(resultHtml);

            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || "Có lỗi xảy ra khi import!";
                alert(msg);
                clearInterval(progressInterval);
                console.error(xhr, xhr?.responseJSON?.message);
            }
        });
        

    });
});
</script>
@endsection


@endsection