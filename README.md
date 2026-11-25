# Sales Insights Dashboard (PHP + MySQL + Chart.js)

An interactive sales analytics dashboard for a small e-commerce business. A PHP
JSON API aggregates data from MySQL, and the frontend renders KPI cards, charts
and a top-customers table with Chart.js. Date-range and city filters reload
every widget.

## Features

- KPI cards: total revenue, order count, average order value
- Monthly revenue trend (line chart)
- Revenue share by product category (doughnut chart)
- Top 10 customers by revenue
- Filters: date range + city, applied across all widgets
- Prepared statements (PDO) on every query

## Tech stack

- **Backend:** PHP 8 (PDO) exposing a JSON API (`api.php`)
- **Database:** MySQL 8 (4 normalized tables: customers, products, orders, order_items)
- **Frontend:** HTML, CSS, vanilla JavaScript, Chart.js 4 (CDN)

## Project layout

```
index.html    dashboard page
app.js        fetches the API and renders charts/tables
style.css     dashboard styling
api.php       JSON endpoints: kpis, monthly, categories, top_customers, cities
config.php    database connection settings
schema.sql    database + table definitions
seed.sql      synthetic demo data (150 customers, 420 orders)
```

## Run it locally

1. Install XAMPP (or any PHP 8 + MySQL stack) and start Apache and MySQL.
2. Copy this folder into `htdocs` (e.g. `C:\xampp\htdocs\sales-insights-dashboard`).
3. In phpMyAdmin, run `schema.sql`, then `seed.sql`.
4. If your MySQL credentials differ from the XAMPP defaults, edit `config.php`.
5. Open `http://localhost/sales-insights-dashboard/`.

## Notes

- All data is synthetic and generated for demonstration. No real customer data.
- The API returns JSON only; the frontend never builds SQL, all filtering is
  done server-side with bound parameters.
