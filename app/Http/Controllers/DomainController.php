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

        $lastChecksIds = DB::table(DomainCheckController::getTableName())
        ->select(DB::raw('max(id) as id'))
        ->groupBy('domain_id')
        ->pluck('id');

        $lastChecks = DB::table(DomainCheckController::getTableName())
        ->whereIn('id', $lastChecksIds)
        ->get();

        $domains = DB::table(self::$tableName)->get()->map(function ($domain, $key) use ($lastChecks) {
            $domain->last_check = $lastChecks->contains('domain_id', $domain->id) ? $lastChecks->firstWhere('domain_id', $domain->id) : null;
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
        $domainChecks = DB::table(DomainCheckController::getTableName())->where('domain_id', $id)->orderBy('created_at')->get();
        return view('domains.show', ['domain' => $domain, 'domainChecks' => $domainChecks]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'domain.name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail): void {
                    if (!$this->isValidUrl($value)) {
                        $fail('Url is invalid.');
                    }
                }
            ],
        ];
        $validator = Validator::make($request->all(), $rules, $messages = [
            'required' => 'Please, fill out the url for check.',
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
        flash('Domain added successfully!')->success();
        return redirect()->route('domains.show', ['domain' => $newDomainId]);
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $domain = DB::table(self::$tableName)->where('id', $id);
        $domain->delete();
        $domainChecks = DB::table(DomainCheckController::getTableName())->where('domain_id', $id)->delete();
        return redirect()->route('domains.index');
    }

    protected function isValidUrl(string $url): bool
    {
        $urlParts = parse_url($url);
        if (isset($urlParts['host'])) {
            $domain = $urlParts['host'];
        } else {
            $domain = explode('/', $url)[0];
        }
        return $this->isValidDomain($domain);
    }

    protected function isValidDomain(string $domain): bool
    {
        return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $domain) === 1;
    }
}
