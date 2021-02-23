<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Mao;

class MaoController extends Controller
{
    //单个商品销量变化
    public function goodsChangeHistory(Request $request)
    {
        $gameIds = false;
        $sellerName = trim($request->input('seller_name'));
        $goodsId = trim($request->input('goods_id'));
        $where = [
            ['goods_count', '<', 300],
            ['price', '>=', 0.58],
            ['mao_games_goods_detail.create_datetime', '>=', $request->input('goods_detail_create_datetime_start')],
            ['mao_games_goods_detail.create_datetime', '<', $request->input('goods_detail_create_datetime_end')]
        ];
        if ($request->input('game_id') > 0) {
            $gameIds = [$request->input('game_id')];
        }
        $bb = DB::table("mao_games_goods_detail")
            ->join("mao_games_goods", "mao_games_goods.goods_id", '=', 'mao_games_goods_detail.goods_id')
            ->selectRaw('mao_games_goods_detail.goods_id')
            ->whereIn('mao_games_goods.game_id', $gameIds)
            ->where('mao_games_goods_detail.create_datetime', '<', $request->input('goods_detail_create_datetime_end'))
            ->where('mao_games_goods_detail.create_datetime', '>=', $request->input('goods_detail_create_datetime_start'))
            ->groupBy('mao_games_goods_detail.goods_id')
            ->havingRaw('count(mao_games_goods_detail.goods_id) >= 2')
            ->get();
        if($bb == null) {
            $goodsIdIn = [];
        } else {
            $goodsIdIn = [];
            foreach($bb as $k => $v) {
                $goodsIdIn[] = $v->goods_id;
            }
        }
        $rows = DB::table("mao_games_goods")
            ->join("mao_games", "mao_games.game_id", '=', 'mao_games_goods.game_id')
            ->join('mao_games_goods_detail', 'mao_games_goods.goods_id', '=', 'mao_games_goods_detail.goods_id')
            ->orderBy(DB::raw('mao_games.id , mao_games_goods.goods_id, create_datetime'))
            ->selectRaw("mao_games.title as game_title, mao_games_goods_detail.title as goods_title,price,mao_games_goods_detail.goods_count as single_goods_count,mao_games_goods.goods_id as goods_id,mao_games_goods_detail.create_datetime as goods_sale_create_datetime,mao_games.game_id,mao_games_goods.url as goods_url,mao_games_goods.seller_name")
            ->where($where)
            ->whereIn("mao_games_goods.goods_id", $goodsIdIn)
            ->when($sellerName, function($query) use($sellerName) {
                $query->where("mao_games_goods.seller_name", '=', $sellerName);
            })
            ->when($goodsId, function($query) use($goodsId) {
                $query->where("mao_games_goods.goods_id", '=', $goodsId);
            })
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
        $rows = DB::table('mao_games_stc')->where('create_datetime','=', $stcCreateDatetime)->get();

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
        $rows = DB::table('mao_games_stc')
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

    // 脚本数据
    public function scriptRecord(Request $request) {
        $stcCreateDatetime = $request->input('stc_create_datetime');
        $minShow = $request->input('min_show');
        $rows = DB::table('script_record')->where('record_date','=', $stcCreateDatetime)->orderBy("game_id")->orderBy("status")->get();

        $rowssInit = DB::table('run_log_statistics')
            ->where('record_date','=', $stcCreateDatetime)
            ->orderBy("game_id")
            ->orderByDesc("count")
            ->get();
        $rowss = [];
        $minShowGameIdArr = [];
        foreach($rowssInit as $k=>$v) {
            if(!isset($minShowGameIdArr[$v->game_id])) {
                $minShowGameIdArr[($v->game_id)] = 0;
            }
            $minShowGameIdArr[$v->game_id] += 1;
            if($minShowGameIdArr[$v->game_id] > $minShow) {
                continue;
            }
            $rowss[] = $v;
        }
        return JSON::ok([
            'items' => $rows,
            'detailItems' => $rowss,
        ]);
    }

    // 游戏状态
    public function gameStatus(Request $request) {
        $hgetall = Redis::hGetAll("game_status");
        return JSON::ok([
            'items' => $hgetall
        ]);
    }

    // 修改游戏状态
    public function revertGameStatus(Request $request) {
        $gameName = $request->input('gameName');
        $modifyStatus = $request->input('modifyStatus');
        Redis::hSet("game_status", $gameName, $modifyStatus);
        return JSON::ok([], $gameName. ' changTo ' . $modifyStatus ." success");
    }

    public function clearRedisAccountCache(Request $request) {
        $gameName = $request->input('gameName');
        $keys = Redis::keys("accountList:".$gameName."*");
        foreach($keys as $k=>$v) {
            Redis::del($v);
        }
        return JSON::ok([], "clear ". $gameName ." success");
    }

    // 恢复账号异常
    public function recoverAccountStatus(Request $request) {
        $gameName = $request->input('gameName');
        $yesterday = date('Y-m-d', time());
        $updateMap = ['status' => 2];
        $affectRow = 0;
        switch($gameName) {
            case 'id5':
                $affectRow = DB::table('account as a')->join('game_id5_account_detail as id5', 'a.id', '=', 'id5.account_id')
                    ->whereIn('a.status', [6,8])
                    ->where('id5.game_update_time', '<', strtotime($yesterday))
                    ->where('a.game_id', '=', 6587)
                    ->update($updateMap);
                break;
            case 'f7':
                $affectRow = DB::table('account as a')->join('game_f7_account_detail as f7', 'a.id', '=', 'f7.account_id')
                    ->whereIn('a.status', [6,8])
                    ->where('f7.game_update_time', '<', strtotime($yesterday))
                    ->update($updateMap);
                break;
            case 'pes':
                $affectRow = DB::table('account as a')->join('game_pes_account_detail as football', 'a.id', '=', 'football.account_id')
                    ->whereIn('a.status', [6,8])
                    ->where('football.game_update_time', '<', strtotime($yesterday))
                    ->update($updateMap);
                break;
            case 'mz':
                $affectRow = DB::table('account as a')->join('game_mz_account_detail as dream', 'a.id', '=', 'dream.account_id')
                    ->whereIn('a.status', [6,8])
                    ->where('dream.game_update_time', '<', strtotime($yesterday))
                    ->update($updateMap);
                break;
            case 'id5Android':
                $affectRow = DB::table('account as a')->join('game_id5_account_detail as id5', 'a.id', '=', 'id5.account_id')
                    ->whereIn('a.status', [6,8])
                    ->where('id5.game_update_time', '<', strtotime($yesterday))
                    ->where('a.game_id', '=', 6586)
                    ->update($updateMap);
                break;
        }
        return JSON::ok([],'recoverAccountStatus ' . $gameName . ';' . 'affect ' . $affectRow);
    }

    // 获取一个idcard
    public function getIdCard(Request $request) {
        $row = DB::table('idcard')->first();
        DB::table('idcard')->where('id', '=', $row->id)->delete();
        return JSON::ok(['idcard'=> $row]);
    }

    // 修改游戏的redis配置
    public function setGameConfig(Request $request) {

    }

    // 获取游戏的redis配置
    public function getGameConfig(Request $request) {

    }

}