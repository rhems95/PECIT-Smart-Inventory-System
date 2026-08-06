<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Services\StudentAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplyStudentController extends Controller
{
    public function __construct(
        protected StudentAccountService $students,
    ) {}

    public function index(Request $request): View
    {
        $query = User::role('Student')->with('department')->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        return view('supply.students.index', [
            'students' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('supply.students.form', [
            'student' => new User,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->students->create($data, $request->user());

        return redirect()->route('supply.students.index')->with('success', 'Student account created.');
    }

    public function edit(User $student): View
    {
        $this->ensureStudent($student);

        return view('supply.students.form', [
            'student' => $student,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $data = $this->validated($request, $student);
        $this->students->update($student, $data, $request->user());

        return redirect()->route('supply.students.index')->with('success', 'Student account updated.');
    }

    public function importForm(): View
    {
        return view('supply.students.import', [
            'departments' => Department::orderBy('code')->get(),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $result = $this->students->importFromCsv($request->file('file'), $request->user());

        $message = "Import finished: {$result['created']} created, {$result['skipped']} skipped.";

        if (! empty($result['errors'])) {
            $preview = collect($result['errors'])->take(8)->implode(' ');

            return redirect()
                ->route('supply.students.import')
                ->with('success', $message)
                ->with('error', $preview.(count($result['errors']) > 8 ? ' …' : ''));
        }

        return redirect()->route('supply.students.index')->with('success', $message);
    }

    public function template(): Response
    {
        return response($this->students->templateCsv(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student-import-template.csv"',
        ]);
    }

    /**
     * @return array{name: string, last_name: string, email: string, employee_id: string, department_id: int, phone: ?string, is_active: bool}
     */
    protected function validated(Request $request, ?User $student = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($student?->id)],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($student?->id)],
            'department_id' => ['required', 'exists:departments,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }

    protected function ensureStudent(User $student): void
    {
        abort_unless($student->hasRole('Student'), 404);
    }
}
