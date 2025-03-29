<?php

namespace App\Rest\Controllers;

use Illuminate\Support\Facades\DB;
use App\Rest\Resources\JobResource;
use App\Models\Job;
use Illuminate\Support\Facades\Bus;
use App\Rest\Controller as RestController;

class JobController extends RestController
{
    // Show all failed jobs
    public function showFailedJobs()
    {
        // Fetch jobs that have failed (attempts > 1 and are not yet reserved)
        $failedJobs = Job::where('attempts', '>', 1)
                         ->whereNull('reserved_at') // Jobs that are not currently being processed
                         ->get();

        // Return the failed jobs as a resource collection
        return JobResource::collection($failedJobs);
    }
    // Retry failed job
   
    public function retryFailedJob($jobId)
    {
        // Fetch the failed job
        $job = Job::find($jobId);

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        // Check if the job has already exceeded the maximum retry limit
        if ($job->attempts >= 5) {
            return response()->json(['message' => 'Job has exceeded maximum retry attempts'], 400);
        }

        // Reset attempts and reserved_at
        $job->attempts = 0;
        $job->reserved_at = null;

        // Dispatch the job again for processing
        try {
            // Assuming the job class is the same as the job type (you might need to adjust this depending on your actual job class)
            $jobData = json_decode($job->payload, true);
            $jobClass = $jobData['data']['commandName'];

            $jobInstance = new $jobClass(...$jobData['data']['command']); // Rebuild job instance from payload

            // Dispatch the job again to the queue
            Bus::dispatch($jobInstance);

            // Optionally, update the job status
            $job->save();

            return response()->json(['message' => 'Job retried successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retry the job', 'error' => $e->getMessage()], 500);
        }
    }
}
