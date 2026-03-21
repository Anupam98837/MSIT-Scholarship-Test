@push('styles')
<style>
    /* ── Page shell ── */
    .ds-wrap{
        max-width:1100px;
        margin:16px auto 40px;
    }
    .ds-card-shell{
        border-radius:16px;
        border:1px solid var(--line-strong);
        background:var(--surface);
        box-shadow:var(--shadow-2);
        overflow:hidden;
    }

    /* ── Card header ── */
    .ds-head{
        padding:16px 18px;
        border-bottom:1px solid var(--line-strong);
        background:var(--surface);
        display:flex;
        align-items:center;
        gap:12px;
    }
    .ds-head-icon{
        width:34px; height:34px;
        border-radius:999px;
        border:1px solid var(--line-strong);
        display:flex; align-items:center; justify-content:center;
        color:var(--accent-color);
        background:var(--surface-2);
        flex-shrink:0;
    }
    .ds-head-title{
        font-family:var(--font-head);
        font-weight:700;
        color:var(--ink);
        margin:0;
        font-size:1rem;
    }
    .ds-head-sub{ color:var(--muted-color); font-size:var(--fs-13); }

    /* ── Body ── */
    .ds-body{ padding:14px 16px 16px; position:relative; }

    /* ── Skeleton loader ── */
    .ds-loader-wrap{
        position:absolute; inset:0;
        display:none;
        align-items:center; justify-content:center;
        background:rgba(0,0,0,.03);
        z-index:2;
    }
    .ds-loader-wrap.show{ display:flex; }
    .ds-loader{
        width:20px; height:20px;
        border:3px solid #0001;
        border-top-color:var(--accent-color);
        border-radius:50%;
        animation:ds-rot 1s linear infinite;
    }
    @keyframes ds-rot{ to{ transform:rotate(360deg); } }

    /* ── Subject list (table style) ── */
    .ds-subject-table-wrap{
        border:1px solid var(--line-strong);
        border-radius:14px;
        overflow:hidden;
        background:var(--surface);
    }
    .ds-subject-row{
        display:flex;
        align-items:center;
        gap:12px;
        padding:10px 14px;
        border-bottom:1px solid var(--line-soft);
        cursor:pointer;
        transition:var(--transition);
    }
    .ds-subject-row:last-child{ border-bottom:none; }
    .ds-subject-row:hover{
        background:rgba(20,184,166,.06);
    }
    .ds-subject-avatar{
        width:34px; height:34px;
        border-radius:10px;
        border:1px solid var(--line-strong);
        background:var(--surface-2);
        display:flex; align-items:center; justify-content:center;
        color:var(--accent-color);
        flex-shrink:0;
    }
    .ds-subject-title{
        font-family:var(--font-head);
        font-weight:700;
        font-size:.92rem;
        color:var(--ink);
        margin:0;
        flex:1;
    }
    .ds-subject-meta{ font-size:var(--fs-12); color:var(--muted-color); margin:0; }
    .ds-subject-badge{
        display:inline-flex;
        align-items:center;
        gap:4px;
        font-size:11px;
        font-weight:700;
        padding:2px 8px;
        border-radius:999px;
        background:var(--t-primary);
        border:1px solid rgba(201,75,80,.22);
        color:var(--accent-color);
        white-space:nowrap;
    }

    /* ── Empty state ── */
    .ds-empty{
        border:1px dashed var(--line-strong);
        border-radius:12px;
        padding:22px 16px;
        text-align:center;
        color:var(--muted-color);
        background:var(--surface-2);
        font-size:var(--fs-13);
    }

    /* ── Toast ── */
    .ds-toast-wrap{
        position:fixed; top:20px; right:20px;
        z-index:10000;
        display:flex; flex-direction:column; gap:8px;
    }
    .ds-toast{
        background:var(--surface);
        border-left:4px solid var(--accent-color);
        border-radius:10px;
        padding:10px 14px;
        min-width:240px;
        box-shadow:var(--shadow-2);
        color:var(--text-color);
        font-size:var(--fs-13);
        animation:ds-slideIn .2s ease;
    }
    @keyframes ds-slideIn{ from{ opacity:0; transform:translateX(16px); } to{ opacity:1; transform:translateX(0); } }
    .ds-toast.success{ border-left-color:var(--success-color); }
    .ds-toast.error  { border-left-color:var(--danger-color); }
    .ds-toast.warning{ border-left-color:var(--warning-color); }

    /* ── Modal ── */
    .ds-modal{
        position:fixed; inset:0;
        background:rgba(0,0,0,.45);
        display:none;
        align-items:center; justify-content:center;
        padding:16px;
        z-index:1050;
    }
    .ds-modal.show{ display:flex; }
    .ds-modal-box{
        width:min(1080px, 100%);
        max-height:90vh;
        overflow:hidden;
        border-radius:16px;
        border:1px solid var(--line-strong);
        background:var(--surface);
        box-shadow:var(--shadow-3);
        display:flex; flex-direction:column;
    }
    .ds-modal-header{
        padding:16px 18px;
        border-bottom:1px solid var(--line-strong);
        background:var(--surface);
        display:flex; align-items:center; justify-content:space-between; gap:12px;
    }
    .ds-modal-title{
        font-family:var(--font-head);
        font-weight:700;
        font-size:1rem;
        color:var(--ink);
        margin:0;
    }
    .ds-modal-sub{ margin:2px 0 0; color:var(--muted-color); font-size:var(--fs-13); }

    /* ── Modal body: chapter list + subtopics ── */
    .ds-modal-body{
        display:grid;
        grid-template-columns:300px 1fr;
        min-height:0; flex:1;
        overflow:hidden;
    }

    /* Chapter panel — list style matching qz-table */
    .ds-chapter-panel{
        border-right:1px solid var(--line-strong);
        background:var(--surface-2);
        overflow:auto;
    }
    .ds-chapter-item{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        padding:10px 14px;
        border-bottom:1px solid var(--line-soft);
        cursor:pointer;
        transition:var(--transition);
        font-size:var(--fs-13);
        color:var(--ink);
    }
    .ds-chapter-item:hover{ background:var(--surface-3); }
    .ds-chapter-item.active{
        background:var(--surface);
        border-left:3px solid var(--accent-color);
        color:var(--accent-color);
        font-weight:600;
    }
    .ds-chapter-name{ flex:1; font-weight:600; line-height:1.3; }
    .ds-chapter-count{
        font-size:11px;
        padding:2px 7px;
        border-radius:999px;
        border:1px solid var(--line-strong);
        background:var(--surface);
        color:var(--muted-color);
        white-space:nowrap;
    }
    .ds-chapter-count.has-doubts{
        background:var(--t-primary);
        border-color:rgba(201,75,80,.22);
        color:var(--accent-color);
        font-weight:700;
    }

    /* Subtopics panel */
    .ds-subtopic-panel{
        padding:16px 18px;
        overflow:auto;
        background:var(--surface);
    }
    .ds-subtopic-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        margin-bottom:14px;
        flex-wrap:wrap;
        padding-bottom:10px;
        border-bottom:1px solid var(--line-soft);
    }
    .ds-subtopic-title{
        font-family:var(--font-head);
        font-weight:700;
        font-size:.95rem;
        color:var(--ink);
        margin:0;
    }
    .ds-subtopic-count{ color:var(--muted-color); font-size:var(--fs-12); margin:0; }

    /* Subtopic grid — pill checkboxes */
    .ds-subtopic-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
        gap:8px;
        margin-bottom:18px;
    }
    .ds-subtopic-check{
        border:1px solid var(--line-strong);
        border-radius:10px;
        padding:10px 12px;
        display:flex; align-items:center; gap:10px;
        background:var(--surface);
        cursor:pointer;
        transition:var(--transition);
        font-size:var(--fs-13);
    }
    .ds-subtopic-check:hover{
        border-color:var(--accent-color);
        background:var(--surface-2);
    }
    .ds-subtopic-check input{
        width:16px; height:16px;
        accent-color:var(--accent-color);
        flex-shrink:0;
    }
    .ds-subtopic-label{ font-weight:500; line-height:1.3; color:var(--ink); }

    /* Notes */
    .ds-notes{
        border:1px solid var(--line-strong);
        border-radius:10px;
        padding:10px 12px;
        min-height:90px;
        resize:vertical;
        width:100%;
        outline:none;
        font-family:var(--font-sans);
        font-size:var(--fs-14);
        color:var(--text-color);
        background:var(--surface);
        transition:var(--transition);
    }
    .ds-notes:focus{
        border-color:var(--accent-color);
        box-shadow:var(--ring);
    }

    /* ── Modal footer ── */
    .ds-modal-footer{
        padding:12px 18px;
        border-top:1px solid var(--line-strong);
        background:#f4faf9;
        display:flex; justify-content:space-between; align-items:center;
        gap:10px; flex-wrap:wrap;
    }
    .ds-status-text{ color:var(--muted-color); font-size:var(--fs-13); }

    /* ── Dark theme ── */
    html.theme-dark .ds-card-shell,
    html.theme-dark .ds-modal-box{ background:var(--surface); border-color:var(--line-strong); }
    html.theme-dark .ds-head{ background:#020b13; border-color:var(--line-strong); }
    html.theme-dark .ds-chapter-panel{ background:#020b13; }
    html.theme-dark .ds-chapter-item{ border-color:var(--line-soft); color:var(--text-color); }
    html.theme-dark .ds-chapter-item:hover{ background:#04151f; }
    html.theme-dark .ds-chapter-item.active{ background:var(--surface); }
    html.theme-dark .ds-subtopic-panel{ background:var(--surface); }
    html.theme-dark .ds-subtopic-check{ background:var(--surface); border-color:var(--line-strong); }
    html.theme-dark .ds-subtopic-check:hover{ background:#04151f; }
    html.theme-dark .ds-subtopic-label{ color:var(--text-color); }
    html.theme-dark .ds-notes{ background:#04151f; color:var(--text-color); border-color:var(--line-strong); }
    html.theme-dark .ds-modal-footer{ background:#020b13; border-color:var(--line-strong); }
    html.theme-dark .ds-modal-header{ background:#020b13; border-color:var(--line-strong); }
    html.theme-dark .ds-toast{ background:var(--surface); color:var(--text-color); }
    html.theme-dark .ds-subject-table-wrap{ background:var(--surface); border-color:var(--line-strong); }
    html.theme-dark .ds-subject-row{ border-color:var(--line-soft); }
    html.theme-dark .ds-subject-row:hover{ background:rgba(20,184,166,.10); }
    html.theme-dark .ds-subject-avatar{ background:#020b13; border-color:var(--line-strong); }

    @media (max-width:768px){
        .ds-modal-body{ grid-template-columns:1fr; }
        .ds-chapter-panel{ border-right:none; border-bottom:1px solid var(--line-strong); max-height:220px; }
    }
</style>
@endpush

@section('content')
<div class="ds-wrap">
    <div class="ds-card-shell">

        <div class="ds-head">
            <div class="ds-head-icon">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <div>
                <h1 class="ds-head-title">Doubt Submission</h1>
                <div class="ds-head-sub">Select a subject, pick chapters, mark your doubts and save.</div>
            </div>
        </div>

        <div class="ds-body">
            <div class="ds-loader-wrap" id="subjectLoader">
                <div class="ds-loader"></div>
            </div>

            <div class="ds-subject-table-wrap" id="subjectGrid">
                {{-- skeleton rows shown while loading --}}
                <div class="ds-subject-row" style="pointer-events:none;">
                    <div style="width:34px;height:34px;border-radius:10px;background:var(--surface-3);flex-shrink:0;"></div>
                    <div style="flex:1;"><div style="height:12px;width:40%;border-radius:6px;background:var(--surface-3);margin-bottom:6px;"></div><div style="height:10px;width:25%;border-radius:6px;background:var(--surface-3);"></div></div>
                </div>
                <div class="ds-subject-row" style="pointer-events:none;">
                    <div style="width:34px;height:34px;border-radius:10px;background:var(--surface-3);flex-shrink:0;"></div>
                    <div style="flex:1;"><div style="height:12px;width:35%;border-radius:6px;background:var(--surface-3);margin-bottom:6px;"></div><div style="height:10px;width:20%;border-radius:6px;background:var(--surface-3);"></div></div>
                </div>
                <div class="ds-subject-row" style="pointer-events:none;">
                    <div style="width:34px;height:34px;border-radius:10px;background:var(--surface-3);flex-shrink:0;"></div>
                    <div style="flex:1;"><div style="height:12px;width:45%;border-radius:6px;background:var(--surface-3);margin-bottom:6px;"></div><div style="height:10px;width:30%;border-radius:6px;background:var(--surface-3);"></div></div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="ds-modal" id="doubtModal">
    <div class="ds-modal-box">

        <div class="ds-modal-header">
            <div>
                <h2 class="ds-modal-title" id="modalSubjectTitle">Subject</h2>
                <p class="ds-modal-sub">Choose a chapter from the left, then tick subtopics on the right.</p>
            </div>
            <button type="button" class="icon-btn" id="closeModalBtn">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="ds-modal-body">
            <div class="ds-chapter-panel" id="chapterPanel"></div>

            <div class="ds-subtopic-panel">
                <div id="subtopicContent"></div>
                <div class="mt-3">
                    <label for="notesBox" class="form-label fw-semibold" style="font-size:var(--fs-13);color:var(--ink);">
                        Notes <span style="color:var(--muted-color);">(optional)</span>
                    </label>
                    <textarea id="notesBox" class="ds-notes" placeholder="Write any doubt note here..."></textarea>
                </div>
            </div>
        </div>

        <div class="ds-modal-footer">
            <div class="ds-status-text" id="saveStatusText">Nothing selected yet.</div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" id="closeFooterBtn">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveBtn">
                    <i class="fa-solid fa-floppy-disk"></i> Save Submission
                </button>
            </div>
        </div>

    </div>
</div>

<div class="ds-toast-wrap" id="toastWrap"></div>
@endsection

@push('scripts')
<script>
(async () => {

    // ----------------------------------------------------------------
    // Config — loaded from API, not from blade/PHP
    // ----------------------------------------------------------------
    let SUBJECTS = {};

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    let activeSubject    = null;
    let activeChapter    = null;
    let fetchedExisting  = false;

    const currentSelections = {};
    const currentNotes      = {};

    // DOM refs
    const subjectGrid      = document.getElementById('subjectGrid');
    const doubtModal       = document.getElementById('doubtModal');
    const chapterPanel     = document.getElementById('chapterPanel');
    const subtopicContent  = document.getElementById('subtopicContent');
    const modalSubjectTitle= document.getElementById('modalSubjectTitle');
    const notesBox         = document.getElementById('notesBox');
    const saveBtn          = document.getElementById('saveBtn');
    const saveStatusText   = document.getElementById('saveStatusText');
    const toastWrap        = document.getElementById('toastWrap');

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    function titleize(text) {
        return String(text).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function getApiToken() {
        return (
            localStorage.getItem('token') ||
            sessionStorage.getItem('token') ||
            localStorage.getItem('auth_token') ||
            sessionStorage.getItem('auth_token') ||
            ''
        );
    }

    function buildHeaders() {
        const headers = {
            'Accept'          : 'application/json',
            'Content-Type'    : 'application/json',
            'X-CSRF-TOKEN'    : csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        };
        const token = getApiToken();
        if (token) headers['Authorization'] = 'Bearer ' + token;
        return headers;
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className   = 'ds-toast ' + type;
        toast.textContent = message;
        toastWrap.appendChild(toast);
        setTimeout(() => toast.remove(), 3200);
    }

    function subjectIcon(key) {
        if (key === 'physics')   return '<i class="fa-solid fa-atom"></i>';
        if (key === 'chemistry') return '<i class="fa-solid fa-flask-vial"></i>';
        return '<i class="fa-solid fa-square-root-variable"></i>';
    }

    // ----------------------------------------------------------------
    // Subjects — fetch from API, then render cards
    // ----------------------------------------------------------------
    async function loadSubjects() {
        try {
            const res = await fetch('/api/student/doubt-subjects', { headers: buildHeaders() });
            if (!res.ok) throw new Error('Failed to load subjects.');
            const payload = await res.json();
            SUBJECTS = payload.data || {};
        } catch (err) {
            console.error(err);
            showToast('Could not load subjects. Please refresh.', 'error');
            SUBJECTS = {};
        }

        // init state for every subject
        Object.keys(SUBJECTS).forEach(subject => {
            currentSelections[subject] = buildEmptySubjectState(subject);
            currentNotes[subject]      = '';
        });

        renderSubjectCards();
        refreshSubjectCounts();
    }

    function renderSubjectCards() {
        const keys = Object.keys(SUBJECTS);

        if (!keys.length) {
            subjectGrid.innerHTML = '<div class="ds-empty" style="margin:12px;">No subjects available.</div>';
            return;
        }

        subjectGrid.innerHTML = keys.map(key => {
            const data  = SUBJECTS[key];
            const total = countSelectedForSubject(key);
            return `
                <div class="ds-subject-row" data-subject="${key}">
                    <div class="ds-subject-avatar">${subjectIcon(key)}</div>
                    <div style="flex:1; min-width:0;">
                        <div class="ds-subject-title">${data.label ?? titleize(key)}</div>
                        <div class="ds-subject-meta">${Object.keys(data.chapters ?? {}).length} chapters</div>
                    </div>
                    <span class="ds-subject-badge" id="subject-selected-${key}" style="opacity:${total > 0 ? 1 : .4};">
                        ${total > 0 ? `<i class="fa-solid fa-circle-check"></i>` : ''}${total} selected
                    </span>
                    <i class="fa-solid fa-chevron-right" style="color:var(--muted-color);font-size:12px;margin-left:6px;"></i>
                </div>
            `;
        }).join('');

        subjectGrid.querySelectorAll('.ds-subject-row').forEach(row => {
            row.addEventListener('click', () => openSubject(row.dataset.subject));
        });
    }

    // ----------------------------------------------------------------
    // Selection state
    // ----------------------------------------------------------------
    function buildEmptySubjectState(subject) {
        const state    = {};
        const chapters = SUBJECTS?.[subject]?.chapters || {};
        Object.keys(chapters).forEach(chapterKey => {
            state[chapterKey] = {};
            Object.keys(chapters[chapterKey]?.subtopics || {}).forEach(subtopicKey => {
                state[chapterKey][subtopicKey] = 0;
            });
        });
        return state;
    }

    function mergeSavedTopics(subject, savedTopics) {
        const fresh = buildEmptySubjectState(subject);
        Object.keys(fresh).forEach(chapter => {
            Object.keys(fresh[chapter]).forEach(subtopic => {
                fresh[chapter][subtopic] = Number(savedTopics?.[chapter]?.[subtopic] ?? 0);
            });
        });
        return fresh;
    }

    function countSelectedForChapter(subject, chapter) {
        return Object.values(currentSelections?.[subject]?.[chapter] || {})
            .filter(v => Number(v) === 1).length;
    }

    function countSelectedForSubject(subject) {
        return Object.keys(currentSelections?.[subject] || {})
            .reduce((sum, ch) => sum + countSelectedForChapter(subject, ch), 0);
    }

    function refreshSubjectCounts() {
        Object.keys(SUBJECTS).forEach(subject => {
            const el    = document.getElementById('subject-selected-' + subject);
            if (!el) return;
            const total = countSelectedForSubject(subject);
            el.innerHTML = `${total > 0 ? '<i class="fa-solid fa-circle-check"></i>' : ''}${total} selected`;
            el.style.opacity = total > 0 ? '1' : '.4';
        });
    }

    // ----------------------------------------------------------------
    // Render chapter panel
    // ----------------------------------------------------------------
    function renderChapterPanel() {
        const chapters    = SUBJECTS?.[activeSubject]?.chapters || {};
        const chapterKeys = Object.keys(chapters);

        if (!chapterKeys.length) {
            chapterPanel.innerHTML = '<div class="ds-empty" style="margin:12px;">No chapters found.</div>';
            return;
        }

        chapterPanel.innerHTML = chapterKeys.map(chapterKey => {
            const chapter  = chapters[chapterKey];
            const count    = countSelectedForChapter(activeSubject, chapterKey);
            const isActive = activeChapter === chapterKey ? 'active' : '';

            return `
                <div class="ds-chapter-item ${isActive}" onclick="openChapter('${chapterKey}')">
                    <span class="ds-chapter-name">${chapter.label ?? titleize(chapterKey)}</span>
                    <span class="ds-chapter-count ${count > 0 ? 'has-doubts' : ''}">${count}</span>
                </div>
            `;
        }).join('');
    }

    // ----------------------------------------------------------------
    // Render subtopics panel
    // ----------------------------------------------------------------
    function renderSubtopics() {
        if (!activeSubject || !activeChapter) {
            subtopicContent.innerHTML = '<div class="ds-empty">Select a chapter from the left to view subtopics.</div>';
            return;
        }

        const chapter       = SUBJECTS?.[activeSubject]?.chapters?.[activeChapter] || {};
        const subtopics     = chapter.subtopics || {};
        const selectedCount = countSelectedForChapter(activeSubject, activeChapter);
        const allChecked    = Object.keys(subtopics).length > 0 &&
                              Object.keys(subtopics).every(k =>
                                  Number(currentSelections[activeSubject][activeChapter][k]) === 1
                              );

        subtopicContent.innerHTML = `
            <div class="ds-subtopic-head">
                <div>
                    <h3 class="ds-subtopic-title">${chapter.label ?? titleize(activeChapter)}</h3>
                    <p class="ds-subtopic-count">${selectedCount} of ${Object.keys(subtopics).length} selected</p>
                </div>
                <label class="ds-subtopic-check" style="width:auto;">
                    <input type="checkbox" ${allChecked ? 'checked' : ''} onchange="toggleSelectAll(this.checked)">
                    <span class="ds-subtopic-label">Select all</span>
                </label>
            </div>

            <div class="ds-subtopic-grid">
                ${Object.keys(subtopics).map(subtopicKey => {
                    const checked = Number(currentSelections[activeSubject][activeChapter][subtopicKey]) === 1 ? 'checked' : '';
                    return `
                        <label class="ds-subtopic-check">
                            <input
                                type="checkbox"
                                ${checked}
                                onchange="toggleSubtopic('${activeChapter}', '${subtopicKey}', this.checked)"
                            >
                            <span class="ds-subtopic-label">${subtopics[subtopicKey]}</span>
                        </label>
                    `;
                }).join('')}
            </div>
        `;

        updateFooterStatus();
    }

    function updateFooterStatus() {
        if (!activeSubject) { saveStatusText.textContent = 'Nothing selected yet.'; return; }
        const total = countSelectedForSubject(activeSubject);
        saveStatusText.textContent = `${titleize(activeSubject)}: ${total} subtopics selected`;
    }

    // ----------------------------------------------------------------
    // Load existing submissions (once per page load)
    // ----------------------------------------------------------------
    async function loadExistingSubmissions() {
        if (fetchedExisting) return;

        try {
            const res = await fetch('/api/student/doubt-submissions', { method: 'GET', headers: buildHeaders() });
            if (!res.ok) throw new Error('Failed to fetch previous submissions.');

            const payload = await res.json();
            const rows    = Array.isArray(payload.data) ? payload.data : [];
            const today   = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata' }).format(new Date());

            const bestBySubject = {};
            rows.forEach(row => {
                if (!row.subject || !(row.subject in SUBJECTS)) return;
                const rowDate  = row.submitted_date || String(row.submitted_at || '').slice(0, 10);
                const rowScore = rowDate === today ? 2 : 1;

                if (!bestBySubject[row.subject]) { bestBySubject[row.subject] = { ...row, __score: rowScore }; return; }

                const existing     = bestBySubject[row.subject];
                const existingTime = new Date(existing.submitted_at || 0).getTime();
                const currentTime  = new Date(row.submitted_at    || 0).getTime();

                if (rowScore > existing.__score || (rowScore === existing.__score && currentTime > existingTime)) {
                    bestBySubject[row.subject] = { ...row, __score: rowScore };
                }
            });

            Object.keys(bestBySubject).forEach(subject => {
                const row = bestBySubject[subject];
                let parsedTopics = {};
                try {
                    parsedTopics = typeof row.topics === 'string' ? JSON.parse(row.topics) : (row.topics || {});
                } catch (e) { parsedTopics = {}; }

                currentSelections[subject] = mergeSavedTopics(subject, parsedTopics);
                currentNotes[subject]      = row.notes || '';
            });

            refreshSubjectCounts();
            fetchedExisting = true;
        } catch (error) {
            console.error(error);
            showToast('Could not load previous submissions. You can still submit new data.', 'warning');
        }
    }

    // ----------------------------------------------------------------
    // Modal open / close
    // ----------------------------------------------------------------
    async function openSubject(subject) {
        // already loaded at boot — skip re-fetch unless something reset the flag
        await loadExistingSubmissions();

        activeSubject  = subject;
        modalSubjectTitle.textContent = SUBJECTS?.[subject]?.label || titleize(subject);

        const chapters = Object.keys(SUBJECTS?.[subject]?.chapters || {});
        activeChapter  = chapters.length ? chapters[0] : null;
        notesBox.value = currentNotes[subject] || '';

        renderChapterPanel();
        renderSubtopics();
        doubtModal.classList.add('show');
        document.body.style.overflow = 'hidden';    }

    function closeModal() {
        doubtModal.classList.remove('show');
        document.body.style.overflow = '';
        activeSubject = null;
        activeChapter = null;
    }

    // ----------------------------------------------------------------
    // Global handlers (called from inline onclick in rendered HTML)
    // ----------------------------------------------------------------
    window.openChapter = function (chapter) {
        activeChapter = chapter;
        renderChapterPanel();
        renderSubtopics();
    };

    window.toggleSubtopic = function (chapter, subtopic, isChecked) {
        currentSelections[activeSubject][chapter][subtopic] = isChecked ? 1 : 0;
        renderChapterPanel();
        renderSubtopics();
        refreshSubjectCounts();
    };

    window.toggleSelectAll = function (isChecked) {
        if (!activeSubject || !activeChapter) return;
        const subtopics = SUBJECTS?.[activeSubject]?.chapters?.[activeChapter]?.subtopics || {};
        Object.keys(subtopics).forEach(key => {
            currentSelections[activeSubject][activeChapter][key] = isChecked ? 1 : 0;
        });
        renderChapterPanel();
        renderSubtopics();
        refreshSubjectCounts();
    };

    // ----------------------------------------------------------------
    // Save submission
    // ----------------------------------------------------------------
    async function saveSubmission() {
        if (!activeSubject) return;

        currentNotes[activeSubject] = notesBox.value.trim();
        saveBtn.disabled    = true;
        saveBtn.innerHTML   = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...';

        try {
            const res = await fetch('/api/student/doubt-submissions', {
                method : 'POST',
                headers: buildHeaders(),
                body   : JSON.stringify({
                    subject: activeSubject,
                    topics : currentSelections[activeSubject],
                    notes  : currentNotes[activeSubject] || null,
                }),
            });

            const payload = await res.json();

            if (!res.ok) {
                let message = payload?.message || 'Save failed.';
                if (payload?.errors) {
                    const firstKey = Object.keys(payload.errors)[0];
                    if (firstKey) message = payload.errors[firstKey][0];
                }
                throw new Error(message);
            }

            if (payload?.submission?.topics) {
                currentSelections[activeSubject] = mergeSavedTopics(activeSubject, payload.submission.topics);
                currentNotes[activeSubject]      = payload.submission.notes || '';
            }

            refreshSubjectCounts();
            renderChapterPanel();
            renderSubtopics();
            showToast(payload?.message || 'Submission saved successfully.', 'success');
            closeModal();
        } catch (error) {
            console.error(error);
            showToast(error.message || 'Something went wrong while saving.', 'error');
        } finally {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Save Submission';
        }
    }

    // ----------------------------------------------------------------
    // Event listeners
    // ----------------------------------------------------------------
    document.getElementById('closeModalBtn').addEventListener('click', closeModal);
    document.getElementById('closeFooterBtn').addEventListener('click', closeModal);
    saveBtn.addEventListener('click', saveSubmission);

    notesBox.addEventListener('input', () => {
        if (activeSubject) currentNotes[activeSubject] = notesBox.value;
    });

    doubtModal.addEventListener('click', e => {
        if (e.target === doubtModal) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && doubtModal.classList.contains('show')) closeModal();
    });

    // ----------------------------------------------------------------
    // Boot — fetch subjects first, then existing submissions, then render
    // ----------------------------------------------------------------
    await loadSubjects();         // builds SUBJECTS + renders skeleton → cards (count = 0)
    await loadExistingSubmissions(); // merges saved topics into currentSelections
    refreshSubjectCounts();       // re-stamp correct counts on the already-rendered rows

})();
</script>
@endpush