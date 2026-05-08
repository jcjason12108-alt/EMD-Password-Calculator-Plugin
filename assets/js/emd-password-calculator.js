(function(){
  'use strict';

  function pad2(n){ return String(n).padStart(2,'0'); }

  function compute(date){
    // Build MM/DD/YY parts in UTC
    const m = pad2(date.getUTCMonth()+1);
    const d = pad2(date.getUTCDate());
    const yFull = date.getUTCFullYear();
    const y = String(yFull).slice(-2);

    // Digit sum of MM/DD/YY (digits only)
    const digits = (m + d + y).split('');
    const sum = digits.reduce((acc, ch) => acc + parseInt(ch, 10), 0);
    const lastDigit = sum % 10;

    // Last digit of year (UTC year)
    const lastDigitYear = yFull % 10;

    // Day reversed (e.g., "04" -> "40")
    const dayReversed = d.split('').reverse().join('');

    // Password format: last digit of digit sum, last digit of year, day reversed
    const password = `${lastDigit}${lastDigitYear}${dayReversed}`;

    return {
      m, d, y, yFull,
      sum, lastDigit, lastDigitYear, dayReversed, password
    };
  }

  function escapeHtml(value){
    return String(value).replace(/[&<>"']/g, function(ch){
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[ch];
    });
  }

  
function renderCalc(el, data){
  // Build digits arrays
  const mDigits = data.m.split('');  // 2 digits
  const dDigits = data.d.split('');  // 2 digits
  const yDigits = data.y.split('');  // 2 digits
  const all = mDigits.concat(dDigits, yDigits); // length 6

  // Color positions 3,4,6 (1-based) => indices 2,3,5
  const redIdx = new Set([2,3,5]);
  const coloredLeft = all.map((ch, i) => redIdx.has(i) ? `<span class="emd-red">${ch}</span>` : `<span>${ch}</span>`).join('+');

  // Sum string with ones digit red
  const sumStr = String(data.sum);
  let coloredRight = '';
  if (sumStr.length === 1){
    coloredRight = `<span class="emd-red">${sumStr}</span>`;
  } else {
    const tens = sumStr.slice(0, -1);
    const ones = sumStr.slice(-1);
    coloredRight = `<span>${tens}</span><span class="emd-red">${ones}</span>`;
  }

  // Password is still lastDigit(sum) + lastDigit(year) + reversed(day)
  el.innerHTML = `
    <div class="emd-lines">
      <div class="emd-line"><strong>${escapeHtml(EMD_PWC_I18N.example)}:</strong></div>
      <div class="emd-line">${escapeHtml(EMD_PWC_I18N.todaysDate)} ${data.m}/${data.d}/${data.y}</div>
      <div class="emd-line">${coloredLeft} = ${coloredRight}</div>
      <div class="emd-line">${escapeHtml(EMD_PWC_I18N.writeDown)}:</div>
      <div class="emd-line emd-red-seq"><span class="emd-red">${data.lastDigit}</span><span class="emd-red">${data.lastDigitYear}</span><span class="emd-red">${data.dayReversed}</span></div>
      <div class="emd-line"><strong>${escapeHtml(EMD_PWC_I18N.passwordEq)} = ${data.password}</strong></div>
      <div class="emd-line emd-legend">(${escapeHtml(EMD_PWC_I18N.legend)})</div>
    </div>
  `;
}


  function setToggle(button, panel){
    function update(expanded){
      button.setAttribute('aria-expanded', String(expanded));
      if(expanded){ panel.removeAttribute('hidden'); }
      else { panel.setAttribute('hidden', ''); }
      button.textContent = expanded ? EMD_PWC_I18N.hideCalc : EMD_PWC_I18N.showCalc;
    }
    update(false);
    button.addEventListener('click', (e)=>{
      e.preventDefault();
      const expanded = button.getAttribute('aria-expanded') === 'true';
      update(!expanded);
    });
  }

  function init(){
    const todayLabel = document.getElementById('emd-pwc-today-label');
    const todayPass  = document.getElementById('emd-pwc-today-pass');
    const todayToggle= document.getElementById('emd-pwc-today-toggle');
    const todayCalc  = document.getElementById('emd-pwc-today-calc');

    const yestLabel  = document.getElementById('emd-pwc-yest-label');
    const yestPass   = document.getElementById('emd-pwc-yest-pass');
    const yestToggle = document.getElementById('emd-pwc-yest-toggle');
    const yestCalc   = document.getElementById('emd-pwc-yest-calc');

    if(!todayLabel || !todayPass || !todayToggle || !todayCalc) return;

    // Today (server UTC if provided; fallback to client UTC)
    const nowUtc = (typeof EMD_PWC_DATA !== 'undefined' && EMD_PWC_DATA && EMD_PWC_DATA.now)
      ? new Date(EMD_PWC_DATA.now * 1000)
      : new Date();
    const datToday = compute(nowUtc);

    todayLabel.textContent = EMD_PWC_I18N.today;
    todayPass.textContent  = datToday.password;
    renderCalc(todayCalc, datToday);
    setToggle(todayToggle, todayCalc);

    // Yesterday
    const yestUtc = (typeof EMD_PWC_DATA !== 'undefined' && EMD_PWC_DATA && EMD_PWC_DATA.yesterday)
      ? new Date(EMD_PWC_DATA.yesterday * 1000)
      : new Date(nowUtc.getTime() - 86400000);
    const datYest = compute(yestUtc);

    yestLabel.textContent = EMD_PWC_I18N.yesterday;
    yestPass.textContent  = datYest.password;
    renderCalc(yestCalc, datYest);
    setToggle(yestToggle, yestCalc);
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
