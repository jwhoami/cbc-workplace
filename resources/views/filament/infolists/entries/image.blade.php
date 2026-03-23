<div>
  <div class="flex justify-center items-center">
    @if($mobile)
    <img src="{{ Storage::disk('public')->url($getRecord()->file) }}" width="300px" />
    @else
    <img src="{{ Storage::disk('public')->url($getRecord()->file) }}" width="640px" />
    @endif
  </div>
</div>
