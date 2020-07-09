<?php

namespace App\Console\Commands;

use App\Model\VisitDetails;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Visit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'visit description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::transaction(function () {
            \App\Model\Visit::where('first_visit', '>=', Carbon::now()->startOfMonth())
                ->where('status', 1)->get()->map(function ($v) {
                    $currentMouth   = Carbon::now()->startOfMonth();
                    $firstVisit     = Carbon::parse($v->first_visit);
                    $firstVisitDate = Carbon::create($firstVisit->year, $firstVisit->month, $v->day);
                    if ($firstVisitDate->startOfMonth()->eq($currentMouth) && !VisitDetails::where('plan_date', $firstVisitDate)->exists()) {
                        VisitDetails::create([
                            'visit_id'   => $v->id,
                            'manager_id' => $v->manager_id,
                            'member_id'  => $v->member_id,
                            'state'      => '',
                            'remarks'    => '',
                            'plan_date'  => $firstVisitDate,
                            'real_date'  => null,
                        ]);
                    }
                    $cycle = Carbon::create($firstVisit->year, $firstVisit->month + $v->cycle, $v->day);
                    if ($cycle->startOfMonth()->eq($currentMouth) && !VisitDetails::where('plan_date', $cycle)->exists()) {
                        VisitDetails::create([
                            'visit_id'   => $v->id,
                            'manager_id' => $v->manager_id,
                            'member_id'  => $v->member_id,
                            'state'      => '',
                            'remarks'    => '',
                            'plan_date'  => $cycle,
                            'real_date'  => null,
                        ]);
                    }
                });
        });
        return 0;
    }
}
