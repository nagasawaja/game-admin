<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mao;

class MaoController extends Controller
{
    //单个商品销量变化
    public function goodsChangeHistory(Request $request)
    {
        $gameIds = false;
        $where = [
            ['goods_count', '<', 100],
            ['price', '>', 5.00],
            ['mao_games_goods_detail.create_datetime', '>=', $request->input('goods_detail_create_datetime_start')],
            ['mao_games_goods_detail.create_datetime', '<', $request->input('goods_detail_create_datetime_end')]
        ];
        if ($request->input('game_id') > 0) {
            $gameIds = [$request->input('game_id')];
        }
        $rows = DB::connection('jiaoyimao')->table("mao_games_goods")
            ->join("mao_games", "mao_games.game_id", '=', 'mao_games_goods.game_id')
            ->join('mao_games_goods_detail', 'mao_games_goods.goods_id', '=', 'mao_games_goods_detail.goods_id')
            ->orderBy(DB::raw('mao_games.id , mao_games_goods.goods_id, create_datetime'))
            ->whereRaw(DB::raw("mao_games_goods.goods_id in (select goods_id from mao_games_goods_detail group by goods_id having count(*) >= 2)"))
            ->selectRaw("mao_games.title as game_title, mao_games_goods_detail.title as goods_title,price,mao_games_goods_detail.goods_count as single_goods_count,mao_games_goods.goods_id as goods_id,mao_games_goods_detail.create_datetime as goods_sale_create_datetime,mao_games.game_id,mao_games_goods.url as goods_url")
            ->where($where)
            ->when($gameIds, function($query) use($gameIds)  {
                $query->whereIn("mao_games.game_id", $gameIds);
            })->get();
        //获取游戏列表
        $gameRows = Mao::singleton()->getGameList();

        return JSON::ok([
            'total' => $rows->count(),
            'items' => $rows,
            'final_game_rows' => $gameRows['final_game_rows'],
        ]);
    }

    //商品总数量与销量比例
    public function goodsScale(Request $request)
    {
        $stcCreateDatetime = $request->input('stc_create_datetime');
        $rows = DB::connection('jiaoyimao')->table('mao_games_stc')->where('create_datetime','=', $stcCreateDatetime)->get();

        return JSON::ok([
            'total' => $rows->count(),
            'items' => $rows,
        ]);
    }

    //数据报表
    public function dateReport(Request $request) {
        $gameId = $request->input('game_id');
        $stcCreateDatetimeStart = $request->input('stc_create_datetime_start');
        $stcCreateDatetimeEnd = $request->input('stc_create_datetime_end');
        $rows = DB::connection('jiaoyimao')
            ->table('mao_games_stc')
            ->where('create_datetime', '>=', $stcCreateDatetimeStart)
            ->where('create_datetime', '<=', $stcCreateDatetimeEnd)
            ->where('create_datetime', 'like', '%' . substr($stcCreateDatetimeStart, -8))
            ->where('game_id', '=', $gameId)
            ->select(DB::raw('game_id,sale_count,goods_total_count,substring(create_datetime,1,10) as create_datetime,title,TRUNCATE(stc, 2) as stc'))
            ->get();
        //获取游戏列表
        $gameRows = Mao::singleton()->getGameList();

        return JSON::ok([
            'rows' => $rows,
            'final_game_rows' => $gameRows['final_game_rows'],
        ]);
    }
}
