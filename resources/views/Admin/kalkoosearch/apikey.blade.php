@extends('Admin.layouts.main')

@section('breadcrumbtitle', 'Kelkoo Search')
@section('breadcrumbtitle2', 'Update key')
@section('content')

@section('styles')
<style>
    .dt-responsive .dataTables_length,
    .dt-responsive .dataTables_filter,
    .dt-responsive .dataTables_info,
    .dt-responsive .dataTables_paginate {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }

    .noOverflow {
        overflow-x: initial !important;
        -webkit-overflow-scrolling: initial !important;
    }
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
                <h5>Update key</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.add.code') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="exampleSelect1">Country</label>
                        <select class="form-select" id="exampleSelect1" name="country" disabled>
                            <option value="it" selected>Italian</option>
                        </select>
                        <input type="hidden" name="country" value="it">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">See Offer Button Text:</label>
                        <input type="text" class="form-control" name="see_offer_button_text" value="{{ $config->see_offer_button_text ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Find Out More Button Text:</label>
                        <input type="text" class="form-control" name="find_out_more_button_text" value="{{ $config->find_out_more_button_text ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Key:</label>
                        <textarea rows="6" cols="50" class="form-control" aria-label="With textarea" name="api_key">{{ $config->api_key ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(() => {
        $('#simpletable').DataTable({
            responsive: true,
            initComplete: function() {
                $('#simpletable').wrap('<div class="OverXTable overflow-x-auto"></div>');
            }
        });
    });
</script>
@endsection