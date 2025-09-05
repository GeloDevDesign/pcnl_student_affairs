@props(['entity' => null])

<form method="POST" class="" $action-request={{ $action }}>
  @csrf
  @if (optional($entity)->exists)
    @method('PUT')
  @endif

  <div>
    {{ $slot }}
  </div>

</form>
