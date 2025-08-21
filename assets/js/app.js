(function(){
  const root = document.getElementById('qb12-root');
  if (!root) return;
  root.classList.add('qb12');
  const BOOT = window.QB12_BOOTSTRAP || {};
  const REV = BOOT.__REV || '';
  const CFG = BOOT.__CFG || {};
  console.log('[QB12] Front loaded. REV=', REV, 'CFG=', CFG);

  const isBuilder = !!CFG.admin_builder;

  if (isBuilder) {
    root.innerHTML = `
    <div class="wrap">
      <aside class="card" id="qb12-aside">
        <div class="h">Chọn bài học (Toán 12)</div>
        <div id="qb12-lessonList" class="muted"></div>
        <div class="hr"></div>
        <div class="h">Xây đề nhanh</div>
        <div class="grid2">
          <input id="qb12-numRand" type="number" min="1" value="5" />
          <button id="qb12-btnAddRand" class="btn">Thêm ngẫu nhiên</button>
        </div>
        <div class="grid3" style="margin-top:8px">
          <button id="qb12-btnSelectAll" class="btn secondary">Chọn hết</button>
          <button id="qb12-btnClear" class="btn secondary">Bỏ chọn</button>
          <button id="qb12-btnRemove" class="btn danger">Xoá mục chọn</button>
        </div>
        <div class="hr"></div>
        <div class="h">Nhập/Xuất ngân hàng</div>
        <div class="grid2">
          <button id="qb12-btnExportBank" class="btn secondary" disabled>Xuất JSON</button>
          <label class="btn secondary" for="qb12-importBank">Nhập JSON</label>
          <input id="qb12-importBank" type="file" accept="application/json" class="hide" />
        </div>
        <div class="hr"></div>
        <div class="muted">Mẹo: Nhấn <span class="kbd">Đổi đề</span> để đảo số liệu & ngữ cảnh.</div>
      </aside>

      <main class="card">
        <div class="flex" style="justify-content:space-between">
          <div class="h">Ngân hàng câu hỏi</div>
          <div class="pill"><span>Đang chọn:</span><strong id="qb12-selCount">0</strong></div>
        </div>
        <div id="qb12-bankView"></div>

        <div class="hr"></div>
        <div class="grid3" id="qb12-controls">
          <div>
            <label class="muted">Chế độ</label>
            <select id="qb12-mode">
              <option value="practice">Practice (hiện feedback từng câu)</option>
              <option value="exam">Exam (ẩn giải thích, hẹn giờ)</option>
            </select>
          </div>
          <div>
            <label class="muted">Thời gian (phút, cho Exam)</label>
            <input id="qb12-minutes" type="number" min="5" step="5" value="15" />
          </div>
          <div class="right">
            <button id="qb12-btnReseed" class="btn secondary">Đổi đề (đổi số liệu)</button>
            <button id="qb12-btnStart" class="btn">Bắt đầu làm bài</button>
          </div>
        </div>

        <div id="qb12-quizArea" class="hide">
          <div class="hr"></div>
          <div class="flex" style="justify-content:space-between">
            <div class="h">Đề đang làm</div>
            <div class="pill">Thời gian: <span id="qb12-timer" class="timer">--:--</span></div>
          </div>
          <div id="qb12-quizList"></div>

          <div class="hr"></div>
          <div class="flex" style="justify-content:space-between">
            <div>
              <button id="qb12-btnSubmit" class="btn">Nộp bài</button>
              <button id="qb12-btnReset" class="btn secondary">Làm lại (đổi đề)</button>
            </div>
            <div id="qb12-scoreBox" class="pill hide"></div>
          </div>

          <div id="qb12-exportArea" class="hide" style="margin-top:10px">
            <button id="qb12-btnDownloadTxt" class="btn secondary">Tải TXT đề</button>
            <button id="qb12-btnDownloadKey" class="btn secondary">Tải TXT đáp án</button>
          </div>
        </div>
      </main>
    </div>`;
  } else {
    root.innerHTML = `
    <div class="wrap">
      <main class="card" style="grid-column:1/-1">
        <div class="flex" style="justify-content:space-between">
          <div class="h">Đề thi Toán 12</div>
          <div class="pill"><span>Trạng thái:</span><strong id="qb12-status">Chuẩn bị</strong></div>
        </div>
        <div id="qb12-quizArea" class="">
          <div class="hr"></div>
          <div class="flex" style="justify-content:space-between">
            <div class="h">Bài làm</div>
            <div class="pill">Thời gian: <span id="qb12-timer" class="timer">--:--</span></div>
          </div>
          <div id="qb12-quizList"></div>
          <div class="hr"></div>
          <div class="flex" style="justify-content:space-between">
            <div>
              <button id="qb12-btnSubmit" class="btn">Nộp bài</button>
              <button id="qb12-btnReset" class="btn secondary">Làm lại (đổi đề)</button>
            </div>
            <div id="qb12-scoreBox" class="pill hide"></div>
          </div>
          <div id="qb12-exportArea" class="hide" style="margin-top:10px">
            <button id="qb12-btnDownloadTxt" class="btn secondary">Tải TXT đề</button>
            <button id="qb12-btnDownloadKey" class="btn secondary">Tải TXT đáp án</button>
          </div>
        </div>
        <div id="qb12-empty" class="muted"></div>
      </main>
    </div>`;
  }

  function mulberry32(seed){ return function(){ let t=seed+=0x6D2B79F5; t=Math.imul(t^(t>>>15),t|1); t^=t+Math.imul(t^(t>>>7),t|61); return ((t^(t>>>14))>>>0)/4294967296; } }
  function randInt(rng,a,b){ return a+Math.floor(rng()*(b-a+1)); }
  function shuffle(rng,arr){ for(let i=arr.length-1;i>0;i--){ const j=Math.floor(rng()*(i+1)); [arr[i],arr[j]]=[arr[j],arr[i]]; } return arr; }
  function round6(x){ return Math.round(x*1e6)/1e6; }
  function formatNumber(x){ return Number.isInteger(x)? String(x) : String(round6(x)); }
  function downloadText(filename, text){ const blob = new Blob([text], {type:"text/plain;charset=utf-8"}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=filename; a.click(); URL.revokeObjectURL(a.href); }
  function stripMath(s){ return s.replace(/\\\\/g,'\\').replace(/\s+/g,' ').trim(); }
  function gcd(a,b){ a=Math.abs(a); b=Math.abs(b); while(b){ const t=b; b=a%b; a=t; } return a||1; }

  const LESSONS = [
    {id:"12-1", name:"Hàm số: đơn điệu & cực trị"},
    {id:"12-2", name:"GTLN – GTNN, tiếp tuyến"},
    {id:"12-3", name:"Hàm mũ – Logarit"},
    {id:"12-4", name:"Số phức"},
    {id:"12-5", name:"Tổ hợp – Xác suất"},
    {id:"12-6", name:"Dãy số, CSC/CSN"},
    {id:"12-7", name:"Giới hạn – Hàm phân thức"},
    {id:"12-8", name:"Nguyên hàm – Tích phân"}
  ];

  function tpl(str, ctx){
    return String(str).replace(/\{\{\s*sign\s+([a-zA-Z_]\w*)\s*\}\}/g, (_,k)=>{
      const v = ctx[k]; return (typeof v==="number" && v>=0) ? "+" : "";
    }).replace(/\{\{\s*([a-zA-Z_]\w*)\s*\}\}/g, (_,k)=> (k in ctx) ? String(ctx[k]) : "");
  }
  function evalExpr(expr, vars, rng){
    const api = {
      abs: Math.abs, sqrt: Math.sqrt, round: Math.round,
      floor: Math.floor, ceil: Math.ceil, min: Math.min, max: Math.max, pow: Math.pow,
      rand: (lo,hi)=> randInt(rng, lo|0, hi|0),
      choice: (...args)=> args.length? args[Math.floor(rng()*args.length)] : undefined,
      iff: (cond, b, c)=> cond ? b : c,
      roundTo: (x, n)=> { const k = Math.pow(10, n|0); return Math.round((+x)*k)/k; },
      formatFrac: (p,q)=>{
        p = Number(p); q = Number(q);
        if (!isFinite(p) || !isFinite(q) || q===0) return "\\\\text{undef}";
        let sign = (p*q<0) ? "-" : "";
        p = Math.abs(p); q = Math.abs(q);
        const g = gcd(p,q);
        const num = Math.trunc(p/g), den = Math.trunc(q/g);
        if (den===1) return sign + String(num);
        return sign + "\\\\frac{"+num+"}{"+den+"}";
      }
    };
    const argNames = [...Object.keys(vars), ...Object.keys(api)];
    const argVals  = [...Object.values(vars), ...Object.values(api)];
    const fn = new Function(...argNames, `return (${expr});`);
    return fn(...argVals);
  }
  function sampleIntExcluding(rng, lo, hi, excludedSet){
    const opts = [];
    for (let v=lo|0; v<=(hi|0); v++){ if (!excludedSet.has(v)) opts.push(v); }
    if (!opts.length){
      let v = randInt(rng, lo|0, hi|0);
      let guard=0;
      while (excludedSet.has(v) && guard<100){ v = randInt(rng, lo|0, hi|0); guard++; }
      return v;
    }
    return opts[Math.floor(rng()*opts.length)];
  }
  function buildDynamicGeneratorFromJSON(item){
    return function(rng){
      const ctx = {};
      for (const [k, spec] of Object.entries(item.vars||{})){
        if (spec && typeof spec.expr === "string"){
          ctx[k] = evalExpr(spec.expr, ctx, rng);
        } else if (spec && Array.isArray(spec.list_vars)){
          const names = spec.list_vars.filter(v=> typeof v === 'string');
          const expr = `choice(${names.join(',')})`;
          ctx[k] = evalExpr(expr, ctx, rng);
        } else if (spec && Array.isArray(spec.list)) {
          const expr = `choice(${spec.list.map(v=> JSON.stringify(v)).join(',')})`;
          ctx[k] = evalExpr(expr, ctx, rng);
        } else {
          const lo = (spec?.min ?? 0), hi = (spec?.max ?? 0);
          const excluded = new Set();
          if (Array.isArray(spec.exclude)) spec.exclude.forEach(v=> excluded.add(v));
          if ('neq' in spec) excluded.add(spec.neq);
          ctx[k] = sampleIntExcluding(rng, lo|0, hi|0, excluded);
        }
      }
      const stemLatex = tpl(item.stem || "", ctx);

      if (item.format === "mcq"){
        let choicesLatex = [];
        if (Array.isArray(item.choices_expr)){
          const arr = item.choices_expr.map(ex => evalExpr(ex, ctx, rng));
          if (item.postprocess==="latexNumber"){
            choicesLatex = arr.map(v => `\\(${formatNumber(v)}\\)`);
          } else if (item.postprocess==="latex"){
            choicesLatex = arr.map(v => `\\(${String(v)}\\)`);
          } else {
            choicesLatex = arr.map(v => String(v));
          }
        } else if (Array.isArray(item.choices)){
          choicesLatex = item.choices.map(s => tpl(s, ctx));
        }
        let correctIndex = item.correct|0;
        if (Array.isArray(item.choices_expr) && Number.isInteger(item.correct)){
          const correctVal = evalExpr(item.choices_expr[item.correct], ctx, rng);
          const correctLatex = item.postprocess==="latexNumber" ? `\\(${formatNumber(correctVal)}\\)`
                              : item.postprocess==="latex" ? `\\(${String(correctVal)}\\)`
                              : String(correctVal);
          choicesLatex = shuffle(rng, choicesLatex.slice());
          correctIndex = choicesLatex.findIndex(x => x === correctLatex);
        } else {
          choicesLatex = shuffle(rng, choicesLatex.slice());
        }
        return {
          id: `json-dyn-mcq-${Math.random().toString(36).slice(2)}`,
          type: "mcq",
          lesson: item.lesson, topic: item.topic||"", difficulty: item.difficulty|0||1,
          stemLatex,
          choicesLatex,
          correctIndex,
          explanationLatex: item.explanation ? tpl(item.explanation, ctx) : ""
        };
      }

      if (item.format === "short"){
        let correctText = item.answer || "";
        if (typeof item.answer_expr === "string"){
          const val = evalExpr(item.answer_expr, ctx, rng);
          correctText = String(round6(val));
        }
        return {
          id: `json-dyn-short-${Math.random().toString(36).slice(2)}`,
          type: "short",
          lesson: item.lesson, topic: item.topic||"", difficulty: item.difficulty|0||1,
          stemLatex,
          correctText,
          explanationLatex: item.explanation ? tpl(item.explanation, ctx) : ""
        };
      }

      if (item.format === "tf"){
        const correctIndex = item.correct_tf ? 0 : 1;
        return {
          id: `json-dyn-tf-${Math.random().toString(36).slice(2)}`,
          type: "tf",
          lesson: item.lesson, topic: item.topic||"", difficulty: item.difficulty|0||1,
          stemLatex,
          correctIndex,
          explanationLatex: item.explanation ? tpl(item.explanation, ctx) : ""
        };
      }

      return { id:`json-unknown-${Math.random().toString(36).slice(2)}`, type:"tf", lesson:item.lesson||"12-1", topic:"Invalid format", difficulty:1, stemLatex:"Định dạng không hợp lệ", correctIndex:1, explanationLatex:"" };
    };
  }
  function buildStaticGeneratorFromJSON(item){
    if (item.format === "mcq"){
      return function(_rng){
        return {
          id: `json-static-mcq-${Math.random().toString(36).slice(2)}`,
          type: "mcq",
          lesson: item.lesson, topic: item.topic||"", difficulty: item.difficulty|0||1,
          stemLatex: item.stem || "",
          choicesLatex: (item.choices||[]).slice(),
          correctIndex: item.correct|0||0,
          explanationLatex: item.explanation || ""
        };
      };
    }
    if (item.format === "short"){
      return function(_rng){
        return {
          id: `json-static-short-${Math.random().toString(36).slice(2)}`,
          type: "short",
          lesson: item.lesson, topic: item.topic||"", difficulty: item.difficulty|0||1,
          stemLatex: item.stem || "",
          correctText: String(item.answer ?? ""),
          explanationLatex: item.explanation || ""
        };
      };
    }
    if (item.format === "tf"){
      return function(_rng){
        return {
          id: `json-static-tf-${Math.random().toString(36).slice(2)}`,
          type: "tf",
          lesson: item.lesson, topic: item.topic||"", difficulty: item.difficulty|0||1,
          stemLatex: item.stem || "",
          correctIndex: item.correct_tf ? 0 : 1,
          explanationLatex: item.explanation || ""
        };
      };
    }
    return function(){ return { id:`json-static-unknown-${Math.random().toString(36).slice(2)}`, type:"tf", lesson:item.lesson||"12-1", topic:"Invalid", difficulty:1, stemLatex:"Định dạng không hợp lệ", correctIndex:1, explanationLatex:"" }; };
  }

  const TEMPLATE_BANK = [];
  let seed = (BOOT.seed|0) || (Date.now() & 0xffffffff);

  if (BOOT.questions && Array.isArray(BOOT.questions.questions)){
    try {
      const json = BOOT.questions;
      for (const item of json.questions){
        if (item.type === "dynamic") TEMPLATE_BANK.push(buildDynamicGeneratorFromJSON(item));
        else if (item.type === "static") TEMPLATE_BANK.push(buildStaticGeneratorFromJSON(item));
      }
    } catch(e){ console.warn('Preload failed', e); }
  }
  const HAS_FIXED_QUIZ = !!(BOOT.questions && Array.isArray(BOOT.questions.questions) && BOOT.questions.questions.length);

  let renderedRows = [];
  function materializeBank(){
    renderedRows = TEMPLATE_BANK.map((gen, idx) => {
      const tmpRng = mulberry32(seed ^ (idx*7919 + 137));
      const sample = gen(tmpRng);
      return { idx, lesson: sample.lesson, topic: sample.topic, difficulty: sample.difficulty, preview: sample.stemLatex, type: sample.type, gen };
    });
  }

  if (isBuilder){
    // --- builder wiring (simplified) ---
    const lessonList = root.querySelector('#qb12-lessonList');
    const bankView = root.querySelector('#qb12-bankView');
    const quizArea = root.querySelector('#qb12-quizArea');
    const quizList = root.querySelector('#qb12-quizList');
    const timerEl = root.querySelector('#qb12-timer');
    const scoreBox = root.querySelector('#qb12-scoreBox');

    function mapLesson(id){ const L={"12-1":"Hàm số: đơn điệu & cực trị","12-2":"GTLN – GTNN, tiếp tuyến","12-3":"Hàm mũ – Logarit","12-4":"Số phức","12-5":"Tổ hợp – Xác suất","12-6":"Dãy số, CSC/CSN","12-7":"Giới hạn – Hàm phân thức","12-8":"Nguyên hàm – Tích phân"}; return L[id]||id; }
    function updateSelCount(){ root.querySelector('#qb12-selCount').textContent = selectedTemplates.size; }
    function renderLessons(){
      lessonList.innerHTML = LESSONS.map(L => `
        <label style="display:flex;align-items:center;margin:6px 0">
          <input type="checkbox" class="qb12-lessonChk" value="${L.id}" checked />
          <span>${L.name}</span>
        </label>`).join("");
      [...root.querySelectorAll('.qb12-lessonChk')].forEach(el=> el.addEventListener('change', renderBank));
    }
    let selectedTemplates = new Set();
    function renderBank(){
      const activeLessons = new Set([...root.querySelectorAll('.qb12-lessonChk:checked')].map(e=>e.value));
      const rows = renderedRows.filter(r => activeLessons.has(r.lesson));
      bankView.innerHTML = rows.map(r => `
        <div class="qrow">
          <div class="flex" style="justify-content:space-between">
            <label class="flex" style="gap:8px;align-items:center">
              <input type="checkbox" class="qb12-rowSel" data-idx="${r.idx}" ${selectedTemplates.has(r.idx)?'checked':''}/>
              <span><strong>${mapLesson(r.lesson)}</strong> • ${r.topic} <span class="tag">${r.type.toUpperCase()}</span></span>
            </label>
            <span class="muted">Độ khó: ${"★".repeat(r.difficulty)}${"☆".repeat(4-r.difficulty)}</span>
          </div>
          <div class="muted">Xem nhanh: \\(${String(r.preview).replace(/\$\$/g,'')}\\)</div>
        </div>
      `).join("");
      if (window.MathJax && window.MathJax.typesetPromise) MathJax.typesetPromise([bankView]);
      updateSelCount();
    }
    bankView.addEventListener('change', e=>{
      if(e.target.classList.contains('qb12-rowSel')){
        const idx=+e.target.dataset.idx;
        if(e.target.checked) selectedTemplates.add(idx); else selectedTemplates.delete(idx);
        updateSelCount();
      }
    });
    document.getElementById('qb12-btnSelectAll').addEventListener('click', ()=>{
      const activeLessons = new Set([...root.querySelectorAll('.qb12-lessonChk:checked')].map(e=>e.value));
      renderedRows.forEach(r => { if(activeLessons.has(r.lesson)) selectedTemplates.add(r.idx); });
      renderBank();
    });
    document.getElementById('qb12-btnClear').addEventListener('click', ()=>{ selectedTemplates.clear(); renderBank(); });
    document.getElementById('qb12-btnRemove').addEventListener('click', ()=>{
      const remain = TEMPLATE_BANK.filter((_,i)=>!selectedTemplates.has(i));
      TEMPLATE_BANK.length = 0; TEMPLATE_BANK.push(...remain);
      selectedTemplates.clear(); materializeBank(); renderBank();
    });
    document.getElementById('qb12-btnAddRand').addEventListener('click', ()=>{
      const n = +document.getElementById('qb12-numRand').value || 1;
      const activeLessons = new Set([...root.querySelectorAll('.qb12-lessonChk:checked')].map(e=>e.value));
      const pool = renderedRows.filter(r=>activeLessons.has(r.lesson)).map(r=>r.idx);
      const rng = mulberry32(seed ^ 0x9e3779b1);
      for(let i=0;i<n;i++){ selectedTemplates.add(pool[Math.floor(rng()*pool.length)]); }
      renderBank();
    });

    function tickTimer(active){
      if(!active || !active.deadline) return false;
      const remain = Math.max(0, Math.floor((active.deadline - Date.now())/1000));
      const m = String(Math.floor(remain/60)).padStart(2,'0');
      const s = String(remain%60).padStart(2,'0');
      timerEl.textContent = `${m}:${s}`;
      return remain>0;
    }
    function isCorrect(q, val){
      if(q.type==='mcq' || q.type==='tf') return String(q.correctIndex)===String(val);
      if(q.type==='short'){
        const a=parseFloat(String(val).replace(',','.')), b=parseFloat(q.correctText);
        return isFinite(a) && Math.abs(a-b)<=1e-6;
      }
      return false;
    }
    function renderOne(q,i){
      const head = `<div class="flex" style="justify-content:space-between">
          <div><strong>Câu ${i+1}</strong> • <span class="tag">${q.lesson||''}</span> <span class="tag">${q.type.toUpperCase()}</span></div>
          <div class="muted">Độ khó: ${"★".repeat(q.difficulty)}${"☆".repeat(4-q.difficulty)}</div>
        </div>`;
      const stem = `<div class="qstem">\\[ ${q.stemLatex} \\]</div>`;
      let body = "";
      if(q.type==='mcq'){
        body = q.choicesLatex.map((ch,j)=>`
          <label><input type="radio" name="q${i}" value="${j}"/>
            <span>(${String.fromCharCode(65+j)}) \\( ${String(ch).replace(/^\\\(|\\\)$/g,'')} \\)</span>
          </label>`).join("");
        body = `<div class="ans">${body}</div>`;
      } else if(q.type==='tf'){
        body = `<div class="ans">
          <label><input type="radio" name="q${i}" value="0"/> Đúng</label>
          <label><input type="radio" name="q${i}" value="1"/> Sai</label>
        </div>`;
      } else if(q.type==='short'){
        body = `<div class="ans"><label class="flex" style="gap:8px">Đáp số: <input type="text" name="q${i}" placeholder="Nhập số..." /></label></div>`;
      }
      const fb = `<div id="qb12-fb${i}" class="muted" style="margin-top:8px"></div>`;
      return `<div class="qrow">${head}${stem}${body}${fb}</div>`;
    }
    function renderQuiz(active){
      quizList.innerHTML = active.items.map((q,i)=> renderOne(q,i)).join("");
      if (window.MathJax && window.MathJax.typesetPromise) MathJax.typesetPromise([quizList]);
      quizList.querySelectorAll('.ans input').forEach(el=>{
        el.addEventListener('change', e=>{
          const key = e.target.name;
          active.answers[key] = e.target.value;
        });
      });
    }
    function startQuiz(){
      if(selectedTemplates.size===0){ alert("Hãy chọn ít nhất 1 mẫu câu hỏi."); return; }
      const mode = document.getElementById('qb12-mode').value;
      const mins = +document.getElementById('qb12-minutes').value || 15;
      const picks = [...selectedTemplates].map(i=> renderedRows.find(r=>r.idx===i));
      const items = picks.map((r,k)=>{
        const localRng = mulberry32((seed ^ (r.idx*104729+7)) & 0xffffffff ^ (k*7919));
        const q = r.gen(localRng);
        if(q.type==='mcq') q.choicesLatex = shuffle(localRng, q.choicesLatex.slice());
        return q;
      });
      const active = { items, mode, deadline: mode==='exam' ? Date.now()+mins*60*1000 : null, answers:{} };
      window.__QB12_ACTIVE__ = active;
      quizArea.classList.remove('hide');
      scoreBox.classList.add('hide');
      document.getElementById('qb12-exportArea').classList.add('hide');
      renderQuiz(active);
      if(mode==='exam'){
        tickTimer(active);
        if (window.__QB12_TIMER__) clearInterval(window.__QB12_TIMER__);
        window.__QB12_TIMER__ = setInterval(()=>{ if(!tickTimer(active)){ clearInterval(window.__QB12_TIMER__); alert("Hết giờ! Hệ thống tự nộp bài."); } }, 500);
      } else {
        document.getElementById('qb12-timer').textContent = "Practice";
      }
    }
    document.getElementById('qb12-btnStart').onclick = ()=> startQuiz();
    document.getElementById('qb12-btnReseed').onclick = ()=>{ seed = (Date.now() ^ (Math.random()*1e9|0)) & 0xffffffff; materializeBank(); renderBank(); };
    document.getElementById('qb12-btnReset').onclick = ()=> { document.getElementById('qb12-btnReseed').click(); startQuiz(); };
    document.getElementById('qb12-importBank').onchange = async (e)=>{
      const file = e.target.files[0]; if(!file) return;
      try{
        const json = JSON.parse(await file.text());
        if (!Array.isArray(json.questions)) throw new Error("Thiếu mảng 'questions'.");
        let added = 0;
        for (const item of json.questions){
          if (!item || !item.format || !item.lesson){ console.warn("Bỏ qua item không hợp lệ:", item); continue; }
          if (item.type === "dynamic"){ TEMPLATE_BANK.push(buildDynamicGeneratorFromJSON(item)); added++; }
          else if (item.type === "static"){ TEMPLATE_BANK.push(buildStaticGeneratorFromJSON(item)); added++; }
        }
        materializeBank(); renderBank();
        alert(`Import xong: đã thêm ${added} mẫu vào ngân hàng (chỉ cho phiên hiện tại).`);
        e.target.value = "";
      } catch(err){
        console.error(err);
        alert("JSON không hợp lệ hoặc sai schema.");
      }
    };
    function initBuilder(){
      materializeBank();
      renderLessons();
      renderBank();
      if (window.MathJax && window.MathJax.typesetPromise) MathJax.typesetPromise([root]);
    }
    initBuilder();
    return;
  }

  // --- front-only wiring ---
  const timerEl = root.querySelector('#qb12-timer');
  const quizList = root.querySelector('#qb12-quizList');
  const scoreBox = root.querySelector('#qb12-scoreBox');
  const emptyEl = root.querySelector('#qb12-empty');
  const statusEl = root.querySelector('#qb12-status');

  if (!(BOOT.questions && Array.isArray(BOOT.questions.questions) && BOOT.questions.questions.length)){
    emptyEl.innerHTML = "Chưa có dữ liệu đề. Dùng shortcode: <code>[quizbank12 quiz=ID variant=A]</code> và đảm bảo đề đã tick câu hỏi.";
    statusEl.textContent = "Thiếu dữ liệu";
    return;
  }

  function generateItems(){
    materializeBank();
    const picks = renderedRows;
    const items = picks.map((r,k)=>{
      const localRng = mulberry32((seed ^ (r.idx*104729+7)) & 0xffffffff ^ (k*7919));
      const q = r.gen(localRng);
      if(q.type==='mcq') q.choicesLatex = shuffle(localRng, q.choicesLatex.slice());
      return q;
    });
    return items;
  }
  function tickTimer(active){
    if(!active || !active.deadline) return false;
    const remain = Math.max(0, Math.floor((active.deadline - Date.now())/1000));
    const m = String(Math.floor(remain/60)).padStart(2,'0');
    const s = String(remain%60).padStart(2,'0');
    timerEl.textContent = `${m}:${s}`;
    return remain>0;
  }
  function isCorrect(q, val){
    if(q.type==='mcq' || q.type==='tf') return String(q.correctIndex)===String(val);
    if(q.type==='short'){
      const a=parseFloat(String(val).replace(',','.')), b=parseFloat(q.correctText);
      return isFinite(a) && Math.abs(a-b)<=1e-6;
    }
    return false;
  }
  function renderOne(q,i){
    const head = `<div class="flex" style="justify-content:space-between">
        <div><strong>Câu ${i+1}</strong> • <span class="tag">${q.lesson||''}</span> <span class="tag">${q.type.toUpperCase()}</span></div>
        <div class="muted">Độ khó: ${"★".repeat(q.difficulty)}${"☆".repeat(4-q.difficulty)}</div>
      </div>`;
    const stem = `<div class="qstem">\\[ ${q.stemLatex} \\ ]</div>`.replace('\\ ]','\\]');
    let body = "";
    if(q.type==='mcq'){
      body = q.choicesLatex.map((ch,j)=>`
        <label><input type="radio" name="q${i}" value="${j}"/>
          <span>(${String.fromCharCode(65+j)}) \\( ${String(ch).replace(/^\\\(|\\\)$/g,'')} \\)</span>
        </label>`).join("");
      body = `<div class="ans">${body}</div>`;
    } else if(q.type==='tf'){
      body = `<div class="ans">
        <label><input type="radio" name="q${i}" value="0"/> Đúng</label>
        <label><input type="radio" name="q${i}" value="1"/> Sai</label>
      </div>`;
    } else if(q.type==='short'){
      body = `<div class="ans"><label class="flex" style="gap:8px">Đáp số: <input type="text" name="q${i}" placeholder="Nhập số..." /></label></div>`;
    }
    const fb = `<div id="qb12-fb${i}" class="muted" style="margin-top:8px"></div>`;
    return `<div class="qrow">${head}${stem}${body}${fb}</div>`;
  }
  function renderQuiz(active){
    quizList.innerHTML = active.items.map((q,i)=> renderOne(q,i)).join("");
    if (window.MathJax && window.MathJax.typesetPromise) MathJax.typesetPromise([quizList]);
    quizList.querySelectorAll('.ans input').forEach(el=>{
      el.addEventListener('change', e=>{
        const key = e.target.name;
        active.answers[key] = e.target.value;
      });
    });
  }
  function startQuizFront(){
    const mode = CFG.mode || 'exam';
    const mins = +CFG.minutes || 15;
    const items = generateItems();
    const active = { items, mode, deadline: mode==='exam' ? Date.now()+mins*60*1000 : null, answers:{} };
    window.__QB12_ACTIVE__ = active;
    renderQuiz(active);
    if(mode==='exam'){
      statusEl.textContent = "Đang thi";
      tickTimer(active);
      if (window.__QB12_TIMER__) clearInterval(window.__QB12_TIMER__);
      window.__QB12_TIMER__ = setInterval(()=>{ if(!tickTimer(active)){ clearInterval(window.__QB12_TIMER__); alert("Hết giờ! Hệ thống tự nộp bài."); } }, 500);
    } else {
      statusEl.textContent = "Practice";
      timerEl.textContent = "Practice";
    }
  }

  document.getElementById('qb12-btnSubmit').onclick = ()=>{
    const active = window.__QB12_ACTIVE__; if(!active) return;
    const n = active.items.length;
    let correct = 0;
    for(let i=0;i<n;i++){
      const q=active.items[i], key='q'+i, val=active.answers[key];
      const ok=isCorrect(q,val); if(ok) correct++;
      const fb = document.getElementById('qb12-fb'+i);
      fb.innerHTML = ok ? `<span class="ok">✓ Chính xác.</span>${q.explanationLatex?`<div>\\(${q.explanationLatex}\\)</div>`:''}`
                        : `<span class="bad">✗ Sai.</span>${q.explanationLatex?`<div>\\(${q.explanationLatex}\\)</div>`:''}`;
    }
    if (window.MathJax && window.MathJax.typesetPromise) MathJax.typesetPromise([quizList]);
    const pct = Math.round(correct/n*100);
    scoreBox.classList.remove('hide');
    scoreBox.innerHTML = `Điểm: <strong>${correct}/${n}</strong> (${pct}%)`;
    document.getElementById('qb12-exportArea').classList.remove('hide');
  };
  document.getElementById('qb12-btnReset').onclick = ()=>{
    seed = (Date.now() ^ (Math.random()*1e9|0)) & 0xffffffff;
    startQuizFront();
  };

  document.getElementById('qb12-btnDownloadTxt').onclick = ()=>{
    const active = window.__QB12_ACTIVE__; if(!active) return;
    const lines=["PHẦN I: TRẮC NGHIỆM (1 lựa chọn)"]; let num=1;
    for(const q of active.items){
      if(q.type==='mcq' || q.type==='tf'){
        lines.push(`Câu ${num}. ${stripMath(q.stemLatex)}`);
        if(q.type==='mcq'){
          q.choicesLatex.forEach((ch,i)=> lines.push(`   ${String.fromCharCode(65+i)}. ${stripMath(ch)}`));
        } else { lines.push("   A. Đúng   B. Sai"); }
        num++;
      }
    }
    downloadText("De_TN_Toan12.txt", lines.join("\n"));
  };
  document.getElementById('qb12-btnDownloadKey').onclick = ()=>{
    const active = window.__QB12_ACTIVE__; if(!active) return;
    const lines=["ĐÁP ÁN:"]; let num=1;
    for(const q of active.items){
      if(q.type==='mcq'){ lines.push(`Câu ${num}: ${String.fromCharCode(65+q.correctIndex)}`); num++; }
      else if(q.type==='tf'){ lines.push(`Câu ${num}: ${q.correctIndex===0?'Đúng':'Sai'}`); num++; }
    }
    downloadText("Dap_an_TN_Toan12.txt", lines.join("\n"));
  };

  // boot
  startQuizFront();
})();