/**
 * Brand → Model → Generation cascade on classic vehicle edit (Read-Only parity).
 */
(function () {
  'use strict';
  var data = window.wlVehicleCascade || { models: [], generations: [] };
  var marca = document.getElementById('wl_ui_marca_id');
  var modelo = document.getElementById('wl_ui_modelo_id');
  var gen = document.getElementById('wl_generacion_id');
  var anio = document.getElementById('wl_anio');
  if (!marca || !modelo || !gen) {
    return;
  }

  function fillSelect(sel, items, placeholder) {
    var cur = sel.value;
    sel.innerHTML = '';
    var opt0 = document.createElement('option');
    opt0.value = '0';
    opt0.textContent = placeholder;
    sel.appendChild(opt0);
    items.forEach(function (it) {
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
    var mid = marca.value;
    fillSelect(modelo, modelsForMarca(mid), data.i18n.selectModel);
    fillSelect(gen, [], data.i18n.selectGeneration);
  }

  function onModelo() {
    fillSelect(gen, gensForModelo(modelo.value), data.i18n.selectGeneration);
  }

  function onGen() {
    var id = gen.value;
    var g = (data.generations || []).find(function (x) {
      return String(x.id) === String(id);
    });
    if (!g || !anio) {
      return;
    }
    if (!anio.value || anio.value === '0') {
      anio.value = String(g.anio_inicio || new Date().getFullYear());
    }
    anio.min = g.anio_inicio || '';
    anio.max = g.anio_fin || '';
  }

  marca.addEventListener('change', onMarca);
  modelo.addEventListener('change', onModelo);
  gen.addEventListener('change', onGen);

  // Initial filter if editing
  if (marca.value && marca.value !== '0') {
    var keepModelo = modelo.value;
    var keepGen = gen.value;
    fillSelect(modelo, modelsForMarca(marca.value), data.i18n.selectModel);
    modelo.value = keepModelo;
    fillSelect(gen, gensForModelo(modelo.value), data.i18n.selectGeneration);
    gen.value = keepGen;
    onGen();
  }
})();
