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
        $domains = DB::table($this->tableName)->get();
        return view('domains.index', ['domains' => $domains]);
    }
    public function show($id)
    {
        $domain = DB::table($this->tableName)->find($id);
        return view('domains.show', ['domain' => $domain]);
    }
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'domain.name' => [
                'required',
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
