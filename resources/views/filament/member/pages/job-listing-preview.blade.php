<div class="space-y-4">
  <h2 class="text-xl font-bold">{{ $record->title }}</h2>

  <div class="grid grid-cols-2 gap-4 text-sm">
    <div>
      <span class="font-semibold">{{ __('models/job-listing.fields.contract_type') }}:</span>
      {{ $record->contract_type->getLabel() }}
    </div>
    <div>
      <span class="font-semibold">{{ __('models/job-listing.fields.work_modality') }}:</span>
      {{ $record->work_modality->getLabel() }}
    </div>
    <div>
      <span class="font-semibold">{{ __('models/job-listing.fields.city') }}:</span>
      {{ $record->city }}, {{ $record->province }}
    </div>
    <div>
      <span class="font-semibold">{{ __('models/job-listing.fields.application_deadline') }}:</span>
      {{ $record->application_deadline->format('d/m/Y') }}
    </div>
    @if($record->salary_min || $record->salary_max)
    <div>
      <span class="font-semibold">{{ __('models/job-listing.sections.salary') }}:</span>
      {{ $record->currency }}
      {{ $record->salary_min ? number_format($record->salary_min, 2) : '' }}
      {{ $record->salary_min && $record->salary_max ? ' - ' : '' }}
      {{ $record->salary_max ? number_format($record->salary_max, 2) : '' }}
    </div>
    @endif
  </div>

  <div class="prose max-w-none">
    <h3>{{ __('models/job-listing.fields.description') }}</h3>
    {!! $record->description !!}
  </div>

  <div class="prose max-w-none">
    <h3>{{ __('models/job-listing.fields.requirements') }}</h3>
    {!! $record->requirements !!}
  </div>

  @if($record->screening_questions && count($record->screening_questions) > 0)
  <div>
    <h3 class="font-semibold">{{ __('models/job-listing.sections.screening') }}</h3>
    <ol class="list-decimal list-inside space-y-1">
      @foreach($record->screening_questions as $question)
      <li>{{ $question }}</li>
      @endforeach
    </ol>
  </div>
  @endif
</div>
