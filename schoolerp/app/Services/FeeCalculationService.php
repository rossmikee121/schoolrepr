<?php

/**
 * =============================================================================
 * FEE CALCULATION SERVICE - Handles Complex Fee Calculations
 * =============================================================================
 * 
 * This service handles all complex fee calculations including scholarships,
 * discounts, and final amount determination.
 * 
 * PURPOSE:
 * - Calculate final fee amount after applying scholarships
 * - Handle different types of scholarships (percentage, fixed amount)
 * - Apply scholarship limits and caps
 * - Provide detailed breakdown of calculations
 * 
 * USED BY:
 * - Fee Controllers (when assigning fees to students)
 * - Scholarship Controllers (when applying scholarships)
 * - Payment Controllers (when processing payments)
 * - Report Controllers (for fee reports)
 * 
 * DATABASE TABLES USED:
 * - students (student information)
 * - fee_structures (base fee amounts)
 * - scholarships (scholarship definitions)
 * - student_scholarships (scholarships assigned to students)
 * 
 * FOR INTERNS:
 * - Service = Contains business logic that can be reused
 * - Static method = Can be called without creating object instance
 * - Business logic = Rules about how fees and scholarships work
 * =============================================================================
 */

namespace App\Services;

use App\Models\User\Student;
use App\Models\Fee\FeeStructure;

class FeeCalculationService
{
    /**
     * CALCULATE FEE WITH SCHOLARSHIP
     * 
     * This method calculates the final fee amount for a student after applying
     * all eligible scholarships and discounts.
     * 
     * CALCULATION PROCESS:
     * 1. Start with base fee amount from fee structure
     * 2. Find all active scholarships for the student
     * 3. Calculate discount for each scholarship
     * 4. Apply scholarship caps/limits if any
     * 5. Sum up all discounts
     * 6. Calculate final amount (base - total discounts)
     * 7. Ensure final amount is not negative
     * 
     * SCHOLARSHIP TYPES:
     * - Percentage: Discount is % of total fee (e.g., 50% = half fee waived)
     * - Fixed Amount: Discount is fixed rupee amount (e.g., ₹5000 off)
     * 
     * BUSINESS RULES:
     * - Multiple scholarships can be applied to same student
     * - Percentage scholarships can have maximum amount caps
     * - Final amount cannot be negative (minimum ₹0)
     * - Only active scholarships for current academic year are considered
     * 
     * EXAMPLE CALCULATION:
     * Base Fee: ₹50,000
     * SC/ST Scholarship: 50% (₹25,000)
     * Merit Scholarship: ₹5,000
     * Total Discount: ₹30,000
     * Final Amount: ₹20,000
     * 
     * @param Student $student The student for whom to calculate fees
     * @param FeeStructure $feeStructure The base fee structure
     * @return array Detailed breakdown of fee calculation
     */
    public static function calculateFeeWithScholarship(Student $student, FeeStructure $feeStructure): array
    {
        // Step 1: Start with base fee amount
        $totalAmount = $feeStructure->amount;
        $discountAmount = 0;

        // Step 2: Get all active scholarships for this student and academic year
        // Only consider scholarships that are:
        // - Assigned to this student
        // - For current academic year
        // - Currently active
        $scholarships = $student->scholarships()
            ->where('academic_year', $student->academic_year)
            ->where('is_active', true)
            ->get();

        // Step 3: Calculate discount for each scholarship
        foreach ($scholarships as $scholarship) {
            
            // Check scholarship type and calculate accordingly
            if ($scholarship->scholarship->type === 'percentage') {
                // PERCENTAGE SCHOLARSHIP
                // Calculate percentage of total amount
                $discount = ($totalAmount * $scholarship->scholarship->value) / 100;
                
                // Apply maximum amount cap if set
                // Example: 50% scholarship with ₹20,000 cap
                // On ₹50,000 fee: 50% = ₹25,000, but cap limits to ₹20,000
                if ($scholarship->scholarship->max_amount) {
                    $discount = min($discount, $scholarship->scholarship->max_amount);
                }
            } else {
                // FIXED AMOUNT SCHOLARSHIP
                // Direct rupee amount discount
                $discount = $scholarship->scholarship->value;
            }
            
            // Add this scholarship's discount to total
            $discountAmount += $discount;
        }

        // Step 4: Calculate final amount
        $finalAmount = $totalAmount - $discountAmount;

        // Step 5: Return detailed breakdown
        return [
            'total_amount' => $totalAmount,                    // Original fee amount
            'discount_amount' => $discountAmount,              // Total discount applied
            'final_amount' => max(0, $finalAmount),           // Final amount (minimum ₹0)
            'scholarships_applied' => $scholarships->count()   // Number of scholarships used
        ];
    }
}

/**
 * =============================================================================
 * FEE CALCULATION SERVICE SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS SERVICE DOES:
 * 1. Takes student and fee structure as input
 * 2. Finds all scholarships applicable to the student
 * 3. Calculates total discount amount
 * 4. Returns final fee amount and breakdown
 * 
 * WHY USE A SERVICE:
 * - Complex business logic is centralized
 * - Can be reused by multiple controllers
 * - Easy to test and maintain
 * - Keeps controllers clean and focused
 * 
 * REAL WORLD EXAMPLE:
 * Student: Rahul Sharma (SC category)
 * Program: B.Com (Fee: ₹45,000)
 * Scholarships:
 * - SC Category: 50% discount
 * - Merit: ₹3,000 fixed
 * 
 * Calculation:
 * Base Fee: ₹45,000
 * SC Discount: ₹22,500 (50%)
 * Merit Discount: ₹3,000
 * Total Discount: ₹25,500
 * Final Amount: ₹19,500
 * 
 * USAGE IN CONTROLLERS:
 * $calculation = FeeCalculationService::calculateFeeWithScholarship($student, $feeStructure);
 * $finalAmount = $calculation['final_amount'];
 * 
 * DATABASE IMPACT:
 * - Reads from scholarships and student_scholarships tables
 * - Does not modify any data (read-only operation)
 * - Results are used by other parts of system for fee assignment
 * =============================================================================
 */