<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::withTrashed();
        if ($request->search) $query->where('name','like',"%{$request->search}%")->orWhere('email','like',"%{$request->search}%");
        if ($request->dept)   $query->where('department',$request->dept);
        if ($request->status) $query->where('status',$request->status);
        $employees   = $query->latest()->paginate(15)->withQueryString();
        $departments = Employee::select('department')->distinct()->pluck('department');
        return view('admin.employees.index', compact('employees','departments'));
    }

    public function create()
    {
        return view('admin.employees.form', ['employee' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email',
            'phone'      => 'nullable|string|max:20',
            'role'       => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'salary'     => 'required|numeric|min:0',
            'status'     => 'required|in:active,inactive',
            'joined_at'  => 'nullable|date',
            'address'    => 'nullable|string',
        ]);
        Employee::create($data);
        return redirect()->route('admin.employees.index')->with('success','Employee added.');
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email,'.$employee->id,
            'phone'      => 'nullable|string|max:20',
            'role'       => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'salary'     => 'required|numeric|min:0',
            'status'     => 'required|in:active,inactive',
            'joined_at'  => 'nullable|date',
            'address'    => 'nullable|string',
        ]);
        $employee->update($data);
        return redirect()->route('admin.employees.index')->with('success','Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success','Employee removed.');
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->update(['status' => $employee->status === 'active' ? 'inactive' : 'active']);
        return back()->with('success','Status updated.');
    }
}
