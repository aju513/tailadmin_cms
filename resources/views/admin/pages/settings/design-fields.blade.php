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

<x-common.component-card title="Capacity reports" desc="Add a reporting year and its figures. Empty figures display a dash on the website.">
    <div x-data="{ reports: {{ Js::from(old('capacity_reports', $settings['capacity_reports'])) }}, addReport() { this.reports.push({ year: '', development: {}, collaboration: {} }); } }" x-effect="reports.length; $nextTick(() => $el.querySelectorAll('label[for]').forEach(label => { const input = label.parentElement.querySelector('input'); if(input) label.htmlFor = input.id; }))" class="space-y-6">
        <input type="hidden" name="capacity_reports" value="">
        <template x-for="(report, index) in reports" :key="index">
            <div class="space-y-6 rounded-xl border border-gray-200 p-5 dark:border-gray-800">
                <div class="flex items-end justify-between gap-6">
                    <x-form.input name="report-year" label="Reporting year" placeholder="2081/82" x-bind:name="'capacity_reports[' + index + '][year]'" x-bind:id="'report-year-' + index" x-model="report.year" required />
                    <x-ui.button type="button" variant="outline" x-on:click="reports.splice(index, 1)">Remove year</x-ui.button>
                </div>
                @foreach(['development'=>'Capacity Development Contribution','collaboration'=>'Contribution Through Collaboration'] as $group=>$title)
                    <div class="space-y-4">
                        <h3 class="font-medium text-gray-800 dark:text-white/90">{{ $title }}</h3>
                        <div class="grid gap-6 md:grid-cols-2">
                            @foreach(['training_programs'=>'Total training programs','participants'=>'Total participants','in_service_programs'=>'In-service training programs','in_service_participants'=>'In-service participants','materials'=>'Training materials developed','dialogues'=>'Issue-focused dialogues','research'=>'Research studies','consultancy'=>'Consultancy services'] as $key=>$label)
                                <x-form.input :name="'report-'.$group.'-'.$key" :label="$label" type="number" min="0" max="1000000000" x-bind:name="'capacity_reports[' + index + '][{{ $group }}][{{ $key }}]'" x-bind:id="'report-' + index + '-{{ $group }}-{{ $key }}'" x-model="report.{{ $group }}.{{ $key }}" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </template>
        <x-ui.button type="button" variant="outline" x-on:click="addReport()" x-bind:disabled="reports.length >= 20">Add reporting year</x-ui.button>
    </div>
</x-common.component-card>
