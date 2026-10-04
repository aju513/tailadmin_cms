@php
    $locale = $language ?? 'en';
    $prefix = $language ? 'translations['.$language.']' : '';
    $titleName = $language ? $prefix.'[title]' : 'title';
    $suffix = $locale === 'ne' ? ' (Nepali)' : '';
@endphp
@if($locale === 'en')
    <x-form.input :name="$titleName" label="Welcome title" :value="old($language ? 'translations.en.title' : 'title', $content->getTranslation('title', 'en', false))" :error="$errors->first('translations.en.title')" x-bind:value="title" @input="updateTitle($event.target.value)" maxlength="255" required />
@else
    <x-form.input :name="$titleName" label="Welcome title (Nepali)" :value="old('translations.ne.title', $content->getTranslation('title', 'ne', false))" :error="$errors->first('translations.ne.title')" maxlength="255" />
@endif
<x-form.input :name="$language ? $prefix.'[subtitle]' : 'subtitle'" :label="'Subtitle'.$suffix" :value="old($language ? 'translations.'.$locale.'.subtitle' : 'subtitle', $content->getTranslation('subtitle', $locale, false))" :error="$errors->first('translations.'.$locale.'.subtitle')" maxlength="255" />
<x-form.editor :name="$language ? $prefix.'[body]' : 'body'" :label="'Description'.$suffix" :value="old($language ? 'translations.'.$locale.'.body' : 'body', $content->getTranslation('body', $locale, false))" :error="$errors->first('translations.'.$locale.'.body')" placeholder="Write the homepage welcome content..." />
