<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date      = $request->date ? Carbon::parse($request->date) : today();
        $employees = Employee::where('status','active')->get();

        // Today's attendance keyed by employee_id
        $records = Attendance::whereDate('date', $date)
            ->get()->keyBy('employee_id');

        // Monthly summary for current month
        $monthly = Attendance::whereMonth('date', $date->month)
            ->whereYear('date',  $date->year)
            ->selectRaw('employee_id, status, count(*) as total')
            ->groupBy('employee_id','status')
            ->get()->groupBy('employee_id');

        return view('admin.attendance.index', compact('employees','records','date','monthly'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,half_day,late,on_leave',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'note'        => 'nullable|string|max:255',
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            $data
        );

        return back()->with('success','Attendance saved.');
    }

    public function bulkMark(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'employee_ids'   => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'status'     => 'required|in:present,absent,half_day,late,on_leave',
        ]);

        foreach ($request->employee_ids as $empId) {
            Attendance::updateOrCreate(
                ['employee_id' => $empId, 'date' => $request->date],
                ['status' => $request->status]
            );
        }

        return back()->with('success', count($request->employee_ids).' records marked as '.ucfirst($request->status).'.');
    }

    public function report(Request $request)
    {
        $month     = $request->month ?? now()->format('Y-m');
        $start     = Carbon::parse($month.'-01')->startOfMonth();
        $end       = $start->copy()->endOfMonth();
        $employees = Employee::where('status','active')->get();

        $data = $employees->map(function ($emp) use ($start, $end) {
            $records = $emp->attendance()->whereBetween('date',[$start,$end])->get();
            return [
                'employee' => $emp,
                'present'  => $records->where('status','present')->count(),
                'absent'   => $records->where('status','absent')->count(),
                'half_day' => $records->where('status','half_day')->count(),
                'late'     => $records->where('status','late')->count(),
                'leave'    => $records->where('status','on_leave')->count(),
                'days'     => $start->diffInWeekdays($end) + 1,
            ];
        });

        return view('admin.attendance.report', compact('data','month','start','end'));
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return back()->with('success','Record deleted.');
    }
}
