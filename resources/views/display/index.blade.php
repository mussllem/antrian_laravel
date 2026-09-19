@extends('layouts.app')
@section('title', 'Layar Antrian')

@push('head')
    <meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('reverb.servers.reverb.hostname', request()->getHost()) }}">
    <meta name="reverb-port" content="{{ config('reverb.servers.reverb.port', 8080) }}">
    <meta name="reverb-scheme" content="{{ config('reverb.servers.reverb.scheme', 'http') }}">
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <style>
        .display-head{display:flex;justify-content:space-between;align-items:center;padding:16px 28px;background:var(--topbar-bg);border-bottom:1px solid var(--border)}
        .display-head h1{margin:0;font-size:28px}
        .now-serving{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;padding:24px}
        .loket-card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:22px;text-align:center}
        .loket-card.flash{animation:flash 1s ease-in-out 3}
        @keyframes flash{0%,100%{background:var(--card)}50%{background:var(--highlight)}}
        .loket-name{color:var(--muted);font-size:18px}
        .loket-code{font-size:64px;font-weight:800;color:var(--accent)}
        .loket-service{font-size:15px;color:var(--muted)}
        .banner{padding:18px 28px;background:var(--highlight);color:var(--text);text-align:center;font-size:22px;display:none}
        .enable-audio{position:fixed;inset:0;background:var(--overlay);display:flex;align-items:center;justify-content:center;z-index:99}
        .clock{font-size:20px;color:var(--muted)}
    </style>
@endpush

@section('body')
<div class="enable-audio" id="enableAudio">
    <button class="btn green" style="font-size:22px;padding:22px 34px" onclick="enableAudio()">
        ▶ Klik untuk mengaktifkan suara
    </button>
</div>

<div class="display-head">
    <h1>ANTRIAN</h1>
    <div class="clock" id="clock"></div>
</div>

<div class="banner" id="banner"></div>

<div class="now-serving" id="grid">
    <!-- diisi via JS -->
</div>
@endsection

@push('scripts')
<script src="/js/queue-audio.js"></script>
<script src="/js/echo-setup.js"></script>
<script>
const player = new QueueAudio('/audio');
let audioEnabled = false;

function enableAudio(){
    audioEnabled = true;
    document.getElementById('enableAudio').style.display = 'none';
    // "unlock" audio di browser dgn memutar bell tanpa suara
    player.announce({ code_digits: [], counter_number: null });
}

function tickClock(){
    const d = new Date();
    document.getElementById('clock').textContent =
        d.toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric'})
        + '  ' + d.toLocaleTimeString('id-ID');
}
setInterval(tickClock, 1000); tickClock();

async function refresh(){
    const res = await fetch(@json(route('display.state')));
    const data = await res.json();
    const grid = document.getElementById('grid');
    if (!data.active.length){
        grid.innerHTML = '<div class="loket-card"><div class="loket-name">Belum ada panggilan</div></div>';
        return;
    }
    grid.innerHTML = data.active.map(a => `
        <div class="loket-card" data-counter="${a.counter_number}">
            <div class="loket-name">${a.counter_name ?? ('Loket '+a.counter_number)}</div>
            <div class="loket-code">${a.code}</div>
            <div class="loket-service">${a.service ?? ''}</div>
        </div>`).join('');
}
refresh();

function showBanner(code, counterName){
    const b = document.getElementById('banner');
    b.textContent = `Nomor ${code}, silakan menuju ${counterName}`;
    b.style.display = 'block';
    setTimeout(() => { b.style.display = 'none'; }, 8000);
}

// ---- Realtime via Reverb ----
const echo = window.setupEcho();
if (echo){
    echo.channel('queue')
        .listen('.ticket.called', (e) => {
            refresh();
            showBanner(e.code, e.counter_name ?? ('Loket '+e.counter_number));
            const card = document.querySelector(`[data-counter="${e.counter_number}"]`);
            if (card){ card.classList.add('flash'); setTimeout(()=>card.classList.remove('flash'),3000); }
            if (audioEnabled && e.audio){
                player.announce(e.audio);
            }
        })
        .listen('.queue.updated', () => refresh());
} else {
    // Fallback polling bila WebSocket tidak tersedia.
    setInterval(refresh, 3000);
}
</script>
@endpush
