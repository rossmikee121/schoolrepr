<?php

namespace App\Services;

use App\Models\Academic\Division;
use App\Models\Lab\Lab;
use App\Models\Lab\LabSession;
use App\Models\Lab\LabBatch;

class LabBatchingService
{
    public static function createBatches(Division $division, Lab $lab, string $subjectName, array $sessionData): array
    {
        $students = $division->students()->active()->get();
        $totalStudents = $students->count();
        
        if ($totalStudents === 0) {
            return ['batches' => 0, 'sessions' => []];
        }

        $batchSize = $lab->capacity;
        $totalBatches = ceil($totalStudents / $batchSize);
        $sessions = [];

        for ($batchNumber = 1; $batchNumber <= $totalBatches; $batchNumber++) {
            $session = LabSession::create([
                'lab_id' => $lab->id,
                'division_id' => $division->id,
                'subject_name' => $subjectName,
                'batch_number' => $batchNumber,
                'max_students' => $batchSize,
                'session_date' => $sessionData['session_date'],
                'start_time' => $sessionData['start_time'],
                'end_time' => $sessionData['end_time'],
                'instructor_id' => $sessionData['instructor_id'] ?? null,
            ]);

            // Assign students to batch
            $batchStudents = $students->slice(($batchNumber - 1) * $batchSize, $batchSize);
            
            foreach ($batchStudents as $student) {
                LabBatch::create([
                    'lab_session_id' => $session->id,
                    'student_id' => $student->id,
                ]);
            }

            $sessions[] = $session;
        }

        return [
            'batches' => $totalBatches,
            'sessions' => $sessions,
            'total_students' => $totalStudents
        ];
    }

    public static function reassignStudent(int $studentId, int $fromSessionId, int $toSessionId): bool
    {
        $fromSession = LabSession::find($fromSessionId);
        $toSession = LabSession::find($toSessionId);

        if (!$fromSession || !$toSession) {
            return false;
        }

        // Check capacity
        $currentCount = $toSession->batches()->count();
        if ($currentCount >= $toSession->max_students) {
            return false;
        }

        // Move student
        LabBatch::where('lab_session_id', $fromSessionId)
            ->where('student_id', $studentId)
            ->update(['lab_session_id' => $toSessionId]);

        return true;
    }
}