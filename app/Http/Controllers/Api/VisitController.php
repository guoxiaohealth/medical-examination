<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\Member;
use App\Model\Visit;
use App\Model\VisitDetails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function visitDetailsMine(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
            'status' => 'in:1,2,3',
        ]);
        return $this->respondWithData(
            VisitDetails::with('member', 'manager')
                ->where('manager_id', $this->user()->id)
                ->whereMonth('plan_date', Carbon::now()->startOfMonth())
                ->when($request->input('search'), function (Builder $query, $value) {
                    $query->whereHas('member', function (Builder $query) use ($value) {
                        $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
                    });
                })->when($request->input('status'), function (Builder $query, $value) {
                    switch ($value) {
                        case 1:
                            $query->whereNull('real_date');
                            break;
                        case 2:
                            $query->whereNotNull('real_date');
                            break;
                        case 3:
                            $query->whereNull('real_date')->where('plan_date', '<', Carbon::now());
                            break;
                    }
                })->paginate()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function visitDetailsTotal(Request $request)
    {
        $request->validate([
            'search'     => 'string|nullable|max:255',
            'status'     => 'in:1,2,3',
            'manager_id' => 'integer',
        ]);
        return $this->respondWithData(
            VisitDetails::with('member', 'manager')
                ->whereMonth('plan_date', Carbon::now()->startOfMonth())
                ->when($request->input('integer'), function (Builder $query, $value) {
                    $query->where('manager_id', $value);
                })->when($request->input('search'), function (Builder $query, $value) {
                    $query->whereHas('member', function (Builder $query) use ($value) {
                        $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
                    });
                })->when($request->input('status'), function (Builder $query, $value) {
                    switch ($value) {
                        case 1:
                            $query->whereNull('real_date');
                            break;
                        case 2:
                            $query->whereNotNull('real_date');
                            break;
                        case 3:
                            $query->whereNull('real_date')->where('plan_date', '<', Carbon::now());
                            break;
                    }
                })->paginate()
        );
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function planList(Request $request)
    {
        $request->validate([
            'search'         => 'string|nullable|max:255',
            'member_kind_id' => 'integer',
            'manager_id'     => 'integer',
        ]);
        return $this->respondWithData(
            Member::with('visit', 'visit.manager')
                ->when($request->input('member_kind_id'), function (Builder $query, $value) {
                    $query->where('member_kind_id', $value);
                })->when($request->input('manager_id'), function (Builder $query, $value) {
                    $query->whereHas('visit', function (Builder $query) use ($value) {
                        $query->where('manager_id', $value);
                    });
                })->when($request->input('search'), function (Builder $query, $value) {
                    $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
                })
                ->paginate()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function planCreate(Request $request)
    {
        $request->validate([
            'member_id'   => 'required|integer|exists:members,id',
            'manager_id'  => 'required|integer|exists:managers,id',
            'status'      => 'required|boolean',
            'cycle'       => 'required|integer|max:12',
            'day'         => 'required|integer|max:28',
            'first_visit' => 'required|date_format:Y-m-d|after:now',
            'remarks'     => 'required|max:255',
        ]);
        Visit::updateOrCreate([
            'member_id' => $request->input('member_id'),
        ], array_filter($request->only([
            'manager_id', 'status', 'cycle', 'day', 'first_visit', 'remarks'
        ])));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param VisitDetails $visit
     * @return \Illuminate\Http\JsonResponse
     */
    public function planCheck(Request $request, VisitDetails $visit)
    {
        $request->validate([
            'state'   => 'required|max:255',
            'remarks' => 'required|max:255',
        ]);
        $visit->update([
            'manager_id' => $this->user()->id,
            'state'      => $request->input('state'),
            'remarks'    => $request->input('remarks'),
            'real_date'  => Carbon::now()
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function visitDetailsList(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);
        return $this->respondWithData(
            VisitDetails::with('manager')
                ->where('member_id', $request->input('member_id'))->get()
        );
    }
}
