<?php
namespace App\Http\Controllers;

use App\Models\Run;
use App\Services\AgentRunService;

class RunController extends Controller {
    public function __invoke(Run $run, AgentRunService $service) {
        $service->cancel($run);
        return back();
    }
}
