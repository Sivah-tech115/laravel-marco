@extends('Admin.layouts.main')

@section('breadcrumbtitle', 'Seo Tags')
@section('breadcrumbtitle2', 'Settings')
@section('content')

@section('styles')
<style>
</style>
@endsection

<div class="row">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <!-- Form Card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5>Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" class="form-control" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea rows="6" cols="50" class="form-control" aria-label="With textarea" name="meta_description">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" name="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary me-2">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection