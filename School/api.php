<?php
// api.php : School RPG 핵심 로직 (전투, 인벤토리, 상점, 도박, 상태이상 등)
require_once 'common.php';

$input = json_decode(file_get_contents('php://input'), true);
$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : (isset($input['cmd']) ? $input['cmd'] : '');

// 로그인 체크 (로그인 요청 제외)
if ($cmd !== 'login' && !isset($_SESSION['uid'])) {
    json_res(['status'=>'error', 'message'=>'로그인이 필요합니다.']);
}

// 로그인 외 기능 수행 시 생존 여부 및 상태이상 체크
if (isset($_SESSION['uid'])) {
    check_status_evolution(); // 상태이상 시간 경과 체크 (common.php에 정의됨)
    
    // 사망해도 사용 가능한 안전한 명령어들
    $safe_cmds = ['login', 'get_my_info', 'battle_list_users', 'check_incoming_challenge', 'battle_chat_send', 'battle_refresh']; 
    
    // 그 외 명령어는 사망 시 차단
    if (!in_array($cmd, $safe_cmds)) check_alive($_SESSION['uid']);
}

try {
    $my_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
    
    switch ($cmd) {
        // =========================================================
        // [1] 유저 기본 (로그인/정보/프로필)
        // =========================================================
        case 'login':
            $name = trim($input['name']);
            $pw = trim($input['pw']);
            if (!$name || !$pw) throw new Exception("정보를 입력하세요.");
            
            if ($name === 'admin') $user = sql_fetch("SELECT * FROM School_Members WHERE user_id = 'admin'");
            else $user = sql_fetch("SELECT * FROM School_Members WHERE name = ? AND role != 'admin'", [$name]);
            
            if (!$user || !password_verify($pw, $user['pw'])) throw new Exception("정보가 일치하지 않습니다.");
            
            $_SESSION['uid'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            sql_exec("UPDATE School_Members SET last_action_at = NOW() WHERE id = ?", [$user['id']]);
            write_log($user['id'], 'SYSTEM', '로그인 성공');
            json_res(['status'=>'success']);
            break;

        case 'get_my_info':
            $me = sql_fetch("SELECT * FROM School_Members WHERE id = ?", [$my_id]);
            // 나에게 온 대기 중인 결투 신청 확인
            $challenge = sql_fetch("
                SELECT b.room_id, m.name 
                FROM School_Battles b
                JOIN School_Members m ON b.host_id = m.id
                WHERE b.target_id = ? AND b.status = 'WAIT'
                LIMIT 1
            ", [$my_id]);
            
            $me['challenge'] = $challenge;
            json_res(['status'=>'success', 'data'=>$me]);
            break;

        case 'update_profile_img_file':
            if (!isset($_FILES['img_file']) || $_FILES['img_file']['error'] != UPLOAD_ERR_OK) {
                throw new Exception("파일 업로드 실패");
            }
            
            $file = $_FILES['img_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) throw new Exception("이미지 파일만 가능합니다.");
            
            if(!is_dir('uploads')) mkdir('uploads', 0777, true);
            $filename = "profile_{$my_id}_" . time() . "." . $ext; 
            $dest = "uploads/" . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                sql_exec("UPDATE School_Members SET img_profile=? WHERE id=?", [$dest, $my_id]);
                json_res(['status'=>'success']);
            } else {
                throw new Exception("파일 저장 실패");
            }
            break;

        case 'update_profile':
            $img = trim($input['image']);
            if (!$img) throw new Exception("이미지 주소를 입력하세요.");
            sql_exec("UPDATE School_Members SET image_url=? WHERE id=?", [$img, $my_id]);
            write_log($my_id, 'SYSTEM', '프로필 이미지를 변경했습니다.');
            json_res(['status'=>'success', 'msg'=>'변경 완료']);
            break;

        // =========================================================
        // [2] 아이템 사용 및 양도
        // =========================================================
        case 'use_item':
            $inv_id = to_int($input['inv_id']);
            
            $inv = sql_fetch("
                SELECT inv.*, i.type, i.effect_data, i.max_dur, i.name 
                FROM School_Inventory inv 
                JOIN School_Item_Info i ON inv.item_id = i.item_id 
                WHERE inv.id=? AND inv.owner_id=?", 
                [$inv_id, $my_id]
            );
            
            if (!$inv) throw new Exception("아이템이 없습니다.");
            if ($inv['type'] !== 'CONSUME' && $inv['type'] !== 'consumable') throw new Exception("장비는 사용할 수 없습니다. 장착하세요.");
            
            $eff = json_decode($inv['effect_data'], true);
            $msg = "[{$inv['name']}] 사용:";
            $me = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$my_id]);

            // 1. HP 회복
            if (!empty($eff['hp_heal'])) {
                $heal = intval($eff['hp_heal']);
                $new_hp = min($me['hp_max'], $me['hp_current'] + $heal);
                sql_exec("UPDATE School_Members SET hp_current=? WHERE id=?", [$new_hp, $my_id]);
                $msg .= " 체력 {$heal} 회복.";
            }

            // 2. 상태이상 관리 (부여/치료/악화/완화)
            if (!empty($eff['status_id']) && !empty($eff['status_act'])) {
                $sid = intval($eff['status_id']);
                $act = $eff['status_act'];
                
                $st_info = sql_fetch("SELECT name FROM School_Status_Info WHERE status_id=?", [$sid]);
                $st_name = $st_info['name'] ?? '알 수 없는 병';

                if ($act === 'add') {
                    sql_exec("INSERT IGNORE INTO School_Status_Active (target_id, status_id, current_stage, created_at, last_evolved_at) VALUES (?, ?, 1, NOW(), NOW())", [$my_id, $sid]);
                    $msg .= " [{$st_name}]에 감염되었습니다.";
                }
                elseif ($act === 'cure') {
                    sql_exec("DELETE FROM School_Status_Active WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                    $msg .= " [{$st_name}] 치료됨.";
                }
                elseif ($act === 'up') {
                    $chk = sql_fetch("SELECT id FROM School_Status_Active WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                    if($chk) {
                        sql_exec("UPDATE School_Status_Active SET current_stage = current_stage + 1 WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                        $msg .= " [{$st_name}] 악화됨.";
                    }
                }
                elseif ($act === 'down') {
                    $cur = sql_fetch("SELECT current_stage FROM School_Status_Active WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                    if($cur) {
                        if($cur['current_stage'] > 1) {
                            sql_exec("UPDATE School_Status_Active SET current_stage = current_stage - 1 WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                            $msg .= " [{$st_name}] 호전됨.";
                        } else {
                            sql_exec("DELETE FROM School_Status_Active WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                            $msg .= " [{$st_name}] 완치됨.";
                        }
                    }
                }
            }

            // 아이템 차감
            if ($inv['count'] > 1) {
                sql_exec("UPDATE School_Inventory SET count = count - 1 WHERE id=?", [$inv_id]);
            } else {
                sql_exec("DELETE FROM School_Inventory WHERE id=?", [$inv_id]);
            }
            
            write_log($my_id, 'ITEM', $msg);
            json_res(['status'=>'success', 'msg'=>$msg]);
            break;

        case 'transfer':
            $target_id = to_int($input['target_id']);
            $type = $input['type']; 
            
            if ($target_id == $my_id) throw new Exception("자신에게 보낼 수 없습니다.");
            $target = sql_fetch("SELECT id, name FROM School_Members WHERE id=?", [$target_id]);
            if (!$target) throw new Exception("존재하지 않는 대상입니다.");

            $pdo->beginTransaction();
            try {
                if ($type === 'point') {
                    $amount = to_int($input['amount']);
                    if ($amount <= 0) throw new Exception("올바른 금액을 입력하세요.");
                    
                    $me = sql_fetch("SELECT point FROM School_Members WHERE id=? FOR UPDATE", [$my_id]);
                    if ($me['point'] < $amount) throw new Exception("포인트가 부족합니다.");
                    
                    sql_exec("UPDATE School_Members SET point = point - ? WHERE id=?", [$amount, $my_id]);
                    sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$amount, $target_id]);
                    $msg = "{$target['name']}님에게 {$amount} P를 보냈습니다.";
                    write_log($my_id, 'POINT', "{$target['name']}님에게 {$amount} P 양도");
                    write_log($target_id, 'POINT', "{$_SESSION['name']}님으로부터 {$amount} P 받음");
                } 
                elseif ($type === 'item') {
                    $inv_id = to_int($input['inv_id']);
                    $count = to_int($input['count']);
                    if ($count <= 0) throw new Exception("수량을 확인하세요.");

                    $my_inv = sql_fetch("SELECT inv.*, info.name FROM School_Inventory inv JOIN School_Item_Info info ON inv.item_id=info.item_id WHERE inv.id=? AND inv.owner_id=? FOR UPDATE", [$inv_id, $my_id]);
                    if (!$my_inv || $my_inv['count'] < $count) throw new Exception("아이템이 부족합니다.");
                    if ($my_inv['is_equipped']) throw new Exception("장착 중인 아이템은 보낼 수 없습니다.");

                    if ($my_inv['count'] == $count) sql_exec("DELETE FROM School_Inventory WHERE id=?", [$inv_id]);
                    else sql_exec("UPDATE School_Inventory SET count = count - ? WHERE id=?", [$count, $inv_id]);

                    sql_exec("INSERT INTO School_Inventory (owner_id, item_id, count, cur_dur) VALUES (?, ?, ?, ?)", 
                        [$target_id, $my_inv['item_id'], $count, $my_inv['cur_dur']]
                    );
                    $msg = "{$target['name']}님에게 {$my_inv['name']}을(를) 보냈습니다.";
                    write_log($my_id, 'ITEM', "{$target['name']}님에게 {$my_inv['name']} {$count}개 양도");
                    write_log($target_id, 'ITEM', "{$_SESSION['name']}님으로부터 {$my_inv['name']} {$count}개 받음");
                }
                $pdo->commit();
                json_res(['status'=>'success', 'msg'=>$msg]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        // =========================================================
        // [3] 전투 시스템 (다수 몹 & 밸런스 패치 적용)
        // =========================================================
        
        case 'battle_refresh':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status IN ('WAIT','READY','BATTLE','END','FIGHTING')", [$my_id, $my_id]);
            if (!$room) { json_res(['status'=>'none']); break; }

            $chats = sql_fetch_all("SELECT * FROM School_Battle_Chat WHERE room_id=? ORDER BY id ASC", [$room['room_id']]);

            // 내 정보 및 적 정보 구성
            $players = json_decode($room['players_data'], true) ?? [];
            $me_stat = [];
            foreach($players as $p) {
                if($p['id'] == $my_id) $me_stat = $p;
            }
            $mobs = json_decode($room['mob_live_data'], true) ?? [];

            json_res([
                'status' => 'battle',
                'room_stat' => $room['status'],
                'me' => $me_stat,
                'players' => $players,
                'enemies' => $mobs,
                'turn_status' => $room['turn_status'],
                'is_my_turn' => ($room['turn_status'] === 'player' || $room['turn_status'] === 'player_defend'),
                'chats' => $chats
            ]);
            break;

        case 'battle_chat_send':
            $msg = trim($input['msg']);
            if (!$msg) throw new Exception("");
            $room = sql_fetch("SELECT room_id FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status != 'END'", [$my_id, $my_id]);
            if (!$room) throw new Exception("전투 중이 아닙니다.");
            
            $me = sql_fetch("SELECT name FROM School_Members WHERE id=?", [$my_id]);
            sql_exec("INSERT INTO School_Battle_Chat (room_id, user_id, name, message, type) VALUES (?, ?, ?, ?, 'CHAT')", 
                [$room['room_id'], $my_id, $me['name'], $msg]);
            json_res(['status'=>'success']);
            break;

        case 'battle_list_users':
            $list = sql_fetch_all("
                SELECT id, name, level, point, injury 
                FROM School_Members 
                WHERE id != ? AND injury < 4 AND role != 'admin'
                ORDER BY level DESC LIMIT 30
            ", [$my_id]);
            json_res(['status'=>'success', 'list'=>$list]);
            break;

        case 'battle_challenge':
            $target_id = to_int($input['target_id']);
            $target = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$target_id]);
            if (!$target) throw new Exception("존재하지 않는 유저입니다.");
            if ($target['injury'] >= 4) throw new Exception("이미 사망한 유저입니다.");
            
            $chk = sql_fetch("SELECT room_id FROM School_Battles WHERE host_id=? OR guest_id=?", [$my_id, $my_id]);
            if ($chk) throw new Exception("이미 전투 중이거나 대기 중입니다.");

            sql_exec("INSERT INTO School_Battles (host_id, target_id, status, created_at, updated_at) VALUES (?, ?, 'WAIT', NOW(), NOW())", [$my_id, $target_id]);
            write_log($my_id, 'BATTLE', "{$target['name']}님에게 결투를 신청했습니다.");
            json_res(['status'=>'success', 'msg'=>'결투장을 보냈습니다.']);
            break;

        case 'battle_make_room':
            $chk = sql_fetch("SELECT room_id FROM School_Battles WHERE host_id=? OR guest_id=?", [$my_id, $my_id]);
            if ($chk) throw new Exception("이미 참여 중인 전투가 있습니다.");
            
            sql_exec("INSERT INTO School_Battles (host_id, target_id, status, created_at, updated_at) VALUES (?, 0, 'WAIT', NOW(), NOW())", [$my_id]);
            json_res(['status'=>'success']);
            break;

        case 'battle_join':
            $rid = to_int($input['room_id']);
            $room = sql_fetch("SELECT * FROM School_Battles WHERE room_id=? AND status='WAIT'", [$rid]);
            if (!$room) throw new Exception("입장할 수 없는 방입니다.");
            if ($room['target_id'] != 0 && $room['target_id'] != $my_id) throw new Exception("당신에게 온 신청이 아닙니다.");
            
            sql_exec("UPDATE School_Battles SET guest_id=?, status='READY', updated_at=NOW() WHERE room_id=?", [$my_id, $rid]);
            json_res(['status'=>'success']);
            break;

        // 전투 시작 (다수 몹 생성 및 밸런스 적용)
        case 'battle_start':
            $active = sql_fetch("SELECT room_id FROM School_Battles WHERE host_id=? AND status='FIGHTING'", [$my_id]);
            if ($active) json_res(['status'=>'success', 'room_id'=>$active['room_id']]);

            $room = sql_fetch("SELECT * FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status IN ('WAIT','READY','BATTLE','END','FIGHTING')", [$my_id, $my_id]);
            $room = sql_fetch("SELECT * FROM School_Battles WHERE host_id=? AND status IN ('WAIT','READY')", [$my_id]);
            $players_list = [$my_id];
            if ($room && $room['guest_id']) $players_list[] = $room['guest_id'];

            // 1. 몬스터 생성 (1~3마리, 다수 인원 시 추가)
            $mob_count = rand(1, 3);
            if (count($players_list) > 1) $mob_count += rand(1, 2);

            $mob_live_data = [];
            $base_mob = sql_fetch("SELECT * FROM School_Monsters ORDER BY RAND() LIMIT 1");
            if (!$base_mob) throw new Exception("몬스터 데이터가 없습니다.");

            for($i=0; $i<$mob_count; $i++) {
                $m_st = json_decode($base_mob['stats'], true);
                $m_calc = calc_battle_stats($m_st);
                
                // 다수 출현 시 마리당 공격력 5% 너프
                if ($mob_count > 1) {
                    $nerf = 1 - ($mob_count * 0.05);
                    $m_calc['atk'] = floor($m_calc['atk'] * $nerf);
                }

                $mob_live_data[] = [
                    'id' => 'mob_'.$i,
                    'name' => $base_mob['name'] . " " . ($i+1),
                    'hp_max' => $m_calc['hp_max'],
                    'hp_cur' => $m_calc['hp_max'],
                    'atk' => $m_calc['atk'],
                    'def' => $m_calc['def'],
                    'speed' => $m_calc['speed'],
                    'give_exp' => $base_mob['give_exp'],
                    'give_point' => $base_mob['give_point'],
                    'is_dead' => false
                ];
            }

            // 2. 플레이어 데이터 생성
            $players_data = [];
            $max_speed_player = 0;
            
            foreach($players_list as $pid) {
                $p_db = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$pid]);
                $equip = sql_fetch_all("SELECT i.effect_data FROM School_Inventory inv JOIN School_Item_Info i ON inv.item_id=i.item_id WHERE inv.owner_id=? AND inv.is_equipped=1", [$pid]);
                $add_atk=0; $add_def=0;
                foreach($equip as $eq) {
                    $eff = json_decode($eq['effect_data'], true);
                    if(isset($eff['atk'])) $add_atk += $eff['atk'];
                    if(isset($eff['def'])) $add_def += $eff['def'];
                }
                
                // 상태이상 보정
                $p_status = []; // (함수 내부에서 처리하거나 여기서 미리 계산)
                if (function_exists('get_player_status_adjust')) $p_status = get_player_status_adjust($pid);

                $p_calc = calc_battle_stats($p_db, $add_atk, $add_def, $p_status);
                $p_calc['id'] = $pid;
                $p_calc['name'] = $p_db['name'];
                $p_calc['hp_cur'] = $p_db['hp_current'];
                $p_calc['is_dead'] = false;
                
                if ($p_calc['speed'] > $max_speed_player) $max_speed_player = $p_calc['speed'];
                $players_data[] = $p_calc;
            }

            // 3. 선공 결정
            $turn = ($max_speed_player >= $mob_live_data[0]['speed']) ? 'player' : 'enemy_ready';
            $logs = [['msg' => "<b>{$base_mob['name']}</b> 무리({$mob_count}마리)가 나타났다!", 'type' => 'system']];

            if ($room) {
                sql_exec("UPDATE School_Battles SET status='FIGHTING', mob_live_data=?, players_data=?, battle_log=?, turn_status=? WHERE room_id=?", 
                    [json_encode($mob_live_data), json_encode($players_data), json_encode($logs), $turn, $room['room_id']]
                );
                json_res(['status'=>'success', 'room_id'=>$room['room_id']]);
            } else {
                sql_exec("INSERT INTO School_Battles (host_id, status, mob_live_data, players_data, battle_log, turn_status) VALUES (?, 'FIGHTING', ?, ?, ?, ?)", 
                    [$my_id, json_encode($mob_live_data), json_encode($players_data), json_encode($logs), $turn]
                );
                json_res(['status'=>'success', 'room_id'=>$pdo->lastInsertId()]);
            }
            break;

        case 'battle_info':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id, $my_id]);
            if (!$room) json_res(['status'=>'ended']);

            $room['mob_live_data'] = json_decode($room['mob_live_data'], true);
            $room['players_data'] = json_decode($room['players_data'], true);
            $room['battle_log'] = json_decode($room['battle_log'], true);
            
            // 적 턴 시작 처리
            if ($room['turn_status'] === 'enemy_ready') {
                $alive_mobs = array_filter($room['mob_live_data'], function($m){ return !$m['is_dead']; });
                
                if (empty($alive_mobs)) {
                    // 몹 전멸 -> 플레이어 턴으로 넘겨서 승리 처리 유도
                    sql_exec("UPDATE School_Battles SET turn_status='player' WHERE room_id=?", [$room['room_id']]);
                } else {
                    $atk_roll = rand(1, 100);
                    $msg = "👹 <b>좀비들</b>이 공격해옵니다! (총 " . count($alive_mobs) . "마리)<br>어떻게 할까? [반격 / 회피 / 맞기]";
                    $room['battle_log'][] = ['msg'=>$msg, 'type'=>'enemy'];

                    sql_exec("UPDATE School_Battles SET turn_status=?, enemy_roll=?, battle_log=? WHERE room_id=?", 
                        ['player_defend', $atk_roll, json_encode($room['battle_log']), $room['room_id']]
                    );
                }
            }
            json_res(['status'=>'playing', 'data'=>$room]);
            break;

        // 플레이어 공격
        case 'battle_action_attack':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id, $my_id]);
            if (!$room || $room['turn_status'] !== 'player') throw new Exception("당신의 턴이 아닙니다.");

            $mobs = json_decode($room['mob_live_data'], true);
            $players = json_decode($room['players_data'], true);
            $logs = json_decode($room['battle_log'], true);
            
            $me = null;
            foreach($players as $p) if($p['id'] == $my_id) $me = $p;
            
            $target_idx = -1;
            foreach($mobs as $idx => $m) {
                if (!$m['is_dead']) { $target_idx = $idx; break; }
            }
            
            if ($target_idx === -1) throw new Exception("적들이 이미 모두 쓰러졌습니다.");
            $target = &$mobs[$target_idx];

            // [공격] 내 공격력 - 적 방어력
            $dmg = max(1, $me['atk'] - $target['def']);
            
            $is_crit = (rand(1, 100) > 90);
            if ($is_crit) {
                $dmg = floor($dmg * 1.5);
                $msg = "⚡ <b>치명타!</b> ";
            } else {
                $msg = "";
            }
            $msg .= "⚔️ <b>{$target['name']}</b>에게 {$dmg} 피해!";
            
            $target['hp_cur'] -= $dmg;
            $logs[] = ['msg'=>$msg, 'type'=>'player'];

            // [내구도 감소] 무기 (WEAPON 타입만)
            $weapon = sql_fetch("SELECT inv.id, inv.cur_dur, i.name FROM School_Inventory inv JOIN School_Item_Info i ON inv.item_id=i.item_id WHERE inv.owner_id=? AND inv.is_equipped=1 AND i.type='WEAPON' LIMIT 1", [$my_id]);
            if ($weapon && $weapon['cur_dur'] > 0 && rand(1,5)==1) {
                sql_exec("UPDATE School_Inventory SET cur_dur = cur_dur - 1 WHERE id=?", [$weapon['id']]);
            }

            // 처치 확인
            if ($target['hp_cur'] <= 0) {
                $target['hp_cur'] = 0; $target['is_dead'] = true;
                $logs[] = ['msg'=>"💀 <b>{$target['name']}</b> 처치!", 'type'=>'system'];
            }

            // 전멸 체크 및 보상
            $all_dead = true;
            foreach($mobs as $m) if(!$m['is_dead']) $all_dead = false;

            if ($all_dead) {
                $msg_reward = "";
                foreach($players as $p) {
                    $total_exp = 0; $total_point = 0;
                    foreach($mobs as $m) {
                        $total_exp += ($m['give_exp'] ?? 20);
                        $total_point += ($m['give_point'] ?? 40);
                    }
                    
                    $db_user = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$p['id']]);
                    $db_user['exp'] += $total_exp;
                    $db_user['point'] += $total_point;
                    
                    $lv_up = 0;
                    while(true) {
                        $req = $db_user['level'] * 10;
                        if($db_user['exp'] >= $req) {
                            $db_user['exp'] -= $req;
                            $db_user['level']++;
                            $db_user['point'] += 200;
                            $db_user['stat_str']+=2; $db_user['stat_dex']+=2; $db_user['stat_con']+=2; $db_user['stat_int']+=2; $db_user['stat_luk']+=2;
                            $db_user['hp_max'] = $db_user['stat_con'];
                            $lv_up++;
                        } else break;
                    }
                    
                    sql_exec("UPDATE School_Members SET level=?, exp=?, point=?, hp_max=?, stat_str=?, stat_dex=?, stat_con=?, stat_int=?, stat_luk=? WHERE id=?", 
                        [$db_user['level'], $db_user['exp'], $db_user['point'], $db_user['hp_max'], 
                         $db_user['stat_str'], $db_user['stat_dex'], $db_user['stat_con'], $db_user['stat_int'], $db_user['stat_luk'], $p['id']]
                    );
                    
                    $msg_reward .= "<br>{$p['name']}: Exp +{$total_exp}, Point +{$total_point}" . ($lv_up?" (LvUP!)":"");
                }
                
                $logs[] = ['msg'=>"🏆 <b>전투 승리!</b>".$msg_reward, 'type'=>'system'];
                write_log($my_id, 'BATTLE', "전투 승리");
                
                sql_exec("UPDATE School_Battles SET status='ENDED', mob_live_data=?, players_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($mobs), json_encode($players), json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'win']);
            } else {
                sql_exec("UPDATE School_Battles SET turn_status='enemy_ready', mob_live_data=?, players_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($mobs), json_encode($players), json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'success']);
            }
            break;

        // 플레이어 방어 (적 턴)
        case 'battle_action_defend':
            $type = $input['type'];
            $room = sql_fetch("SELECT * FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id, $my_id]);
            if (!$room || $room['turn_status'] !== 'player_defend') throw new Exception("타이밍이 아닙니다.");

            $mobs = json_decode($room['mob_live_data'], true);
            $players = json_decode($room['players_data'], true);
            $logs = json_decode($room['battle_log'], true);
            
            // 살아있는 몹들이 랜덤 플레이어 공격
            $alive_mobs = array_filter($mobs, function($m){ return !$m['is_dead']; });
            
            foreach($alive_mobs as $mob) {
                $alive_players_idx = [];
                foreach($players as $idx=>$p) if(!$p['is_dead']) $alive_players_idx[] = $idx;
                if (empty($alive_players_idx)) break; 
                
                $target_idx = $alive_players_idx[array_rand($alive_players_idx)];
                $me = &$players[$target_idx];

                $dmg_msg = "";
                $is_hit = false;

                // 방어 행동 판정
                if ($type === 'dodge') {
                    $chance = min(90, $me['dex'] * 3);
                    if (rand(1, 100) <= $chance) $dmg_msg = "💨 {$me['name']} 회피!";
                    else $is_hit = true;
                } elseif ($type === 'counter') {
                    if (rand(1,100) > $room['enemy_roll']) {
                        $c_dmg = max(1, $me['atk'] - $mob['def']);
                        $mob['hp_cur'] -= $c_dmg;
                        $dmg_msg = "✨ {$me['name']} 반격 성공! ({$c_dmg} 피해)";
                    } else {
                        $is_hit = true;
                        $dmg_msg = "💦 반격 실패..";
                    }
                } else {
                    $is_hit = true; // hit
                }

                // 피격 처리
                if ($is_hit) {
                    $base_dmg = max(1, $mob['atk'] - $me['def']);
                    if ($type === 'hit') $base_dmg = round($base_dmg * 0.7); // 방어 시 경감
                    
                    $me['hp_cur'] -= $base_dmg;
                    $dmg_msg .= " 💥 {$me['name']} 피격 (-{$base_dmg})";
                    
                    // 방어구 내구도 (모든 부위)
                    $armor = sql_fetch("SELECT inv.id, inv.cur_dur FROM School_Inventory inv JOIN School_Item_Info i ON inv.item_id=i.item_id WHERE inv.owner_id=? AND inv.is_equipped=1 AND i.type IN ('HAT','FACE','TOP','BOTTOM','GLOVES','SHOES','ARMOR') ORDER BY RAND() LIMIT 1", [$me['id']]);
                    if($armor && rand(1,5)==1) sql_exec("UPDATE School_Inventory SET cur_dur = cur_dur - 1 WHERE id=?", [$armor['id']]);
                }
                
                $logs[] = ['msg'=>"<b>{$mob['name']}</b>의 공격: " . $dmg_msg, 'type'=>'enemy'];
                
                // 사망 체크
                if ($me['hp_cur'] <= 0) {
                    $me['hp_cur'] = 0; $me['is_dead'] = true;
                    $logs[] = ['msg'=>"💀 <b>{$me['name']}</b>님이 쓰러졌습니다...", 'type'=>'system'];
                }
            }

            // 전멸 체크
            $all_dead = true;
            foreach($players as $p) if(!$p['is_dead']) $all_dead = false;

            if ($all_dead) {
                $logs[] = ['msg'=>"전멸했습니다... (패배)", 'type'=>'system'];
                
                // 패널티 및 상태이상 심화 적용
                foreach($players as $p) {
                    // 1. 포인트 감소
                    sql_exec("UPDATE School_Members SET hp_current=1, point=GREATEST(0, point-50) WHERE id=?", [$p['id']]);
                    
                    // 2. 상태이상 단계 상승 (패배 시)
                    // 현재 활성화된 상태이상이 있다면 1개 골라서 단계 상승
                    $active_status = sql_fetch("SELECT id, status_id, current_stage FROM School_Status_Active WHERE target_id=? ORDER BY RAND() LIMIT 1", [$p['id']]);
                    if ($active_status) {
                        $s_info = sql_fetch("SELECT max_stage, name FROM School_Status_Info WHERE status_id=?", [$active_status['status_id']]);
                        if ($active_status['current_stage'] < $s_info['max_stage']) {
                            sql_exec("UPDATE School_Status_Active SET current_stage = current_stage + 1 WHERE id=?", [$active_status['id']]);
                            write_log($p['id'], 'BATTLE', "패배로 인한 {$s_info['name']} 악화");
                        }
                    } else {
                        // 상태이상이 없다면 랜덤 감염 (선택사항)
                        $rnd_st = sql_fetch("SELECT status_id FROM School_Status_Info ORDER BY RAND() LIMIT 1");
                        if ($rnd_st) {
                            sql_exec("INSERT INTO School_Status_Active (target_id, status_id, current_stage) VALUES (?, ?, 1)", [$p['id'], $rnd_st['status_id']]);
                        }
                    }
                }

                sql_exec("UPDATE School_Battles SET status='ENDED', players_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($players), json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'lose']);
            } else {
                sql_exec("UPDATE School_Battles SET turn_status='player', mob_live_data=?, players_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($mobs), json_encode($players), json_encode($logs), $room['room_id']]
                );
                foreach($players as $p) {
                    sql_exec("UPDATE School_Members SET hp_current=? WHERE id=?", [$p['hp_cur'], $p['id']]);
                }
                json_res(['status'=>'success']);
            }
            break;

        case 'battle_run':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE (host_id=? OR guest_id=?) AND status='FIGHTING'", [$my_id, $my_id]);
            if(rand(1,100) <= 50) {
                sql_exec("UPDATE School_Battles SET status='ENDED' WHERE room_id=?", [$room['room_id']]);
                json_res(['status'=>'success', 'msg'=>'도망쳤습니다!']);
            } else {
                json_res(['status'=>'fail', 'msg'=>'도망 실패!']);
            }
            break;

        // =========================================================
        // [4] 인벤토리 액션 (장비 슬롯 제한 등)
        // =========================================================
        case 'inventory_action':
            $inv_id = to_int($input['inv_id']);
            $action = $input['action']; 
            
            $item = sql_fetch("SELECT inv.*, info.type, info.name, info.effect_data 
                               FROM School_Inventory inv 
                               JOIN School_Item_Info info ON inv.item_id = info.item_id 
                               WHERE inv.id=? AND inv.owner_id=?", [$inv_id, $my_id]);
            
            if (!$item) throw new Exception("아이템이 존재하지 않습니다.");

            if ($action === 'equip') {
                $allowed_slots = ['WEAPON', 'HAT', 'FACE', 'TOP', 'BOTTOM', 'GLOVES', 'SHOES'];
                
                if ($item['type'] === 'ETC') {
                     $cnt = sql_fetch("SELECT count(*) as c FROM School_Inventory inv 
                                       JOIN School_Item_Info info ON inv.item_id = info.item_id 
                                       WHERE inv.owner_id=? AND inv.is_equipped=1 AND info.type='ETC'", [$my_id]);
                     if ($cnt['c'] >= 5) throw new Exception("장신구(기타)는 최대 5개까지만 장착 가능합니다.");
                } 
                elseif (in_array($item['type'], $allowed_slots)) {
                    // 같은 부위 자동 해제
                    sql_exec("UPDATE School_Inventory inv 
                              JOIN School_Item_Info info ON inv.item_id = info.item_id 
                              SET inv.is_equipped = 0 
                              WHERE inv.owner_id = ? AND info.type = ? AND inv.is_equipped = 1", 
                              [$my_id, $item['type']]);
                } 
                else {
                    throw new Exception("이 아이템은 장착할 수 없습니다.");
                }

                sql_exec("UPDATE School_Inventory SET is_equipped = 1 WHERE id=?", [$inv_id]);
                write_log($my_id, 'ITEM', "{$item['name']} 장착");
                json_res(['status'=>'success', 'msg'=>'장착 완료']);
            } 
            elseif ($action === 'unequip') {
                sql_exec("UPDATE School_Inventory SET is_equipped = 0 WHERE id=?", [$inv_id]);
                write_log($my_id, 'ITEM', "{$item['name']} 해제");
                json_res(['status'=>'success', 'msg'=>'해제 완료']);
            } 
            elseif ($action === 'use') {
                // (위의 use_item과 로직 공유하거나 여기서 호출)
                // 편의상 use_item case를 다시 호출하는 게 좋지만, 구조상 복붙
                if ($item['type'] !== 'CONSUME' && $item['type'] !== 'consumable') throw new Exception("사용할 수 없는 아이템입니다.");
                
                $eff = json_decode($item['effect_data'], true);
                $me = sql_fetch("SELECT hp_current, hp_max FROM School_Members WHERE id=?", [$my_id]);
                
                if (isset($eff['hp_heal'])) {
                    $new_hp = min($me['hp_max'], $me['hp_current'] + $eff['hp_heal']);
                    sql_exec("UPDATE School_Members SET hp_current=? WHERE id=?", [$new_hp, $my_id]);
                }
                
                if ($item['count'] > 1) sql_exec("UPDATE School_Inventory SET count = count - 1 WHERE id=?", [$inv_id]);
                else sql_exec("DELETE FROM School_Inventory WHERE id=?", [$inv_id]);
                
                json_res(['status'=>'success', 'msg'=>'아이템 사용 완료']);
            }
            break;

        // =========================================================
        // [5] 도박 (홀짝, 룰렛, 블랙잭)
        // =========================================================
        case 'gamble_hj':
            $amount = to_int($input['amount']);
            $pick = $input['pick'];
            if ($amount <= 0) throw new Exception("배팅 금액 확인");
            
            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            if ($me['point'] < $amount) throw new Exception("포인트 부족");
            sql_exec("UPDATE School_Members SET point = point - ? WHERE id=?", [$amount, $my_id]);

            $dice = rand(1, 10);
            $result = ($dice % 2 !== 0) ? 'odd' : 'even';
            $is_win = ($pick === $result);
            $current_point = $me['point'] - $amount;
            $gain = 0;

            if ($is_win) {
                $gain = floor($amount * 2);
                $current_point += $gain;
                sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$gain, $my_id]);
                write_log($my_id, 'GAMBLE', "홀짝 승리 (+{$gain} P)");
                json_res(['status'=>'win', 'result'=>$result, 'gain'=>$gain, 'current_point'=>$current_point]);
            } else {
                write_log($my_id, 'GAMBLE', "홀짝 패배 (-{$amount} P)");
                json_res(['status'=>'lose', 'result'=>$result, 'current_point'=>$current_point]);
            }
            break;

        case 'gamble_roulette':
            $bet = to_int($input['bet']);
            if ($bet <= 0) throw new Exception("금액 오류");
            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            if ($me['point'] < $bet) throw new Exception("포인트 부족");

            $item = sql_fetch("SELECT * FROM School_Gamble_Config ORDER BY RAND() LIMIT 1");
            if (!$item) throw new Exception("룰렛 설정 없음");

            $ratio = floatval($item['ratio']);
            $change = floor($bet * $ratio);
            
            sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$change, $my_id]);
            write_log($my_id, 'GAMBLE', "룰렛: {$change} P");

            json_res([
                'status'=>'success', 'result_name'=>$item['name'], 
                'ratio'=>$ratio, 'change'=>$change,
                'now_point'=> ($me['point'] + $change)
            ]);
            break;

        case 'gamble_bj_start':
            $amount = to_int($input['amount']);
            if ($amount <= 0) throw new Exception("금액 오류");
            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            if ($me['point'] < $amount) throw new Exception("포인트 부족");
            
            sql_exec("UPDATE School_Members SET point = point - ? WHERE id=?", [$amount, $my_id]);
            
            $p_hand = [rand(1, 13), rand(1, 13)];
            $d_hand = [rand(1, 13), rand(1, 13)];
            $_SESSION['bj_game'] = ['bet' => $amount, 'p_hand' => $p_hand, 'd_hand' => $d_hand, 'status' => 'playing'];
            
            json_res(['status'=>'success', 'data'=>['player_hand'=>$p_hand, 'dealer_hand'=>$d_hand, 'player_score'=>calc_bj_score($p_hand), 'dealer_score'=>calc_bj_score($d_hand)], 'current_point'=>$me['point']-$amount]);
            break;

        case 'gamble_bj_action':
            if (!isset($_SESSION['bj_game']) || $_SESSION['bj_game']['status'] !== 'playing') throw new Exception("게임 없음");
            $game = &$_SESSION['bj_game'];
            $action = $input['action'];
            $is_end = false; $msg = "";
            
            if ($action === 'hit') {
                $game['p_hand'][] = rand(1, 13);
                if (calc_bj_score($game['p_hand']) > 21) { $is_end = true; $msg = "버스트! 패배"; }
            } elseif ($action === 'stand') {
                while (calc_bj_score($game['d_hand']) < 17) { $game['d_hand'][] = rand(1, 13); }
                $is_end = true;
                $p_score = calc_bj_score($game['p_hand']);
                $d_score = calc_bj_score($game['d_hand']);
                $bet = $game['bet'];
                $win = 0;
                
                if ($d_score > 21 || $p_score > $d_score) { $msg = "승리!"; $win = $bet*2; }
                elseif ($p_score == $d_score) { $msg = "무승부"; $win = $bet; }
                else { $msg = "패배..."; }
                
                if ($win > 0) sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$win, $my_id]);
            }

            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            $data = ['player_hand' => $game['p_hand'], 'dealer_hand' => $game['d_hand'], 'player_score' => calc_bj_score($game['p_hand']), 'dealer_score' => calc_bj_score($game['d_hand'])];
            
            if ($is_end) {
                unset($_SESSION['bj_game']);
                json_res(['status'=>'end', 'data'=>$data, 'msg'=>$msg, 'current_point'=>$me['point']]);
            } else {
                json_res(['status'=>'playing', 'data'=>$data]);
            }
            break;

        default: throw new Exception("알 수 없는 요청: $cmd");
    }

} catch (Exception $e) {
    json_res(['status'=>'error', 'message'=>$e->getMessage()]);
}

