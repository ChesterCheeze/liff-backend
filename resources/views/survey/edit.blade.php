<div class="container">
    <a href="{{ route('survey.create') }}" class="btn btn-primary">Back to Surveys</a>
    <h1>Edit Survey</h1>
    <form action="{{ route('survey.update', ['id' => $survey->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="section">Section</label>
            <input type="text" class="form-control" id="section" name="section" value="{{ $survey->section }}" required>
            <label for="name">Survey Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $survey->name }}" required>
            <label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description" value="{{ $survey->description }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Survey</button>
    </form>
</div>