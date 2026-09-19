@extends('layouts.app')
@section('title', 'Panel ' . $counter->name)

@section('body')
<div class="wrap">
    <div class="row" style="justify-content:space-between;align-items:center">
        <h1>{{ $counter->name }} <span class="pill">No. {{ $counter->number }}</span></h1>
        <div style="display:flex;gap:8px;align-items:center">
            <span class="pill">{{ auth()->user()->name }}</span>
            <a href="{{ route('operator.index') }}" class="pill">Ganti Loket</a>
            <form method="POST" action="{{ route('operator.release', $counter) }}" style="display:inline">
                @csrf
                <button class="btn red" style="padding:6px 12px">Lepas Loket</button>
            </form>
        </div>
    </div>

    <div class="card" style="text-align:center">
        <div style="color:var(--muted)">Sedang dilayani</div>
        <div id="current-code" style="font-size:72px;font-weight:800;color:var(--accent)">—</div>
    </div>

    <div class="row">
        <button id="btn-next"   class="btn green" style="flex:2;padding:20px;font-size:18px">Panggil Berikutnya</button>
        <button id="btn-recall" class="btn"       style="flex:1;padding:20px">Panggil Ulang</button>
        <button id="btn-skip"   class="btn warn"  style="flex:1;padding:20px">Lewati</button>
        <button id="btn-finish" class="btn gray"  style="flex:1;padding:20px">Selesai</button>
    </div>

    <div class="card" style="margin-top:16px">
        <h3>Menunggu per layanan</h3>
        <table><tbody id="waiting-body"></tbody></table>
    </div>

    <div id="msg" style="margin-top:10px;color:var(--warn)"></div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const counterId = @json($counter->id);
const routes = {
    state:     @json(route('operator.state',    $counter)),
    next:      @json(route('operator.callNext', $counter)),
    recall:    @json(route('operator.recall',   $counter)),
    skip:      @json(route('operator.skip',     $counter)),
    finish:    @json(route('operator.finish',   $counter)),
    heartbeat: @json(route('operator.heartbeat',$counter)),
    index:     @json(route('operator.index')),
};
const heartbeatInterval = @json((int) config('antrian.heartbeat_interval', 60)) * 1000;

function setMsg(t){ document.getElementById('msg').textContent = t || ''; }

async function refresh(){
    const res = await fetch(routes.state);
    const data = await res.json();
    document.getElementById('current-code').textContent = data.current ? data.current.code : '—';
    document.getElementById('waiting-body').innerHTML = data.waiting.map(w =>
        `<tr><td>${w.service}</td><td style="text-align:right"><b>${w.waiting}</b> menunggu</td></tr>`
    ).join('') || '<tr><td>Tidak ada layanan terhubung</td></tr>';
}
refresh();
setInterval(refresh, 4000);

// Heartbeat: beri tahu server loket ini masih aktif agar tidak di-auto-release.
async function heartbeat(){
    try {
        const res = await fetch(routes.heartbeat, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        });
        if (res.status === 409) {
            // Loket sudah dilepas otomatis & mungkin diambil operator lain.
            setMsg('Sesi loket berakhir (idle terlalu lama). Mengarahkan ke pemilihan loket…');
            setTimeout(() => { window.location.href = routes.index; }, 1500);
        }
    } catch (e) { /* offline sesaat; coba lagi pada interval berikutnya */ }
}
heartbeat();
setInterval(heartbeat, heartbeatInterval);
// Catatan: bila tab ditutup tanpa "Lepas Loket", heartbeat berhenti dan loket
// otomatis dibebaskan setelah melewati batas idle (config antrian.loket_idle_timeout).

async function action(url, okMsg){
    setMsg('');
    const res = await fetch(url, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
    });
    const data = await res.json().catch(()=>({}));
    if (!res.ok){
        setMsg(data.message || 'Terjadi kesalahan.');
        // Sesi loket tidak valid (mis. loket direbut/dilepas) -> kembali ke pemilihan loket.
        if (res.status === 403) {
            setTimeout(() => { window.location.href = @json(route('operator.index')); }, 1500);
        }
    }
    await refresh();
    return data;
}

document.getElementById('btn-next').onclick   = () => action(routes.next);
document.getElementById('btn-recall').onclick = () => action(routes.recall);
document.getElementById('btn-skip').onclick   = () => action(routes.skip);
document.getElementById('btn-finish').onclick = () => action(routes.finish);
</script>
@endpush
