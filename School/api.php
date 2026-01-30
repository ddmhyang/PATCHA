<?php
// api.php : 상태이상 단계 상승 패치 포함 최종본
require_once 'common.php';

$input = json_decode(file_get_contents('php://input'), true);
$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : (isset($input['cmd']) ? $input['cmd'] : '');

// 로그인 체크 (로그인 요청 제외)
if ($cmd !== 'login' && !isset($_SESSION['uid'])) {
    json_res(['status'=>'error', 'message'=>'로그인이 필요합니다.']);
}

// [중요] API 호출 시마다 상태이상 시간 경과 체크 (자동 진화)
if (isset($_SESSION['uid'])) {
    check_status_evolution();
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
            if (!$me) { session_destroy(); throw new Exception("정보 없음"); }
            json_res(['status'=>'success', 'data'=>$me]);
            break;

        case 'update_profile':
            $img = trim($input['image']);
            if (!$img) throw new Exception("이미지 주소를 입력하세요.");
            sql_exec("UPDATE School_Members SET image_url=? WHERE id=?", [$img, $my_id]);
            write_log($my_id, 'SYSTEM', '프로필 이미지를 변경했습니다.');
            json_res(['status'=>'success', 'msg'=>'변경 완료']);
            break;

        // =========================================================
        // [2] 양도 시스템 (포인트/아이템)
        // =========================================================
        case 'transfer':
            $target_id = to_int($input['target_id']);
            $type = $input['type']; // 'point' or 'item'
            
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

                    // 내 가방 차감
                    if ($my_inv['count'] == $count) sql_exec("DELETE FROM School_Inventory WHERE id=?", [$inv_id]);
                    else sql_exec("UPDATE School_Inventory SET count = count - ? WHERE id=?", [$count, $inv_id]);

                    // 상대 가방 추가 (내구도 유지)
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
        // [3] 전투 시스템
        // =========================================================
        
        // 3-1. 전투 시작
        case 'battle_start':
            $active = sql_fetch("SELECT room_id FROM School_Battles WHERE host_id=? AND status='FIGHTING'", [$my_id]);
            if ($active) json_res(['status'=>'success', 'room_id'=>$active['room_id']]);

            $mob = sql_fetch("SELECT * FROM School_Monsters ORDER BY RAND() LIMIT 1");
            if (!$mob) throw new Exception("몬스터가 없습니다.");

            // 몬스터 스텟 계산
            $m_st = json_decode($mob['stats'], true);
            $m_calc = calc_battle_stats([
                'stat_str'=>$m_st['str']??10, 'stat_dex'=>$m_st['dex']??10, 
                'stat_con'=>$m_st['con']??10, 'stat_int'=>$m_st['int']??10, 'stat_luk'=>$m_st['luk']??10
            ]);
            
            $mob_data = [[
                'name' => $mob['name'],
                'hp_max' => $m_calc['hp_max'],
                'hp_cur' => $m_calc['hp_max'],
                'atk' => $m_calc['atk'],
                'def' => $m_calc['def'],
                'speed' => $m_calc['speed'],
                'dex' => $m_calc['dex'],
                'drop' => $mob['drop_items'],
                // [보상 정보 저장]
                'give_exp' => $mob['give_exp'],
                'give_point' => $mob['give_point'],
                'is_dead' => false
            ]];

            // 플레이어 스텟 계산 (장비 포함)
            $me = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$my_id]);
            $equip = sql_fetch_all("SELECT i.effect_data FROM School_Inventory inv JOIN School_Item_Info i ON inv.item_id=i.item_id WHERE inv.owner_id=? AND inv.is_equipped=1", [$my_id]);
            $add_atk = 0; $add_def = 0;
            foreach($equip as $eq) {
                $eff = json_decode($eq['effect_data'], true);
                if(isset($eff['atk'])) $add_atk += $eff['atk'];
                if(isset($eff['def'])) $add_def += $eff['def'];
            }
            $p_calc = calc_battle_stats($me, $add_atk, $add_def);
            $p_calc['hp_cur'] = $me['hp_current'];
            $p_calc['name'] = $me['name'];

            // 선공 결정 (스피드)
            $turn = ($p_calc['speed'] >= $m_calc['speed']) ? 'player' : 'enemy_ready';
            
            $logs = [['msg' => "야생의 <b>{$mob['name']}</b>(이)가 나타났다!", 'type' => 'system']];

            sql_exec("INSERT INTO School_Battles (host_id, status, mob_live_data, players_data, battle_log, turn_status) VALUES (?, 'FIGHTING', ?, ?, ?, ?)", 
                [$my_id, json_encode($mob_data), json_encode([$p_calc]), json_encode($logs), $turn]
            );
            write_log($my_id, 'BATTLE', "{$mob['name']}와 전투 시작");
            json_res(['status'=>'success', 'room_id'=>$pdo->lastInsertId()]);
            break;

        // 3-2. 전투 정보 조회
        case 'battle_info':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE host_id=? AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id]);
            if (!$room) json_res(['status'=>'ended']);

            $room['mob_live_data'] = json_decode($room['mob_live_data'], true);
            $room['players_data'] = json_decode($room['players_data'], true);
            $room['battle_log'] = json_decode($room['battle_log'], true);
            
            if ($room['turn_status'] === 'enemy_ready') {
                $mob = &$room['mob_live_data'][0];
                $atk_roll = rand(1, 100); 
                
                $room['turn_status'] = 'player_defend'; 
                $room['enemy_roll'] = $atk_roll;
                
                $msg = "👹 <b>{$mob['name']}</b>의 공격!<br>어떻게 할까? [반격 / 회피 / 맞기]";
                $room['battle_log'][] = ['msg'=>$msg, 'type'=>'enemy'];

                sql_exec("UPDATE School_Battles SET turn_status=?, enemy_roll=?, battle_log=? WHERE room_id=?", 
                    ['player_defend', $atk_roll, json_encode($room['battle_log']), $room['room_id']]
                );
            }
            json_res(['status'=>'playing', 'data'=>$room]);
            break;

        // 3-3. 플레이어 공격 (내구도 감소, 승리 시 레벨업)
        case 'battle_action_attack':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE host_id=? AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id]);
            if (!$room || $room['turn_status'] !== 'player') throw new Exception("당신의 턴이 아닙니다.");

            $mobs = json_decode($room['mob_live_data'], true);
            $players = json_decode($room['players_data'], true);
            $logs = json_decode($room['battle_log'], true);
            
            $me = $players[0];
            $target = &$mobs[0];

            // 데미지 계산
            $dice = rand(1, 100);
            $base_dmg = floor($me['atk'] / 10);
            
            $is_crit = ($dice > 90);
            if ($is_crit) $base_dmg = floor($base_dmg * 1.5);
            if ($base_dmg < 1) $base_dmg = 1;

            // 몬스터 방어 확률
            $mob_def_roll = rand(1, 100);
            $final_dmg = $base_dmg;
            if ($mob_def_roll <= $target['def']) {
                $final_dmg = round($base_dmg * 0.75); // 방어 성공 시 데미지 75%
            }

            $target['hp_cur'] -= $final_dmg;
            
            $msg = "⚔️ <b>{$target['name']}</b>에게 공격! <b style='color:#e74c3c'>HP -{$final_dmg}</b>";
            if ($is_crit) $msg = "⚡ <b>급소에 맞았다!</b> " . $msg;
            $logs[] = ['msg'=>$msg, 'type'=>'player'];

            // [내구도 감소] 무기
            $wep = sql_fetch("SELECT inv.id, inv.cur_dur, info.name FROM School_Inventory inv JOIN School_Item_Info info ON inv.item_id=info.item_id WHERE inv.owner_id=? AND inv.is_equipped=1 AND info.type='WEAPON' LIMIT 1", [$my_id]);
            if ($wep && $wep['cur_dur'] > 0) {
                $new_dur = $wep['cur_dur'] - 1;
                if ($new_dur <= 0) {
                    sql_exec("DELETE FROM School_Inventory WHERE id=?", [$wep['id']]);
                    $logs[] = ['msg'=>"💥 <b>{$wep['name']}</b>이(가) 부서졌습니다!", 'type'=>'system'];
                    write_log($my_id, 'ITEM', "무기 {$wep['name']} 파괴됨");
                } else {
                    sql_exec("UPDATE School_Inventory SET cur_dur=? WHERE id=?", [$new_dur, $wep['id']]);
                }
            }

            // 승리 판정 & 레벨업
            if ($target['hp_cur'] <= 0) {
                $target['hp_cur'] = 0; $target['is_dead'] = true;
                
                // 보상
                $gain_exp = $target['give_exp'] ?? 10;
                $gain_point = $target['give_point'] ?? 50;
                
                $real_me = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$my_id]);
                $real_me['exp'] += $gain_exp;
                $real_me['point'] += $gain_point;
                
                $logs[] = ['msg'=>"<b>{$target['name']}</b>(은)는 쓰러졌다!<br>(Exp +{$gain_exp}, Point +{$gain_point})", 'type'=>'system'];
                write_log($my_id, 'BATTLE', "{$target['name']} 처치 (Exp +{$gain_exp}, Point +{$gain_point})");

                // [레벨업 루프]
                $levelup_count = 0;
                while(true) {
                    $req_exp = $real_me['level'] * 10; // 필요 경험치 = 레벨 * 10
                    if ($real_me['exp'] >= $req_exp) {
                        $real_me['exp'] -= $req_exp;
                        $real_me['level']++;
                        $real_me['point'] += 200; // 레벨업 보너스
                        // 스텟 상승
                        $real_me['stat_str'] += 2; $real_me['stat_dex'] += 2; 
                        $real_me['stat_con'] += 2; $real_me['stat_int'] += 2; $real_me['stat_luk'] += 2;
                        $real_me['hp_max'] = $real_me['stat_con']; // 체력 공식 갱신
                        $levelup_count++;
                    } else {
                        break;
                    }
                }

                if ($levelup_count > 0) {
                    $logs[] = ['msg'=>"🎉 <b>레벨 업! (Lv.{$real_me['level']})</b><br>모든 스텟 +".($levelup_count*2).", 보너스 +".($levelup_count*200)."P", 'type'=>'system'];
                    write_log($my_id, 'SYSTEM', "레벨 업! (Lv.{$real_me['level']})");
                }

                // DB 업데이트
                sql_exec("UPDATE School_Members SET level=?, exp=?, point=?, hp_max=?, stat_str=?, stat_dex=?, stat_con=?, stat_int=?, stat_luk=? WHERE id=?", 
                    [$real_me['level'], $real_me['exp'], $real_me['point'], $real_me['hp_max'], 
                     $real_me['stat_str'], $real_me['stat_dex'], $real_me['stat_con'], $real_me['stat_int'], $real_me['stat_luk'], $my_id]
                );
                
                sql_exec("UPDATE School_Battles SET status='ENDED', mob_live_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($mobs), json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'win']);
            } else {
                sql_exec("UPDATE School_Battles SET turn_status='enemy_ready', mob_live_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($mobs), json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'success']);
            }
            break;

        // 3-4. 플레이어 방어 (반격/회피/맞기 + 페널티 + 상태이상 중첩)
        case 'battle_action_defend':
            $type = $input['type'];
            $room = sql_fetch("SELECT * FROM School_Battles WHERE host_id=? AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id]);
            if (!$room || $room['turn_status'] !== 'player_defend') throw new Exception("타이밍이 아닙니다.");

            $mobs = json_decode($room['mob_live_data'], true);
            $players = json_decode($room['players_data'], true);
            $logs = json_decode($room['battle_log'], true);
            
            $me = &$players[0];
            $mob = &$mobs[0];
            $enemy_roll = $room['enemy_roll'];

            $msg = "";
            $is_hit = false;

            if ($type === 'counter') {
                $my_roll = rand(1, 100);
                if ($my_roll > $enemy_roll) {
                    $dmg = floor($me['atk'] / 10);
                    $mob['hp_cur'] -= $dmg;
                    $msg = "✨ <b>반격 성공!</b> <b>{$mob['name']}</b>에게 <b style='color:red'>HP -{$dmg}</b>";
                } else {
                    $is_hit = true;
                    $msg = "💦 반격 실패...";
                }
            } 
            elseif ($type === 'dodge') {
                $chance = min(90, $me['dex'] * 3);
                $roll = rand(1, 100);
                if ($roll <= $chance) {
                    $msg = "💨 공격을 가볍게 회피했다!";
                } else {
                    $is_hit = true;
                    $msg = "💦 회피 실패!";
                }
            } 
            elseif ($type === 'hit') {
                $is_hit = true;
                $msg = "🛡️ 공격을 받아냈다.";
            }

            if ($is_hit) {
                $base_dmg = floor($mob['atk'] / 10);
                $my_def_roll = rand(1, 100);
                $final_dmg = $base_dmg;

                if ($my_def_roll <= $me['def']) {
                    $final_dmg = round($base_dmg * 0.75); // 방어 성공
                }

                $me['hp_cur'] -= $final_dmg;
                $msg .= " <b style='color:red'>HP -{$final_dmg}</b> 피해를 입었다.";

                // [내구도 감소] 방어구
                $armor = sql_fetch("SELECT inv.id, inv.cur_dur, info.name FROM School_Inventory inv JOIN School_Item_Info info ON inv.item_id=info.item_id WHERE inv.owner_id=? AND inv.is_equipped=1 AND info.type='ARMOR' LIMIT 1", [$my_id]);
                if ($armor && $armor['cur_dur'] > 0) {
                    $new_dur = $armor['cur_dur'] - 1;
                    if ($new_dur <= 0) {
                        sql_exec("DELETE FROM School_Inventory WHERE id=?", [$armor['id']]);
                        $logs[] = ['msg'=>"💥 <b>{$armor['name']}</b>이(가) 부서졌습니다!", 'type'=>'system'];
                        write_log($my_id, 'ITEM', "방어구 {$armor['name']} 파괴됨");
                    } else {
                        sql_exec("UPDATE School_Inventory SET cur_dur=? WHERE id=?", [$new_dur, $armor['id']]);
                    }
                }
            }

            $logs[] = ['msg'=>$msg, 'type'=>($is_hit?'enemy':'player')];

            // [패배 처리] 페널티 적용
            if ($me['hp_cur'] <= 0) {
                $me['hp_cur'] = 0;
                
                // 페널티 로직
                $mob_info = sql_fetch("SELECT defeat_penalty FROM School_Monsters WHERE name=?", [$mob['name']]);
                $penalty = json_decode($mob_info['defeat_penalty'], true);
                $pen_msg = [];
                
                // 1. 포인트 차감
                if (!empty($penalty['point'])) {
                    sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$penalty['point'], $my_id]);
                    $pen_msg[] = "포인트 변동({$penalty['point']})";
                    write_log($my_id, 'BATTLE', "패배 페널티: 포인트 {$penalty['point']}");
                }

                // 2. 상태이상 (중복 시 단계 상승 로직)
                if (!empty($penalty['status'])) {
                    $sid = $penalty['status'];
                    $exist = sql_fetch("SELECT id, current_stage FROM School_Status_Active WHERE target_id=? AND status_id=?", [$my_id, $sid]);
                    $s_info = sql_fetch("SELECT name, max_stage FROM School_Status_Info WHERE status_id=?", [$sid]);

                    if ($exist) {
                        if ($exist['current_stage'] < $s_info['max_stage']) {
                            sql_exec("UPDATE School_Status_Active SET current_stage = current_stage + 1 WHERE id=?", [$exist['id']]);
                            $pen_msg[] = "상태이상 [{$s_info['name']}] 단계 상승";
                            write_log($my_id, 'BATTLE', "상태이상 {$s_info['name']} 단계 상승 ({$exist['current_stage']}->".($exist['current_stage']+1).")");
                        } else {
                            $pen_msg[] = "상태이상 [{$s_info['name']}] (이미 최대)";
                        }
                    } else {
                        sql_exec("INSERT INTO School_Status_Active (target_id, status_id, current_stage) VALUES (?, ?, 1)", [$my_id, $sid]);
                        $pen_msg[] = "상태이상 [{$s_info['name']}] 감염";
                        write_log($my_id, 'BATTLE', "상태이상 {$s_info['name']} 감염");
                    }
                }
                
                $final_msg = "눈앞이 캄캄해졌다... (패배)" . (empty($pen_msg) ? "" : "<br>📢 " . implode(", ", $pen_msg));
                $logs[] = ['msg'=>$final_msg, 'type'=>'system'];

                sql_exec("UPDATE School_Members SET hp_current=1 WHERE id=?", [$my_id]);
                sql_exec("UPDATE School_Battles SET status='ENDED', players_data=?, battle_log=? WHERE room_id=?", 
                    [json_encode($players), json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'lose']);
            } else {
                // 적 죽음 (반격으로) - 레벨업 로직 복사
                if ($mob['hp_cur'] <= 0) {
                     $gain_exp = $mob['give_exp'] ?? 10;
                     $gain_point = $mob['give_point'] ?? 50;
                     
                     $real_me = sql_fetch("SELECT * FROM School_Members WHERE id=?", [$my_id]);
                     $real_me['exp'] += $gain_exp;
                     $real_me['point'] += $gain_point;
                     
                     // 레벨업 루프
                     $levelup_count = 0;
                     while(true) {
                         $req_exp = $real_me['level'] * 10;
                         if ($real_me['exp'] >= $req_exp) {
                             $real_me['exp'] -= $req_exp;
                             $real_me['level']++;
                             $real_me['point'] += 200;
                             $real_me['stat_str'] += 2; $real_me['stat_dex'] += 2; 
                             $real_me['stat_con'] += 2; $real_me['stat_int'] += 2; $real_me['stat_luk'] += 2;
                             $real_me['hp_max'] = $real_me['stat_con'];
                             $levelup_count++;
                         } else break;
                     }
                     if ($levelup_count > 0) {
                         $logs[] = ['msg'=>"🎉 <b>레벨 업! (Lv.{$real_me['level']})</b>", 'type'=>'system'];
                         write_log($my_id, 'SYSTEM', "레벨 업! (Lv.{$real_me['level']})");
                     }

                     sql_exec("UPDATE School_Members SET level=?, exp=?, point=?, hp_max=?, stat_str=?, stat_dex=?, stat_con=?, stat_int=?, stat_luk=? WHERE id=?", 
                        [$real_me['level'], $real_me['exp'], $real_me['point'], $real_me['hp_max'], 
                         $real_me['stat_str'], $real_me['stat_dex'], $real_me['stat_con'], $real_me['stat_int'], $real_me['stat_luk'], $my_id]
                     );

                     $logs[] = ['msg'=>"<b>{$mob['name']}</b>(은)는 쓰러졌다! (반격 승리)<br>(Exp +{$gain_exp}, Point +{$gain_point})", 'type'=>'system'];
                     write_log($my_id, 'BATTLE', "{$mob['name']} 처치 (반격 승리, Exp +{$gain_exp}, Point +{$gain_point})");
                     
                     sql_exec("UPDATE School_Battles SET status='ENDED', mob_live_data=?, players_data=?, battle_log=? WHERE room_id=?", 
                        [json_encode($mobs), json_encode($players), json_encode($logs), $room['room_id']]
                     );
                     json_res(['status'=>'win']);
                } else {
                    sql_exec("UPDATE School_Battles SET turn_status='player', mob_live_data=?, players_data=?, battle_log=? WHERE room_id=?", 
                        [json_encode($mobs), json_encode($players), json_encode($logs), $room['room_id']]
                    );
                    sql_exec("UPDATE School_Members SET hp_current=? WHERE id=?", [$me['hp_cur'], $my_id]);
                    json_res(['status'=>'success']);
                }
            }
            break;

        case 'battle_run':
            $room = sql_fetch("SELECT * FROM School_Battles WHERE host_id=? AND status='FIGHTING' ORDER BY room_id DESC LIMIT 1", [$my_id]);
            $players = json_decode($room['players_data'], true);
            $my_dex = $players[0]['dex'];
            
            $success_chance = min(100, $my_dex * 3);
            if (rand(1, 100) <= $success_chance) {
                $logs = json_decode($room['battle_log'], true);
                $logs[] = ['msg'=>"💨 성공적으로 도망쳤다!", 'type'=>'system'];
                sql_exec("UPDATE School_Battles SET status='ENDED', battle_log=? WHERE room_id=?", [json_encode($logs), $room['room_id']]);
                write_log($my_id, 'BATTLE', "전투에서 도망침");
                json_res(['status'=>'success', 'msg'=>'도망 성공!']);
            } else {
                $logs = json_decode($room['battle_log'], true);
                $logs[] = ['msg'=>"💦 도망치는데 실패했다!", 'type'=>'system'];
                sql_exec("UPDATE School_Battles SET turn_status='enemy_ready', battle_log=? WHERE room_id=?", 
                    [json_encode($logs), $room['room_id']]
                );
                json_res(['status'=>'fail', 'msg'=>'도망 실패!']);
            }
            break;

        // =========================================================
        // [4] 인벤토리 (장착 제한 적용)
        // =========================================================
        case 'inventory_action':
            $inv_id = to_int($input['inv_id']);
            $action = $input['action']; 
            
            $item = sql_fetch("SELECT inv.*, info.type, info.name, info.effect_data FROM School_Inventory inv JOIN School_Item_Info info ON inv.item_id = info.item_id WHERE inv.id=? AND inv.owner_id=?", [$inv_id, $my_id]);
            if (!$item) throw new Exception("아이템 없음");

            if ($action === 'equip') {
                if (!in_array($item['type'], ['WEAPON', 'ARMOR', 'ETC'])) throw new Exception("장착불가");
                
                // 장착 제한 확인
                $equipped = sql_fetch_all("SELECT info.type FROM School_Inventory inv JOIN School_Item_Info info ON inv.item_id = info.item_id WHERE inv.owner_id=? AND inv.is_equipped=1", [$my_id]);
                $cnt = ['WEAPON'=>0, 'ARMOR'=>0, 'ETC'=>0];
                foreach($equipped as $eq) $cnt[$eq['type']]++;

                if ($item['type'] == 'WEAPON' && $cnt['WEAPON'] >= 1) {
                    sql_exec("UPDATE School_Inventory inv JOIN School_Item_Info info ON inv.item_id=info.item_id SET is_equipped=0 WHERE inv.owner_id=? AND info.type='WEAPON'", [$my_id]);
                }
                elseif ($item['type'] == 'ARMOR' && $cnt['ARMOR'] >= 1) {
                    sql_exec("UPDATE School_Inventory inv JOIN School_Item_Info info ON inv.item_id=info.item_id SET is_equipped=0 WHERE inv.owner_id=? AND info.type='ARMOR'", [$my_id]);
                }
                elseif ($item['type'] == 'ETC' && $cnt['ETC'] >= 5) {
                    throw new Exception("장신구(기타)는 최대 5개까지만 장착 가능합니다.");
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
                if ($item['type'] !== 'CONSUME') throw new Exception("사용불가");
                $eff = json_decode($item['effect_data'], true);
                $msg = [];
                if (isset($eff['hp_heal'])) {
                    $me = sql_fetch("SELECT hp_current, hp_max FROM School_Members WHERE id=?", [$my_id]);
                    $new_hp = min($me['hp_max'], $me['hp_current'] + $eff['hp_heal']);
                    sql_exec("UPDATE School_Members SET hp_current=? WHERE id=?", [$new_hp, $my_id]);
                    $msg[] = "체력 {$eff['hp_heal']} 회복.";
                }
                if ($item['count'] > 1) sql_exec("UPDATE School_Inventory SET count = count - 1 WHERE id=?", [$inv_id]);
                else sql_exec("DELETE FROM School_Inventory WHERE id=?", [$inv_id]);
                write_log($my_id, 'ITEM', "{$item['name']} 사용");
                json_res(['status'=>'success', 'msg'=>implode(" ", $msg)]);
            }
            break;

        // =========================================================
        // [5] 도박 시스템
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
                $gain = floor($amount * 1.9);
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
            $amount = to_int($input['amount']);
            if ($amount <= 0) throw new Exception("배팅 금액 확인");
            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            if ($me['point'] < $amount) throw new Exception("포인트 부족");
            
            sql_exec("UPDATE School_Members SET point = point - ? WHERE id=?", [$amount, $my_id]);
            $current_point = $me['point'] - $amount;

            $configs = sql_fetch_all("SELECT * FROM School_Gamble_Config");
            if (!$configs) throw new Exception("설정 없음");

            $picked = null;
            $rand = rand(1, 100);
            $cumulative = 0;
            foreach ($configs as $cfg) {
                $cumulative += $cfg['probability'];
                if ($rand <= $cumulative) { $picked = $cfg; break; }
            }

            $gain = 0;
            if ($picked) {
                $gain = floor($amount * $picked['ratio']);
                $current_point += $gain;
                sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$gain, $my_id]);
                write_log($my_id, 'GAMBLE', "룰렛 당첨: {$picked['name']} (+{$gain} P)");
            } else {
                $picked = ['name'=>'꽝', 'ratio'=>0];
                write_log($my_id, 'GAMBLE', "룰렛 꽝 (-{$amount} P)");
            }
            json_res(['status'=>'success', 'data'=>$picked, 'gain'=>$gain, 'current_point'=>$current_point]);
            break;

        case 'gamble_bj_start':
            $amount = to_int($input['amount']);
            if ($amount <= 0) throw new Exception("배팅 금액 확인");
            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            if ($me['point'] < $amount) throw new Exception("포인트 부족");
            
            sql_exec("UPDATE School_Members SET point = point - ? WHERE id=?", [$amount, $my_id]);
            
            $p_hand = [rand(1, 13), rand(1, 13)];
            $d_hand = [rand(1, 13), rand(1, 13)];
            $_SESSION['bj_game'] = ['bet' => $amount, 'p_hand' => $p_hand, 'd_hand' => $d_hand, 'status' => 'playing'];
            write_log($my_id, 'GAMBLE', "블랙잭 시작 (배팅: {$amount})");
            json_res(['status'=>'success', 'data'=>['player_hand'=>$p_hand, 'dealer_hand'=>$d_hand, 'player_score'=>calc_bj_score($p_hand), 'dealer_score'=>calc_bj_score($d_hand)], 'current_point'=>$me['point']-$amount]);
            break;

        case 'gamble_bj_action':
            if (!isset($_SESSION['bj_game']) || $_SESSION['bj_game']['status'] !== 'playing') throw new Exception("게임 없음");
            $game = &$_SESSION['bj_game'];
            $action = $input['action'];
            $is_end = false;
            $msg = "";
            
            if ($action === 'hit') {
                $game['p_hand'][] = rand(1, 13);
                if (calc_bj_score($game['p_hand']) > 21) { 
                    $is_end = true; $msg = "버스트! 패배"; 
                    write_log($my_id, 'GAMBLE', "블랙잭 패배 (버스트)"); 
                }
            } elseif ($action === 'stand') {
                while (calc_bj_score($game['d_hand']) < 17) { $game['d_hand'][] = rand(1, 13); }
                $is_end = true;
                $p_score = calc_bj_score($game['p_hand']);
                $d_score = calc_bj_score($game['d_hand']);
                $bet = $game['bet'];
                $win = 0;
                
                if ($d_score > 21) { $msg = "딜러 버스트! 승리!"; $win = $bet*2; write_log($my_id, 'GAMBLE', "블랙잭 승리 (+{$win})"); }
                elseif ($p_score > $d_score) { $msg = "승리!"; $win = $bet*2; write_log($my_id, 'GAMBLE', "블랙잭 승리 (+{$win})"); }
                elseif ($p_score == $d_score) { $msg = "무승부"; $win = $bet; write_log($my_id, 'GAMBLE', "블랙잭 무승부"); }
                else { $msg = "패배..."; write_log($my_id, 'GAMBLE', "블랙잭 패배"); }
                
                if ($win > 0) sql_exec("UPDATE School_Members SET point = point + ? WHERE id=?", [$win, $my_id]);
            }

            $me = sql_fetch("SELECT point FROM School_Members WHERE id=?", [$my_id]);
            $data = [
                'player_hand' => $game['p_hand'], 'dealer_hand' => $game['d_hand'],
                'player_score' => calc_bj_score($game['p_hand']), 'dealer_score' => calc_bj_score($game['d_hand'])
            ];
            
            if ($is_end) {
                unset($_SESSION['bj_game']);
                json_res(['status'=>'end', 'data'=>$data, 'msg'=>$msg, 'current_point'=>$me['point']]);
            } else {
                json_res(['status'=>'playing', 'data'=>$data]);
            }
            break;

        default: throw new Exception("알 수 없는 요청");
    }

} catch (Exception $e) {
    json_res(['status'=>'error', 'message'=>$e->getMessage()]);
}

// ---------------------------------------------------------
// [헬퍼 함수]
// ---------------------------------------------------------
function calc_battle_stats($base_stats, $add_atk=0, $add_def=0) {
    $str = $base_stats['stat_str'] ?? 0;
    $dex = $base_stats['stat_dex'] ?? 0;
    $con = $base_stats['stat_con'] ?? 0;
    $int = $base_stats['stat_int'] ?? 0;
    $luk = $base_stats['stat_luk'] ?? 0;

    $atk = round(($str*0.4) + ($dex*0.3) + ($con*0.1) + ($luk*0.1) + ($int*0.1)) + $add_atk;
    $def = round(($con*0.5) + ($dex*0.3) + ($int*0.1) + ($luk*0.1)) + $add_def;
    $hp  = $con; 
    $spd = $dex;

    return ['atk' => $atk, 'def' => $def, 'hp_max' => $hp, 'speed' => $spd, 'str' => $str, 'dex' => $dex, 'con' => $con, 'int' => $int, 'luk' => $luk];
}

function calc_bj_score($hand) {
    $score = 0;
    foreach ($hand as $card) {
        if ($card >= 11 && $card <= 13) $score += 10;
        else if ($card == 1) $score += 1;
        else $score += $card;
    }
    return $score;
}
?>