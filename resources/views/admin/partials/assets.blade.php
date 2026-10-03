@php(\Illuminate\Support\Facades\Vite::useHotFile(storage_path(config('admin.assets.hot_file'))))
@vite(config('admin.assets.entrypoints'), config('admin.assets.build_directory'))
