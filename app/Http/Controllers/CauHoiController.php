<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\tblCauHoi;
use App\Models\tblCauTraLoi;
use App\Models\tblHinhAnh;
use App\Models\tblMedia;

class CauHoiController extends Controller
{

    // /api/grid -> trả danh sách số thứ tự, chương và câu liệt
    public function grid()
    {
        return tblCauHoi::orderBy('stt')
            ->select('stt', 'chuong', 'cauliet')
            ->get()
            ->map(function ($item) {
                return [
                    'stt' => (int)$item->stt,
                    'chuong' => (int)($item->chuong ?? 1),
                    'cauliet' => (int)($item->cauliet ?? 0)
                ];
            })
            ->toArray();
    }

    // /api/xe-may/grid -> trả danh sách stt_250, chuong và cauliet của 250 câu xe máy
    public function gridXeMay()
    {
        $query = tblCauHoi::query();
        $query->where('in_250', 1);
        $query->whereNotNull('stt_250');

        $results = $query->orderBy('stt_250')
                ->select('stt_250', 'chuong', 'cauliet')
                ->get()
                ->filter(function ($item) {
                    return $item->stt_250 !== null && $item->stt_250 !== '';
                })
                ->values()
                ->map(function ($item) {
                    return [
                        'stt' => (int)$item->stt_250,
                        'chuong' => (int)($item->chuong ?? 1),
                        'cauliet' => (int)($item->cauliet ?? 0)
                    ];
                })
                ->toArray();
        return $results;
    }

