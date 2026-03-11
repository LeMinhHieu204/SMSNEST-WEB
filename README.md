# SMSNEST-WEB

SMSNEST-WEB là nền tảng web cung cấp dịch vụ SMS verification, được xây dựng nhằm hỗ trợ quy trình mua số, nhận mã OTP và quản lý giao dịch trên một giao diện trực quan, dễ sử dụng.

Dự án hướng tới mô hình vận hành thực tế cho dịch vụ số điện thoại ảo, kết hợp giữa trải nghiệm người dùng, quản trị hệ thống và khả năng kết nối với các nhà cung cấp bên thứ ba để xử lý đơn hàng, nạp tiền và chăm sóc khách hàng.

## Giới thiệu

Hệ thống được phát triển theo định hướng trở thành một công cụ kinh doanh có thể triển khai nhanh trên môi trường local hoặc máy chủ riêng. Nền tảng tập trung vào các nhu cầu cốt lõi như quản lý tài khoản người dùng, xử lý đơn mua SMS theo dịch vụ và quốc gia, theo dõi giao dịch, tiếp nhận hỗ trợ khách hàng và quản lý nội dung hướng dẫn.

## Định hướng vận hành

SMSNEST-WEB phù hợp cho các mô hình như website bán dịch vụ OTP/SMS verification, hệ thống nội bộ cần quản lý đơn hàng SMS tập trung hoặc mô hình reseller cần đồng bộ giá và tồn kho từ nhà cung cấp.

Hệ thống hiện tích hợp các thành phần cần thiết để phục vụ vận hành, bao gồm gửi email, lưu trữ hình ảnh, đồng bộ dữ liệu SMS và xử lý thanh toán crypto.

## Công nghệ sử dụng

- PHP
- MySQL
- MVC Architecture
- SMTP
- Cloudinary
- SMSPool API
- Cryptomus API

