@extends('layouts.chat_full')

@section('content')

<div class="container-fluid p-3">

<style>
    .row-urgent td {
        background: #ffe5e5 !important;
    }

    .row-urgent td:first-child {
        font-weight: 700;
    }

    /* Center tables */
    #resultArea table th,
    #resultArea table td {
        text-align: center;
        vertical-align: middle;
    }

    /* Better spacing for full screen */
    #chatLog {
        height: 220px;
    }
</style>

<div class="card shadow-sm">

    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="fw-bold">Member Chat — Full Screen</div>
        <button onclick="history.back()"
            class="btn btn-sm btn-outline-secondary">
                {{ t('common.back','Back') }}
        </button>

    </div>

    <div class="card-body">

        {{-- Suggestions --}}
        <div class="mb-3">

            <div class="small text-muted mb-2">Try one:</div>

            <div class="accordion" id="chatSuggestionsAcc">

                @php $i = 0; @endphp

                @foreach($suggestionGroups as $groupTitle => $items)

                    @php
                        $i++;
                        $collapseId = "sgCollapseFull".$i;
                    @endphp

                    <div class="accordion-item">

                        <h2 class="accordion-header" id="sgHeadFull{{ $i }}">

                            <button class="accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}">
                                {{ $groupTitle }}
                            </button>

                        </h2>

                        <div id="{{ $collapseId }}"
                             class="accordion-collapse collapse"
                             data-bs-parent="#chatSuggestionsAcc">

                            <div class="accordion-body d-flex flex-wrap gap-2">

                                @foreach($items as $s)

                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary suggestion-btn">
                                        {{ $s }}
                                    </button>

                                @endforeach

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>


        {{-- Chat Window --}}
        <div id="chatLog"
             class="border rounded p-3 mb-3"
             style="overflow:auto; background:#fafafa;">

            <div class="text-muted small">{{ $welcome ?? 'Hello! ' }} Ask a question about members…</div>

        </div>


        {{-- Input --}}
        <div class="d-flex gap-2 mb-3">

            <input id="chatInput"
                   type="text"
                   class="form-control"
                   placeholder="Example: How many male and female members in Morning shift?">

            <button id="sendBtn"
                    class="btn btn-primary">
                Send
            </button>

        </div>


        {{-- Results --}}
        <div>

            <div id="resultTitle"
                 class="fw-bold mb-2"
                 style="display:none;">
            </div>

            <div id="resultArea"></div>

        </div>

    </div>

</div>

</div>


{{-- ================= CHAT SCRIPT ================= --}}

<script>

