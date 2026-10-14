@extends('layouts.admin')

@section('title', 'Maintenance')
@section('page-title', 'System Maintenance')

@php
    /** @var array $overview */
    $fmtBytes = function ($bytes) {
        if ($bytes === null) return '—';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 1) . ' ' . $units[$i];
    };
    $chip = fn ($ok) => $ok
        ? 'bg-green-50 text-green-700 border-green-200'
        : 'bg-amber-50 text-amber-800 border-amber-200';
@endphp

@section('content')
<div class="space-y-6">

    {{-- Overview --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['Database', $overview['database_ok'] ? 'Connected' : 'Unreachable', $overview['database_ok'], 'ph-database'],
            ['Pending migrations', $overview['pending_migrations'] ?? '—', ($overview['pending_migrations'] ?? 0) === 0, 'ph-stack'],
            ['Site status', $overview['down'] ? 'Maintenance mode' : 'Live', !$overview['down'], 'ph-globe'],
            ['Last backup', $overview['last_backup'] ? \Carbon\Carbon::createFromTimestamp($overview['last_backup']['created_at'])->diffForHumans() : 'Never', (bool) $overview['last_backup'], 'ph-archive'],
            ['Failed jobs', $overview['failed_jobs'] ?? '—', ($overview['failed_jobs'] ?? 0) === 0, 'ph-warning-circle'],
            ['Calls (Agora)', $overview['agora_configured'] ? 'Configured' : 'Not configured', $overview['agora_configured'], 'ph-phone'],
            ['Storage link', $overview['storage_linked'] ? 'Linked' : 'Missing', $overview['storage_linked'], 'ph-link'],
            ['Disk free', $fmtBytes($overview['disk_free']), true, 'ph-hard-drives'],
        ] as [$label, $value, $ok, $icon])
            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="ph {{ $icon }} text-base"></i>{{ $label }}
                </div>
                <div class="mt-2 inline-flex items-center px-2.5 py-1 rounded-lg border text-sm font-semibold {{ $chip($ok) }}">
                    {{ $value }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2 text-xs text-gray-500">
        <span class="px-2 py-1 bg-gray-100 rounded-lg">PHP {{ $overview['php'] }}</span>
        <span class="px-2 py-1 bg-gray-100 rounded-lg">Laravel {{ $overview['laravel'] }}</span>
        <span class="px-2 py-1 bg-gray-100 rounded-lg">env: {{ $overview['environment'] }}</span>
        <span class="px-2 py-1 bg-gray-100 rounded-lg">cache: {{ $overview['cache_driver'] }}</span>
        <span class="px-2 py-1 bg-gray-100 rounded-lg">queue: {{ $overview['queue_driver'] }}</span>
        @if ($overview['debug'] && $overview['environment'] === 'production')
            <span class="px-2 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg">APP_DEBUG is on in production — turn it off in .env, it shows error details to users</span>
        @endif
        @if ($overview['config_cached'])
            <span class="px-2 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg">Config is cached — this breaks SMS, push and calls here. Use “Clear all caches”.</span>
        @endif
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Migrations --}}
        <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm xl:col-span-2">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Database migrations</h2>
                    <p class="text-sm text-gray-500">“Run pending” takes a backup first and stops if the backup fails. Each migration runs as its own step, so a rollback of 1 undoes exactly one.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button class="mt-btn" data-action="pretend"><i class="ph ph-eye"></i>Preview SQL</button>
                    <button class="mt-btn mt-primary" data-action="migrate"><i class="ph ph-play"></i>Run pending (safe)</button>
                    <button class="mt-btn mt-warn" data-action="force"><i class="ph ph-lightning"></i>Force run (no backup)</button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-4 p-3 bg-gray-50 rounded-xl">
                <span class="text-sm text-gray-700">Roll back the last</span>
                <select id="rollbackSteps" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm">
                    @foreach ([1, 2, 3, 5, 10] as $n)
                        <option value="{{ $n }}">{{ $n }} migration{{ $n > 1 ? 's' : '' }}</option>
                    @endforeach
                </select>
                <button class="mt-btn mt-danger" data-action="rollback"><i class="ph ph-arrow-counter-clockwise"></i>Roll back…</button>
                <span class="text-xs text-gray-500">A backup is taken first. You’ll see exactly what will be undone before confirming.</span>
            </div>

            <div class="overflow-auto max-h-80 border border-gray-100 rounded-xl">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 sticky top-0">
                        <tr><th class="text-left px-3 py-2">Migration</th><th class="text-left px-3 py-2 w-28">Status</th><th class="text-left px-3 py-2 w-20">Batch</th></tr>
                    </thead>
                    <tbody id="migrationRows"><tr><td colspan="3" class="px-3 py-4 text-gray-400">Loading…</td></tr></tbody>
                </table>
            </div>
        </section>

        {{-- Caches --}}
        <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900">Caches</h2>
            <p class="text-sm text-gray-500 mb-4">Clear after deploying new code or changing .env. “Optimize” caches routes, views and events — never config, which this app can’t run with.</p>
            <div class="flex flex-wrap gap-2">
                <button class="mt-btn mt-primary" data-action="cache" data-cache="all"><i class="ph ph-broom"></i>Clear all caches</button>
                @foreach ($caches as $cache)
                    <button class="mt-btn" data-action="cache" data-cache="{{ $cache }}">{{ ucfirst($cache) }}</button>
                @endforeach
                <button class="mt-btn" data-action="optimize"><i class="ph ph-rocket-launch"></i>Optimize (safe)</button>
            </div>
        </section>

        {{-- Queue, storage, maintenance mode --}}
        <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900">Queue, storage & site status</h2>
            <p class="text-sm text-gray-500 mb-4">
                {{ $overview['queued_jobs'] ?? 0 }} job(s) waiting · {{ $overview['failed_jobs'] ?? 0 }} failed
            </p>
            <div class="flex flex-wrap gap-2">
                <button class="mt-btn" data-action="queue-retry"><i class="ph ph-arrows-clockwise"></i>Retry failed jobs</button>
                <button class="mt-btn" data-action="queue-restart"><i class="ph ph-power"></i>Restart queue workers</button>
                <button class="mt-btn" data-action="storage-link"><i class="ph ph-link"></i>Link storage</button>
                @if ($overview['down'])
                    <button class="mt-btn mt-primary" data-action="up"><i class="ph ph-globe"></i>Bring site back up</button>
                @else
                    <button class="mt-btn mt-danger" data-action="down"><i class="ph ph-traffic-cone"></i>Maintenance mode…</button>
                @endif
            </div>
        </section>

        {{-- Backups --}}
        <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Backups</h2>
                    <p class="text-sm text-gray-500">Full SQL dumps, kept privately in storage/app/backups.</p>
                </div>
                <button class="mt-btn mt-primary" data-action="backup"><i class="ph ph-download-simple"></i>Back up now</button>
            </div>
            <ul id="backupList" class="divide-y divide-gray-100 text-sm max-h-72 overflow-auto"></ul>
        </section>

        {{-- Log --}}
        <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Application log</h2>
                    <p class="text-sm text-gray-500">laravel.log · {{ $fmtBytes($overview['log_size']) }}</p>
                </div>
                <div class="flex gap-2">
                    <button class="mt-btn" data-action="log"><i class="ph ph-file-text"></i>Show last 200 lines</button>
                    <button class="mt-btn mt-danger" data-action="log-clear"><i class="ph ph-trash"></i>Clear…</button>
                </div>
            </div>
        </section>
    </div>

    {{-- Console --}}
    <section class="bg-gray-900 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-800">
            <span class="text-xs font-medium text-gray-400">Output</span>
            <button id="consoleClear" class="text-xs text-gray-400 hover:text-white">Clear</button>
        </div>
        <pre id="console" class="text-xs text-gray-100 p-4 max-h-96 overflow-auto whitespace-pre-wrap">Ready.</pre>
    </section>
