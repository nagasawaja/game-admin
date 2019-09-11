<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Mao extends Model
{
    //游戏列表
   public function getGameList() {
       $gameRows = DB::table('mao_games')->selectRaw('game_id, title')->get();
       //热门游戏名单
       $findStrRows = ['阴阳师【苹果版】礼包', '永远的7日之都【苹果版】礼包', '实况足球【苹果版】礼包', '第五人格【苹果版】礼包'];
       $hotGameRows = [];
       foreach($gameRows as $k=> $gameRow) {
           $gameRows[$k]->title = $gameRow->title;
           foreach($findStrRows as $findStrRow) {
               if(strpos($gameRow->title, $findStrRow) !== false) {
                   $hotGameRows[] = $gameRows[$k];
                   unset($gameRows[$k]);
               }
           }
       }
       //整理后的名单
       $finalGameRows = [
           [
               'label' => '热门游戏',
               'option' => $hotGameRows
           ],
           [
               'label' => '游戏',
               'option' => $gameRows
           ]
       ];
       return ['final_game_rows' => $finalGameRows];
   }

    public static function singleton()
    {
        static $inst = null;
        if (!$inst) {
            $inst = new static();
        }

        return $inst;
    }
}
