@php
    $suffix = $language === 'ne' ? ' (Nepali)' : ($language === 'en' ? ' (English)' : '');
    $locale = $language ?? 'en';
    $fieldName = fn ($field) => $language ? 'translations['.$language.']['.$field.']' : $field;
    $fieldKey = fn ($field) => $language ? 'translations.'.$language.'.'.$field : $field;
@endphp
<x-form.editor :name="$fieldName('summary')" :label="'Summary'.$suffix" :value="old($fieldKey('summary'), $hall->getTranslation('summary', $locale, false))" :error="$errors->first('translations.'.$locale.'.summary')" placeholder="A short introduction for the hall listing..." />
<x-form.editor :name="$fieldName('body')" :label="'Hall description'.$suffix" :value="old($fieldKey('body'), $hall->getTranslation('body', $locale, false))" :error="$errors->first('translations.'.$locale.'.body')" placeholder="Describe the venue, seating arrangements, and facilities..." />
<x-form.editor :name="$fieldName('booking_instructions')" :label="'Booking instructions'.$suffix" :value="old($fieldKey('booking_instructions'), $hall->getTranslation('booking_instructions', $locale, false))" :error="$errors->first('translations.'.$locale.'.booking_instructions')" placeholder="Describe the request process, included services, and venue rules..." />
@if ($locale === 'ne')
    <p class="text-xs text-gray-500 dark:text-gray-400">Leave Nepali fields blank to use the English content.</p>
@endif
