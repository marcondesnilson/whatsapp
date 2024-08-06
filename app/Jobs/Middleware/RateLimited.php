<?php
// app/Jobs/Middleware/RateLimited.php
namespace App\Jobs\Middleware;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Routing\Middleware\ThrottleRequests;

class RateLimited
{
    public function handle($job, $next)
    {
        return (new 'ThrottleRequests'($limiter))->handle($job, $next); // Limita a 10 requisições por minuto
    }
}
