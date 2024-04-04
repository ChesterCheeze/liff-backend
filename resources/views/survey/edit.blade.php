<div class="container">
    <h1>Edit Survey</h1>
    <form action="{{ route('survey.update', ['id' => $survey->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Survey Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $survey->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Survey</button>
    </form>

    <h2>Survey Questions</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Label</th>
                <th>Name</th>
                <th>Type</th>
                <th>Required</th>
            </tr>
        </thead>
        <tbody>
            @foreach($survey->questions as $question)
                <tr>
                    <td>{{ $question->label }}</td>
                    <td>{{ $question->name }}</td>
                    <td>{{ $question->type }}</td>
                    <td>{{ $question->required ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>