(function () {

    const chatLog    = document.getElementById('chatLog');
    const chatInput  = document.getElementById('chatInput');
    const sendBtn    = document.getElementById('sendBtn');
    const resultArea = document.getElementById('resultArea');
    const resultTitle= document.getElementById('resultTitle');


    function appendMsg(who, text) {

        const div = document.createElement('div');

        div.className = 'mb-2';

        div.innerHTML =
            `<div class="small">
                <span class="fw-bold">${who}:</span>
                ${escapeHtml(text)}
            </div>`;

        chatLog.appendChild(div);

        chatLog.scrollTop = chatLog.scrollHeight;
    }


    function escapeHtml(str) {

        return String(str)
          .replaceAll('&','&amp;')
          .replaceAll('<','&lt;')
          .replaceAll('>','&gt;')
          .replaceAll('"','&quot;')
          .replaceAll("'","&#039;");
    }


    function renderCounts(title, rows) {

        resultTitle.style.display = '';
        resultTitle.textContent = title;

        let m = 0, f = 0;

        (rows || []).forEach(r => {

            if ((r.Sex || '').toUpperCase() === 'M') m = r.total;
            if ((r.Sex || '').toUpperCase() === 'F') f = r.total;

        });

        resultArea.innerHTML = `

        <div class="row g-2">

            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Male</div>
                    <div class="fs-4 fw-bold">${m}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Female</div>
                    <div class="fs-4 fw-bold">${f}</div>
                </div>
            </div>

        </div>`;
    }


    function exportButtons(exportInfo){

        if(!exportInfo) return '';

        const params = new URLSearchParams(exportInfo).toString();


        if (exportInfo.type === 'vitals_monthly') {

            return `
            <div class="mb-2 d-flex gap-2">

              <a class="btn btn-sm btn-outline-success"
                 href="/chat/export/vitals/excel?${params}">
                 Export Excel
              </a>

              <a class="btn btn-sm btn-outline-danger"
                 href="/chat/export/vitals/pdf?${params}"
                 target="_blank">
                 Export PDF
              </a>

            </div>`;
        }


        return `
        <div class="mb-2 d-flex gap-2">

          <a class="btn btn-sm btn-outline-success"
             href="/chat/export/birthdays/excel?${params}">
             Export Excel
          </a>

          <a class="btn btn-sm btn-outline-danger"
             href="/chat/export/birthdays/pdf?${params}"
             target="_blank">
             Export PDF
          </a>

        </div>`;
    }



    function renderTable(title, columns, rows) {

        resultTitle.style.display = '';
        resultTitle.textContent = title;


        const thead =
            `<tr>${columns.map(c=>`<th>${c}</th>`).join('')}</tr>`;


        const tbody = (rows || []).map(r => {

            const urgent = String(r.Urgent)==='1' ? 'row-urgent':'';

            return `
            <tr class="${urgent}">

            ${columns.map(c => {

                let v = r[c] ?? '';

                if (c === 'Age' && v !== '') {
                    v = ordinal(v)+' Birthday';
                }

                return `<td>${escapeHtml(v)}</td>`;

            }).join('')}

            </tr>`;

        }).join('');


        const urgentCount =
            (rows||[]).filter(x=>String(x.Urgent)==='1').length;


        const urgentNote = urgentCount>0
          ? `<div class="alert alert-danger py-1 small">
               Urgent: ${urgentCount} record(s)
             </div>`
          : '';


        const exportsHtml = exportButtons(window.__exportInfo);


        resultArea.innerHTML = urgentNote+`

        ${exportsHtml}

        <div class="table-responsive">

        <table class="table table-sm table-bordered">

            <thead>${thead}</thead>

            <tbody>
                ${tbody || `<tr><td colspan="${columns.length}">No records</td></tr>`}
            </tbody>

        </table>

        </div>`;
    }



    async function sendMessage(msg) {

        appendMsg('You', msg);


        const res = await fetch("/chat/ask", {

            method: 'POST',

            headers: {
              'Content-Type':'application/json',
              'X-CSRF-TOKEN':"{{ csrf_token() }}"
            },

            body: JSON.stringify({message:msg})
        });


        const data = await res.json();

        window.__exportInfo = data.export || null;


        if(!data.ok){
            appendMsg('Bot', data.message || 'Error');
            return;
        }


        appendMsg('Bot', data.title || 'Done');


        if(data.type==='counts'){
            renderCounts(data.title, data.data);
        }

        if(data.type==='table'){
            renderTable(data.title, data.columns, data.data);
        }

    }



    function ordinal(n){

        n = parseInt(n,10);

        if(isNaN(n)) return n;

        const mod100 = n%100;

        if(mod100>=11 && mod100<=13) return n+'th';

        switch(n%10){
          case 1:return n+'st';
          case 2:return n+'nd';
          case 3:return n+'rd';
          default:return n+'th';
        }
    }



    sendBtn.addEventListener('click', ()=>{

        const msg = chatInput.value.trim();

        if(!msg) return;

        chatInput.value='';

        sendMessage(msg);
    });


    chatInput.addEventListener('keydown', e=>{

        if(e.key==='Enter'){
            e.preventDefault();
            sendBtn.click();
        }
    });


    document.addEventListener('click', e=>{

        const btn = e.target.closest('.suggestion-btn');

        if(!btn) return;

        chatInput.value = btn.textContent.trim();

        chatInput.focus();
    });


})();

</script>

@endsection
