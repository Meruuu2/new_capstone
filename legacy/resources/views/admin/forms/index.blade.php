@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Form Submissions Review (GSU)</h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="submissionTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending">Pending ({{ $submissions->where('status', 'pending')->count() }})</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approved">Approved ({{ $submissions->where('status', 'approved')->count() }})</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#converted">Converted ({{ $submissions->where('status', 'converted')->count() }})</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#disapproved">Disapproved ({{ $submissions->where('status', 'disapproved')->count() }})</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pending">
                            @include('admin.forms._table', ['submissions' => $submissions->where('status', 'pending')])
                        </div>
                        <div class="tab-pane fade" id="approved">
                            @include('admin.forms._table', ['submissions' => $submissions->where('status', 'approved')])
                        </div>
                        <div class="tab-pane fade" id="converted">
                            @include('admin.forms._table', ['submissions' => $submissions->where('status', 'converted')])
                        </div>
                        <div class="tab-pane fade" id="disapproved">
                            @include('admin.forms._table', ['submissions' => $submissions->where('status', 'disapproved')])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
 