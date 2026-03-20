<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TokenHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view-tokens'), 403);

        $auth = auth()->user();
        $isAdmin = $auth?->is_admin ?? false;

        $users = [];
        $currentTokens = null;
        $tokenHistories = null;
        $pagination = null;
        $historyFilters = null;

        if ($isAdmin) {
            // Current Tokens - All users with their current balance
            $currentTokens = User::orderBy('name')
                ->get(['id', 'name', 'email', 'tokens', 'created_at'])
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'tokens' => $user->tokens,
                        'created_at' => $user->created_at,
                    ];
                });

            // Token History with pagination and search
            $query = TokenHistory::with(['user', 'creator']);

            // Search
            if ($search = $request->get('search')) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by type
            if ($type = $request->get('type')) {
                $query->where('type', $type);
            }

            // Sorting
            $sortField = $request->get('sort_field', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $histories = $query->paginate($perPage);

            $tokenHistories = $histories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'user' => [
                        'id' => $history->user->id,
                        'name' => $history->user->name,
                        'email' => $history->user->email,
                    ],
                    'amount' => $history->amount,
                    'balance_after' => $history->balance_after,
                    'type' => $history->type,
                    'notes' => $history->notes,
                    'creator' => $history->creator ? [
                        'id' => $history->creator->id,
                        'name' => $history->creator->name,
                    ] : null,
                    'created_at' => $history->created_at,
                ];
            });

            $pagination = [
                'current_page' => $histories->currentPage(),
                'last_page' => $histories->lastPage(),
                'per_page' => $histories->perPage(),
                'total' => $histories->total(),
                'from' => $histories->firstItem(),
                'to' => $histories->lastItem(),
            ];

            $historyFilters = [
                'search' => $request->get('search'),
                'type' => $request->get('type'),
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ];

            // Users list for dropdown
            $users = User::orderBy('name')->get(['id', 'name', 'email', 'tokens']);
        }

        return Inertia::render('Tokens/Index', [
            'users' => $users,
            'currentTokens' => $currentTokens,
            'tokenHistories' => $tokenHistories,
            'pagination' => $pagination,
            'historyFilters' => $historyFilters,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $auth = auth()->user();

        if (! $auth || ! $auth->is_admin) {
            abort(403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($data, $auth) {

            $user = User::findOrFail($data['user_id']);

            // Get current balance before adding
            $balanceBefore = $user->tokens;

            // tambah saldo
            $user->increment('tokens', $data['amount']);

            // Refresh user model to get new balance
            $user->refresh();

            // simpan histori - SELALU BUAT BARU (tidak update)
            TokenHistory::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'balance_after' => $user->tokens,
                'type' => 'purchase',
                'created_by' => $auth->id,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('tokens.index')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Tokens added successfully!',
            ]);
    }

    public function destroy(TokenHistory $tokenHistory)
    {
        $auth = auth()->user();

        if (! $auth || ! $auth->is_admin) {
            abort(403);
        }

        $tokenHistory->delete();

        return redirect()->route('tokens.index')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Token history deleted successfully!',
            ]);
    }
}