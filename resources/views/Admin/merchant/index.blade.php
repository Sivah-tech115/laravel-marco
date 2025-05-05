@extends('Admin.layouts.main')
@section('breadcrumbtitle', 'Merchant')
@section('breadcrumbtitle2', 'All Merchants')


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
@section('content')

<div class="page-wrapper">
    <div class="row">
        <!-- Table Card -->
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5>Merchant List</h5>
                </div>
                <div class="card-body">
                    @if($data->isEmpty())
                    <p>No data available.</p>
                    @else
                    <table id="simpletable" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>Merchant Id</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $merchant)
                            <tr>
                                <td>{{ $merchant->kelkoo_merchant_id }}</td>
                                <td>{{ $merchant->name }}</td>
                                <td>
                                    <a href="{{ route('admin.merchant.product', $merchant->id) }}"
                                        class="btn btn-info btn-sm">
                                        Create Csv
                                    </a>
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