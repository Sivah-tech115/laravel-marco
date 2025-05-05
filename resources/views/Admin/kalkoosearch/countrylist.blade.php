@extends('Admin.layouts.main')

@section('breadcrumbtitle', 'Kelkoo Search')
@section('breadcrumbtitle2', 'Add Country')
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
                <h5>Add Country</h5>
            </div>
            <div class="card-body">


                <form action="{{ route('admin.add.country') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Country Name:</label>
                        <input type="text" name="country_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Code:</label>
                        <input type="text" name="country_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary me-2">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5>Country List</h5>
            </div>
            <div class="card-body">
                @if($countries->isEmpty())
                <p>No countries available.</p>
                @else
                <table id="simpletable" class="table table-bordered nowrap w-100">
                    <thead>
                        <tr>
                            <th>Country Name</th>
                            <th>Country Code</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($countries as $country)
                        <tr>
                            <td>{{ $country->countryname }}</td>
                            <td>{{ $country->countrycode }}</td>
                            <td>
                                <form action="{{ route('admin.delete.country', $country->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this country?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#simpletable').DataTable({
            responsive: true,
            initComplete: function() {
                $('#simpletable').wrap('<div class="OverXTable overflow-x-auto"></div>');
            }
        });
    });
</script>
@endsection