<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @vite('resources/js/app.js')

  @inertiaHead

  {{-- @routes --}}
  <link rel="icon" type="image/png" href="{{ asset('/icons/logo.svg') }}">


</head>

<body>
  @inertia
</body>

</html>