// ---------------------------------------------------------
// [헬퍼 함수]
// ---------------------------------------------------------

function calc_bj_score($hand) {
    $score = 0;
    foreach ($hand as $card) {
        if ($card >= 11 && $card <= 13) $score += 10;
        else if ($card == 1) $score += 1;
        else $score += $card;
    }
    return $score;
}

function calc_battle_stats($base_stats, $add_atk=0, $add_def=0, $status_adjust=[]) {
    $str = $base_stats['stat_str'] ?? 10;
    $dex = $base_stats['stat_dex'] ?? 10;
    $con = $base_stats['stat_con'] ?? 10;
    $int = $base_stats['stat_int'] ?? 10;
    $luk = $base_stats['stat_luk'] ?? 10;

    $status_atk = $status_adjust['atk'] ?? 0;
    $status_def = $status_adjust['def'] ?? 0;

    // 공식: (ATK = 스텟반영 + 템 + 상태), (DEF = 스텟반영 + 템 + 상태)
    $atk = round(($str*0.4) + ($dex*0.3) + ($con*0.1) + ($luk*0.1) + ($int*0.1)) + $add_atk + $status_atk;
    $def = round(($con*0.5) + ($dex*0.3) + ($int*0.1) + ($luk*0.1)) + $add_def + $status_def;
    
    if ($atk < 1) $atk = 1;
    if ($def < 0) $def = 0;

    return ['atk' => $atk, 'def' => $def, 'hp_max' => $con, 'speed' => $dex, 'str' => $str, 'dex' => $dex, 'con' => $con, 'int' => $int, 'luk' => $luk];
}

// 플레이어 상태이상 보정값 가져오는 헬퍼 (함수화)
function get_player_status_adjust($uid) {
    $my_status = sql_fetch_all("
        SELECT s.current_stage, i.stage_config 
        FROM School_Status_Active s 
        JOIN School_Status_Info i ON s.status_id = i.status_id 
        WHERE s.target_id = ?
    ", [$uid]);

    $st_atk = 0; $st_def = 0;
    foreach($my_status as $st) {
        $cfg = json_decode($st['stage_config'], true);
        $stage = $st['current_stage'];
        if(isset($cfg[$stage])) {
            $st_atk += ($cfg[$stage]['atk'] ?? 0);
            $st_def += ($cfg[$stage]['def'] ?? 0);
        }
    }
    return ['atk' => $st_atk, 'def' => $st_def];
}
?>