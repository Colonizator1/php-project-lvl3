<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UrlController extends Controller
{
    private static String $tableName = 'urls';

    public static function getTableName(): string
    {
        return self::$tableName;
    }

    public function index(Request $request): \Illuminate\Contracts\View\View
    {

        $lastChecksIds = DB::table(UrlCheckController::getTableName())
        ->select(DB::raw('max(id) as id'))
        ->groupBy('url_id')
        ->pluck('id');

        $lastChecks = DB::table(UrlCheckController::getTableName())
        ->whereIn('id', $lastChecksIds)
        ->get();

        $urls = DB::table(self::$tableName)->get()->map(function ($url, $key) use ($lastChecks) {
            $url->last_check = $lastChecks->contains('url_id', $url->id) ? $lastChecks->firstWhere('url_id', $url->id) : null;
            return $url;
        });
        return view('urls.index', ['urls' => $urls]);
    }

    public function show(int $id): \Illuminate\Contracts\View\View
    {
        $url = DB::table(self::$tableName)->find($id);
        if ($url === null) {
            abort(404);
        }
        $urlChecks = DB::table(UrlCheckController::getTableName())->where('url_id', $id)->orderBy('created_at')->get();
        return view('urls.show', ['url' => $url, 'urlChecks' => $urlChecks]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $preparedRequest = $request->all();
        if ($request['url']['name'] !== null && !$this->isUrlHasProtocol($request['url']['name'])) {
            $preparedRequest['url']['name'] = 'http://' . $request['url']['name'];
        }
        $rules = [
            'url.name' => [
                'required',
                'max:255',
                'url'
                // function ($attribute, $value, $fail): void {
                //     if (!$this->isValidUrl($value)) {
                //         $fail('Url is invalid.');
                //     }
                // }
            ],
        ];
        $a = $preparedRequest;
        $validator = Validator::make($preparedRequest, $rules, $messages = [
            'required' => 'Please, fill out the url for check.',
            'max' => 'Url may not be greater than 255 characters.'
        ]);
        $duplicateUrl = DB::table(self::$tableName)->where('name', $preparedRequest['url']['name']);
        if ($duplicateUrl->first() !== null) {
            $duplicateUrlId = $duplicateUrl->value('id');
            flash('Url ' . $preparedRequest['url']['name'] . ' has already been taken.')->warning();
            return redirect()->route('urls.show', ['url' => $duplicateUrlId]);
        }
        $data = $validator->validate();
        $currentTime = Carbon::now()->toString();
        $data['url']['created_at'] = $currentTime;
        $data['url']['updated_at'] = $currentTime;
        $newUrlId = DB::table(self::$tableName)->insertGetId($data['url']);
        flash('Url added successfully!')->success();
        return redirect()->route('urls.show', ['url' => $newUrlId]);
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $url = DB::table(self::$tableName)->where('id', $id);
        $url->delete();
        $urlChecks = DB::table(UrlCheckController::getTableName())->where('url_id', $id)->delete();
        return redirect()->route('urls.index');
    }

    protected function isUrlHasProtocol(string $url): bool
    {
        $urlParts = parse_url($url);
        return isset($urlParts['scheme']);
    }
}
