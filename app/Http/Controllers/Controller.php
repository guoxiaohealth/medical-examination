<?php

namespace App\Http\Controllers;

use App\Model\ConfigKind;
use App\Model\ConfigMerit;
use App\Model\ConfigProject;
use App\Model\ConfigSubject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var mixed
     */
    protected $user;

    /**
     * @return \Illuminate\Support\Optional|mixed
     */
    protected function user()
    {
        if (!$this->user) {
            $this->user = optional(auth()->user());
        }
        return $this->user;
    }

    /**
     * @param $v
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithData($v)
    {
        return response()->json($v);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithSuccess()
    {
        return response()->json('success');
    }

    /**
     * @param $error
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($error, $status = 400)
    {
        return response()->json(['error' => $error], $status);
    }

    protected function cmp($merit, $value)
    {
        if (!$value) {
            return null;
        }
        switch ($merit['symbol']) {
            case 'eq':
                return $value == $merit['values'];
            case 'gt':
                return floatval($value) > floatval($merit['values']);
            case 'gte':
                return floatval($value) >= floatval($merit['values']);
            case 'lt':
                return floatval($value) < floatval($merit['values']);
            case 'lte':
                return floatval($value) <= floatval($merit['values']);
            case 'range':
                $cmp = explode(',', strval($merit['values']));
                if (count($cmp) != 2) {
                    return null;
                }
                return floatval($cmp[0]) <= floatval($value) && floatval($value) <= floatval($cmp[1]);
            case 'rangeout':
                $cmp = explode(',', strval($merit['values']));
                if (count($cmp) != 2) {
                    return null;
                }
                return floatval($cmp[0]) > floatval($value) && floatval($value) < floatval($cmp[1]);
            case 'match':
                return Str::contains(strval($merit['values']), strval($value));
            default:
                return null;
        }
    }

    protected function meritsAbnormal($merit)
    {
        $merit['ex'] = collect($merit['ex'])->map(function ($v) use ($merit) {
            $v['status'] = $this->cmp($v, $merit['value']);
            return $v;
        });
        return $merit;
    }

    protected function medicalPlanMerits(array $merits)
    {
        return collect($merits)->map(function ($v) {
            $merit          = ConfigMerit::where('id', $v['id'])->value('expression');
            $merit['value'] = $v['value'];
            return $this->meritsAbnormal($merit);
        });
    }

    protected function meritAbnormal($merit)
    {
        if (empty($merit)) {
            return [];
        }
        if (empty($merit['expression'])) {
            return [];
        }
        if (empty($merit['expression']['ex'])) {
            return [];
        }
        foreach ($merit['expression']['ex'] as $key => $ex) {
            $merit['expression']['ex'][$key]['status'] = $this->cmp($ex, $merit['value']);
        }
        return $merit;
    }

    protected function medicalPlanKinds(array $kinds)
    {
        return collect($kinds)->map(function ($v) {
            $kind           = ConfigKind::find($v['id']) ?? new \stdClass();
            $kind->id       = $v['id'];
            $kind->projects = collect($v['projects'])->map(function ($v) {
                $project           = ConfigProject::find($v['id']) ?? new \stdClass();
                $project->id       = $v['id'];
                $project->subjects = collect($v['subjects'])->map(function ($v) {
                    $subject           = ConfigSubject::find($v['id']) ?? new \stdClass();
                    $subject->id       = $v['id'];
                    $subject->original = $v['original'];
                    $subject->date     = $v['date'];
                    $subject->merits   = collect($v['merits'])->map(function ($v) {
                        $merit = ConfigMerit::find($v['id']);
                        if (is_null($merit)) {
                            $merit = [];
                        } else {
                            $merit = $merit->toArray();
                        }
                        $merit['id']    = $v['id'];
                        $merit['value'] = $v['value'];
                        return $this->meritAbnormal($merit);
                    })->filter(function ($v) {
                        return !empty($v);
                    });
                    return $subject;
                });
                return $project;
            });
            return $kind;
        });
    }

    public function makeMedicalPlanKinds(array $kinds)
    {
        return collect($kinds)->map(function ($kind) {
            return [
                'id'       => $kind['id'],
                'projects' => collect($kind['projects'])->map(function ($project) use ($kind) {
                    ConfigProject::where([
                        'id'             => $project['id'],
                        'config_kind_id' => $kind['id'],
                    ])->firstOrFail();
                    return [
                        'id'       => $project['id'],
                        'subjects' => collect($project['subjects'])->map(function ($subject) use ($project) {
                            ConfigSubject::where([
                                'id'                => $subject['id'],
                                'config_project_id' => $project['id'],
                            ])->firstOrFail();
                            if (collect($subject['merits'])->isEmpty()) {
                                return [
                                    'id'       => $subject['id'],
                                    'original' => optional($subject)['original'],
                                    'date'     => optional($subject)['date'],
                                    'merits'   => ConfigMerit::query()->where([
                                        'config_subject_id' => $subject['id'],
                                    ])->pluck('id')->map(function ($id) {
                                        return [
                                            'id'    => $id,
                                            'value' => null,
                                        ];
                                    }),
                                ];
                            } else {
                                return [
                                    'id'       => $subject['id'],
                                    'original' => optional($subject)['original'],
                                    'date'     => optional($subject)['date'],
                                    'merits'   => collect($subject['merits'])->map(function ($merit) use ($subject) {
                                        ConfigMerit::where([
                                            'id'                => $merit['id'],
                                            'config_subject_id' => $subject['id'],
                                        ])->firstOrFail();
                                        return [
                                            'id'    => $merit['id'],
                                            'value' => optional($merit)['value'],
                                        ];
                                    })
                                ];
                            }
                        })
                    ];
                }),
            ];
        });
    }
}
