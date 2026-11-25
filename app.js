const fmt = n => Number(n || 0).toLocaleString('en-EG') + ' EGP';
let monthlyChart, categoryChart;

function params() {
  const p = new URLSearchParams();
  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;
  const city = document.getElementById('city').value;
  if (from) p.set('from', from);
  if (to) p.set('to', to);
  if (city) p.set('city', city);
  return p;
}

async function getJSON(action) {
  const res = await fetch(`api.php?action=${action}&` + params());
  if (!res.ok) throw new Error(`API error ${res.status}`);
  return res.json();
}

async function loadKpis() {
  const k = await getJSON('kpis');
  document.getElementById('kpi-revenue').textContent = fmt(k.revenue);
  document.getElementById('kpi-orders').textContent = Number(k.orders).toLocaleString();
  document.getElementById('kpi-aov').textContent = fmt(k.avg_order_value);
}

async function loadMonthly() {
  const rows = await getJSON('monthly');
  const data = {
    labels: rows.map(r => r.month),
    datasets: [{ label: 'Revenue (EGP)', data: rows.map(r => r.revenue),
                 borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .3 }]
  };
  if (monthlyChart) { monthlyChart.data = data; monthlyChart.update(); }
  else monthlyChart = new Chart(document.getElementById('chart-monthly'), { type: 'line', data });
}

async function loadCategories() {
  const rows = await getJSON('categories');
  const data = {
    labels: rows.map(r => r.category),
    datasets: [{ data: rows.map(r => r.revenue),
                 backgroundColor: ['#2563eb','#16a34a','#f59e0b','#dc2626','#7c3aed'] }]
  };
  if (categoryChart) { categoryChart.data = data; categoryChart.update(); }
  else categoryChart = new Chart(document.getElementById('chart-category'),
    { type: 'doughnut', data, options: { plugins: { legend: { position: 'bottom' } } } });
}

async function loadTopCustomers() {
  const rows = await getJSON('top_customers');
  document.querySelector('#top-customers tbody').innerHTML = rows.map(r =>
    `<tr><td>${r.customer}</td><td>${r.city}</td><td>${r.orders}</td><td>${fmt(r.revenue)}</td></tr>`
  ).join('');
}

async function loadCities() {
  const rows = await getJSON('cities');
  const sel = document.getElementById('city');
  rows.forEach(r => {
    const o = document.createElement('option');
    o.value = o.textContent = r.city;
    sel.appendChild(o);
  });
}

async function refresh() {
  await Promise.all([loadKpis(), loadMonthly(), loadCategories(), loadTopCustomers()]);
}

document.getElementById('apply').addEventListener('click', refresh);
loadCities().then(refresh);
