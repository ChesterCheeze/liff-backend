    <div class="container">
        <h1>Create Survey Question</h1>
        <form action="{{ route('survey.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="label">Label:</label>
                <input type="text" name="label" id="label" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="scale">Scale</option>
                    <option value="text">Text</option>
                </select>
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="required" id="required" class="form-check-input" value="1">
                        <input type="checkbox" name="required" id="required" class="form-check-input" value="1" {{ old('required') ? 'checked' : '' }}>
                    <label for="required" class="form-check-label">Required</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
    </div>
