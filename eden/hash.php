<?php
// 비밀번호 평문
$password = '113042EW';

// 비밀번호를 해시로 변환 (bcrypt 알고리즘 사용)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 결과 출력
echo "비밀번호 해시: " . $hashed_password;

// 비밀번호 확인 (검증 예시)
$input_password = '113042EW';
if (password_verify($input_password, $hashed_password)) {
    echo "\n비밀번호가 일치합니다!";
} else {
    echo "\n비밀번호가 일치하지 않습니다.";
}
?>