</div>

{{-- Typed confirmation --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 id="confirmTitle" class="font-semibold text-gray-900"></h3>
        <div id="confirmBody" class="mt-2 text-sm text-gray-600"></div>
        <label class="block mt-4 text-sm text-gray-700">Type <strong id="confirmWord"></strong> to continue</label>
        <input id="confirmInput" type="text" autocomplete="off" class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-200">
        <div class="mt-5 flex justify-end gap-2">
            <button id="confirmCancel" class="mt-btn">Cancel</button>
            <button id="confirmOk" class="mt-btn mt-danger" disabled>Confirm</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mt-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem .85rem; border-radius:.75rem; font-size:.85rem; font-weight:500; border:1px solid #e5e7eb; background:#fff; color:#374151; transition:all .15s; }
    .mt-btn:hover:not(:disabled) { border-color:#15803d; color:#15803d; }
    .mt-btn:disabled { opacity:.5; cursor:not-allowed; }
    .mt-primary { background:#15803d; border-color:#15803d; color:#fff; }
    .mt-primary:hover:not(:disabled) { background:#166534; color:#fff; }
    .mt-warn { border-color:#fcd34d; color:#92400e; background:#fffbeb; }
    .mt-danger { border-color:#fecaca; color:#b91c1c; background:#fef2f2; }
    .mt-danger:hover:not(:disabled) { border-color:#b91c1c; color:#b91c1c; }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const base = @json(url('admin/settings/maintenance'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const out = document.getElementById('console');
    let busy = false;

    const write = (text, ok = true) => {
        const stamp = new Date().toLocaleTimeString();
        out.textContent = `[${stamp}] ${ok ? '' : 'ERROR: '}${text}\n\n` + out.textContent;
    };
    document.getElementById('consoleClear').onclick = () => out.textContent = '';

    async function call(method, path, body) {
        const res = await fetch(base + path, {
            method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: body ? JSON.stringify(body) : undefined,
        });
        let data = {};
        try { data = await res.json(); } catch (_) { data = { success: false, message: `HTTP ${res.status}` }; }
        return data;
    }

    async function run(label, method, path, body, { reload = false } = {}) {
        if (busy) return;
        busy = true;
        document.querySelectorAll('[data-action]').forEach(b => b.disabled = true);
        write(`${label}…`);
        try {
            const data = await call(method, path, body);
            const text = [data.message, data.output].filter(Boolean).join('\n');
            write(text || (data.success ? 'Done.' : 'Failed.'), data.success);
            if (data.bypass_url) {
                write('Opening your bypass link so you stay signed in while the site is down…');
                window.location.href = data.bypass_url;
                return data;
            }
            if (data.success && reload) setTimeout(() => location.reload(), 900);
            return data;
        } catch (e) {
            write(e.message, false);
        } finally {
            busy = false;
            document.querySelectorAll('[data-action]').forEach(b => b.disabled = false);
            loadMigrations();
            loadBackups();
        }
    }

    // Typed confirmation, resolved with true only for the exact word.
    function confirmTyped(title, bodyHtml, word) {
        const modal = document.getElementById('confirmModal');
        const input = document.getElementById('confirmInput');
        const ok = document.getElementById('confirmOk');
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmBody').innerHTML = bodyHtml;
        document.getElementById('confirmWord').textContent = word;
        input.value = '';
        ok.disabled = true;
        modal.classList.remove('hidden');
        input.focus();
        return new Promise(resolve => {
            input.oninput = () => ok.disabled = input.value.trim().toUpperCase() !== word;
            const close = (v) => { modal.classList.add('hidden'); resolve(v); };
            ok.onclick = () => close(true);
            document.getElementById('confirmCancel').onclick = () => close(false);
        });
    }

    const escapeHtml = s => s.replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

    async function loadMigrations() {
        const data = await call('GET', '/migrations');
        const rows = document.getElementById('migrationRows');
        if (!data.success) { rows.innerHTML = '<tr><td colspan="3" class="px-3 py-4 text-red-600">Could not read migrations.</td></tr>'; return; }
        // Pending first, then the most recent.
        const list = [...data.migrations].reverse().sort((a, b) => a.ran - b.ran);
        rows.innerHTML = list.map(m => `
            <tr class="border-t border-gray-100 ${m.ran ? '' : 'bg-amber-50'}">
                <td class="px-3 py-1.5 font-mono text-xs">${escapeHtml(m.name)}</td>
                <td class="px-3 py-1.5">${m.ran ? '<span class="text-green-700">Ran</span>' : '<span class="text-amber-700 font-semibold">Pending</span>'}</td>
                <td class="px-3 py-1.5 text-gray-500">${m.batch ?? '—'}</td>
            </tr>`).join('');
    }

    async function loadBackups() {
        const data = await call('GET', '/overview');
        const list = document.getElementById('backupList');
        if (!data.success) return;
        if (!data.backups.length) { list.innerHTML = '<li class="py-3 text-gray-400">No backups yet.</li>'; return; }
        list.innerHTML = data.backups.map(b => `
            <li class="py-2 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="font-mono text-xs truncate">${escapeHtml(b.filename)}</div>
                    <div class="text-xs text-gray-500">${new Date(b.created_at * 1000).toLocaleString()} · ${(b.size / 1048576).toFixed(2)} MB</div>
                </div>
                <div class="flex gap-1 shrink-0">
                    <a class="mt-btn" href="${base}/backups/${encodeURIComponent(b.filename)}"><i class="ph ph-download-simple"></i></a>
                    <button class="mt-btn mt-danger" data-delete="${escapeHtml(b.filename)}"><i class="ph ph-trash"></i></button>
                </div>
            </li>`).join('');
        list.querySelectorAll('[data-delete]').forEach(btn => btn.onclick = async () => {
            const name = btn.dataset.delete;
            if (await confirmTyped('Delete backup?', `<span class="font-mono text-xs">${escapeHtml(name)}</span> will be removed permanently.`, 'DELETE')) {
                run('Deleting backup', 'DELETE', '/backups/' + encodeURIComponent(name), { confirm: 'DELETE' });
            }
        });
    }

    const actions = {
        pretend: () => run('Previewing migration SQL', 'POST', '/migrations/pretend'),
        migrate: () => run('Backing up, then running pending migrations', 'POST', '/migrations/run', null, { reload: true }),
        force: async () => {
            if (await confirmTyped('Run migrations without a backup?',
                'Use this only if the automatic backup keeps failing. If a migration goes wrong there will be no backup to restore.', 'MIGRATE')) {
                run('Running migrations (no backup)', 'POST', '/migrations/force', { confirm: 'MIGRATE' }, { reload: true });
            }
        },
        rollback: async () => {
            const steps = document.getElementById('rollbackSteps').value;
            const preview = await call('GET', '/migrations/rollback-preview?steps=' + steps);
            if (!preview.success || !preview.migrations.length) { write('Nothing to roll back.', false); return; }
            const list = preview.migrations.map(m => `<li class="font-mono text-xs">${escapeHtml(m)}</li>`).join('');
            if (await confirmTyped('Roll back these migrations?',
                `A backup is taken first. These will be undone, newest first — any data in the columns or tables they created is lost:<ul class="mt-2 list-disc list-inside">${list}</ul>`, 'ROLLBACK')) {
                run(`Rolling back ${steps} migration(s)`, 'POST', '/migrations/rollback', { confirm: 'ROLLBACK', steps: Number(steps) }, { reload: true });
            }
        },
        cache: (btn) => run(`Clearing ${btn.dataset.cache} cache`, 'POST', '/cache/clear', { cache: btn.dataset.cache }),
        optimize: () => run('Optimizing (routes, views, events)', 'POST', '/cache/optimize'),
        backup: () => run('Creating backup', 'POST', '/backups'),
        'queue-retry': () => run('Retrying failed jobs', 'POST', '/queue/retry', null, { reload: true }),
        'queue-restart': () => run('Restarting queue workers', 'POST', '/queue/restart'),
        'storage-link': () => run('Linking storage', 'POST', '/storage/link', null, { reload: true }),
        down: async () => {
            if (await confirmTyped('Put the site into maintenance mode?',
                'The mobile app and the API return “Service unavailable” to everyone until you bring it back up. This browser keeps access through a private link.', 'DOWN')) {
                run('Entering maintenance mode', 'POST', '/down', { confirm: 'DOWN' });
            }
        },
        up: () => run('Bringing the site back up', 'POST', '/up', null, { reload: true }),
        log: async () => {
            const data = await call('GET', '/log');
            write(data.output || '(log is empty)', data.success);
        },
        'log-clear': async () => {
            if (await confirmTyped('Clear the application log?', 'The current laravel.log is emptied.', 'CLEAR')) {
                run('Clearing log', 'POST', '/log/clear', { confirm: 'CLEAR' }, { reload: true });
            }
        },
    };

    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', () => actions[btn.dataset.action]?.(btn));
    });

    loadMigrations();
    loadBackups();
})();
</script>
@endpush
