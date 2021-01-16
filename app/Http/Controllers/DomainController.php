<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    private static $tableName = 'domains';

    public static function getTableName()
    {
        return self::$tableName;
    }

    public function index(Request $request)
    {
        $latestChecks = DB::table(DomainCheckController::getTableName())
            ->select('domain_id', DB::raw('MAX(created_at) as last_domain_check'))
            ->groupBy('domain_id');
        $domains = DB::table(self::$tableName)
            ->leftJoinSub($latestChecks, 'latest_checks', function ($join) {
                $join->on('domains.id', '=', 'latest_checks.domain_id');
            })->leftJoin(DomainCheckController::getTableName(), function ($join) {
                $join->on('latest_checks.last_domain_check', '=', DomainCheckController::getTableName() . '.created_at');
            })->select(
                self::$tableName . '.*',
                'latest_checks.last_domain_check',
                DomainCheckController::getTableName() . '.status_code'
            )
            ->get();
        return view('domains.index', ['domains' => $domains]);
    }
    public function show($id)
    {
        $domain = DB::table(self::$tableName)->find($id);
        if ($domain === null) {
            return abort(404);
        }
        $domainChecks = DB::table(DomainCheckController::getTableName())->where('domain_id', $id)->get();
        return view('domains.show', ['domain' => $domain, 'domainChecks' => $domainChecks]);
    }
    public function store(Request $request)
    {
        $rules = [
            'domain.name' => [
                'unique:domains,name',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidUrl($value)) {
                        $fail('Url is invalid.');
                    }
                }
            ],
        ];
        $validator = Validator::make($request->all(), $rules, $messages = [
            'unique' => 'Url :input has already been taken.',
            'max' => 'Url may not be greater than 255 characters.'
        ]);
        $errors = $validator->errors();
        $failedRules = $validator->failed();
        Log::debug(\dump($failedRules));
        Log::debug(\dump($errors));
        if ($failedRules && collect($failedRules['domain.name'])->has('Unique')) {
            $duplicateDomainId = DB::table(self::$tableName)->where('name', $request['domain']['name'])->value('id');
            return redirect()->route('domains.show', ['domain' => $duplicateDomainId])->withErrors($validator);
        }
        $data = $validator->validate();
        $currentTime = Carbon::now()->toString();
        $data['domain']['created_at'] = $currentTime;
        $data['domain']['updated_at'] = $currentTime;
        $newDomainId = DB::table(self::$tableName)->insertGetId($data['domain']);
        if (\is_integer($newDomainId)) {
            flash('Domain added successfully!')->success();
            // $request->session()->flash('success', 'Domain added successfully!');
        };
        return redirect()->route('domains.show', ['domain' => $newDomainId]);
    }
    public function destroy($id)
    {
        $domain = DB::table(self::$tableName)->where('id', $id);
        if ($domain) {
            $domain->delete();
        }
        $domainChecks = DB::table(DomainCheckController::getTableName())->where('domain_id', $id);
        if ($domainChecks) {
            $domainChecks->delete();
        }
        return redirect()->route('domains.index');
    }
    protected function isValidUrl($url)
    {
        $parse = parse_url($url);
        if (isset($parse['host'])) {
            $domain = $parse['host'];
        } else {
            $domain = explode('/', $url)[0];
        }
        return $this->isValidDomain($domain);
    }
    protected function isValidDomain($domain)
    {
        return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $domain);
    }
}
