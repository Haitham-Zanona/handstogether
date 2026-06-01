<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialMediaService
{
    private const POSTS_CACHE_KEY = 'social_media_posts';
    private const TOKEN_CACHE_KEY = 'instagram_access_token_refreshed';

    public function getCachedPosts(): array
    {
        return Cache::get(self::POSTS_CACHE_KEY, []);
    }

    public function fetchAndCachePosts(): void
    {
        $instagram = $this->fetchInstagramPosts();
        $tiktok    = $this->fetchTikTokPosts();

        // Interleave posts: IG, TT, IG, TT... for balanced visual grid
        $posts = $this->interleave($instagram, $tiktok);

        // Only update cache if we got at least something; don't wipe a good cache on partial failure
        if (!empty($posts)) {
            Cache::put(
                self::POSTS_CACHE_KEY,
                $posts,
                now()->addHours(config('social.cache_ttl_hours', 2))
            );
        }
    }

    /**
     * Refresh the Instagram long-lived token (valid 60 days) before it expires.
     * Stores the refreshed token in cache so the service picks it up automatically.
     */
    public function refreshInstagramToken(): bool
    {
        $token = $this->resolveInstagramToken();

        if (empty($token)) {
            Log::warning('Instagram token refresh skipped: no token configured.');
            return false;
        }

        try {
            $response = Http::timeout(10)->get('https://graph.instagram.com/refresh_access_token', [
                'grant_type'   => 'ig_refresh_token',
                'access_token' => $token,
            ]);

            if ($response->successful() && $response->json('access_token')) {
                $newToken  = $response->json('access_token');
                $expiresIn = $response->json('expires_in', 5183944); // ~60 days

                // Cache for 55 days (5 days before expiry) to ensure auto-refresh catches it
                Cache::put(self::TOKEN_CACHE_KEY, $newToken, now()->addSeconds($expiresIn - 432000));
                return true;
            }

            Log::error('Instagram token refresh failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Instagram token refresh exception: ' . $e->getMessage());
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveInstagramToken(): ?string
    {
        // Prefer the auto-refreshed token stored in cache; fall back to .env value
        return Cache::get(self::TOKEN_CACHE_KEY) ?? config('social.instagram.access_token');
    }

    private function fetchInstagramPosts(): array
    {
        $token  = $this->resolveInstagramToken();
        $userId = config('social.instagram.user_id', 'me');
        $limit  = config('social.instagram.post_count', 4);

        if (empty($token)) {
            return [];
        }

        try {
            $response = Http::timeout(15)->get("https://graph.instagram.com/v21.0/{$userId}/media", [
                'fields'       => 'id,media_type,media_url,thumbnail_url,permalink,caption,timestamp',
                'limit'        => $limit,
                'access_token' => $token,
            ]);

            if ($response->failed()) {
                Log::error('Instagram API error ' . $response->status() . ': ' . $response->body());
                return [];
            }

            return collect($response->json('data', []))
                ->take($limit)
                ->map(fn($post) => $this->normaliseInstagramPost($post))
                ->filter(fn($post) => !empty($post['thumbnail']))
                ->values()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('Instagram fetch exception: ' . $e->getMessage());
            return [];
        }
    }

    private function normaliseInstagramPost(array $post): array
    {
        $permalink = $post['permalink'] ?? '';

        // Extract path type (p / reel / tv) and shortcode from permalink
        preg_match('#instagram\.com/(p|reel|tv)/([A-Za-z0-9_-]+)#', $permalink, $m);
        $pathType  = $m[1] ?? 'p';
        $shortcode = $m[2] ?? '';

        return [
            'platform'  => 'instagram',
            'id'        => $post['id'] ?? '',
            'thumbnail' => $post['thumbnail_url'] ?? $post['media_url'] ?? '',
            'permalink' => $permalink,
            'embed_url' => $shortcode ? "https://www.instagram.com/{$pathType}/{$shortcode}/embed/" : '',
            'caption'   => mb_substr($post['caption'] ?? '', 0, 150),
            'type'      => strtolower($post['media_type'] ?? 'image'),
        ];
    }

    private function fetchTikTokPosts(): array
    {
        $token = config('social.tiktok.access_token');
        $limit = config('social.tiktok.post_count', 4);

        if (empty($token)) {
            return [];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ])
                ->post(
                    'https://open.tiktokapis.com/v2/video/list/?fields=id,title,cover_image_url,embed_link,share_url',
                    ['max_count' => $limit]
                );

            if ($response->failed()) {
                Log::error('TikTok API error ' . $response->status() . ': ' . $response->body());
                return [];
            }

            return collect($response->json('data.videos', []))
                ->take($limit)
                ->map(fn($video) => $this->normaliseTikTokVideo($video))
                ->filter(fn($video) => !empty($video['thumbnail']))
                ->values()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('TikTok fetch exception: ' . $e->getMessage());
            return [];
        }
    }

    private function normaliseTikTokVideo(array $video): array
    {
        $videoId = (string) ($video['id'] ?? '');

        return [
            'platform'  => 'tiktok',
            'id'        => $videoId,
            'thumbnail' => $video['cover_image_url'] ?? '',
            'permalink' => $video['share_url'] ?? '',
            'embed_url' => $video['embed_link'] ?? ($videoId ? "https://www.tiktok.com/embed/v2/{$videoId}" : ''),
            'caption'   => mb_substr($video['title'] ?? '', 0, 150),
            'type'      => 'video',
        ];
    }

    /**
     * Interleave two arrays so platforms alternate: [A0, B0, A1, B1, ...]
     */
    private function interleave(array $a, array $b): array
    {
        $result = [];
        $max    = max(count($a), count($b));

        for ($i = 0; $i < $max; $i++) {
            if (isset($a[$i])) {
                $result[] = $a[$i];
            }
            if (isset($b[$i])) {
                $result[] = $b[$i];
            }
        }

        return $result;
    }
}
