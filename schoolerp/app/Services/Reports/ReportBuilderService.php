<?php

namespace App\Services\Reports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Department;
use App\Models\Program;
use App\Models\Division;
use App\Models\Fee\StudentFee;
use App\Models\Examination\StudentMark;
use App\Models\Attendance;

class ReportBuilderService
{
    protected array $availableModels = [
        'students' => Student::class,
        'departments' => Department::class,
        'programs' => Program::class,
        'divisions' => Division::class,
        'student_fees' => StudentFee::class,
        'student_marks' => StudentMark::class,
        'attendance' => Attendance::class,
    ];

    protected array $availableColumns = [
        'students' => [
            'id' => 'Student ID',
            'roll_number' => 'Roll Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'admission_date' => 'Admission Date',
            'status' => 'Status',
            'created_at' => 'Created At'
        ],
        'departments' => [
            'id' => 'Department ID',
            'name' => 'Department Name',
            'code' => 'Department Code',
            'description' => 'Description'
        ],
        'programs' => [
            'id' => 'Program ID',
            'name' => 'Program Name',
            'code' => 'Program Code',
            'duration_years' => 'Duration (Years)',
            'degree_type' => 'Degree Type'
        ],
        'divisions' => [
            'id' => 'Division ID',
            'name' => 'Division Name',
            'capacity' => 'Capacity',
            'current_strength' => 'Current Strength'
        ],
        'student_fees' => [
            'id' => 'Fee ID',
            'total_amount' => 'Total Amount',
            'paid_amount' => 'Paid Amount',
            'outstanding_amount' => 'Outstanding Amount',
            'status' => 'Payment Status',
            'due_date' => 'Due Date'
        ],
        'student_marks' => [
            'id' => 'Mark ID',
            'marks_obtained' => 'Marks Obtained',
            'total_marks' => 'Total Marks',
            'percentage' => 'Percentage',
            'grade' => 'Grade',
            'status' => 'Status'
        ],
        'attendance' => [
            'id' => 'Attendance ID',
            'attendance_date' => 'Date',
            'status' => 'Status',
            'check_in_time' => 'Check In Time',
            'remarks' => 'Remarks'
        ]
    ];

    public function buildQuery(array $configuration): Builder
    {
        $baseModel = $configuration['base_model'];
        $columns = $configuration['columns'] ?? [];
        $filters = $configuration['filters'] ?? [];
        $joins = $configuration['joins'] ?? [];
        $orderBy = $configuration['order_by'] ?? [];

        if (!isset($this->availableModels[$baseModel])) {
            throw new \InvalidArgumentException("Invalid base model: {$baseModel}");
        }

        $modelClass = $this->availableModels[$baseModel];
        $query = $modelClass::query();

        // Apply joins
        foreach ($joins as $join) {
            $this->applyJoin($query, $join);
        }

        // Apply filters
        if (!empty($filters)) {
            $this->applyFilters($query, $filters);
        }

        // Apply ordering
        foreach ($orderBy as $order) {
            $query->orderBy($order['column'], $order['direction'] ?? 'asc');
        }

        return $query;
    }

    protected function applyJoin(Builder $query, array $join): void
    {
        $type = $join['type'] ?? 'inner';
        $table = $join['table'];
        $first = $join['first'];
        $operator = $join['operator'] ?? '=';
        $second = $join['second'];

        switch ($type) {
            case 'left':
                $query->leftJoin($table, $first, $operator, $second);
                break;
            case 'right':
                $query->rightJoin($table, $first, $operator, $second);
                break;
            default:
                $query->join($table, $first, $operator, $second);
        }
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $logic = $filters['logic'] ?? 'and';
        $conditions = $filters['conditions'] ?? [];

        if ($logic === 'or') {
            $query->where(function ($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    $q->orWhere($condition['column'], $condition['operator'], $condition['value']);
                }
            });
        } else {
            foreach ($conditions as $condition) {
                $query->where($condition['column'], $condition['operator'], $condition['value']);
            }
        }
    }

    public function getAvailableColumns(string $model = null): array
    {
        if ($model && isset($this->availableColumns[$model])) {
            return $this->availableColumns[$model];
        }

        return $this->availableColumns;
    }

    public function getAvailableModels(): array
    {
        return array_keys($this->availableModels);
    }

    public function executeReport(array $configuration): array
    {
        $query = $this->buildQuery($configuration);
        $columns = $configuration['columns'] ?? [];
        
        // Select specific columns if provided
        if (!empty($columns)) {
            $selectColumns = [];
            foreach ($columns as $column) {
                $selectColumns[] = $column['field'] . ' as ' . ($column['alias'] ?? $column['field']);
            }
            $query->select($selectColumns);
        }

        $limit = $configuration['limit'] ?? 1000;
        $results = $query->limit($limit)->get();

        return [
            'data' => $results->toArray(),
            'total' => $query->count(),
            'configuration' => $configuration
        ];
    }
}