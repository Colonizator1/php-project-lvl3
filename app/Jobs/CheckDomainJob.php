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
use App\Http\Controllers\DomainCheckController;
use App\Events\DomainCheckUpdated;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckDomainJob implements ShouldQueue
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

    public $domainName;
    public $domainCheckId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $domainName, int $domainCheckId)
    {
        $this->domainName = $domainName;
        $this->domainCheckId = $domainCheckId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $domainResponse = Http::timeout(10)->get($this->domainName);
        $dom = new Document($domainResponse->body());

        if ($dom->has('meta[name=keywords]')) {
            Log::info(dump($dom->find('meta[name=keywords]')));
            $data['keywords'] = $dom->find('meta[name=keywords]')[0]->getAttribute('content');
        }
        if ($dom->has('meta[name=description]')) {
            Log::info(dump($dom->find('meta[name=description]')));
            $data['description'] = $dom->find('meta[name=description]')[0]->getAttribute('content');
        }
        if ($dom->has('h1')) {
            Log::info('has h1');
            Log::info(dump($dom->find('h1')));
            $data['h1'] = $dom->find('h1')[0]->text();
        }

        $data['status_code'] = $domainResponse->status();
        $currentTime = Carbon::now()->toString();
        $data['updated_at'] = $currentTime;
        $data['status'] = 'success';

        DB::table(DomainCheckController::getTableName())
            ->where('id', $this->domainCheckId)
            ->update($data);
        $domainCheck = DB::table(DomainCheckController::getTableName())->find($this->domainCheckId);
        if ($domainCheck) {
            DomainCheckUpdated::dispatch($domainCheck);
            flash('Domain checked successfully!')->success()->important();
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
        Log::info(\dump($exception));
        DB::table(DomainCheckController::getTableName())
            ->where('id', $this->domainCheckId)
            ->update(['status' => 'failed']);
        $domainCheck = DB::table(DomainCheckController::getTableName())->find($this->domainCheckId);
        if ($domainCheck) {
            DomainCheckUpdated::dispatch($domainCheck, $exception);
        }
    }
}
