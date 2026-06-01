@props(['posts' => []])

@if(!empty($posts))
{{-- =====================================================================
     Social Media Feed Section
     ===================================================================== --}}
<section id="social-feed" class="py-20 bg-white overflow-hidden" dir="rtl">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- ── Section Header ─────────────────────────────────────────── --}}
        <div class="mb-12 text-center">
            {{-- Live badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-5 rounded-full text-sm font-medium"
                 style="background-color:#2778E5; color:#fff; opacity:0.9;">
                <span class="inline-block w-2 h-2 rounded-full bg-white" style="animation:sfPulse 1.6s ease-in-out infinite;"></span>
                آخر منشوراتنا
            </div>

            <h2 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl">
                تابعونا على منصات التواصل
            </h2>
            <p class="max-w-2xl mx-auto mb-8 text-lg leading-relaxed text-gray-500">
                آخر فيديوهاتنا ومنشوراتنا على
                <span style="color:#dc2743; font-weight:600;">Instagram</span>
                و
                <span class="font-semibold text-gray-900">TikTok</span>
                — مباشرة من أرض الواقع
            </p>

            {{-- Platform follow buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-3">
                @if(config('social.instagram.username'))
                <a href="https://www.instagram.com/{{ config('social.instagram.username') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-full shadow-md transition-all duration-200 hover:scale-105 hover:shadow-lg"
                   style="background:linear-gradient(135deg,#f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                    تابعونا على Instagram
                </a>
                @endif

                @if(config('social.tiktok.username'))
                <a href="https://www.tiktok.com/@{{ config('social.tiktok.username') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-full bg-gray-900 shadow-md transition-all duration-200 hover:scale-105 hover:shadow-lg hover:bg-black">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.19 8.19 0 004.79 1.54V6.77a4.85 4.85 0 01-1.02-.08z"/>
                    </svg>
                    تابعونا على TikTok
                </a>
                @endif
            </div>
        </div>

        {{-- ── Posts Grid ─────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5">
            @foreach($posts as $index => $post)
            <article class="sf-card group relative overflow-hidden rounded-2xl cursor-pointer bg-gray-900 shadow-lg
                            transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl"
                     data-embed-url="{{ $post['embed_url'] }}"
                     data-platform="{{ $post['platform'] }}"
                     data-caption="{{ $post['caption'] }}"
                     data-permalink="{{ $post['permalink'] }}"
                     tabindex="0"
                     role="button"
                     aria-label="مشاهدة {{ $post['platform'] === 'instagram' ? 'منشور Instagram' : 'فيديو TikTok' }}">

                {{-- Aspect-ratio wrapper --}}
                <div class="relative" style="aspect-ratio:4/5; overflow:hidden;">

                    {{-- Thumbnail --}}
                    @if(!empty($post['thumbnail']))
                    <img src="{{ $post['thumbnail'] }}"
                         alt="{{ $post['caption'] ? Str::limit($post['caption'], 60) : 'منشور من ' . $post['platform'] }}"
                         loading="{{ $index < 4 ? 'eager' : 'lazy' }}"
                         decoding="async"
                         class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110"
                         onerror="this.parentElement.classList.add('sf-thumb-error')">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900">
                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" stroke-width="1">
                            <rect x="2" y="2" width="20" height="20" rx="3"/>
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                    @endif

                    {{-- Glassmorphism hover overlay --}}
                    <div class="sf-overlay absolute inset-0 flex flex-col items-center justify-center
                                bg-black/0 group-hover:bg-black/55 transition-all duration-300"
                         aria-hidden="true">

                        {{-- Play button --}}
                        <div class="sf-play-btn w-16 h-16 rounded-full border-2 border-white/70 bg-white/15
                                    backdrop-blur-sm flex items-center justify-center
                                    opacity-0 group-hover:opacity-100
                                    scale-75 group-hover:scale-100
                                    transition-all duration-300">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>

                        <span class="mt-3 text-white text-xs font-medium tracking-wide
                                     opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-75">
                            مشاهدة الآن
                        </span>
                    </div>

                    {{-- Platform badge (top corner, RTL = top-left visually) --}}
                    <div class="absolute top-2.5 left-2.5" aria-hidden="true">
                        @if($post['platform'] === 'instagram')
                        <div class="flex items-center gap-1 px-2 py-1 rounded-full text-white text-[10px] font-semibold shadow-lg backdrop-blur-sm"
                             style="background:linear-gradient(135deg,#f09433,#dc2743,#bc1888);">
                            <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                            Instagram
                        </div>
                        @else
                        <div class="flex items-center gap-1 px-2 py-1 rounded-full text-white text-[10px] font-semibold shadow-lg backdrop-blur-sm bg-gray-900/80">
                            <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.19 8.19 0 004.79 1.54V6.77a4.85 4.85 0 01-1.02-.08z"/>
                            </svg>
                            TikTok
                        </div>
                        @endif
                    </div>

                    {{-- Caption slide-up --}}
                    @if(!empty($post['caption']))
                    <div class="sf-caption absolute bottom-0 left-0 right-0 p-3
                                bg-gradient-to-t from-black/85 via-black/40 to-transparent
                                translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"
                         aria-hidden="true">
                        <p class="text-white text-[11px] leading-relaxed line-clamp-2">
                            {{ $post['caption'] }}
                        </p>
                    </div>
                    @endif

                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>

{{-- =====================================================================
     Lightbox Modal
     ===================================================================== --}}
<div id="sf-modal"
     class="fixed inset-0 z-[9999] hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="sf-modal-title">

    {{-- Backdrop --}}
    <div id="sf-backdrop"
         class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    </div>

    {{-- Scroll container --}}
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">

        {{-- Modal card --}}
        <div id="sf-modal-card"
             class="relative w-full max-w-sm bg-gray-950 rounded-2xl overflow-hidden shadow-2xl
                    transition-all duration-300 opacity-0 scale-90">

            {{-- Header --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-800">
                <div id="sf-modal-badge" aria-hidden="true"></div>
                <p id="sf-modal-title"
                   class="flex-1 text-gray-300 text-sm leading-snug line-clamp-1 min-w-0 text-right">
                </p>
                <a id="sf-modal-link"
                   href="#"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="shrink-0 flex items-center gap-1 text-gray-500 hover:text-white text-xs transition-colors"
                   title="فتح في التطبيق">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    فتح
                </a>
                <button id="sf-modal-close"
                        class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-800 text-gray-400
                               hover:bg-gray-700 hover:text-white transition-colors"
                        aria-label="إغلاق">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Embed area --}}
            <div id="sf-modal-embed"
                 class="relative flex items-center justify-center bg-black min-h-[400px]">

                {{-- Loading spinner (shown until iframe fires load event) --}}
                <div id="sf-spinner" class="absolute inset-0 flex flex-col items-center justify-center gap-3" aria-hidden="true">
                    <div class="w-10 h-10 border-4 border-gray-700 border-t-blue-500 rounded-full"
                         style="animation:sfSpin 0.9s linear infinite;"></div>
                    <span class="text-gray-500 text-xs">جارٍ التحميل...</span>
                </div>

                {{-- iframe injected by JS on card click --}}
            </div>

        </div>
    </div>
</div>

{{-- Minimal CSS for animations + error state --}}
@push('styles')
<style>
@keyframes sfPulse {
    0%,100% { opacity:1; }
    50%      { opacity:.35; }
}
@keyframes sfSpin {
    to { transform:rotate(360deg); }
}
.sf-thumb-error {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

{{-- Vanilla JS for modal open / close / iframe inject --}}
@push('scripts')
<script>
(function () {
    'use strict';

    /* ── DOM refs ─────────────────────────────────────────────────────── */
    const modal      = document.getElementById('sf-modal');
    const backdrop   = document.getElementById('sf-backdrop');
    const card       = document.getElementById('sf-modal-card');
    const embed      = document.getElementById('sf-modal-embed');
    const spinner    = document.getElementById('sf-spinner');
    const closeBtn   = document.getElementById('sf-modal-close');
    const badge      = document.getElementById('sf-modal-badge');
    const titleEl    = document.getElementById('sf-modal-title');
    const linkEl     = document.getElementById('sf-modal-link');

    /* ── Platform badge HTML ──────────────────────────────────────────── */
    const BADGES = {
        instagram:
            '<div style="background:linear-gradient(135deg,#f09433,#dc2743,#bc1888);padding:3px 8px;border-radius:9999px;display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#fff;">' +
            '<svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>' +
            'Instagram</div>',

        tiktok:
            '<div style="background:#111827;padding:3px 8px;border-radius:9999px;display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#fff;">' +
            '<svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.19 8.19 0 004.79 1.54V6.77a4.85 4.85 0 01-1.02-.08z"/></svg>' +
            'TikTok</div>',
    };

    /* ── Build iframe HTML ────────────────────────────────────────────── */
    function buildIframe(embedUrl, platform) {
        const isTikTok = platform === 'tiktok';

        const attrs = [
            'src="' + embedUrl + '"',
            isTikTok ? 'width="325" height="580"' : 'width="100%" height="480"',
            'frameborder="0"',
            'scrolling="no"',
            'allowtransparency="true"',
            'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"',
            'allowfullscreen',
            'id="sf-iframe"',
            'style="display:block;' + (isTikTok ? 'margin:0 auto;' : 'width:100%;') + '"',
        ].join(' ');

        return '<iframe ' + attrs + '></iframe>';
    }

    /* ── Open modal ───────────────────────────────────────────────────── */
    function openModal(el) {
        const embedUrl  = el.dataset.embedUrl;
        const platform  = el.dataset.platform;
        const caption   = el.dataset.caption  || '';
        const permalink = el.dataset.permalink || '#';

        if (!embedUrl) return;

        // Fill header
        badge.innerHTML  = BADGES[platform] || '';
        titleEl.textContent = caption;
        linkEl.href      = permalink;

        // Show spinner, inject iframe
        spinner.style.display = 'flex';
        embed.innerHTML = spinner.outerHTML + buildIframe(embedUrl, platform);

        // Hide spinner once iframe loads
        const iframe = embed.querySelector('#sf-iframe');
        if (iframe) {
            iframe.addEventListener('load', function onLoad() {
                const s = embed.querySelector('#sf-spinner');
                if (s) s.style.display = 'none';
                iframe.removeEventListener('load', onLoad);
            });
        }

        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Animate in (double rAF ensures transition fires after display:block)
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                backdrop.style.opacity = '1';
                card.style.opacity     = '1';
                card.style.transform   = 'scale(1)';
            });
        });
    }

    /* ── Close modal ──────────────────────────────────────────────────── */
    function closeModal() {
        backdrop.style.opacity = '0';
        card.style.opacity     = '0';
        card.style.transform   = 'scale(0.9)';

        setTimeout(function () {
            modal.classList.add('hidden');
            embed.innerHTML          = '';
            document.body.style.overflow = '';
        }, 280);
    }

    /* ── Wire up card clicks ──────────────────────────────────────────── */
    document.querySelectorAll('.sf-card').forEach(function (el) {
        el.addEventListener('click', function () { openModal(el); });
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal(el);
            }
        });
    });

    /* ── Close triggers ───────────────────────────────────────────────── */
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>
@endpush

@endif
