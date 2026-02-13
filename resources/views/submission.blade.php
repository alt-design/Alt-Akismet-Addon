@extends('statamic::layout')

@section('content')
    <div id="alt-akismet-submission-app">
        <alt-akismet-submission
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
        />
    </div>
@endsection
