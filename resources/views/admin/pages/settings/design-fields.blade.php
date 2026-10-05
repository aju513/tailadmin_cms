<x-common.component-card title="Homepage services" desc="These four cards keep the layout supplied in the website design.">
    <div class="space-y-6">
        @foreach(old('homepage_services', $settings['homepage_services']) as $index=>$service)
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.input :name="'homepage_services['.$index.'][title]'" :label="'Service '.($index + 1).' title'" :value="$service['title']" required />
                <x-form.select :name="'homepage_services['.$index.'][icon]'" label="Icon" :options="['capacity.svg'=>'Capacity','organization.svg'=>'Organization','research.svg'=>'Research','consultant.svg'=>'Consultancy']" :value="$service['icon']" required />
                <div class="md:col-span-2"><x-form.textarea :name="'homepage_services['.$index.'][description]'" label="Description" :value="$service['description']" :rows="2" required /></div>
            </div>
        @endforeach
    </div>
</x-common.component-card>
@can('capacity-reports.manage')
    <x-common.component-card title="Capacity reports" desc="Contribution figures are managed separately by fiscal year.">
        <a href="{{ route('admin.capacity-reports.index') }}" class="text-sm font-medium text-brand-500 hover:text-brand-600">Manage Capacity Reports</a>
    </x-common.component-card>
@endcan
