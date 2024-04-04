<div class="container">
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
                <option value="text">Text</option>
                <option value="checkbox">Checkbox</option>
                <option value="radio">Radio</option>
            </select>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="required" name="required">
            <label class="form-check-label" for="required">Required</label>
        </div>
        <input type="hidden" name="survey_id" value="{{ $survey->id }}">
        <button type="submit" class="btn btn-primary">Add Question</button>
    </form>
</div>