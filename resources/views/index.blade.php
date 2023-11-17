@extends('statamic::layout')

@section('content')
    <div id="alt-akismet-app" >
        <alt-akismet
            title="Alt Akismet"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
            :items="{{ json_encode($data) }}"
        ></alt-akismet>
    </div>
@endsection
