<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DomainCheckController extends Controller
{
    public $tableName = 'domain_checks';
    public function store(Request $request, $domainId)
    {
        // Log::info(dump($request));
        // Log::info(dump($domainId));
        $currentTime = Carbon::now()->toString();
        $data['domain_id'] = $domainId;
        $data['created_at'] = $currentTime;
        $data['updated_at'] = $currentTime;
        if (DB::table($this->tableName)->insert($data)) {
            $request->session()->flash('info', 'Website has been checked!');
        };
        return redirect()->route('domains.show', ['id' => $domainId]);
    }
}
