<div class="container">
    <a href="{{ route('survey.questions', ['id' => $question->survey_id]) }}" class="btn btn-primary">Back to Questions</a>
    <h1>Edit Question</h1>
    <form action="{{ route('question.update', ['id' => $question->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="survey_id" value="{{ $question->survey_id }}">
        <div class="form-group">
            <label for="label">Question Label</label>
            <input type="text" class="form-control" id="label" name="label" value="{{ $question->label }}" required>
        </div>
        <div class="form-group">
            <label for="name">Question Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $question->name }}" required>
        </div>
        <div class="form-group">
            <label for="type">Question Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="scale" {{ $question->type == 'scale' ? 'selected' : '' }}>Scale</option>
                <option value="text" {{ $question->type == 'text' ? 'selected' : '' }}>Text</option>
                <option value="checkbox" {{ $question->type == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                <option value="radio" {{ $question->type == 'radio' ? 'selected' : '' }}>Radio</option>
            </select>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="required" name="required" value="checked" {{ $question->required ? 'checked' : '' }}>
            <label class="form-check-label" for="required">Required</label>
        </div>
        <button type="submit" class="btn btn-primary">Update Question</button>
    </form>
</div>