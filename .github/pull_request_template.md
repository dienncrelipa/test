Checklist cho Merge Request. Ghi X vào trong ngoặc []. PM xác nhận pass hết các điều kiện dưới đây thì nhấn approve

### [Developer]
  - [ ] Đã tự review lại source code của mình trước khi nhờ người khác review theo [Coding Checklist](http://bit.ly/2H8kodH)
  - [ ] Đã self-test và điền kết quả vào checklist
  - [ ] Đã đặt title merge request theo định dạng `Task #{ISSUE_NUMBER} #{ISSUE_TYPE} {ISSUE_CONTENTS}``
  - [ ] TẤT CẢ các commit message tuân theo định dạng `Task #{ISSUE_NUMBER} #{ISSUE_TYPE} {ISSUE_CONTENTS}``
    - Trong đó :
      - `#{ISSUE_NUMBER}` là Redmine ID.
      - `#{ISSUE_TYPE}` là dạng issue : `#DEV` | `#HOTFIX` | `#RELEASE`
      - `#{ISSUE_CONTENTS}` là nội dung ngắn gọn mô tả task
    - V/d:
      - `Task #1234 #DEV Gọt đầu ông Tuyển`
      - `Task #1234 #HOTFIX Gọt đầu ông Tuyển`
      - `Task #1234 #RELEASE Gọt đầu ông Tuyển`
  - [ ] Đã viết thông tin phương hướng sửa trong Redmine
  - [ ] Đã viết release note
  - [ ] Đã tạo branch rebase cho release
  - [ ] `XÁC NHẬN CODE Ở BRANCH REBASE VÀ ORIGIN LÀ GIỐNG NHAU`

### [Reviewer]
  - [ ] Đã kiểm tra redmine chưa:
    - Xác nhận dev đã ghi đầy đủ `PHƯƠNG HƯỚNG SỬA`, `RELEASE NOTE`, `LINK GITHUB`, `PHẠM VI ẢNH HƯỞNG`, `...`
  - [ ] Đã review source code và xác nhận `KHÔNG CÓ LỖI VỀ MẶT CÚ PHÁP`
  - [ ] Đã review source code và xác nhận `KHÔNG SAI LỆCH CHỨC NĂNG`
  - [ ] Đã review source code và xác nhận `KHÔNG GÂY SAI LỆCH TỚI CHỨC NĂNG KHÁC`

### [Tester] Checklist
  - [ ] Đã đọc và kiểm tra kết quả self-test của Developer
  - [ ] Đã test lại và xác nhận KHÔNG GÂY ẢNH HƯỞNG TỚI CHỨC NĂNG KHÁC
  - [ ] Đã chuyển Redmine issue sang trạng thái Test done

### [PM]
  - [ ] Đã approved checklist của tester
  - [ ] Xác nhận khách hàng đã đồng ý release
