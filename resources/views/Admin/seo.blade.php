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
                <h5>Edit SEO Scripts</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.seo.update') }}" method="POST">
                    @csrf
                    @method('PUT')
    
                    <div class="mb-3">
                        <label class="form-label">Header Scripts (inside &lt;/head&gt;):</label>
                        <textarea rows="10" cols="50" class="form-control" aria-label="With textarea" name="header_scripts">{{ old('header_scripts', $seo->header_scripts) }}</textarea>

                    </div>
                    <div class="mb-3">
                        <label class="form-label">Footer Scripts (before &lt;/body&gt;):</label>
                        <textarea rows="10" cols="50" class="form-control" aria-label="With textarea" name="footer_scripts">{{ old('footer_scripts', $seo->footer_scripts) }}</textarea>

                    </div>


                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary me-2">Save Scripts</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection