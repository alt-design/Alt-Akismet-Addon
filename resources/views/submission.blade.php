@extends('statamic::layout')

@section('content')
    <div>
        <publish-form
            title="Alt Akismet"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
            read-only
        ></publish-form>
    </div>
@endsection
