<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('department', 'like', "%{$request->search}%");
            });
        }

        if ($request->department) $query->where('department', $request->department);
        if ($request->status)     $query->where('status', $request->status);

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:employees',
            'role'       => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'salary'     => 'required|numeric|min:0',
            'status'     => 'in:active,inactive',
            'phone'      => 'nullable|string',
            'address'    => 'nullable|string',
            'joined_at'  => 'nullable|date',
        ]);

        $employee = Employee::create($data);

        return response()->json(['message' => 'Employee created.', 'employee' => $employee], 201);
    }

    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:employees,email,' . $employee->id,
            'role'       => 'sometimes|string',
            'department' => 'sometimes|string',
            'salary'     => 'sometimes|numeric|min:0',
            'status'     => 'sometimes|in:active,inactive',
            'phone'      => 'nullable|string',
            'address'    => 'nullable|string',
        ]);

        $employee->update($data);

        return response()->json(['message' => 'Employee updated.', 'employee' => $employee]);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employee removed.']);
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        $request->validate(['status' => 'required|in:active,inactive']);
        $employee->update(['status' => $request->status]);
        return response()->json(['message' => 'Status updated.', 'employee' => $employee]);
    }
}
