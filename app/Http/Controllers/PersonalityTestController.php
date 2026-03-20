<?php

namespace App\Http\Controllers;

use App\Models\Element;
use App\Models\Option;
use App\Models\Question;
use App\Models\TestResult;
use App\Models\TestSession;
use App\Models\TestType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PersonalityTestController extends Controller
{
    public function start()
    {
        abort_unless(auth()->user()->hasPermission('start-test'), 403);

        $user = auth()->user();

        return Inertia::render('Test/Start', [
            'testTypes' => TestType::all(),
            'users' => $user && $user->is_admin ? User::orderBy('name')->get() : [],
            'isAdmin' => $user?->is_admin ?? false,
        ]);
    }

    public function storeStart(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('start-test'), 403);

        $request->validate([
            'participant_name' => 'required|string',
            'birth_date' => 'required|date',
            'test_type_id' => 'required|exists:test_types,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $birthDate = now()->parse($request->input('birth_date'));
        $age = $birthDate->age;

        $authUser = auth()->user();
        $userId = $authUser->id;

        // Admin can create test sessions for other users.
        if ($authUser->is_admin && $request->filled('user_id')) {
            $userId = $request->input('user_id');
        }

        $user = User::find($userId);
        if (! $user) {
            abort(404);
        }

        if ($user->tokens <= 0) {
            return back()->withErrors(['tokens' => 'User tidak memiliki token yang cukup untuk memulai test.']);
        }

        $session = TestSession::create([
            'participant_name' => $request->input('participant_name'),
            'participant_age' => $age,
            'certificate_number' => strtoupper(bin2hex(random_bytes(4))),
            'test_type_id' => $request->input('test_type_id'),
            'started_at' => now(),
            'user_id' => $userId,
        ]);

        $user->decrement('tokens', 1);

        return redirect()->route('test.take', $session);
    }

    public function take(TestSession $session)
    {
        abort_unless(auth()->user()->hasPermission('start-test'), 403);

        $questions = Question::with(['options.element'])
            ->where('test_type_id', $session->test_type_id)
            ->get();

        // Ensure the session includes its test type for rendering
        $session->load(['testType', 'user']);

        return Inertia::render('Test/Take', [
            'session' => $session,
            'questions' => $questions,
        ]);
    }

    public function submit(TestSession $session, Request $request)
    {
        abort_unless(auth()->user()->hasPermission('start-test'), 403);

        try {
            $request->validate([
                'answers' => 'required|array',
            ]);

            $answers = $request->input('answers');

            // Remove existing answers/results for this session
            $session->testAnswers()->delete();
            $session->testResults()->delete();

            foreach ($answers as $questionId => $answer) {
                $question = Question::find($questionId);
                if (! $question) {
                    continue;
                }

                if ($question->question_type === 'ranking') {
                    $ranks = $answer['ranks'] ?? [];
                    foreach ($ranks as $optionId => $rank) {
                        $option = Option::find($optionId);
                        if (! $option) {
                            continue;
                        }

                        $session->testAnswers()->create([
                            'question_id' => $question->id,
                            'option_id' => $option->id,
                            'rank' => $rank,
                            'score' => null,
                        ]);
                    }

                    continue;
                }

                if (!isset($answer['option_id'])) {
                    continue;
                }

                $option = Option::find($answer['option_id']);
                if (! $option) {
                    continue;
                }

                $session->testAnswers()->create([
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                    'score' => $option->score,
                ]);
            }

            if ($session->testAnswers()->count() === 0) {
                throw ValidationException::make(['answers' => 'Please answer at least one question.']);
            }

            $this->calculateResults($session);

            $session->load(['testType', 'testAnswers.question', 'testAnswers.option']);
            $results = $session->testResults()->get();

            return Inertia::render('Test/Results', [
                'session' => $session,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view-tests'), 403);

        $user = auth()->user();

        $query = TestSession::with('testType', 'user');

        // Filter by user if not admin
        if ($user && ! $user->is_admin) {
            $query->where('user_id', $user->id);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('participant_name', 'like', "%{$search}%")
                    ->orWhereHas('testType', function ($qt) use ($search) {
                        $qt->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by test type
        if ($testTypeId = $request->get('test_type_id')) {
            $query->where('test_type_id', $testTypeId);
        }

        // Sorting
        $sortField = $request->get('sort_field', 'started_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $sessions = $query->paginate($perPage);

        $sessionsData = $sessions->map(function ($session) {
            return [
                'id' => $session->id,
                'participant_name' => $session->participant_name,
                'test_type' => $session->testType ? [
                    'id' => $session->testType->id,
                    'name' => $session->testType->name,
                ] : null,
                'certificate_number' => $session->certificate_number,
                'started_at' => $session->started_at,
                'completed_at' => $session->completed_at,
                'user' => $session->user ? [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                ] : null,
            ];
        });

        return Inertia::render('Test/MyTests', [
            'sessions' => $sessionsData,
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
                'from' => $sessions->firstItem(),
                'to' => $sessions->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search'),
                'test_type_id' => $request->get('test_type_id'),
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'testTypes' => TestType::all(['id', 'name']),
        ]);
    }

    public function show(TestSession $session)
    {
        abort_unless(auth()->user()->hasPermission('view-tests'), 403);

        $user = auth()->user();

        // Allow admins, or sessions owned by the user, or sessions without an owner.
        if (! $user->is_admin && $session->user_id && $session->user_id !== $user->id) {
            abort(403);
        }

        $session->load(['testType', 'testAnswers.question', 'testAnswers.option']);
        $results = $session->testResults()->get();

        return Inertia::render('Test/Results', [
            'session' => $session,
            'results' => $results,
        ]);
    }

    public function results(TestSession $session)
    {
        $session->load(['testType', 'testAnswers.question', 'testAnswers.option']);
        $results = $session->testResults()->get();

        return Inertia::render('Test/Results', [
            'session' => $session,
            'results' => $results,
        ]);
    }

    protected function calculateResults(TestSession $session): void
    {
        $answers = $session->testAnswers()->with(['option', 'question'])->get();
        if ($answers->isEmpty()) {
            return;
        }

        $firstQuestion = $answers->first()->question;
        $categoryId = $firstQuestion->category_id;
        $type = $firstQuestion->question_type;

        if ($type === 'ranking') {
            $elementScores = [];

            foreach ($answers as $answer) {
                if (! $answer->rank) {
                    continue;
                }

                $point = max(1, 6 - intval($answer->rank));
                $elementId = $answer->option->element_id;
                $elementScores[$elementId] = ($elementScores[$elementId] ?? 0) + $point;
            }

            arsort($elementScores);
            $dominantElementId = array_key_first($elementScores);
            $dominantScore = $elementScores[$dominantElementId] ?? 0;
            $dominantElement = $dominantElementId ? Element::find($dominantElementId) : null;

            $session->testResults()->create([
                'category_id' => $categoryId,
                'result_type' => 'dominant_element',
                'result_value' => $dominantElement?->name ?? 'Unknown',
                'score' => $dominantScore,
            ]);
        } else {
            $totalScore = $answers->sum(fn ($a) => $a->score ?? 0);
            $resultValue = 'UNKNOWN';

            if ($totalScore >= 5 && $totalScore <= 8) {
                $resultValue = 'PROFUNDA';
            } elseif ($totalScore >= 9 && $totalScore <= 11) {
                $resultValue = 'ESENSIAL';
            } elseif ($totalScore >= 12 && $totalScore <= 15) {
                $resultValue = 'SUPERFISIAL';
            }

            $session->testResults()->create([
                'category_id' => $categoryId,
                'result_type' => 'energy_type',
                'result_value' => $resultValue,
                'score' => $totalScore,
            ]);
        }
    }
}
