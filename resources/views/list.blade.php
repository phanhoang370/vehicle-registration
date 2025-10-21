@extends('layouts.master')

@section('title', 'Đăng Ký Xe Nhận Hàng Tại Mỏ')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-truck"></i>Danh sách đăng Ký Xe Nhận Hàng Tại Mỏ</h4>
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


                     <table class="table table-bordered" id="cars-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ngày đăng ký</th>
                                <th>Mã hợp đồng</th>
                                <th>Biển số</th>
                                <th>Quốc gia</th>
                                <th>Tên người lái xe</th>
                                <th>Mã hộ chiếu</th>
                                <th>Số điện thoại</th>
                                <th>Đơn vị vận chuyển</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <nav>
                        <ul class="pagination" id="pagination"></ul>
                    </nav>

                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
     $(document).ready(function() {
            loadCars(1);

            function loadCars(page) {
                $.ajax({
                    url: "{{ route('register-car.get-list') }}?page=" + page,
                    type: "GET",
                    success: function(res) {
                        renderTable(res.data);
                        renderPagination(res.pagination);
                    },
                    error: function() {
                        alert('Không thể tải dữ liệu!');
                    }
                });
            }

            function renderTable(data) {
                let rows = '';
                data.forEach(car => {
                    rows += `
                        <tr>
                            <td>${car.id}</td>
                            <td>${car.register_date}</td>
                            <td>${car.contract_no}</td>
                            <td>${car.truck_plate}</td>
                            <td>${car.country}</td>
                            <td>${car.driver_name}</td>
                            <td>${car.id_passport}</td>
                            <td>${car.phone_number ?? ''}</td>
                            <td>${car.transportation_company ?? ''}</td>
                        </tr>
                    `;
                });
                $('#cars-table tbody').html(rows);
            }

            function renderPagination(pagination) {
                let links = '';

                if (pagination.current_page > 1) {
                    links += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">«</a></li>`;
                }

                for (let i = 1; i <= pagination.last_page; i++) {
                    links += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
                }

                if (pagination.current_page < pagination.last_page) {
                    links += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">»</a></li>`;
                }

                $('#pagination').html(links);
            }

            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                loadCars(page);
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