    // /api/cauhoi/{stt} -> 1 câu + đáp án + ảnh
    public function byStt($stt)
    {
        $q = tblCauHoi::where('stt', $stt)
            ->select('id', DB::raw('stt' . ' as stt'), 'noidung')
            ->first();

        if (!$q) return response()->json(['message' => 'Not found'], 404);

        // answers
        $answers = collect();

        $ansQuery = tblCauTraLoi::where('CauHoiId', $q->id)->select('id', 'noidung', 'caudung');
        $ansQuery->addSelect('stt')->orderBy('stt');

        $answers = $ansQuery->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'stt' => $a->stt ?? null,
                'noidung' => $a->noidung,
                'caudung' => (bool)$a->caudung,
            ];
        });

        // images
        $images = collect();
        $tblI = (new tblHinhAnh())->getTable();
        if (Schema::hasTable($tblI)) {
            $imgQuery = tblHinhAnh::where('CauHoiId', $q->id);
            $imgQuery->where('active', 1);
            $imgQuery->orderBy('stt');

            $images = $imgQuery->get()->map(
                function($item){
                    $file = $item->Media->name;
                    $file = ltrim((string)$file, '/\\');
                return [
                    'url' => asset('images/cauhoi/' . $file),
                    'alt' => $file,
                ];
                }
            );
        }

        return response()->json([
            'id' => $q->id,
            'stt' => $q->stt,
            'noidung' => $q->noidung,
            'cau_tra_lois' => $answers ?? [],
            'images' => $images ?? [],
        ]);
    }

    public function search(Request $req)
    {
        $q = trim((string)$req->query('q', ''));
        if ($q === '') return response()->json(['items' => []]);

        $tbl = (new tblCauHoi())->getTable();

        if (ctype_digit($q)) {
            $stt = (int)$q;
            $row = tblCauHoi::select('id', 'stt', 'noidung')->where('stt', $stt)->first();
            return response()->json([
                'items' => $row ? [[
                    'id' => $row->id,
                    'stt' => $row->stt,
                    'snippet' => \Illuminate\Support\Str::limit(strip_tags($row->noidung), 120),
                ]] : []
            ]);
        }

        $items = tblCauHoi::select('id', 'stt', 'noidung')
            ->where('noidung', 'like', '%' . $q . '%')
            ->orderBy('stt')
            ->limit(10)
            ->get()
            ->map(function ($r) use ($q) {
                $plain = strip_tags($r->noidung);
                $pos = mb_stripos($plain, $q);
                if ($pos === false) {
                    $snippet = \Illuminate\Support\Str::limit($plain, 120);
                } else {
                    $start = max(0, $pos - 25);
                    $snippet = mb_substr($plain, $start, 120);
                    if ($start > 0) $snippet = '…' . $snippet;
                }
                return [
                    'id' => $r->id,
                    'stt' => $r->stt,
                    'snippet' => $snippet,
                ];
            })->values();

        return response()->json(['items' => $items]);
    }

    // /api/xe-may/search -> tìm kiếm câu hỏi xe máy theo số câu hoặc từ khóa
    public function searchXeMay(Request $req)
    {
        $q = trim((string)$req->query('q', ''));
        if ($q === '') return response()->json(['items' => []]);

        $tbl = (new tblCauHoi())->getTable();
        $hasIn250 = Schema::hasColumn($tbl, 'in_250');
        $hasStt250 = Schema::hasColumn($tbl, 'stt_250');

        $baseQuery = tblCauHoi::query();

        if ($hasIn250) {
            $baseQuery->where('in_250', 1);
        } elseif ($hasStt250) {
            $baseQuery->whereNotNull('stt_250');
        }

        if (ctype_digit($q)) {
            $stt250 = (int)$q;

            $query = clone $baseQuery;
            if ($hasStt250) {
                $query->where('stt_250', $stt250);
            } else {
                $qSttCol = Schema::hasColumn($tbl, 'stt') ? 'stt' : 'id';
                $query->where($qSttCol, $stt250);
            }

            $row = $query->select('id', 'noidung', $hasStt250 ? 'stt_250' : DB::raw('NULL as stt_250'))->first();

            if ($row) {
                $displayStt = ($hasStt250 && isset($row->stt_250) && $row->stt_250 !== null) 
                    ? $row->stt_250 
                    : ($row->stt ?? $row->id);

                return response()->json([
                    'items' => [[
                        'id'      => $row->id,
                        'stt'     => $displayStt,
                        'stt_250' => $hasStt250 ? ($row->stt_250 ?? null) : null,
                        'snippet' => \Illuminate\Support\Str::limit(strip_tags($row->noidung), 120),
                    ]]
                ]);
            }

            return response()->json(['items' => []]);
        }

        $query = clone $baseQuery;
        $query->where('noidung', 'like', '%' . $q . '%');

        $selectCols = ['id', 'noidung'];
        $qSttCol = Schema::hasColumn($tbl, 'stt') ? 'stt' : 'id';
        $selectCols[] = $qSttCol;
        if ($hasStt250) $selectCols[] = 'stt_250';

        $items = $query->select($selectCols)
            ->orderBy($hasStt250 ? 'stt_250' : $qSttCol)
            ->limit(20)
            ->get()
            ->map(function ($r) use ($q, $hasStt250, $qSttCol) {
                $plain = strip_tags($r->noidung);
                $pos = mb_stripos($plain, $q);
                if ($pos === false) $snippet = \Illuminate\Support\Str::limit($plain, 120);
                else {
                    $start = max(0, $pos - 25);
                    $snippet = mb_substr($plain, $start, 120);
                    if ($start > 0) $snippet = '…' . $snippet;
                }

                $sttValue = $r->{$qSttCol} ?? null;
                $displayStt = ($hasStt250 && isset($r->stt_250) && $r->stt_250 !== null) ? $r->stt_250 : ($sttValue ?? $r->id);

                return [
                    'id' => $r->id,
                    'stt' => $displayStt,
                    'stt_250' => $hasStt250 ? ($r->stt_250 ?? null) : null,
                    'snippet' => $snippet,
                ];
            })->values();

        return response()->json(['items' => $items]);
    }

    // /api/xe-may/cau-hoi/{stt250} -> 1 câu xe máy theo stt_250 + đáp án + ảnh
    public function bySttXeMay($stt250)
    {
        $query = tblCauHoi::query();
        $query->where('stt_250', $stt250)->where('in_250', 1);

        $selectCols = ['id', 'noidung', 'stt'];
        $selectCols[] = 'stt_250';

        $q = $query->select($selectCols)->first();
        if (!$q) return response()->json(['message' => 'Not found'], 404);

        $displayStt = (isset($q->stt_250) && $q->stt_250 !== null) ? $q->stt_250 : (isset($q->stt) ? $q->stt : $q->id);

        // answers
        $answers = collect();

        $ansQuery = tblCauTraLoi::where('CauHoiId', $q->id)->select('id', 'noidung', 'caudung');
        $ansQuery->addSelect('stt')->orderBy('stt');
        $answers = $ansQuery->get()->map(function ($a) { 
            return [
                'id' => $a->id, 
                'stt' => $a->stt ?? null, 
                'noidung' => $a->noidung, 
                'caudung' => (bool)$a->caudung
            ]; });


        // images
        $images = collect();
        $tblI = (new tblHinhAnh())->getTable();
        if (Schema::hasTable($tblI)) {

                $imgQuery = tblHinhAnh::where('CauHoiId', $q->id);
                $imgQuery->where('active', 1);
                $imgQuery->orderBy('stt');

                $images = $imgQuery->get()->map(
                function($item){
                    $file = $item->Media->name;
                    $file = ltrim((string)$file, '/\\');
                return [
                    'url' => asset('images/cauhoi/' . $file),
                    'alt' => $file,
                ];
                }
            );
            
        }

        return response()->json(['id' => $q->id, 'stt' => $displayStt, 'noidung' => $q->noidung, 'cau_tra_lois' => $answers ?? [], 'images' => $images ?? []]);
    }

}