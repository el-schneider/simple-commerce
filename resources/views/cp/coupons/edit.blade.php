@extends('statamic::layout')
@section('title', 'Edit Coupon')

@section('content')
    <publish-form
            title="{{ $values->title }}"
            action="{{ cp_route('coupons.store') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection