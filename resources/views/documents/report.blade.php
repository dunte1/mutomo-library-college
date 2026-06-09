@extends('documents.layout')

@section('content')
    @foreach($sections as $section)
        <div class="section-heading">{{ $section['label'] }}</div>
        <table class="data-table">
            @if(!empty($section['headers']))
                <thead>
                    <tr>
                        @foreach($section['headers'] as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @forelse($section['rows'] as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="99" style="text-align:center;color:#999;">No data available</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
@endsection
