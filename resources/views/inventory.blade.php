<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Inventory & POS</title>

  <!-- CSRF token for JS -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Vite / assets -->
  @vite(['resources/js/app.js'])
</head>
<body style="background:#f3f4f6; min-height:100vh; margin:0;">
  <div id="app" style="min-height:100vh; display:flex; align-items:flex-start; justify-content:center; padding:18px;">
    <inventory-app data-csrf="{{ csrf_token() }}"></inventory-app>
  </div>

</body>
</html>
