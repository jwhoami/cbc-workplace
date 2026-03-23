<x-mail::message>
  # Emprendimiento Vencido

  Su emprendimiento titulado "{{ $venture->title }}" venció el día {{ $venture->expires_at }}

  @if($venture->is_extendable)
    Usted tiene 30 días para extender el emprendimiento. De no extender su emprendimiento,
    el mismo será eliminado.
  @endif

  # Entrepreneurship Expired

  Your entrepreneurship entitled "{{ $venture->title }}" expired on {{ $venture->expires_at }}

  @if($venture->is_extendable)
    You have 30 days to extend your entrepreneurship. On the contrary you entrepreneurship
    will be deleted.
  @endif


  <x-mail::button :url="''">
    Acceder
  </x-mail::button>

  Gracias


  {{ config('app.name') }}
</x-mail::message>
