<?php
require_once 'includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }

$post = ['id' => '', 'type' => 'novel', 'chapter' => '', 'title' => '', 'content' => '', 'thumbnail' => '', 'position_y' => $_GET['y'] ?? 0, 'side' => 'left'];
if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM home2_timeline WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) { $post = $result; }
    $stmt->close();
}
?>
<div class="form-page-container">
    <h2><?php echo $post['id'] ? '타임라인 수정' : '타임라인 추가'; ?></h2>
    <form class="ajax-form" action="ajax_save_timeline.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="position_y" value="<?php echo $post['position_y']; ?>">

        <div class="form-group">
            <label for="type">타임라인 종류</label>
            <select id="type" name="type">
                <option value="novel" <?php echo ($post['type'] == 'novel') ? 'selected' : ''; ?>>소설</option>
                <option value="roleplay" <?php echo ($post['type'] == 'roleplay') ? 'selected' : ''; ?>>역극 백업</option>
                <option value="trpg" <?php echo ($post['type'] == 'trpg') ? 'selected' : ''; ?>>TRPG 로그</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>위치</label>
            <div>
                <label><input type="radio" name="side" value="left" <?php echo ($post['side'] == 'left') ? 'checked' : ''; ?>> 왼쪽</label>
                <label><input type="radio" name="side" value="right" <?php echo ($post['side'] == 'right') ? 'checked' : ''; ?>> 오른쪽</label>
            </div>
        </div>

        <div class="form-group">
            <label>표시 형식</label>
            <div>
                <label><input type="radio" name="display_type" value="dot" <?php echo (!isset($post['display_type']) || $post['display_type'] == 'dot') ? 'checked' : ''; ?>> 점</label>
                <label><input type="radio" name="display_type" value="interval" <?php echo (isset($post['display_type']) && $post['display_type'] == 'interval') ? 'checked' : ''; ?>> 간격</label>
            </div>
        </div>
        <div class="form-group">
            <label for="chapter">챕터명</label>
            <input type="text" id="chapter" name="chapter" value="<?php echo htmlspecialchars($post['chapter']); ?>">
        </div>
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="thumbnail">썸네일 이미지</label>
            <input type="file" id="thumbnail" name="thumbnail">
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>

        <div class="form-buttons">
            <a href="#/timeline" class="btn-cancel">취소</a>
            <button type="submit" class="btn-submit">저장하기</button>
        </div>
    </form>
</div>