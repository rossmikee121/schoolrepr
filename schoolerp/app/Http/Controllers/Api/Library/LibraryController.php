<?php

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\Book;
use App\Models\Library\BookIssue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LibraryController extends Controller
{
    public function getBooks(Request $request): JsonResponse
    {
        $query = Book::active();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('author', 'like', "%{$request->search}%")
                  ->orWhere('isbn', 'like', "%{$request->search}%");
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        $books = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    public function issueBook(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
            'due_date' => 'required|date|after:today'
        ]);

        $book = Book::findOrFail($request->book_id);

        if ($book->available_copies <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Book not available'
            ], 400);
        }

        // Check if student already has this book
        $existingIssue = BookIssue::where('book_id', $request->book_id)
            ->where('student_id', $request->student_id)
            ->where('status', 'issued')
            ->exists();

        if ($existingIssue) {
            return response()->json([
                'success' => false,
                'message' => 'Student already has this book issued'
            ], 400);
        }

        $issue = BookIssue::create([
            'book_id' => $request->book_id,
            'student_id' => $request->student_id,
            'issue_date' => now()->toDateString(),
            'due_date' => $request->due_date,
            'status' => 'issued'
        ]);

        // Update available copies
        $book->decrement('available_copies');

        return response()->json([
            'success' => true,
            'data' => $issue->load(['book', 'student']),
            'message' => 'Book issued successfully'
        ]);
    }

    public function returnBook(Request $request): JsonResponse
    {
        $request->validate([
            'issue_id' => 'required|exists:book_issues,id'
        ]);

        $issue = BookIssue::with(['book'])->findOrFail($request->issue_id);

        if ($issue->status !== 'issued') {
            return response()->json([
                'success' => false,
                'message' => 'Book already returned'
            ], 400);
        }

        $returnDate = now()->toDateString();
        $fineAmount = 0;

        // Calculate fine if overdue
        if ($returnDate > $issue->due_date) {
            $overdueDays = now()->diffInDays($issue->due_date);
            $fineAmount = $overdueDays * 5; // ₹5 per day fine
        }

        $issue->update([
            'return_date' => $returnDate,
            'fine_amount' => $fineAmount,
            'status' => 'returned'
        ]);

        // Update available copies
        $issue->book->increment('available_copies');

        return response()->json([
            'success' => true,
            'data' => $issue->fresh(),
            'message' => 'Book returned successfully'
        ]);
    }

    public function getStudentIssues(int $studentId): JsonResponse
    {
        $issues = BookIssue::with(['book'])
            ->where('student_id', $studentId)
            ->orderBy('issue_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $issues
        ]);
    }

    public function getOverdueBooks(): JsonResponse
    {
        $overdueIssues = BookIssue::with(['book', 'student'])
            ->overdue()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $overdueIssues
        ]);
    }
}