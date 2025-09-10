@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Create Travel Order</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('travel-orders.store') }}" method="POST">
        @csrf
        @include('travel-orders.partials.form')

        <div class="d-flex justify-content-end">
          <a href="{{ route('travel-orders.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

