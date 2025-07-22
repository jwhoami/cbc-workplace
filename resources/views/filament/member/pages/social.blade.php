<div class="fi-in-text w-full mb-2">
  <div class="text-sm leading-6 flex justify-end gap-x-2 text-gray-950 dark:text-white  " style="">
    @foreach($getRecord()->member->contact->social ?? [] as $social)
    @if($social['network'] == "Facebook" && $social['url'] ?? null)
    <a href="{{ $social['url'] ?? "" }}" target="_blank">
      <img src="{{ asset('images/facebook.png') }}" alt="logo-x" width="16px" />
    </a>
    @endif
    @if($social['network'] == "Instagram" && $social['url'] ?? null)
    <a href="{{ $social['url'] ?? "" }}" target="_blank">
      <img src="{{ asset('images/instagram.png') }}" alt="logo-x" width="16px" />
    </a>
    @endif
    @if($social['network'] == "LinkedIn" && $social['url'] ?? null)
    <a href="{{ $social['url'] ?? "" }}" target="_blank">
      <img src="{{ asset('images/linkedin.png') }}" alt="logo-x" width="16px" />
    </a>
    @endif
    @if($social['network'] == "Youtube" && $social['url'] ?? null)
    <a href="{{ $social['url'] ?? "" }}" target="_blank">
      <img src="{{ asset('images/youtube.png') }}" alt="logo-x" width="16px" />
    </a>
    @endif
    @if($social['network'] == "X" && $social['url'] ?? null)
    <a href="{{ $social['url'] ?? "" }}" target="_blank">
      <img src="{{ asset('images/x.png') }}" alt="logo-x" width="16px" />
    </a>
    @endif
    @endforeach
  </div>
</div>