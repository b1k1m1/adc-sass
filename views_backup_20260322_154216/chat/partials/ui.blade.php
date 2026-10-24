
<div class="page-content">
  <div class="container-fluid">
    <style>
        .row-urgent td { background: #ffe5e5 !important; }   /* light red */
        .row-urgent td:first-child { font-weight: 700; }
         /* Center all chat result tables */
        #resultArea table th,
        #resultArea table td {
            text-align: center;
            vertical-align: middle;

        }
    </style>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="fw-bold">Member Chat (V1)</div>
        <div class="text-muted small">Allowed: Shift counts + Birthdays + Prior Authorization + HNP Expiration</div>
      </div>

      <div class="card-body">

        {{-- Suggestions --}}
        <!--
        <div class="mb-3">
          <div class="small text-muted mb-2">Try one:</div>
          <div id="suggestions" class="d-flex flex-wrap gap-2">
            {{-- @foreach($suggestions as $s)  --}}
                <button type="button" class="btn btn-sm btn-outline-primary suggestion-btn">{{-- $s -- }}</button>
            {{-- @endforeach  --}}
          </div>
        </div>
        -->

        <div class="mb-3">
            <div class="small text-muted mb-2">Try one:</div>
                <div class="accordion" id="chatSuggestionsAcc">
                    @php $i = 0; @endphp

                    @foreach($suggestionGroups as $groupTitle => $items)
                    @php $i++; $collapseId = "sgCollapse".$i; @endphp

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="sgHead{{ $i }}">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="false"
                                aria-controls="{{ $collapseId }}">
                            {{ $groupTitle }}
                        </button>
                        </h2>

                        <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                            aria-labelledby="sgHead{{ $i }}"
                            data-bs-parent="#chatSuggestionsAcc">
                        <div class="accordion-body d-flex flex-wrap gap-2">
                            @foreach($items as $s)
                            <button type="button" class="btn btn-sm btn-outline-primary suggestion-btn">
                                {{ $s }}
                            </button>
                            @endforeach
                        </div>
                        </div>
                    </div>
                    @endforeach
                </div>
        </div>


        {{-- Chat window --}}
        <div id="chatLog" class="border rounded p-3 mb-3" style="height:260px; overflow:auto; background:#fafafa;">
          <div class="text-muted small">{{ $welcome ?? 'Hello! ' }} Ask a question about members…</div>
        </div>

        {{-- Input --}}
        <div class="d-flex gap-2">
          <input id="chatInput" type="text" class="form-control" placeholder="Example: How many male and female members in Morning shift?">
          <button id="sendBtn" class="btn btn-primary">Send</button>
        </div>

        {{-- Results --}}
        <div class="mt-4">
          <div id="resultTitle" class="fw-bold mb-2" style="display:none;"></div>
          <div id="resultArea"></div>
        </div>

      </div>
    </div>

  </div>
</div>

<script>

