@extends('Admin.layouts.main')
@section('breadcrumbtitle', 'Offers')
@section('breadcrumbtitle2', 'All Offers')


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
                    <h5>Offers List</h5>
                </div>
                <div class="card-body">
                    @if($products->isEmpty())
                    <p>No data available.</p>
                    @else
                    <table id="simpletable" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>Offer Id</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $merchant)
                            <tr>
                                <td>{{ $merchant->offer_id }}</td>
                                <td>{{ $merchant->title }}</td>
                                <td>
                                    <a href="{{ route('offers.product', [Str::slug($merchant->title)]) }}" class="btn btn-primary">
                                    View Product
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