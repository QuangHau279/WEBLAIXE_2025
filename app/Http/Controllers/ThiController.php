<?php

namespace App\Http\Controllers;

use App\Models\tblLoaiBangLai;
use App\Models\LoaiBangLai;
use App\Models\tblCauHoi;
use App\Models\CauHoiBangLai;
use App\Models\tblCauHoiBangLai;
use App\Models\tblBoCauHoi;
use App\Models\tblBoCauHoi_CauHoi;
use App\Models\tblHinhAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ThiController extends Controller
{
    /**
     * GET /api/thi/presets
     */
    public function presets()
    {
        $bangs = tblLoaiBangLai::where('active', 1)
            ->orderBy('id')
            ->get(['id', 'ten', 'socauhoi', 'mincauhoidung', 'active']);

        $presets = $bangs->mapWithKeys(function ($b) {
            $soCau    = (int) ($b->socauhoi ?: 25);
            // $thoiGian = max(10, (int) ceil($soCau * 1.2));
            $thoiGian = (int) ($b->thoigian ?: max(10, (int) ceil($soCau * 1.2)));
            
            // Kiểm tra số bộ đề có sẵn cho hạng này
            // $deAvailable = CauHoiBangLai::where('BangLaiId', $b->id)
            //     ->distinct()
            //     ->pluck('BoDe')
            //     ->filter(function($de) {
            //         return is_numeric($de) && $de >= 1 && $de <= 5;
            //     })
            //     ->sort()
            //     ->values()
            //     ->toArray();

            $deAvailable = tblBoCauHoi::where('BangLaiId', $b->id)->where('active', 1)
                ->orderBy('stt')
                ->pluck('stt')
                ->filter(function($de) {
                    return is_numeric($de) && $de >= 1 && $de <= 5;
                })
                ->unique()
                ->values()
                ->toArray();
            
            // Luôn có đề ngẫu nhiên nếu có ít nhất 1 câu hỏi
            $totalQuestions = tblCauHoiBangLai::where('BangLaiId', $b->id)->count();
            $deOptions = $deAvailable; // [1, 2, 3, ...] nếu có
            if ($totalQuestions > 0) {
                $deOptions[] = 'RANDOM';
            }
            
            return [
                strtoupper(trim($b->id)) => [
                    'ten'           => $b->ten,
                    'so_cau'       => $soCau,
                    'thoi_gian'    => $thoiGian,
                    'dau_tu'       => (int) ($b->mincauhoidung ?: floor($soCau * 0.8)),
                    'min_cau_liet' => 0,
                    'de_options'   => $deOptions, // Chỉ trả về các bộ đề có sẵn
                    'total_questions' => $totalQuestions, // Tổng số câu hỏi của hạng
                ],
            ];
        });

        return response()->json([
            'presets'   => $presets,
            'loai_bang' => $bangs,
        ]);
    }

    /**
     * POST /api/thi/tao-de  body: { "hang": "B1" }
     * Trả về câu hỏi + đáp án (đã trộn nếu muốn) nhưng KHÔNG lộ đáp án đúng.
     */
    public function create(Request $request)
    {
        $request->validate([
            'hang' => 'required|string',
            'de'   => 'nullable',
        ]);
        $hang = strtoupper(trim($request->input('hang')));
        $de   = $request->input('de');

        // Tìm hạng chính xác trước
        $bang = tblLoaiBangLai::where('id', $hang)
            ->where('active', 1)
            ->first();

        // Nếu không tìm thấy, thử tìm gần đúng
        // if (!$bang) {
        //     $bang = tblLoaiBangLai::where('ten', 'like', "%{$hang}%")
        //         ->where('active', 1)
        //         ->first();
        // }

        if (!$bang) {
            return response()->json([
                'message' => "Hạng không hợp lệ. Vui lòng chọn lại hạng thi."
            ], 422);
        }
        
        // Lưu hạng thực tế được sử dụng
        // $hang = strtoupper(trim($bang->ten));

        $soCau    = (int) ($bang->socauhoi ?: 25);
        //$thoiGian = max(10, (int) ceil($soCau * 1.2));
        $thoiGian = (int) ($bang->thoigian ?: max(10, (int) ceil($soCau * 1.2)));
        $dauTu    = (int) ($bang->mincauhoidung ?: floor($soCau * 0.8));

        // Nếu chọn một bộ đề cụ thể (1..5) thì lọc theo cột BoDe
        if (is_numeric($de)) {
            // $idsTheoBang = CauHoiBangLai::where('BangLaiId', $bang->id)
            //     ->where('BoDe', (int)$de)
            //     ->pluck('CauHoiId');

            $idsTheoBang = tblBoCauHoi_CauHoi::where('BoCauHoiId', $de)->pluck('CauHoiId');
        } else {
            $idsTheoBang = tblCauHoiBangLai::where('BangLaiId', $bang->id)->pluck('CauHoiId');
        }

        $query = tblCauHoi::where('active', 1);

        if ($idsTheoBang->count() > 0) {
            $query->whereIn('id', $idsTheoBang);
        }

        $all = $query->get(['id', 'stt', 'noidung', 'cauliet', 'chuong']);

        if ($all->count() === 0) {
            $message = is_numeric($de) 
                ? "Bằng {$bang->ten} chưa có bộ đề {$de}. Vui lòng chọn bộ đề khác hoặc đề ngẫu nhiên."
                : "Bằng {$bang->ten} chưa có câu hỏi. Vui lòng kiểm tra lại dữ liệu.";
            return response()->json(['message' => $message], 404);
        }

        // Kiểm tra nếu là hạng xe máy A1 hoặc A
        $isXeMay = in_array(strtoupper($hang), ['A1', 'A']);

        // Tạo đề:
        // - Nếu de là RANDOM (hoặc null) ⇒ chọn ngẫu nhiên
        // - Nếu de là 1..5 ⇒ theo bộ cố định
        if (!is_numeric($de)) {
            if ($isXeMay) {
                // ===== ĐỀ NGẪU NHIÊN XE MÁY A1/A =====
                // Phân bổ theo chương: 8-1-1-1-8-6 = 25 câu
                // Chương 1: 8 câu quy định chung
                // Câu liệt: 1 câu (lấy từ câu có cauliet = 1)
                // Chương 2: 1 câu văn hóa giao thông
                // Chương 3 hoặc 4: 1 câu kỹ thuật/cấu tạo
                // Chương 5: 8 câu báo hiệu đường bộ
                // Chương 6: 6 câu sa hình và xử lý tình huống
                
                $chon = collect();
                
                // 1. Lấy 8 câu ngẫu nhiên từ chương 1 (không lấy câu liệt)
                $ch1 = $all->where('chuong', 1)->where('cauliet', '!=', 1)->shuffle()->take(8);
                foreach ($ch1 as $q) {
                    $q->order_priority = 1;
                }
                $chon = $chon->concat($ch1);
                
                // 2. Lấy 1 câu liệt (cauliet = 1) - đặt sau chương 1
                $liet = $all->where('cauliet', 1);
                if ($liet->count() > 0) {
                    $oneLiet = $liet->random(1);
                    foreach ($oneLiet as $q) {
                        $q->order_priority = 1.5; // Sau chương 1, trước chương 2
                    }
                    $chon = $chon->concat($oneLiet);
                }
                
                // 3. Lấy 1 câu từ chương 2
                $ch2 = $all->where('chuong', 2)->where('cauliet', '!=', 1)->shuffle()->take(1);
                foreach ($ch2 as $q) {
                    $q->order_priority = 2;
                }
                $chon = $chon->concat($ch2);
                
                // 4. Lấy 1 câu từ chương 3 hoặc 4
                $ch34 = $all->whereIn('chuong', [3, 4])->where('cauliet', '!=', 1)->shuffle()->take(1);
                foreach ($ch34 as $q) {
                    $q->order_priority = 3;
                }
                $chon = $chon->concat($ch34);
                
                // 5. Lấy 8 câu từ chương 5
                $ch5 = $all->where('chuong', 5)->where('cauliet', '!=', 1)->shuffle()->take(8);
                foreach ($ch5 as $q) {
                    $q->order_priority = 5;
                }
                $chon = $chon->concat($ch5);
                
                // 6. Lấy 6 câu từ chương 6
                $ch6 = $all->where('chuong', 6)->where('cauliet', '!=', 1)->shuffle()->take(6);
                foreach ($ch6 as $q) {
                    $q->order_priority = 6;
                }
                $chon = $chon->concat($ch6);
                
                // Sắp xếp theo order_priority (1 -> 1.5 -> 2 -> 3 -> 5 -> 6)
                $chon = $chon->sortBy('order_priority')->values();
                
            } else {
                // ===== ĐỀ NGẪU NHIÊN XE Ô TÔ B/B1/B2/C1 =====
                $hangUpper = strtoupper($hang);
                $isB1 = in_array($hangUpper, ['B1']); // 30 câu
                $isB2orC1 = in_array($hangUpper, ['B', 'B2', 'C1']); // 35 câu

                if ($isB1) {
                    // ===== HẠNG B1 (30 câu): 9-1-1-1-1-9-8 =====
                    $chon = collect();
                    
                    // Chương 1: 9 câu
                    $ch1 = $all->where('chuong', 1)->where('cauliet', '!=', 1)->shuffle()->take(9);
                    foreach ($ch1 as $q) { $q->order_priority = 1; }
                    $chon = $chon->concat($ch1);
                    
                    // Câu điểm liệt: 1 câu
                    $liet = $all->where('cauliet', 1);
                    if ($liet->count() > 0) {
                        $oneLiet = $liet->random(1);
                        foreach ($oneLiet as $q) { $q->order_priority = 1.5; }
                        $chon = $chon->concat($oneLiet);
                    }
                    
                    // Chương 2: 1 câu
                    $ch2 = $all->where('chuong', 2)->where('cauliet', '!=', 1)->shuffle()->take(1);
                    foreach ($ch2 as $q) { $q->order_priority = 2; }
                    $chon = $chon->concat($ch2);
                    
                    // Chương 3: 1 câu
                    $ch3 = $all->where('chuong', 3)->where('cauliet', '!=', 1)->shuffle()->take(1);
                    foreach ($ch3 as $q) { $q->order_priority = 3; }
                    $chon = $chon->concat($ch3);
                    
                    // Chương 4: 1 câu
                    $ch4 = $all->where('chuong', 4)->where('cauliet', '!=', 1)->shuffle()->take(1);
                    foreach ($ch4 as $q) { $q->order_priority = 4; }
                    $chon = $chon->concat($ch4);
                    
                    // Chương 5: 9 câu
                    $ch5 = $all->where('chuong', 5)->where('cauliet', '!=', 1)->shuffle()->take(9);
                    foreach ($ch5 as $q) { $q->order_priority = 5; }
                    $chon = $chon->concat($ch5);
                    
                    // Chương 6: 8 câu
                    $ch6 = $all->where('chuong', 6)->where('cauliet', '!=', 1)->shuffle()->take(8);
                    foreach ($ch6 as $q) { $q->order_priority = 6; }
                    $chon = $chon->concat($ch6);
                    
                    // Sắp xếp theo chương
                    $chon = $chon->sortBy('order_priority')->values();
                    
                } elseif ($isB2orC1) {
                    // ===== HẠNG B2/C1 (35 câu): 10-1-1-2-1-10-10 =====
                    $chon = collect();
                    
                    // Chương 1: 10 câu
                    $ch1 = $all->where('chuong', 1)->where('cauliet', '!=', 1)->shuffle()->take(10);
                    foreach ($ch1 as $q) { $q->order_priority = 1; }
                    $chon = $chon->concat($ch1);
                    
                    // Câu điểm liệt: 1 câu
                    $liet = $all->where('cauliet', 1);
                    if ($liet->count() > 0) {
                        $oneLiet = $liet->random(1);
                        foreach ($oneLiet as $q) { $q->order_priority = 1.5; }
                        $chon = $chon->concat($oneLiet);
                    }
                    
                    // Chương 2: 1 câu
                    $ch2 = $all->where('chuong', 2)->where('cauliet', '!=', 1)->shuffle()->take(1);
                    foreach ($ch2 as $q) { $q->order_priority = 2; }
                    $chon = $chon->concat($ch2);
                    
                    // Chương 3: 2 câu
                    $ch3 = $all->where('chuong', 3)->where('cauliet', '!=', 1)->shuffle()->take(2);
                    foreach ($ch3 as $q) { $q->order_priority = 3; }
                    $chon = $chon->concat($ch3);
                    
                    // Chương 4: 1 câu
                    $ch4 = $all->where('chuong', 4)->where('cauliet', '!=', 1)->shuffle()->take(1);
                    foreach ($ch4 as $q) { $q->order_priority = 4; }
                    $chon = $chon->concat($ch4);
                    
                    // Chương 5: 10 câu
                    $ch5 = $all->where('chuong', 5)->where('cauliet', '!=', 1)->shuffle()->take(10);
                    foreach ($ch5 as $q) { $q->order_priority = 5; }
                    $chon = $chon->concat($ch5);
                    
                    // Chương 6: 10 câu
                    $ch6 = $all->where('chuong', 6)->where('cauliet', '!=', 1)->shuffle()->take(10);
                    foreach ($ch6 as $q) { $q->order_priority = 6; }
                    $chon = $chon->concat($ch6);
                    
                    // Sắp xếp theo chương
                    $chon = $chon->sortBy('order_priority')->values();
                    
                } else {
                    // Các hạng khác: logic cũ
                    $liet = $all->where('cauliet', 1);
                    if ($liet->count() > 0) {
                        $oneLiet = $liet->random(1);
                        $remain  = $all->whereNotIn('id', $oneLiet->pluck('id'))
                                       ->shuffle()->take(max(0, $soCau - 1));
                        $chon = $oneLiet->concat($remain)->shuffle();
                    } else {
                        $chon = $all->shuffle()->take($soCau);
                    }
                }
            }
        } else {
            // Bộ đề cố định: lấy ngẫu nhiên từ bộ đề đó, nhưng không quá số câu có
            $chon = $all->shuffle()->take(min($soCau, $all->count()));
        }
        
        // Nếu không đủ câu, điều chỉnh số câu xuống số lượng thực tế có
        if ($chon->count() < $soCau) {
            // Nếu chọn bộ đề cố định mà không đủ câu
            if (is_numeric($de)) {
                $soCau = $chon->count();
                //$thoiGian = max(10, (int) ceil($soCau * 1.2));
                $thoiGian = (int) ($bang->thoigian ?: max(10, (int) ceil($soCau * 1.2)));
                $dauTu = (int) floor($soCau * 0.8);
            } else {
                // Nếu đề ngẫu nhiên mà không đủ → báo lỗi rõ ràng
                return response()->json([
                    'message' => "Không đủ câu hỏi để tạo đề. Hạng {$hang} chỉ có {$all->count()} câu hỏi, yêu cầu {$soCau} câu."
                ], 500);
            }
        }

        $payloadQuestions = [];
        $answerKey        = [];

        foreach ($chon as $q) {
            $answers = $q->dapAns()->get(['id','stt','noidung','caudung'])
                ->map(fn($a) => ['id'=>$a->id,'stt'=>$a->stt,'text'=>$a->noidung])
                ->values()->all();

            // Nếu muốn trộn đáp án hiển thị, bỏ comment dòng sau:
            // shuffle($answers);

            // $imgs = $q->hinhAnhs()->get(['id','ten'])
            //     ->map(fn($h) => ['id'=>$h->id, 'ten'=>$h->ten, 'url'=>$h->url])
            //     ->values()->all();

            $imgs = tblHinhAnh::where('CauHoiId', $q->id)
                ->where('active', 1)
                ->orderBy('stt')
                ->get(['id', 'MediaId'])
                ->map(function($h) {
                    return [
                        'id'  => $h->id,
                        'ten' => $h->Media->name,
                        'url' => asset('images/cauhoi/' . $h->Media->name),
                    ];
                })
                ->values()
                ->all();

            $correctIds = $q->dapAns()->where('caudung', 1)->pluck('id')->all();
            $answerKey[$q->id] = $correctIds;

            $payloadQuestions[] = [
                'id'      => $q->id,
                'stt'     => $q->stt,
                'text'    => $q->noidung,
                'is_liet' => (int)$q->cauliet === 1,
                'answers' => $answers,
                'images'  => $imgs,
            ];
        }

        $examId    = (string) Str::uuid();
        $expiresAt = Carbon::now()->addMinutes($thoiGian);

        session([
            "exams.$examId" => [
                'hang'         => $hang,
                'preset'       => [
                    'so_cau'    => $soCau,
                    'thoi_gian' => $thoiGian,
                    'dau_tu'    => $dauTu,
                ],
                'question_ids' => collect($payloadQuestions)->pluck('id')->all(),
                'answer_key'   => $answerKey,
                'liet_ids'     => $chon->where('cauliet', 1)->pluck('id')->all(),
                'expires_at'   => $expiresAt->toIso8601String(),
            ],
        ]);

        return response()->json([
            'exam_id'        => $examId,
            'hang'           => $hang,
            'expires_at'     => $expiresAt->toIso8601String(),
            'thoi_gian_phut' => $thoiGian,
            'so_cau'         => $soCau,
            'questions'      => $payloadQuestions,
        ]);
    }

    /**
     * POST /api/thi/nop-bai
     * body: { "exam_id":"...", "answers":[{"question_id":1,"answer_id":2}, ...] }
     */
    public function submit(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|string',
            'answers' => 'required|array',
        ]);

        $examId = $request->input('exam_id');
        $state  = session("exams.$examId");

        if (!$state) {
            return response()->json(['message' => 'Phiên thi không tồn tại hoặc đã hết hạn'], 410);
        }

        $expired = false;
        if (!empty($state['expires_at'])) {
            $expired = Carbon::parse($state['expires_at'])->isPast();
        }

        $answers   = collect($request->input('answers'));
        $answerKey = $state['answer_key'] ?? []; // {qid => [aid,...]}
        $lietIds   = $state['liet_ids'] ?? [];
        $preset    = $state['preset'] ?? ['dau_tu' => 0];

        // Map câu -> đáp án người dùng chọn (1 đáp án/câu)
        $mapUser = []; // {qid => aid}
        foreach ($answers as $ans) {
            $qid = (int) ($ans['question_id'] ?? 0);
            $aid = (int) ($ans['answer_id'] ?? 0);
            if ($qid > 0 && $aid > 0) {
                $mapUser[$qid] = $aid;
            }
        }

        $correctCount = 0;
        $wrong        = [];
        $lietWrong    = false;

        foreach ($answerKey as $qid => $correctIds) {
            $userAid   = $mapUser[$qid] ?? null;
            $isCorrect = $userAid && in_array($userAid, $correctIds, true);

            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrong[] = (int) $qid;
                if (in_array($qid, $lietIds, true)) {
                    $lietWrong = true; // sai câu liệt ⇒ rớt
                }
            }
        }

        $total  = count($answerKey);
        $passed = ($correctCount >= (int) ($preset['dau_tu'] ?? 0)) && !$lietWrong;

        // Xoá session sau khi chấm (tuỳ nhu cầu có thể giữ lại để xem tiếp)
        session()->forget("exams.$examId");

        return response()->json([
            'passed'   => $passed,
            'reason'   => $passed ? null : ($lietWrong ? 'Sai câu liệt' : 'Không đủ số câu đúng tối thiểu'),
            'total'    => $total,
            'correct'  => $correctCount,
            'required' => (int) ($preset['dau_tu'] ?? 0),
            'wrong_question_ids' => $wrong,
            'liet_wrong' => $lietWrong,
            'expired'    => $expired,

            // === Thêm cho bảng xem lại ===
            'correct_map' => $answerKey, // map đáp án đúng
            'user_map'    => $mapUser,   // map đáp án người dùng đã chọn
        ]);
    }
}
