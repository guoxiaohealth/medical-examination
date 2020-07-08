<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\Member;
use App\Model\Visit;
use App\Model\VisitDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitController extends Controller
{
    public function planMine(Request $request)
    {
        return $this->respondWithData(
            VisitDetails::with('member', 'managers')
                ->where('manager_id', auth()->id())
                ->whereMonth('plan_date', Carbon::now()->startOfMonth())->paginate()
        );
    }

    public function planList(Request $request)
    {
        return $this->respondWithData(
            VisitDetails::with('member', 'managers')
                ->whereMonth('plan_date', Carbon::now()->startOfMonth())->paginate()
        );
    }

    public function planTotal(Request $request)
    {
        return $this->respondWithData(
            Member::with('visit', 'visit.managers')->paginate()
        );
    }

    public function planCheck(Request $request, VisitDetails $visitDetails)
    {
        $request->validate([
            'manager_id' => 'required|integer|exists:managers,id',
            'state'      => 'required|max:255',
            'remarks'    => 'required|max:255',
        ]);
        $visitDetails->update([
            'manager_id' => auth()->id(),
            'state'      => $request->input('state'),
            'remarks'    => $request->input('remarks'),
            'real_date'  => Carbon::now()
        ]);
        return $this->respondWithSuccess();
    }

    public function planCreate(Request $request)
    {
        $request->validate([
            'member_id'   => 'required|integer|exists:members,id',
            'manager_id'  => 'required|integer|exists:managers,id',
            'status'      => 'required|boolean',
            'cycle'       => 'required|integer',
            'day'         => 'required|integer',
            'first_visit' => 'required|date_format:Y-m',
            'remarks'     => 'required|max:255',
        ]);
        Visit::updateOrCreate([
            'member_id' => $request->input('member_id'),
        ], array_filter($request->only([
            'manager_id', 'status', 'cycle', 'day', 'first_visit', 'remarks'
        ])));
        return $this->respondWithSuccess();
    }

    public function planRecords(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);

        return $this->respondWithData(
            VisitDetails::where('member_id', $request->input('member_id'))->get()
        );
    }
}
