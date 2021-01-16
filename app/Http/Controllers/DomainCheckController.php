<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use DiDom\Document;
use App\Jobs\CheckDomainJob;

class DomainCheckController extends Controller
{
    public const CHECK_TIMEOUT_SECOND = 3;

    private static $tableName = 'domain_checks';

    public static function getTableName()
    {
        return self::$tableName;
    }

    public function store(Request $request, $domainId)
    {
        $checkData['domain_id'] = $domainId;
        $currentTime = Carbon::now()->toString();
        $checkData['created_at'] = $currentTime;
        $checkData['updated_at'] = $currentTime;
        $checkData['status'] = 'pending';
        $newCheckId = DB::table(self::$tableName)->insertGetId($checkData);
        $domain = DB::table(DomainController::getTableName())->find($domainId);

        dispatch(new CheckDomainJob($domain->name, $newCheckId));
        flash('Site will be checked soon! Please wait or return to this page later...')->info()->important();
        return redirect()->route('domains.show', ['domain' => $domainId]);
    }
}
