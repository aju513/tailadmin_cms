@php(\Illuminate\Support\Facades\Vite::useHotFile(storage_path(config('frontend.assets.hot_file'))))
@vite(config('frontend.assets.entrypoints'), config('frontend.assets.build_directory'))
