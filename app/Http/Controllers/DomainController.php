<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    private static String $tableName = 'domains';

    public static function getTableName(): string
    {
        return self::$tableName;
    }

    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $allChecks = DB::table(DomainCheckController::getTableName())->get()->groupBy('domain_id')->map(function ($domainChecks, $domain_id) {
            return $domainChecks->sortBy('created_at')->last();
        });
        $domains = DB::table(self::$tableName)->get()->map(function ($domain, $key) use ($allChecks) {
            $domain->last_check = $allChecks->has($domain->id) ? $allChecks[$domain->id] : null;
            return $domain;
        });
        return view('domains.index', ['domains' => $domains]);
    }

    public function show(int $id): \Illuminate\Contracts\View\View
    {
        $domain = DB::table(self::$tableName)->find($id);
        if ($domain === null) {
            abort(404);
        }
        $domainChecks = DB::table(DomainCheckController::getTableName())->where('domain_id', $id)->get();
        return view('domains.show', ['domain' => $domain, 'domainChecks' => $domainChecks]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'domain.name' => [
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidUrl($value)) {
                        $fail('Url is invalid.');
                    }
                }
            ],
        ];
        $validator = Validator::make($request->all(), $rules, $messages = [
            'max' => 'Url may not be greater than 255 characters.'
        ]);
        $duplicateDomain = DB::table(self::$tableName)->where('name', $request['domain']['name']);
        if ($duplicateDomain->first() !== null) {
            $duplicateDomainId = $duplicateDomain->value('id');
            flash('Url ' . $request['domain']['name'] . ' has already been taken.')->warning();
            return redirect()->route('domains.show', ['domain' => $duplicateDomainId]);
        }
        $data = $validator->validate();
        $currentTime = Carbon::now()->toString();
        $data['domain']['created_at'] = $currentTime;
        $data['domain']['updated_at'] = $currentTime;
        $newDomainId = DB::table(self::$tableName)->insertGetId($data['domain']);
        if (\is_integer($newDomainId)) {
            flash('Domain added successfully!')->success();
        };
        return redirect()->route('domains.show', ['domain' => $newDomainId]);
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $domain = DB::table(self::$tableName)->where('id', $id);
        if ($domain !== null) {
            $domain->delete();
            $domainChecks = DB::table(DomainCheckController::getTableName())->where('domain_id', $id)->delete();
        }
        return redirect()->route('domains.index');
    }

    protected function isValidUrl(string $url): bool
    {
        $parse = parse_url($url);
        if (isset($parse['host'])) {
            $domain = $parse['host'];
        } else {
            $domain = explode('/', $url)[0];
        }
        return $this->isValidDomain($domain);
    }

    protected function isValidDomain(string $domain): bool
    {
        return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $domain);
    }
}
