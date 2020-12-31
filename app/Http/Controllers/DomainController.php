<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    public $tableName = 'domains';
    public function index(Request $request)
    {
        $latestChecks = DB::table('domain_checks')
            ->select('domain_id', DB::raw('MAX(created_at) as last_domain_check'))
            ->groupBy('domain_id');

        $domains = DB::table($this->tableName)
            ->leftJoinSub($latestChecks, 'latest_checks', function ($join) {
                $join->on('domains.id', '=', 'latest_checks.domain_id');
            })->get();

        return view('domains.index', ['domains' => $domains]);
    }
    public function show($id)
    {
        $domain = DB::table($this->tableName)->find($id);
        $domainChecks = DB::table('domain_checks')->where('domain_id', $id)->get();
        return view('domains.show', ['domain' => $domain, 'domainChecks' => $domainChecks]);
    }
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'domain.name' => [
                'unique:domains,name',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidUrl($value)) {
                        $fail('Url is invalid.');
                    }
                }
            ],
        ]);
        $currentTime = Carbon::now()->toString();

        $data['domain']['created_at'] = $currentTime;
        $data['domain']['updated_at'] = $currentTime;

        if (DB::table($this->tableName)->insert($data)) {
            $request->session()->flash('success', 'Domain added successfully!');
        };
        return redirect()->route('domains.index');
    }
    public function destroy($id)
    {
        $domain = DB::table($this->tableName)->where('id', $id);
        if ($domain) {
            $domain->delete();
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
