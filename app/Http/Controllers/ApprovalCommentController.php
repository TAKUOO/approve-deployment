<?php

namespace App\Http\Controllers;

use App\Models\ApprovalComment;
use App\Models\ApprovalMessage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalCommentController extends Controller
{
    private function parseMentions(string $body): array
    {
        preg_match_all('/@([\pL\pN_\-]{1,32})/u', $body, $matches);
        if (!isset($matches[1])) {
            return [];
        }
        return array_values(array_unique($matches[1]));
    }

    private function clampRatio($value): float
    {
        $float = (float) $value;
        if ($float < 0) return 0.0;
        if ($float > 1) return 1.0;
        return $float;
    }

    private function clampNullableRatio($value): ?float
    {
        if ($value === null) {
            return null;
        }
        return $this->clampRatio($value);
    }

    private function approvalMessageForProject(Project $project, $messageId): ?ApprovalMessage
    {
        if (!$messageId) {
            return null;
        }

        return ApprovalMessage::where('id', $messageId)
            ->where('project_id', $project->id)
            ->first();
    }

    private function projectForToken(string $token): ?Project
    {
        return Project::where('approve_token', $token)
            ->where(function ($query) {
                $query->whereNull('approve_token_expires_at')
                      ->orWhere('approve_token_expires_at', '>', now());
            })
            ->first();
    }

    private function serializeComment(ApprovalComment $comment, bool $canEdit): array
    {
        return [
            'id' => $comment->id,
            'author_name' => $comment->author_name,
            'body' => $comment->body,
            'x_ratio' => (float) $comment->x_ratio,
            'y_ratio' => (float) $comment->y_ratio,
            'w_ratio' => $comment->w_ratio !== null ? (float) $comment->w_ratio : null,
            'h_ratio' => $comment->h_ratio !== null ? (float) $comment->h_ratio : null,
            'mentions' => $comment->mentions ?? [],
            'created_at' => $comment->created_at?->toIso8601String(),
            'updated_at' => $comment->updated_at?->toIso8601String(),
            'can_edit' => $canEdit,
        ];
    }

    public function indexForProject(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $approvalMessage = $this->approvalMessageForProject($project, $request->query('msg'));
        if (!$approvalMessage) {
            return response()->json(['error' => 'Approval message not found'], 404);
        }

        $comments = ApprovalComment::where('approval_message_id', $approvalMessage->id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'comments' => $comments->map(fn ($comment) => $this->serializeComment($comment, true)),
        ]);
    }

    public function storeForProject(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'msg' => 'required|integer',
            'body' => 'required|string|max:2000',
            'x_ratio' => 'required|numeric|min:0|max:1',
            'y_ratio' => 'required|numeric|min:0|max:1',
            'w_ratio' => 'nullable|numeric|min:0|max:1',
            'h_ratio' => 'nullable|numeric|min:0|max:1',
            'author_name' => 'nullable|string|max:120',
        ]);

        $approvalMessage = $this->approvalMessageForProject($project, $validated['msg']);
        if (!$approvalMessage) {
            return response()->json(['error' => 'Approval message not found'], 404);
        }

        $authorName = $validated['author_name'] ?? (Auth::user()->name ?? 'Owner');

        $comment = ApprovalComment::create([
            'approval_message_id' => $approvalMessage->id,
            'project_id' => $project->id,
            'created_by_user_id' => Auth::id(),
            'author_name' => $authorName,
            'body' => $validated['body'],
            'x_ratio' => $this->clampRatio($validated['x_ratio']),
            'y_ratio' => $this->clampRatio($validated['y_ratio']),
            'w_ratio' => $this->clampNullableRatio($validated['w_ratio'] ?? null),
            'h_ratio' => $this->clampNullableRatio($validated['h_ratio'] ?? null),
            'mentions' => $this->parseMentions($validated['body']),
        ]);

        return response()->json([
            'comment' => $this->serializeComment($comment, true),
        ], 201);
    }

    public function updateForProject(Request $request, Project $project, ApprovalComment $comment)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        if ($comment->project_id !== $project->id) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        $validated = $request->validate([
            'body' => 'nullable|string|max:2000',
            'x_ratio' => 'nullable|numeric|min:0|max:1',
            'y_ratio' => 'nullable|numeric|min:0|max:1',
            'w_ratio' => 'nullable|numeric|min:0|max:1',
            'h_ratio' => 'nullable|numeric|min:0|max:1',
        ]);

        $update = [];
        if (array_key_exists('body', $validated)) {
            $update['body'] = $validated['body'];
            $update['mentions'] = $this->parseMentions($validated['body'] ?? '');
        }
        if (array_key_exists('x_ratio', $validated)) {
            $update['x_ratio'] = $this->clampRatio($validated['x_ratio']);
        }
        if (array_key_exists('y_ratio', $validated)) {
            $update['y_ratio'] = $this->clampRatio($validated['y_ratio']);
        }
        if (array_key_exists('w_ratio', $validated)) {
            $update['w_ratio'] = $this->clampNullableRatio($validated['w_ratio']);
        }
        if (array_key_exists('h_ratio', $validated)) {
            $update['h_ratio'] = $this->clampNullableRatio($validated['h_ratio']);
        }

        if (!empty($update)) {
            $comment->update($update);
        }

        return response()->json([
            'comment' => $this->serializeComment($comment->fresh(), true),
        ]);
    }

    public function destroyForProject(Project $project, ApprovalComment $comment)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        if ($comment->project_id !== $project->id) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        $comment->delete();

        return response()->json(['deleted' => true]);
    }

    public function indexForApproval(Request $request, string $token)
    {
        $project = $this->projectForToken($token);
        if (!$project) {
            return response()->json(['error' => 'Invalid or expired token'], 404);
        }

        $approvalMessage = $this->approvalMessageForProject($project, $request->query('msg'));
        if (!$approvalMessage) {
            return response()->json(['error' => 'Approval message not found'], 404);
        }

        $comments = ApprovalComment::where('approval_message_id', $approvalMessage->id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'comments' => $comments->map(fn ($comment) => $this->serializeComment($comment, false)),
        ]);
    }

    public function storeForApproval(Request $request, string $token)
    {
        $project = $this->projectForToken($token);
        if (!$project) {
            return response()->json(['error' => 'Invalid or expired token'], 404);
        }

        $validated = $request->validate([
            'msg' => 'required|integer',
            'body' => 'required|string|max:2000',
            'x_ratio' => 'required|numeric|min:0|max:1',
            'y_ratio' => 'required|numeric|min:0|max:1',
            'w_ratio' => 'nullable|numeric|min:0|max:1',
            'h_ratio' => 'nullable|numeric|min:0|max:1',
            'author_name' => 'required|string|max:120',
        ]);

        $approvalMessage = $this->approvalMessageForProject($project, $validated['msg']);
        if (!$approvalMessage) {
            return response()->json(['error' => 'Approval message not found'], 404);
        }

        $comment = ApprovalComment::create([
            'approval_message_id' => $approvalMessage->id,
            'project_id' => $project->id,
            'created_by_user_id' => null,
            'author_name' => $validated['author_name'],
            'body' => $validated['body'],
            'x_ratio' => $this->clampRatio($validated['x_ratio']),
            'y_ratio' => $this->clampRatio($validated['y_ratio']),
            'w_ratio' => $this->clampNullableRatio($validated['w_ratio'] ?? null),
            'h_ratio' => $this->clampNullableRatio($validated['h_ratio'] ?? null),
            'mentions' => $this->parseMentions($validated['body']),
        ]);

        return response()->json([
            'comment' => $this->serializeComment($comment, false),
        ], 201);
    }
}
