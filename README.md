# Portfolio Pulse

A static site portfolio dashboard built with Vue 3, Vite, Tailwind CSS, and Chart.js, with a PHP backend for processing stock transactions.

## Features
- Visualize your stock portfolio with a pie chart, tables, and metrics
- Activity feed and latest blog post integration
- PHP script to process your transaction history and generate frontend data
- Social and SEO meta tags, responsive design

## Setup Instructions

1. **Create or update your transaction data**
   - Edit or add your stock transactions in `resources/transaction.csv` (see the sample for format)

2. **Generate the portfolio data**
   - Run the PHP generator to process your CSV and output `public/data.json`:
     ```sh
     php generator.php
     ```

3. **Build the frontend**
   - Install dependencies (if you haven't):
     ```sh
     npm install
     ```
   - Build the static site:
     ```sh
     npm run build
     ```

4. **Preview or deploy**
   - Use `npm run preview` to locally preview the production build
   - Deploy the contents of the `dist/` directory to your static hosting

## Development
- Use `npm run dev` for local development with hot reload
- Update `resources/transaction.csv` and re-run `php generator.php` whenever your data changes

---

Feel free to customize the dashboard, styles, or backend logic to fit your needs!
