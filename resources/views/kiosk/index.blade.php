@extends('layouts.app')
@section('title', 'Ambil Nomor Antrian')

@section('body')
<div class="wrap">
    <h1 style="text-align:center">Ambil Nomor Antrian</h1>
    <p style="text-align:center;color:var(--muted)">Silakan pilih layanan yang Anda butuhkan</p>

    <div class="row" style="justify-content:center">
        @forelse($services as $service)
            <button class="btn green take" data-id="{{ $service->id }}"
                    style="min-width:220px;padding:28px;font-size:20px;margin:8px">
                {{ $service->name }}
                <div style="font-size:13px;font-weight:400;opacity:.85;margin-top:6px">
                    {{ $service->description }}
                </div>
            </button>
        @empty
            <p>Belum ada layanan aktif. Silakan hubungi admin.</p>
        @endforelse
    </div>

    <div id="result" class="card" style="display:none;text-align:center;margin-top:24px">
        <div style="color:var(--muted)">Nomor antrian Anda</div>
        <div id="result-code" style="font-size:72px;font-weight:800;color:var(--accent)"></div>
        <div id="result-service" style="font-size:18px"></div>
        <div id="result-ahead" style="color:var(--muted);margin-top:8px"></div>
        <div style="margin-top:12px;font-size:13px;color:var(--muted)">Simpan / cetak nomor ini. Layar akan tertutup otomatis.</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

document.querySelectorAll('.take').forEach(btn => {
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        try {
            const res = await fetch(@json(route('kiosk.take')), {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                body: JSON.stringify({ service_id: btn.dataset.id }),
            });
            const data = await res.json();
            document.getElementById('result-code').textContent = data.code;
            document.getElementById('result-service').textContent = data.service;
            document.getElementById('result-ahead').textContent =
                data.ahead > 0 ? `${data.ahead} orang menunggu sebelum Anda` : 'Tidak ada antrian di depan Anda';
            document.getElementById('result').style.display = 'block';
            // Buka struk tiket di jendela baru untuk dicetak (printer termal).
            if (data.print_url) {
                window.open(data.print_url, 'cetak-tiket', 'width=380,height=560');
            }
            setTimeout(() => { document.getElementById('result').style.display='none'; }, 8000);
        } catch (e) {
            alert('Gagal mengambil nomor. Coba lagi.');
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
@endpush
