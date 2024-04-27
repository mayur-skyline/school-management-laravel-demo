<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AcceptStudentDataSharing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $payload;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = env('ASSESSMENT_DATA_SHARING_URL');
        $api_service_url = env('API_SERVICE_KEY');
        $url = "$url?access_key=$api_service_url";
        Log::info("Publish Assessments");
        Log::info("Publish URL EENV");
        Log::info(env('ASSESSMENT_DATA_SHARING_URL'));
        Log::info(json_encode($this->payload));

        $response = Http::timeout(60)->post($url, [
            'source_school_id' => $this->payload->source_school_id ?? null,
            'destination_school_id' => $this->payload->destination_school_id,
            'source_current_round' => $this->payload->current_round ?? null,
            'source_current_academic_year' => null,
            'assessment_main_id' => $this->payload->assessment_main_id ?? null,
            'period' => $this->payload->period
        ]);
        return $response;
    }
}
