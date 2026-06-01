<?php

namespace App\Console\Commands;

use App\Services\SocialMediaService;
use Illuminate\Console\Command;

class FetchSocialMediaPostsCommand extends Command
{
    protected $signature   = 'social:fetch-posts';
    protected $description = 'Fetch latest posts from Instagram and TikTok and store them in cache';

    public function handle(SocialMediaService $service): int
    {
        $this->info('Fetching social media posts...');

        $service->fetchAndCachePosts();

        $count = count($service->getCachedPosts());

        if ($count > 0) {
            $this->info("Done. {$count} posts cached successfully.");
            return Command::SUCCESS;
        }

        $this->warn('No posts were cached. Check that INSTAGRAM_ACCESS_TOKEN and TIKTOK_ACCESS_TOKEN are set in .env and valid.');
        return Command::FAILURE;
    }
}