(function () {
  const chatLog = document.getElementById('chatLog');
  const chatInput = document.getElementById('chatInput');
  const sendBtn = document.getElementById('sendBtn');
  const resultTitle = document.getElementById('resultTitle');
  const resultArea = document.getElementById('resultArea');

    let __lastQuery = null;
    let __isRefreshing = false;

    // Re-run last query without printing "You:" again (optional)
    async function refreshLastQuery(){
    if(!__lastQuery) return;
    __isRefreshing = true;
    await sendMessage(__lastQuery, { silentUser: true });
    __isRefreshing = false;
    }


  function appendMsg(who, text) {
    const div = document.createElement('div');
    div.className = 'mb-2';
    div.innerHTML = `<div class="small"><span class="fw-bold">${who}:</span> ${escapeHtml(text)}</div>`;
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function setSuggestions(list) {
    //const wrap = document.getElementById('suggestions');
    //wrap.innerHTML = '';
    //(list || []).forEach(s => {
    //  const btn = document.createElement('button');
    //  btn.type = 'button';
    //  btn.className = 'btn btn-sm btn-outline-primary suggestion-btn';
    //  btn.textContent = s;
    //  btn.addEventListener('click', () => {
    //    chatInput.value = s;
    //    chatInput.focus();
    //  });
    //  wrap.appendChild(btn);
    //});
  }

  function renderCounts(title, rows) {
    resultTitle.style.display = '';
    resultTitle.textContent = title;

    // Build M/F cards
    let m = 0, f = 0;
    (rows || []).forEach(r => {
      if ((r.Sex || '').toUpperCase() === 'M') m = parseInt(r.total || 0);
      if ((r.Sex || '').toUpperCase() === 'F') f = parseInt(r.total || 0);
    });

    resultArea.innerHTML = `
      <div class="row g-2">
        <div class="col-md-3">
          <div class="border rounded p-3">
            <div class="text-muted small">Male (M)</div>
            <div class="fs-4 fw-bold">${m}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded p-3">
            <div class="text-muted small">Female (F)</div>
            <div class="fs-4 fw-bold">${f}</div>
          </div>
        </div>
      </div>
    `;
  }

    function exportButtonsHtml(exportInfo,editInfo){
    if(!exportInfo) return '';

    const params = new URLSearchParams(exportInfo).toString();

    if (exportInfo.type === 'vitals_monthly') {
       const excelUrl = "{{ route('chat.export.vitals.excel', [], false) }}" + "?" + params;
       const pdfUrl   = "{{ route('chat.export.vitals.pdf',   [], false) }}" + "?" + params;

         // ✅ Build Edit button if available
        let editBtn = '';
        let editHtml = '';

        if (editInfo && editInfo.url) {
            editHtml = `
                <a class="btn btn-sm btn-outline-primary"
                   href="${editInfo.url}"
                   target="_blank">
                   ${editInfo.label || 'Open Edit'}
                </a>
            `;
        }

        return `
        <div class="mb-2 d-flex gap-2">
            <a class="btn btn-sm btn-outline-success" href="${excelUrl}" target="_blank">Export Excel</a>
            <a class="btn btn-sm btn-outline-danger" href="${pdfUrl}" target="_blank">Export PDF</a>
             ${editHtml}
        </div>
        `;
    }

    // default birthdays
    const excelUrl = "{{ route('chat.export.birthdays.excel', [], false) }}" + "?" + params;
    const pdfUrl   = "{{ route('chat.export.birthdays.pdf',   [], false) }}" + "?" + params;

    return `
        <div class="mb-2 d-flex gap-2">
        <a class="btn btn-sm btn-outline-success" href="${excelUrl}" target="_blank">Export Excel</a>
        <a class="btn btn-sm btn-outline-danger" href="${pdfUrl}" target="_blank">Export PDF</a>
        </div>
    `;
    }


  function renderTable(title, columns, rows) {
    resultTitle.style.display = '';
    resultTitle.textContent = title;
    const actions = window.__actions || {};
    const hasRowActions = actions && Object.keys(actions).length > 0;
    const cols = hasRowActions ? [...columns, 'Action'] : [...columns];

    const thead = `<tr>${columns.map(c => `<th class="small">${escapeHtml(c)}</th>`).join('')}</tr>`;
    const tbody = (rows || []).map(r => {
    const urgentClass = (String(r.Urgent) === '1') ? 'row-urgent' : '';

    let tds = columns.map(c => {
        let v = r[c] ?? '';

        if (c === 'Age' && v !== '') {
        v = ordinal(v) + ' Birthday';
        }

        return `<td class="small">${escapeHtml(v)}</td>`;
    }).join('');

    // ✅ add Action cell if actions exist
    if (hasRowActions) {
        const pidRaw = r.ParticipantId ?? '';
        const a = actions[pidRaw]?.pa || actions[String(pidRaw)]?.pa;

        if (a && a.url) {
        tds += `<td class="small text-nowrap">
            <a class="btn btn-sm btn-outline-warning"
            href="${a.url}"
            target="_blank">${escapeHtml(a.label || 'Edit')}</a>
        </td>`;
        } else {
        tds += `<td class="small text-muted">-</td>`;
        }
    }

    return `<tr class="${urgentClass}">${tds}</tr>`;
    }).join('');

    const urgentCount = (rows || []).filter(x => String(x.Urgent) === '1').length;
    const urgentNote = urgentCount > 0
    ? `<div class="alert alert-danger py-1 mb-2 small">Urgent: ${urgentCount} PA(s) / HNP expiring within 7 days</div>`
    : '';

    const exportsHtml = exportButtonsHtml(window.__exportInfo, window.__editInfo);

    resultArea.innerHTML = urgentNote +`
        ${exportsHtml}
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
          <thead>${thead}</thead>
          <tbody>${tbody || `<tr><td colspan="${cols.length}" class="text-muted small">No records found</td></tr>`}</tbody>
        </table>
      </div>
    `;
  }

  async function sendMessage(msg, opts = {}) {
    const silentUser = !!opts.silentUser;
    if (!silentUser) {
        __lastQuery = msg;             // ✅ remember last query
        appendMsg('You', msg);
    }

    const res = await fetch("/chat/ask", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      body: JSON.stringify({ message: msg })
    });

    const data = await res.json();
    window.__exportInfo = data.export || null;
    window.__editInfo   = data.edit   || null;
    window.__actions    = data.actions || null;

    if (!data.ok) {
      if(!silentUser) appendMsg('Bot', data.message || 'Error');
      //setSuggestions(data.suggestions || []);
      return;
    }

    if (data.type === 'unsupported') {
      if (!silentUser) appendMsg('Bot', data.message || 'Unsupported question.');
      //setSuggestions(data.suggestions || []);
      resultTitle.style.display = 'none';
      resultArea.innerHTML = '';
      return;
    }

    if (!silentUser) appendMsg('Bot', data.title || 'Done');
    //setSuggestions(data.suggestions || []);

    if (data.type === 'counts') {
      renderCounts(data.title, data.data);
      return;
    }

    if (data.type === 'table') {
      renderTable(data.title, data.columns || [], data.data || []);
      return;
    }

    // fallback
    resultTitle.style.display = 'none';
    resultArea.innerHTML = '';
  }

    let bc = null;
    if ('BroadcastChannel' in window) {
    bc = new BroadcastChannel('adc-chat');
    bc.onmessage = (ev) => {
        const m = ev.data || {};
        if (m.type === 'refresh_chat') {
        refreshLastQuery();
        }
    };
    }
    function ordinal(n) {
        n = parseInt(n, 10);
        if (isNaN(n)) return n;

        const mod100 = n % 100;

        if (mod100 >= 11 && mod100 <= 13) {
            return n + 'th';
        }

        switch (n % 10) {
            case 1: return n + 'st';
            case 2: return n + 'nd';
            case 3: return n + 'rd';
            default: return n + 'th';
            }
    }

    sendBtn.addEventListener('click', () => {
        const msg = chatInput.value.trim();
        if (!msg) return;
        chatInput.value = '';
    sendMessage(msg);
  });

  chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      sendBtn.click();
    }
  });

  // Suggestion buttons already rendered by Blade; attach click handlers
  //document.querySelectorAll('.suggestion-btn').forEach(btn => {
  //  btn.addEventListener('click', () => {
  //    chatInput.value = btn.textContent.trim();
  //    chatInput.focus();
  //  });
  //});

  document.addEventListener('click', function(e){
  const btn = e.target.closest('.suggestion-btn');
  if(!btn) return;
  chatInput.value = btn.textContent.trim();
  chatInput.focus();
});

})();
</script>


