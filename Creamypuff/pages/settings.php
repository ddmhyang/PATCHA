<?php
require_once '../includes/db.php';
if (!$is_admin) { die("관리자만 접근 가능합니다."); }

$settings_result = $mysqli->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$current_bgm_type = $_SESSION['bgm_type'] ?? ''; // 'youtube' or 'mp3'
$current_bgm_youtube_id = $_SESSION['bgm_youtube_id'] ?? '';
$current_bgm_youtube_si = $_SESSION['bgm_youtube_si'] ?? '';
$current_bgm_mp3_filename = $_SESSION['bgm_mp3_filename'] ?? '';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_youtube_url = trim($_POST['youtube_url'] ?? '');
    $existing_mp3_filename = trim($_POST['existing_mp3_filename'] ?? ''); // 기존 MP3 파일명 유지용

    // 초기화: 새로운 설정이 적용될 때 이전 값들을 제거합니다.
    $current_bgm_type = '';
    $current_bgm_youtube_id = '';
    $current_bgm_youtube_si = '';
    $current_bgm_mp3_filename = '';

    // 1. YouTube 링크 처리 (가장 높은 우선순위)
    if (!empty($input_youtube_url)) {
        $parsed_url = parse_url($input_youtube_url);
        $query_params = [];
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_params);
        }

        $video_id = $query_params['v'] ?? ''; // 'v' 파라미터에서 영상 ID 추출
        $share_id = $query_params['si'] ?? '';  // 'si' 파라미터에서 공유 ID 추출 (없으면 빈 문자열)

        if (!empty($video_id)) {
            // 유효한 YouTube 영상 ID가 추출되면 해당 값으로 설정
            $current_bgm_type = 'youtube';
            $current_bgm_youtube_id = $video_id;
            $current_bgm_youtube_si = $share_id; // 'si'가 없으면 빈 문자열이 됩니다.

            // MP3 파일이 업로드된 경우에도 YouTube가 우선이므로 MP3는 무시됩니다.
            // (실제 파일 업로드 처리 시, 이 경우 업로드된 파일은 삭제하거나 무시해야 함)
            $_SESSION['message'] = 'YouTube BGM으로 설정되었습니다.';
        } else {
            // YouTube URL이 입력되었으나 유효한 영상 ID를 찾을 수 없는 경우
            // 다음 MP3 로직으로 넘어갈 수 있도록 아무것도 설정하지 않습니다.
             $_SESSION['message'] = '유효한 YouTube 영상 ID를 찾을 수 없습니다. MP3 업로드를 확인합니다.';
        }
    }

    // 2. MP3 파일 업로드 처리 (YouTube 링크가 없거나 유효하지 않을 경우)
    // 'current_bgm_type'이 아직 'youtube'로 설정되지 않았다면 MP3를 확인
    if ($current_bgm_type !== 'youtube') {
        // 새 MP3 파일이 업로드되었는지 확인
        if (isset($_FILES['mp3_file']) && $_FILES['mp3_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['mp3_file']['tmp_name'];
            $file_name = basename($_FILES['mp3_file']['name']); // 실제 파일명 추출
            $upload_dir = 'assets/bgm/'; // MP3 파일을 저장할 디렉토리 (서버에 미리 생성되어야 함)
            $target_file_path = $upload_dir . $file_name;

            // 파일 이동 및 저장
            if (move_uploaded_file($file_tmp_path, $target_file_path)) {
                $current_bgm_type = 'mp3';
                $current_bgm_mp3_filename = $file_name;
                $_SESSION['message'] = 'MP3 BGM으로 설정되었습니다.';

                // YouTube 설정은 비웁니다.
                $current_bgm_youtube_id = '';
                $current_bgm_youtube_si = '';
            } else {
                $_SESSION['message'] = 'MP3 파일 업로드에 실패했습니다.';
            }
        }
        // 새로운 MP3 파일이 없고, 기존 MP3 파일명이 전달된 경우 (기존 MP3 유지)
        else if (!empty($existing_mp3_filename) && file_exists('assets/bgm/' . $existing_mp3_filename)) {
            $current_bgm_type = 'mp3';
            $current_bgm_mp3_filename = $existing_mp3_filename;
            $_SESSION['message'] = '기존 MP3 BGM이 유지됩니다.';

            // YouTube 설정은 비웁니다.
            $current_bgm_youtube_id = '';
            $current_bgm_youtube_si = '';
        }
    }
    
    // 설정 값 세션에 저장 (실제 서비스에서는 DB에 저장)
    $_SESSION['bgm_type'] = $current_bgm_type;
    $_SESSION['bgm_youtube_id'] = $current_bgm_youtube_id;
    $_SESSION['bgm_youtube_si'] = $current_bgm_youtube_si;
    $_SESSION['bgm_mp3_filename'] = $current_bgm_mp3_filename;

    // 성공 메시지 표시
    // 페이지 리다이렉트 (POST-REDIRECT-GET 패턴)
    header('Location: settings.php');
    exit;
}

// 최종적으로 플레이어에 사용될 변수 할당
$bgm_link = $current_bgm_youtube_id;
$bgm_si = $current_bgm_youtube_si;
$mp3 = $current_bgm_mp3_filename;

// 메시지 표시 (리다이렉트 후 세션에서 메시지 가져오기)
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']); // 메시지 한 번 표시 후 제거
?>
<div class="settings-container">
    <h2>사이트 설정</h2>
    <form class="ajax-form" action="ajax_save_settings.php" method="post" enctype="multipart/form-data">
        <!-- <div>
            <label for="youtube_url">YouTube 영상 링크 입력:</label>
            <input type="text" id="youtube_url" name="youtube_url"
                   value="<?php echo htmlspecialchars($current_bgm_type === 'youtube' ? 'https://www.youtube.com/watch?v=' . $current_bgm_youtube_id . ($current_bgm_youtube_si ? '&si=' . $current_bgm_youtube_si : '') : ''); ?>"
                   placeholder="예: https://www.youtube.com/watch?v=OisBQ5dj4lI">
            <small>YouTube 링크를 입력하면 MP3 BGM은 자동으로 비활성화됩니다.</small>
        </div>

        <div>
            <label for="mp3_file">MP3 파일 업로드:</label>
            <input type="file" id="mp3_file" name="mp3_file" accept=".mp3">
            <?php if ($current_bgm_type === 'mp3' && !empty($current_bgm_mp3_filename)): ?>
                <p>현재 MP3 BGM: <strong><?php echo htmlspecialchars($current_bgm_mp3_filename); ?></strong></p>
                <input type="hidden" name="existing_mp3_filename" value="<?php echo htmlspecialchars($current_bgm_mp3_filename); ?>">
            <?php else: ?>
                <p>현재 MP3 BGM: 없음</p>
            <?php endif; ?>
            <small>MP3 파일을 업로드하거나 기존 파일을 유지합니다. YouTube 링크가 있으면 MP3는 비활성화됩니다.</small>
        </div>
 -->

        <hr>
        <div class="form-group">
            <label for="main_background">메인 배경화면</label>
            <label for="main_background" class="file-upload-button">파일 선택</label>
            <input type="file" id="main_background" name="main_background" style="display: none;">
            <p style="font-family: Fre1; font-size:16px">현재 이미지: <?php echo basename($settings['main_background']); ?></p>
        </div>
        <hr>
        <button class="submit_btn" type="submit">설정 저장</button>
    </form>
</div>