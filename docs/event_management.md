# Quản lý sự kiện

## Mô tả chức năng
Chức năng quản lý sự kiện cho phép người quản lý đoàn hội tạo và quản lý các sự kiện, sinh viên có thể xem và đăng ký tham gia.

## Các chức năng chính

### 1. Quản lý cơ bản
- **Thêm sự kiện mới** với thông tin cần thiết
- **Xóa sự kiện** không cần thiết
- **Sửa thông tin sự kiện** đã tạo
- **Xuất danh sách sự kiện** dưới dạng báo cáo

### 2. Tính năng đặc biệt
- **Cộng điểm rèn luyện** cho sinh viên tham gia
- **Sự kiện như bài viết** với khả năng bình luận
- **Nội dung HTML** cho phép định dạng phong phú, thêm ảnh và header

## Cấu trúc dữ liệu

### Thông tin sự kiện
- Tiêu đề sự kiện
- Nội dung (HTML)
- Thời gian bắt đầu và kết thúc
- Địa điểm
- Đoàn hội tổ chức
- Điểm rèn luyện được cộng
- Trạng thái (sắp diễn ra, đang diễn ra, đã kết thúc)

### Đăng ký tham gia
- Danh sách sinh viên đăng ký
- Trạng thái đăng ký (đã duyệt, chờ duyệt, từ chối)

### Bình luận
- Nội dung bình luận
- Người bình luận
- Thời gian bình luận
- Bình luận cha (để hỗ trợ trả lời bình luận)

## Quy trình sử dụng

### Người quản lý đoàn hội
1. Tạo sự kiện mới với đầy đủ thông tin
2. Công bố sự kiện
3. Duyệt đăng ký tham gia
4. Quản lý bình luận và trả lời
5. Xuất báo cáo khi cần

### Sinh viên
1. Xem danh sách sự kiện
2. Đăng ký tham gia
3. Bình luận và tương tác
4. Nhận điểm rèn luyện sau khi tham gia

## Giao diện người dùng

### Trang quản lý sự kiện
- Danh sách sự kiện với bộ lọc và tìm kiếm
- Form tạo/chỉnh sửa sự kiện với trình soạn thảo HTML
- Quản lý đăng ký và bình luận

### Trang hiển thị sự kiện
- Hiển thị nội dung HTML của sự kiện
- Nút đăng ký tham gia
- Phần bình luận và trả lời
