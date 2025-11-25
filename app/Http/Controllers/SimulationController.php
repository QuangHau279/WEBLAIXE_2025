<?php

namespace App\Http\Controllers;

use App\Models\tblMoPhong;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    /**
     * Hiển thị trang mô phỏng lý thuyết lái xe
     */
    public function index(Request $request)
    {
        $videoId = $request->query('v', null);
        $mode = $request->query('mode', 'practice'); // 'practice' hoặc 'test'
        
        // Lấy danh sách video mô phỏng
        if ($mode === 'test') {
            // Thi thử: Mỗi lần F5 (không có video ID) = tạo bài thi mới
            $sessionKey = 'simulation_test_videos';
            
            if ($videoId) {
                // Đang làm bài (có video ID): Giữ session và lấy danh sách từ session
                if ($request->session()->has($sessionKey)) {
                    $videoIds = $request->session()->get($sessionKey);
                    $videos = tblMoPhong::where('active', true)
                        ->whereIn('id', $videoIds)
                        ->get()
                        ->sortBy(function($video) use ($videoIds) {
                            return array_search($video->id, $videoIds);
                        })
                        ->values();
                } else {
                    // Session bị mất, tạo lại
                    $videos = collect([]);
                }
            } else {
                // F5 hoặc vào trang mới (không có video ID): Xóa session cũ và tạo bài thi mới
                $request->session()->forget($sessionKey);
                $videos = collect([]); // Trả về rỗng để hiển thị màn hình bắt đầu
            }
        } else {
            // Ôn tập: Lấy tất cả video theo thứ tự
            $videos = tblMoPhong::where('active', true)
                ->orderBy('stt')
                ->get();
        }

        // Chọn video chính: theo ?v=... hoặc video đầu tiên
        $mainVideo = null;
        if ($videoId) {
            // Kiểm tra xem video có trong danh sách hiện tại không
            $mainVideo = $videos->firstWhere('id', $videoId);
        } elseif ($mode === 'test') {
            // Trong test mode, KHÔNG tự động chọn video đầu tiên khi reload
            // Chỉ hiển thị màn hình bắt đầu nếu không có video ID
            $mainVideo = null;
        } elseif ($mode === 'practice' && $videos->count() > 0) {
            // Trong practice mode, chọn video đầu tiên
            $mainVideo = $videos->first();
        }

        // Lấy danh sách video khác
        $otherVideos = $videos->reject(function ($video) use ($mainVideo) {
            return $mainVideo && $video->id === $mainVideo->id;
        });

        return view('pages.simulation', [
            'mainVideo' => $mainVideo,
            'otherVideos' => $otherVideos,
            'allVideos' => $videos,
            'mode' => $mode,
        ]);
    }

    /**
     * Bắt đầu thi thử: Tạo danh sách video ngẫu nhiên và lưu vào session
     */
    public function startTest(Request $request)
    {
        // Lấy ngẫu nhiên 10 video
        $videos = tblMoPhong::where('active', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();
        
        // Lưu danh sách ID vào session để giữ nguyên trình tự
        $videoIds = $videos->pluck('id')->toArray();
        $request->session()->put('simulation_test_videos', $videoIds);
        
        // Chuyển đến câu hỏi đầu tiên
        if ($videos->count() > 0) {
            $firstVideo = $videos->first();
            return redirect()->route('simulation', ['mode' => 'test', 'v' => $firstVideo->id]);
        }
        
        return redirect()->route('simulation', ['mode' => 'test']);
    }

    /**
     * Xóa session thi thử (bắt đầu lại)
     */
    public function resetTest(Request $request)
    {
        $request->session()->forget('simulation_test_videos');
        // Redirect về trang test mode (sẽ hiển thị màn hình bắt đầu)
        return redirect()->route('simulation', ['mode' => 'test'])->with('reset_success', true);
    }

    /**
     * API: Lưu điểm trừ của video
     */
    public function savePoints(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:tblmophong,id',
            'diem5' => 'nullable|numeric|min:0',
            'diem4' => 'nullable|numeric|min:0',
            'diem3' => 'nullable|numeric|min:0',
            'diem2' => 'nullable|numeric|min:0',
            'diem1' => 'nullable|numeric|min:0',
            'diem1end' => 'nullable|numeric|min:0',
        ]);

        $video = tblMoPhong::findOrFail($request->video_id);
        
        $video->update([
            'diem5' => $request->diem5 ?? 0.0,
            'diem4' => $request->diem4 ?? 0.0,
            'diem3' => $request->diem3 ?? 0.0,
            'diem2' => $request->diem2 ?? 0.0,
            'diem1' => $request->diem1 ?? 0.0,
            'diem1end' => $request->diem1end ?? 0.0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu điểm trừ thành công',
            'data' => $video,
        ]);
    }

    /**
     * API: Lấy thông tin video
     */
    public function getVideo($id)
    {
        $video = tblMoPhong::where('id', $id)
            ->where('active', true)
            ->firstOrFail();

        return response()->json($video);
    }

    /**
     * Trang cấu hình điểm trừ cho video (Admin)
     */
    public function configPoints(Request $request)
    {
        $videoId = $request->query('v', null);
        
        // Lấy danh sách tất cả video
        $videos = tblMoPhong::where('active', true)
            ->orderBy('stt')
            ->get();

        // Chọn video cần cấu hình
        $mainVideo = null;
        if ($videoId) {
            $mainVideo = tblMoPhong::where('id', $videoId)
                ->where('active', true)
                ->first();
        }
        
        if (!$mainVideo && $videos->count() > 0) {
            $mainVideo = $videos->first();
        }

        return view('pages.simulation-config', [
            'mainVideo' => $mainVideo,
            'allVideos' => $videos,
        ]);
    }
}

