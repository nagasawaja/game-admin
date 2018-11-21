<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Models\Account;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            //每天清理一次sign_day=9999的帐号
            Account::singleton()->recoverAccount999();
        })->daily()->name('recoverAccount999')->withoutOverlapping();

        $schedule->call(function () {
            //每天清理一次oubo=16的识别欧泊错误的数据
            Account::singleton()->recoverOuBo16();
        })->daily()->name('recoverOuBo16')->withoutOverlapping();
    }
}
