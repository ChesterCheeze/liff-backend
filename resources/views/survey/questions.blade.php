<div class="container">
    <a href="{{ route('survey.create') }}" class="btn btn-primary">Back to Surveys</a>
    <h1>Add Question to Survey</h1>
    <form action="{{ route('questions.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="label">Question Label</label>
            <input type="text" class="form-control" id="label" name="label" required>
        </div>
        <div class="form-group">
            <label for="name">Question Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="type">Question Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="scale">Scale</option>
                <option value="text">Text</option>
                <option value="checkbox">Checkbox</option>
                <option value="radio">Radio</option>
            </select>
        </div>
        <div class="form-group form-check">
            <input type="radio" class="form-check-input" id="required" name="required" value="true">
            <label class="form-check-label" for="required">Required</label>
        </div>
        <input type="hidden" name="survey_id" value="{{ $survey->id }}">
        <button type="submit" class="btn btn-primary">Add Question</button>
    </form>
</div>
<table>
    <thead>
        <tr>
            <th>Label</th>
            <th>Name</th>
            <th>Type</th>
            <th>Required</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        @foreach($survey->questions as $question)
            <tr>
                <td>{{ $question->label }}</td>
                <td>{{ $question->name }}</td>
                <td>{{ $question->type }}</td>
                <td>{{ $question->required ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="#" class="btn btn-primary">Edit</a>
                </td>
                <td>
                    <a href="#" class="btn btn-primary">Delete</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>