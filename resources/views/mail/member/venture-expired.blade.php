<x-mail::message>
  # Emprendimiento Vencido

  Su emprendimiento titulado "{{ $venture->title }}" venció el día {{ $venture->expires_at }}

  @if($venture->is_extendable)
    Usted tiene 5 días para extender el emprendimiento. De no extender su emprendimiento,
    el mismo será eliminado.
  @endif

  <x-mail::button :url="''">
    Acceder
  </x-mail::button>

  Gracias,<br>
  {{ config('app.name') }}
</x-mail::message>
