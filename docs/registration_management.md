# Quản lý đăng ký

## Mô tả chức năng

Chức năng quản lý đăng ký cho phép người quản lý đoàn hội theo dõi, duyệt và quản lý các đăng ký tham gia sự kiện của sinh viên. Đây là một phần quan trọng trong quy trình quản lý sự kiện, giúp đảm bảo việc tổ chức sự kiện được diễn ra suôn sẻ và hiệu quả.

## Các chức năng chính

### 1. Xem danh sách đăng ký
- Hiển thị danh sách sinh viên đã đăng ký tham gia sự kiện
- Lọc danh sách theo trạng thái (đang chờ duyệt, đã duyệt, từ chối)
- Tìm kiếm sinh viên theo tên, mã số sinh viên
- Sắp xếp danh sách theo thời gian đăng ký, tên sinh viên

### 2. Duyệt đăng ký tham gia
- Duyệt đơn lẻ từng đăng ký
- Duyệt hàng loạt nhiều đăng ký cùng lúc
- Từ chối đăng ký với lý do
- Ghi chú khi duyệt đăng ký

### 3. Quản lý trạng thái đăng ký
- Theo dõi các trạng thái đăng ký (đang chờ duyệt, đã duyệt, từ chối)
- Thay đổi trạng thái đăng ký
- Hủy đăng ký đã duyệt (nếu cần)

### 4. Xuất báo cáo đăng ký
- Xuất danh sách đăng ký ra file Excel/CSV
- Thống kê số lượng đăng ký theo trạng thái
- In danh sách đăng ký cho điểm danh

## Quy trình quản lý đăng ký

### 1. Sinh viên đăng ký tham gia
- Sinh viên đăng ký tham gia sự kiện qua ứng dụng
- Hệ thống ghi nhận thông tin đăng ký với trạng thái "đang chờ duyệt"
- Thông báo đăng ký thành công cho sinh viên

### 2. Người quản lý xem xét đăng ký
- Người quản lý đoàn hội xem danh sách đăng ký
- Kiểm tra thông tin sinh viên và điều kiện tham gia
- Quyết định duyệt hoặc từ chối đăng ký

### 3. Duyệt/Từ chối đăng ký
- Duyệt đăng ký: cập nhật trạng thái thành "đã duyệt"
- Từ chối đăng ký: cập nhật trạng thái thành "từ chối" kèm lý do
- Hệ thống gửi thông báo kết quả cho sinh viên

### 4. Quản lý sau duyệt
- Theo dõi số lượng đăng ký đã duyệt
- So sánh với số lượng tham gia tối đa của sự kiện
- Đóng đăng ký khi đã đủ số lượng

### 5. Chuẩn bị điểm danh
- Xuất danh sách đăng ký đã duyệt
- Chuẩn bị tài liệu điểm danh
- Chuyển thông tin sang module điểm danh

## Giao diện người dùng

### Màn hình danh sách đăng ký
- Bảng hiển thị danh sách với các cột: Tên sinh viên, MSSV, Thời gian đăng ký, Trạng thái
- Bộ lọc theo trạng thái, thời gian
- Ô tìm kiếm
- Nút xuất Excel/CSV
- Nút duyệt/từ chối hàng loạt

### Form duyệt đăng ký
- Thông tin sinh viên
- Thông tin sự kiện
- Dropdown chọn trạng thái (duyệt/từ chối)
- Trường nhập lý do (bắt buộc khi từ chối)
- Trường ghi chú (tùy chọn)

## Phân quyền

### Admin
- Toàn quyền quản lý đăng ký cho tất cả sự kiện

### Quản lý đoàn hội
- Quản lý đăng ký cho sự kiện của đoàn hội mình
- Duyệt/từ chối đăng ký
- Xuất báo cáo đăng ký

### Sinh viên
- Xem trạng thái đăng ký của mình
- Hủy đăng ký (nếu sự kiện chưa diễn ra)
