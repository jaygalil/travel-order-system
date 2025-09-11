@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Edit Travel Order</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('travel-orders.update', $travelOrder) }}" method="POST">
        @csrf
        @method('PUT')
        @include('travel-orders.partials.form', ['travelOrder' => $travelOrder])

        <div class="d-flex justify-content-end">
          <a href="{{ route('travel-orders.show', $travelOrder) }}" class="btn btn-outline-secondary me-2">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
