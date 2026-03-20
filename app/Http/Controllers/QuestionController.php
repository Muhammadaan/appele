<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Element;
use App\Models\Question;
use App\Models\TestCategory;
use App\Models\TestType;
use Inertia\Inertia;

class QuestionController extends Controller
{
    protected function syncOptions(Question $question, array $options, string $type): void
    {
        // Ensure option rules based on question type.
        if ($type === 'ranking') {
            abort_unless(count($options) === 5, 422, 'Ranking questions must have exactly 5 options.');

            foreach ($options as $option) {
                abort_unless(!empty($option['element_id']), 422, 'Ranking options require an element_id.');
                abort_if(isset($option['score']) && $option['score'] !== null, 422, 'Ranking options must not have a score.');
            }
        } else {
            abort_unless(count($options) === 3, 422, 'Single choice questions must have exactly 3 options.');

            $expectedLabels = ['A', 'B', 'C'];
            $labels = array_column($options, 'option_label');
            abort_unless($labels === $expectedLabels, 422, 'Single choice option labels must be A, B, C in order.');

            foreach ($options as $option) {
                abort_unless(isset($option['score']) && is_numeric($option['score']), 422, 'Single choice options must have a numeric score.');
                abort_unless(empty($option['element_id']), 422, 'Single choice options must not have an element_id.');
            }
        }

        $question->options()->delete();

        foreach ($options as $index => $option) {
            $label = $option['option_label'] ?? null;

            if ($type === 'ranking' && empty($label)) {
                // Ranking options are required, but label is optional; assign a default index-based label.
                $label = (string) ($index + 1);
            }

            $question->options()->create([
                'option_label' => $label,
                'option_text' => $option['option_text'] ?? null,
                'element_id' => $option['element_id'] ?? null,
                'score' => $option['score'] ?? null,
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view-questions'), 403);

        $query = Question::with('testCategory', 'testType');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                    ->orWhereHas('testCategory', function ($qt) use ($search) {
                        $qt->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('testType', function ($qt) use ($search) {
                        $qt->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by test type
        if ($testTypeId = $request->get('test_type_id')) {
            $query->where('test_type_id', $testTypeId);
        }

        // Filter by type
        if ($questionType = $request->get('question_type')) {
            $query->where('question_type', $questionType);
        }

        // Sorting
        $sortField = $request->get('sort_field', 'order_number');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $questions = $query->paginate($perPage);

        $questionsData = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'category' => $question->testCategory ? [
                    'id' => $question->testCategory->id,
                    'name' => $question->testCategory->name,
                ] : null,
                'test_type' => $question->testType ? [
                    'id' => $question->testType->id,
                    'name' => $question->testType->name,
                ] : null,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'order_number' => $question->order_number,
                'is_active' => $question->is_active,
            ];
        });

        return Inertia::render('Admin/Questions/Index', [
            'questions' => $questionsData,
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'from' => $questions->firstItem(),
                'to' => $questions->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search'),
                'category_id' => $request->get('category_id'),
                'test_type_id' => $request->get('test_type_id'),
                'question_type' => $request->get('question_type'),
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
            'categories' => TestCategory::all(['id', 'name']),
            'testTypes' => TestType::all(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(auth()->user()->hasPermission('manage-questions'), 403);

        return Inertia::render('Admin/Questions/Create', [
            'categories' => TestCategory::all(),
            'testTypes' => TestType::all(),
            'elements' => Element::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('manage-questions'), 403);

        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'test_type_id' => 'required|exists:test_types,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:ranking,single_choice',
            'order_number' => 'required|integer',
            'is_active' => 'boolean',
            'options' => 'required|array',
        ]);

        $question = Question::create($request->only([
            'category_id',
            'test_type_id',
            'question_text',
            'question_type',
            'order_number',
            'is_active',
        ]));

        $this->syncOptions($question, $request->input('options', []), $request->input('question_type'));

        return redirect()->route('questions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_unless(auth()->user()->hasPermission('view-questions'), 403);
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_unless(auth()->user()->hasPermission('manage-questions'), 403);

        $question = Question::findOrFail($id);

        return Inertia::render('Admin/Questions/Edit', [
            'question' => $question,
            'categories' => TestCategory::all(),
            'testTypes' => TestType::all(),
            'elements' => Element::all(),
            'options' => $question->options()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_unless(auth()->user()->hasPermission('manage-questions'), 403);

        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'test_type_id' => 'required|exists:test_types,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:ranking,single_choice',
            'order_number' => 'required|integer',
            'is_active' => 'boolean',
            'options' => 'required|array',
        ]);

        $question = Question::findOrFail($id);
        $question->update($request->only([
            'category_id',
            'test_type_id',
            'question_text',
            'question_type',
            'order_number',
            'is_active',
        ]));

        $this->syncOptions($question, $request->input('options', []), $request->input('question_type'));

        return redirect()->route('questions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_unless(auth()->user()->hasPermission('manage-questions'), 403);

        Question::findOrFail($id)->delete();

        return redirect()->route('questions.index');
    }
}
