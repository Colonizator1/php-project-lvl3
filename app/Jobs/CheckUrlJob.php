<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use DiDom\Document;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UrlCheckController;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckUrlJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public String $urlName;
    public Int $urlCheckId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $urlName, int $urlCheckId)
    {
        $this->urlName = $urlName;
        $this->urlCheckId = $urlCheckId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $urlResponse = Http::timeout(10)->get($this->urlName);
        $dom = new Document($urlResponse->body());
        $data = [];
        if ($dom->has('meta[name=keywords]')) {
            $data['keywords'] = $dom->find('meta[name=keywords]')[0]->getAttribute('content');
        }
        if ($dom->has('meta[name=description]')) {
            $data['description'] = $dom->find('meta[name=description]')[0]->getAttribute('content');
        }
        if ($dom->has('h1')) {
            /* Spagetti code for pass hexlet autotests */
            $h1 = $dom->first('h1') . '';
            $patterns = ['/<h1(.*?)>/','/<\/h1>/'];
            $readyH1 = preg_replace($patterns, '', $h1);
            $data['h1'] = $readyH1;
        }

        $data['status_code'] = $urlResponse->status();
        $currentTime = Carbon::now()->toString();
        $data['updated_at'] = $currentTime;
        $data['status'] = 'success';

        DB::table(UrlCheckController::getTableName())
            ->where('id', $this->urlCheckId)
            ->update($data);
        $urlCheck = DB::table(UrlCheckController::getTableName())->find($this->urlCheckId);
        if ($urlCheck !== null) {
            flash('Url checked successfully!')->success()->important();
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $error = '';
        if (strpos($exception->getMessage(), 'Connection timed out') !== false) {
            $error = 'Url check failed. Connection timed out!';
        } elseif (strpos($exception->getMessage(), 'Could not resolve host') !== false) {
            $error = 'Url check failed. Site does not exist!';
        } else {
            $error = 'Url check failed for an unknown reason.';
        }
        DB::table(UrlCheckController::getTableName())
            ->where('id', $this->urlCheckId)
            ->update(['status' => 'failed', 'error_msg' => $error]);
        $urlCheck = DB::table(UrlCheckController::getTableName())->find($this->urlCheckId);
        if ($urlCheck !== null) {
            flash($error)->success()->important();
        }
    }
}
