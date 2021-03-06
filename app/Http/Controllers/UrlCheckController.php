<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use DiDom\Document;
use App\Jobs\CheckUrlJob;

class UrlCheckController extends Controller
{
    public const CHECK_TIMEOUT_SECOND = 3;

    private static String $tableName = 'url_checks';

    public static function getTableName(): string
    {
        return self::$tableName;
    }

    public function store(Request $request, int $urlId): \Illuminate\Http\RedirectResponse
    {
        $checkData = [];
        $checkData['url_id'] = $urlId;
        $currentTime = Carbon::now()->toString();
        $checkData['created_at'] = $currentTime;
        $checkData['updated_at'] = $currentTime;
        $checkData['status'] = 'pending';
        $newCheckId = DB::table(self::$tableName)->insertGetId($checkData);
        $url = DB::table(UrlController::getTableName())->find($urlId);

        dispatch(new CheckUrlJob($url->name, $newCheckId));
        flash('Site will be checked soon! Please wait or return to this page later...')->info()->important();
        return redirect()->route('urls.show', ['url' => $urlId]);
    }
}
