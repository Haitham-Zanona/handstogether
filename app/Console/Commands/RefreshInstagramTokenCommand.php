<?php

namespace App\Console\Commands;

use App\Services\SocialMediaService;
use Illuminate\Console\Command;

class RefreshInstagramTokenCommand extends Command
{
    protected $signature   = 'social:refresh-instagram-token';
    protected $description = 'Refresh the Instagram long-lived access token before it expires (runs monthly via scheduler)';

    public function handle(SocialMediaService $service): int
    {
        $this->info('Refreshing Instagram long-lived access token...');

        if ($service->refreshInstagramToken()) {
            $this->info('Token refreshed and cached successfully.');
            return Command::SUCCESS;
        }

        $this->error('Token refresh failed. Check laravel.log for details.');
        return Command::FAILURE;
    }
}
