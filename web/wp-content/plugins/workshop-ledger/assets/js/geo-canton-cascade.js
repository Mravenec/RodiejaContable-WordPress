/**
 * Province → canton cascade (Costa Rica Options map).
 */
(function () {
  'use strict';
  var map = window.wlGeoCantones || {};
  var prov = document.getElementById('wl_provincia');
  var canton = document.getElementById('wl_canton');
  if (!prov || !canton) {
    return;
  }

  function fillCantones(province, keep) {
    var list = map[province] || [];
    var cur = keep || canton.value;
    canton.innerHTML = '';
    var opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = '—';
    canton.appendChild(opt0);
    list.forEach(function (name) {
      var o = document.createElement('option');
      o.value = name;
      o.textContent = name;
      if (name === cur) {
        o.selected = true;
      }
      canton.appendChild(o);
    });
  }

  prov.addEventListener('change', function () {
    fillCantones(prov.value, '');
  });
})();
