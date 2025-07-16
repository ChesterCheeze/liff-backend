@php $surveys = $surveys ?? collect(); @endphp
<div class="container">
    <h1>Create Survey</h1>
    <form action="{{ route('survey.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="section">Section</label>
            <input type="text" class="form-control" id="section" name="section" required>
            <label for="name">Survey Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Survey</button>
    </form>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div> 
@endif
    <h2>Existing Surveys</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Section</th>
                <th>Name</th>
                <th>Description</th>
                <th>Edit</th>
                <th>Questions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($surveys as $survey)
                <tr>
                    <td>{{ $survey->section }}</td>
                    <td>{{ $survey->name }}</td>
                    <td>{{ $survey->description }}</td>
                    <td>
                        <a href="{{ route('survey.edit', ['id' => $survey->id]) }}" class="btn btn-primary">Edit</a>
                    </td>
                    <td>
                        <a href="{{ route('survey.questions', ['id' => $survey->id]) }}" class="btn btn-primary">View Questions</a>
                    </td>
                    <td>
                        <form action="{{ route('survey.delete', ['id' => $survey->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-primary">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>