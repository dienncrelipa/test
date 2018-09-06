Checklist cho PR. Ghi X vào trong ngoặc []. PM xác nhận pass hết các điều kiện dưới đây thì nhấn approve

### [Developer]
  - [ ] Đã tự review lại source code của mình trước khi nhờ người khác review theo [Coding Checklist](http://bit.ly/2H8kodH)
  - [ ] Đã self-test và điền kết quả vào checklist
  - [ ] Đã đặt title PR theo định dạng `Task #{ISSUE_NUMBER} {ISSUE_CONTENTS}`
  - [ ] TẤT CẢ các commit message tuân theo định dạng `Task #{ISSUE_NUMBER} {ISSUE_CONTENTS}`
    - Trong đó :
      - `#{ISSUE_NUMBER}` là Redmine ID.
      - `#{ISSUE_CONTENTS}` là nội dung ngắn gọn mô tả task
    - V/d:
      - `Task #1234 #DEV Gọt đầu ông Tuyển`
      - `Task #1234 #HOTFIX Gọt đầu ông Tuyển`
      - `Task #1234 #RELEASE Gọt đầu ông Tuyển`
  - [ ] Đã viết đầy đủ thông tin trong Redmine. Mục nào không có thì ghi "Không"
  - [ ] Đã viết Release Note. Đảm bảo release khi có lỗi xảy ra có thể rollback ngay lập tức
  - [ ] Đã rebase lại branch khi tạo PR vào master. Đảm bảo 1 PR chỉ có 1 commit
  - [ ]  Đã chuyển Redmine issue sang trạng thái Resolved

### [Reviewer]
  - [ ] Đã kiểm tra redmine chưa:
    - Đã kiểm tra Redmine các mục: `PHƯƠNG HƯỚNG SỬA, PHẠM VI ẢNH HƯỞNG, RELEASE NOTE, PR LINK, TRẠNG THÁI ISSUE...`
  - [ ] Đã review source code và xác nhận `KHÔNG CÓ LỖI VỀ MẶT CÚ PHÁP`
  - [ ] Đã review source code và xác nhận `KHÔNG SAI LỆCH CHỨC NĂNG`
  - [ ] Đã review source code và xác nhận `KHÔNG GÂY SAI LỆCH TỚI CHỨC NĂNG KHÁC`

### [Tester] Checklist
  - [ ] Đã đọc và kiểm tra kết quả self-test của Developer
  - [ ] Đã test lại và xác nhận KHÔNG GÂY ẢNH HƯỞNG TỚI CHỨC NĂNG KHÁC
  - [ ] Đã chuyển Redmine issue sang trạng thái Test done

### [PM]
  - [ ] Xác nhận khách hàng đã đồng ý release
