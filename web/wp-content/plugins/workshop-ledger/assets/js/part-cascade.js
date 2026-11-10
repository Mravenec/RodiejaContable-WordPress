/**
 * Part Add form: mode toggle + Brand→Model→Generation cascade (Read-Only parity, native).
 */
(function () {
  'use strict';
  var data = window.wlPartCascade || { models: [], generations: [], i18n: {} };
  var modoRadios = document.querySelectorAll('input[name="wl_modo"]');
  var withVeh = document.getElementById('wl-part-with-vehicle');
  var withoutVeh = document.getElementById('wl-part-without-vehicle');
  var marca = document.getElementById('wl_ui_marca_id');
  var modelo = document.getElementById('wl_ui_modelo_id');
  var gen = document.getElementById('wl_generacion_id');
  var costo = document.getElementById('wl_precio_costo');
  var venta = document.getElementById('wl_precio_venta');

  function currentModo() {
    var checked = document.querySelector('input[name="wl_modo"]:checked');
    return checked ? checked.value : 'con_vehiculo';
  }

  function syncMode() {
    var modo = currentModo();
    if (withVeh) {
      withVeh.hidden = modo !== 'con_vehiculo';
    }
    if (withoutVeh) {
      withoutVeh.hidden = modo !== 'sin_vehiculo';
    }
  }

  function fillSelect(sel, items, placeholder) {
    if (!sel) {
      return;
    }
    var cur = sel.value;
    sel.innerHTML = '';
    var opt0 = document.createElement('option');
    opt0.value = '0';
    opt0.textContent = placeholder || '—';
    sel.appendChild(opt0);
    (items || []).forEach(function (it) {
      var o = document.createElement('option');
      o.value = String(it.id);
      o.textContent = it.title;
      if (String(it.id) === String(cur)) {
        o.selected = true;
      }
      sel.appendChild(o);
    });
  }

  function modelsForMarca(marcaId) {
    return (data.models || []).filter(function (m) {
      return String(m.marca_id) === String(marcaId);
    });
  }

  function gensForModelo(modeloId) {
    return (data.generations || []).filter(function (g) {
      return String(g.modelo_id) === String(modeloId);
    });
  }

  function onMarca() {
    fillSelect(modelo, modelsForMarca(marca.value), data.i18n.selectModel);
    fillSelect(gen, [], data.i18n.selectGeneration);
  }

  function onModelo() {
    fillSelect(gen, gensForModelo(modelo.value), data.i18n.selectGeneration);
  }

  function onCosto() {
    if (!costo || !venta) {
      return;
    }
    var c = parseFloat(costo.value || '0');
    if (!isFinite(c) || c <= 0) {
      return;
    }
    if (!venta.value || venta.dataset.auto === '1') {
      venta.value = (Math.round(c * 1.5 * 100) / 100).toFixed(2);
      venta.dataset.auto = '1';
    }
  }

  modoRadios.forEach(function (r) {
    r.addEventListener('change', syncMode);
  });
  if (marca) {
    marca.addEventListener('change', onMarca);
  }
  if (modelo) {
    modelo.addEventListener('change', onModelo);
  }
  if (costo) {
    costo.addEventListener('input', onCosto);
  }
  if (venta) {
    venta.addEventListener('input', function () {
      venta.dataset.auto = '0';
    });
  }

  syncMode();
  if (marca && marca.value && marca.value !== '0') {
    var keepM = modelo ? modelo.value : '0';
    var keepG = gen ? gen.value : '0';
    fillSelect(modelo, modelsForMarca(marca.value), data.i18n.selectModel);
    if (modelo) {
      modelo.value = keepM;
    }
    fillSelect(gen, gensForModelo(modelo ? modelo.value : '0'), data.i18n.selectGeneration);
    if (gen) {
      gen.value = keepG;
    }
  } else if (marca) {
    fillSelect(modelo, [], data.i18n.selectModel);
    fillSelect(gen, [], data.i18n.selectGeneration);
  }
})();
