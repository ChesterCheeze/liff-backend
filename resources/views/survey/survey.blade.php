<div class="container">
    <h1>Survey Questions</h1>
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
            @foreach($surveyQuestions as $question)
                <tr>
                    <td>{{ $question->label }}</td>
                    <td>{{ $question->name }}</td>
                    <td>{{ $question->type }}</td>
                    <td>{{ $question->required ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createQuestionModal">
        Add Question
    </button>

    <!-- Modal -->
    <div class="modal fade" id="createQuestionModal" tabindex="-1" role="dialog" aria-labelledby="createQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                @include('survey.create')
            </div>
        </div>
    </div>
</div>
